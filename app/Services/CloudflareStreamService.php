<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareStreamService
{
    protected string $accountId;
    protected string $apiToken;
    protected string $customerSubdomain;
    protected int    $maxDuration;
    protected array  $allowedOrigins;

    public function __construct()
    {
        // Lire depuis les settings DB en priorité, fallback sur .env
        $this->accountId         = setting('cf_stream_account_id')    ?: config('cloudflare.stream.account_id', '');
        $this->apiToken          = setting('cf_stream_api_token')      ?: config('cloudflare.stream.api_token', '');
        $this->customerSubdomain = setting('cf_stream_customer_subdomain') ?: config('cloudflare.stream.customer_subdomain', '');
        $this->maxDuration       = (int)(setting('cf_stream_max_duration') ?: config('cloudflare.stream.max_duration_seconds', 3600));
        $this->allowedOrigins    = config('cloudflare.stream.allowed_origins', []);
    }

    public function isEnabled(): bool
    {
        $enabledSetting = setting('cf_stream_enabled');
        $enabled = $enabledSetting !== null
            ? ($enabledSetting == '1')
            : config('cloudflare.stream.enabled', false);

        return $enabled && !empty($this->accountId) && !empty($this->apiToken);
    }

    /**
     * Génère une URL d'upload one-time pour le Direct Creator Upload (POST simple).
     * Retourne ['uid' => '...', 'uploadURL' => '...'] ou lance une exception.
     */
    public function generateDirectUploadUrl(array $meta = []): array
    {
        $payload = [
            'maxDurationSeconds' => $this->maxDuration,
            'expiry'             => now()->addHours(2)->toIso8601String(),
        ];

        if (!empty($this->allowedOrigins)) {
            $payload['allowedOrigins'] = $this->allowedOrigins;
        }

        if (!empty($meta['name'])) {
            $payload['meta'] = ['name' => $meta['name']];
        }

        if (!empty($meta['creator'])) {
            $payload['creator'] = $meta['creator'];
        }

        $response = Http::withToken($this->apiToken)
            ->timeout(30)
            ->post("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/direct_upload", $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Cloudflare Stream API error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            $msg = collect($data['errors'] ?? [])->pluck('message')->implode(', ');
            throw new \RuntimeException('Cloudflare Stream error: ' . $msg);
        }

        return [
            'uid'       => $data['result']['uid'],
            'uploadURL' => $data['result']['uploadURL'],
        ];
    }

    /**
     * Génère une URL TUS one-time pour les vidéos > 200 MB ou connexions instables.
     * Retourne ['uid' => '...', 'tusEndpoint' => '...']
     */
    public function generateTusUploadUrl(int $fileSize, array $meta = []): array
    {
        $maxDurB64   = base64_encode((string) $this->maxDuration);
        $nameB64     = base64_encode($meta['name'] ?? 'video');
        $creatorB64  = base64_encode($meta['creator'] ?? '');

        $uploadMetadata = "maxdurationseconds {$maxDurB64},name {$nameB64}";
        if (!empty($meta['creator'])) {
            $uploadMetadata .= ",creator {$creatorB64}";
        }

        $response = Http::withToken($this->apiToken)
            ->withHeaders([
                'Tus-Resumable'   => '1.0.0',
                'Upload-Length'   => (string) $fileSize,
                'Upload-Metadata' => $uploadMetadata,
            ])
            ->timeout(30)
            ->post("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream?direct_user=true");

        if ($response->status() !== 201) {
            throw new \RuntimeException(
                'Cloudflare Stream TUS error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        $location = $response->header('Location');
        if (empty($location)) {
            throw new \RuntimeException('Cloudflare Stream TUS: Location header manquant dans la réponse');
        }

        // Extraire l'UID depuis le header Stream-Media-Id ou depuis l'URL Location
        $uid = $response->header('Stream-Media-Id', '');
        if (empty($uid)) {
            // Extraire depuis l'URL : https://upload.videodelivery.net/tus/{uid}
            if (preg_match('/\/tus\/([a-f0-9]{32})/i', $location, $m)) {
                $uid = $m[1];
            }
        }

        return [
            'uid'         => $uid,
            'tusEndpoint' => $location,
        ];
    }

    /**
     * Récupère le statut d'une vidéo Cloudflare Stream.
     */
    public function getVideoStatus(string $uid): array
    {
        $response = Http::withToken($this->apiToken)
            ->timeout(15)
            ->get("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$uid}");

        if (!$response->successful()) {
            return ['status' => 'error', 'uid' => $uid];
        }

        $result = $response->json()['result'] ?? [];

        return [
            'uid'           => $uid,
            'status'        => $result['status']['state'] ?? 'unknown',
            'readyToStream' => $result['readyToStream'] ?? false,
            'duration'      => $result['duration'] ?? null,
            'thumbnail'     => $result['thumbnail'] ?? null,
            'embedUrl'      => $this->buildEmbedUrl($uid),
            'iframeUrl'     => $this->buildIframeUrl($uid),
        ];
    }

    /**
     * Supprime une vidéo de Cloudflare Stream.
     */
    public function deleteVideo(string $uid): bool
    {
        $response = Http::withToken($this->apiToken)
            ->timeout(15)
            ->delete("https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/stream/{$uid}");

        return $response->successful();
    }

    /**
     * Construit l'URL embed iframe pour le player SEN-EVENTS.
     * Le format est reconnu par decryptVideoUrl() comme 'embedded'.
     */
    public function buildIframeUrl(string $uid): string
    {
        if (empty($this->customerSubdomain)) {
            return "https://iframe.videodelivery.net/{$uid}";
        }

        return "https://customer-{$this->customerSubdomain}.cloudflarestream.com/{$uid}/iframe?autoplay=true";
    }

    /**
     * Construit le tag iframe HTML complet (stocké dans video_url_input).
     */
    public function buildEmbedUrl(string $uid): string
    {
        $src = $this->buildIframeUrl($uid);

        return "<iframe src=\"{$src}\" style=\"border:none;width:100%;height:100%\" "
             . "allow=\"accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture\" "
             . "allowfullscreen=\"true\"></iframe>";
    }

    /**
     * Vérifie la signature du webhook Cloudflare Stream.
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        $secret = config('cloudflare.stream.webhook_secret', '');
        if (empty($secret)) return true; // pas de secret configuré → on accepte

        $expected = hash_hmac('sha256', $body, $secret);
        return hash_equals($expected, $signature);
    }
}

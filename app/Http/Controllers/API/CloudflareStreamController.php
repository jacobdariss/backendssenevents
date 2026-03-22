<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CloudflareStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Video\Models\Video;

class CloudflareStreamController extends Controller
{
    protected CloudflareStreamService $cf;

    public function __construct(CloudflareStreamService $cf)
    {
        $this->cf = $cf;
    }

    /**
     * Génère une URL d'upload one-time (POST simple, vidéos ≤ 200 MB).
     * POST /api/cf-stream/upload-url
     */
    public function generateUploadUrl(Request $request)
    {
        if (!$this->cf->isEnabled()) {
            return response()->json(['success' => false, 'message' => 'Cloudflare Stream non activé.'], 503);
        }

        $request->validate([
            'name'         => 'nullable|string|max:255',
            'content_type' => 'nullable|string|in:movie,tvshow,episode,video',
        ]);

        try {
            $result = $this->cf->generateDirectUploadUrl([
                'name'    => $request->input('name', 'video'),
                'creator' => 'user-' . auth()->id(),
            ]);

            return response()->json([
                'success'   => true,
                'uid'       => $result['uid'],
                'uploadURL' => $result['uploadURL'],
            ]);
        } catch (\Exception $e) {
            Log::error('CF Stream generateUploadUrl: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère une URL TUS pour les vidéos > 200 MB.
     * POST /api/cf-stream/tus-url
     */
    public function generateTusUrl(Request $request)
    {
        if (!$this->cf->isEnabled()) {
            return response()->json(['success' => false, 'message' => 'Cloudflare Stream non activé.'], 503);
        }

        $request->validate([
            'file_size' => 'required|integer|min:1',
            'name'      => 'nullable|string|max:255',
        ]);

        try {
            $result = $this->cf->generateTusUploadUrl(
                $request->integer('file_size'),
                [
                    'name'    => $request->input('name', 'video'),
                    'creator' => 'user-' . auth()->id(),
                ]
            );

            return response()->json([
                'success'     => true,
                'uid'         => $result['uid'],
                'tusEndpoint' => $result['tusEndpoint'],
            ]);
        } catch (\Exception $e) {
            Log::error('CF Stream generateTusUrl: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Polling statut.
     * GET /api/cf-stream/status/{uid}
     */
    public function videoStatus(string $uid)
    {
        if (!$this->cf->isEnabled()) {
            return response()->json(['success' => false], 503);
        }

        try {
            return response()->json(['success' => true, 'data' => $this->cf->getVideoStatus($uid)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Webhook — pas d'auth, vérification par signature HMAC.
     * POST /api/cf-stream/webhook
     */
    public function webhook(Request $request)
    {
        $body = $request->getContent();

        if (!$this->cf->verifyWebhookSignature($body, $request->header('Webhook-Signature', ''))) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = json_decode($body, true);
        $uid     = $payload['uid'] ?? null;
        $state   = $payload['status']['state'] ?? null;

        if (!$uid || !$state) {
            return response()->json(['ok' => true]);
        }

        $cfStatus = match($state) {
            'ready'         => 'ready',
            'error'         => 'error',
            'inprogress'    => 'processing',
            default         => 'pending',
        };

        $embedUrl = $state === 'ready' ? $this->cf->buildEmbedUrl($uid) : null;

        foreach ([Entertainment::class, Episode::class, Video::class] as $model) {
            $record = $model::where('cf_stream_uid', $uid)->first();
            if ($record) {
                $record->cf_stream_status = $cfStatus;
                if ($embedUrl) $record->video_url_input = $embedUrl;
                $record->save();
                break;
            }
        }

        return response()->json(['ok' => true]);
    }
}

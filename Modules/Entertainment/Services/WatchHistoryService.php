<?php

namespace Modules\Entertainment\Services;

use Illuminate\Http\Request;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Video\Models\Video;

/**
 * WatchHistoryService
 * Responsable : enregistrement des vues, tracking analytics
 * Extrait de EntertainmentsController (lignes 2121-2170)
 */
class WatchHistoryService
{
    public function recordView(Request $request, int $userId): EntertainmentView
    {
        $data                 = $request->all();
        $data['user_id']      = $userId;
        $data['device_type']  = getDeviceType($request);

        // Platform depuis User-Agent
        $ua = $request->header('User-Agent', '');
        if (preg_match('/android/i', $ua))             $data['platform'] = 'Android';
        elseif (preg_match('/iphone|ipad|ios/i', $ua)) $data['platform'] = 'iOS';
        elseif (preg_match('/windows/i', $ua))         $data['platform'] = 'Windows';
        elseif (preg_match('/macintosh|mac os/i', $ua)) $data['platform'] = 'macOS';
        elseif (preg_match('/linux/i', $ua))            $data['platform'] = 'Linux';
        else                                            $data['platform'] = 'Web';

        // IP & pays Cloudflare
        $data['ip_address']   = $request->ip();
        $data['country_code'] = $request->header('CF-IPCountry')
            ?? $request->header('X-Country-Code')
            ?? null;

        $data['content_type'] = $request->content_type ?? null;

        // Résoudre partner_id depuis le contenu
        if ($request->entertainment_id) {
            $ent = Entertainment::select('partner_id')->find($request->entertainment_id);
            if ($ent?->partner_id) $data['partner_id'] = $ent->partner_id;
        }
        if (!isset($data['partner_id']) && $request->episode_id) {
            $ep = Episode::select('partner_id')->find($request->episode_id);
            if ($ep?->partner_id) $data['partner_id'] = $ep->partner_id;
        }

        if ($request->episode_id) $data['episode_id'] = $request->episode_id;
        if ($request->video_id)   $data['video_id']   = $request->video_id;

        return EntertainmentView::create($data);
    }

    public function syncWatchTime(int $userId, int $entertainmentId, string $watchedTime): void
    {
        $parts   = explode(':', $watchedTime ?? '0:0:0');
        $seconds = (int)($parts[0] ?? 0) * 3600
                 + (int)($parts[1] ?? 0) * 60
                 + (int)($parts[2] ?? 0);

        if ($seconds > 0) {
            EntertainmentView::where('user_id', $userId)
                ->where('entertainment_id', $entertainmentId)
                ->update(['watch_time' => $seconds]);
        }
    }
}

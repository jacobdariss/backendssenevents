<?php

namespace Modules\Entertainment\Services;

use Illuminate\Http\Request;
use Modules\Entertainment\Models\Entertainment;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;

/**
 * SearchService
 * Responsable : recherche unifiée movies, tvshows, videos, livetv
 * Extrait de EntertainmentsController (lignes 833-1047)
 */
class SearchService
{
    public function search(Request $request, ?int $userId = null, ?int $profileId = null): array
    {
        $term      = $request->input('keyword', $request->input('search', ''));
        $perPage   = $request->input('per_page', 10);
        $type      = $request->input('type');

        $results = [];

        // Films & Séries
        if (!$type || in_array($type, ['movie', 'tvshow'])) {
            $q = Entertainment::where('status', 1)
                ->whereNull('deleted_at')
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
                });

            if ($type) $q->where('type', $type);
            if ($request->has('is_restricted')) $q->where('is_restricted', $request->is_restricted);

            $results['entertainments'] = $q->orderBy('name')->limit($perPage)->get();
        }

        // Vidéos
        if (!$type || $type === 'video') {
            $results['videos'] = Video::where('status', 1)
                ->whereNull('deleted_at')
                ->where('name', 'like', "%{$term}%")
                ->orderBy('name')
                ->limit($perPage)
                ->get();
        }

        // Live TV
        if (!$type || $type === 'livetv') {
            $results['livetv'] = LiveTvChannel::where('status', 1)
                ->where('name', 'like', "%{$term}%")
                ->orderBy('name')
                ->limit($perPage)
                ->get();
        }

        return $results;
    }
}

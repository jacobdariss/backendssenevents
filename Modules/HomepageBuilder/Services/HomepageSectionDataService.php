<?php

namespace Modules\HomepageBuilder\Services;

use Modules\HomepageBuilder\Models\HomepageSection;
use Modules\Entertainment\Models\Entertainment;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;
use Modules\Genres\Models\Genres;
use Modules\CastCrew\Models\CastCrew;
use Modules\Constant\Models\Constant;
use Modules\Entertainment\Transformers\Backend\CommonContentResourceV3;
use Modules\Video\Transformers\Backend\VideoResourceV3;
use Modules\LiveTV\Transformers\Backend\LiveTvChannelResourceV3;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class HomepageSectionDataService
{
    /**
     * Charge les données pour une section HomepageBuilder.
     * Retourne null si le type ne nécessite pas de données (banner, continue_watching...).
     */
    public function loadForSection(HomepageSection $section, Request $request): ?array
    {
        $limit = $section->content_limit ?? 20;
        $sort  = $section->sort_by ?? 'created_at';
        $ct    = $section->content_type;

        // Si des IDs sont sélectionnés manuellement, on les utilise
        $manualIds = $section->content_ids ?? [];

        switch ($section->type) {

            case 'entertainment':
                return $this->loadEntertainment($ct, $limit, $sort, $manualIds, $request);

            case 'video':
                return $this->loadVideos($limit, $sort, $manualIds, $request);

            case 'livetv':
                return $this->loadLiveTV($limit, $manualIds, $request);

            case 'genre':
                return $this->loadGenres($limit, $manualIds);

            case 'personality':
                return $this->loadPersonalities($limit, $manualIds);

            case 'language':
                return $this->loadLanguages($limit, $manualIds);

            // Ces types ont des données chargées séparément (banner, payperview, continue_watching, top_10)
            default:
                return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function loadEntertainment(?string $ct, int $limit, string $sort, array $ids, Request $request): array
    {
        $query = Entertainment::where('status', 1)->whereNull('deleted_at');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if (!empty($ct) && in_array($ct, ['movie', 'tvshow'])) {
            $query->where('type', $ct);
        }

        // Tri
        match ($sort) {
            'views'        => $query->orderByDesc('views'),
            'likes'        => $query->orderByDesc('likes'),
            'release_date' => $query->orderByDesc('release_date'),
            default        => $query->orderByDesc('created_at'),
        };

        $items = $query->with(['genresdata:id,name'])
                       ->limit($limit)
                       ->get();

        return [
            'data' => CommonContentResourceV3::collection($items)->toArray($request),
        ];
    }

    private function loadVideos(int $limit, string $sort, array $ids, Request $request): array
    {
        $query = Video::where('status', 1)->whereNull('deleted_at')
                      ->where(function ($q) {
                          $q->whereNull('release_date')->orWhereDate('release_date', '<=', now());
                      });

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        match ($sort) {
            'views'        => $query->orderByDesc('views'),
            'likes'        => $query->orderByDesc('likes'),
            'release_date' => $query->orderByDesc('release_date'),
            default        => $query->orderByDesc('created_at'),
        };

        return [
            'data' => VideoResourceV3::collection($query->limit($limit)->get())->toArray($request),
        ];
    }

    private function loadLiveTV(int $limit, array $ids, Request $request): array
    {
        $query = LiveTvChannel::where('status', 1)->whereNull('deleted_at');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->orderByDesc('created_at');
        }

        return [
            'data' => LiveTvChannelResourceV3::collection($query->limit($limit)->get())->toArray($request),
        ];
    }

    private function loadGenres(int $limit, array $ids): array
    {
        $query = Genres::where('status', 1)->whereNull('deleted_at');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->orderBy('name');
        }

        $genres = $query->limit($limit)->get()->map(fn ($g) => [
            'id'           => $g->id,
            'name'         => $g->name,
            'poster_image' => !empty($g->file_url) ? setBaseUrlWithFileName($g->file_url, 'image', 'genres') : null,
        ])->toArray();

        return ['data' => $genres];
    }

    private function loadPersonalities(int $limit, array $ids): array
    {
        $query = CastCrew::where('status', 1)->whereNull('deleted_at');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->orderByDesc('created_at');
        }

        return [
            'data' => $query->limit($limit)->get(),
        ];
    }

    private function loadLanguages(int $limit, array $ids): array
    {
        $query = Constant::where('type', 'movie_language')->where('status', 1)->whereNull('deleted_at');

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } else {
            $query->orderBy('name');
        }

        return [
            'data' => $query->select('id', 'name', 'language_image')->limit($limit)->get(),
        ];
    }
}

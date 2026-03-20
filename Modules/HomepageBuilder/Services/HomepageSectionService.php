<?php

namespace Modules\HomepageBuilder\Services;

use Illuminate\Http\Request;
use Modules\HomepageBuilder\Models\HomepageSection;
use Modules\Entertainment\Models\Entertainment;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Video\Models\Video;
use Modules\LiveTV\Models\LiveTvChannel;
use Modules\Genres\Models\Genres;
use Modules\CastCrew\Models\CastCrew;
use Modules\Banner\Models\Banner;
use Illuminate\Support\Facades\DB;

class HomepageSectionService
{
    /**
     * Charger le contenu d'une section selon son type et sa config
     */
    public function loadContent(HomepageSection $section, ?int $userId = null): array
    {
        $limit = $section->content_limit ?: 20;

        return match($section->type) {
            'entertainment'     => $this->loadEntertainment($section, $limit),
            'video'             => $this->loadVideos($section, $limit),
            'livetv'            => $this->loadLiveTV($section, $limit),
            'genre'             => $this->loadGenres($section, $limit),
            'personality'       => $this->loadPersonalities($section, $limit),
            'language'          => $this->loadLanguages($section, $limit),
            'payperview'        => $this->loadPayPerView($section, $limit),
            'banner'            => $this->loadBanners($section),
            'continue_watching' => [],  // Géré côté vue (requiert user_id)
            default             => [],
        };
    }

    // ── Entertainment (Films / Séries) ────────────────────────────────────────
    private function loadEntertainment(HomepageSection $section, int $limit): array
    {
        $contentType = $section->content_type ?: 'movie';

        // Si IDs manuels sélectionnés
        if (!empty($section->content_ids)) {
            return Entertainment::whereIn('id', $section->content_ids)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get()
                ->sortBy(fn($e) => array_search($e->id, $section->content_ids))
                ->values()
                ->toArray();
        }

        $query = Entertainment::where('type', $contentType)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('release_date')->orWhereDate('release_date', '<=', now());
            });

        // Tri
        switch ($section->sort_by) {
            case 'views':
                $viewedIds = EntertainmentView::select('entertainment_id', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('entertainment_id')->orderByRaw('COUNT(*) DESC')
                    ->pluck('entertainment_id');
                $query->whereIn('id', $viewedIds);
                $items = $query->get()->sortBy(fn($e) => $viewedIds->search($e->id))->values();
                return $items->take($limit)->toArray();
            case 'release_date':
                $query->orderByDesc('release_date');
                break;
            case 'likes':
                $query->withCount('entertainmentLike')->orderByDesc('entertainment_like_count');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        // Filtre spécifique par slug
        if ($section->slug === 'free-movies') {
            $query->where('movie_access', 'free');
        } elseif ($section->slug === 'latest-movies') {
            $query->whereDate('release_date', '>=', now()->subMonths(12));
        }

        return $query->limit($limit)->get()->toArray();
    }

    // ── Vidéos ────────────────────────────────────────────────────────────────
    private function loadVideos(HomepageSection $section, int $limit): array
    {
        if (!empty($section->content_ids)) {
            return Video::whereIn('id', $section->content_ids)
                ->where('status', 1)->whereNull('deleted_at')
                ->get()->sortBy(fn($v) => array_search($v->id, $section->content_ids))
                ->values()->toArray();
        }

        $query = Video::where('status', 1)->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('release_date')->orWhereDate('release_date', '<=', now());
            });

        match($section->sort_by) {
            'views'        => $query->withCount('entertainmentView')->orderByDesc('entertainment_view_count'),
            'release_date' => $query->orderByDesc('release_date'),
            default        => $query->orderByDesc('created_at'),
        };

        return $query->limit($limit)->get()->toArray();
    }

    // ── Live TV ───────────────────────────────────────────────────────────────
    private function loadLiveTV(HomepageSection $section, int $limit): array
    {
        if (!empty($section->content_ids)) {
            return LiveTvChannel::whereIn('id', $section->content_ids)
                ->where('status', 1)->get()->toArray();
        }
        return LiveTvChannel::where('status', 1)->limit($limit)->get()->toArray();
    }

    // ── Genres ────────────────────────────────────────────────────────────────
    private function loadGenres(HomepageSection $section, int $limit): array
    {
        return Genres::where('status', 1)->whereNull('deleted_at')
            ->orderBy('name')->limit($limit)->get()->toArray();
    }

    // ── Personnalités ─────────────────────────────────────────────────────────
    private function loadPersonalities(HomepageSection $section, int $limit): array
    {
        return CastCrew::where('type', 'actor')
            ->whereNull('deleted_at')->limit($limit)->get()->toArray();
    }

    // ── Langues ───────────────────────────────────────────────────────────────
    private function loadLanguages(HomepageSection $section, int $limit): array
    {
        return \Modules\Constant\Models\Constant::where('type', 'movie_language')
            ->where('status', 1)->limit($limit)->get()->toArray();
    }

    // ── Pay Per View ──────────────────────────────────────────────────────────
    private function loadPayPerView(HomepageSection $section, int $limit): array
    {
        return Entertainment::where('movie_access', 'pay-per-view')
            ->where('status', 1)->whereNull('deleted_at')
            ->limit($limit)->get()->toArray();
    }

    // ── Bannières ─────────────────────────────────────────────────────────────
    private function loadBanners(HomepageSection $section): array
    {
        return Banner::where('status', 1)->orderBy('position')->get()->toArray();
    }
}

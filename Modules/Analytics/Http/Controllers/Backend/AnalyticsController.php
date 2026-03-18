<?php

namespace Modules\Analytics\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Video\Models\Video;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Entertainment\Models\Like;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        $isPartner = auth()->user()->hasRole('partner');
        $partnerId = $isPartner ? optional(auth()->user()->partner)->id : null;

        // Period filter: 7, 30, 90 days (default 30)
        $days = (int) $request->get('days', 30);
        if (!in_array($days, [7, 30, 90])) {
            $days = 30;
        }

        $since = now()->subDays($days)->startOfDay();

        // Base query scoped to partner if applicable
        $videoQuery = Video::query()->whereNull('videos.deleted_at');
        if ($isPartner && $partnerId) {
            $videoQuery->where('videos.partner_id', $partnerId);
        }

        $videoIds = (clone $videoQuery)->pluck('id');

        // Total views in period
        $totalViews = EntertainmentView::whereIn('entertainment_id', $videoIds)
            ->where('created_at', '>=', $since)
            ->count();

        // Total views all time
        $totalViewsAllTime = EntertainmentView::whereIn('entertainment_id', $videoIds)->count();

        // Unique viewers in period
        $uniqueViewers = EntertainmentView::whereIn('entertainment_id', $videoIds)
            ->where('created_at', '>=', $since)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Total videos
        $totalVideos = $videoIds->count();

        // Likes in period
        $totalLikes = Like::whereIn('entertainment_id', $videoIds)
            ->where('type', 'video')
            ->where('is_like', 1)
            ->where('created_at', '>=', $since)
            ->count();

        // Top 10 most watched videos in period
        $topVideos = (clone $videoQuery)
            ->select('videos.id', 'videos.name', 'videos.poster_url')
            ->withCount([
                'entertainmentView as views_count' => function ($q) use ($since) {
                    $q->where('created_at', '>=', $since);
                },
                'entertainmentLike as likes_count' => function ($q) use ($since) {
                    $q->where('is_like', 1)->where('type', 'video')->where('created_at', '>=', $since);
                },
            ])
            ->orderByDesc('views_count')
            ->limit(10)
            ->get()
            ->each(function ($video) {
                $video->poster_url = $video->poster_url
                    ? setBaseUrlWithFileName($video->poster_url, 'image', 'video')
                    : null;
            });

        return view('analytics::backend.analytics.index', compact(
            'totalViews',
            'totalViewsAllTime',
            'uniqueViewers',
            'totalVideos',
            'totalLikes',
            'topVideos',
            'days',
            'isPartner'
        ));
    }

    /**
     * Return daily view counts for chart (JSON).
     */
    public function chartData(Request $request)
    {
        $isPartner = auth()->user()->hasRole('partner');
        $partnerId = $isPartner ? optional(auth()->user()->partner)->id : null;

        $days = (int) $request->get('days', 30);
        if (!in_array($days, [7, 30, 90])) {
            $days = 30;
        }

        $since = now()->subDays($days - 1)->startOfDay();

        $videoIds = Video::query()
            ->whereNull('deleted_at')
            ->when($isPartner && $partnerId, fn($q) => $q->where('partner_id', $partnerId))
            ->pluck('id');

        $rawData = EntertainmentView::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereIn('entertainment_id', $videoIds)
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Fill all days with 0 if no views
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $data[] = $rawData[$date] ?? 0;
        }

        return response()->json(['labels' => $labels, 'data' => $data]);
    }

    /**
     * Return top videos data for bar chart (JSON).
     */
    public function topVideos(Request $request)
    {
        $isPartner = auth()->user()->hasRole('partner');
        $partnerId = $isPartner ? optional(auth()->user()->partner)->id : null;

        $days = (int) $request->get('days', 30);
        if (!in_array($days, [7, 30, 90])) {
            $days = 30;
        }

        $since = now()->subDays($days)->startOfDay();

        $results = Video::query()
            ->whereNull('videos.deleted_at')
            ->when($isPartner && $partnerId, fn($q) => $q->where('videos.partner_id', $partnerId))
            ->select('videos.id', 'videos.name')
            ->withCount(['entertainmentView as views_count' => function ($q) use ($since) {
                $q->where('created_at', '>=', $since);
            }])
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        return response()->json([
            'labels' => $results->pluck('name'),
            'data'   => $results->pluck('views_count'),
        ]);
    }
}

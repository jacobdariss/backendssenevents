<?php
namespace Modules\Analytics\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Partner\Models\Partner;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Video\Models\Video;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $period = $request->get('period', '30d');
        [$from, $to] = $this->analytics->getPeriodDates($period);

        $stats         = $this->analytics->globalStats($from, $to);
        $viewsPerDay   = $this->analytics->viewsPerDay($from, $to);
        $byDevice      = $this->analytics->viewsByDevice($from, $to);
        $byPlatform    = $this->analytics->viewsByPlatform($from, $to);
        $byCountry     = $this->analytics->viewsByCountry($from, $to);
        $topContent    = $this->analytics->topContent($from, $to, null, 10)->map(fn($r) => tap($r, fn($r) => $r->content_name = $this->resolveName($r)));
        $revenuePerDay = $this->analytics->revenuePerDay($from, $to);
        $partners      = Partner::where('status', 1)->orderBy('name')->get();
        $module_action = 'Analytics';

        return view('analytics::backend.analytics.index', compact(
            'stats','viewsPerDay','byDevice','byPlatform','byCountry',
            'topContent','revenuePerDay','partners','period','module_action'
        ));
    }

    public function partner(Request $request, int $partnerId)
    {
        $partner = Partner::findOrFail($partnerId);
        $period  = $request->get('period', '30d');
        [$from, $to] = $this->analytics->getPeriodDates($period);

        $stats = [
            'total_views' => $this->analytics->totalViews($from, $to, $partnerId),
            'watch_time'  => $this->analytics->totalWatchTime($from, $to, $partnerId),
            'ppv_revenue' => $this->analytics->ppvRevenue($from, $to, $partnerId),
        ];
        $viewsPerDay = $this->analytics->viewsPerDay($from, $to, $partnerId);
        $byDevice    = $this->analytics->viewsByDevice($from, $to, $partnerId);
        $byPlatform  = $this->analytics->viewsByPlatform($from, $to, $partnerId);
        $byCountry   = $this->analytics->viewsByCountry($from, $to, $partnerId);
        $topContent  = $this->analytics->topContent($from, $to, $partnerId, 10)->map(fn($r) => tap($r, fn($r) => $r->content_name = $this->resolveName($r)));
        $module_action = 'Analytics';

        return view('analytics::backend.analytics.partner', compact(
            'partner','stats','viewsPerDay','byDevice','byPlatform',
            'byCountry','topContent','period','module_action'
        ));
    }

    public function partnerSelf(Request $request)
    {
        $partner = Partner::where('user_id', auth()->id())->firstOrFail();
        return $this->partner($request, $partner->id);
    }

    protected function resolveName($row): string
    {
        try {
            if ($row->episode_id) return Episode::find($row->episode_id)?->name ?? 'Épisode #'.$row->episode_id;
            if ($row->video_id)   return Video::find($row->video_id)?->name   ?? 'Vidéo #'.$row->video_id;
            if ($row->entertainment_id) return Entertainment::find($row->entertainment_id)?->name ?? 'Contenu #'.$row->entertainment_id;
        } catch (\Exception $e) {}
        return 'Inconnu';
    }
}

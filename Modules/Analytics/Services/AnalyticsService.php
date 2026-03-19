<?php

namespace Modules\Analytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Frontend\Models\PayperviewTransaction;
use Modules\Partner\Models\Partner;

class AnalyticsService
{
    public function getPeriodDates(string $period = '30d'): array
    {
        return match ($period) {
            '7d'    => [Carbon::now()->subDays(7)->startOfDay(),  Carbon::now()->endOfDay()],
            'month' => [Carbon::now()->startOfMonth(),            Carbon::now()->endOfDay()],
            default => [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()],
        };
    }

    public function totalViews(Carbon $from, Carbon $to, ?int $partnerId = null): int
    {
        $q = EntertainmentView::whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->count();
    }

    public function totalWatchTime(Carbon $from, Carbon $to, ?int $partnerId = null): array
    {
        $q = EntertainmentView::whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        $seconds = $q->sum('watch_time') ?? 0;
        return [
            'seconds' => $seconds,
            'hours'   => round($seconds / 3600, 1),
            'minutes' => round($seconds / 60),
        ];
    }

    public function viewsPerDay(Carbon $from, Carbon $to, ?int $partnerId = null): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('date')->orderBy('date')->get();
    }

    public function viewsByDevice(Carbon $from, Carbon $to, ?int $partnerId = null): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select('device_type', DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('device_type')->orderByDesc('views')->get();
    }

    public function viewsByPlatform(Carbon $from, Carbon $to, ?int $partnerId = null): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select('platform', DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('platform')->orderByDesc('views')->get();
    }

    public function viewsByCountry(Carbon $from, Carbon $to, ?int $partnerId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select('country_code', DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('country_code');
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('country_code')->orderByDesc('views')->limit($limit)->get();
    }

    public function ppvRevenue(Carbon $from, Carbon $to, ?int $partnerId = null): array
    {
        $q = PayperviewTransaction::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success');
        $total = (float) ($q->sum('amount') ?? 0);
        $count = $q->count();
        $commission = 0;
        if ($partnerId) {
            $partner = Partner::find($partnerId);
            $rate = $partner?->commission_rate ?? 0;
            $commission = round($total * $rate / 100, 2);
        }
        return [
            'total'      => round($total, 2),
            'count'      => $count,
            'commission' => $commission,
            'net'        => round($total - $commission, 2),
        ];
    }

    public function revenuePerDay(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return PayperviewTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as transactions'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success')
            ->groupBy('date')->orderBy('date')->get();
    }

    public function topContent(Carbon $from, Carbon $to, ?int $partnerId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select('content_type', 'entertainment_id', 'episode_id', 'video_id',
                DB::raw('COUNT(*) as views'),
                DB::raw('SUM(watch_time) as watch_time'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('content_type', 'entertainment_id', 'episode_id', 'video_id')
            ->orderByDesc('views')->limit($limit)->get();
    }

    public function globalStats(Carbon $from, Carbon $to): array
    {
        $prevFrom = (clone $from)->subDays($from->diffInDays($to));
        $prevTo   = (clone $from)->subSecond();
        return [
            'total_views'    => $this->totalViews($from, $to),
            'prev_views'     => $this->totalViews($prevFrom, $prevTo),
            'watch_time'     => $this->totalWatchTime($from, $to),
            'ppv_revenue'    => $this->ppvRevenue($from, $to),
            'unique_viewers' => EntertainmentView::whereBetween('created_at', [$from, $to])
                                    ->whereNotNull('user_id')->distinct('user_id')->count(),
            'partner_count'  => Partner::where('status', 1)->count(),
        ];
    }
}

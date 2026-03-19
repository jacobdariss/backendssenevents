<?php

namespace Modules\Analytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Frontend\Models\PayperviewTransaction;
use Modules\Partner\Models\Partner;
use Modules\Entertainment\Models\Like;
use Modules\Entertainment\Models\Review;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\Plan;

class AnalyticsService
{
    public function getPeriodDates(string $period = '30d'): array
    {
        return match ($period) {
            '7d'    => [Carbon::now()->subDays(7)->startOfDay(),  Carbon::now()->endOfDay()],
            'month' => [Carbon::now()->startOfMonth(),            Carbon::now()->endOfDay()],
            'all'   => [Carbon::createFromDate(2020, 1, 1)->startOfDay(), Carbon::now()->endOfDay()],
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
        $q = EntertainmentView::select(
                DB::raw("COALESCE(NULLIF(device_type,''), 'Inconnu') as device_type"),
                DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('device_type')->orderByDesc('views')->get();
    }

    public function viewsByPlatform(Carbon $from, Carbon $to, ?int $partnerId = null): \Illuminate\Support\Collection
    {
        $q = EntertainmentView::select(
                DB::raw("COALESCE(NULLIF(platform,''), 'Inconnu') as platform"),
                DB::raw('COUNT(*) as views'))
            ->whereBetween('created_at', [$from, $to]);
        if ($partnerId) $q->where('partner_id', $partnerId);
        return $q->groupBy('platform')->orderByDesc('views')->get();
    }

    public function paymentGatewayStats(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        // Transactions PPV par gateway
        $ppv = \Modules\Frontend\Models\PayperviewTransaction::select(
                'payment_type',
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success')
            ->groupBy('payment_type')
            ->get()
            ->map(fn($r) => ['gateway' => $r->payment_type ?? 'Inconnu', 'transactions' => $r->transactions, 'revenue' => $r->revenue, 'type' => 'PPV']);

        // Abonnements par gateway
        $subs = DB::table('subscriptions_transactions')
            ->select('payment_type',
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(total_amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('payment_type')
            ->get()
            ->map(fn($r) => ['gateway' => $r->payment_type ?? 'Inconnu', 'transactions' => $r->transactions, 'revenue' => $r->revenue, 'type' => 'Abonnement']);

        return collect($ppv)->merge($subs)
            ->groupBy('gateway')
            ->map(function($rows, $gateway) {
                return [
                    'gateway'      => $gateway,
                    'transactions' => $rows->sum('transactions'),
                    'revenue'      => round($rows->sum('revenue'), 2),
                ];
            })
            ->values()
            ->sortByDesc('revenue');
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

    // ─── Likes / Dislikes ────────────────────────────────────────────────────
    public function likesStats(Carbon $from, Carbon $to, ?int $partnerId = null): array
    {
        $baseQuery = Like::whereBetween('likes.created_at', [$from, $to]);

        if ($partnerId) {
            $baseQuery->join('entertainments', 'entertainments.id', '=', 'likes.entertainment_id')
                      ->where('entertainments.partner_id', $partnerId);
        }

        $likes    = (clone $baseQuery)->where('is_like', 1)->count();
        $dislikes = (clone $baseQuery)->where('is_like', 0)->count();
        $total    = $likes + $dislikes;

        return [
            'likes'       => $likes,
            'dislikes'    => $dislikes,
            'total'       => $total,
            'like_rate'   => $total > 0 ? round($likes / $total * 100, 1) : 0,
        ];
    }

    public function likesPerDay(Carbon $from, Carbon $to, ?int $partnerId = null): \Illuminate\Support\Collection
    {
        $q = Like::select(
                DB::raw('DATE(likes.created_at) as date'),
                DB::raw('SUM(likes.is_like = 1) as likes'),
                DB::raw('SUM(likes.is_like = 0) as dislikes'))
            ->whereBetween('likes.created_at', [$from, $to]);
        if ($partnerId) {
            $q->join('entertainments', 'entertainments.id', '=', 'likes.entertainment_id')
              ->where('entertainments.partner_id', $partnerId);
        }
        return $q->groupBy('date')->orderBy('date')->get();
    }

    public function topLikedContent(Carbon $from, Carbon $to, ?int $partnerId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $q = Like::select('likes.entertainment_id',
                DB::raw('SUM(likes.is_like = 1) as likes'),
                DB::raw('SUM(likes.is_like = 0) as dislikes'),
                DB::raw('COUNT(*) as total'))
            ->whereBetween('likes.created_at', [$from, $to])
            ->where('likes.is_like', 1);
        if ($partnerId) {
            $q->join('entertainments', 'entertainments.id', '=', 'likes.entertainment_id')
              ->where('entertainments.partner_id', $partnerId);
        }
        return $q->groupBy('entertainment_id')
            ->orderByDesc('likes')
            ->limit($limit)
            ->get();
    }

    // ─── Abonnements ─────────────────────────────────────────────────────────
    public function subscriptionStats(Carbon $from, Carbon $to): array
    {
        $new      = Subscription::whereBetween('created_at', [$from, $to])->count();
        $active   = Subscription::where('status', 'active')->count();
        $expired  = Subscription::where('status', 'expired')->whereNotNull('end_date')->count();
        $revenue  = (float) (Subscription::whereBetween('created_at', [$from, $to])
                        ->where('status', 'active')->sum('total_amount') ?? 0);

        $prevDays = Carbon::now()->subDays(60)->startOfDay();
        $newPrev  = Subscription::whereBetween('created_at', [$prevDays, $from])->count();

        return [
            'new'         => $new,
            'new_prev'    => $newPrev,
            'active'      => $active,
            'expired'     => $expired,
            'revenue'     => round($revenue, 2),
            'growth'      => $newPrev > 0 ? round(($new - $newPrev) / $newPrev * 100, 1) : 0,
        ];
    }

    public function subscriptionsPerDay(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return Subscription::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')->orderBy('date')->get();
    }

    public function subscriptionsByPlan(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return Subscription::select('name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('name')
            ->orderByDesc('count')
            ->get();
    }

    public function churnRate(Carbon $from, Carbon $to): float
    {
        $expired = Subscription::whereBetween('end_date', [$from, $to])
            ->where('status', 'expired')->count();
        $total   = Subscription::where('status', 'active')
            ->orWhere('status', 'expired')->count();
        return $total > 0 ? round($expired / $total * 100, 1) : 0;
    }


    // ─── Notations (ratings) ────────────────────────────────────────────────
    public function ratingsStats(Carbon $from, Carbon $to, ?int $partnerId = null): array
    {
        $q = Review::whereBetween('reviews.created_at', [$from, $to])
            ->where('reviews.rating', '>', 0);
        if ($partnerId) {
            $q->join('entertainments', 'entertainments.id', '=', 'reviews.entertainment_id')
              ->where('entertainments.partner_id', $partnerId);
        }
        $total  = $q->count();
        $avg    = $total > 0 ? round($q->avg('reviews.rating'), 2) : 0;

        // Distribution 1→5
        $dist = [];
        for ($i = 1; $i <= 5; $i++) {
            $q2 = Review::whereBetween('reviews.created_at', [$from, $to])->where('reviews.rating', $i);
            if ($partnerId) {
                $q2->join('entertainments', 'entertainments.id', '=', 'reviews.entertainment_id')
                   ->where('entertainments.partner_id', $partnerId);
            }
            $dist[$i] = $q2->count();
        }

        return [
            'total'        => $total,
            'average'      => $avg,
            'distribution' => $dist,
        ];
    }

    public function topRatedContent(Carbon $from, Carbon $to, ?int $partnerId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $q = Review::select('reviews.entertainment_id',
                DB::raw('AVG(reviews.rating) as avg_rating'),
                DB::raw('COUNT(*) as review_count'))
            ->whereBetween('reviews.created_at', [$from, $to])
            ->where('reviews.rating', '>', 0);
        if ($partnerId) {
            $q->join('entertainments', 'entertainments.id', '=', 'reviews.entertainment_id')
              ->where('entertainments.partner_id', $partnerId);
        }
        return $q->groupBy('reviews.entertainment_id')
            ->having('review_count', '>=', 1)
            ->orderByDesc('avg_rating')
            ->limit($limit)
            ->get();
    }

    public function recentComments(Carbon $from, Carbon $to, ?int $partnerId = null, int $limit = 10): \Illuminate\Support\Collection
    {
        $q = Review::with(['entertainment:id,name'])
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('review')
            ->where('review', '!=', '');
        if ($partnerId) {
            $q->whereHas('entertainment', fn($e) => $e->where('partner_id', $partnerId));
        }
        return $q->latest()->limit($limit)->get();
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

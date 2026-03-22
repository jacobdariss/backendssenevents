<?php

namespace Modules\Analytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Frontend\Models\PayPerView;
use Modules\Frontend\Models\PayperviewTransaction;
use Modules\Subscriptions\Models\Subscription;
use Modules\Partner\Models\Partner;
use Modules\Entertainment\Models\Entertainment;
use Modules\Episode\Models\Episode;
use Modules\Video\Models\Video;

class FinanceService
{
    // ─── Période ────────────────────────────────────────────────────────────
    public function getPeriodDates(string $period = '30d'): array
    {
        return match ($period) {
            '7d'    => [Carbon::now()->subDays(7)->startOfDay(),  Carbon::now()->endOfDay()],
            'month' => [Carbon::now()->startOfMonth(),            Carbon::now()->endOfDay()],
            'all'   => [Carbon::createFromDate(2020, 1, 1),       Carbon::now()->endOfDay()],
            default => [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()],
        };
    }

    // ─── KPIs globaux ────────────────────────────────────────────────────────
    public function globalKpis(Carbon $from, Carbon $to): array
    {
        $ppvRevenue  = (float)(PayperviewTransaction::whereBetween('created_at', [$from, $to])->where('payment_status', 'success')->sum('amount') ?? 0);
        $ppvCount    = PayperviewTransaction::whereBetween('created_at', [$from, $to])->where('payment_status', 'success')->count();
        // Revenus abonnements : depuis subscriptions_transactions (payment_status='paid')
        $subRevenue  = (float)(DB::table('subscriptions_transactions')->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('amount') ?? 0);
        $subCount    = DB::table('subscriptions_transactions')->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->count();
        $totalRev    = $ppvRevenue + $subRevenue;

        // Période précédente pour comparaison
        $diff        = max(1, $from->diffInDays($to));
        $prevFrom    = $from->copy()->subDays($diff);
        $prevTo      = $from->copy()->subSecond();
        $prevTotal   = (float)(PayperviewTransaction::whereBetween('created_at', [$prevFrom, $prevTo])->where('payment_status', 'success')->sum('amount') ?? 0)
                     + (float)(DB::table('subscriptions_transactions')->whereBetween('created_at', [$prevFrom, $prevTo])->where('payment_status', 'paid')->sum('amount') ?? 0);

        $growth = $prevTotal > 0 ? round(($totalRev - $prevTotal) / $prevTotal * 100, 1) : 0;

        return [
            'total_revenue'   => round($totalRev, 2),
            'ppv_revenue'     => round($ppvRevenue, 2),
            'ppv_count'       => $ppvCount,
            'sub_revenue'     => round($subRevenue, 2),
            'sub_count'       => $subCount,
            'growth'          => $growth,
            'prev_total'      => round($prevTotal, 2),
            'avg_transaction' => ($ppvCount + $subCount) > 0 ? round($totalRev / ($ppvCount + $subCount), 2) : 0,
        ];
    }

    // ─── Revenus par jour (PPV + Abonnements) ────────────────────────────────
    public function revenuePerDay(Carbon $from, Carbon $to): array
    {
        $ppv = PayperviewTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success')
            ->groupBy('date')->orderBy('date')->get()
            ->keyBy('date');

        $subs = DB::table('subscriptions_transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('date')->orderBy('date')->get()
            ->keyBy('date');

        // Fusionner les deux séries sur les mêmes dates
        $dates = collect(array_unique(array_merge($ppv->keys()->toArray(), $subs->keys()->toArray())))->sort()->values();

        return [
            'labels'   => $dates->values()->toArray(),
            'ppv'      => $dates->map(fn($d) => (float)($ppv[$d]->revenue ?? 0))->values()->toArray(),
            'subs'     => $dates->map(fn($d) => (float)($subs[$d]->revenue ?? 0))->values()->toArray(),
        ];
    }

    // ─── Revenus par gateway ──────────────────────────────────────────────────
    public function revenueByGateway(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        $ppv = PayperviewTransaction::select('payment_type',
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success')
            ->groupBy('payment_type')->get()
            ->map(fn($r) => ['gateway' => $r->payment_type ?? 'Inconnu', 'transactions' => (int)$r->transactions, 'revenue' => (float)$r->revenue, 'source' => 'PPV']);

        $subs = DB::table('subscriptions_transactions')
            ->select('payment_type',
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->groupBy('payment_type')->get()
            ->map(fn($r) => ['gateway' => $r->payment_type ?? 'Inconnu', 'transactions' => (int)$r->transactions, 'revenue' => (float)$r->revenue, 'source' => 'Abonnement']);

        return collect($ppv)->merge($subs)
            ->groupBy('gateway')
            ->map(fn($rows, $g) => [
                'gateway'      => $g,
                'transactions' => $rows->sum('transactions'),
                'revenue'      => round($rows->sum('revenue'), 2),
            ])
            ->values()->sortByDesc('revenue');
    }

    // ─── Revenus par partenaire ───────────────────────────────────────────────
    public function revenueByPartner(Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        return Partner::where('status', 1)->get()->map(function($partner) use ($from, $to) {
            // PPV sur les contenus du partenaire
            $ppvRev = (float)(PayperviewTransaction::whereBetween('payperviewstransactions.created_at', [$from, $to])
                ->where('payperviewstransactions.payment_status', 'success')
                ->join('pay_per_views', 'pay_per_views.id', '=', 'payperviewstransactions.pay_per_view_id')
                ->join('entertainments', 'entertainments.id', '=', 'pay_per_views.movie_id')
                ->where('entertainments.partner_id', $partner->id)
                ->sum('payperviewstransactions.amount') ?? 0);

            $commission = $partner->commission_rate ?? 0;

            return [
                'partner'    => $partner,
                'ppv_rev'    => round($ppvRev, 2),
                'commission' => round($ppvRev * $commission / 100, 2),
                'net'        => round($ppvRev * (1 - $commission / 100), 2),
                'rate'       => $commission,
            ];
        })->sortByDesc('ppv_rev');
    }

    // ─── Top contenus PPV ────────────────────────────────────────────────────
    public function topPpvContent(Carbon $from, Carbon $to, int $limit = 10): \Illuminate\Support\Collection
    {
        return PayperviewTransaction::select(
                'pay_per_views.movie_id',
                'pay_per_views.type',
                DB::raw('COUNT(*) as purchases'),
                DB::raw('SUM(payperviewstransactions.amount) as revenue'))
            ->join('pay_per_views', 'pay_per_views.id', '=', 'payperviewstransactions.pay_per_view_id')
            ->whereBetween('payperviewstransactions.created_at', [$from, $to])
            ->where('payperviewstransactions.payment_status', 'success')
            ->groupBy('pay_per_views.movie_id', 'pay_per_views.type')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function($row) {
                $name = match($row->type) {
                    'movie', 'series' => Entertainment::find($row->movie_id)?->name ?? 'Contenu #'.$row->movie_id,
                    'episode'         => Episode::find($row->movie_id)?->name ?? 'Épisode #'.$row->movie_id,
                    'video'           => Video::find($row->movie_id)?->name ?? 'Vidéo #'.$row->movie_id,
                    default           => 'Contenu #'.$row->movie_id,
                };
                $row->content_name = $name;
                return $row;
            });
    }

    // ─── Transactions récentes ────────────────────────────────────────────────
    public function recentTransactions(Carbon $from, Carbon $to, int $limit = 15): array
    {
        $result = [];

        $ppvList = PayperviewTransaction::whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'success')
            ->latest()->limit($limit)->get();

        foreach ($ppvList as $t) {
            $result[] = [
                'date'           => $t->created_at->format('Y-m-d H:i:s'),
                'type'           => 'PPV',
                'amount'         => $t->amount,
                'gateway'        => $t->payment_type ?? '—',
                'status'         => $t->payment_status,
                'transaction_id' => $t->transaction_id ?? '—',
            ];
        }

        $subsList = DB::table('subscriptions_transactions')
            ->whereBetween('created_at', [$from, $to])
            ->where('payment_status', 'paid')
            ->latest()->limit($limit)->get();

        foreach ($subsList as $s) {
            $result[] = [
                'date'           => $s->created_at,
                'type'           => 'Abonnement',
                'amount'         => $s->amount,
                'gateway'        => $s->payment_type ?? '—',
                'status'         => $s->payment_status,
                'transaction_id' => $s->transaction_id ?? '—',
            ];
        }

        usort($result, fn($a, $b) => strcmp($b['date'], $a['date']));

        return array_slice($result, 0, $limit);
    }

    // ─── Abonnements détaillés ────────────────────────────────────────────────
    public function subscriptionDetails(Carbon $from, Carbon $to): array
    {
        $new     = DB::table('subscriptions_transactions')->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->count();
        $active  = Subscription::where('status', 'active')->count();
        $expired = Subscription::where('status', 'expired')->count();
        $revenue = (float)(DB::table('subscriptions_transactions')->whereBetween('created_at', [$from, $to])->where('payment_status', 'paid')->sum('amount') ?? 0);

        $byPlan = Subscription::select('name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->where('status', 'active')
            ->groupBy('name')->orderByDesc('revenue')->get();

        $churn = ($active + $expired) > 0 ? round($expired / ($active + $expired) * 100, 1) : 0;

        return [
            'new'     => $new,
            'active'  => $active,
            'expired' => $expired,
            'revenue' => round($revenue, 2),
            'churn'   => $churn,
            'by_plan' => $byPlan,
        ];
    }
}

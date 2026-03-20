<?php

namespace Modules\Analytics\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Analytics\Services\FinanceService;
use Modules\Partner\Models\Partner;
use League\Csv\Writer;

class ExportController extends Controller
{
    protected function analytics(): AnalyticsService
    {
        return app(AnalyticsService::class);
    }

    protected function finance(): FinanceService
    {
        return app(FinanceService::class);
    }

    // ── Export Analytics ─────────────────────────────────────────────────────
    public function analyticsExport(Request $request)
    {
        $period    = $request->get('period', '30d');
        $partnerId = $request->get('partner_id');
        [$from, $to] = $this->analytics()->getPeriodDates($period);

        $csv = Writer::createFromString();
        $csv->setDelimiter(',');

        // ─ Vue d'ensemble ─
        $csv->insertOne(['=== RAPPORT ANALYTICS SEN-EVENTS ===']);
        $csv->insertOne(['Période', $from->format('d/m/Y') . ' → ' . $to->format('d/m/Y')]);
        $csv->insertOne(['Généré le', now()->format('d/m/Y H:i')]);
        $csv->insertOne([]);

        // ─ KPIs ─
        $stats = $this->analytics()->globalStats($from, $to);
        $csv->insertOne(['=== KPIs ===']);
        $csv->insertOne(['Vues totales', $stats['total_views']]);
        $csv->insertOne(['Temps de visionnage (heures)', $stats['watch_time']['hours']]);
        $csv->insertOne(['Temps de visionnage (minutes)', $stats['watch_time']['minutes']]);
        $csv->insertOne(['Spectateurs uniques', $stats['unique_viewers']]);
        $csv->insertOne(['Revenus PPV (FCFA)', $stats['ppv_revenue']['total']]);
        $csv->insertOne([]);

        // ─ Vues par jour ─
        $csv->insertOne(['=== VUES PAR JOUR ===']);
        $csv->insertOne(['Date', 'Vues']);
        foreach ($this->analytics()->viewsPerDay($from, $to, $partnerId) as $row) {
            $csv->insertOne([$row->date, $row->views]);
        }
        $csv->insertOne([]);

        // ─ Par device ─
        $csv->insertOne(['=== PAR APPAREIL ===']);
        $csv->insertOne(['Appareil', 'Vues']);
        foreach ($this->analytics()->viewsByDevice($from, $to, $partnerId) as $row) {
            $csv->insertOne([$row->device_type ?? 'Inconnu', $row->views]);
        }
        $csv->insertOne([]);

        // ─ Par plateforme ─
        $csv->insertOne(['=== PAR PLATEFORME ===']);
        $csv->insertOne(['Plateforme', 'Vues']);
        foreach ($this->analytics()->viewsByPlatform($from, $to, $partnerId) as $row) {
            $csv->insertOne([$row->platform ?? 'Inconnu', $row->views]);
        }
        $csv->insertOne([]);

        // ─ Top contenus ─
        $csv->insertOne(['=== TOP CONTENUS ===']);
        $csv->insertOne(['Contenu', 'Type', 'Vues', 'Watch time (min)']);
        foreach ($this->analytics()->topContent($from, $to, $partnerId) as $row) {
            $csv->insertOne([
                $row->content_name ?? 'Contenu #' . $row->entertainment_id,
                $row->content_type ?? '—',
                $row->views,
                round(($row->watch_time ?? 0) / 60),
            ]);
        }
        $csv->insertOne([]);

        // ─ Likes ─
        $likes = $this->analytics()->likesStats($from, $to, $partnerId);
        $csv->insertOne(['=== LIKES / DISLIKES ===']);
        $csv->insertOne(['Likes', $likes['likes']]);
        $csv->insertOne(['Dislikes', $likes['dislikes']]);
        $csv->insertOne(['Taux de like', $likes['like_rate'] . '%']);
        $csv->insertOne([]);

        // ─ Notations ─
        $ratings = $this->analytics()->ratingsStats($from, $to, $partnerId);
        $csv->insertOne(['=== NOTATIONS ===']);
        $csv->insertOne(['Note moyenne', $ratings['average'] . ' / 5']);
        $csv->insertOne(['Nombre d\'avis', $ratings['total']]);
        foreach ($ratings['distribution'] as $star => $count) {
            $csv->insertOne([$star . ' étoile(s)', $count]);
        }

        $filename = 'analytics_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        return response((string) $csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ── Export Finance ────────────────────────────────────────────────────────
    public function financeExport(Request $request)
    {
        $period = $request->get('period', '30d');
        [$from, $to] = $this->finance()->getPeriodDates($period);

        $csv = Writer::createFromString();
        $csv->setDelimiter(',');

        // ─ En-tête ─
        $csv->insertOne(['=== RAPPORT FINANCE SEN-EVENTS ===']);
        $csv->insertOne(['Période', $from->format('d/m/Y') . ' → ' . $to->format('d/m/Y')]);
        $csv->insertOne(['Généré le', now()->format('d/m/Y H:i')]);
        $csv->insertOne([]);

        // ─ KPIs ─
        $kpis = $this->finance()->globalKpis($from, $to);
        $csv->insertOne(['=== KPIs FINANCIERS ===']);
        $csv->insertOne(['Revenus totaux (FCFA)',       $kpis['total_revenue']]);
        $csv->insertOne(['Revenus PPV (FCFA)',           $kpis['ppv_revenue']]);
        $csv->insertOne(['Transactions PPV',             $kpis['ppv_count']]);
        $csv->insertOne(['Revenus abonnements (FCFA)',   $kpis['sub_revenue']]);
        $csv->insertOne(['Abonnés actifs',               $kpis['sub_count']]);
        $csv->insertOne(['Panier moyen (FCFA)',          $kpis['avg_transaction']]);
        $csv->insertOne(['Croissance vs période préc.', $kpis['growth'] . '%']);
        $csv->insertOne([]);

        // ─ Revenus par jour ─
        $revenuePerDay = $this->finance()->revenuePerDay($from, $to);
        $csv->insertOne(['=== REVENUS PAR JOUR ===']);
        $csv->insertOne(['Date', 'Revenus PPV (FCFA)', 'Revenus Abonnements (FCFA)', 'Total']);
        foreach ($revenuePerDay['labels'] as $i => $date) {
            $ppv  = $revenuePerDay['ppv'][$i]  ?? 0;
            $subs = $revenuePerDay['subs'][$i] ?? 0;
            $csv->insertOne([$date, $ppv, $subs, $ppv + $subs]);
        }
        $csv->insertOne([]);

        // ─ Par gateway ─
        $csv->insertOne(['=== PASSERELLES DE PAIEMENT ===']);
        $csv->insertOne(['Gateway', 'Transactions', 'Revenus (FCFA)']);
        foreach ($this->finance()->revenueByGateway($from, $to) as $row) {
            $csv->insertOne([$row['gateway'], $row['transactions'], $row['revenue']]);
        }
        $csv->insertOne([]);

        // ─ Revenus par partenaire ─
        $csv->insertOne(['=== REVENUS PAR PARTENAIRE ===']);
        $csv->insertOne(['Partenaire', 'Commission (%)', 'Revenus PPV (FCFA)', 'Commission (FCFA)', 'Net plateforme (FCFA)']);
        foreach ($this->finance()->revenueByPartner($from, $to) as $row) {
            $csv->insertOne([
                $row['partner']->name,
                $row['rate'],
                $row['ppv_rev'],
                $row['commission'],
                $row['net'],
            ]);
        }
        $csv->insertOne([]);

        // ─ Top contenus PPV ─
        $csv->insertOne(['=== TOP CONTENUS PPV ===']);
        $csv->insertOne(['Contenu', 'Type', 'Achats', 'Revenus (FCFA)']);
        foreach ($this->finance()->topPpvContent($from, $to) as $row) {
            $csv->insertOne([$row->content_name, $row->type, $row->purchases, $row->revenue]);
        }
        $csv->insertOne([]);

        // ─ Abonnements ─
        $subs = $this->finance()->subscriptionDetails($from, $to);
        $csv->insertOne(['=== ABONNEMENTS ===']);
        $csv->insertOne(['Nouveaux abonnés', $subs['new']]);
        $csv->insertOne(['Abonnés actifs', $subs['active']]);
        $csv->insertOne(['Abonnés expirés', $subs['expired']]);
        $csv->insertOne(['Taux de résiliation', $subs['churn'] . '%']);
        $csv->insertOne(['Revenus abonnements (FCFA)', $subs['revenue']]);
        $csv->insertOne([]);
        $csv->insertOne(['Plan', 'Abonnés', 'Revenus (FCFA)']);
        foreach ($subs['by_plan'] as $plan) {
            $csv->insertOne([$plan->name ?? '—', $plan->count, $plan->revenue]);
        }
        $csv->insertOne([]);

        // ─ Transactions récentes ─
        $csv->insertOne(['=== TRANSACTIONS RÉCENTES ===']);
        $csv->insertOne(['Date', 'Type', 'Gateway', 'Montant (FCFA)', 'Statut', 'ID Transaction']);
        foreach ($this->finance()->recentTransactions($from, $to, 50) as $tx) {
            $csv->insertOne([
                $tx['date'],
                $tx['type'],
                $tx['gateway'],
                $tx['amount'],
                $tx['status'],
                $tx['transaction_id'],
            ]);
        }

        $filename = 'finance_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        return response((string) $csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

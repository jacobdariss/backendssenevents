@extends('backend.layouts.app', ['isBanner' => false])

@section('title') {{ __('messages.dashboard') }} @endsection

@push('after-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.11.0/css/flag-icons.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.dash-kpi { border-radius: 12px; padding: 20px 24px; display: flex; align-items: center; gap: 16px; }
.dash-kpi .kpi-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dash-kpi .kpi-val { font-size: 1.7rem; font-weight: 800; line-height: 1; }
.dash-kpi .kpi-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; opacity: .65; margin-top: 4px; }
.dash-kpi .kpi-sub { font-size: .75rem; margin-top: 6px; }
.shortcut-card { border-radius: 12px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; text-decoration: none; transition: transform .15s, box-shadow .15s; cursor: pointer; }
.shortcut-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.18); text-decoration: none; }
.shortcut-card .sc-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.shortcut-card .sc-label { font-size: .8rem; opacity: .7; }
.shortcut-card .sc-title { font-weight: 700; font-size: .95rem; }
.section-title { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; opacity: .5; font-weight: 700; margin-bottom: 12px; }
.badge-pending { background: rgba(255,160,0,.15); color: #ffa000; border: 1px solid rgba(255,160,0,.3); font-size: .7rem; padding: 2px 8px; border-radius: 20px; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">{{ __('messages.dashboard') }}</h4>
        <small class="text-muted">{{ now()->translatedFormat('l d F Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('backend.analytics.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="ph ph-chart-line me-1"></i>Analytics
        </a>
        <a href="{{ route('backend.finance.index') }}" class="btn btn-sm btn-outline-success">
            <i class="ph ph-currency-circle-dollar me-1"></i>Finance
        </a>
    </div>
</div>

{{-- ── KPIs ──────────────────────────────────────────────────────────────── --}}
<p class="section-title">Indicateurs clés</p>
<div class="row g-3 mb-4">

    {{-- Revenus totaux --}}
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="dash-kpi">
                <div class="kpi-icon" style="background:rgba(34,197,94,.15)">
                    <i class="ph ph-currency-circle-dollar fs-4" style="color:#22c55e"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ number_format($total_revenue, 0, ',', ' ') }}</div>
                    <div class="kpi-label">Revenus totaux</div>
                    <div class="kpi-sub">
                        <span class="text-muted">PPV {{ number_format($rent_revenue,0,',',' ') }}</span>
                        &nbsp;·&nbsp;
                        <span class="text-muted">Abo {{ number_format($subscription_revenue,0,',',' ') }}</span>
                        <span class="ms-1 text-muted" style="font-size:.7rem">FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Utilisateurs --}}
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="dash-kpi">
                <div class="kpi-icon" style="background:rgba(99,102,241,.15)">
                    <i class="ph ph-users fs-4" style="color:#6366f1"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ number_format($totalusers) }}</div>
                    <div class="kpi-label">Utilisateurs</div>
                    <div class="kpi-sub">
                        <span class="text-success">{{ number_format($activeusers) }} actifs</span>
                        &nbsp;·&nbsp;
                        <span class="text-primary">{{ number_format($totalSubscribers) }} abonnés</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vues --}}
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="dash-kpi">
                <div class="kpi-icon" style="background:rgba(14,165,233,.15)">
                    <i class="ph ph-play-circle fs-4" style="color:#0ea5e9"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ number_format($viewsToday) }}</div>
                    <div class="kpi-label">Vues aujourd'hui</div>
                    <div class="kpi-sub">
                        <span class="text-muted">{{ number_format($viewsThisMonth) }} ce mois</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenus --}}
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="dash-kpi">
                <div class="kpi-icon" style="background:rgba(249,115,22,.15)">
                    <i class="ph ph-film-slate fs-4" style="color:#f97316"></i>
                </div>
                <div>
                    <div class="kpi-val">{{ number_format($totalmovies + $totaltvshow + $totalvideo) }}</div>
                    <div class="kpi-label">Contenus actifs</div>
                    <div class="kpi-sub">
                        <span class="text-muted">{{ $totalmovies }} Emissions · {{ $totaltvshow }} Séries · {{ $totalvideo }} Vidéos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Raccourcis ───────────────────────────────────────────────────────── --}}
<p class="section-title">Accès rapides</p>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.analytics.index') }}" class="card shortcut-card">
            <div class="sc-icon" style="background:rgba(99,102,241,.15)">
                <i class="ph ph-chart-line fs-5" style="color:#6366f1"></i>
            </div>
            <div>
                <div class="sc-label">Module</div>
                <div class="sc-title">Analytics</div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.finance.index') }}" class="card shortcut-card">
            <div class="sc-icon" style="background:rgba(34,197,94,.15)">
                <i class="ph ph-currency-circle-dollar fs-5" style="color:#22c55e"></i>
            </div>
            <div>
                <div class="sc-label">Module</div>
                <div class="sc-title">Finance</div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.partners.index') }}" class="card shortcut-card">
            <div class="sc-icon" style="background:rgba(249,115,22,.15)">
                <i class="ph ph-handshake fs-5" style="color:#f97316"></i>
            </div>
            <div>
                <div class="sc-label">{{ $activePartners }} actifs / {{ $totalPartners }}</div>
                <div class="sc-title">Partenaires</div>
            </div>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.partner-validation.index') }}" class="card shortcut-card">
            <div class="sc-icon" style="background:rgba(255,160,0,.15)">
                <i class="ph ph-clock fs-5" style="color:#ffa000"></i>
            </div>
            <div>
                <div class="sc-label">Contenus</div>
                <div class="sc-title d-flex align-items-center gap-2">
                    Validation
                    @if($pendingContents > 0)
                    <span class="badge-pending">{{ $pendingContents }}</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

</div>

{{-- ── Graphique + Top contenus ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Courbe revenus 30 jours --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>Revenus — 30 derniers jours</h6>
                <a href="{{ route('backend.finance.index') }}" class="small text-muted">Voir Finance →</a>
            </div>
            <div class="card-body p-3" style="position:relative;height:220px">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top 5 contenus --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ph ph-trophy me-2"></i>Top contenus — 30j</h6>
                <a href="{{ route('backend.analytics.index') }}" class="small text-muted">Voir Analytics →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <tbody>
                        @forelse($topContent30d as $i => $row)
                        <tr>
                            <td class="ps-3 text-muted" style="width:30px">{{ $i+1 }}</td>
                            <td class="fw-semibold">{{ Str::limit($row->content_name, 30) }}</td>
                            <td class="text-end pe-3">
                                <span class="fw-bold">{{ number_format($row->views) }}</span>
                                <span class="text-muted ms-1">vues</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucune vue enregistrée</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Abonnements + Alertes ────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Derniers abonnements --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ph ph-identification-badge me-2"></i>Derniers abonnements</h6>
                <a href="{{ route('backend.finance.index') }}" class="small text-muted">Voir Finance →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th class="ps-3">Utilisateur</th>
                            <th>Plan</th>
                            <th class="text-end pe-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSubs as $sub)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $sub->user?->first_name }} {{ $sub->user?->last_name }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-75">{{ $sub->name }}</span>
                            </td>
                            <td class="text-end pe-3 text-muted">{{ $sub->created_at?->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucun abonnement récent</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Alertes --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="ph ph-bell me-2"></i>Alertes & état</h6>
            </div>
            <div class="card-body d-flex flex-column gap-3 py-3">

                @if($pendingContents > 0)
                <a href="{{ route('backend.partner-validation.index') }}" class="d-flex align-items-center gap-3 text-decoration-none p-2 rounded" style="background:rgba(255,160,0,.08);border:1px solid rgba(255,160,0,.2)">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,160,0,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ph ph-clock" style="color:#ffa000"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#ffa000">{{ $pendingContents }} contenu(s) en attente</div>
                        <div class="small text-muted">Validation partenaire requise</div>
                    </div>
                    <i class="ph ph-arrow-right ms-auto text-muted"></i>
                </a>
                @else
                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2)">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(34,197,94,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ph ph-check-circle" style="color:#22c55e"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#22c55e">Aucun contenu en attente</div>
                        <div class="small text-muted">Tout est validé</div>
                    </div>
                </div>
                @endif

                @if($totalsoontoexpire > 0)
                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2)">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(239,68,68,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ph ph-warning" style="color:#ef4444"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#ef4444">{{ $totalsoontoexpire }} abonnement(s) expirent bientôt</div>
                        <div class="small text-muted">Dans les 7 prochains jours</div>
                    </div>
                </div>
                @endif

                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2)">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(99,102,241,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ph ph-hard-drive" style="color:#6366f1"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#6366f1">Stockage utilisé</div>
                        <div class="small text-muted">{{ $totalUsageFormatted }}</div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:rgba(14,165,233,.08);border:1px solid rgba(14,165,233,.2)">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(14,165,233,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="ph ph-star" style="color:#0ea5e9"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#0ea5e9">{{ number_format($totalreview) }} avis</div>
                        <div class="small text-muted">Total notations utilisateurs</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ── Statistiques globales (petits compteurs) ─────────────────────────── --}}
<p class="section-title">Statistiques globales</p>
<div class="row g-3">
    @php
    $stats = [
        ['icon'=>'ph-users','color'=>'#6366f1','bg'=>'rgba(99,102,241,.12)','val'=>number_format($totalusers),'label'=>'Utilisateurs total'],
        ['icon'=>'ph-identification-badge','color'=>'#22c55e','bg'=>'rgba(34,197,94,.12)','val'=>number_format($totalSubscribers),'label'=>'Abonnés actifs'],
        ['icon'=>'ph-film-strip','color'=>'#f97316','bg'=>'rgba(249,115,22,.12)','val'=>number_format($totalmovies),'label'=>'Emissions'],
        ['icon'=>'ph-television','color'=>'#a855f7','bg'=>'rgba(168,85,247,.12)','val'=>number_format($totaltvshow),'label'=>'Séries TV'],
        ['icon'=>'ph-video','color'=>'#0ea5e9','bg'=>'rgba(14,165,233,.12)','val'=>number_format($totalvideo),'label'=>'Vidéos'],
        ['icon'=>'ph-handshake','color'=>'#ffa000','bg'=>'rgba(255,160,0,.12)','val'=>$activePartners.' / '.$totalPartners,'label'=>'Partenaires actifs'],
        ['icon'=>'ph-download','color'=>'#14b8a6','bg'=>'rgba(20,184,166,.12)','val'=>number_format($totalDownloads),'label'=>'Téléchargements'],
        ['icon'=>'ph-receipt','color'=>'#ef4444','bg'=>'rgba(239,68,68,.12)','val'=>number_format($totalTransactions),'label'=>'Transactions'],
    ];
    @endphp
    @foreach($stats as $s)
    <div class="col-6 col-sm-4 col-md-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $s['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="ph {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:1.2rem"></i>
                </div>
                <div>
                    <div class="fw-bold">{{ $s['val'] }}</div>
                    <div class="small text-muted">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('after-scripts')
<script>
// ── Graphique revenus 30 jours ────────────────────────────────────────────
(function() {
    const subData  = @json($revenuePerDay30->pluck('revenue', 'date'));
    const ppvData  = @json($ppvPerDay30->pluck('revenue', 'date'));
    const allDates = [...new Set([...Object.keys(subData), ...Object.keys(ppvData)])].sort();

    if (!allDates.length) return;

    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: allDates,
            datasets: [
                {
                    label: 'Abonnements',
                    data: allDates.map(d => subData[d] || 0),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                },
                {
                    label: 'PPV',
                    data: allDates.map(d => ppvData[d] || 0),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { ticks: { font: { size: 10 }, maxTicksLimit: 8 }, grid: { display: false } },
                y: { ticks: { font: { size: 10 } }, beginAtZero: true }
            }
        }
    });
})();
</script>
@endpush

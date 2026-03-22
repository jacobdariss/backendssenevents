@extends('backend.layouts.app', ['isBanner' => false])

@section('title') {{ __('messages.dashboard') }} @endsection

@push('after-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.11.0/css/flag-icons.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.section-title { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .08em; color: var(--color-text-secondary); margin-bottom: 10px; }
/* KPI cards */
.kpi { border-radius: 14px; padding: 18px 20px; position: relative; overflow: hidden; }
.kpi-purple { background: rgba(174,169,236,0.12); }
.kpi-teal   { background: rgba(29,158,117,0.12); }
.kpi-blue   { background: rgba(55,138,221,0.12); }
.kpi-amber  { background: rgba(239,159,39,0.12); }
.kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.kpi-purple .kpi-icon { background: rgba(174,169,236,0.25); }
.kpi-teal   .kpi-icon { background: rgba(29,158,117,0.25); }
.kpi-blue   .kpi-icon { background: rgba(55,138,221,0.25); }
.kpi-amber  .kpi-icon { background: rgba(239,159,39,0.25); }
.kpi-icon i { font-size: 16px; }
.kpi-purple .kpi-icon i { color: #AFA9EC; }
.kpi-teal   .kpi-icon i { color: #5DCAA5; }
.kpi-blue   .kpi-icon i { color: #85B7EB; }
.kpi-amber  .kpi-icon i { color: #EF9F27; }
.kpi-val { font-size: 26px; font-weight: 500; line-height: 1; margin-bottom: 4px; }
.kpi-purple .kpi-val { color: #AFA9EC; }
.kpi-teal   .kpi-val { color: #5DCAA5; }
.kpi-blue   .kpi-val { color: #85B7EB; }
.kpi-amber  .kpi-val { color: #EF9F27; }
.kpi-lbl { font-size: 12px; margin-bottom: 6px; }
.kpi-purple .kpi-lbl { color: #7F77DD; }
.kpi-teal   .kpi-lbl { color: #1D9E75; }
.kpi-blue   .kpi-lbl { color: #378ADD; }
.kpi-amber  .kpi-lbl { color: #BA7517; }
.kpi-sub { font-size: 11px; }
.kpi-purple .kpi-sub { color: rgba(174,169,236,0.6); }
.kpi-teal   .kpi-sub { color: rgba(29,158,117,0.7); }
.kpi-blue   .kpi-sub { color: rgba(55,138,221,0.7); }
.kpi-amber  .kpi-sub { color: rgba(239,159,39,0.6); }
.kpi-deco { position: absolute; right: -10px; bottom: -10px; width: 64px; height: 64px; border-radius: 50%; opacity: .18; }
.kpi-purple .kpi-deco { background: #534AB7; }
.kpi-teal   .kpi-deco { background: #0F6E56; }
.kpi-blue   .kpi-deco { background: #185FA5; }
.kpi-amber  .kpi-deco { background: #854F0B; }
/* Shortcut cards */
.sc { border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; text-decoration: none; transition: opacity .15s; }
.sc:hover { opacity: .85; text-decoration: none; }
.sc-purple { background: rgba(174,169,236,0.12); }
.sc-teal   { background: rgba(29,158,117,0.12); }
.sc-coral  { background: rgba(216,90,48,0.12); }
.sc-amber  { background: rgba(239,159,39,0.12); }
.sc .sc-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sc-purple .sc-icon { background: rgba(174,169,236,0.25); }
.sc-teal   .sc-icon { background: rgba(29,158,117,0.25); }
.sc-coral  .sc-icon { background: rgba(216,90,48,0.25); }
.sc-amber  .sc-icon { background: rgba(239,159,39,0.25); }
.sc .sc-icon i { font-size: 18px; }
.sc-purple .sc-icon i { color: #AFA9EC; }
.sc-teal   .sc-icon i { color: #5DCAA5; }
.sc-coral  .sc-icon i { color: #F0997B; }
.sc-amber  .sc-icon i { color: #EF9F27; }
.sc .sc-meta  { font-size: 11px; margin-bottom: 2px; }
.sc .sc-ttl   { font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; }
.sc-purple .sc-meta { color: rgba(174,169,236,0.6); }
.sc-purple .sc-ttl  { color: #AFA9EC; }
.sc-teal   .sc-meta { color: rgba(29,158,117,0.7); }
.sc-teal   .sc-ttl  { color: #5DCAA5; }
.sc-coral  .sc-meta { color: rgba(216,90,48,0.7); }
.sc-coral  .sc-ttl  { color: #F0997B; }
.sc-amber  .sc-meta { color: rgba(239,159,39,0.6); }
.sc-amber  .sc-ttl  { color: #EF9F27; }
.sc-arrow { margin-left: auto; opacity: .35; font-size: 14px; }
.sc-purple .sc-arrow { color: rgba(174,169,236,0.4); }
.sc-teal   .sc-arrow { color: rgba(29,158,117,0.4); }
.sc-coral  .sc-arrow { color: rgba(216,90,48,0.4); }
.sc-amber  .sc-arrow { color: rgba(239,159,39,0.4); }
.sc-badge { background: #D85A30; color: #fff; font-size: 10px; font-weight: 500; border-radius: 20px; padding: 2px 7px; }
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

    <div class="col-6 col-md-3">
        <div class="kpi kpi-purple">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-currency-circle-dollar"></i></div>
            <div class="kpi-val">{{ number_format($total_revenue, 0, ',', ' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">Revenus totaux</div>
            <div class="kpi-sub">PPV {{ number_format($rent_revenue,0,',',' ') }} XOF · Abo {{ number_format($subscription_revenue,0,',',' ') }} XOF</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-teal">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-users"></i></div>
            <div class="kpi-val">{{ number_format($totalusers) }}</div>
            <div class="kpi-lbl">Utilisateurs</div>
            <div class="kpi-sub">{{ number_format($activeusers) }} actifs · {{ number_format($totalSubscribers) }} abonnés</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-blue">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-play-circle"></i></div>
            <div class="kpi-val">{{ number_format($viewsToday) }}</div>
            <div class="kpi-lbl">Vues aujourd'hui</div>
            <div class="kpi-sub">{{ number_format($viewsThisMonth) }} ce mois</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-amber">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-film-slate"></i></div>
            <div class="kpi-val">{{ number_format($totalmovies + $totaltvshow + $totalvideo) }}</div>
            <div class="kpi-lbl">Contenus actifs</div>
            <div class="kpi-sub">{{ $totalmovies }} Émissions · {{ $totaltvshow }} Séries · {{ $totalvideo }} Vidéos</div>
        </div>
    </div>

</div>

{{-- ── Raccourcis ───────────────────────────────────────────────────────── --}}
<p class="section-title">Accès rapides</p>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.analytics.index') }}" class="sc sc-purple">
            <div class="sc-icon"><i class="ph ph-chart-line"></i></div>
            <div>
                <div class="sc-meta">Module</div>
                <div class="sc-ttl">Analytics</div>
            </div>
            <i class="ph ph-caret-right sc-arrow"></i>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.finance.index') }}" class="sc sc-teal">
            <div class="sc-icon"><i class="ph ph-currency-circle-dollar"></i></div>
            <div>
                <div class="sc-meta">Module</div>
                <div class="sc-ttl">Finance</div>
            </div>
            <i class="ph ph-caret-right sc-arrow"></i>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.partners.index') }}" class="sc sc-coral">
            <div class="sc-icon"><i class="ph ph-handshake"></i></div>
            <div>
                <div class="sc-meta">{{ $activePartners }} actifs / {{ $totalPartners }}</div>
                <div class="sc-ttl">Partenaires</div>
            </div>
            <i class="ph ph-caret-right sc-arrow"></i>
        </a>
    </div>

    <div class="col-6 col-md-3">
        <a href="{{ route('backend.partner-validation.index') }}" class="sc sc-amber">
            <div class="sc-icon"><i class="ph ph-clock"></i></div>
            <div style="flex:1">
                <div class="sc-meta">Contenus</div>
                <div class="sc-ttl">
                    Validation
                    @if($pendingContents > 0)
                    <span class="sc-badge">{{ $pendingContents }}</span>
                    @endif
                </div>
            </div>
            <i class="ph ph-caret-right sc-arrow"></i>
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
                y: { ticks: { font: { size: 10 }, callback: v => v.toLocaleString() + ' XOF' }, beginAtZero: true }
            }
        }
    });
})();
</script>
@endpush

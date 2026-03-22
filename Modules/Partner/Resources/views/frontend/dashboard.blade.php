@extends('backend.layouts.app')
@section('title') {{ __('sidebar.dashboard') }} — {{ $partner->name }} @endsection

@push('after-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.11.0/css/flag-icons.min.css"/>
<style>
.section-title { font-size: 11px; font-weight: 500; text-transform: uppercase; letter-spacing: .08em; color: var(--color-text-secondary, #888); margin-bottom: 10px; }
/* KPI */
.kpi { border-radius: 14px; padding: 18px 20px; position: relative; overflow: hidden; }
.kpi-purple { background: rgba(174,169,236,0.12); }
.kpi-teal   { background: rgba(29,158,117,0.12); }
.kpi-blue   { background: rgba(55,138,221,0.12); }
.kpi-amber  { background: rgba(239,159,39,0.12); }
.kpi-coral  { background: rgba(216,90,48,0.12); }
.kpi .kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.kpi-purple .kpi-icon { background: rgba(174,169,236,0.25); }
.kpi-teal   .kpi-icon { background: rgba(29,158,117,0.25); }
.kpi-blue   .kpi-icon { background: rgba(55,138,221,0.25); }
.kpi-amber  .kpi-icon { background: rgba(239,159,39,0.25); }
.kpi-coral  .kpi-icon { background: rgba(216,90,48,0.25); }
.kpi .kpi-icon i { font-size: 16px; }
.kpi-purple .kpi-icon i { color: #AFA9EC; }
.kpi-teal   .kpi-icon i { color: #5DCAA5; }
.kpi-blue   .kpi-icon i { color: #85B7EB; }
.kpi-amber  .kpi-icon i { color: #EF9F27; }
.kpi-coral  .kpi-icon i { color: #F0997B; }
.kpi .kpi-val { font-size: 26px; font-weight: 500; line-height: 1; margin-bottom: 4px; }
.kpi-purple .kpi-val { color: #AFA9EC; }
.kpi-teal   .kpi-val { color: #5DCAA5; }
.kpi-blue   .kpi-val { color: #85B7EB; }
.kpi-amber  .kpi-val { color: #EF9F27; }
.kpi-coral  .kpi-val { color: #F0997B; }
.kpi .kpi-lbl { font-size: 12px; margin-bottom: 4px; }
.kpi-purple .kpi-lbl { color: #7F77DD; }
.kpi-teal   .kpi-lbl { color: #1D9E75; }
.kpi-blue   .kpi-lbl { color: #378ADD; }
.kpi-amber  .kpi-lbl { color: #BA7517; }
.kpi-coral  .kpi-lbl { color: #D85A30; }
.kpi .kpi-sub { font-size: 11px; }
.kpi-purple .kpi-sub { color: rgba(174,169,236,0.6); }
.kpi-teal   .kpi-sub { color: rgba(29,158,117,0.7); }
.kpi-blue   .kpi-sub { color: rgba(55,138,221,0.7); }
.kpi-amber  .kpi-sub { color: rgba(239,159,39,0.6); }
.kpi-coral  .kpi-sub { color: rgba(216,90,48,0.6); }
.kpi .kpi-deco { position: absolute; right: -10px; bottom: -10px; width: 64px; height: 64px; border-radius: 50%; opacity: .1; }
.kpi-purple .kpi-deco { background: #7F77DD; }
.kpi-teal   .kpi-deco { background: #1D9E75; }
.kpi-blue   .kpi-deco { background: #378ADD; }
.kpi-amber  .kpi-deco { background: #BA7517; }
.kpi-coral  .kpi-deco { background: #D85A30; }
/* Shortcuts */
.sc { border-radius: 14px; padding: 16px 18px; display: flex; align-items: center; gap: 12px; text-decoration: none; transition: opacity .15s; }
.sc:hover { opacity: .82; text-decoration: none; }
.sc-purple { background: rgba(174,169,236,0.12); }
.sc-teal   { background: rgba(29,158,117,0.12); }
.sc-blue   { background: rgba(55,138,221,0.12); }
.sc-amber  { background: rgba(239,159,39,0.12); }
.sc .sc-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sc-purple .sc-icon { background: rgba(174,169,236,0.25); }
.sc-teal   .sc-icon { background: rgba(29,158,117,0.25); }
.sc-blue   .sc-icon { background: rgba(55,138,221,0.25); }
.sc-amber  .sc-icon { background: rgba(239,159,39,0.25); }
.sc .sc-icon i { font-size: 17px; }
.sc-purple .sc-icon i { color: #AFA9EC; }
.sc-teal   .sc-icon i { color: #5DCAA5; }
.sc-blue   .sc-icon i { color: #85B7EB; }
.sc-amber  .sc-icon i { color: #EF9F27; }
.sc .sc-meta { font-size: 10px; margin-bottom: 2px; }
.sc .sc-ttl  { font-size: 13px; font-weight: 500; }
.sc-purple .sc-meta { color: rgba(174,169,236,0.6); }
.sc-purple .sc-ttl  { color: #AFA9EC; }
.sc-teal   .sc-meta { color: rgba(29,158,117,0.7); }
.sc-teal   .sc-ttl  { color: #5DCAA5; }
.sc-blue   .sc-meta { color: rgba(55,138,221,0.7); }
.sc-blue   .sc-ttl  { color: #85B7EB; }
.sc-amber  .sc-meta { color: rgba(239,159,39,0.6); }
.sc-amber  .sc-ttl  { color: #EF9F27; }
.sc .sc-arrow { margin-left: auto; opacity: .3; font-size: 13px; }
.sc-badge { background: #D85A30; color: #fff; font-size: 10px; font-weight: 500; border-radius: 20px; padding: 2px 7px; margin-left: 4px; }
</style>
@endpush

@section('content')

{{-- ── Header partenaire ────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        @if($partner->logo_url)
            @php $logoUrl = setBaseUrlWithFileName($partner->logo_url, 'image', 'partners'); @endphp
            <img src="{{ $logoUrl }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;flex-shrink:0">
        @else
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:48px;height:48px;font-size:20px;flex-shrink:0">
                {{ strtoupper(substr($partner->name,0,1)) }}
            </div>
        @endif
        <div>
            <h4 class="mb-0 fw-bold">{{ $partner->name }}</h4>
            <small class="text-muted">{{ __('partner::partner.lbl_partner') }} · Actif depuis {{ $partner->created_at?->format('d/m/Y') }}</small>
        </div>
    </div>
    <a href="{{ route('partner.analytics') }}" class="btn btn-sm btn-outline-primary">
        <i class="ph ph-chart-line me-1"></i>Analytics détaillés
    </a>
</div>

{{-- ── Quota ────────────────────────────────────────────────────────────── --}}
@if($stats['quota_max'] !== null)
@php
    $pct = min(100, round($stats['quota_used'] / max(1, $stats['quota_max']) * 100));
    $qcolor = $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success');
@endphp
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-semibold"><i class="ph ph-database me-1"></i>Quota de contenus</span>
            <span class="small {{ $pct >= 90 ? 'text-danger fw-bold' : 'text-muted' }}">
                {{ $stats['quota_used'] }} / {{ $stats['quota_max'] }}
                @if($pct >= 90) <i class="ph ph-warning ms-1"></i> @endif
            </span>
        </div>
        <div class="progress" style="height:6px">
            <div class="progress-bar bg-{{ $qcolor }}" style="width:{{ $pct }}%"></div>
        </div>
        @if($pct >= 100)
        <div class="alert alert-danger mt-2 py-1 mb-0 small"><i class="ph ph-warning me-1"></i>{{ __('partner::partner.quota_exceeded_warning') }}</div>
        @endif
    </div>
</div>
@endif

{{-- ── KPIs ─────────────────────────────────────────────────────────────── --}}
<p class="section-title">Indicateurs clés</p>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="kpi kpi-teal">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-play-circle"></i></div>
            <div class="kpi-val">{{ number_format($viewsToday) }}</div>
            <div class="kpi-lbl">Vues aujourd'hui</div>
            <div class="kpi-sub">{{ number_format($viewsThisMonth) }} ce mois · {{ number_format($viewsTotal) }} total</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-purple">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-currency-circle-dollar"></i></div>
            <div class="kpi-val">{{ number_format($ppvRevenue, 0, ',', ' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">Revenus PPV</div>
            <div class="kpi-sub">Commission {{ number_format($commission, 0, ',', ' ') }} XOF ({{ $partner->commission_rate }}%)</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-blue">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-check-circle"></i></div>
            <div class="kpi-val">{{ number_format($stats['videos_active']) }}</div>
            <div class="kpi-lbl">Contenus actifs</div>
            <div class="kpi-sub">{{ $stats['videos_inactive'] }} inactifs · {{ $stats['movies_total'] }} total</div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="kpi kpi-amber">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-clock"></i></div>
            <div class="kpi-val">{{ number_format($stats['videos_pending']) }}</div>
            <div class="kpi-lbl">En attente</div>
            <div class="kpi-sub">
                @if($stats['videos_rejected'] > 0)
                    <span style="color:#F0997B">{{ $stats['videos_rejected'] }} rejeté(s)</span>
                @else
                    Aucun rejet
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Accès rapides ────────────────────────────────────────────────────── --}}
<p class="section-title">Accès rapides</p>
<div class="row g-3 mb-4">

    @foreach([
        ['route' => 'partner.videos', 'icon' => 'ph-video', 'meta' => 'Gérer', 'title' => 'Vidéos', 'class' => 'sc-teal'],
        ['route' => 'partner.movies', 'icon' => 'ph-film-strip', 'meta' => 'Gérer', 'title' => 'Émissions', 'class' => 'sc-purple'],
        ['route' => 'partner.tvshows', 'icon' => 'ph-television', 'meta' => 'Gérer', 'title' => 'Séries TV', 'class' => 'sc-blue'],
        ['route' => 'partner.analytics', 'icon' => 'ph-chart-line', 'meta' => 'Module', 'title' => 'Analytics', 'class' => 'sc-amber'],
    ] as $sc)
    <div class="col-6 col-md-3">
        <a href="{{ route($sc['route']) }}" class="sc {{ $sc['class'] }}">
            <div class="sc-icon"><i class="ph {{ $sc['icon'] }}"></i></div>
            <div>
                <div class="sc-meta">{{ $sc['meta'] }}</div>
                <div class="sc-ttl">{{ $sc['title'] }}</div>
            </div>
            <i class="ph ph-caret-right sc-arrow"></i>
        </a>
    </div>
    @endforeach

</div>

{{-- ── Top contenus + Contenus récents ─────────────────────────────────── --}}
<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ph ph-trophy me-2"></i>Top contenus — 30 jours</h6>
                <a href="{{ route('partner.analytics') }}" class="small text-muted">Voir tout →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <tbody>
                        @forelse($topContent as $i => $row)
                        <tr>
                            <td class="ps-3 text-muted" style="width:28px">{{ $i+1 }}</td>
                            <td class="fw-semibold">{{ Str::limit($row->content_name, 28) }}</td>
                            <td class="text-end pe-3 fw-bold">{{ number_format($row->views) }} <span class="text-muted fw-normal">vues</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucune vue enregistrée</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ph ph-clock-counter-clockwise me-2"></i>Derniers contenus ajoutés</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <tbody>
                        @forelse($recentContent as $item)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ Str::limit($item->name, 28) }}</td>
                            <td>
                                @php
                                    $colors = ['approved'=>'success','pending'=>'warning','rejected'=>'danger'];
                                    $labels = ['approved'=>'Approuvé','pending'=>'En attente','rejected'=>'Rejeté'];
                                    $st = $item->approval_status ?? 'pending';
                                @endphp
                                <span class="badge bg-{{ $colors[$st] ?? 'secondary' }} bg-opacity-75">{{ $labels[$st] ?? $st }}</span>
                            </td>
                            <td class="text-end pe-3 text-muted">{{ $item->created_at?->format('d/m') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Aucun contenu</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ── Types de contenus autorisés ─────────────────────────────────────── --}}
@if($partner->allowed_content_types)
<div class="card">
    <div class="card-body py-3 d-flex align-items-center gap-3 flex-wrap">
        <span class="small text-muted fw-semibold">Types autorisés :</span>
        @foreach($partner->allowed_content_types as $type)
            <span class="badge bg-primary bg-opacity-75 px-3 py-2">{{ __('partner::partner.content_type_' . $type) }}</span>
        @endforeach
    </div>
</div>
@endif

@endsection

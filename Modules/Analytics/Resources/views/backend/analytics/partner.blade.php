@extends('backend.layouts.app')
@section('title') Analytics — {{ $partner->name }} @endsection

@push('after-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.11.0/css/flag-icons.min.css"/>
<style>
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
.kpi .kpi-deco { position: absolute; right:-10px; bottom:-10px; width:64px; height:64px; border-radius:50%; opacity:.1; }
.kpi-purple .kpi-deco { background:#7F77DD; }
.kpi-teal   .kpi-deco { background:#1D9E75; }
.kpi-blue   .kpi-deco { background:#378ADD; }
.kpi-amber  .kpi-deco { background:#BA7517; }
.kpi-coral  .kpi-deco { background:#D85A30; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="{{ route('backend.analytics.index', ['period'=>$period]) }}" class="btn btn-sm btn-secondary">
        <i class="ph ph-caret-double-left me-1"></i>{{ __('messages.back') }}
    </a>
    <h5 class="mb-0"><i class="ph ph-handshake me-2"></i>{{ $partner->name }}</h5>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="partner_id" value="{{ $partner->id }}">
            <div>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7d"    {{ $period=='7d'    ?'selected':'' }}>{{ __('analytics::analytics.last_7_days') }}</option>
                    <option value="30d"   {{ $period=='30d'   ?'selected':'' }}>{{ __('analytics::analytics.last_30_days') }}</option>
                    <option value="month" {{ $period=='month' ?'selected':'' }}>{{ __('analytics::analytics.this_month') }}</option>
                    <option value="all"   {{ $period=='all'   ?'selected':'' }}>{{ __('analytics::analytics.all_time') }}</option>
                </select>
            </div>
        </form>
    </div>
</div>

{{-- KPIs partenaire --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi kpi-blue">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-eye"></i></div>
            <div class="kpi-val">{{ number_format($stats['total_views']) }}</div>
            <div class="kpi-lbl">{{ __('analytics::analytics.total_views') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi kpi-teal">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-clock"></i></div>
            <div class="kpi-val">{{ $stats['watch_time']['hours'] }}h</div>
            <div class="kpi-lbl">{{ __('analytics::analytics.watch_time') }}</div>
            <div class="kpi-sub">{{ number_format($stats['watch_time']['minutes']) }} min</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi kpi-amber">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-currency-circle-dollar"></i></div>
            <div class="kpi-val">{{ number_format($stats['ppv_revenue']['total'],0,',',' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">{{ __('analytics::analytics.ppv_revenue') }}</div>
            @if($stats['ppv_revenue']['commission'] > 0)
            <div class="kpi-sub">Commission {{ number_format($stats['ppv_revenue']['commission'],0,',',' ') }} XOF ({{ $partner->commission_rate }}%)</div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>{{ __('analytics::analytics.views_over_time') }}</h6></div>
            <div class="card-body" style="height:200px"><canvas id="viewsChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-device-mobile me-2"></i>{{ __('analytics::analytics.by_device') }}</h6></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byDevice->count())
                    <canvas id="deviceChart" style="max-height:170px;max-width:170px"></canvas>
                @else
                    <p class="text-muted small">{{ __('messages.no_record_found') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trophy me-2"></i>{{ __('analytics::analytics.top_content') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('messages.name') }}</th>
                        <th class="text-end">{{ __('analytics::analytics.views') }}</th>
                        <th class="text-end">Watch time</th>
                    </tr></thead>
                    <tbody>
                        @forelse($topContent as $row)
                        <tr>
                            <td>{{ $row->content_name }}</td>
                            <td class="text-end fw-bold">{{ number_format($row->views) }}</td>
                            <td class="text-end text-muted small">{{ round(($row->watch_time??0)/60) }} min</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-credit-card me-2"></i>{{ __('analytics::analytics.payment_gateways') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Gateway</th>
                        <th class="text-end">{{ __('analytics::analytics.transactions') }}</th>
                        <th class="text-end">Revenus</th>
                    </tr></thead>
                    <tbody>
                        @php $totalRev = $gatewayStats->sum('revenue') ?: 1; @endphp
                        @forelse($gatewayStats as $row)
                        <tr>
                            <td><span class="badge bg-secondary text-uppercase">{{ $row['gateway'] }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($row['transactions']) }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;max-width:60px">
                                        <div class="progress-bar bg-warning" style="width:{{ round($row['revenue']/$totalRev*100) }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ number_format($row['revenue'],0,',',' ') }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pays --}}
        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-globe me-2"></i>{{ __('analytics::analytics.by_country') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('analytics::analytics.country') }}</th>
                        <th class="text-end">{{ __('analytics::analytics.views') }}</th>
                        <th style="width:100px">%</th>
                    </tr></thead>
                    <tbody>
                        @php $totalCountryViews = $byCountry->sum('views') ?: 1; @endphp
                        @forelse($byCountry as $row)
                        <tr>
                            <td>
                                <span class="fi fi-{{ strtolower($row->country_code) }} me-2" style="font-size:1.1rem;"></span>
                                {{ $row->country_code }}
                            </td>
                            <td class="text-end fw-bold">{{ number_format($row->views) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-info" style="width:{{ round($row->views/$totalCountryViews*100) }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ round($row->views/$totalCountryViews*100) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- Notations & Commentaires --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-star me-2"></i>{{ __('analytics::analytics.ratings') }}</h6></div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="display-5 fw-bold text-warning">{{ $ratingsStats['average'] }}</div>
                    <div class="text-muted small">/ 5 — {{ number_format($ratingsStats['total']) }} {{ __('analytics::analytics.reviews') }}</div>
                    <div class="mt-1">
                        @for($i=1;$i<=5;$i++)
                        <i class="ph {{ $i <= round($ratingsStats['average']) ? 'ph-star-fill text-warning' : 'ph-star text-muted' }}"></i>
                        @endfor
                    </div>
                </div>
                @foreach([5,4,3,2,1] as $star)
                @php $pct = $ratingsStats['total'] > 0 ? round($ratingsStats['distribution'][$star] / $ratingsStats['total'] * 100) : 0; @endphp
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="small text-muted" style="width:20px">{{ $star }}★</span>
                    <div class="progress flex-grow-1" style="height:6px">
                        <div class="progress-bar bg-warning" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="small text-muted" style="width:30px">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trophy me-2"></i>{{ __('analytics::analytics.top_rated') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('messages.name') }}</th>
                        <th class="text-end">Note</th>
                        <th class="text-end">Avis</th>
                    </tr></thead>
                    <tbody>
                        @forelse($topRated as $row)
                        <tr>
                            <td class="small">{{ $row->content_name }}</td>
                            <td class="text-end">
                                <span class="badge bg-warning text-dark">★ {{ number_format($row->avg_rating, 1) }}</span>
                            </td>
                            <td class="text-end text-muted small">{{ $row->review_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3 small">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-chat-dots me-2"></i>{{ __('analytics::analytics.recent_comments') }}</h6></div>
            <div class="card-body p-0" style="max-height:320px;overflow-y:auto">
                @forelse($recentComments as $comment)
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <span class="small fw-semibold">{{ $comment->entertainment?->name ?? '—' }}</span>
                        <span class="badge bg-warning text-dark ms-2 flex-shrink-0">★ {{ $comment->rating }}</span>
                    </div>
                    <p class="small text-muted mb-1">{{ Str::limit($comment->review, 100) }}</p>
                    <span class="text-muted" style="font-size:11px">{{ $comment->created_at?->diffForHumans() }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">{{ __('messages.no_record_found') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Likes / Dislikes --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-thumbs-up me-2"></i>{{ __('analytics::analytics.likes_dislikes') }}</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center mb-3">
                    <div>
                        <div class="fs-3 fw-bold text-success">{{ number_format($likesStats['likes']) }}</div>
                        <div class="text-muted small"><i class="ph ph-thumbs-up me-1"></i>{{ __('analytics::analytics.likes') }}</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-danger">{{ number_format($likesStats['dislikes']) }}</div>
                        <div class="text-muted small"><i class="ph ph-thumbs-down me-1"></i>{{ __('analytics::analytics.dislikes') }}</div>
                    </div>
                    <div class="border-end"></div>
                    <div>
                        <div class="fs-3 fw-bold text-primary">{{ $likesStats['like_rate'] }}%</div>
                        <div class="text-muted small">{{ __('analytics::analytics.like_rate') }}</div>
                    </div>
                </div>
                <div class="progress" style="height:8px">
                    <div class="progress-bar bg-success" style="width:{{ $likesStats['like_rate'] }}%"></div>
                    <div class="progress-bar bg-danger" style="width:{{ 100 - $likesStats['like_rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>{{ __('analytics::analytics.likes_over_time') }}</h6></div>
            <div class="card-body"><canvas id="likesChart" height="90"></canvas></div>
        </div>
    </div>
</div>

{{-- Top contenus likés --}}
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="ph ph-heart me-2"></i>{{ __('analytics::analytics.top_liked') }}</h6></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>{{ __('messages.name') }}</th>
                <th class="text-end text-success"><i class="ph ph-thumbs-up"></i></th>
                <th class="text-end text-danger"><i class="ph ph-thumbs-down"></i></th>
                <th class="text-end">{{ __('analytics::analytics.like_rate') }}</th>
            </tr></thead>
            <tbody>
                @forelse($topLiked as $row)
                @php $total = $row->likes + $row->dislikes ?: 1; @endphp
                <tr>
                    <td>{{ $row->content_name }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($row->likes) }}</td>
                    <td class="text-end text-danger">{{ number_format($row->dislikes) }}</td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="progress flex-grow-1" style="height:6px;max-width:80px">
                                <div class="progress-bar bg-success" style="width:{{ round($row->likes/$total*100) }}%"></div>
                            </div>
                            <span class="small">{{ round($row->likes/$total*100) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('after-scripts')
<script>
const C = ['#e63757','#00d4ff','#00b69b','#fd7e14','#6f42c1','#20c997','#0dcaf0','#ffc107'];
new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: { labels: @json($viewsPerDay->pluck('date')), datasets: [{ data: @json($viewsPerDay->pluck('views')), borderColor:'#e63757', backgroundColor:'rgba(230,55,87,0.1)', fill:true, tension:0.4 }] },
    options: { responsive:true, plugins:{ legend:{ display:false } } }
});
@if($byDevice->count())
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: { labels: @json($byDevice->pluck('device_type')), datasets: [{ data: @json($byDevice->pluck('views')), backgroundColor: C }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
});
@endif
// Likes chart
const likesData = @json($likesPerDay);
if (document.getElementById('likesChart') && likesData.length) {
    new Chart(document.getElementById('likesChart'), {
        type: 'bar',
        data: {
            labels: likesData.map(d => d.date),
            datasets: [
                { label: 'Likes', data: likesData.map(d => d.likes), backgroundColor: 'rgba(25,135,84,0.7)' },
                { label: 'Dislikes', data: likesData.map(d => d.dislikes), backgroundColor: 'rgba(220,53,69,0.7)' }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: false }, y: { beginAtZero: true } } }
    });
}

</script>
@endpush

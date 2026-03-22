@extends('backend.layouts.app')
@section('title') Analytics @endsection

@push('after-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@6.11.0/css/flag-icons.min.css"/>
@endpush

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('backend.analytics.index') }}" class="d-flex gap-2 align-items-end">
            <div>
                <label class="form-label mb-1 small">{{ __('analytics::analytics.period') }}</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7d"    {{ $period=='7d'    ?'selected':'' }}>{{ __('analytics::analytics.last_7_days') }}</option>
                    <option value="30d"   {{ $period=='30d'   ?'selected':'' }}>{{ __('analytics::analytics.last_30_days') }}</option>
                    <option value="month" {{ $period=='month' ?'selected':'' }}>{{ __('analytics::analytics.this_month') }}</option>
                    <option value="all"   {{ $period=='all'   ?'selected':'' }}>{{ __('analytics::analytics.all_time') }}</option>
                </select>
            </div>
        </form>
                    <a href="{{ route('backend.analytics.export', ['period' => $period]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ph ph-download-simple me-1"></i>Export CSV
            </a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @php $kpis = [
        ['label'=>__('analytics::analytics.total_views'),    'value'=>number_format($stats['total_views']),          'sub'=>'',                           'icon'=>'ph-eye',                    'color'=>'primary'],
        ['label'=>__('analytics::analytics.watch_time'),     'value'=>$stats['watch_time']['hours'].'h',             'sub'=>number_format($stats['watch_time']['minutes']).' min', 'icon'=>'ph-clock',  'color'=>'success'],
        ['label'=>__('analytics::analytics.ppv_revenue'),    'value'=>number_format($stats['ppv_revenue']['total'],0,',',' ').' FCFA', 'sub'=>$stats['ppv_revenue']['count'].' transactions', 'icon'=>'ph-currency-circle-dollar', 'color'=>'warning'],
        ['label'=>__('analytics::analytics.unique_viewers'), 'value'=>number_format($stats['unique_viewers']),       'sub'=>$stats['partner_count'].' partenaires actifs', 'icon'=>'ph-users', 'color'=>'info'],
    ]; @endphp
    @foreach($kpis as $kpi)
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $kpi['label'] }}</p>
                        <h3 class="mb-0 fw-bold">{{ $kpi['value'] }}</h3>
                        @if($kpi['sub'])<small class="text-muted">{{ $kpi['sub'] }}</small>@endif
                    </div>
                    <div class="bg-{{ $kpi['color'] }} bg-opacity-10 rounded p-2">
                        <i class="ph {{ $kpi['icon'] }} text-{{ $kpi['color'] }} fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Courbes --}}
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>{{ __('analytics::analytics.views_over_time') }}</h6></div>
            <div class="card-body p-2" style="position:relative;height:200px"><canvas id="viewsChart"></canvas></div>
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
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-currency-circle-dollar me-2"></i>{{ __('analytics::analytics.revenue_over_time') }}</h6></div>
            <div class="card-body p-2" style="position:relative;height:200px"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-desktop me-2"></i>{{ __('analytics::analytics.by_platform') }}</h6></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byPlatform->count())
                    <canvas id="platformChart" style="max-height:170px;max-width:170px"></canvas>
                @else
                    <p class="text-muted small">{{ __('messages.no_record_found') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Top Contenus + Pays --}}
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trophy me-2"></i>{{ __('analytics::analytics.top_content') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('messages.name') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th class="text-end">{{ __('analytics::analytics.views') }}</th>
                        <th class="text-end">Watch time</th>
                    </tr></thead>
                    <tbody>
                        @forelse($topContent as $row)
                        <tr>
                            <td>{{ $row->content_name }}</td>
                            <td><span class="badge bg-secondary">{{ $row->content_type ?? '—' }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($row->views) }}</td>
                            <td class="text-end text-muted small">{{ round(($row->watch_time??0)/60) }} min</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
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
                            <td>
                                <span class="badge bg-secondary text-uppercase">{{ $row['gateway'] }}</span>
                            </td>
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

{{-- Partenaires --}}
@if($partners->count())
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="ph ph-handshake me-2"></i>{{ __('analytics::analytics.partners') }}</h6></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>{{ __('partner::partner.title') }}</th>
                <th class="text-end">Contenus</th>
                <th class="text-end">Actif depuis</th>
                <th class="text-end">{{ __('messages.action') }}</th>
            </tr></thead>
            <tbody>
                @foreach($partners as $p)
                @php
                    $pContentCount = \Modules\Entertainment\Models\Entertainment::where('partner_id', $p->id)->count()
                                   + \Modules\Video\Models\Video::where('partner_id', $p->id)->count();
                @endphp
                <tr>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td class="text-end">
                        <span class="badge bg-primary">{{ $pContentCount }} vidéos</span>
                    </td>
                    <td class="text-end text-muted small">{{ $p->created_at ? $p->created_at->format('d/m/Y') : '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('backend.analytics.partner', [$p->id, 'period'=>$period]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ph ph-chart-bar me-1"></i>{{ __('analytics::analytics.see_details') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Lien Finance --}}
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="ph ph-currency-circle-dollar fs-4"></i>
    <div>
        <strong>{{ __('analytics::analytics.finance_title') }}</strong> —
        {{ __('analytics::analytics.finance_hint') }}
        <a href="{{ route('backend.finance.index', ['period' => $period]) }}" class="btn btn-sm btn-primary ms-3">
            <i class="ph ph-arrow-right me-1"></i>{{ __('analytics::analytics.see_finance') }}
        </a>
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
const chartDefaults = { responsive: true, plugins: { legend: { display: false } } };

new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: { labels: @json($viewsPerDay->pluck('date')), datasets: [{ label:'Vues', data: @json($viewsPerDay->pluck('views')), borderColor:'#e63757', backgroundColor:'rgba(230,55,87,0.1)', fill:true, tension:0.4 }] },
    options: chartDefaults
});

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: { labels: @json($revenuePerDay->pluck('date')), datasets: [{ label:'FCFA', data: @json($revenuePerDay->pluck('revenue')), backgroundColor:'rgba(0,182,155,0.7)' }] },
    options: chartDefaults
});

@if($byDevice->count())
new Chart(document.getElementById('deviceChart'), {
    type: 'doughnut',
    data: { labels: @json($byDevice->pluck('device_type')), datasets: [{ data: @json($byDevice->pluck('views')), backgroundColor: C }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
});
@endif

@if($byPlatform->count())
new Chart(document.getElementById('platformChart'), {
    type: 'doughnut',
    data: { labels: @json($byPlatform->pluck('platform')), datasets: [{ data: @json($byPlatform->pluck('views')), backgroundColor: C }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
});
@endif
// Abonnements chart
const subsData = @json($subsPerDay);
if (document.getElementById('subsChart') && subsData.length) {
    new Chart(document.getElementById('subsChart'), {
        type: 'bar',
        data: {
            labels: subsData.map(d => d.date),
            datasets: [
                { label: 'Nouveaux abonnés', data: subsData.map(d => d.count), backgroundColor: 'rgba(99,102,241,0.7)', yAxisID: 'y' },
                { label: 'Revenus (FCFA)', data: subsData.map(d => d.revenue), type:'line', borderColor:'#ffc107', backgroundColor:'transparent', yAxisID: 'y1', tension:0.4 }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom' } },
            scales:{
                y:{ beginAtZero:true, position:'left' },
                y1:{ beginAtZero:true, position:'right', grid:{ drawOnChartArea:false } }
            }
        }
    });
}

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

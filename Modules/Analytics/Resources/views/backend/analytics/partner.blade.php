@extends('backend.layouts.app')
@section('title') Analytics — {{ $partner->name }} @endsection

@push('after-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                </select>
            </div>
        </form>
    </div>
</div>

{{-- KPIs partenaire --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">{{ __('analytics::analytics.total_views') }}</p>
                    <h3 class="mb-0 fw-bold">{{ number_format($stats['total_views']) }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 rounded p-2"><i class="ph ph-eye text-primary fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">{{ __('analytics::analytics.watch_time') }}</p>
                    <h3 class="mb-0 fw-bold">{{ $stats['watch_time']['hours'] }}h</h3>
                    <small class="text-muted">{{ number_format($stats['watch_time']['minutes']) }} min</small>
                </div>
                <div class="bg-success bg-opacity-10 rounded p-2"><i class="ph ph-clock text-success fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">{{ __('analytics::analytics.ppv_revenue') }}</p>
                    <h3 class="mb-0 fw-bold">{{ number_format($stats['ppv_revenue']['total'], 0, ',', ' ') }} FCFA</h3>
                    @if($stats['ppv_revenue']['commission'] > 0)
                    <small class="text-success">Commission : {{ number_format($stats['ppv_revenue']['commission'],0,',',' ') }} FCFA ({{ $partner->commission_rate }}%)</small>
                    @endif
                </div>
                <div class="bg-warning bg-opacity-10 rounded p-2"><i class="ph ph-currency-circle-dollar text-warning fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>{{ __('analytics::analytics.views_over_time') }}</h6></div>
            <div class="card-body"><canvas id="viewsChart" height="90"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-device-mobile me-2"></i>{{ __('analytics::analytics.by_device') }}</h6></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byDevice->count())
                    <canvas id="deviceChart" height="180"></canvas>
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
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-globe me-2"></i>{{ __('analytics::analytics.by_country') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('analytics::analytics.country') }}</th>
                        <th class="text-end">{{ __('analytics::analytics.views') }}</th>
                        <th class="text-end">%</th>
                    </tr></thead>
                    <tbody>
                        @php $total = $byCountry->sum('views') ?: 1; @endphp
                        @forelse($byCountry as $row)
                        <tr>
                            <td>{{ $row->country_code ?? '—' }}</td>
                            <td class="text-end">{{ number_format($row->views) }}</td>
                            <td class="text-end text-muted small">{{ round($row->views/$total*100,1) }}%</td>
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
    options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
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

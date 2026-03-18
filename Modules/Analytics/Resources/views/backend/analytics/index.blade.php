@extends('backend.layouts.app')

@section('title')
    {{ __('analytics::analytics.title') }}
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="mb-1"><i class="ph ph-chart-line me-2"></i>{{ __('analytics::analytics.title') }}</h4>
            <small class="text-muted">{{ __('analytics::analytics.subtitle') }}</small>
        </div>

        {{-- Period filter --}}
        <div class="d-flex gap-2">
            @foreach([7 => __('analytics::analytics.last_7_days'), 30 => __('analytics::analytics.last_30_days'), 90 => __('analytics::analytics.last_90_days')] as $value => $label)
                <a href="{{ route('backend.analytics.index', ['days' => $value]) }}"
                   class="btn btn-sm {{ $days == $value ? 'btn-primary' : 'btn-dark' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('analytics::analytics.views_period') }}</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalViews) }}</h2>
                        </div>
                        <div class="avatar-50 d-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10">
                            <i class="ph ph-eye fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('analytics::analytics.views_alltime') }}</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalViewsAllTime) }}</h2>
                        </div>
                        <div class="avatar-50 d-flex align-items-center justify-content-center rounded bg-success bg-opacity-10">
                            <i class="ph ph-chart-bar fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('analytics::analytics.unique_viewers') }}</p>
                            <h2 class="fw-bold mb-0">{{ number_format($uniqueViewers) }}</h2>
                        </div>
                        <div class="avatar-50 d-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10">
                            <i class="ph ph-users fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('analytics::analytics.likes_period') }}</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalLikes) }}</h2>
                        </div>
                        <div class="avatar-50 d-flex align-items-center justify-content-center rounded bg-danger bg-opacity-10">
                            <i class="ph ph-heart fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="card card-stats h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">{{ __('analytics::analytics.total_videos') }}</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalVideos) }}</h2>
                        </div>
                        <div class="avatar-50 d-flex align-items-center justify-content-center rounded bg-info bg-opacity-10">
                            <i class="ph ph-video-camera fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Views over time --}}
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('analytics::analytics.views_over_time') }}</h6>
                </div>
                <div class="card-body">
                    <div id="chart-views-timeline"></div>
                </div>
            </div>
        </div>

        {{-- Top 5 donut --}}
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">{{ __('analytics::analytics.top_videos_share') }}</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div id="chart-top-donut" style="width:100%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Videos Table --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="mb-0">{{ __('analytics::analytics.top_videos') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('analytics::analytics.video') }}</th>
                            <th>{{ __('analytics::analytics.views') }}</th>
                            <th>{{ __('analytics::analytics.likes') }}</th>
                            <th style="min-width:200px">{{ __('analytics::analytics.share') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVideos as $index => $video)
                            @php $percent = $totalViews > 0 ? round(($video->views_count / $totalViews) * 100, 1) : 0; @endphp
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($video->poster_url)
                                            <img src="{{ $video->poster_url }}" alt="" width="40" height="55"
                                                 class="rounded object-fit-cover" style="object-fit:cover">
                                        @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                                 style="width:40px;height:55px">
                                                <i class="ph ph-video-camera text-muted"></i>
                                            </div>
                                        @endif
                                        <span class="fw-medium">{{ $video->name }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary-subtle text-primary px-3 py-2">{{ number_format($video->views_count) }}</span></td>
                                <td><span class="badge bg-danger-subtle text-danger px-3 py-2"><i class="ph ph-heart me-1"></i>{{ number_format($video->likes_count) }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px">
                                            <div class="progress-bar bg-primary" style="width:{{ $percent }}%"></div>
                                        </div>
                                        <small class="text-muted" style="min-width:35px">{{ $percent }}%</small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('analytics::analytics.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const days = {{ $days }};
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const gridColor = isDark ? '#343a40' : '#e9ecef';

    // ── Timeline chart ────────────────────────────────────────────
    fetch(`{{ route('backend.analytics.chart_data') }}?days=${days}`)
        .then(r => r.json())
        .then(({ labels, data }) => {
            new ApexCharts(document.getElementById('chart-views-timeline'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: "{{ __('analytics::analytics.views') }}", data }],
                xaxis: { categories: labels, labels: { style: { colors: textColor } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { colors: textColor } } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                colors: ['#0d6efd'],
                grid: { borderColor: gridColor, strokeDashArray: 4 },
                tooltip: { theme: isDark ? 'dark' : 'light' },
            }).render();
        });

    // ── Donut chart (top 5) ───────────────────────────────────────
    fetch(`{{ route('backend.analytics.top_videos') }}?days=${days}`)
        .then(r => r.json())
        .then(({ labels, data }) => {
            const top5Labels = labels.slice(0, 5);
            const top5Data   = data.slice(0, 5).map(Number);

            if (top5Data.every(v => v === 0)) {
                document.getElementById('chart-top-donut').innerHTML =
                    '<p class="text-center text-muted py-5">{{ __("analytics::analytics.no_data") }}</p>';
                return;
            }

            new ApexCharts(document.getElementById('chart-top-donut'), {
                chart: { type: 'donut', height: 280 },
                series: top5Data,
                labels: top5Labels,
                legend: { position: 'bottom', labels: { colors: textColor } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
                tooltip: { theme: isDark ? 'dark' : 'light' },
                plotOptions: { pie: { donut: { size: '65%' } } },
            }).render();
        });
});
</script>
@endpush

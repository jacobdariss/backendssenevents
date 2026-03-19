@extends('backend.layouts.app')
@section('title') {{ __('analytics::analytics.finance_title') }} @endsection

@push('after-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- Filtre période --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('backend.finance.index') }}" class="d-flex gap-2 align-items-end">
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
    </div>
</div>

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ __('analytics::analytics.total_revenue') }}</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($kpis['total_revenue'],0,',',' ') }} <small class="fs-6">FCFA</small></h3>
                        @if($kpis['growth'] != 0)
                        <small class="{{ $kpis['growth'] > 0 ? 'text-success' : 'text-danger' }}">
                            <i class="ph {{ $kpis['growth'] > 0 ? 'ph-trend-up' : 'ph-trend-down' }}"></i>
                            {{ $kpis['growth'] > 0 ? '+' : '' }}{{ $kpis['growth'] }}% vs période préc.
                        </small>
                        @endif
                    </div>
                    <div class="bg-success bg-opacity-10 rounded p-2"><i class="ph ph-currency-circle-dollar text-success fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ __('analytics::analytics.ppv_revenue') }}</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($kpis['ppv_revenue'],0,',',' ') }} <small class="fs-6">FCFA</small></h3>
                        <small class="text-muted">{{ $kpis['ppv_count'] }} achats</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded p-2"><i class="ph ph-play-circle text-primary fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ __('analytics::analytics.subscription_revenue') }}</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($kpis['sub_revenue'],0,',',' ') }} <small class="fs-6">FCFA</small></h3>
                        <small class="text-muted">{{ $kpis['sub_count'] }} abonnés actifs</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded p-2"><i class="ph ph-credit-card text-warning fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ __('analytics::analytics.avg_transaction') }}</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($kpis['avg_transaction'],0,',',' ') }} <small class="fs-6">FCFA</small></h3>
                        <small class="text-muted">par transaction</small>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded p-2"><i class="ph ph-calculator text-info fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Courbe revenus ──────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="ph ph-trend-up me-2"></i>{{ __('analytics::analytics.revenue_over_time') }}</h6></div>
    <div class="card-body" style="position:relative;height:220px">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

{{-- Gateways + Abonnements ──────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-credit-card me-2"></i>{{ __('analytics::analytics.payment_gateways') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Gateway</th>
                        <th class="text-end">{{ __('analytics::analytics.transactions') }}</th>
                        <th class="text-end">Revenus</th>
                        <th class="text-end">%</th>
                    </tr></thead>
                    <tbody>
                        @php $totalGw = $byGateway->sum('revenue') ?: 1; @endphp
                        @forelse($byGateway as $row)
                        <tr>
                            <td><span class="badge bg-secondary text-uppercase">{{ $row['gateway'] }}</span></td>
                            <td class="text-end fw-bold">{{ number_format($row['transactions']) }}</td>
                            <td class="text-end">{{ number_format($row['revenue'],0,',',' ') }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <div class="progress" style="height:6px;width:50px">
                                        <div class="progress-bar bg-warning" style="width:{{ round($row['revenue']/$totalGw*100) }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ round($row['revenue']/$totalGw*100,1) }}%</span>
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
    </div>
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="ph ph-users me-2"></i>{{ __('analytics::analytics.subscriptions') }}</h6>
                <div class="d-flex gap-3">
                    <span class="small text-success fw-bold">{{ number_format($subDetails['active']) }} actifs</span>
                    <span class="small text-danger">{{ number_format($subDetails['expired']) }} expirés</span>
                    <span class="small text-muted">Churn {{ $subDetails['churn'] }}%</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Plan</th>
                        <th class="text-end">Abonnés</th>
                        <th class="text-end">Revenus</th>
                        <th class="text-end">%</th>
                    </tr></thead>
                    <tbody>
                        @php $totalSub = $subDetails['by_plan']->sum('revenue') ?: 1; @endphp
                        @forelse($subDetails['by_plan'] as $plan)
                        <tr>
                            <td class="fw-semibold">{{ $plan->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($plan->count) }}</td>
                            <td class="text-end">{{ number_format($plan->revenue,0,',',' ') }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <div class="progress" style="height:6px;width:50px">
                                        <div class="progress-bar bg-primary" style="width:{{ round($plan->revenue/$totalSub*100) }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ round($plan->revenue/$totalSub*100,1) }}%</span>
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
    </div>
</div>

{{-- Revenus par partenaire ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="ph ph-handshake me-2"></i>{{ __('analytics::analytics.revenue_by_partner') }}</h6></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr>
                <th>{{ __('partner::partner.title') }}</th>
                <th class="text-end">Revenus PPV</th>
                <th class="text-end">Commission (%)</th>
                <th class="text-end">Commission (FCFA)</th>
                <th class="text-end">Net plateforme</th>
            </tr></thead>
            <tbody>
                @forelse($byPartner as $row)
                <tr>
                    <td><strong>{{ $row['partner']->name }}</strong></td>
                    <td class="text-end fw-bold">{{ number_format($row['ppv_rev'],0,',',' ') }}</td>
                    <td class="text-end text-muted">{{ $row['rate'] }}%</td>
                    <td class="text-end text-warning">{{ number_format($row['commission'],0,',',' ') }}</td>
                    <td class="text-end text-success">{{ number_format($row['net'],0,',',' ') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                @endforelse
            </tbody>
            @if($byPartner->sum('ppv_rev') > 0)
            <tfoot class="table-light fw-bold">
                <tr>
                    <td>Total</td>
                    <td class="text-end">{{ number_format($byPartner->sum('ppv_rev'),0,',',' ') }}</td>
                    <td class="text-end">—</td>
                    <td class="text-end text-warning">{{ number_format($byPartner->sum('commission'),0,',',' ') }}</td>
                    <td class="text-end text-success">{{ number_format($byPartner->sum('net'),0,',',' ') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Top contenus PPV + Transactions récentes ───────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-trophy me-2"></i>{{ __('analytics::analytics.top_ppv_content') }}</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Contenu</th>
                        <th class="text-end">Achats</th>
                        <th class="text-end">Revenus</th>
                    </tr></thead>
                    <tbody>
                        @forelse($topPpv as $row)
                        <tr>
                            <td class="small">
                                {{ $row->content_name }}
                                <span class="badge bg-secondary ms-1">{{ $row->type }}</span>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($row->purchases) }}</td>
                            <td class="text-end text-muted small">{{ number_format($row->revenue,0,',',' ') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-list-checks me-2"></i>{{ __('analytics::analytics.recent_transactions') }}</h6></div>
            <div class="card-body p-0" style="max-height:380px;overflow-y:auto">
                <table class="table table-hover table-sm mb-0">
                    <thead><tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Gateway</th>
                        <th class="text-end">Montant</th>
                        <th>Statut</th>
                    </tr></thead>
                    <tbody>
                        @forelse($recentTx as $tx)
                        <tr>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($tx['date'])->format('d/m H:i') }}</td>
                            <td><span class="badge {{ $tx['type']=='PPV' ? 'bg-primary' : 'bg-warning text-dark' }}">{{ $tx['type'] }}</span></td>
                            <td class="small text-uppercase">{{ $tx['gateway'] }}</td>
                            <td class="text-end fw-bold">{{ number_format($tx['amount'],0,',',' ') }}</td>
                            <td>
                                <span class="badge {{ $tx['status']=='success' || $tx['status']=='active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $tx['status'] }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_record_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script>
const C = ['#e63757','#00b69b','#6f42c1','#fd7e14','#0dcaf0','#ffc107'];
const labels  = @json($revenuePerDay['labels']);
const ppvData = @json($revenuePerDay['ppv']);
const subData = @json($revenuePerDay['subs']);

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'PPV', data: ppvData, backgroundColor: 'rgba(230,55,87,0.7)', stack: 'revenue' },
            { label: 'Abonnements', data: subData, backgroundColor: 'rgba(0,182,155,0.7)', stack: 'revenue' }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
    }
});
</script>
@endpush

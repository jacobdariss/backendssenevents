@extends('backend.layouts.app')
@section('title') {{ __('analytics::analytics.finance_title') }} @endsection

@push('after-styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                    <a href="{{ route('backend.finance.export', ['period' => $period]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="ph ph-download-simple me-1"></i>Export CSV
            </a>
    </div>
</div>

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi kpi-teal">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-currency-circle-dollar"></i></div>
            <div class="kpi-val">{{ number_format($kpis['total_revenue'],0,',',' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">{{ __('analytics::analytics.total_revenue') }}</div>
            @if($kpis['growth'] != 0)
            <div class="kpi-sub">
                <i class="ph {{ $kpis['growth'] > 0 ? 'ph-trend-up' : 'ph-trend-down' }}"></i>
                {{ $kpis['growth'] > 0 ? '+' : '' }}{{ $kpis['growth'] }}% vs préc.
            </div>
            @endif
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi kpi-purple">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-play-circle"></i></div>
            <div class="kpi-val">{{ number_format($kpis['ppv_revenue'],0,',',' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">{{ __('analytics::analytics.ppv_revenue') }}</div>
            <div class="kpi-sub">{{ $kpis['ppv_count'] }} achats</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi kpi-amber">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-credit-card"></i></div>
            <div class="kpi-val">{{ number_format($kpis['sub_revenue'],0,',',' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">{{ __('analytics::analytics.subscription_revenue') }}</div>
            <div class="kpi-sub">{{ $kpis['sub_count'] }} abonnés actifs</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi kpi-blue">
            <div class="kpi-deco"></div>
            <div class="kpi-icon"><i class="ph ph-calculator"></i></div>
            <div class="kpi-val">{{ number_format($kpis['avg_transaction'],0,',',' ') }} <span style="font-size:13px;opacity:.7">XOF</span></div>
            <div class="kpi-lbl">{{ __('analytics::analytics.avg_transaction') }}</div>
            <div class="kpi-sub">par transaction</div>
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

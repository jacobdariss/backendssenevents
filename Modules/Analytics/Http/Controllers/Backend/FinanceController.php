<?php

namespace Modules\Analytics\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Analytics\Services\FinanceService;

class FinanceController extends Controller
{
    protected function finance(): FinanceService
    {
        return app(FinanceService::class);
    }

    public function index(Request $request)
    {
        $period = $request->get('period', '30d');
        [$from, $to] = $this->finance()->getPeriodDates($period);

        $kpis           = $this->finance()->globalKpis($from, $to);
        $revenuePerDay  = $this->finance()->revenuePerDay($from, $to);
        $byGateway      = $this->finance()->revenueByGateway($from, $to);
        $byPartner      = $this->finance()->revenueByPartner($from, $to);
        $topPpv         = $this->finance()->topPpvContent($from, $to);
        $recentTx       = $this->finance()->recentTransactions($from, $to);
        $subDetails     = $this->finance()->subscriptionDetails($from, $to);
        $module_action  = 'Finance';

        return view('analytics::backend.finance.index', compact(
            'kpis', 'revenuePerDay', 'byGateway', 'byPartner',
            'topPpv', 'recentTx', 'subDetails', 'period', 'module_action'
        ));
    }
}

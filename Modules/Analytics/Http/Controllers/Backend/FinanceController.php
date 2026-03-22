<?php

namespace Modules\Analytics\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Analytics\Services\FinanceService;

class FinanceController extends Controller
{
    protected FinanceService $finance;

    public function __construct(FinanceService $finance)
    {
        $this->finance = $finance;
    }

    public function index(Request $request)
    {
        $period = $request->get('period', '30d');
        [$from, $to] = $this->finance->getPeriodDates($period);

        // Utiliser des copies Carbon pour éviter la mutation entre appels
        $kpis           = $this->finance->globalKpis($from->copy(), $to->copy());
        $revenuePerDay  = $this->finance->revenuePerDay($from->copy(), $to->copy());
        $byGateway      = $this->finance->revenueByGateway($from->copy(), $to->copy());
        $byPartner      = $this->finance->revenueByPartner($from->copy(), $to->copy());
        $topPpv         = $this->finance->topPpvContent($from->copy(), $to->copy());
        $recentTx       = $this->finance->recentTransactions($from->copy(), $to->copy());
        $subDetails     = $this->finance->subscriptionDetails($from->copy(), $to->copy());
        $module_action  = 'Finance';

        return view('analytics::backend.finance.index', compact(
            'kpis', 'revenuePerDay', 'byGateway', 'byPartner',
            'topPpv', 'recentTx', 'subDetails', 'period', 'module_action'
        ));
    }
}

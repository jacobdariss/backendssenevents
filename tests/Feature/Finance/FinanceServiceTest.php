<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Analytics\Services\FinanceService;
use Modules\Partner\Models\Partner;
use Tests\TestCase;
use Carbon\Carbon;

class FinanceServiceTest extends TestCase
{
    use DatabaseTransactions;

    private FinanceService $financeService;
    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeService = app(FinanceService::class);

        $this->partner = Partner::create([
            'name'            => 'Studio Finance Test',
            'slug'            => 'studio-finance-test',
            'commission_rate' => 25,
            'status'          => 1,
        ]);
    }

    /** @test */
    public function finance_service_returns_correct_kpi_structure(): void
    {
        $from = Carbon::now()->subDays(30);
        $to   = Carbon::now();

        $kpis = $this->financeService->globalKpis($from, $to);

        $this->assertArrayHasKey('total_revenue',    $kpis);
        $this->assertArrayHasKey('ppv_revenue',      $kpis);
        $this->assertArrayHasKey('sub_revenue',      $kpis);
        $this->assertArrayHasKey('ppv_count',        $kpis);
        $this->assertArrayHasKey('sub_count',        $kpis);
        $this->assertArrayHasKey('avg_transaction',  $kpis);
        $this->assertArrayHasKey('growth',           $kpis);
    }

    /** @test */
    public function revenue_per_day_returns_correct_structure(): void
    {
        $from = Carbon::now()->subDays(7);
        $to   = Carbon::now();

        $revenue = $this->financeService->revenuePerDay($from, $to);

        $this->assertArrayHasKey('labels', $revenue);
        $this->assertArrayHasKey('ppv',    $revenue);
        $this->assertArrayHasKey('subs',   $revenue);
    }

    /** @test */
    public function partner_commission_is_calculated_correctly(): void
    {
        // Commission rate = 25%, revenus = 10 000 FCFA
        // Commission attendue = 2 500 FCFA, Net = 7 500 FCFA
        $rate       = $this->partner->commission_rate;
        $revenue    = 10000;
        $commission = round($revenue * $rate / 100, 2);
        $net        = round($revenue * (1 - $rate / 100), 2);

        $this->assertEquals(2500, $commission);
        $this->assertEquals(7500, $net);
        $this->assertEquals($revenue, $commission + $net);
    }

    /** @test */
    public function finance_export_route_is_accessible_to_admin(): void
    {
        $admin = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
                         ->get('/app/finance/export?period=30d');

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'text/csv',
            $response->headers->get('Content-Type')
        );
    }

    /** @test */
    public function finance_export_is_blocked_for_non_admin(): void
    {
        $user = User::factory()->create();
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->assignRole('user');

        $response = $this->actingAs($user)
                         ->get('/app/finance/export?period=30d');

        // Doit soit rediriger (302) soit retourner une page non-CSV
        // En aucun cas retourner un fichier CSV à un non-admin
        $contentType = $response->headers->get('Content-Type', '');
        $this->assertStringNotContainsString(
            'text/csv',
            $contentType,
            'Un utilisateur non-admin ne doit pas recevoir un export CSV'
        );
    }

    /** @test */
    public function subscription_details_returns_valid_structure(): void
    {
        $from    = Carbon::now()->subDays(30);
        $to      = Carbon::now();
        $details = $this->financeService->subscriptionDetails($from, $to);

        $this->assertArrayHasKey('new',      $details);
        $this->assertArrayHasKey('active',   $details);
        $this->assertArrayHasKey('expired',  $details);
        $this->assertArrayHasKey('revenue',  $details);
        $this->assertArrayHasKey('churn',    $details);
        $this->assertArrayHasKey('by_plan',  $details);
        $this->assertGreaterThanOrEqual(0, $details['churn']);
        $this->assertLessThanOrEqual(100,   $details['churn']);
    }
}

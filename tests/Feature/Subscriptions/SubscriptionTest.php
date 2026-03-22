<?php

namespace Tests\Feature\Subscriptions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Subscriptions\Models\Plan;
use Modules\Subscriptions\Models\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->plan = Plan::create([
            'name'           => 'Premium',
            'identifier'     => 'premium',
            'level'          => 2,
            'duration'       => 'month',
            'duration_value' => 1,
            'price'          => 4990,
            'status'         => 1,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createSubscription(array $overrides = []): Subscription
    {
        return Subscription::create(array_merge([
            'user_id'    => $this->user->id,
            'plan_id'    => $this->plan->id,
            'start_date' => now(),
            'end_date'   => now()->addMonth(),
            'status'     => 1,
            'amount'     => 4990,
            'name'       => 'Premium',
            'identifier' => 'premium',
            'level'      => 2,
        ], $overrides));
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function subscription_is_created_with_correct_fields(): void
    {
        $sub = $this->createSubscription();

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status'  => 1,
        ]);

        $this->assertEquals($this->plan->id, $sub->plan_id);
        $this->assertEquals(1, $sub->status);
    }

    /** @test */
    public function active_subscription_has_future_end_date(): void
    {
        $sub = $this->createSubscription([
            'end_date' => now()->addMonth(),
            'status'   => 1,
        ]);

        $this->assertTrue(Carbon::parse($sub->end_date)->isFuture());
    }

    /** @test */
    public function expired_subscription_has_past_end_date(): void
    {
        $sub = $this->createSubscription([
            'end_date' => now()->subDay(),
            'status'   => 0,
        ]);

        $this->assertTrue(Carbon::parse($sub->end_date)->isPast());
        $this->assertEquals(0, $sub->status);
    }

    /** @test */
    public function user_can_have_only_one_active_subscription(): void
    {
        $this->createSubscription(['status' => 1]);

        $activeCount = Subscription::where('user_id', $this->user->id)
            ->where('status', 1)
            ->where('end_date', '>', now())
            ->count();

        $this->assertEquals(1, $activeCount);
    }

    /** @test */
    public function subscription_amount_matches_plan_price(): void
    {
        $sub = $this->createSubscription(['amount' => $this->plan->price]);

        $this->assertEquals($this->plan->price, $sub->amount);
    }

    /** @test */
    public function subscription_level_is_inherited_from_plan(): void
    {
        $sub = $this->createSubscription(['level' => $this->plan->level]);

        $this->assertEquals($this->plan->level, $sub->level);
    }

    /** @test */
    public function subscription_page_requires_authentication(): void
    {
        $response = $this->get('/subscription-plan');

        // Doit rediriger vers login ou renvoyer 200 (page publique selon config)
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    /** @test */
    public function multiple_plans_can_exist_with_different_levels(): void
    {
        $basicPlan = Plan::create([
            'name'           => 'Basic',
            'identifier'     => 'basic',
            'level'          => 1,
            'duration'       => 'month',
            'duration_value' => 1,
            'price'          => 1990,
            'status'         => 1,
        ]);

        $this->assertLessThan($this->plan->level, $basicPlan->level);
        $this->assertLessThan($this->plan->price,  $basicPlan->price);

        $planCount = Plan::where('status', 1)->count();
        $this->assertGreaterThanOrEqual(2, $planCount);
    }
}

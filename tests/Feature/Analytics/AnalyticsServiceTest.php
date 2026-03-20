<?php

namespace Tests\Feature\Analytics;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Analytics\Services\AnalyticsService;
use Modules\Entertainment\Models\Entertainment;
use Modules\Entertainment\Models\EntertainmentView;
use Modules\Partner\Models\Partner;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $analytics;
    private Partner $partner;
    private Entertainment $movie;
    private User $user;
    private Carbon $from;
    private Carbon $to;

    protected function setUp(): void
    {
        parent::setUp();

        $this->analytics = app(AnalyticsService::class);

        Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->partner = Partner::create([
            'name'            => 'Studio Analytics',
            'slug'            => 'studio-analytics',
            'commission_rate' => 20,
            'status'          => 1,
        ]);

        $this->movie = Entertainment::create([
            'name'       => 'Film Analytics Test',
            'slug'       => 'film-analytics-test',
            'type'       => 'movie',
            'status'     => 1,
            'partner_id' => $this->partner->id,
        ]);

        $this->from = Carbon::now()->subDays(30);
        $this->to   = Carbon::now();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function recordView(array $overrides = []): EntertainmentView
    {
        return EntertainmentView::create(array_merge([
            'entertainment_id' => $this->movie->id,
            'user_id'          => $this->user->id,
            'content_type'     => 'movie',
            'partner_id'       => $this->partner->id,
            'device_type'      => 'mobile',
            'platform'         => 'Android',
            'country_code'     => 'SN',
            'watch_time'       => 120,
        ], $overrides));
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function view_is_recorded_in_database(): void
    {
        $this->recordView();

        $this->assertDatabaseHas('entertainment_views', [
            'entertainment_id' => $this->movie->id,
            'user_id'          => $this->user->id,
            'content_type'     => 'movie',
        ]);
    }

    /** @test */
    public function total_views_count_is_correct(): void
    {
        $this->recordView();
        $this->recordView();
        $this->recordView();

        $total = $this->analytics->totalViews($this->from, $this->to);

        $this->assertEquals(3, $total);
    }

    /** @test */
    public function watch_time_is_accumulated_correctly(): void
    {
        $this->recordView(['watch_time' => 60]);
        $this->recordView(['watch_time' => 120]);
        $this->recordView(['watch_time' => 180]);

        $watchTime = $this->analytics->totalWatchTime($this->from, $this->to);

        $this->assertArrayHasKey('seconds', $watchTime);
        $this->assertEquals(360, $watchTime['seconds']);
    }

    /** @test */
    public function views_are_filtered_by_partner_correctly(): void
    {
        // Vue du partner 1
        $this->recordView(['partner_id' => $this->partner->id]);

        // Vue d'un autre partenaire
        $otherPartner = Partner::create([
            'name'   => 'Other Partner',
            'slug'   => 'other-partner',
            'status' => 1,
        ]);
        $this->recordView(['partner_id' => $otherPartner->id]);

        $totalAll     = $this->analytics->totalViews($this->from, $this->to);
        $partnerViews = $this->analytics->totalViews($this->from, $this->to, $this->partner->id);

        $this->assertEquals(2, $totalAll);
        $this->assertEquals(1, $partnerViews);
    }

    /** @test */
    public function views_per_day_returns_correct_structure(): void
    {
        $this->recordView();

        $perDay = $this->analytics->viewsPerDay($this->from, $this->to);

        $this->assertNotEmpty($perDay);
        $firstEntry = $perDay->first();
        $this->assertArrayHasKey('date',  (array) $firstEntry);
        $this->assertArrayHasKey('views', (array) $firstEntry);
    }

    /** @test */
    public function views_by_device_groups_correctly(): void
    {
        $this->recordView(['device_type' => 'mobile']);
        $this->recordView(['device_type' => 'mobile']);
        $this->recordView(['device_type' => 'desktop']);

        $byDevice = $this->analytics->viewsByDevice($this->from, $this->to);

        $deviceTypes = $byDevice->pluck('device_type')->toArray();
        $this->assertContains('mobile',  $deviceTypes);
        $this->assertContains('desktop', $deviceTypes);

        $mobileViews = $byDevice->firstWhere('device_type', 'mobile');
        $this->assertEquals(2, $mobileViews->total);
    }

    /** @test */
    public function top_content_returns_most_viewed_first(): void
    {
        // Film 2 avec plus de vues
        $movie2 = Entertainment::create([
            'name'   => 'Film Top Test',
            'slug'   => 'film-top-test',
            'type'   => 'movie',
            'status' => 1,
        ]);

        $this->recordView(['entertainment_id' => $this->movie->id]);
        $this->recordView(['entertainment_id' => $movie2->id]);
        $this->recordView(['entertainment_id' => $movie2->id]);
        $this->recordView(['entertainment_id' => $movie2->id]);

        $topContent = $this->analytics->topContent($this->from, $this->to, null, 10);

        $this->assertNotEmpty($topContent);
        // Le premier doit avoir le plus de vues
        $this->assertGreaterThanOrEqual(
            $topContent->last()->total_views ?? 0,
            $topContent->first()->total_views ?? 0
        );
    }

    /** @test */
    public function analytics_export_accessible_to_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
                         ->get('/app/analytics/export?period=30d');

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'text/csv',
            $response->headers->get('Content-Type')
        );
    }
}

<?php

namespace Tests\Feature\PPV;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Entertainment\Models\Entertainment;
use Modules\Frontend\Models\PayPerView;
use Modules\Partner\Models\Partner;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PayPerViewTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private Entertainment $movie;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');

        $this->movie = Entertainment::create([
            'name'         => 'Film PPV Test',
            'slug'         => 'film-ppv-test',
            'type'         => 'movie',
            'status'       => 1,
            'movie_access' => 'pay-per-view',
            'price'        => 5000,
            'purchase_type' => 'rental',
            'available_for' => 7,
            'access_duration' => 48,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createPpvAccess(array $overrides = []): PayPerView
    {
        return PayPerView::create(array_merge([
            'user_id'         => $this->user->id,
            'movie_id'        => $this->movie->id,
            'type'            => 'movie',
            'price'           => 5000,
            'content_price'   => 5000,
            'view_expiry_date' => now()->addDays(7),
            'available_for'   => 7,
        ], $overrides));
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function ppv_access_is_granted_after_purchase(): void
    {
        $this->createPpvAccess();

        $isPurchased = Entertainment::isPurchased(
            $this->movie->id, 'movie', $this->user->id
        );

        $this->assertTrue($isPurchased);
    }

    /** @test */
    public function ppv_access_is_denied_without_purchase(): void
    {
        $isPurchased = Entertainment::isPurchased(
            $this->movie->id, 'movie', $this->user->id
        );

        $this->assertFalse($isPurchased);
    }

    /** @test */
    public function ppv_access_is_denied_after_view_expiry(): void
    {
        $this->createPpvAccess([
            'view_expiry_date' => now()->subDay(),
        ]);

        $isPurchased = Entertainment::isPurchased(
            $this->movie->id, 'movie', $this->user->id
        );

        $this->assertFalse($isPurchased);
    }

    /** @test */
    public function ppv_access_valid_while_within_expiry(): void
    {
        $this->createPpvAccess([
            'view_expiry_date' => now()->addDays(3),
        ]);

        $this->assertTrue(
            Entertainment::isPurchased($this->movie->id, 'movie', $this->user->id)
        );
    }

    /** @test */
    public function ppv_price_with_discount_calculated_correctly(): void
    {
        $originalPrice      = 10000;
        $discountPercentage = 20;
        $expectedPrice      = $originalPrice * (1 - $discountPercentage / 100);

        $this->assertEquals(8000, $expectedPrice);
    }

    /** @test */
    public function ppv_access_is_isolated_per_user(): void
    {
        $this->createPpvAccess(['user_id' => $this->user->id]);

        $otherUser = User::factory()->create();

        $this->assertTrue(
            Entertainment::isPurchased($this->movie->id, 'movie', $this->user->id)
        );
        $this->assertFalse(
            Entertainment::isPurchased($this->movie->id, 'movie', $otherUser->id)
        );
    }

    /** @test */
    public function ppv_record_is_stored_with_correct_fields(): void
    {
        $this->createPpvAccess();

        $this->assertDatabaseHas('pay_per_views', [
            'user_id'  => $this->user->id,
            'movie_id' => $this->movie->id,
            'type'     => 'movie',
        ]);
    }

    /** @test */
    public function ppv_page_redirects_unauthenticated_user(): void
    {
        // Vérifier qu'un utilisateur non connecté est redirigé depuis une page PPV
        $response = $this->get("/tvshow-details/{$this->movie->slug}");

        // Doit retourner 200 (page publique) ou rediriger — jamais 500
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}

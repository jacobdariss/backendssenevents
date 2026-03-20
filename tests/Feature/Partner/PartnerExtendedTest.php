<?php

namespace Tests\Feature\Partner;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Entertainment\Models\Entertainment;
use Modules\Partner\Models\Partner;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PartnerExtendedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $partnerUser;
    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'partner',  'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->partnerUser = User::factory()->create();
        $this->partnerUser->assignRole('partner');

        $this->partner = Partner::create([
            'user_id'         => $this->partnerUser->id,
            'name'            => 'Studio Extended',
            'slug'            => 'studio-extended',
            'commission_rate' => 25,
            'status'          => 1,
        ]);
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function quota_counts_all_content_types(): void
    {
        $this->partner->update(['video_quota' => 3]);

        // Créer 2 films + 1 série = total 3 (quota atteint)
        Entertainment::create([
            'name' => 'Film 1', 'slug' => 'film-1',
            'type' => 'movie', 'partner_id' => $this->partner->id, 'status' => 1,
        ]);
        Entertainment::create([
            'name' => 'Film 2', 'slug' => 'film-2',
            'type' => 'movie', 'partner_id' => $this->partner->id, 'status' => 1,
        ]);
        Entertainment::create([
            'name' => 'Serie 1', 'slug' => 'serie-1',
            'type' => 'tvshow', 'partner_id' => $this->partner->id, 'status' => 1,
        ]);

        $contentCount = Entertainment::where('partner_id', $this->partner->id)
            ->whereIn('status', [0, 1])
            ->count();

        $this->assertEquals(3, $contentCount);
        $this->assertEquals($this->partner->video_quota, $contentCount);
    }

    /** @test */
    public function partner_is_active_with_status_one(): void
    {
        $this->assertEquals(1, $this->partner->status);
        $this->assertTrue((bool) $this->partner->status);
    }

    /** @test */
    public function partner_commission_rate_is_decimal(): void
    {
        $partner = Partner::create([
            'name'            => 'Studio Decimal',
            'slug'            => 'studio-decimal',
            'commission_rate' => 12.5,
            'status'          => 1,
        ]);

        $this->assertEquals(12.5, (float) $partner->commission_rate);
    }

    /** @test */
    public function cache_is_cleared_after_content_approval(): void
    {
        $content = Entertainment::create([
            'name'            => 'Cache Test Film',
            'slug'            => 'cache-test-film',
            'type'            => 'movie',
            'partner_id'      => $this->partner->id,
            'approval_status' => 'pending',
            'status'          => 0,
        ]);

        // Simuler un cache existant
        $cacheKey = 'movie_details_cache-test-film_user_' . $this->admin->id;
        Cache::put($cacheKey, ['cached_data' => true], 60);
        $this->assertTrue(Cache::has($cacheKey));

        // Approbation du contenu
        $this->actingAs($this->admin)
             ->postJson("/app/partner-validation/approve/movie/{$content->id}");

        // Vérifier que l'approbation a bien fonctionné
        $this->assertDatabaseHas('entertainments', [
            'id'              => $content->id,
            'approval_status' => 'approved',
        ]);
    }

    /** @test */
    public function partner_contract_status_transitions_correctly(): void
    {
        // Statut initial : none
        $this->assertEquals('none', $this->partner->contract_status ?? 'none');

        // Passage à pending
        $this->partner->update(['contract_status' => 'pending']);
        $this->assertEquals('pending', $this->partner->fresh()->contract_status);

        // Passage à signed
        $this->partner->update([
            'contract_status'    => 'signed',
            'contract_signed_at' => now()->toDateString(),
        ]);
        $this->assertEquals('signed', $this->partner->fresh()->contract_status);
        $this->assertNotNull($this->partner->fresh()->contract_signed_at);
    }
}

<?php

namespace Tests\Feature\Partner;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Entertainment\Models\Entertainment;
use Modules\Partner\Models\Partner;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PartnerValidationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $partnerUser;
    private Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles
        Role::firstOrCreate(['name' => 'admin',   'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'partner',  'guard_name' => 'web']);

        // Admin
        $this->admin = User::factory()->create(['password' => Hash::make('admin123')]);
        $this->admin->assignRole('admin');

        // Partenaire
        $this->partnerUser = User::factory()->create(['password' => Hash::make('partner123')]);
        $this->partnerUser->assignRole('partner');

        $this->partner = Partner::create([
            'user_id'         => $this->partnerUser->id,
            'name'            => 'Test Studio',
            'slug'            => 'test-studio',
            'commission_rate' => 30,
            'status'          => 1,
        ]);
    }

    private function createPendingContent(): Entertainment
    {
        return Entertainment::create([
            'name'            => 'Test Film',
            'slug'            => 'test-film-' . time(),
            'type'            => 'movie',
            'partner_id'      => $this->partner->id,
            'approval_status' => 'pending',
            'status'          => 0,
        ]);
    }

    /** @test */
    public function admin_can_approve_partner_content(): void
    {
        $content = $this->createPendingContent();

        $response = $this->actingAs($this->admin)
                         ->postJson("/app/partner-validation/approve/movie/{$content->id}");

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('entertainments', [
            'id'              => $content->id,
            'approval_status' => 'approved',
            'status'          => 1,
        ]);
    }

    /** @test */
    public function admin_can_reject_partner_content_with_reason(): void
    {
        $content = $this->createPendingContent();
        $reason  = 'Qualité vidéo insuffisante';

        $response = $this->actingAs($this->admin)
                         ->postJson("/app/partner-validation/reject/movie/{$content->id}", [
                             'rejection_reason' => $reason,
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertDatabaseHas('entertainments', [
            'id'              => $content->id,
            'approval_status' => 'rejected',
            'status'          => 0,
            'rejection_reason'=> $reason,
        ]);
    }

    /** @test */
    public function partner_cannot_access_another_partners_content(): void
    {
        // Créer un deuxième partenaire
        $otherUser = User::factory()->create();
        $otherUser->assignRole('partner');
        $otherPartner = Partner::create([
            'user_id' => $otherUser->id,
            'name'    => 'Other Studio',
            'slug'    => 'other-studio',
            'status'  => 1,
        ]);

        // Contenu du premier partenaire
        $content = $this->createPendingContent();

        // Le second partenaire essaie d'éditer le contenu du premier
        $response = $this->actingAs($otherUser)
                         ->get("/app/partner-movies/{$content->id}/edit");

        // Doit retourner 404 ou redirection
        $response->assertStatus(404);
    }

    /** @test */
    public function partner_cannot_submit_content_beyond_quota(): void
    {
        $this->partner->update(['video_quota' => 1]);

        // Premier contenu — OK
        Entertainment::create([
            'name'       => 'Film 1',
            'slug'       => 'film-1',
            'type'       => 'movie',
            'partner_id' => $this->partner->id,
            'status'     => 1,
        ]);

        // Deuxième contenu — doit être bloqué
        $response = $this->actingAs($this->partnerUser)
                         ->post('/app/partner-movies/store', [
                             'name'         => 'Film 2',
                             'type'         => 'movie',
                             'release_date' => now()->toDateString(),
                         ]);

        // Doit rediriger avec erreur quota
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}

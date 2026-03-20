<?php

namespace Tests\Feature\HomepageBuilder;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HomepageBuilder\Models\HomepageSection;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomepageBuilderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createSection(array $overrides = []): HomepageSection
    {
        return HomepageSection::create(array_merge([
            'slug'          => 'test-section-' . uniqid(),
            'name'          => 'Section Test',
            'type'          => 'entertainment',
            'content_type'  => 'movie',
            'platform'      => 'both',
            'content_limit' => 10,
            'sort_by'       => 'created_at',
            'position'      => 1,
            'is_active'     => true,
        ], $overrides));
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_access_homepage_builder(): void
    {
        $response = $this->actingAs($this->admin)
                         ->get('/app/homepage-builder');

        $response->assertStatus(200);
    }

    /** @test */
    public function section_is_created_with_correct_fields(): void
    {
        $section = $this->createSection([
            'name'         => 'Films Populaires',
            'type'         => 'entertainment',
            'content_type' => 'movie',
            'is_active'    => true,
        ]);

        $this->assertDatabaseHas('homepage_sections', [
            'name'         => 'Films Populaires',
            'type'         => 'entertainment',
            'content_type' => 'movie',
            'is_active'    => true,
        ]);

        $this->assertEquals('entertainment', $section->type);
        $this->assertTrue($section->is_active);
    }

    /** @test */
    public function section_can_be_toggled_active_inactive(): void
    {
        $section = $this->createSection(['is_active' => true]);

        // Désactiver
        $section->update(['is_active' => false]);
        $this->assertFalse($section->fresh()->is_active);

        // Réactiver
        $section->update(['is_active' => true]);
        $this->assertTrue($section->fresh()->is_active);
    }

    /** @test */
    public function sections_are_ordered_by_position(): void
    {
        $this->createSection(['position' => 3, 'name' => 'Section C']);
        $this->createSection(['position' => 1, 'name' => 'Section A']);
        $this->createSection(['position' => 2, 'name' => 'Section B']);

        $sections = HomepageSection::orderBy('position')->get();

        $this->assertEquals('Section A', $sections[0]->name);
        $this->assertEquals('Section B', $sections[1]->name);
        $this->assertEquals('Section C', $sections[2]->name);
    }

    /** @test */
    public function episode_ids_are_saved_and_retrieved_correctly(): void
    {
        $episodeIds = [101, 205, 307];

        $section = $this->createSection([
            'type'         => 'entertainment',
            'content_type' => 'tvshow',
            'episode_ids'  => $episodeIds,
        ]);

        $fresh = $section->fresh();
        $this->assertIsArray($fresh->episode_ids);
        $this->assertCount(3, $fresh->episode_ids);
        $this->assertEquals($episodeIds, $fresh->episode_ids);
    }

    /** @test */
    public function content_ids_are_stored_as_json(): void
    {
        $contentIds = [1, 2, 3, 4, 5];

        $section = $this->createSection(['content_ids' => $contentIds]);

        $fresh = $section->fresh();
        $this->assertIsArray($fresh->content_ids);
        $this->assertCount(5, $fresh->content_ids);
    }

    /** @test */
    public function section_deletion_removes_from_database(): void
    {
        $section = $this->createSection();
        $id      = $section->id;

        $section->delete();

        $this->assertDatabaseMissing('homepage_sections', ['id' => $id]);
    }

    /** @test */
    public function non_admin_cannot_access_homepage_builder(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)
                         ->get('/app/homepage-builder');

        // Doit refuser l'accès
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}

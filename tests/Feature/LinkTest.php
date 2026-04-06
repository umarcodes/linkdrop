<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_link(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/links', [
            'title' => 'My Website',
            'url' => 'https://example.com',
        ])->assertCreated()->assertJsonFragment(['title' => 'My Website']);
    }

    public function test_invalid_url_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/links', [
            'title' => 'Bad Link',
            'url' => 'ftp://example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors(['url']);
    }

    public function test_user_can_list_links(): void
    {
        $user = User::factory()->create();
        Link::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)->getJson('/api/links')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_toggle_active(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $this->actingAs($user)->putJson("/api/links/{$link->id}", [
            'is_active' => false,
        ])->assertOk()->assertJsonFragment(['is_active' => false]);
    }

    public function test_cannot_update_another_users_link(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $link = Link::factory()->create(['user_id' => $other->id]);

        $this->actingAs($user)->putJson("/api/links/{$link->id}", [
            'title' => 'Hijacked',
        ])->assertForbidden();
    }

    public function test_public_profile_returns_active_links_only(): void
    {
        $user = User::factory()->create(['username' => 'publicuser']);
        Link::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        Link::factory()->create(['user_id' => $user->id, 'is_active' => false]);

        $this->getJson('/api/p/publicuser')
            ->assertOk()
            ->assertJsonCount(1, 'links');
    }

    public function test_click_is_tracked(): void
    {
        $user = User::factory()->create(['username' => 'clicker']);
        $link = Link::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $this->postJson("/api/p/clicker/click/{$link->id}")
            ->assertOk();

        $this->assertDatabaseHas('link_clicks', ['link_id' => $link->id]);
    }
}

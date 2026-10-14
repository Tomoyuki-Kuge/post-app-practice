<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_post_as_themselves(): void
    {
        $user = User::factory()->create(['name' => '投稿者']);
        $category = Category::create(['name' => '技術メモ']);

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'title' => '新しい投稿',
            'category_id' => $category->id,
            'content' => '投稿本文です。',
            'user_id' => User::factory()->create()->id,
        ]);

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '新しい投稿',
            'content' => '投稿本文です。',
        ]);
    }

    public function test_post_with_empty_content_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => '技術メモ']);

        $response = $this->actingAs($user)->from(route('posts.index'))->post(route('posts.store'), [
            'title' => '本文なし',
            'category_id' => $category->id,
            'content' => '',
        ]);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_post_requires_an_existing_category_and_respects_field_limits(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('posts.index'))->post(route('posts.store'), [
            'title' => str_repeat('題', 256),
            'category_id' => 999,
            'content' => str_repeat('文', 141),
        ]);

        $response->assertSessionHasErrors(['title', 'category_id', 'content']);
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_guest_is_redirected_to_login_when_opening_timeline_or_posting(): void
    {
        $this->get(route('posts.index'))->assertRedirect(route('login'));
        $this->post(route('posts.store'))->assertRedirect(route('login'));
    }

    public function test_timeline_displays_post_form_and_new_post_at_the_top(): void
    {
        $user = User::factory()->create(['name' => 'タイムライン利用者']);
        $category = Category::create(['name' => '雑記']);
        $existingPost = Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '既存ポスト',
            'content' => '既存本文',
        ]);

        $this->actingAs($user)->post(route('posts.store'), [
            'title' => '新着ポスト',
            'category_id' => $category->id,
            'content' => '新着本文',
        ]);

        $response = $this->actingAs($user)->get(route('posts.index'));

        $response->assertOk();
        $response->assertSee('投稿者：タイムライン利用者');
        $response->assertSeeInOrder(['新着ポスト', '既存ポスト']);
        $response->assertSee('name="category_id"', false);
        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseHas('posts', ['id' => $existingPost->id]);
    }
}

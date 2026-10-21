<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_detail_displays_the_post_and_its_replies(): void
    {
        $author = User::factory()->create(['name' => 'ポスト作成者']);
        $replier = User::factory()->create(['name' => '返信者']);
        $post = $this->createPost($author);
        $reply = $post->replies()->create(['user_id' => $replier->id, 'content' => '返信本文です']);

        $response = $this->actingAs($author)->get(route('posts.show', $post));

        $response->assertOk()
            ->assertSee('ポストとリプライ')
            ->assertSee($post->title)
            ->assertSee($post->content)
            ->assertSee($reply->content)
            ->assertSee($replier->name);
    }

    public function test_authenticated_user_can_add_a_reply_to_the_detail_list(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $post = $this->createPost($author);

        $response = $this->actingAs($replier)->post(route('replies.store', $post), [
            'content' => '追加した返信',
        ]);

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('replies', [
            'post_id' => $post->id,
            'user_id' => $replier->id,
            'content' => '追加した返信',
        ]);
        $this->actingAs($replier)->get(route('posts.show', $post))->assertSee('追加した返信');
    }

    public function test_empty_reply_is_rejected_and_does_not_create_a_reply(): void
    {
        $author = User::factory()->create();
        $replier = User::factory()->create();
        $post = $this->createPost($author);

        $response = $this->actingAs($replier)->from(route('posts.show', $post))
            ->post(route('replies.store', $post), ['content' => '']);

        $response->assertRedirect(route('posts.show', $post))
            ->assertSessionHasErrors('content');
        $this->assertDatabaseCount('replies', 0);
    }

    public function test_user_can_delete_their_reply_but_not_another_users_reply(): void
    {
        $postAuthor = User::factory()->create();
        $replyAuthor = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = $this->createPost($postAuthor);
        $ownReply = $post->replies()->create(['user_id' => $replyAuthor->id, 'content' => '自分の返信']);
        $otherReply = $post->replies()->create(['user_id' => $otherUser->id, 'content' => '他人の返信']);

        $this->actingAs($replyAuthor)
            ->delete(route('replies.destroy', $ownReply))
            ->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseMissing('replies', ['id' => $ownReply->id]);

        $this->actingAs($replyAuthor)
            ->delete(route('replies.destroy', $otherReply))
            ->assertForbidden();
        $this->assertDatabaseHas('replies', ['id' => $otherReply->id]);
    }

    private function createPost(User $author): Post
    {
        $category = Category::create(['name' => '技術メモ']);

        return Post::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'テストポスト',
            'content' => 'ポストの本文です。',
        ]);
    }
}

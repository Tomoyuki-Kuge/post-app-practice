<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // 投稿と関連するユーザー・カテゴリ、カテゴリ一覧を取得して投稿一覧画面に渡す。
        $posts = Post::with(['user', 'category'])->latest()->orderByDesc('id')->get();
        $categories = Category::orderBy('id')->get();

        return view('posts.index', compact('posts', 'categories'));
    }

    public function show(Post $post)
    {
        $post->load(['user', 'category', 'replies.user']);

        return view('posts.show', compact('post'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string|max:140',
        ], [
            'content.max' => '本文は140字以内で入力してください。',
        ]);

        $request->user()->posts()->create($validated);

        return redirect()->route('posts.index');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $categories = Category::orderBy('id')->get();

        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:140',
            'category_id' => 'required|exists:categories,id',
        ], [
            'content.max' => '本文は140字以内で入力してください。',
        ]);

        $post->update($validated);

        return redirect()->route('posts.index');
    }

    public function destroy(Post $post)
    {
        // 投稿の削除権限を確認して投稿を削除し、投稿一覧へ戻す。
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}

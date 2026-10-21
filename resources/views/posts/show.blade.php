<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }} / つぶやき投稿アプリ</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Hiragino Sans", "Noto Sans JP", sans-serif; color: #0F1419; }
        .col { max-width: 600px; margin: 0 auto; min-height: 100vh; border-inline: 1px solid #EFF1F4; }
        header { padding: 1rem; border-bottom: 1px solid #EFF1F4; }
        header a { color: inherit; text-decoration: none; font-weight: 700; }
        h1 { font-size: 1.05rem; margin: 1rem 0 0; }
        .post, .reply, .composer { padding: 1rem; border-bottom: 1px solid #EFF1F4; }
        .meta { color: #5B6570; font-size: .85rem; margin-bottom: .6rem; }
        .post h2 { font-size: 1.2rem; margin: .4rem 0; }
        .content { line-height: 1.7; white-space: pre-wrap; overflow-wrap: anywhere; }
        .composer label { display: block; margin-bottom: .4rem; font-weight: 700; }
        textarea { width: 100%; min-height: 90px; padding: .7rem; border: 1px solid #D3D9DE; border-radius: 8px; font: inherit; }
        button { padding: .5rem 1rem; border: 0; border-radius: 999px; background: #0F1419; color: white; font: inherit; cursor: pointer; }
        .submit { display: block; margin: .7rem 0 0 auto; }
        .delete { background: white; color: #B93225; border: 1px solid #D3D9DE; }
        .error { margin-top: .35rem; color: #B93225; font-size: .85rem; }
        .section-title { padding: 1rem; font-size: 1rem; font-weight: 800; }
        .empty { padding: 0 1rem 1rem; color: #5B6570; }
    </style>
</head>
<body>
    <div class="col">
        <header>
            <a href="{{ route('posts.index') }}">← ホーム</a>
            <h1>ポストとリプライ</h1>
        </header>

        <main>
            <article class="post">
                <div class="meta">{{ $post->user->name }} ・ {{ $post->category->name }} ・ {{ $post->created_at->format('n月j日 H:i') }}</div>
                <h2>{{ $post->title }}</h2>
                <p class="content">{{ $post->content }}</p>
            </article>

            <form class="composer" action="{{ route('replies.store', $post) }}" method="POST">
                @csrf
                <label for="content">リプライを書く</label>
                <textarea id="content" name="content" maxlength="140" required>{{ old('content') }}</textarea>
                @error('content')
                    <p class="error" role="alert">{{ $message }}</p>
                @enderror
                <button class="submit" type="submit">リプライを送る</button>
            </form>

            <div class="section-title">リプライ一覧（{{ $post->replies->count() }}）</div>
            @forelse ($post->replies as $reply)
                <article class="reply">
                    <div class="meta">{{ $reply->user->name }} ・ {{ $reply->created_at->format('n月j日 H:i') }}</div>
                    <p class="content">{{ $reply->content }}</p>
                    @can('delete', $reply)
                        <form action="{{ route('replies.destroy', $reply) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="delete" type="submit">削除</button>
                        </form>
                    @endcan
                </article>
            @empty
                <p class="empty">まだリプライはありません。</p>
            @endforelse
        </main>
    </div>
</body>
</html>

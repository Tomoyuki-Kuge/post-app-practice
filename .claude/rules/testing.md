# テストのルール

## 配置

- PHPUnit の設定とテストスイート定義は `phpunit.xml` を参照します。
- リクエスト、認可、データベース、ビューなど、Laravel アプリケーションの振る舞いを確認するテストは `tests/Feature/` に置きます。
- アプリケーションから独立して確認できる単独のロジックのテストは `tests/Unit/` に置きます。

## 書き方

- テスト名は `test_` に続けて、確認する振る舞いが分かる snake_case で記述します。例: `test_users_cannot_edit_another_users_post`。
- Feature テストは `Tests\\TestCase` を使い、必要に応じて HTTP リクエストを実行してレスポンスを検証します。
- Unit テストは `PHPUnit\\Framework\\TestCase` を使い、対象ロジックをアプリケーション全体から切り離して検証します。
- 変更した機能の振る舞いをカバーするテストを追加または更新します。

## 実行

- Docker Desktop を起動し、テストは `./vendor/bin/sail artisan test` で実行します。
- カバレッジの数値基準は設けません。

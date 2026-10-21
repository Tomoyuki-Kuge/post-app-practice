#!/usr/bin/env bash

test_output=$(./vendor/bin/sail test 2>&1)
test_status=$?

if [ "$test_status" -ne 0 ]; then
    printf '%s\n' "$test_output" >&2
    printf '\n自動テストが失敗しました。出力を確認して原因を修正し、テストを再実行してください。\n' >&2
    exit 2
fi

exit 0

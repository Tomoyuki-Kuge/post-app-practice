#!/usr/bin/env bash

hook_input=$(cat)
hook_event=$(printf '%s' "$hook_input" | python3 -c 'import json, sys; print(json.load(sys.stdin).get("hook_event_name", ""))')
session_id=$(printf '%s' "$hook_input" | python3 -c 'import json, sys; print(json.load(sys.stdin).get("session_id", ""))')
project_root=$(git rev-parse --show-toplevel 2>/dev/null) || {
    printf 'Cannot find the project root; run the Sail test suite before finishing.\n' >&2
    exit 2
}

safe_session_id=${session_id//[^a-zA-Z0-9_-]/_}
test_marker="${TMPDIR:-/tmp}/codex-tests-required-${safe_session_id}"

if [ "$hook_event" = "PostToolUse" ]; then
    touch "$test_marker"
elif [ "$hook_event" = "Stop" ] && [ ! -f "$test_marker" ]; then
    printf '{"continue":true}\n'
    exit 0
fi

cd "$project_root" || {
    printf 'Cannot enter the project root; run the Sail test suite before finishing.\n' >&2
    exit 2
}

test_output=$(./vendor/bin/sail test 2>&1)
test_status=$?

if [ "$test_status" -ne 0 ]; then
    printf '%s\n' "$test_output" >&2
    printf '\n自動テストが失敗しました。出力を確認して原因を修正し、テストを再実行してください。\n' >&2
    exit 2
fi

if [ "$hook_event" = "Stop" ]; then
    rm -f "$test_marker"
    printf '{"continue":true}\n'
fi

exit 0

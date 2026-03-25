#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

cd "${PROJECT_ROOT}"

if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    exec vendor/bin/sail artisan boost:mcp
fi

exec php artisan boost:mcp

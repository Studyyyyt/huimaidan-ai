#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUN_BUILDS="${RUN_BUILDS:-0}"
CHECK_ENV="${CHECK_ENV:-0}"

run_step() {
  local title="$1"
  shift
  echo
  echo "==> ${title}"
  "$@"
}

run_in_dir() {
  local dir="$1"
  shift
  (cd "$dir" && "$@")
}

BACKEND_DIR="$ROOT_DIR/huimaidan/backend"
UNIAPP_DIR="$ROOT_DIR/huimaidan-uniapp"
ADMIN_DIR="$ROOT_DIR/huimaidan/admin"

for dir in "$BACKEND_DIR" "$UNIAPP_DIR" "$ADMIN_DIR"; do
  if [ ! -d "$dir" ]; then
    echo "Missing directory: $dir" >&2
    exit 1
  fi
done

run_step "Backend AI lint, contracts, and schema import" run_in_dir "$BACKEND_DIR" bash scripts/check_huimaidan_ai.sh

if [ "$CHECK_ENV" = "1" ]; then
  run_step "Backend AI environment configuration" run_in_dir "$BACKEND_DIR" bash scripts/check_huimaidan_ai_env.sh
else
  echo
  echo "Skipped environment configuration check. Set CHECK_ENV=1 to validate .env for deployment."
fi

run_step "Mini program AI/front-end contracts" run_in_dir "$UNIAPP_DIR" pnpm test:contracts
run_step "Mini program type check" run_in_dir "$UNIAPP_DIR" env CI=true pnpm type-check

run_step "Admin AI contracts" run_in_dir "$ADMIN_DIR" npm run test:contracts
run_step "Admin AI API syntax" run_in_dir "$ADMIN_DIR" node --check src/api/huimaidanAi.js
run_step "Admin AI route syntax" run_in_dir "$ADMIN_DIR" node --check src/router/modules/huimaidanAi.js

if [ "$RUN_BUILDS" = "1" ]; then
  run_step "Mini program mp-weixin build" run_in_dir "$UNIAPP_DIR" pnpm build:mp
  run_step "Admin production build" run_in_dir "$ADMIN_DIR" npm run build
else
  echo
  echo "Skipped build steps. Set RUN_BUILDS=1 to also run pnpm build:mp and npm run build."
fi

echo
echo "Huimaidan AI local acceptance checks passed"

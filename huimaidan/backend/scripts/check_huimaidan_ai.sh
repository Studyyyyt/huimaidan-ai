#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WORKSPACE_DIR="$(cd "$ROOT_DIR/../.." && pwd)"
PHP_IMAGE="${PHP_IMAGE:-php:7.4-cli}"
MYSQL_IMAGE="${MYSQL_IMAGE:-mysql:8.0}"

cd "$ROOT_DIR"

echo "==> PHP lint: AI files"
rg --files \
  app/common/repositories/huimaidan \
  app/common/dao/huimaidan \
  app/common/model/huimaidan \
  app/controller/admin/huimaidan \
  app/controller/api/huimaidan \
  app/command \
  crmeb/services/ai \
  tests/huimaidan \
  | rg '(Ai|MerchantTag|HuimaidanAi|Llm|Bailian|Claude|OpenAi)' \
  | while IFS= read -r file; do
      docker run --rm -v "$ROOT_DIR":/app -w /app "$PHP_IMAGE" php -l "$file" >/dev/null
    done

echo "==> PHP contract tests: AI"
docker run --rm -v "$WORKSPACE_DIR":/workspace -w /workspace/huimaidan/backend "$PHP_IMAGE" php tests/huimaidan/AiRouteContractTest.php
docker run --rm -v "$ROOT_DIR":/app -w /app "$PHP_IMAGE" php tests/huimaidan/AiResponseParserTest.php
docker run --rm -v "$ROOT_DIR":/app -w /app "$PHP_IMAGE" php tests/huimaidan/AiRecommendationContractTest.php
docker run --rm -v "$ROOT_DIR":/app -w /app "$PHP_IMAGE" php tests/huimaidan/AiNluContractTest.php

echo "==> MySQL syntax/import check: AI schema"
container="huimaidan_ai_sql_check_$$"
cleanup() {
  docker rm -f "$container" >/dev/null 2>&1 || true
}
trap cleanup EXIT

docker run -d --name "$container" \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=crmeb \
  "$MYSQL_IMAGE" \
  --character-set-server=utf8mb4 \
  --collation-server=utf8mb4_general_ci >/dev/null

ready=0
for _ in $(seq 1 60); do
  if docker exec "$container" mysql -uroot -proot -N -e "SELECT 1" >/dev/null 2>&1; then
    ready=1
    break
  fi
  sleep 2
done

if [ "$ready" -ne 1 ]; then
  docker logs "$container"
  exit 1
fi

docker exec -i "$container" mysql -uroot -proot crmeb < docs/sql/migrations/011_惠买单_AI推荐大脑表结构.sql
docker exec "$container" mysql -uroot -proot -N -e "SELECT COUNT(*) FROM crmeb.eb_huimaidan_ai_config;" | grep -q '^17$'

echo "Huimaidan AI checks passed"

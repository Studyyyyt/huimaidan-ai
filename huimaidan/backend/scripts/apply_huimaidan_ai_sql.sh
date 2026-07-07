#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ENV_FILE:-$ROOT_DIR/.env}"
WITH_TEST_DATA="${WITH_TEST_DATA:-0}"
MYSQL_BIN="${MYSQL_BIN:-mysql}"

SCHEMA_SQL="$ROOT_DIR/docs/sql/migrations/011_惠买单_AI推荐大脑表结构.sql"
TEST_DATA_SQL="$ROOT_DIR/docs/sql/migrations/012_惠买单_AI推荐大脑开发测试数据.sql"

normalize_key() {
  local key="$1"
  key="${key// /}"
  key="$(printf '%s' "$key" | tr '[:upper:]' '[:lower:]')"
  echo "$key"
}

trim() {
  local value="$1"
  value="${value#"${value%%[![:space:]]*}"}"
  value="${value%"${value##*[![:space:]]}"}"
  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"
  echo "$value"
}

env_file_value() {
  local wanted="$1"
  if [ ! -f "$ENV_FILE" ]; then
    echo ""
    return
  fi
  awk -v wanted="$wanted" '
    function trim(s) {
      gsub(/^[[:space:]]+|[[:space:]]+$/, "", s)
      gsub(/^["'"'"']|["'"'"']$/, "", s)
      return s
    }
    function normalize(s) {
      gsub(/[[:space:]]+/, "", s)
      return tolower(s)
    }
    BEGIN { section = ""; wanted = normalize(wanted) }
    {
      sub(/\r$/, "", $0)
      line = trim($0)
      if (line == "" || line ~ /^#/) next
      if (line ~ /^\[.*\]$/) {
        section = normalize(substr(line, 2, length(line) - 2))
        next
      }
      pos = index(line, "=")
      if (pos <= 0) next
      key = normalize(trim(substr(line, 1, pos - 1)))
      value = trim(substr(line, pos + 1))
      if (key == wanted || (section != "" && section "." key == wanted)) {
        print value
        exit
      }
    }
  ' "$ENV_FILE"
}

env_value() {
  local candidate normalized value
  for candidate in "$@"; do
    value="${!candidate-}"
    if [ -n "$value" ]; then
      echo "$value"
      return
    fi
    normalized="$(normalize_key "$candidate")"
    value="$(env_file_value "$normalized")"
    if [ -n "$value" ]; then
      echo "$value"
      return
    fi
  done
  echo ""
}

is_placeholder() {
  local value="$1"
  [ -z "$value" ] && return 0
  [[ "$value" == \#*\# ]] && return 0
  [[ "$value" == "your-"* ]] && return 0
  [[ "$value" == "xxx"* ]] && return 0
  return 1
}

require_value() {
  local label="$1"
  shift
  local value
  value="$(env_value "$@")"
  if is_placeholder "$value"; then
    echo "Missing database config: $label" >&2
    exit 1
  fi
  echo "$value"
}

if ! command -v "$MYSQL_BIN" >/dev/null 2>&1; then
  echo "mysql client is required. Set MYSQL_BIN=/path/to/mysql if needed." >&2
  exit 1
fi

if [ ! -f "$SCHEMA_SQL" ]; then
  echo "Missing schema SQL: $SCHEMA_SQL" >&2
  exit 1
fi

db_host="$(require_value "database.hostname" DATABASE_HOSTNAME database.hostname HOSTNAME)"
db_port="$(env_value DATABASE_HOSTPORT database.hostport HOSTPORT)"
db_port="${db_port:-3306}"
db_name="$(require_value "database.database" DATABASE_DATABASE database.database DATABASE)"
db_user="$(require_value "database.username" DATABASE_USERNAME database.username USERNAME)"
db_pass="$(env_value DATABASE_PASSWORD database.password PASSWORD)"

mysql_args=(--default-character-set=utf8mb4 -h "$db_host" -P "$db_port" -u "$db_user" "$db_name")
if [ -n "$db_pass" ]; then
  mysql_args=(--default-character-set=utf8mb4 -h "$db_host" -P "$db_port" -u "$db_user" "-p${db_pass}" "$db_name")
fi

echo "Importing Huimaidan AI schema into ${db_user}@${db_host}:${db_port}/${db_name}"
"$MYSQL_BIN" "${mysql_args[@]}" < "$SCHEMA_SQL"
echo "AI schema imported."

if [ "$WITH_TEST_DATA" = "1" ]; then
  if [ ! -f "$TEST_DATA_SQL" ]; then
    echo "Missing test data SQL: $TEST_DATA_SQL" >&2
    exit 1
  fi
  echo "Importing Huimaidan AI development test data."
  "$MYSQL_BIN" "${mysql_args[@]}" < "$TEST_DATA_SQL"
  echo "AI development test data imported."
else
  echo "Skipped development test data. Set WITH_TEST_DATA=1 only for local/test databases."
fi

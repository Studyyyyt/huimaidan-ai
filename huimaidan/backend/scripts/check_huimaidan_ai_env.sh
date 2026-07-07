#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ENV_FILE:-$ROOT_DIR/.env}"
STRICT="${STRICT:-0}"

errors=0
warnings=0

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

ok() {
  echo "OK      $1"
}

warn() {
  warnings=$((warnings + 1))
  echo "WARN    $1"
}

fail() {
  errors=$((errors + 1))
  echo "MISSING $1"
}

require_any() {
  local label="$1"
  shift
  local value
  value="$(env_value "$@")"
  if is_placeholder "$value"; then
    fail "$label"
  else
    ok "$label"
  fi
}

echo "Huimaidan AI environment check"
echo "ENV_FILE=$ENV_FILE"
echo

if [ ! -f "$ENV_FILE" ]; then
  fail ".env file exists"
else
  ok ".env file exists"
fi

installed="$(env_value INSTALLED)"
cache_driver="$(env_value CACHE_DRIVER cache.driver)"
driver="$(env_value HUIMAIDAN_AI_LLM_DRIVER)"
enabled="$(env_value HUIMAIDAN_AI_ENABLED)"

driver="${driver:-bailian}"
enabled="${enabled:-true}"
cache_driver="${cache_driver:-redis}"
enabled_lower="$(printf '%s' "$enabled" | tr '[:upper:]' '[:lower:]')"
installed_lower="$(printf '%s' "$installed" | tr '[:upper:]' '[:lower:]')"

if [[ "$enabled_lower" =~ ^(0|false|off|no)$ ]]; then
  warn "HUIMAIDAN_AI_ENABLED is disabled; AI interfaces will degrade or be unavailable"
else
  ok "HUIMAIDAN_AI_ENABLED enabled"
fi

case "$driver" in
  bailian|deepseek|claude)
    ok "HUIMAIDAN_AI_LLM_DRIVER=$driver"
    ;;
  *)
    fail "HUIMAIDAN_AI_LLM_DRIVER must be bailian, deepseek, or claude"
    ;;
esac

require_any "database.hostname" DATABASE_HOSTNAME database.hostname HOSTNAME
require_any "database.database" DATABASE_DATABASE database.database DATABASE
require_any "database.username" DATABASE_USERNAME database.username USERNAME

if [[ "$installed_lower" =~ ^(1|true|on|yes)$ ]] || [ "$cache_driver" = "redis" ]; then
  require_any "redis.redis_hostname" REDIS_REDIS_HOSTNAME redis.redis_hostname REDIS_HOSTNAME
  require_any "redis.port" REDIS_PORT redis.port PORT
else
  warn "cache.driver is not redis; AI session, rate limit, banner cache, and LLM circuit breaker should use Redis in production"
fi

case "$driver" in
  bailian)
    require_any "BAILIAN_APP_ID" BAILIAN_APP_ID
    require_any "BAILIAN_API_KEY" BAILIAN_API_KEY DASHSCOPE_API_KEY
    ;;
  deepseek)
    require_any "DEEPSEEK_API_KEY" DEEPSEEK_API_KEY
    ;;
  claude)
    require_any "CLAUDE_API_KEY" CLAUDE_API_KEY
    ;;
esac

for numeric in BAILIAN_TIMEOUT DEEPSEEK_TIMEOUT CLAUDE_TIMEOUT HUIMAIDAN_AI_LLM_RETRY_TIMES HUIMAIDAN_AI_LLM_FAIL_THRESHOLD HUIMAIDAN_AI_LLM_RECOVERY_SECONDS; do
  value="$(env_value "$numeric")"
  if [ -n "$value" ] && ! [[ "$value" =~ ^[0-9]+$ ]]; then
    warn "$numeric should be numeric when configured"
  fi
done

echo
if [ "$errors" -gt 0 ]; then
  echo "Huimaidan AI environment check failed: $errors missing item(s), $warnings warning(s)."
  exit 1
fi

if [ "$warnings" -gt 0 ] && [ "$STRICT" = "1" ]; then
  echo "Huimaidan AI environment check failed in STRICT mode: $warnings warning(s)."
  exit 1
fi

echo "Huimaidan AI environment check passed with $warnings warning(s)."

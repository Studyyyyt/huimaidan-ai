#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1:8324}"
TOKEN="${TOKEN:-}"
LATITUDE="${LATITUDE:-30.5728}"
LONGITUDE="${LONGITUDE:-104.0668}"
CITY_ID="${CITY_ID:-}"
CITY_NAME="${CITY_NAME:-成都}"
MESSAGE="${MESSAGE:-附近有包间适合聚餐吗}"

if ! command -v curl >/dev/null 2>&1; then
  echo "curl is required" >&2
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "php is required for JSON assertions" >&2
  exit 1
fi

query="latitude=${LATITUDE}&longitude=${LONGITUDE}"
if [ -n "$CITY_ID" ]; then
  query="${query}&city_id=${CITY_ID}"
else
  query="${query}&city_name=${CITY_NAME}"
fi

echo "==> AI banner smoke"
banner_response="$(curl -fsS "${BASE_URL%/}/api/huimaidan/ai/banner?${query}")"
php -r '
$payload = json_decode(stream_get_contents(STDIN), true);
if (!is_array($payload)) { fwrite(STDERR, "banner response is not JSON\n"); exit(1); }
$data = $payload["data"] ?? $payload;
foreach (["meal_type", "title", "subtitle", "background_color", "text_color"] as $key) {
    if (!array_key_exists($key, $data)) { fwrite(STDERR, "banner missing {$key}\n"); exit(1); }
}
echo "banner ok: " . $data["meal_type"] . "\n";
' <<< "$banner_response"

if [ -z "$TOKEN" ]; then
  echo "TOKEN is empty; skipped login-required chat/event smoke."
  echo "Set TOKEN to a valid mobile login token to test /api/huimaidan/ai/chat and /api/huimaidan/ai/event."
  exit 0
fi

export MESSAGE LATITUDE LONGITUDE CITY_ID CITY_NAME
chat_body="$(php -r '
$body = [
    "message" => getenv("MESSAGE"),
    "latitude" => (float)getenv("LATITUDE"),
    "longitude" => (float)getenv("LONGITUDE"),
];
if (getenv("CITY_ID") !== "") {
    $body["city_id"] = (int)getenv("CITY_ID");
} else {
    $body["city_name"] = getenv("CITY_NAME");
}
echo json_encode($body, JSON_UNESCAPED_UNICODE);
')"

echo "==> AI chat smoke"
chat_response="$(curl -fsS \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -X POST \
  -d "$chat_body" \
  "${BASE_URL%/}/api/huimaidan/ai/chat")"

parsed_chat="$(php -r '
$payload = json_decode(stream_get_contents(STDIN), true);
if (!is_array($payload)) { fwrite(STDERR, "chat response is not JSON\n"); exit(1); }
$data = $payload["data"] ?? $payload;
foreach (["session_id", "type", "content"] as $key) {
    if (!array_key_exists($key, $data)) { fwrite(STDERR, "chat missing {$key}\n"); exit(1); }
}
if (!isset($data["content"]["text"])) { fwrite(STDERR, "chat missing content.text\n"); exit(1); }
$merchants = $data["content"]["merchants"] ?? [];
$firstMerId = is_array($merchants) && isset($merchants[0]["mer_id"]) ? (int)$merchants[0]["mer_id"] : 0;
echo json_encode([
    "log_id" => (int)($data["log_id"] ?? 0),
    "session_id" => (string)$data["session_id"],
    "mer_id" => $firstMerId,
], JSON_UNESCAPED_UNICODE);
' <<< "$chat_response")"

echo "chat ok: ${parsed_chat}"

log_id="$(php -r '$data = json_decode($argv[1], true); echo (int)($data["log_id"] ?? 0);' "$parsed_chat")"
session_id="$(php -r '$data = json_decode($argv[1], true); echo (string)($data["session_id"] ?? "");' "$parsed_chat")"
mer_id="$(php -r '$data = json_decode($argv[1], true); echo (int)($data["mer_id"] ?? 0);' "$parsed_chat")"

if [ "$log_id" -le 0 ] || [ "$mer_id" -le 0 ]; then
  echo "chat returned no log_id or merchant; skipped event smoke."
  exit 0
fi

echo "==> AI event smoke"
event_body="$(LOG_ID="$log_id" SESSION_ID="$session_id" MER_ID="$mer_id" php -r '
echo json_encode([
    "log_id" => (int)getenv("LOG_ID"),
    "session_id" => getenv("SESSION_ID"),
    "event" => "detail",
    "mer_id" => (int)getenv("MER_ID"),
], JSON_UNESCAPED_UNICODE);
')"

event_response="$(curl -fsS \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -X POST \
  -d "$event_body" \
  "${BASE_URL%/}/api/huimaidan/ai/event")"

php -r '
$payload = json_decode(stream_get_contents(STDIN), true);
if (!is_array($payload)) { fwrite(STDERR, "event response is not JSON\n"); exit(1); }
$data = $payload["data"] ?? $payload;
if (!array_key_exists("updated", $data)) { fwrite(STDERR, "event missing updated\n"); exit(1); }
echo "event ok\n";
' <<< "$event_response"

echo "Huimaidan AI live smoke passed"

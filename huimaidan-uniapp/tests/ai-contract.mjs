import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

const root = resolve(import.meta.dirname, '..')

function read(path) {
  return readFileSync(resolve(root, path), 'utf8')
}

const aiApi = read('src/api/ai.ts')
const aiChat = read('src/pages/ai-chat/index.vue')
const huimaidanApi = read('src/api/huimaidan.ts')
const banner = read('src/components/AiSmartBanner.vue')
const routerConfig = read('src/router/config.ts')
const routerInterceptor = read('src/router/interceptor.ts')
const checkout = read('src/pages/payment/checkout.vue')

for (const forbidden of [
  'dashscope.aliyuncs.com',
  'BAILIAN_API_KEY',
  'BAILIAN_APP_ID',
  'mockPostAiChat',
]) {
  assert.equal(aiApi.includes(forbidden), false, `frontend AI API must not contain ${forbidden}`)
}

for (const endpoint of [
  "'/api/huimaidan/ai/banner'",
  "'/api/huimaidan/ai/chat'",
  "'/api/huimaidan/ai/event'",
]) {
  assert.equal(aiApi.includes(endpoint), true, `frontend AI API missing backend endpoint ${endpoint}`)
}

assert.match(aiApi, /city_name\?: string/, 'AI API params must support city_name fallback')
assert.match(aiChat, /params\.city_name = locationStore\.city/, 'AI chat must send city_name when city_id is unavailable')
assert.match(banner, /params\.city_name = locationStore\.city/, 'AI banner must send city_name when city_id is unavailable')
assert.equal(aiApi.includes('timeout: 75000'), true, 'AI chat request timeout must allow Bailian cold starts')
assert.equal(aiChat.includes('80000'), true, 'AI chat loading timeout must be longer than request timeout')
assert.equal(aiApi.includes('normalizeMediaUrl'), true, 'AI API must normalize merchant image URLs for mini program image loading')
assert.equal(huimaidanApi.includes('normalizeMediaUrl'), true, 'Huimaidan merchant API must normalize image URLs for mini program image loading')

assert.equal(routerConfig.includes("'/pages/ai-chat/index'"), true, 'AI chat page must be in FORCE_LOGIN_PATH_LIST')
assert.equal(routerInterceptor.includes('FORCE_LOGIN_PATH_LIST.includes(path)'), true, 'route interceptor must enforce FORCE_LOGIN_PATH_LIST')

for (const field of [
  'merchant.mer_name',
  'merchant.recommend_reason',
  'merchant.distance',
  'merchant.discount_label',
  'merchant.rating',
  'merchant.price_per_person_text',
  'merchant.mer_address',
]) {
  assert.equal(aiChat.includes(field), true, `AI merchant card missing ${field}`)
}

for (const action of [
  "reportAiEvent(source, 'detail'",
  "reportAiEvent(source, 'navigate'",
  "reportAiEvent(source, 'click'",
  "reportAiEvent(msg, 'feedback'",
]) {
  assert.equal(aiChat.includes(action), true, `AI chat missing event reporting: ${action}`)
}

assert.match(aiChat, /url: `\/pages\/merchant\/detail\?id=\$\{merchant\.mer_id\}&ai_log_id=\$\{source\.log_id \|\| ''\}`/, 'AI detail navigation must carry ai_log_id')
assert.match(aiChat, /open-type="share"/, 'AI merchant card must support share')

assert.equal(checkout.includes('options?.ai_log_id'), true, 'checkout page must read ai_log_id')
assert.equal(checkout.includes("event: 'order'"), true, 'checkout page must report AI order attribution')
assert.equal(checkout.includes('postAiEvent'), true, 'checkout page must call postAiEvent')

assert.equal(banner.includes("url: '/pages/ai-chat/index'"), true, 'AI banner must navigate to chat page')

console.log('ai-contract passed')

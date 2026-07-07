/**
 * AI 推荐精简测试脚本（用于验证 LLM 参与效果）
 * 用法：node scripts/ai-chat-test-mini.mjs <token> [latitude] [longitude]
 */

const TOKEN = process.argv[2] || ''
const LATITUDE = parseFloat(process.argv[3]) || undefined
const LONGITUDE = parseFloat(process.argv[4]) || undefined
const BASE_URL = 'http://127.0.0.1:8324'

const testCases = [
  { name: '基础品类', message: '想吃火锅' },
  { name: '场景+设施', message: '适合朋友聚餐有包间的地方' },
  { name: '语义理解', message: '性价比高一点的火锅' },
]

async function chat(message) {
  const body = {
    message,
    city_id: 0,
    city_name: '呼和浩特市',
  }
  if (LATITUDE !== undefined && LONGITUDE !== undefined) {
    body.latitude = LATITUDE
    body.longitude = LONGITUDE
  }

  const res = await fetch(`${BASE_URL}/api/huimaidan/ai/chat`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${TOKEN}`,
    },
    body: JSON.stringify(body),
  })

  const data = await res.json()
  if (data.status !== 200) {
    throw new Error(`请求失败: ${JSON.stringify(data)}`)
  }
  return data.data
}

async function main() {
  if (!TOKEN) {
    console.error('请提供 token')
    process.exit(1)
  }

  for (const tc of testCases) {
    console.log(`\n===== ${tc.name}: "${tc.message}" =====`)
    const start = Date.now()
    const result = await chat(tc.message)
    console.log(`耗时: ${Date.now() - start}ms | 降级: ${result.degraded ? '是' : '否'}`)
    console.log('意图标签:', JSON.stringify(result.content?.intent_tags, null, 2))
    console.log('推荐理由:', result.content?.text)
    console.log('推荐商户:')
    ;(result.content?.merchants || []).forEach((m, idx) => {
      console.log(`  #${idx + 1} [${m.mer_id}] ${m.mer_name} | ${m.distance || '-'} | ${m.discount_label || '无优惠'}`)
    })
  }
}

main()

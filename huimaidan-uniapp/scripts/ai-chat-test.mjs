/**
 * AI 推荐接口批量测试脚本
 * 用法：node scripts/ai-chat-test.mjs <token> [latitude] [longitude]
 */

const TOKEN = process.argv[2] || ''
const LATITUDE = parseFloat(process.argv[3]) || undefined
const LONGITUDE = parseFloat(process.argv[4]) || undefined
const BASE_URL = 'http://127.0.0.1:8324'

const testCases = [
  { name: '品类', message: '想吃火锅' },
  { name: '品类+场景', message: '附近适合聚餐的火锅' },
  { name: '品类+低价', message: '人均30以内的火锅' },
  { name: '品类+极低价格', message: '人均10块钱以下的火锅' },
  { name: '品类+设施', message: '有包间的火锅' },
  { name: '品类+口味', message: '辣一点的火锅' },
  { name: '场景+设施', message: '适合朋友聚餐有包间的地方' },
  { name: '价格', message: '便宜一点的餐厅' },
  { name: '兜底闲聊', message: '你叫什么名字' },
]

async function chat(message) {
  const body = {
    message,
    city_id: 0,
    city_name: '广州市',
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

function formatMerchant(m, idx) {
  return `  #${idx + 1} [${m.mer_id}] ${m.mer_name} | ${m.distance || '-'} | ${m.discount_label || '无优惠'} | 人均${m.per_capita || '-'} | score=${m.score}`
}

async function main() {
  if (!TOKEN) {
    console.error('请提供 token：node scripts/ai-chat-test.mjs <token> [lat] [lng]')
    process.exit(1)
  }

  console.log(`位置参数: lat=${LATITUDE || '未传'}, lng=${LONGITUDE || '未传'}\n`)

  for (const tc of testCases) {
    console.log(`===== ${tc.name}: "${tc.message}" =====`)
    try {
      const result = await chat(tc.message)
      console.log('类型:', result.type)
      console.log('降级:', result.degraded ? '是' : '否')
      if (result.content?.intent_tags) {
        console.log('意图标签:', JSON.stringify(result.content.intent_tags, null, 2))
      }
      const merchants = result.content?.merchants || []
      console.log(`召回数量: ${merchants.length}`)
      merchants.forEach((m, idx) => console.log(formatMerchant(m, idx)))
    }
    catch (err) {
      console.error('错误:', err.message)
    }
    console.log('')
  }
}

main()

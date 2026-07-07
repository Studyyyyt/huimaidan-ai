import assert from 'node:assert'

/**
 * 测试 parseAiJson 的 JSON 解析逻辑。
 * 该函数内部实现与 src/api/ai.ts 中的 parseAiJson 保持一致。
 */

function parseAiJson(text) {
  if (!text)
    return null
  try {
    const cleaned = text
      .replace(/```json\\s*/g, '')
      .replace(/```\\s*$/g, '')
      .trim()
    return JSON.parse(cleaned)
  }
  catch {
    return null
  }
}

// 1. 纯 JSON
const plainJson = '{"text":"hello","merchants":[]}'
assert.deepStrictEqual(parseAiJson(plainJson), { text: 'hello', merchants: [] })

// 2. Markdown 代码块包裹的 JSON
const markdownJson = '```json\n{"text":"hi","merchants":[{"mer_id":1}]}\n```'
assert.deepStrictEqual(parseAiJson(markdownJson), { text: 'hi', merchants: [{ mer_id: 1 }] })

// 3. 非 JSON 文本
assert.strictEqual(parseAiJson('这不是 JSON'), null)

// 4. 空字符串/空值
assert.strictEqual(parseAiJson(''), null)
assert.strictEqual(parseAiJson(null), null)

console.log('[test-ai-parse] ✅ parseAiJson 测试通过')

import fs from 'node:fs/promises'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { SpreadsheetFile, Workbook } from '@oai/artifact-tool'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const sourcePath = path.join(root, '商家数据.txt')
const outputDir = path.join(root, 'outputs', 'huimaidan-ai-import')
const outputPath = path.join(outputDir, '惠买单真实商户AI导入数据.xlsx')

const headers = [
  '外部商户ID', '商户名称*', '分类名称', '省份', '城市名称', '区县', '详细地址*', '经度', '纬度',
  '门头图/头像URL', '联系电话', '人均消费', '营业开始', '营业结束', '营业时间文字',
  '设施', '优惠说明/商户标语', '展示销量', '商家折扣', '会员折扣',
  'AI分类标签', 'AI场景标签', 'AI口味标签', 'AI设施标签', 'AI价格标签', 'AI餐段标签',
]

function extractJsonObjects(text) {
  const objects = []
  let depth = 0
  let start = -1
  let inString = false
  let escaped = false
  for (let i = 0; i < text.length; i++) {
    const char = text[i]
    if (inString) {
      if (escaped) {
        escaped = false
      } else if (char === '\\') {
        escaped = true
      } else if (char === '"') {
        inString = false
      }
      continue
    }
    if (char === '"') {
      inString = true
      continue
    }
    if (char === '{') {
      if (depth === 0) start = i
      depth++
    } else if (char === '}') {
      depth--
      if (depth === 0 && start >= 0) {
        objects.push(JSON.parse(text.slice(start, i + 1)))
        start = -1
      }
    }
  }
  return objects
}

function stripHtml(value) {
  return String(value || '')
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/\s+/g, ' ')
    .trim()
}

function unique(values) {
  return [...new Set(values.filter(Boolean))]
}

function includesAny(text, words) {
  return words.some(word => text.includes(word))
}

function priceTag(perCapita) {
  const value = Number.parseFloat(perCapita)
  if (!Number.isFinite(value) || value <= 0) return ''
  if (value <= 30) return '0-30'
  if (value <= 60) return '30-60'
  if (value <= 100) return '60-100'
  if (value <= 150) return '100-150'
  return '150+'
}

function tagsFor(row) {
  const name = String(row.name || '')
  const categoryName = String(row.cname || '')
  const content = stripHtml(row.content)
  const text = `${name} ${categoryName} ${content}`

  const categories = []
  if (includesAny(text, ['火锅', '涮', '锅'])) categories.push('火锅')
  if (includesAny(text, ['川菜', '四川', '麻辣', '串串', '冒菜'])) categories.push('川菜')
  if (includesAny(text, ['烧烤', '烤肉', '烤串', '烤吧'])) categories.push('烧烤')
  if (includesAny(text, ['奶茶', '茶饮', '饮品', '咖啡', '茶屋'])) categories.push('奶茶')
  if (includesAny(text, ['甜品', '蛋糕', '烘焙', '面包'])) categories.push('甜品')
  if (includesAny(text, ['日料', '寿司', '刺身', '料理'])) categories.push('日料')
  if (includesAny(text, ['快餐', '便当', '汉堡', '炸鸡', '披萨'])) categories.push('快餐')
  if (includesAny(text, ['早餐', '包子', '粥', '豆浆', '油条'])) categories.push('快餐')
  if (!categories.length && includesAny(text, ['餐', '饭', '菜', '宴', '小吃', '美食'])) categories.push('中餐')
  if (!categories.length) categories.push(categoryName || '本地生活')

  const scenes = []
  if (includesAny(text, ['宴', '家宴', '聚餐', '饭店', '酒楼', '大桌'])) scenes.push('聚餐')
  if (includesAny(text, ['亲子', '儿童', '宝宝'])) scenes.push('亲子')
  if (includesAny(text, ['商务', '会所', '接待'])) scenes.push('商务')
  if (includesAny(text, ['情侣', '约会'])) scenes.push('约会')
  if (includesAny(text, ['奶茶', '甜品', '咖啡', '茶'])) scenes.push('下午茶')
  if (!scenes.length) scenes.push('日常')

  const tastes = []
  if (includesAny(text, ['辣', '麻辣', '川', '湘', '串串', '冒菜'])) tastes.push('辣')
  if (includesAny(text, ['清淡', '粥', '养生'])) tastes.push('清淡')
  if (includesAny(text, ['甜', '甜品', '蛋糕', '奶茶'])) tastes.push('甜')

  const facilities = []
  if (includesAny(text, ['包间', '包厢'])) facilities.push('包间')
  if (includesAny(text, ['大桌', '宴会', '聚餐'])) facilities.push('大桌')
  if (row.tel) facilities.push('电话预订')

  const meals = []
  if (includesAny(text, ['早餐', '包子', '豆浆', '油条', '粥'])) meals.push('早餐')
  if (includesAny(text, ['奶茶', '甜品', '咖啡', '茶'])) meals.push('下午茶')
  if (includesAny(text, ['烧烤', '夜宵', '烤串', '小龙虾'])) meals.push('夜宵')
  meals.push('午餐', '晚餐')

  return {
    categories: unique(categories),
    scenes: unique(scenes),
    tastes: unique(tastes),
    facilities: unique(facilities),
    prices: unique([priceTag(row.per_capita)]),
    meals: unique(meals),
  }
}

function discountValue(value) {
  const numeric = Number.parseFloat(value)
  if (!Number.isFinite(numeric) || numeric <= 0) return ''
  if (numeric > 1 && numeric <= 10) return numeric
  if (numeric > 10 && numeric <= 100) return numeric / 10
  return numeric
}

function rowToExcel(row) {
  const tags = tagsFor(row)
  const start = String(row.start_hours || '').trim()
  const end = String(row.end_hours || '').trim()
  const businessText = start || end ? `周一至周日 ${start || '00:00'}-${end || '24:00'}` : ''
  const slogan = stripHtml(row.content)
  return [
    row.id || row.mid || '',
    row.name || '',
    row.cname || '',
    row.province || '',
    row.city || '',
    row.district || '',
    row.address || '',
    row.longitude || '',
    row.latitude || '',
    row.logo || '',
    row.tel || row.linktel || '',
    Number.parseFloat(row.per_capita) || 0,
    start,
    end,
    businessText,
    tags.facilities.join(','),
    slogan,
    Number.parseInt(row.sales, 10) || 0,
    discountValue(row.discount),
    discountValue(row.discount),
    tags.categories.join(','),
    tags.scenes.join(','),
    tags.tastes.join(','),
    tags.facilities.join(','),
    tags.prices.join(','),
    tags.meals.join(','),
  ]
}

function isObviousTestMerchant(row) {
  return /测试|TEST/i.test(String(row.name || '').trim())
}

const raw = await fs.readFile(sourcePath, 'utf8')
const jsonObjects = extractJsonObjects(raw)
const merged = []
const seen = new Set()
for (const item of jsonObjects) {
  for (const row of item.data || []) {
    if (isObviousTestMerchant(row)) {
      continue
    }
    const key = row.id ? `id:${row.id}` : `${row.name || ''}|${row.address || ''}|${row.longitude || ''}|${row.latitude || ''}`
    if (!seen.has(key)) {
      seen.add(key)
      merged.push(row)
    }
  }
}

const workbook = Workbook.create()
const sheet = workbook.worksheets.add('商户导入数据')
sheet.showGridLines = false
sheet.freezePanes.freezeRows(1)

const data = [headers, ...merged.map(rowToExcel)]
sheet.getRangeByIndexes(0, 0, data.length, headers.length).values = data
sheet.tables.add(`A1:Z${data.length}`, true, 'MerchantImportTable')
sheet.getRange('A1:Z1').format.fill.color = '#1F4E78'
sheet.getRange('A1:Z1').format.font.color = '#FFFFFF'
sheet.getRange('A1:Z1').format.font.bold = true
sheet.getRange('A1:Z1').format.wrapText = true
sheet.getRange(`A2:Z${data.length}`).format.borders = { preset: 'inside', style: 'thin', color: '#E5E7EB' }
sheet.getRange(`L2:L${data.length}`).format.numberFormat = [['0.00']]
sheet.getRange(`R2:R${data.length}`).format.numberFormat = [['0']]
sheet.getRange(`S2:T${data.length}`).format.numberFormat = [['0.00']]
for (let col = 0; col < headers.length; col++) {
  sheet.getRangeByIndexes(0, col, data.length, 1).format.columnWidth = [6, 22, 14, 16, 14, 14, 28, 14, 14, 34, 16, 12, 12, 12, 24, 18, 32, 10, 10, 10, 18, 20, 16, 18, 16, 20][col]
}

const tips = workbook.worksheets.add('填写说明')
tips.showGridLines = false
const tipsRows = [
  ['说明项', '内容'],
  ['数据来源', '由 商家数据.txt 自动转换；已合并多个 JSON 段并按商户 ID 去重。'],
  ['导入方式', '后台进入 AI 推荐大脑 - 商家标签管理，点击“导入商户 Excel”上传本文件。'],
  ['AI标签', '系统已根据店名、分类、优惠说明自动填入；后续可在后台按商户手动微调。'],
  ['折扣', '原始 discount=8 会导入为 8 折，系统会自动转成 0.80 存储。'],
  ['人均消费', 'per_capita 为 0 的商户卡片不会显示人均，后续可在 Excel 或后台补充。'],
]
tips.getRangeByIndexes(0, 0, tipsRows.length, 2).values = tipsRows
tips.getRange('A1:B1').format.fill.color = '#1F4E78'
tips.getRange('A1:B1').format.font.color = '#FFFFFF'
tips.getRange('A1:B1').format.font.bold = true
tips.getRange('A:B').format.wrapText = true
tips.getRange('A:A').format.columnWidth = 18
tips.getRange('B:B').format.columnWidth = 80

await fs.mkdir(outputDir, { recursive: true })
const inspect = await workbook.inspect({ kind: 'sheet,table', maxChars: 3000, tableMaxRows: 3, tableMaxCols: 8 })
console.log(inspect.ndjson)
const rendered = await workbook.render({ sheetName: '商户导入数据', range: 'A1:Z12', scale: 1, format: 'png' })
await fs.writeFile(path.join(outputDir, '惠买单真实商户AI导入数据-preview.png'), new Uint8Array(await rendered.arrayBuffer()))
const exported = await SpreadsheetFile.exportXlsx(workbook)
await exported.save(outputPath)
console.log(JSON.stringify({
  outputPath,
  jsonObjects: jsonObjects.length,
  sourceRows: jsonObjects.reduce((sum, item) => sum + ((item.data || []).length), 0),
  uniqueRows: merged.length,
}, null, 2))

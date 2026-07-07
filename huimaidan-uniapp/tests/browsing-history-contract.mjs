import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import process from 'node:process'
import ts from 'typescript'

const root = resolve(import.meta.dirname, '..')
const read = path => readFileSync(resolve(root, path), 'utf8')

async function importTsModule(path) {
  const source = read(path)
  const { outputText } = ts.transpileModule(source, {
    compilerOptions: {
      module: ts.ModuleKind.ESNext,
      target: ts.ScriptTarget.ES2022,
    },
  })
  return import(`data:text/javascript;charset=utf-8,${encodeURIComponent(outputText)}`)
}

const api = read('src/api/huimaidan.ts')
assert.match(api, /\/api\/huimaidan\/user\/merchant_history/, '浏览足迹列表必须对接惠买单店铺历史接口')
assert.match(api, /store_group_id\?: number/, '浏览足迹列表参数必须支持店铺分组筛选')
assert.match(api, /\/api\/huimaidan\/user\/merchant_history\/delete\/\$\{id\}/, '单条删除必须传 history_id 到惠买单接口')
assert.match(api, /\/api\/huimaidan\/user\/merchant_history\/batch_delete/, '批量删除/清空必须对接惠买单接口')
assert.doesNotMatch(api, /\/api\/user\/history/, '浏览足迹不能继续使用 CRMEB 商品浏览历史接口')

const page = read('src/pages/browsing-history/browsing-history.vue')
assert.match(page, /getErrorMessage/, '历史页必须透传后端错误文案，不能固定隐藏为通用错误')
assert.doesNotMatch(page, /pageError\.value = '获取浏览记录失败'/, '历史页不能把后端错误静默替换为固定文案')
assert.match(page, /topSafeHeight/, '自定义导航页必须计算顶部安全高度，避免筛选栏压住返回按钮或胶囊')
assert.match(page, /page-top-safe/, '浏览足迹页必须渲染顶部安全占位')
assert.match(page, /shopListHeight/, '列表高度必须扣除顶部安全占位和筛选栏高度')
assert.match(page, /params\.store_group_id = activeCategory\.value as number/, '选择店铺分组后必须把 store_group_id 传给历史接口')
assert.match(page, /categoryBreadcrumb\.value\.push/, '点击有子分类的分组时必须进入下一级，不能直接关闭面板查询父级')

async function main() {
  const { mapBrowsingHistoryItem } = await importTsModule('src/pages/browsing-history/browsing-history.helpers.ts')

  assert.deepEqual(
    mapBrowsingHistoryItem({
      history_id: 12,
      mer_id: 1001,
      browseTime: '2026-06-18 14:30:00',
      visitCount: 3,
      shop: {
        mer_id: 1001,
        mer_name: 'TECHNO ｜【酒吧聚会】酒杯',
        mer_avatar: 'https://example.com/bar.jpg',
        category_name: '美食餐饮 中餐',
        sales_text: '半年售35万+',
        phone: '157 8028 2354',
        rating: 4.0,
        distance: '128m',
        discount_label: '8.0折',
        price_per_person_text: '人均 ¥88',
      },
    }),
    {
      id: 12,
      merchantId: 1001,
      name: 'TECHNO ｜【酒吧聚会】酒杯',
      image: 'https://example.com/bar.jpg',
      salesText: '半年售35万+',
      phone: '157 8028 2354',
      visitedAt: '2026-06-18 14:30:00',
      visitCount: 3,
      rating: 4,
      distance: '128m',
      discountLabel: '8.0折',
      categoryName: '美食餐饮 中餐',
      pricePerPersonText: '人均 ¥88',
    },
  )

  console.log('browsing-history-contract passed')
}

main().catch((error) => {
  console.error(error)
  process.exitCode = 1
})

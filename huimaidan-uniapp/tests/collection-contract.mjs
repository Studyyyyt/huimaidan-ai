import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

const root = resolve(import.meta.dirname, '..')
const read = path => readFileSync(resolve(root, path), 'utf8')

const api = read('src/api/huimaidan.ts')
assert.match(api, /store_group_id\?: number/, '收藏列表参数必须支持店铺分组筛选')
assert.match(api, /getCollectionList\(params\?: ICollectionListParams\)/, '收藏列表接口必须接收筛选参数')

const page = read('src/pages/collection/collection.vue')
assert.match(page, /params\.store_group_id = activeCategory\.value as number/, '选择店铺分组后必须把 store_group_id 传给收藏接口')
assert.match(page, /function handleCategoryTap[\s\S]*?findCategoryInTree[\s\S]*?isCategoryPanelOpen\.value = true[\s\S]*?function handleAllCategoryTap/, '点击顶部有子级分类时必须展开下级分类面板')
assert.match(page, /categoryBreadcrumb\.value\.push/, '点击有子分类的分组时必须进入下一级，不能直接关闭面板查询父级')
assert.match(page, /currentSubCategories\.value = item\.children\.map/, '分类面板必须把子级分类渲染出来')
assert.match(page, /class="collection-category-section bg-white"/, '分类面板外层必须提供定位上下文')
assert.match(page, /\.collection-category-section[\s\S]*?position: relative/, '分类面板绝对定位必须相对分类区域，不能跑到页面外')

console.log('collection-contract passed')

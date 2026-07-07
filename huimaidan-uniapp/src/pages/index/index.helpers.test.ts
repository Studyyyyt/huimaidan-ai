import assert from 'node:assert/strict'
import {
  buildCategoryPanelItems,
  buildStickySearchBarLayout,
  getCategoryPanelAllTargetId,
} from './index.helpers.ts'

assert.deepEqual(
  buildStickySearchBarLayout({
    statusBarHeight: 47,
    safeAreaRight: 0,
    windowWidth: 390,
    menuButtonLeft: 298,
  }),
  {
    paddingTop: 47,
    paddingRight: 104,
    paddingBottom: 8,
    paddingLeft: 12,
  },
)

assert.deepEqual(
  buildStickySearchBarLayout({
    statusBarHeight: 24,
    safeAreaRight: 0,
  }),
  {
    paddingTop: 24,
    paddingRight: 16,
    paddingBottom: 8,
    paddingLeft: 12,
  },
)

const categoryTree = [
  {
    store_group_id: 1,
    value: 1,
    name: '美食餐饮',
    label: '美食餐饮',
    children: [
      { store_group_id: 11, value: 11, name: '湘菜', label: '湘菜' },
      { store_group_id: 12, value: 12, name: '粤菜', label: '粤菜' },
    ],
  },
  {
    store_group_id: 2,
    value: 2,
    name: '生活娱乐',
    label: '生活娱乐',
  },
]

assert.deepEqual(
  buildCategoryPanelItems(categoryTree, 11).map(item => item.name),
  ['湘菜', '粤菜'],
)
assert.equal(getCategoryPanelAllTargetId(categoryTree, 11), 1)

assert.deepEqual(
  buildCategoryPanelItems(categoryTree, 2).map(item => item.name),
  [],
)

import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

const root = resolve(import.meta.dirname, '..')

function read(path) {
  return readFileSync(resolve(root, path), 'utf8')
}

const api = read('src/api/huimaidanAi.js')
const route = read('src/router/modules/huimaidanAi.js')
const routerIndex = read('src/router/index.js')
const page = read('src/views/huimaidan/ai/index.vue')

const requiredApiFns = [
  'aiTagsApi',
  'aiTagSaveApi',
  'aiTagImportApi',
  'aiTagDeleteApi',
  'aiMerchantTagsApi',
  'aiMerchantTagsSaveApi',
  'aiMerchantTagsInitApi',
  'aiMerchantImportTemplateUrl',
  'aiMerchantImportApi',
  'aiBannersApi',
  'aiBannerSaveApi',
  'aiBannerDeleteApi',
  'aiConfigsApi',
  'aiConfigSaveApi',
  'aiConfigDeleteApi',
  'aiLogsApi',
]

for (const fn of requiredApiFns) {
  assert.equal(api.includes(`export function ${fn}`), true, `missing API function ${fn}`)
  assert.equal(page.includes(fn), true, `AI admin page must use ${fn}`)
}

for (const endpoint of [
  'huimaidan/ai/tags',
  'huimaidan/ai/tag/save',
  'huimaidan/ai/tag/import',
  'huimaidan/ai/tag/delete/',
  'huimaidan/ai/merchant_tags/',
  'huimaidan/ai/merchant_tags/init',
  'huimaidan/ai/merchant_import/template',
  'huimaidan/ai/merchant_import',
  'huimaidan/ai/banners',
  'huimaidan/ai/banner/save',
  'huimaidan/ai/banner/delete/',
  'huimaidan/ai/configs',
  'huimaidan/ai/config/save',
  'huimaidan/ai/config/delete/',
  'huimaidan/ai/logs',
]) {
  assert.equal(api.includes(endpoint), true, `missing Admin endpoint ${endpoint}`)
}

assert.equal(route.includes("title: 'AI 推荐大脑'"), true, 'AI admin route title missing')
assert.equal(route.includes('@/views/huimaidan/standaloneRedirect'), true, 'AI admin route must load standalone redirect page')
assert.equal(routerIndex.includes('huimaidanAiRouter'), true, 'AI admin route module must be registered')

for (const tab of [
  'AI标签管理',
  '商户AI标签',
  'AI Banner配置',
  '推荐参数',
  '推荐日志',
]) {
  assert.equal(page.includes(tab), true, `AI admin page missing tab ${tab}`)
}

for (const actionText of [
  '批量导入',
  '初始化当前商户',
  '初始化全部',
  '保存人工标签',
  '下载商户导入模板',
  '导入商户Excel',
  '编辑Banner配置',
  '编辑推荐参数',
  '用户输入',
  '推荐商户',
  '买单商户',
  '反馈',
  '降级',
]) {
  assert.equal(page.includes(actionText), true, `AI admin page missing visible capability ${actionText}`)
}

for (const tagType of [
  "'category'",
  "'scene'",
  "'taste'",
  "'facility'",
  "'price'",
  "'feature'",
  "'meal'",
  "'promotion'",
]) {
  assert.equal(page.includes(tagType), true, `AI admin page missing tag type ${tagType}`)
}

console.log('huimaidan-ai-contract passed')

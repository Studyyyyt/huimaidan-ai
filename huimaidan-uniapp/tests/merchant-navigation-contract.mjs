import assert from 'node:assert/strict'
import fs from 'node:fs'

const page = fs.readFileSync(new URL('../src/pages/merchant/detail.vue', import.meta.url), 'utf8')

assert.match(page, /function resolveMerchantLocation/, '商户详情页必须统一解析后端经纬度')
assert.match(page, /latitude\s*\?\?\s*merchant\.value\?\.lat/s, '导航应优先使用后端 latitude 字段并兼容 lat')
assert.match(page, /longitude\s*\?\?\s*merchant\.value\?\.long/s, '导航应优先使用后端 longitude 字段并兼容 long')
assert.match(page, /Number\.isFinite\(latitude\).*Number\.isFinite\(longitude\)/s, '导航前必须校验经纬度为有效数字')
assert.match(page, /scale:\s*18/, '调用微信地图时应传入缩放级别')
assert.match(page, /wx\.openLocation|uni\.openLocation/, '必须调用微信/uni 位置打开能力')

console.log('merchant-navigation-contract passed')

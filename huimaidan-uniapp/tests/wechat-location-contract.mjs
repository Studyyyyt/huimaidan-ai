import assert from 'node:assert/strict'
import fs from 'node:fs'

const helper = fs.readFileSync(new URL('../src/utils/wechat-location.ts', import.meta.url), 'utf8')
const indexPage = fs.readFileSync(new URL('../src/pages/index/index.vue', import.meta.url), 'utf8')
const api = fs.readFileSync(new URL('../src/api/huimaidan.ts', import.meta.url), 'utf8')
const locationStore = fs.readFileSync(new URL('../src/store/location.ts', import.meta.url), 'utf8')
const manifest = fs.readFileSync(new URL('../manifest.config.ts', import.meta.url), 'utf8')
const weixinAppJsonUrls = [
  new URL('../dist/build/mp-weixin/app.json', import.meta.url),
  new URL('../dist/dev/mp-weixin/app.json', import.meta.url),
]

assert.match(helper, /wx\.getLocation/, '微信小程序定位必须使用 wx.getLocation')
assert.match(helper, /type:\s*['"]gcj02['"]/, 'wx.getLocation 必须使用 gcj02 坐标系')
assert.match(helper, /isHighAccuracy:\s*true/, 'wx.getLocation 必须开启高精度定位')
assert.match(helper, /#ifdef\s+MP-WEIXIN/, 'wx.getLocation 调用必须限制在 MP-WEIXIN')
assert.match(helper, /reject\(/, '定位失败或非微信小程序端必须显式抛错')
assert.match(helper, /errMsg/, '定位失败时必须保留微信原始 errMsg，不能隐藏真实错误')

assert.match(indexPage, /getWechatUserLocation/, '首页必须通过微信定位 helper 获取用户位置')
assert.match(api, /function getLbsGeocoder/, '首页精确位置展示必须对接后端 lbs/geocoder 逆地址解析')
assert.match(indexPage, /getLbsGeocoder/, '首页获取当前位置后必须解析真实地址用于顶部位置展示')
assert.match(api, /function getLbsAddress/, '首页手动省市区选择必须对接后端 lbs/address 地址解析')
assert.match(indexPage, /getLbsAddress/, '首页手动选择省市区后必须解析坐标用于距离展示')
assert.match(indexPage, /async function refreshCurrentLocation/, '首页必须有统一的刷新当前位置流程')
assert.match(indexPage, /await refreshCurrentLocation\(\)/, '首页首屏加载商户列表前必须先尝试获取当前位置')
assert.doesNotMatch(indexPage, /uni\.getLocation/, '首页不得直接调用 uni.getLocation')
assert.match(indexPage, /@tap=["']handleLocateTap["']/, '顶部位置组件必须保留自动定位入口')
assert.match(indexPage, /mode=["']region["']/, '顶部位置组件必须提供省市区选择器')
assert.match(indexPage, /@change=["']handleRegionChange["']/, '顶部省市区选择器必须处理变更')
assert.match(indexPage, /async function handleRegionChange/, '首页必须有手动省市区选择流程')
assert.match(indexPage, /locationStore\.clearCoordinates\(\)/, '手动选择省市区重新解析前必须清除旧坐标，避免展示过期距离')
assert.match(indexPage, /locationStore\.hasCoordinates/, '首页应使用 hasCoordinates 判断零坐标也有效')
assert.match(
  indexPage,
  /if \(locationStore\.hasCoordinates\) \{[\s\S]*params\.latitude = locationStore\.latitude[\s\S]*params\.longitude = locationStore\.longitude/,
  '只要已有定位坐标，商户列表请求就必须传 latitude/longitude，不能只在距离筛选或最近排序时传',
)
assert.doesNotMatch(
  indexPage,
  /locationStore\.hasCoordinates\s*&&\s*\(selectedFilters\.value\.distance\s*\|\|\s*selectedFilters\.value\.sortBy === ['"]location['"]\)/,
  '商户列表距离展示不能被距离筛选或最近排序条件限制',
)
assert.match(locationStore, /setLocation\(newProvince: string, newCity: string, newDistrict: string, exactAddress\?: string\)/, '位置 store 必须支持保存逆地址解析得到的精确地址')
assert.match(locationStore, /function clearCoordinates\(\)/, '位置 store 必须支持单独清除经纬度')

assert.match(manifest, /permission:\s*\{[\s\S]*['"]scope\.userLocation['"]/, '微信小程序 manifest 必须声明 scope.userLocation 用途')
assert.match(manifest, /requiredPrivateInfos:\s*\[[\s\S]*['"]getLocation['"]/, '微信小程序 manifest 必须声明 requiredPrivateInfos.getLocation')

for (const weixinAppJsonUrl of weixinAppJsonUrls) {
  if (!fs.existsSync(weixinAppJsonUrl)) {
    continue
  }

  const builtWeixinAppJson = JSON.parse(fs.readFileSync(weixinAppJsonUrl, 'utf8'))
  assert.equal(
    builtWeixinAppJson.permission?.['scope.userLocation']?.desc,
    '用于根据当前位置推荐附近商户',
    `${weixinAppJsonUrl.pathname} 必须声明 scope.userLocation 用途`,
  )
  assert.ok(
    builtWeixinAppJson.requiredPrivateInfos?.includes('getLocation'),
    `${weixinAppJsonUrl.pathname} 必须声明 requiredPrivateInfos.getLocation`,
  )
}

console.log('wechat-location-contract passed')

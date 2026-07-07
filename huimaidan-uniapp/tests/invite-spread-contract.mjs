import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

const root = resolve(import.meta.dirname, '..')
const read = path => readFileSync(resolve(root, path), 'utf8')

const loginApi = read('src/api/login.ts')
assert.match(loginApi, /export function bindSpread/, '小程序必须封装已登录用户补绑邀请关系接口')
assert.match(loginApi, /\/api\/user\/spread/, '补绑邀请关系必须调用后端真实 /api/user/spread 接口')
assert.doesNotMatch(loginApi, /spread:\s*0/, '登录 API 兼容函数不能继续固定传 spread: 0')

const inviteUtil = read('src/utils/invite-spread.ts')
assert.match(inviteUtil, /INVITE_SPREAD_UID_STORAGE_KEY/, '小程序必须统一维护邀请人 UID 本地缓存 key')
assert.match(inviteUtil, /getPendingSpreadUid/, '小程序必须提供统一方法读取待透传邀请人 UID')

const tokenStore = read('src/store/token.ts')
assert.match(tokenStore, /getPendingSpreadUid/, 'token store 必须通过统一工具获取待透传邀请人 UID')
assert.match(tokenStore, /getMpLoginType\(\{\s*code:\s*loginCode,\s*spread\s*\}/s, 'mp_login_type 必须透传扫码邀请人 UID')
assert.match(tokenStore, /buildRoutineAuthReq\(code:\s*string,\s*spread:\s*number\)[\s\S]*spread,[\s\S]*spread_code:\s*''/s, 'mpAuthLogin 必须通过统一请求构造透传扫码邀请人 UID')
assert.match(tokenStore, /mpAuthLogin\(buildRoutineAuthReq\(loginCode,\s*spread\)\)/, 'mpAuthLogin 必须使用当前扫码邀请人 UID 构造请求')
assert.doesNotMatch(tokenStore, /spread:\s*0/, 'token store 不能继续固定传 spread: 0')
assert.match(tokenStore, /clearPendingSpreadUid\(\)/, '登录成功后必须清理邀请缓存')

const scanEntry = read('src/pages/scan-entry/index.vue')
assert.match(scanEntry, /parseScanScene/, '扫码入口必须使用统一 scene 解析，支持店铺码和邀请码')
assert.match(scanEntry, /bindSpread\(\{\s*spread_spid:\s*parsed\.spreadUid\s*\}\)/, '已登录扫码邀请码必须调用补绑接口')
assert.match(scanEntry, /setPendingSpreadUid\(parsed\.spreadUid\)/, '未登录扫码邀请码必须缓存邀请人 UID')
assert.match(scanEntry, /buildInviteEntryUrl\(parsed\)/, '邀请码入口必须跳转到首页并携带 spread 参数')

const home = read('src/pages/index/index.vue')
assert.match(home, /spid|spread/, '首页必须消费旧小程序码 spid 或 spread 参数')
assert.match(home, /setPendingSpreadUid/, '首页必须把旧小程序码邀请参数写入邀请缓存')

const mePage = read('src/pages/me/me.vue')
const sharePosterCase = mePage.match(/case '分享海报':([\s\S]*?)break/)
assert.ok(sharePosterCase, '常用功能必须处理分享海报点击')
assert.doesNotMatch(sharePosterCase[1], /功能开发中/, '分享海报不能继续提示功能开发中')
assert.match(mePage, /\/pages\/share-poster\/share-poster/, '分享海报入口必须进入独立海报页面')
assert.doesNotMatch(mePage, /previewImage/, '会员中心入口不能绕过海报页直接预览图片')
const teamCase = mePage.match(/case '我的团队':([\s\S]*?)break/)
assert.ok(teamCase, '常用功能必须处理我的团队点击')
assert.doesNotMatch(teamCase[1], /功能开发中/, '我的团队不能继续提示功能开发中')
assert.match(mePage, /\/pages\/my-team\/my-team/, '我的团队入口必须进入独立团队页面')

const sharePosterPage = read('src/pages/share-poster/share-poster.vue')
assert.match(sharePosterPage, /getSpreadPoster/, '分享海报页面必须调用真实推广海报接口')
assert.match(sharePosterPage, /\/api\/user\/v2\/spread_image|qrcode/, '分享海报页面必须消费后端生成的二维码')
assert.match(sharePosterPage, /邀您共享会员卡/, '分享海报页面必须还原设计主标题')
assert.match(sharePosterPage, /复制分享链接/, '分享海报页面必须提供复制分享链接按钮')
assert.match(sharePosterPage, /分享海报/, '分享海报页面必须提供分享海报按钮')
assert.match(sharePosterPage, /@error="handleQrcodeImageError"/, '分享海报二维码图片加载失败时必须展示明确错误')
assert.match(sharePosterPage, /二维码图片加载失败/, '分享海报二维码图片加载失败不能静默处理')
assert.doesNotMatch(sharePosterPage, /mock|placeholder\.com|data:image/i, '分享海报页面禁止使用 mock 或占位二维码')

const huimaidanApi = read('src/api/huimaidan.ts')
assert.match(huimaidanApi, /export function getSpreadTeamList/, 'uniapp 必须封装我的团队列表接口')
assert.match(huimaidanApi, /\/api\/user\/spread_list/, '我的团队必须调用后端真实 /api/user/spread_list 接口')

const teamPage = read('src/pages/my-team/my-team.vue')
assert.match(teamPage, /getSpreadTeamList/, '我的团队页面必须调用真实团队列表接口')
assert.match(teamPage, /level:\s*1/, '我的团队页面必须按一级邀请关系查询')
assert.match(teamPage, /邀请用户|团队成员|邀请时间/, '我的团队页面必须展示邀请用户列表信息')
assert.doesNotMatch(teamPage, /mock|placeholder\.com|data:image/i, '我的团队页面禁止使用 mock 或占位用户数据')

console.log('invite-spread-contract passed')

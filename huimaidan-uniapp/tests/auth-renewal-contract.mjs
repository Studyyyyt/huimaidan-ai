import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

const root = resolve(import.meta.dirname, '..')
const read = path => readFileSync(resolve(root, path), 'utf8')

const apiLogin = read('src/api/login.ts')
if (!apiLogin.includes('\'/api/auth/refresh_token\'')) {
  throw new Error('refreshToken API 必须对接后端 /api/auth/refresh_token')
}
if (!apiLogin.includes('Authorization: `Bearer $' + '{token}`')) {
  throw new Error('refreshToken API 必须显式携带待续期 token，不能依赖已过期 token 的拦截器')
}

const tokenStore = read('src/store/token.ts')
if (tokenStore.includes('单token模式不支持刷新token')) {
  throw new Error('单 token 模式必须支持无感续期，不能直接抛“不支持刷新token”')
}
if (!tokenStore.includes('isSingleTokenRes(tokenInfo.value)')) {
  throw new Error('token store 必须处理单 token 续期分支')
}
if (!tokenStore.includes('return hasLoginInfo.value')) {
  throw new Error('路由登录态不能只依赖本地 expires_time，否则到期会提前踢回登录页')
}

const http = read('src/http/http.ts')
if (http.includes('if (!isDoubleTokenMode)')) {
  throw new Error('HTTP 401 处理不能在单 token 模式下直接退出登录，应先尝试无感续期')
}
if (http.includes('token 刷新成功')) {
  throw new Error('无感续期成功不能弹 toast 打断用户')
}

console.log('auth-renewal-contract passed')

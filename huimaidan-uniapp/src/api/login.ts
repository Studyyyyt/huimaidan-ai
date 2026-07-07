import type {
  IAuthLoginRes,
  ICaptcha,
  ICrmebWrappedResult,
  IMpAuthRawRes,
  IMpAuthReq,
  IMpLoginTypeReq,
  IMpLoginTypeRes,
  IMpPhoneLoginReq,
  ISingleTokenRes,
  IUpdateInfo,
  IUserInfoRes,
} from './types/login'
import { http } from '@/http/http'

/**
 * 登录表单
 */
export interface ILoginForm {
  username: string
  password: string
}

/**
 * 获取验证码
 * 模板遗留：当前惠买单小程序主链路不使用账号密码验证码登录。
 * @returns ICaptcha 验证码
 */
export function getCode() {
  return http.get<ICaptcha>('/user/getCode')
}

/**
 * 商户管理员登录
 * 模板遗留：仅保留给非小程序管理端场景，惠买单小程序主链路使用 /api/auth 系列接口。
 * @param loginForm 登录表单
 */
export function login(loginForm: ILoginForm) {
  return http.post<IAuthLoginRes>('/auth/admin/login', loginForm)
}

/**
 * 刷新 token
 * CRMEB 小程序当前为单 token 模式，续期时必须显式携带待续期 token。
 * @param token 当前本地 token
 */
export function refreshToken(token: string) {
  return http.post<IAuthLoginRes>('/api/auth/refresh_token', undefined, undefined, {
    Authorization: `Bearer ${token}`,
  }, {
    skipUnauthorizedHandler: true,
  })
}

/**
 * 获取用户信息
 */
export function getUserInfo() {
  return http.get<IUserInfoRes>('/api/user')
}

/**
 * 退出登录
 */
export function logout() {
  return http.post<void>('/api/logout', undefined, undefined, undefined, {
    skipUnauthorizedHandler: true,
  })
}

/**
 * 修改用户信息
 * 模板遗留：当前后端小程序用户资料接口未核入惠买单主链路。
 */
export function updateInfo(data: IUpdateInfo) {
  return http.post('/user/updateInfo', data)
}

/**
 * 获取微信登录凭证
 * @returns Promise 包含微信登录凭证(code)
 */
export function getWxCode() {
  return new Promise<UniApp.LoginRes>((resolve, reject) => {
    uni.login({
      provider: 'weixin',
      success: res => resolve(res),
      fail: err => reject(new Error(err.errMsg || '获取微信登录凭证失败')),
    })
  })
}

/**
 * 查询小程序登录方式
 */
export function getMpLoginType(data: IMpLoginTypeReq) {
  return http.post<IMpLoginTypeRes>('/api/auth/mp_login_type', data, undefined, undefined, {
    successCodes: [0, 200, 201],
  })
}

/**
 * 小程序授权登录
 */
export function mpAuthLogin(data: IMpAuthReq) {
  return http.post<IMpAuthRawRes>('/api/auth', data, undefined, undefined, {
    successCodes: [0, 200, 201],
    returnRawResponse: true,
  })
}

/**
 * 小程序手机号绑定登录
 */
export function mpPhoneLogin(data: IMpPhoneLoginReq) {
  return http.post<ISingleTokenRes | ICrmebWrappedResult<ISingleTokenRes>>('/api/auth/mp_phone', data)
}

/**
 * 已登录用户补绑邀请关系
 */
export function bindSpread(data: { spread_spid: number }) {
  return http.post<void>('/api/user/spread', data)
}

/**
 * 微信登录（兼容旧调用名）
 */
export function wxLogin(data: { code: string, spread?: number }) {
  const spread = data.spread ?? 0
  return mpAuthLogin({
    auth: {
      type: 'routine',
      auth: {
        code: data.code,
        spread,
        spread_code: '',
      },
    },
  })
}

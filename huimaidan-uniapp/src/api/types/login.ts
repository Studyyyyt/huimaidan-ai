// 认证模式类型
export type AuthMode = 'single' | 'double'

// 单Token响应类型
export interface ISingleTokenRes {
  token: string
  expiresIn?: number | string // 有效期(秒)
  exp?: number | string
  expires_time?: number | string
  user?: Record<string, any>
}

// 双Token响应类型
export interface IDoubleTokenRes {
  accessToken: string
  refreshToken: string
  accessExpiresIn: number // 访问令牌有效期(秒)
  refreshExpiresIn: number // 刷新令牌有效期(秒)
}

/**
 * 登录返回的信息，其实就是 token 信息
 */
export type IAuthLoginRes = ISingleTokenRes | IDoubleTokenRes

/**
 * 用户信息
 */
export type UserRole = string

export interface IUserInfoRes {
  userId: number
  uid?: number
  username: string
  account?: string
  phone?: string
  nickname: string
  avatar?: string
  /** 同时支持单角色和多角色，你自行选择一种就行 */
  role?: UserRole
  roles?: UserRole[]
  [key: string]: any // 允许其他扩展字段
}

// 认证存储数据结构
export interface AuthStorage {
  mode: AuthMode
  tokens: ISingleTokenRes | IDoubleTokenRes
  userInfo?: IUserInfoRes
  loginTime: number // 登录时间戳
}

/**
 * 获取验证码
 */
export interface ICaptcha {
  captchaEnabled: boolean
  uuid: string
  image: string
}
/**
 * 上传成功的信息
 */
export interface IUploadSuccessInfo {
  fileId: number
  originalName: string
  fileName: string
  storagePath: string
  fileHash: string
  fileType: string
  fileBusinessType: string
  fileSize: number
}
/**
 * 更新用户信息
 */
export interface IUpdateInfo {
  id: number
  name: string
  sex: string
}

export interface IMpLoginTypeReq {
  code: string
  spread?: number
}

export interface IMpLoginTypeRes {
  bindPhone: boolean
  key: string
  wechat_phone_switch: string | number
}

export interface IMpAuthReq {
  auth: {
    type: 'routine'
    auth: {
      code: string
      spread?: number
      spread_code?: string | Record<string, any>
    }
  }
}

export interface IMpAuthPendingRes {
  key: string
  wechat_phone_switch: string | number
}

export interface ICrmebWrappedResult<T> {
  status: number | string
  result: T
  message?: string
}

export interface IMpAuthRawRes {
  code: 0 | 200 | 201
  msg?: string
  message?: string
  data: ISingleTokenRes | IMpAuthPendingRes | ICrmebWrappedResult<ISingleTokenRes | IMpAuthPendingRes>
}

export interface IMpPhoneLoginReq {
  auth_token: string
  phone_code: string
}

export interface IWechatPhoneAuthDetail {
  errMsg?: string
  errno?: number
  code?: string
}

/**
 * 判断是否为单Token响应
 * @param tokenRes 登录响应数据
 * @returns 是否为单Token响应
 */
export function isSingleTokenRes(tokenRes: unknown): tokenRes is ISingleTokenRes {
  return typeof tokenRes === 'object' && tokenRes !== null && 'token' in tokenRes && !('refreshToken' in tokenRes)
}

/**
 * 判断是否为双Token响应
 * @param tokenRes 登录响应数据
 * @returns 是否为双Token响应
 */
export function isDoubleTokenRes(tokenRes: unknown): tokenRes is IDoubleTokenRes {
  return typeof tokenRes === 'object' && tokenRes !== null && 'accessToken' in tokenRes && 'refreshToken' in tokenRes
}

/**
 * 在 uniapp 的 RequestOptions 和 IUniUploadFileOptions 基础上，添加自定义参数
 */
export type CustomRequestOptions = UniApp.RequestOptions & {
  query?: Record<string, any>
  /** 出错时是否隐藏错误提示 */
  hideErrorToast?: boolean
  /** 业务成功码，默认兼容 0 和 200 */
  successCodes?: number[]
  /** 返回完整业务响应，适用于需要读取 code/msg 的中间态接口 */
  returnRawResponse?: boolean
  /** 跳过 401 自动登出处理，避免登出接口递归调用 */
  skipUnauthorizedHandler?: boolean
} & IUniUploadFileOptions // 添加uni.uploadFile参数类型

/** 主要提供给 openapi-ts-request 生成的代码使用 */
export type CustomRequestOptions_ = Omit<CustomRequestOptions, 'url'>

export interface HttpRequestResult<T> {
  promise: Promise<T>
  requestTask: UniApp.RequestTask
}

// 通用响应格式（兼容 msg + message 字段）
export type IResponse<T = any> = {
  code: number
  status?: number | string
  data: T
  message: string
  [key: string]: any // 允许额外属性
} | {
  code: number
  status?: number | string
  data: T
  msg: string
  [key: string]: any // 允许额外属性
} | {
  status: number | string
  code?: number | string
  data: T
  message: string
  [key: string]: any // 允许额外属性
}

// 分页请求参数
export interface PageParams {
  page: number
  pageSize: number
  [key: string]: any
}

// 分页响应数据
export interface PageResult<T> {
  list: T[]
  total: number
  page: number
  pageSize: number
}

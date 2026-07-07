import type { CustomRequestOptions, IResponse } from '@/http/types'
import { isMp } from '@uni-helper/uni-env'
import { useTokenStore } from '@/store/token'
import { toLoginPage } from '@/utils/toLoginPage'
import { normalizeBusinessError } from './http-error'
import { ResultEnum } from './tools/enum'

// 刷新 token 状态管理
let refreshing = false // 防止重复刷新 token 标识
let taskQueue: Array<{
  resolve: (value: unknown) => void
  reject: (reason?: unknown) => void
  options: CustomRequestOptions
}> = [] // 刷新 token 请求队列

function getRenewToken(tokenInfo: unknown) {
  if (typeof tokenInfo !== 'object' || tokenInfo === null) {
    return ''
  }
  const info = tokenInfo as Record<string, string>
  return info.refreshToken || info.token || ''
}

export function http<T>(options: CustomRequestOptions) {
  // 1. 返回 Promise 对象
  return new Promise<T>((resolve, reject) => {
    uni.request({
      ...options,
      dataType: 'json',
      // #ifndef MP-WEIXIN
      responseType: 'json',
      // #endif
      // 响应成功
      success: async (res) => {
        const responseData = res.data as IResponse<T>
        const code = Number(responseData.code ?? responseData.status)
        const normalizedResponseData = {
          ...responseData,
          code,
        }
        const successCodes = options.successCodes ?? [ResultEnum.Success0, ResultEnum.Success200]

        // 检查是否是401错误（包括HTTP状态码401或业务码401）
        const isTokenExpired = res.statusCode === 401 || code === 401

        if (isTokenExpired && !options.skipUnauthorizedHandler) {
          const tokenStore = useTokenStore()

          taskQueue.push({
            resolve: value => resolve(value as T),
            reject,
            options,
          })

          if (!refreshing) {
            refreshing = true
            try {
              // 先尝试刷新 token
              const renewToken = getRenewToken(tokenStore.tokenInfo)
              if (renewToken) {
                await tokenStore.refreshToken()
                console.log('[http] token 已刷新')
              }
              else if (isMp) {
                // 小程序环境下，没有 refresh token，尝试静默重新登录
                console.log('[http] 无 refresh token，尝试静默重新登录')
                const loginSuccess = await tokenStore.silentLogin()
                if (!loginSuccess) {
                  throw new Error('静默登录失败')
                }
              }
              else {
                throw new Error('无有效 token')
              }

              refreshing = false
              taskQueue.forEach(task => task.resolve(http(task.options)))
            }
            catch (refreshErr) {
              console.error('[http] token 刷新/重新登录失败:', refreshErr)
              refreshing = false
              taskQueue.forEach(task => task.reject(refreshErr))

              // 小程序环境下，如果静默登录失败，可能需要绑定手机号
              if (isMp && tokenStore.needBindWxPhone) {
                uni.showToast({
                  title: '请授权手机号完成登录',
                  icon: 'none',
                })
              }
              else {
                uni.showToast({
                  title: '登录已过期，请重新登录',
                  icon: 'none',
                })
              }

              await tokenStore.logout()
              setTimeout(() => {
                toLoginPage()
              }, 2000)
            }
            finally {
              taskQueue = []
            }
          }

          return
        }

        // 处理其他成功状态（HTTP状态码200-299）
        if (res.statusCode >= 200 && res.statusCode < 300) {
          // 若调用方需要原始响应，直接返回，不做业务码校验
          if (options.returnRawResponse) {
            return resolve(normalizedResponseData as T)
          }
          // 处理业务逻辑错误
          if (!successCodes.includes(code)) {
            const businessError = normalizeBusinessError(normalizedResponseData)
            uni.showToast({
              icon: 'none',
              title: businessError.message,
            })
            return reject(businessError)
          }
          return resolve(responseData.data)
        }

        // 处理其他错误
        !options.hideErrorToast
        && uni.showToast({
          icon: 'none',
          title: (res.data as any).msg || '请求错误',
        })
        reject(res)
      },
      // 响应失败
      fail(err) {
        uni.showToast({
          icon: 'none',
          title: '网络错误，换个网络试试',
        })
        reject(err)
      },
    })
  })
}

/**
 * GET 请求
 * @param url 后台地址
 * @param query 请求query参数
 * @param header 请求头，默认为json格式
 * @returns
 */
export function httpGet<T>(url: string, query?: Record<string, any>, header?: Record<string, any>, options?: Partial<CustomRequestOptions>) {
  return http<T>({
    url,
    query,
    method: 'GET',
    header,
    ...options,
  })
}

/**
 * POST 请求
 * @param url 后台地址
 * @param data 请求body参数
 * @param query 请求query参数，post请求也支持query，很多微信接口都需要
 * @param header 请求头，默认为json格式
 * @returns
 */
export function httpPost<T>(url: string, data?: Record<string, any>, query?: Record<string, any>, header?: Record<string, any>, options?: Partial<CustomRequestOptions>) {
  return http<T>({
    url,
    query,
    data,
    method: 'POST',
    header,
    ...options,
  })
}
/**
 * PUT 请求
 */
export function httpPut<T>(url: string, data?: Record<string, any>, query?: Record<string, any>, header?: Record<string, any>, options?: Partial<CustomRequestOptions>) {
  return http<T>({
    url,
    data,
    query,
    method: 'PUT',
    header,
    ...options,
  })
}

/**
 * DELETE 请求（无请求体，仅 query）
 */
export function httpDelete<T>(url: string, query?: Record<string, any>, header?: Record<string, any>, options?: Partial<CustomRequestOptions>) {
  return http<T>({
    url,
    query,
    method: 'DELETE',
    header,
    ...options,
  })
}

// 支持与 axios 类似的API调用
http.get = httpGet
http.post = httpPost
http.put = httpPut
http.delete = httpDelete

// 支持与 alovaJS 类似的API调用
http.Get = httpGet
http.Post = httpPost
http.Put = httpPut
http.Delete = httpDelete

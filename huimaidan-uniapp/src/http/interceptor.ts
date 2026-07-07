import type { CustomRequestOptions } from '@/http/types'
import { useTokenStore } from '@/store'
import { useMerTokenStore } from '@/store/mer-token'
import { getEnvBaseUrl } from '@/utils'
import { stringifyQuery } from './tools/queryString'

// 请求基准地址
const baseUrl = getEnvBaseUrl()

// 拦截器配置
const httpInterceptor = {
  // 拦截前触发
  invoke(options: CustomRequestOptions) {
    // 如果您使用了alova，则请把下面的代码放开注释
    // alova 执行流程：alova beforeRequest --> 本拦截器 --> alova responded
    // return options

    // 非 alova 请求，正常执行
    // 接口请求支持通过 query 参数配置 queryString
    if (options.query) {
      const queryStr = stringifyQuery(options.query)
      if (options.url.includes('?')) {
        options.url += `&${queryStr}`
      }
      else {
        options.url += `?${queryStr}`
      }
    }
    // 非 http 开头需拼接地址
    if (!options.url.startsWith('http')) {
      // 确保 URL 拼接时不会出现双斜杠
      const normalizedBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl
      const normalizedUrl = options.url.startsWith('/') ? options.url : `/${options.url}`

      // #ifdef H5
      if (JSON.parse(import.meta.env.VITE_APP_PROXY_ENABLE)) {
        // 自动拼接代理前缀
        options.url = import.meta.env.VITE_APP_PROXY_PREFIX + normalizedUrl
      }
      else {
        options.url = normalizedBaseUrl + normalizedUrl
      }
      // #endif
      // 非H5正常拼接
      // #ifndef H5
      options.url = normalizedBaseUrl + normalizedUrl
      // #endif
      // TIPS: 如果需要对接多个后端服务，也可以在这里处理，拼接成所需要的地址
    }
    // 1. 请求超时，允许慢接口按需覆盖默认值。
    options.timeout = options.timeout ?? 60000 // 60s
    // 2. （可选）添加小程序端请求头标识
    options.header = {
      ...options.header,
    }
    // 3. 添加 token 请求头标识
    // 判断是否是商户端 API（以 /mer/ 开头）
    const isMerApi = options.url.includes('/mer/')

    if (isMerApi) {
      // 商户端 API 使用 X-Token 头
      const merTokenStore = useMerTokenStore()
      merTokenStore.updateNowTime()
      const merToken = merTokenStore.validToken

      if (merToken) {
        options.header['X-Token'] = merToken
      }
    }
    else {
      // 用户端 API 使用 Authorization 头
      const tokenStore = useTokenStore()
      const token = tokenStore.updateNowTime().validToken

      if (token) {
        options.header.Authorization = `Bearer ${token}`
      }
    }
    return options
  },
}

export const requestInterceptor = {
  install() {
    // 拦截 request 请求
    uni.addInterceptor('request', httpInterceptor)
    // 拦截 uploadFile 文件上传
    uni.addInterceptor('uploadFile', httpInterceptor)
  },
}

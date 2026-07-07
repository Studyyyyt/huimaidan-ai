import type {
  ILoginForm,
} from '@/api/login'
import type {
  IAuthLoginRes,
  ICrmebWrappedResult,
  IMpAuthPendingRes,
  ISingleTokenRes,
  IUserInfoRes,
  IWechatPhoneAuthDetail,
} from '@/api/types/login'
import { isMp } from '@uni-helper/uni-env'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue' // 修复：导入 computed
import {
  login as _login,
  logout as _logout,
  refreshToken as _refreshToken,
  getMpLoginType,
  getWxCode,
  mpAuthLogin,
  mpPhoneLogin,
} from '@/api/login'
import { isDoubleTokenRes, isSingleTokenRes } from '@/api/types/login'
import { clearPendingSpreadUid, getPendingSpreadUid } from '@/utils/invite-spread'
import { isDoubleTokenMode } from '@/utils'
import { useUserStore } from './user'

// 初始化状态
const tokenInfoState = isDoubleTokenMode
  ? {
      accessToken: '',
      accessExpiresIn: 0,
      refreshToken: '',
      refreshExpiresIn: 0,
    }
  : {
      token: '',
      expiresIn: 0,
    }

interface IWxLoginResult {
  status: 'logged-in' | 'need-phone'
  authToken?: string
  wechatPhoneSwitch?: string | number
}

function getLoginCode(loginRes: UniApp.LoginRes) {
  if (!loginRes.code) {
    throw new Error('获取微信登录凭证失败')
  }
  return loginRes.code
}

function isMpAuthPendingRes(data: ISingleTokenRes | IMpAuthPendingRes): data is IMpAuthPendingRes {
  return 'key' in data && !('token' in data)
}

function isCrmebWrappedResult<T>(data: T | ICrmebWrappedResult<T>): data is ICrmebWrappedResult<T> {
  return typeof data === 'object' && data !== null && 'result' in data
}

function unwrapCrmebResult<T>(data: T | ICrmebWrappedResult<T>): T {
  return isCrmebWrappedResult(data) ? data.result : data
}

function buildRoutineAuthReq(code: string, spread: number) {
  return {
    auth: {
      type: 'routine' as const,
      auth: {
        code,
        spread,
        spread_code: '',
      },
    },
  }
}

function normalizeLoginUserInfo(user: Record<string, any>): IUserInfoRes {
  return {
    ...user,
    userId: Number(user.userId ?? user.uid ?? -1),
    username: String(user.username ?? user.account ?? ''),
    nickname: String(user.nickname ?? user.username ?? user.account ?? ''),
  }
}

function getSingleTokenExpireTime(tokenInfo: ISingleTokenRes) {
  const now = Date.now()
  const absoluteExpireTime = Number(tokenInfo.expires_time)
  if (Number.isFinite(absoluteExpireTime) && absoluteExpireTime > 0) {
    return absoluteExpireTime > 1000000000000 ? absoluteExpireTime : absoluteExpireTime * 1000
  }

  const expiresIn = Number(tokenInfo.expiresIn ?? tokenInfo.exp)
  if (!Number.isFinite(expiresIn) || expiresIn <= 0) {
    throw new Error('登录响应缺少 token 过期时间')
  }
  return now + expiresIn * 1000
}

export const useTokenStore = defineStore(
  'token',
  () => {
    // 定义用户信息
    const tokenInfo = ref<IAuthLoginRes>({ ...tokenInfoState })
    const mpPhoneAuth = ref<IWxLoginResult | null>(null)

    // 添加一个时间戳 ref 作为响应式依赖
    const nowTime = ref(Date.now())

    // 登录就绪状态（用于等待自动登录完成）
    let loginReadyResolve: ((value: boolean) => void) | null = null
    const loginReady = new Promise<boolean>((resolve) => {
      loginReadyResolve = resolve
    })

    // 标记登录流程已完成
    const markLoginReady = (success: boolean) => {
      if (loginReadyResolve) {
        loginReadyResolve(success)
        loginReadyResolve = null
      }
    }
    /**
     * 更新响应式数据:now
     * 确保isTokenExpired/isRefreshTokenExpired重新计算,而不是用错误过期缓存值
     * 可useTokenStore内部适时调用;也可链式调用:tokenStore.updateNowTime().hasLogin
     * @returns 最新的tokenStore实例
     */
    const updateNowTime = () => {
      nowTime.value = Date.now()
      return useTokenStore()
    }

    // 设置用户信息
    const setTokenInfo = (val: IAuthLoginRes) => {
      updateNowTime()
      tokenInfo.value = val

      // 计算并存储过期时间
      const now = Date.now()
      if (isSingleTokenRes(val)) {
        // 单token模式
        const expireTime = getSingleTokenExpireTime(val)
        uni.setStorageSync('accessTokenExpireTime', expireTime)
      }
      else if (isDoubleTokenRes(val)) {
        // 双token模式
        const accessExpireTime = now + val.accessExpiresIn * 1000
        const refreshExpireTime = now + val.refreshExpiresIn * 1000
        uni.setStorageSync('accessTokenExpireTime', accessExpireTime)
        uni.setStorageSync('refreshTokenExpireTime', refreshExpireTime)
      }
    }

    /**
     * 判断token是否过期
     */
    const isTokenExpired = computed(() => {
      if (!tokenInfo.value) {
        return true
      }

      const now = nowTime.value
      const expireTime = uni.getStorageSync('accessTokenExpireTime')

      if (!expireTime)
        return true
      return now >= expireTime
    })

    /**
     * 判断refreshToken是否过期
     */
    const isRefreshTokenExpired = computed(() => {
      if (!isDoubleTokenMode)
        return true

      const now = nowTime.value
      const refreshExpireTime = uni.getStorageSync('refreshTokenExpireTime')

      if (!refreshExpireTime)
        return true
      return now >= refreshExpireTime
    })

    /**
     * 登录成功后处理逻辑
     * @param tokenInfo 登录返回的token信息
     */
    async function _postLogin(tokenInfo: IAuthLoginRes, clearInvite = false) {
      setTokenInfo(tokenInfo)
      mpPhoneAuth.value = null
      const userStore = useUserStore()
      if (isSingleTokenRes(tokenInfo) && tokenInfo.user) {
        userStore.setUserInfo(normalizeLoginUserInfo(tokenInfo.user))
        if (clearInvite) {
          clearPendingSpreadUid()
        }
        return
      }
      await userStore.fetchUserInfo()
      if (clearInvite) {
        clearPendingSpreadUid()
      }
    }

    /**
     * 用户登录
     * 有的时候后端会用一个接口返回token和用户信息，有的时候会分开2个接口，一个获取token，一个获取用户信息
     * （各有利弊，看业务场景和系统复杂度），这里使用2个接口返回的来模拟
     * @param loginForm 登录参数
     * @returns 登录结果
     */
    const login = async (loginForm: ILoginForm) => {
      try {
        const res = await _login(loginForm)
        console.log('普通登录-res: ', res)
        await _postLogin(res)
        uni.showToast({
          title: '登录成功',
          icon: 'success',
        })
        return res
      }
      catch (error) {
        console.error('登录失败:', error)
        uni.showToast({
          title: '登录失败，请重试',
          icon: 'error',
        })
        throw error
      }
      finally {
        updateNowTime()
      }
    }

    /**
     * 微信登录
     * 有的时候后端会用一个接口返回token和用户信息，有的时候会分开2个接口，一个获取token，一个获取用户信息
     * （各有利弊，看业务场景和系统复杂度），这里使用2个接口返回的来模拟
     * @returns 登录结果
     */
    const wxLogin = async () => {
      try {
        // 获取微信小程序登录的code
        const loginCode = getLoginCode(await getWxCode())
        const spread = getPendingSpreadUid()
        console.log('微信登录-code: ', loginCode)
        const loginType = await getMpLoginType({ code: loginCode, spread })
        console.log('微信登录方式-res: ', loginType)
        const res = await mpAuthLogin(buildRoutineAuthReq(loginCode, spread))
        console.log('微信登录-res: ', res)
        const authData = unwrapCrmebResult(res.data)
        if (res.code === 201 || isMpAuthPendingRes(authData)) {
          const pendingData = isMpAuthPendingRes(authData) ? authData : loginType
          if (!pendingData.key) {
            throw new Error('登录需要绑定手机号，但后端未返回 auth_token')
          }
          mpPhoneAuth.value = {
            status: 'need-phone',
            authToken: pendingData.key,
            wechatPhoneSwitch: pendingData.wechat_phone_switch,
          }
          uni.showToast({
            title: '请授权手机号完成登录',
            icon: 'none',
          })
          return mpPhoneAuth.value
        }

        if (!authData.token) {
          throw new Error('登录响应缺少 token')
        }
        await _postLogin(authData, spread > 0)
        uni.showToast({
          title: '登录成功',
          icon: 'success',
        })
        return { status: 'logged-in' } satisfies IWxLoginResult
      }
      catch (error) {
        console.error('微信登录失败:', error)
        uni.showToast({
          title: '微信登录失败，请重试',
          icon: 'error',
        })
        throw error
      }
      finally {
        updateNowTime()
      }
    }

    /**
     * 微信手机号绑定登录
     */
    const bindWxPhone = async (detail: IWechatPhoneAuthDetail) => {
      try {
        if (!mpPhoneAuth.value?.authToken) {
          throw new Error('后端未返回手机号绑定 auth_token，无法完成强制手机号绑定')
        }
        if (!detail.code) {
          throw new Error(detail.errMsg || '请获取手机号授权 code')
        }

        const res = unwrapCrmebResult(await mpPhoneLogin({
          auth_token: mpPhoneAuth.value.authToken,
          phone_code: detail.code,
        }))
        if (!res.token) {
          throw new Error('手机号绑定登录响应缺少 token')
        }
        await _postLogin(res, true)
        uni.showToast({
          title: '登录成功',
          icon: 'success',
        })
        return { status: 'logged-in' } satisfies IWxLoginResult
      }
      catch (error) {
        console.error('手机号绑定登录失败:', error)
        uni.showToast({
          title: error instanceof Error ? error.message : '手机号绑定登录失败',
          icon: 'none',
        })
        throw error
      }
      finally {
        updateNowTime()
      }
    }

    /**
     * 退出登录 并 删除用户信息
     */
    const logout = async () => {
      try {
        // TODO 实现自己的退出登录逻辑
        await _logout()
      }
      catch (error) {
        console.error('退出登录失败:', error)
      }
      finally {
        updateNowTime()

        // 无论成功失败，都需要清除本地token信息
        // 清除存储的过期时间
        uni.removeStorageSync('accessTokenExpireTime')
        uni.removeStorageSync('refreshTokenExpireTime')
        console.log('退出登录-清除用户信息')
        tokenInfo.value = { ...tokenInfoState }
        mpPhoneAuth.value = null
        uni.removeStorageSync('token')
        const userStore = useUserStore()
        userStore.clearUserInfo()
      }
    }

    /**
     * 刷新token
     * @returns 刷新结果
     */
    const refreshToken = async () => {
      try {
        if (isSingleTokenRes(tokenInfo.value)) {
          if (!tokenInfo.value.token) {
            throw new Error('无效的 token')
          }

          const res = await _refreshToken(tokenInfo.value.token)
          if (!isSingleTokenRes(res)) {
            throw new Error('刷新 token 响应格式异常')
          }
          await _postLogin(res)
          return res
        }

        if (isDoubleTokenRes(tokenInfo.value)) {
          if (!tokenInfo.value.refreshToken) {
            throw new Error('无效的 refreshToken')
          }

          const refreshToken = tokenInfo.value.refreshToken
          const res = await _refreshToken(refreshToken)
          console.log('刷新token-res: ', res)
          setTokenInfo(res)
          return res
        }

        throw new Error('无效的 token 信息')
      }
      catch (error) {
        console.error('刷新token失败:', error)
        throw error
      }
      finally {
        updateNowTime()
      }
    }

    /**
     * 获取有效的token
     * 注意：在computed中不直接调用异步函数，只做状态判断
     * 实际的刷新操作应由调用方处理
     * 建议这样使用 tokenStore.updateNowTime().validToken
     */
    const getValidToken = computed(() => {
      // token已过期，返回空
      if (isTokenExpired.value) {
        return ''
      }

      if (!isDoubleTokenMode) {
        return isSingleTokenRes(tokenInfo.value) ? tokenInfo.value.token : ''
      }
      else {
        return isDoubleTokenRes(tokenInfo.value) ? tokenInfo.value.accessToken : ''
      }
    })

    /**
     * 检查是否有登录信息（不考虑token是否过期）
     */
    const hasLoginInfo = computed(() => {
      if (!tokenInfo.value) {
        return false
      }
      if (isDoubleTokenMode) {
        return isDoubleTokenRes(tokenInfo.value) && !!tokenInfo.value.accessToken
      }
      else {
        return isSingleTokenRes(tokenInfo.value) && !!tokenInfo.value.token
      }
    })

    /**
     * 检查是否已登录且token有效
     * 建议这样使用tokenStore.updateNowTime().hasLogin
     */
    const hasValidLogin = computed(() => {
      console.log('hasValidLogin hasLoginInfo:', hasLoginInfo.value, 'isTokenExpired:', isTokenExpired.value)
      return hasLoginInfo.value && !isTokenExpired.value
    })

    const needBindWxPhone = computed(() => mpPhoneAuth.value?.status === 'need-phone')

    /**
     * 预检微信登录方式。
     * 小程序手机号弹窗只能由 open-type=getPhoneNumber 按钮点击触发，
     * 所以强制绑定手机号时需要先准备 auth_token，再让用户点击手机号按钮。
     */
    const prepareWxPhoneAuth = async () => {
      try {
        if (hasValidLogin.value) {
          return { status: 'logged-in' } satisfies IWxLoginResult
        }

        const loginCode = getLoginCode(await getWxCode())
        const spread = getPendingSpreadUid()
        const loginType = await getMpLoginType({ code: loginCode, spread })
        const res = await mpAuthLogin(buildRoutineAuthReq(loginCode, spread))
        const authData = unwrapCrmebResult(res.data)

        // 情况1：mpAuthLogin 直接返回了 token，说明用户已有手机号，可以直接登录
        if (isSingleTokenRes(authData)) {
          await _postLogin(authData, spread > 0)
          return { status: 'logged-in' } satisfies IWxLoginResult
        }

        // 情况2：mpAuthLogin 返回了 pending 状态，需要绑定手机号
        if (isMpAuthPendingRes(authData)) {
          if (!authData.key) {
            throw new Error('登录需要绑定手机号，但后端未返回 auth_token')
          }
          mpPhoneAuth.value = {
            status: 'need-phone',
            authToken: authData.key,
            wechatPhoneSwitch: authData.wechat_phone_switch,
          }
          return mpPhoneAuth.value
        }

        // 情况3：后端返回 bindPhone: false，说明用户已有手机号，不需要强制绑定
        // 但 mpAuthLogin 没有返回 token，这种情况下使用 loginType 的 key 尝试登录
        if (loginType.bindPhone === false && loginType.key) {
          mpPhoneAuth.value = {
            status: 'need-phone',
            authToken: loginType.key,
            wechatPhoneSwitch: loginType.wechat_phone_switch,
          }
          return mpPhoneAuth.value
        }

        // 情况4：其他情况，抛出错误
        throw new Error('登录失败：后端返回的数据格式异常')
      }
      finally {
        updateNowTime()
      }
    }

    /**
     * 尝试获取有效的token，如果过期且可刷新，则刷新token
     * @returns 有效的token或空字符串
     */
    const tryGetValidToken = async (): Promise<string> => {
      updateNowTime()
      if (!getValidToken.value && hasLoginInfo.value) {
        if (isDoubleTokenMode && isRefreshTokenExpired.value) {
          return ''
        }
        try {
          await refreshToken()
          return getValidToken.value
        }
        catch (error) {
          console.error('尝试刷新token失败:', error)
          return ''
        }
      }
      return getValidToken.value
    }

    /**
     * 静默自动登录（微信小程序）
     * 在 App.vue onLaunch 时调用，自动完成登录流程
     * 如果需要绑定手机号，不会弹出提示，只记录状态
     */
    const silentLogin = async (): Promise<boolean> => {
      // 非小程序环境直接返回
      if (!isMp) {
        markLoginReady(false)
        return false
      }

      try {
        updateNowTime()

        // 如果本地有 token（无论是否过期），都先尝试用 wx.login 向服务器验证/续期
        // 因为本地 token 可能被服务器主动失效（如用户在其他设备退出、后台踢出等）
        if (hasLoginInfo.value) {
          console.log('[silentLogin] 本地有 token，尝试向服务器验证...')

          // 1. 先尝试刷新 token
          if (!isTokenExpired.value || isDoubleTokenMode) {
            try {
              await refreshToken()
              console.log('[silentLogin] token 已刷新')
              markLoginReady(true)
              return true
            }
            catch (refreshError) {
              console.log('[silentLogin] token 刷新失败，尝试微信登录', refreshError)
            }
          }

          // 2. 刷新失败，尝试微信登录
          try {
            const loginCode = getLoginCode(await getWxCode())
            const spread = getPendingSpreadUid()
            const res = await mpAuthLogin(buildRoutineAuthReq(loginCode, spread))
            const authData = unwrapCrmebResult(res.data)

            if (isSingleTokenRes(authData)) {
              await _postLogin(authData, spread > 0)
              console.log('[silentLogin] 微信登录成功（刷新 token）')
              markLoginReady(true)
              return true
            }

            if (isMpAuthPendingRes(authData)) {
              console.log('[silentLogin] 需要绑定手机号，等待用户操作')
              if (authData.key) {
                mpPhoneAuth.value = {
                  status: 'need-phone',
                  authToken: authData.key,
                  wechatPhoneSwitch: authData.wechat_phone_switch,
                }
              }
              markLoginReady(false)
              return false
            }
          }
          catch (wxError) {
            console.error('[silentLogin] 微信登录失败:', wxError)
          }

          // 3. 全部失败，清除无效 token，标记未登录
          console.log('[silentLogin] 所有验证方式失败，清除本地 token')
          await logout()
          markLoginReady(false)
          return false
        }

        // 本地无 token，首次微信登录
        console.log('[silentLogin] 本地无 token，开始首次微信登录')
        const loginCode = getLoginCode(await getWxCode())
        const spread = getPendingSpreadUid()
        const res = await mpAuthLogin(buildRoutineAuthReq(loginCode, spread))
        const authData = unwrapCrmebResult(res.data)

        if (isSingleTokenRes(authData)) {
          await _postLogin(authData, spread > 0)
          console.log('[silentLogin] 首次微信登录成功')
          markLoginReady(true)
          return true
        }

        if (isMpAuthPendingRes(authData)) {
          console.log('[silentLogin] 需要绑定手机号，等待用户操作')
          if (authData.key) {
            mpPhoneAuth.value = {
              status: 'need-phone',
              authToken: authData.key,
              wechatPhoneSwitch: authData.wechat_phone_switch,
            }
          }
          markLoginReady(false)
          return false
        }

        console.log('[silentLogin] 登录响应格式异常')
        markLoginReady(false)
        return false
      }
      catch (error) {
        console.error('[silentLogin] 静默登录失败:', error)
        markLoginReady(false)
        return false
      }
    }

    return {
      // 核心API方法
      login,
      prepareWxPhoneAuth,
      wxLogin,
      bindWxPhone,
      logout,
      needBindWxPhone,

      // 认证状态判断（最常用的）
      hasLogin: hasValidLogin,

      // 内部系统使用的方法
      refreshToken,
      tryGetValidToken,
      validToken: getValidToken,
      silentLogin,

      // 登录就绪状态（用于等待自动登录完成）
      loginReady,

      // 调试或特殊场景可能需要直接访问的信息
      tokenInfo,
      setTokenInfo,
      updateNowTime,
    }
  },
  {
    // 添加持久化配置，确保刷新页面后token信息不丢失
    persist: true,
  },
)

import type { IMerInfo, IMerLoginParams, IMerLoginRes } from '@/api/mer'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { getCaptcha, getMerInfo, merLogin, merLogout } from '@/api/mer'

/** 商户 Token 信息 */
interface IMerTokenInfo {
  token: string
  exp: number
  admin: IMerLoginRes['admin'] | null
}

/** 初始化状态 */
const merTokenInfoState: IMerTokenInfo = {
  token: '',
  exp: 0,
  admin: null,
}

export const useMerTokenStore = defineStore(
  'mer-token',
  () => {
    // 商户 token 信息
    const merTokenInfo = ref<IMerTokenInfo>({ ...merTokenInfoState })

    // 商户信息
    const merInfo = ref<IMerInfo | null>(null)

    // 图形验证码
    const captchaKey = ref('')
    const captchaImage = ref('')

    // 当前时间戳（用于计算 token 是否过期）
    const nowTime = ref(Date.now())

    /**
     * 更新当前时间
     */
    const updateNowTime = () => {
      nowTime.value = Date.now()
      return useMerTokenStore()
    }

    /**
     * 判断 token 是否过期
     * exp 存储的是绝对过期时间（毫秒）
     */
    const isTokenExpired = computed(() => {
      if (!merTokenInfo.value.token) {
        return true
      }
      const now = nowTime.value
      const expTime = merTokenInfo.value.exp
      if (!expTime)
        return true
      return now >= expTime
    })

    /**
     * 是否已登录（有 token 且未过期）
     */
    const hasLogin = computed(() => {
      return !!merTokenInfo.value.token && !isTokenExpired.value
    })

    /**
     * 获取有效的 token
     */
    const validToken = computed(() => {
      if (isTokenExpired.value) {
        return ''
      }
      return merTokenInfo.value.token
    })

    /**
     * 获取图形验证码
     */
    async function fetchCaptcha() {
      try {
        const res = await getCaptcha()
        captchaKey.value = res.key
        captchaImage.value = res.captcha
        return res
      }
      catch (error) {
        console.error('获取验证码失败:', error)
        throw error
      }
    }

    /**
     * 商户登录
     * @param params 登录参数
     */
    async function login(params: IMerLoginParams) {
      try {
        const res = await merLogin(params)
        // 将 exp 转换为绝对过期时间（毫秒）
        // 后端可能返回绝对时间戳（秒）或相对时长（秒），需要兼容处理
        const now = Date.now()
        const exp = Number(res.exp)
        const expireTime = exp > 1000000000 ? exp * 1000 : now + exp * 1000

        merTokenInfo.value = {
          token: res.token,
          exp: expireTime,
          admin: res.admin,
        }
        // 存储到本地
        uni.setStorageSync('mer_token_info', JSON.stringify(merTokenInfo.value))
        return res
      }
      catch (error) {
        console.error('商户登录失败:', error)
        throw error
      }
    }

    /**
     * 获取商户信息
     */
    async function fetchMerInfo() {
      try {
        const res = await getMerInfo()
        merInfo.value = res
        return res
      }
      catch (error) {
        console.error('获取商户信息失败:', error)
        throw error
      }
    }

    /**
     * 退出登录
     */
    async function logout() {
      try {
        await merLogout()
      }
      catch (error) {
        console.error('退出登录失败:', error)
      }
      finally {
        merTokenInfo.value = { ...merTokenInfoState }
        merInfo.value = null
        uni.removeStorageSync('mer_token_info')
      }
    }

    /**
     * 从本地存储恢复 token
     */
    function restoreToken() {
      const stored = uni.getStorageSync('mer_token_info')
      if (stored) {
        try {
          const parsed = JSON.parse(stored)
          // 兼容旧格式：如果 exp 是相对时长（秒），转换为绝对时间（毫秒）
          if (parsed.exp && parsed.exp < 1000000000) {
            parsed.exp = 0 // 旧格式无法确定登录时间，标记为过期
          }
          merTokenInfo.value = parsed
        }
        catch (e) {
          uni.removeStorageSync('mer_token_info')
        }
      }
    }

    return {
      // 状态
      merTokenInfo,
      merInfo,
      captchaKey,
      captchaImage,

      // 计算属性
      hasLogin,
      validToken,
      isTokenExpired,

      // 方法
      updateNowTime,
      fetchCaptcha,
      login,
      fetchMerInfo,
      logout,
      restoreToken,
    }
  },
  {
    persist: false, // 不使用 pinia-plugin-persistedstate，手动控制存储
  },
)

<script setup lang="ts">
import { onHide, onLaunch, onShow } from '@dcloudio/uni-app'
import { navigateToInterceptor } from '@/router/interceptor'
import { useTokenStore } from '@/store/token'

onLaunch(async (options) => {
  console.log('App.vue onLaunch', options)

  // 自动静默登录（微信小程序）
  // #ifdef MP-WEIXIN
  try {
    const tokenStore = useTokenStore()
    const loginSuccess = await tokenStore.silentLogin()
    console.log('App.vue 静默登录结果:', loginSuccess)
  }
  catch (error) {
    console.error('App.vue 静默登录异常:', error)
  }
  // #endif
})
onShow(async (options) => {
  console.log('App.vue onShow', options)

  // 从后台切换回来时，检查并刷新登录状态
  // #ifdef MP-WEIXIN
  try {
    const tokenStore = useTokenStore()
    tokenStore.updateNowTime()

    // 如果有登录信息但 token 过期，尝试刷新
    if (!tokenStore.hasLogin) {
      console.log('App.vue onShow: 检测到未登录状态，尝试自动登录')
      await tokenStore.silentLogin()
    }
  }
  catch (error) {
    console.error('App.vue onShow 刷新登录状态失败:', error)
  }
  // #endif

  // 处理直接进入页面路由的情况：如h5直接输入路由、微信小程序分享后进入等
  // https://github.com/feige996/unibest/issues/192
  if (options?.path) {
    navigateToInterceptor.invoke({
      url: `/${options.path}`,
      query: options.query,
    })
  }
  else {
    navigateToInterceptor.invoke({ url: '/' })
  }
})
onHide(() => {
  console.log('App Hide')
})
</script>

<style lang="scss"></style>

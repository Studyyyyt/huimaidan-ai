<script lang="ts" setup>
import type { IWechatPhoneAuthDetail } from '@/api/types/login'
import { AGREEMENT_KEY } from '@/api/huimaidan'
import { useTokenStore } from '@/store/token'

definePage({
  style: {
    navigationBarTitleText: '登录',
  },
})

const tokenStore = useTokenStore()
const preparing = ref(false)
const phoneLoading = ref(false)

async function prepareLogin() {
  if (tokenStore.hasLogin) {
    return
  }

  preparing.value = true
  try {
    const res = await tokenStore.prepareWxPhoneAuth()
    if (res.status === 'logged-in') {
      uni.navigateBack()
    }
  }
  catch (error) {
    console.error('微信登录预检失败', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '微信登录预检失败',
      icon: 'none',
    })
  }
  finally {
    preparing.value = false
  }
}

onMounted(() => {
  // #ifdef MP-WEIXIN
  prepareLogin()
  // #endif
})

async function handleGetPhoneNumber(event: { detail: IWechatPhoneAuthDetail }) {
  phoneLoading.value = true
  try {
    await tokenStore.bindWxPhone(event.detail)
    uni.navigateBack()
  }
  catch (error) {
    console.error('微信手机号绑定失败', error)
  }
  finally {
    phoneLoading.value = false
  }
}

function openAgreement(key: string) {
  uni.navigateTo({ url: `/pages/auth/agreement?key=${key}` })
}
</script>

<template>
  <view class="min-h-screen from-purple-50 to-white bg-gradient-to-b">
    <!-- Logo 区域 -->
    <view class="flex flex-col items-center pt-120rpx">
      <view class="h-160rpx w-160rpx flex items-center justify-center rounded-full from-purple-400 to-indigo-500 bg-gradient-to-br shadow-lg">
        <text class="text-72rpx text-white">惠</text>
      </view>
      <text class="mt-24rpx text-40rpx text-gray-800 font-bold">惠买单</text>
      <text class="mt-8rpx text-26rpx text-gray-400">帮你省更多</text>
    </view>

    <!-- 登录区域 -->
    <view class="mx-48rpx mt-80rpx">
      <!-- #ifdef MP-WEIXIN -->
      <button
        class="h-96rpx w-full rounded-full bg-[#07c160] text-32rpx text-white font-bold shadow-lg"
        open-type="getPhoneNumber"
        :loading="phoneLoading"
        :disabled="phoneLoading || preparing"
        @getphonenumber="handleGetPhoneNumber"
      >
        {{ preparing ? '登录准备中' : '微信手机号快捷登录' }}
      </button>
      <!-- #endif -->
    </view>

    <!-- 底部协议 -->
    <view class="absolute bottom-48rpx left-0 right-0 flex flex-row items-center justify-center px-32rpx">
      <text class="text-22rpx text-gray-400">登录即表示同意</text>
      <text
        class="text-22rpx text-purple-500"
        @click="openAgreement(AGREEMENT_KEY.USER_AGREE)"
      >《用户协议》</text>
      <text class="text-22rpx text-gray-400">和</text>
      <text
        class="text-22rpx text-purple-500"
        @click="openAgreement(AGREEMENT_KEY.USER_PRIVACY)"
      >《隐私政策》</text>
    </view>
  </view>
</template>

<style lang="scss" scoped>
//
</style>

<template>
  <view class="scan-entry-page">
    <text class="scan-entry-text">{{ message }}</text>
  </view>
</template>

<script lang="ts" setup>
import { onLoad } from '@dcloudio/uni-app'
import { bindSpread } from '@/api/login'
import { useTokenStore } from '@/store/token'
import { clearPendingSpreadUid, setPendingSpreadUid } from '@/utils/invite-spread'
import { buildInviteEntryUrl, buildStoreQrcodeCheckoutUrl, parseScanScene } from './scene'

defineOptions({
  name: 'ScanEntry',
})

definePage({
  style: {
    navigationBarTitleText: '扫码买单',
  },
})

const message = ref('正在处理二维码...')

onLoad(async (options) => {
  console.log('=== 扫码入口原始请求数据 ===')
  console.log('options:', options)
  console.log('原始 scene 参数:', options?.scene)
  console.log('================================')

  try {
    const parsed = parseScanScene(options?.scene)
    console.log('解析后的数据:', parsed)

    if (parsed.type === 'store') {
      message.value = '正在进入买单页...'
      uni.redirectTo({
        url: buildStoreQrcodeCheckoutUrl(parsed),
      })
      return
    }

    message.value = '正在记录邀请关系...'
    const tokenStore = useTokenStore()
    const token = await tokenStore.tryGetValidToken()
    if (token) {
      await bindSpread({ spread_spid: parsed.spreadUid })
      clearPendingSpreadUid()
      uni.showToast({
        title: '邀请关系已记录',
        icon: 'none',
      })
    }
    else {
      setPendingSpreadUid(parsed.spreadUid)
    }

    uni.reLaunch({
      url: buildInviteEntryUrl(parsed),
    })
  }
  catch (error) {
    console.error('扫码二维码处理失败:', error)
    const title = error instanceof Error ? error.message : '二维码参数错误'
    message.value = title
    uni.showToast({
      title,
      icon: 'none',
    })
  }
})
</script>

<style lang="scss" scoped>
.scan-entry-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7f8fa;
  padding: 48rpx;
  box-sizing: border-box;
}

.scan-entry-text {
  font-size: 30rpx;
  color: #333;
}
</style>

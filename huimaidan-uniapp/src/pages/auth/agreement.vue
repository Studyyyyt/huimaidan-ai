<script lang="ts" setup>
import { onLoad } from '@dcloudio/uni-app'
import type { IAgreementResult } from '@/api/huimaidan'
import { getAgreement } from '@/api/huimaidan'

definePage({
  style: {
    navigationBarTitleText: '协议',
  },
})

const loading = ref(true)
const title = ref('')
// ponytail: 后端按 key 动态返回字段，这里用 Record 承载富文本 html
const content = ref('')

onLoad((query) => {
  const key = query?.key || ''
  if (!key) {
    loading.value = false
    uni.showToast({ title: '协议参数缺失', icon: 'none' })
    return
  }
  loadAgreement(key)
})

async function loadAgreement(key: string) {
  try {
    const res = await getAgreement(key)
    title.value = res.title || ''
    content.value = res[key] || ''
    if (title.value) {
      uni.setNavigationBarTitle({ title: title.value })
    }
  }
  catch (error) {
    console.error('协议加载失败', error)
    uni.showToast({ title: '协议加载失败', icon: 'none' })
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 加载占位 -->
    <view v-if="loading" class="flex justify-center pt-200rpx">
      <wd-loading color="#7c5cff" />
    </view>

    <!-- 内容 -->
    <view v-else class="px-32rpx py-32rpx">
      <view v-if="content" class="agreement-content">
        <rich-text :nodes="content" />
      </view>
      <view v-else class="pt-200rpx text-center text-26rpx text-gray-400">
        暂无协议内容
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
// ponytail: 富文本图片自适应宽度，避免溢出
.agreement-content {
  :deep(img) {
    max-width: 100%;
    height: auto;
  }
  :deep(p) {
    margin: 16rpx 0;
    line-height: 1.7;
  }
}
</style>

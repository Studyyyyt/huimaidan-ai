<script lang="ts" setup>
import type { IAiBannerResponse } from '@/api/ai'
import { getAiBanner } from '@/api/ai'
import { useLocationStore } from '@/store'

defineOptions({
  name: 'AiSmartBanner',
})

const locationStore = useLocationStore()

// Banner 数据
const bannerData = ref<IAiBannerResponse | null>(null)
// 加载状态
const loading = ref(false)
const failed = ref(false)

/**
 * 获取 AI Banner 数据
 */
async function fetchBanner() {
  if (loading.value)
    return

  loading.value = true
  failed.value = false
  try {
    const params: { latitude?: number, longitude?: number, city_id?: number, city_name?: string } = {}
    if (locationStore.hasCoordinates) {
      params.latitude = locationStore.latitude ?? undefined
      params.longitude = locationStore.longitude ?? undefined
    }
    if (locationStore.cityId) {
      params.city_id = locationStore.cityId
    }
    else if (locationStore.city) {
      params.city_name = locationStore.city
    }
    bannerData.value = await getAiBanner(params)
  }
  catch (error) {
    console.error('获取 AI Banner 失败:', error)
    failed.value = true
  }
  finally {
    loading.value = false
  }
}

/**
 * 点击 Banner 跳转 AI 对话页
 */
function handleTap() {
  uni.navigateTo({
    url: '/pages/ai-chat/index',
  })
}

function handleMerchantTap() {
  const merId = bannerData.value?.recommend_merchant?.mer_id
  if (!merId) {
    handleTap()
    return
  }
  uni.navigateTo({
    url: `/pages/merchant/detail?id=${merId}&from=ai_banner`,
  })
}

onMounted(() => {
  void fetchBanner()
})

// 暴露刷新方法，供父组件调用
defineExpose({
  refresh: fetchBanner,
})
</script>

<template>
  <view
    v-if="bannerData || failed"
    class="ai-smart-banner"
    :style="{ backgroundColor: bannerData?.background_color || '#F3F4F6' }"
    @tap="handleTap"
  >
    <view class="min-w-0 flex-1">
      <text class="block text-16px font-bold" :style="{ color: bannerData?.text_color || '#374151' }">
        {{ bannerData?.title || 'AI 小惠推荐' }}
      </text>
      <text class="mt-1 block text-13px" :style="{ color: bannerData?.text_color || '#374151' }">
        {{ bannerData?.subtitle || 'AI 推荐暂不可用，点我进入对话稍后再试' }}
      </text>
      <view
        v-if="bannerData?.recommend_merchant"
        class="mt-2 inline-flex items-center rounded-full bg-white/70 px-3 py-1"
        @tap.stop="handleMerchantTap"
      >
        <text class="text-12px font-bold" :style="{ color: bannerData.text_color }">
          {{ bannerData.recommend_merchant.mer_name }}
        </text>
        <text v-if="bannerData.recommend_merchant.discount_label || bannerData.recommend_merchant.distance" class="ml-2 text-11px" :style="{ color: bannerData.text_color }">
          {{ [bannerData.recommend_merchant.discount_label, bannerData.recommend_merchant.distance].filter(Boolean).join(' · ') }}
        </text>
      </view>
    </view>

    <view
      class="ml-3 h-48px w-48px flex flex-shrink-0 items-center justify-center rounded-full bg-white/50"
      :style="{ color: bannerData?.text_color || '#374151' }"
    >
      <text class="i-carbon-bot text-26px" />
    </view>
  </view>
</template>

<style lang="scss" scoped>
.ai-smart-banner {
  display: flex;
  align-items: center;
  padding: 16px;
  border-radius: 16px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
</style>

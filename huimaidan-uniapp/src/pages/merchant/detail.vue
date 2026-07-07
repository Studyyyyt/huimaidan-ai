<script lang="ts" setup>
import type { IStoreDetailResponse } from '@/api/huimaidan'
import { onLoad, onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app'
import {
  addCollection,
  checkCollection,
  getStoreDetail,
  removeCollection,
} from '@/api/huimaidan'
import { useLocationStore, useTokenStore } from '@/store'
import { toLoginPage } from '@/utils/toLoginPage'

defineOptions({
  name: 'MerchantDetail',
})
definePage({
  style: {
    'navigationStyle': 'custom',
    'navigationBarTitleText': '商家详情',
    'mp-alipay': {
      defaultTitle: '商家详情',
      transparentTitle: 'always',
      titlePenetrate: 'YES',
      titleBarColor: '#ffffff',
    },
  },
})

// 商户ID
const merchantId = ref(0)
const aiLogId = ref(0)
const tokenStore = useTokenStore()

// 位置信息
const locationStore = useLocationStore()

// 商家数据
const merchantData = ref<IStoreDetailResponse | null>(null)

// 商户基础信息（从 merchant 字段提取）
const merchant = computed(() => merchantData.value?.merchant || null)

// 展示信息（从 display 字段提取）
const display = computed(() => merchantData.value?.display || null)

// 优惠标签文案
const discountLabel = computed(() => display.value?.discount_label || '')
const discountNumber = computed(() => discountLabel.value ? discountLabel.value.slice(0, -1) : '')
const discountUnit = computed(() => discountLabel.value ? discountLabel.value.slice(-1) : '')

const formattedDistance = computed(() => {
  const distance = display.value?.distance
  if (distance)
    return formatDistanceText(distance)

  const distanceKm = display.value?.distance_km
  return distanceKm ? formatDistanceText(`${distanceKm}km`) : ''
})

function formatDistanceText(distance: string | number) {
  const raw = String(distance).trim()
  const match = raw.match(/^([\d.]+)\s*(m|米|km|公里)?$/i)
  if (!match)
    return raw

  let meters = Number(match[1])
  if (!Number.isFinite(meters))
    return raw

  const unit = match[2]?.toLowerCase()
  if (unit === 'km' || unit === '公里')
    meters *= 1000

  return meters >= 1000 ? `${Number((meters / 1000).toFixed(1))}km` : `${Math.round(meters)}m`
}

// 商户图片列表（轮播用）
const merchantImages = computed(() => {
  const images: string[] = []
  if (merchant.value?.mer_avatar) {
    images.push(merchant.value.mer_avatar)
  }
  if (merchant.value?.mer_banner) {
    images.push(merchant.value.mer_banner)
  }
  // 如果没有图片，使用默认图
  if (images.length === 0) {
    images.push('https://img.yzcdn.cn/vant/cat.jpeg')
  }
  return images
})

const merchantSharePayload = computed(() => {
  const name = merchant.value?.mer_name || '惠买单优惠商家'
  const payload: Record<string, string> = {
    title: `${name}，这家店可以看看`,
    path: `/pages/merchant/detail?id=${merchantId.value}&from=merchant_share`,
  }
  const imageUrl = merchant.value?.mer_avatar || merchantImages.value[0]
  if (imageUrl)
    payload.imageUrl = imageUrl
  return payload
})

// 当前轮播索引
const currentSwiperIndex = ref(0)

// 收藏状态
const isCollected = ref(false)

// 加载状态
const isLoading = ref(true)

// 页面加载
onLoad((options) => {
  if (options?.id) {
    merchantId.value = Number(options.id)
    aiLogId.value = Number(options.ai_log_id) || 0
    fetchMerchantDetail()
    fetchCollectionStatus()
  }
})

// 获取商户详情
async function fetchMerchantDetail() {
  isLoading.value = true
  try {
    const params: { latitude?: number, longitude?: number } = {}
    if (locationStore.hasCoordinates) {
      params.latitude = locationStore.latitude
      params.longitude = locationStore.longitude
    }
    const res = await getStoreDetail(merchantId.value, params)
    merchantData.value = res
  }
  catch (error) {
    console.error('获取商户详情失败:', error)
    uni.showToast({
      title: '商家不存在或未营业',
      icon: 'none',
    })
    setTimeout(() => {
      uni.navigateBack()
    }, 1500)
  }
  finally {
    isLoading.value = false
  }
}

// 获取收藏状态
async function fetchCollectionStatus() {
  await tokenStore.loginReady
  tokenStore.updateNowTime()

  if (!tokenStore.hasLogin) {
    isCollected.value = false
    return
  }

  try {
    const res = await checkCollection(merchantId.value)
    isCollected.value = res?.isCollected ?? false
  }
  catch (error) {
    console.error('获取收藏状态失败:', error)
    uni.showToast({
      title: '获取收藏状态失败',
      icon: 'none',
    })
  }
}

// 轮播切换
function onSwiperChange(e: any) {
  currentSwiperIndex.value = e.detail.current
}

// 返回
function handleBack() {
  uni.navigateBack()
}

// 拨打电话
function handleCall() {
  if (display.value?.phone) {
    uni.makePhoneCall({
      phoneNumber: display.value.phone,
    })
  }
  else {
    uni.showToast({
      title: '暂无联系电话',
      icon: 'none',
    })
  }
}

function resolveMerchantLocation() {
  const latitude = Number.parseFloat(String(display.value?.latitude ?? merchant.value?.lat ?? ''))
  const longitude = Number.parseFloat(String(display.value?.longitude ?? merchant.value?.long ?? ''))

  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    return null
  }
  return { latitude, longitude }
}

// 导航到店
function handleNavigate() {
  const location = resolveMerchantLocation()
  if (!location) {
    uni.showToast({
      title: '暂无位置信息',
      icon: 'none',
    })
    return
  }

  uni.openLocation({
    latitude: location.latitude,
    longitude: location.longitude,
    name: merchant.value?.mer_name || '',
    address: merchant.value?.mer_address || '',
    scale: 18,
  })
}

// 优惠买单 - 跳转到支付页面
function handleBuy() {
  if (!merchant.value)
    return
  uni.navigateTo({
    url: `/pages/payment/checkout?id=${merchant.value.mer_id}&name=${encodeURIComponent(merchant.value.mer_name)}&ai_log_id=${aiLogId.value || ''}`,
  })
}

// 收藏/取消收藏
async function handleCollect() {
  tokenStore.updateNowTime()
  if (!tokenStore.hasLogin) {
    toLoginPage()
    return
  }

  try {
    if (isCollected.value) {
      // 取消收藏
      await removeCollection(merchantId.value)
      isCollected.value = false
      uni.showToast({
        title: '已取消收藏',
        icon: 'success',
      })
    }
    else {
      // 添加收藏
      await addCollection(merchantId.value)
      isCollected.value = true
      uni.showToast({
        title: '已收藏',
        icon: 'success',
      })
    }
  }
  catch (error) {
    console.error('收藏操作失败:', error)
    uni.showToast({
      title: '操作失败，请重试',
      icon: 'none',
    })
  }
}

// 查看全部评价
function handleViewAllReviews() {
  uni.showToast({ title: '评价列表暂未接入后端', icon: 'none' })
}

// 查看更多商家信息
function handleViewMoreInfo() {
  uni.showToast({ title: '更多商家信息暂未接入后端', icon: 'none' })
}

onShareAppMessage(() => merchantSharePayload.value)

onShareTimeline(() => ({
  title: merchantSharePayload.value.title,
  query: `id=${merchantId.value}&from=merchant_share`,
  imageUrl: merchantSharePayload.value.imageUrl,
}))
</script>

<template>
  <view class="detail-page">
    <!-- 加载状态 -->
    <view v-if="isLoading" class="loading-container">
      <text class="loading-text">加载中...</text>
    </view>

    <template v-else-if="merchant">
      <!-- 顶部轮播图 -->
      <view class="swiper-container">
        <swiper
          class="swiper"
          :indicator-dots="false"
          :autoplay="false"
          :circular="true"
          :current="currentSwiperIndex"
          @change="onSwiperChange"
        >
          <swiper-item v-for="(img, index) in merchantImages" :key="index">
            <image :src="img" class="swiper-image" mode="aspectFill" />
          </swiper-item>
        </swiper>

        <!-- 返回按钮 -->
        <view class="back-btn" @tap="handleBack">
          <text class="back-icon">‹</text>
        </view>

        <!-- 页码指示器 -->
        <view class="page-indicator">
          <text class="indicator-text">{{ currentSwiperIndex + 1 }}/{{ merchantImages.length }}</text>
        </view>
      </view>

      <!-- 内容区域 -->
      <view class="content-area">
        <!-- 商家信息卡片 -->
        <view class="info-card">
          <!-- 标题区域 -->
          <view class="title-section">
            <view class="title-row">
              <text class="merchant-name">{{ merchant.mer_name }}</text>
              <text v-if="display?.store_branch_name" class="merchant-slogan">【{{ display.store_branch_name }}】</text>
            </view>
            <!-- 商户标语 -->
            <view v-if="display?.slogan" class="mt-8px">
              <text class="merchant-slogan">{{ display.slogan }}</text>
            </view>
          </view>

          <!-- 标签区域 -->
          <view class="tags-section">
            <view class="tags-row">
              <view v-if="display?.sales_text" class="tag-item">
                <text class="tag-text">{{ display.sales_text }}</text>
              </view>
              <view v-if="display?.category_name" class="tag-item">
                <text class="tag-text">{{ display?.category_name }}</text>
              </view>
              <view v-if="display?.settled_years_text" class="tag-item">
                <text class="tag-text">{{ display.settled_years_text }}</text>
              </view>
              <view v-for="(tag, idx) in (display?.facility_tags || [])" :key="idx" class="tag-item">
                <text class="tag-text">{{ tag }}</text>
              </view>
            </view>
          </view>

          <!-- 联系信息 -->
          <view class="contact-section flex justify-between">
            <view class="rating-row">
              <text v-if="display?.phone" class="i-carbon-phone-filled phone-icon" />
              <text v-if="display?.phone" class="phone-text" @tap="handleCall">{{ display.phone }}</text>
              <view v-if="display?.price_per_person_text" class="ml-16rpx">
                <text class="phone-text">{{ display.price_per_person_text }}</text>
              </view>
            </view>
            <!-- 地址和距离 -->
            <view class="rating-row address-row">
              <text class="i-carbon-location-filled location-icon" />
              <text class="address-text">{{ merchant.mer_address }}</text>
              <view v-if="formattedDistance" class="distance-info">
                <text class="i-carbon-location-current distance-icon" />
                <text class="distance-text">{{ formattedDistance }}</text>
              </view>
            </view>
          </view>

          <!-- 优惠区域 -->
          <view v-if="discountLabel" class="discount-section">
            <view class="discount-left">
              <view class="discount-value-block">
                <text class="discount-value">{{ discountNumber }}</text>
                <text class="discount-unit">{{ discountUnit }}</text>
              </view>
              <text class="discount-tag-text">到店优惠</text>
            </view>
          </view>
        </view>
        <!-- 商户介绍 -->
        <view v-if="merchant.mer_info" class="promo-section">
          <view class="promo-info">
            <rich-text :nodes="merchant.mer_info" class="info-text" />
          </view>
        </view>

        <!-- 促销图 -->
        <view v-if="display?.promo_image" class="promo-section">
          <view class="promo-banner">
            <image :src="display.promo_image" class="promo-image" mode="aspectFill" />
          </view>
        </view>

        <!-- 底部占位 -->
        <view class="bottom-placeholder" />
      </view>
    </template>
    <!-- 底部操作栏 -->
    <view v-if="!isLoading && merchant" class="bottom-bar">
      <view class="action-btn collect-btn" @tap="handleCollect">
        <text v-if="tokenStore.hasLogin && isCollected" class="i-carbon-star-filled collect-icon collected" />
        <text v-else class="i-carbon-star collect-icon" />
        <text class="action-text">{{ tokenStore.hasLogin ? (isCollected ? '已收藏' : '未收藏') : '请登录' }}</text>
      </view>
      <view class="cta-group">
        <view class="action-btn navigate-btn" @tap="handleNavigate">
          <text class="navigate-text">导航到店</text>
        </view>
        <view class="action-btn buy-btn" @tap="handleBuy">
          <text class="action-text-white">优惠买单</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.detail-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 120rpx;
}

// 加载状态
.loading-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}

.loading-text {
  font-size: 28rpx;
  color: #999;
}

// 轮播图区域
.swiper-container {
  position: relative;
  width: 100%;
  height: 500rpx;
}

.swiper {
  width: 100%;
  height: 100%;
}

.swiper-image {
  width: 100%;
  height: 100%;
}

.back-btn {
  position: absolute;
  top: 80rpx;
  left: 24rpx;
  width: 64rpx;
  height: 64rpx;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.back-icon {
  font-size: 40rpx;
  color: #fff;
  font-weight: bold;
}

.page-indicator {
  position: absolute;
  bottom: 24rpx;
  right: 24rpx;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 20rpx;
  padding: 4rpx 16rpx;
}

.indicator-text {
  font-size: 24rpx;
  color: #fff;
}

// 内容区域
.content-area {
  padding: 0;
}

// 信息卡片
.info-card {
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  padding: 32rpx;
  margin-top: 0;
  position: relative;
  z-index: 5;
}

.title-section {
  margin-bottom: 20rpx;
}

.title-row {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8rpx;
}

.merchant-name {
  font-size: 36rpx;
  font-weight: bold;
  color: #0b0969;
}

.merchant-slogan {
  font-size: 28rpx;
  color: #666;
}

// 标签区域
.tags-section {
  margin-bottom: 20rpx;
}

.tags-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8rpx 10rpx;
}

.tag-item {
  display: flex;
  align-items: center;
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  background: #f5f5f5;
}

.tag-icon {
  font-size: 24rpx;
}

.tag-text {
  font-size: 24rpx;
  line-height: 1.3;
  color: #9ca3af;
}

// 评分和联系信息
.contact-section {
  margin-bottom: 20rpx;
  padding-top: 20rpx;
}

.rating-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12rpx;
}

.stars {
  display: flex;
  gap: 4rpx;
}

.star-icon {
  font-size: 24rpx;
  color: #ff9900;
}

.rating-text {
  font-size: 24rpx;
  color: #ff9900;
  font-weight: bold;
}

.phone-icon {
  font-size: 24rpx;
  color: #b8bec8;
  margin-left: 16rpx;
}

.phone-text {
  font-size: 24rpx;
  color: #666;
}

.distance-info {
  display: flex;
  align-items: center;
  gap: 4rpx;
  flex-shrink: 0;
}

.address-row {
  flex: 1;
  min-width: 0;
  flex-wrap: nowrap;
  justify-content: flex-end;
  margin-left: 16rpx;
}

.address-text {
  min-width: 0;
  max-width: 260rpx;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 24rpx;
  color: #666;
}

.location-icon {
  font-size: 24rpx;
  color: #b8bec8;
  flex-shrink: 0;
}

.distance-icon {
  font-size: 24rpx;
  color: #b8bec8;
}

.distance-text {
  font-size: 24rpx;
  color: #666;
}

// 折扣区域
.discount-section {
  display: flex;
  align-items: center;
  padding-top: 20rpx;
}

.discount-left {
  display: flex;
  align-items: center;
  gap: 48rpx;
}

.discount-value-block {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.discount-value {
  font-size: 52rpx;
  font-weight: bold;
  line-height: 1;
  color: #ff3b1f;
}

.discount-unit {
  margin-top: 4rpx;
  font-size: 26rpx;
  font-weight: bold;
  line-height: 1;
  color: #ff3b1f;
}

.discount-tag-text {
  padding: 6rpx 18rpx;
  border-radius: 999rpx;
  background: #fff0ed;
  font-size: 24rpx;
  font-weight: bold;
  color: #ff6a5b;
}

// 促销区域
.promo-section {
  margin-top: 24rpx;
  background: #fff;
  overflow: hidden;
}

.promo-header {
  padding: 24rpx 32rpx;
}

.promo-logo {
  display: flex;
  align-items: center;
  gap: 16rpx;
}

.logo-image {
  width: 64rpx;
  height: 64rpx;
  border-radius: 12rpx;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 28rpx;
  font-weight: bold;
  color: #0b0969;
}

.brand-slogan {
  font-size: 22rpx;
  color: #999;
}

.promo-banner {
  width: 100%;
  height: 400rpx;
}

.promo-image {
  width: 100%;
  height: 100%;
}

.promo-info {
  padding: 32rpx;
}

.info-text {
  font-size: 26rpx;
  color: #666;
  line-height: 1.6;
}

// 底部占位
.bottom-placeholder {
  height: 40rpx;
}

// 底部操作栏
.bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 110rpx;
  background: #fff;
  display: flex;
  align-items: center;
  gap: 24rpx;
  padding: 0 32rpx;
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.collect-btn {
  width: 180rpx;
  flex: 0 0 180rpx;
  flex-direction: row;
  justify-content: flex-start;
  gap: 16rpx;
}

.collect-icon {
  font-size: 52rpx;
  color: #999;
}

.collect-icon.collected {
  color: #f6b300;
}

.action-text {
  font-size: 28rpx;
  color: #666;
}

.cta-group {
  flex: 1;
  height: 80rpx;
  display: flex;
  overflow: hidden;
  border-radius: 40rpx;
}

.navigate-btn {
  flex: 1;
  height: 100%;
  background: #f2e8ff;
}

.navigate-text {
  font-size: 28rpx;
  color: #8c4bfb;
  font-weight: bold;
}

.buy-btn {
  flex: 1;
  height: 100%;
  background: linear-gradient(135deg, #ffc877 0%, #ff4c22 100%);
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-text-white {
  font-size: 28rpx;
  color: #fff;
  font-weight: bold;
}
</style>

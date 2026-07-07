<template>
  <view class="my-coupon-page">
    <!-- 自定义导航栏 -->
    <view class="custom-nav">
      <view class="nav-status-bar" />
      <view class="nav-content">
        <view class="nav-back" @tap="handleBack">
          <text class="i-carbon-arrow-left text-36rpx text-gray-800" />
        </view>
        <text class="nav-title">我的优惠券</text>
        <view class="nav-placeholder" />
      </view>
    </view>

    <!-- 标签栏 -->
    <view class="tabs-bar">
      <view
        v-for="tab in tabs"
        :key="tab.key"
        class="tab-item"
        :class="{ 'tab-item--active': currentTab === tab.key }"
        @tap="handleTabChange(tab.key)"
      >
        <text class="tab-text">{{ tab.name }}</text>
        <view v-if="currentTab === tab.key" class="tab-indicator" />
      </view>
    </view>

    <!-- 优惠券列表 -->
    <scroll-view
      class="coupon-list"
      scroll-y
      :style="{ height: `${scrollHeight}px` }"
    >
      <view v-if="isLoading" class="empty-state">
        <text class="empty-text">加载中...</text>
      </view>
      <view v-else-if="pageError" class="empty-state">
        <text class="empty-text">{{ pageError }}</text>
      </view>
      <!-- 未使用 -->
      <template v-else-if="currentTab === 'unused'">
        <view v-if="unusedCoupons.length === 0" class="empty-state">
          <text class="empty-text">暂无可用优惠券</text>
        </view>
        <view
          v-for="coupon in unusedCoupons"
          :key="coupon.id"
          class="coupon-card"
          @tap="handleCouponDetail(coupon)"
        >
          <view class="coupon-card__left">
            <view class="coupon-card__price">
              <text class="coupon-card__currency">¥</text>
              <text class="coupon-card__amount">{{ coupon.amount }}</text>
            </view>
            <text class="coupon-card__threshold">满{{ coupon.threshold }}元可用</text>
          </view>
          <view class="coupon-card__right">
            <view class="coupon-card__info">
              <text class="coupon-card__name">{{ coupon.name }}</text>
              <text class="coupon-card__expire">有效期: {{ coupon.expireTime }}</text>
            </view>
            <button
              class="coupon-card__btn coupon-card__btn--use"
              @tap.stop="handleUseCoupon(coupon)"
            >
              去使用
            </button>
          </view>
        </view>
      </template>

      <!-- 已使用 -->
      <template v-if="!isLoading && !pageError && currentTab === 'used'">
        <view v-if="usedCoupons.length === 0" class="empty-state">
          <text class="empty-text">暂无已使用优惠券</text>
        </view>
        <view
          v-for="coupon in usedCoupons"
          :key="coupon.id"
          class="coupon-card coupon-card--disabled"
        >
          <view class="coupon-card__left">
            <view class="coupon-card__price">
              <text class="coupon-card__currency">¥</text>
              <text class="coupon-card__amount">{{ coupon.amount }}</text>
            </view>
            <text class="coupon-card__threshold">满{{ coupon.threshold }}元可用</text>
          </view>
          <view class="coupon-card__right">
            <view class="coupon-card__info">
              <text class="coupon-card__name">{{ coupon.name }}</text>
              <text class="coupon-card__expire">使用时间: {{ coupon.usedTime }}</text>
            </view>
            <view class="coupon-card__btn coupon-card__btn--used">
              已使用
            </view>
          </view>
        </view>
      </template>

      <!-- 已过期 -->
      <template v-if="!isLoading && !pageError && currentTab === 'expired'">
        <view v-if="expiredCoupons.length === 0" class="empty-state">
          <text class="empty-text">暂无过期优惠券</text>
        </view>
        <view
          v-for="coupon in expiredCoupons"
          :key="coupon.id"
          class="coupon-card coupon-card--disabled"
        >
          <view class="coupon-card__left">
            <view class="coupon-card__price">
              <text class="coupon-card__currency">¥</text>
              <text class="coupon-card__amount">{{ coupon.amount }}</text>
            </view>
            <text class="coupon-card__threshold">满{{ coupon.threshold }}元可用</text>
          </view>
          <view class="coupon-card__right">
            <view class="coupon-card__info">
              <text class="coupon-card__name">{{ coupon.name }}</text>
              <text class="coupon-card__expire">有效期: {{ coupon.expireTime }}</text>
            </view>
            <view class="coupon-card__btn coupon-card__btn--expired">
              过期
            </view>
          </view>
        </view>
      </template>
    </scroll-view>
  </view>
</template>

<script lang="ts" setup>
import type { ICouponViewItem, TCouponTab } from './my-coupon.helpers'
import { onShow } from '@dcloudio/uni-app'
import { computed, ref } from 'vue'
import { getMyCoupons } from '@/api/huimaidan'
import { useTokenStore } from '@/store/token'
import { mapMyCoupon } from './my-coupon.helpers'

defineOptions({
  name: 'MyCoupon',
})

definePage({
  style: {
    navigationStyle: 'custom',
    navigationBarTitleText: '我的优惠券',
  },
})

const tokenStore = useTokenStore()

// 当前选中的标签
const currentTab = ref<TCouponTab>('unused')

// 标签配置
const tabs = [
  { key: 'unused' as TCouponTab, name: '未使用' },
  { key: 'used' as TCouponTab, name: '已使用' },
  { key: 'expired' as TCouponTab, name: '已过期' },
]

const couponList = ref<ICouponViewItem[]>([])
const isLoading = ref(false)
const pageError = ref('')

// 未使用优惠券
const unusedCoupons = computed(() =>
  couponList.value.filter(c => c.status === 'unused'),
)

// 已使用优惠券
const usedCoupons = computed(() =>
  couponList.value.filter(c => c.status === 'used'),
)

// 已过期优惠券
const expiredCoupons = computed(() =>
  couponList.value.filter(c => c.status === 'expired'),
)

async function fetchCoupons() {
  // 检查登录状态
  if (!tokenStore.updateNowTime().hasLogin) {
    pageError.value = '请先登录'
    isLoading.value = false
    return
  }

  isLoading.value = true
  pageError.value = ''
  try {
    // 不传 statusTag，一次拉取所有优惠券，前端按 status 字段过滤
    const res = await getMyCoupons({
      page: 1,
      limit: 100,
    })
    couponList.value = (res?.list || []).map(mapMyCoupon)
  }
  catch (error) {
    console.error('获取优惠券失败:', JSON.stringify(error))
    pageError.value = '获取优惠券失败'
  }
  finally {
    isLoading.value = false
  }
}

function handleTabChange(tab: TCouponTab) {
  currentTab.value = tab
  fetchCoupons()
}

onShow(() => {
  fetchCoupons()
})

// 滚动区域高度
const scrollHeight = ref(0)

// 获取屏幕高度
uni.getSystemInfo({
  success: (res) => {
    const statusBarHeight = res.statusBarHeight || 0
    const navHeight = 44
    const tabsHeight = 90
    scrollHeight.value = res.windowHeight - statusBarHeight - navHeight - tabsHeight
  },
})

// 返回上一页
function handleBack() {
  uni.navigateBack()
}

// 查看优惠券详情
function handleCouponDetail(coupon: ICouponViewItem) {
  uni.showToast({
    title: coupon.name,
    icon: 'none',
  })
}

// 使用优惠券
function handleUseCoupon(_coupon: ICouponViewItem) {
  uni.switchTab({
    url: '/pages/index/index',
  })
}
</script>

<style lang="scss" scoped>
.my-coupon-page {
  min-height: 100vh;
  background: #f5f5f5;
}

/* 自定义导航栏 */
.custom-nav {
  background: #fff;

  .nav-status-bar {
    height: var(--status-bar-height, 0px);
  }

  .nav-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 88rpx;
    padding: 0 24rpx;
  }

  .nav-back {
    width: 60rpx;
    height: 60rpx;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .nav-title {
    font-size: 34rpx;
    font-weight: 600;
    color: #333;
  }

  .nav-placeholder {
    width: 60rpx;
  }
}

/* 标签栏 */
.tabs-bar {
  display: flex;
  background: #fff;
  border-bottom: 1rpx solid #f0f0f0;
}

.tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 90rpx;
  position: relative;

  &--active {
    .tab-text {
      color: #333;
      font-weight: 600;
    }
  }
}

.tab-text {
  font-size: 28rpx;
  color: #999;
  transition: color 0.3s;
}

.tab-indicator {
  position: absolute;
  bottom: 0;
  width: 48rpx;
  height: 6rpx;
  background: linear-gradient(90deg, #ff6b59, #ff8f5f);
  border-radius: 3rpx;
}

/* 优惠券列表 */
.coupon-list {
  padding: 24rpx;
  box-sizing: border-box;
}

/* 空状态 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 120rpx 0;
}

.empty-text {
  margin-top: 24rpx;
  font-size: 28rpx;
  color: #999;
}

/* 优惠券卡片 */
.coupon-card {
  display: flex;
  background: #fff;
  border-radius: 16rpx;
  margin-bottom: 24rpx;
  overflow: hidden;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);

  &--disabled {
    opacity: 0.7;
  }

  &__left {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 180rpx;
    padding: 24rpx 0;
    background: linear-gradient(135deg, #fff5f5, #fff0f0);
  }

  &__price {
    display: flex;
    align-items: baseline;
  }

  &__currency {
    font-size: 28rpx;
    font-weight: 700;
    color: #ff4c3f;
  }

  &__amount {
    font-size: 56rpx;
    font-weight: 800;
    color: #ff4c3f;
    line-height: 1;
  }

  &__threshold {
    font-size: 22rpx;
    color: #999;
    margin-top: 8rpx;
  }

  &__right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 24rpx 28rpx;
  }

  &__info {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  &__name {
    font-size: 28rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 8rpx;
  }

  &__expire {
    font-size: 22rpx;
    color: #999;
  }

  &__btn {
    min-width: 120rpx;
    height: 52rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 26rpx;
    font-size: 24rpx;
    font-weight: 600;

    &--use {
      background: linear-gradient(135deg, #ff6b59, #ff8f5f);
      color: #fff;
      border: none;
      padding: 0 32rpx;
      line-height: 52rpx;

      &::after {
        border: none;
      }
    }

    &--used {
      background: transparent;
      color: #999;
      border: 2rpx solid #ddd;
    }

    &--expired {
      background: transparent;
      color: #ff4c3f;
      border: 2rpx solid #ff4c3f;
    }
  }
}
</style>

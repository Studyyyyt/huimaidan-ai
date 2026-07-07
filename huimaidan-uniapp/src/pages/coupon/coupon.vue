<template>
  <view class="coupon-page">
    <view class="coupon-poster">
      <view class="poster-orb poster-orb--left" />
      <view class="poster-orb poster-orb--right" />
      <view class="poster-star poster-star--one">
        ✦
      </view>
      <view class="poster-star poster-star--two">
        ✦
      </view>
      <view class="poster-star poster-star--three">
        ✧
      </view>
      <view class="poster-star poster-star--four">
        ✦
      </view>

      <view class="poster-header">
        <text class="poster-title">惠买单微信小程序</text>
        <view class="tutorial-row">
          <view class="tutorial-line" />
          <view class="tutorial-pill">
            用户使用教程
          </view>
          <view class="tutorial-line" />
        </view>
      </view>

      <!-- 新人专享 共享卡片 -->
      <view class="coupon-section">
        <view class="coupon-section__shine" />
        <view class="coupon-section__header">
          <text class="coupon-section__title">新人专享</text>
        </view>

        <view
          class="coupon-section__body"
          :class="{ 'coupon-section__body--with-coupons': !isLoading && couponList.length > 0 }"
        >
          <!-- 加载状态 -->
          <view v-if="isLoading" class="coupon-state">
            <text class="coupon-state__text">优惠券加载中...</text>
          </view>

          <!-- 无优惠券 -->
          <view v-else-if="couponList.length === 0" class="coupon-state">
            <text class="coupon-state__title">当前没有可领取的优惠券</text>
            <text class="coupon-state__desc">有新优惠会第一时间展示</text>
          </view>

          <!-- 优惠券两列网格 -->
          <view v-else class="coupon-grid">
            <view
              v-for="coupon in couponList"
              :key="coupon.coupon_id"
              class="coupon-cell"
              :class="{ 'coupon-cell--received': coupon.issue }"
              :hover-class="coupon.issue || isReceiving ? '' : 'coupon-cell--active'"
              @tap="onCellTap(coupon)"
            >
              <view class="coupon-cell__amount">
                <text class="coupon-cell__currency">¥</text>
                <text class="coupon-cell__number">{{ coupon.coupon_price }}</text>
              </view>
              <view class="coupon-cell__badge">
                {{ coupon.title }}
              </view>

              <!-- 已领取遮罩 -->
              <view v-if="coupon.issue" class="coupon-cell__mask">
                <text class="coupon-cell__mask-tag">已领取</text>
              </view>
            </view>
          </view>
        </view>
      </view>

      <view class="rules-decor-card mt-10">
        <!-- 星球装饰 -->
        <view class="planet-decoration -top-4 right-0">
          <view class="planet-decoration__glow" />
          <view class="planet-decoration__ring" />
          <view class="planet-decoration__body" />
          <view class="planet-decoration__spark" />
        </view>
        <view class="rules-card -mt-8">
          <view class="rules-card__time px-3 py-1.5 text-xs">
            <text class="font-bold">活动时间：</text>
            <text>2026.4.1—2027.3.31</text>
          </view>

          <view class="rules-list">
            <view class="rules-item">
              <view class="rules-item__dot" />
              <view class="rules-item__content">
                <text class="rules-item__main">新用户首次登录赠送100元优惠券</text>
                <text class="rules-item__note mt-1">（注：每张10元，可领取10张，每次消费限用一张）</text>
              </view>
            </view>

            <view class="rules-item">
              <view class="rules-item__dot" />
              <view class="rules-item__content">
                <text class="rules-item__main">会员每推荐一名新用户可得5积分</text>
                <text class="rules-item__note mt-1">（注：500积分封顶，1积分可抵1元现金使用）</text>
              </view>
            </view>

            <view class="rules-item">
              <view class="rules-item__dot" />
              <view class="rules-item__content">
                <text class="rules-item__main">会员推荐新用户满200名，升级为平台VIP用户</text>
                <text class="rules-item__note mt-1">（注：全场可享受更低折扣）</text>
              </view>
            </view>
          </view>
        </view>
      </view>

      <view class="tutorial-card pb-10">
        <image
          class="tutorial-card__search -top-4 -right-3 size-20"
          src="/static/images/coupon/tutorial-search.webp"
          mode="aspectFit"
        />
        <image
          class="tutorial-card__gift"
          src="/static/images/coupon/tutorial-gift.webp"
          mode="aspectFit"
        />
        <text class="tutorial-card__title">如何找到惠买单小程序</text>

        <view class="tutorial-video">
          <video
            v-if="tutorialVideoSrc"
            class="tutorial-video__player"
            :src="tutorialVideoSrc"
            controls
            object-fit="cover"
          />
          <view v-else class="tutorial-video__empty">
            <view class="tutorial-video__play" />
            <text>视频待上传</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script lang="ts" setup>
import type { ICouponItem } from '@/api/huimaidan'
import { onLoad } from '@dcloudio/uni-app'
import { ref } from 'vue'
import { getAppConfig, getCouponList, receiveCoupon } from '@/api/huimaidan'
import { getEnvBaseUrl } from '@/utils'

defineOptions({
  name: 'Coupon',
})
definePage({
  style: {
    'navigationStyle': 'custom',
    'navigationBarTitleText': '优惠券',
    'mp-alipay': {
      defaultTitle: '优惠券',
      transparentTitle: 'always',
      titlePenetrate: 'YES',
      titleBarColor: '#ffffff',
    },
  },
})

// 优惠券列表
const couponList = ref<ICouponItem[]>([])
const isLoading = ref(false)
const isReceiving = ref(false)
const tutorialVideoSrc = ref('')

// 获取优惠券列表
async function fetchCoupons() {
  isLoading.value = true
  try {
    const res = await getCouponList({
      page: 1,
      limit: 50,
    })
    couponList.value = res?.list || []
  }
  catch (error) {
    console.error('获取优惠券失败:', error)
    uni.showToast({ title: '获取优惠券失败', icon: 'none' })
  }
  finally {
    isLoading.value = false
  }
}

// 点击券格领取（已领取或领取中则忽略）
function onCellTap(coupon: ICouponItem) {
  if (coupon.issue || isReceiving.value) {
    return
  }
  handleReceive(coupon)
}

// 领取优惠券
async function handleReceive(coupon: ICouponItem) {
  // 检查登录状态
  const token = uni.getStorageSync('token')
  if (!token) {
    uni.navigateTo({ url: '/pages/auth/login' })
    return
  }

  isReceiving.value = true
  try {
    await receiveCoupon(coupon.coupon_id)
    // 标记为已领取
    coupon.issue = 1
    uni.showToast({ title: '领取成功', icon: 'success' })
  }
  catch {
    // HTTP 模块已通过 uni.showToast 显示后端返回的错误信息
    // 这里不再重复弹窗，仅标记状态
    console.warn('优惠券领取失败')
  }
  finally {
    isReceiving.value = false
  }
}

// 获取使用说明视频地址
async function fetchTutorialVideo() {
  try {
    const res = await getAppConfig()
    if (res?.coupon_tutorial_video) {
      const base = getEnvBaseUrl()
      const path = res.coupon_tutorial_video as string
      tutorialVideoSrc.value = path.startsWith('http') ? path : `${base}${path.startsWith('/') ? '' : '/'}${path}`
    }
  }
  catch {
    // 静默失败，不影响页面展示
  }
}

onLoad(() => {
  fetchCoupons()
  fetchTutorialVideo()
})
</script>

<style lang="scss" scoped>
.coupon-page {
  min-height: 100vh;
  background: #a98dff;
}

.coupon-poster {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  box-sizing: border-box;
  padding: 86rpx 28rpx calc(56rpx + env(safe-area-inset-bottom));
  background:
    radial-gradient(circle at 12% 62%, rgba(210, 244, 255, 0.78) 0 13%, transparent 28%),
    radial-gradient(circle at 86% 72%, rgba(153, 148, 255, 0.38) 0 11%, transparent 29%),
    linear-gradient(145deg, #9377f8 0%, #ffa173 48%, #c7f1ff 100%);
  color: #fff;
}

.coupon-poster::before {
  content: '';
  position: absolute;
  left: -120rpx;
  bottom: -68rpx;
  width: 360rpx;
  height: 260rpx;
  border-radius: 50%;
  background: rgba(180, 236, 255, 0.42);
  filter: blur(2rpx);
}

.coupon-poster::after {
  content: '';
  position: absolute;
  right: -96rpx;
  bottom: -58rpx;
  width: 280rpx;
  height: 220rpx;
  border-radius: 50%;
  background: rgba(193, 172, 255, 0.45);
  filter: blur(2rpx);
}

.poster-orb {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  pointer-events: none;
}

.poster-orb--left {
  top: 154rpx;
  left: -64rpx;
  width: 196rpx;
  height: 196rpx;
}

.poster-orb--right {
  top: 126rpx;
  right: -58rpx;
  width: 182rpx;
  height: 182rpx;
  background: rgba(255, 244, 216, 0.36);
}

.poster-star {
  position: absolute;
  z-index: 3;
  color: rgba(255, 255, 255, 0.94);
  font-weight: 700;
  line-height: 1;
  text-shadow: 0 0 12rpx rgba(255, 255, 255, 0.65);
}

.poster-star--one {
  top: 184rpx;
  left: 162rpx;
  font-size: 28rpx;
}

.poster-star--two {
  top: 206rpx;
  right: 126rpx;
  font-size: 30rpx;
}

.poster-star--three {
  top: 728rpx;
  left: 50rpx;
  font-size: 24rpx;
}

.poster-star--four {
  top: 664rpx;
  right: 210rpx;
  font-size: 18rpx;
}

.poster-header {
  position: relative;
  z-index: 4;
  text-align: center;
}

.poster-title {
  display: block;
  font-size: 39rpx;
  font-weight: 800;
  line-height: 1;
  letter-spacing: 1rpx;
  color: #fff;
  text-shadow: 0 4rpx 16rpx rgba(102, 70, 190, 0.18);
}

.tutorial-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18rpx;
  margin-top: 50rpx;
}

.tutorial-line {
  width: 64rpx;
  height: 2rpx;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.46));
}

.tutorial-line:last-child {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.46), transparent);
}

.tutorial-pill {
  min-width: 248rpx;
  box-sizing: border-box;
  padding: 11rpx 30rpx 12rpx;
  border-radius: 999rpx;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 4rpx 14rpx rgba(135, 90, 190, 0.08);
  color: #3f3a4b;
  font-size: 31rpx;
  font-weight: 800;
  line-height: 1;
}

/* 新人专享 共享卡片 */
.coupon-section {
  position: relative;
  z-index: 5;
  margin-top: 32rpx;
  overflow: hidden;
  border-radius: 28rpx 28rpx 26rpx 26rpx;
  background: #fff;
  box-shadow: 0 18rpx 40rpx rgba(130, 88, 220, 0.14);
}

.coupon-section__shine {
  position: absolute;
  z-index: 1;
  left: 0;
  right: 0;
  top: 0;
  height: 122rpx;
  overflow: hidden;
}

.coupon-section__shine::before {
  content: '';
  position: absolute;
  left: 50%;
  top: -254rpx;
  width: 1080rpx;
  height: 360rpx;
  border-radius: 50%;
  transform: translateX(-50%);
  background:
    radial-gradient(circle at 18% 28%, rgba(255, 234, 170, 0.55) 0 12%, transparent 28%),
    radial-gradient(circle at 86% 26%, rgba(255, 187, 164, 0.32) 0 14%, transparent 30%),
    linear-gradient(95deg, #ffd76a 0%, #ff8f5f 48%, #ff5e50 100%);
}

.coupon-section__header {
  position: relative;
  z-index: 2;
  height: 94rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.coupon-section__title {
  color: #fff;
  font-size: 46rpx;
  font-weight: 800;
  line-height: 1;
  letter-spacing: 2rpx;
  text-shadow: 0 4rpx 14rpx rgba(198, 59, 33, 0.18);
}

.coupon-section__body {
  position: relative;
  z-index: 3;
  padding: 30rpx 24rpx 28rpx;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), #fff 28%);
}

.coupon-section__body--with-coupons {
  margin-top: 28rpx;
  padding-top: 2rpx;
}

/* 加载 / 空状态 */
.coupon-state {
  min-height: 160rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.coupon-state__title {
  color: #6a5c82;
  font-size: 27rpx;
  font-weight: 800;
  line-height: 1.3;
}

.coupon-state__desc {
  margin-top: 12rpx;
  color: #9a8fab;
  font-size: 23rpx;
  line-height: 1.3;
}

.coupon-state__text {
  color: #6a5c82;
  font-size: 27rpx;
  font-weight: 700;
}

/* 两列网格 */
.coupon-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

/* 单个券格 */
.coupon-cell {
  position: relative;
  width: calc(50% - 12rpx);
  box-sizing: border-box;
  margin-top: 24rpx;
  padding: 22rpx 12rpx 18rpx;
  border-radius: 14rpx;
  background: linear-gradient(90deg, #ece6fe, #c5b2fd);
  box-shadow:
    inset 0 -8rpx 0 rgba(255, 255, 255, 0.18),
    0 8rpx 20rpx rgba(130, 88, 220, 0.14);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.coupon-cell--active {
  background: linear-gradient(90deg, #ece6fe, #c5b2fd);
}

/* 两侧波浪豁口（票券撕口感） */
.coupon-cell::before,
.coupon-cell::after {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  width: 16rpx;
  z-index: 10;
  background:
    radial-gradient(circle at 50% 10rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 34rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 58rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 82rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 106rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 130rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 154rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 178rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 202rpx, #fff 6rpx, transparent 6.5rpx),
    radial-gradient(circle at 50% 226rpx, #fff 6rpx, transparent 6.5rpx);
}

.coupon-cell::before {
  left: -8rpx;
}

.coupon-cell::after {
  right: -8rpx;
}

.coupon-cell__amount {
  display: flex;
  align-items: baseline;
  justify-content: center;
}

.coupon-cell__currency {
  margin-right: 4rpx;
  color: #ff4c3f;
  font-size: 26rpx;
  font-weight: 900;
}

.coupon-cell__number {
  color: #ff4c3f;
  font-size: 76rpx;
  font-weight: 900;
  line-height: 0.9;
  letter-spacing: -4rpx;
}

.coupon-cell__badge {
  margin-top: 12rpx;
  box-sizing: border-box;
  padding: 7rpx 18rpx;
  border-radius: 999rpx;
  background: linear-gradient(90deg, #ffb95a 0%, #ff6b59 100%);
  color: #fff;
  font-size: 19rpx;
  font-weight: 800;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
}

.coupon-cell__name {
  margin-top: 12rpx;
  max-width: 100%;
  color: #6a5c82;
  font-size: 21rpx;
  font-weight: 700;
  line-height: 1.3;
  text-align: center;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

/* 已领取遮罩 */
.coupon-cell--received {
  opacity: 0.6;
}

.coupon-cell__mask {
  position: absolute;
  inset: 0;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.35);
}

.coupon-cell__mask-tag {
  padding: 8rpx 22rpx;
  border-radius: 999rpx;
  background: rgba(106, 92, 130, 0.85);
  color: #fff;
  font-size: 22rpx;
  font-weight: 800;
}

.planet-decoration {
  position: absolute;
  z-index: 6;
  width: 154rpx;
  height: 128rpx;
  pointer-events: none;
}

.planet-decoration__glow {
  position: absolute;
  top: 4rpx;
  left: 16rpx;
  width: 92rpx;
  height: 92rpx;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(255, 255, 255, 0.9),
    rgba(128, 181, 255, 0.68) 42%,
    rgba(177, 114, 249, 0.18) 70%,
    transparent 72%
  );
  box-shadow: 0 0 28rpx rgba(255, 255, 255, 0.72);
}

.planet-decoration__body {
  position: absolute;
  top: 18rpx;
  left: 28rpx;
  width: 72rpx;
  height: 72rpx;
  border-radius: 50%;
  background:
    radial-gradient(circle at 30% 22%, rgba(255, 255, 255, 0.96) 0 10%, transparent 22%),
    linear-gradient(135deg, #ffffff 0%, #8cc5ff 38%, #9b7cf7 78%, #ffb5c4 100%);
  box-shadow: inset -10rpx -10rpx 18rpx rgba(104, 81, 195, 0.2);
}

.planet-decoration__ring {
  position: absolute;
  top: 44rpx;
  left: 5rpx;
  width: 142rpx;
  height: 42rpx;
  border: 8rpx solid rgba(255, 215, 167, 0.62);
  border-left-color: rgba(148, 136, 255, 0.24);
  border-radius: 50%;
  transform: rotate(-18deg);
  box-shadow: 0 0 12rpx rgba(255, 255, 255, 0.42);
}

.planet-decoration__spark {
  position: absolute;
  top: 12rpx;
  right: 20rpx;
  width: 20rpx;
  height: 20rpx;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.85);
  box-shadow:
    -96rpx -20rpx 0 -5rpx rgba(255, 255, 255, 0.8),
    -136rpx 46rpx 0 -6rpx rgba(255, 255, 255, 0.7);
}

.rules-decor-card {
  position: relative;
  z-index: 5;
  padding: 30rpx 20rpx 28rpx;
  border-radius: 24rpx;
  background:
    radial-gradient(circle at 8% 28%, rgba(210, 244, 255, 0.78) 0 14%, transparent 36%),
    radial-gradient(circle at 88% 76%, rgba(180, 141, 255, 0.42) 0 18%, transparent 42%),
    linear-gradient(135deg, rgba(200, 246, 255, 0.92), rgba(220, 176, 255, 0.86));
  box-shadow: 0 16rpx 38rpx rgba(101, 108, 214, 0.12);
}

.rules-card {
  position: relative;
  padding: 28rpx 28rpx 24rpx;
  border-radius: 18rpx;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 10rpx 24rpx rgba(101, 108, 214, 0.08);
}

.rules-card__time {
  display: inline-flex;
  align-items: center;
  box-sizing: border-box;
  border-radius: 999rpx;
  background: linear-gradient(90deg, #ffc865 0%, #ff6b59 100%);
  color: #fff;
  line-height: 1;
}

.rules-list {
  margin-top: 22rpx;
}

.rules-item {
  display: flex;
  align-items: flex-start;
  margin-top: 19rpx;
}

.rules-item:first-child {
  margin-top: 0;
}

.rules-item__dot {
  flex: 0 0 auto;
  width: 8rpx;
  height: 8rpx;
  margin-top: 14rpx;
  margin-right: 10rpx;
  border-radius: 2rpx;
  background: #6552d9;
  box-shadow: 0 0 0 2rpx rgba(101, 82, 217, 0.16);
}

.rules-item__content {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.rules-item__main {
  color: #5743d4;
  font-size: 24rpx;
  font-weight: 900;
  line-height: 1.35;
}

.rules-item__note {
  color: #7b7181;
  font-size: 22rpx;
  line-height: 1.45;
}

.tutorial-card {
  position: relative;
  z-index: 5;
  margin-top: 42rpx;
  padding: 34rpx 34rpx 40rpx;
  border-radius: 26rpx;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 18rpx 42rpx rgba(101, 108, 214, 0.12);
}

.tutorial-card__search {
  position: absolute;
  z-index: 4;
  pointer-events: none;
}

.tutorial-card__gift {
  position: absolute;
  z-index: 4;
  left: -8rpx;
  bottom: -12rpx;
  width: 142rpx;
  height: 100rpx;
  pointer-events: none;
}

.tutorial-card__title {
  position: relative;
  z-index: 2;
  display: block;
  padding-right: 112rpx;
  color: #2f2738;
  font-size: 34rpx;
  font-weight: 900;
  line-height: 1.25;
}

.tutorial-video {
  position: relative;
  z-index: 2;
  margin-top: 32rpx;
  overflow: hidden;
  border: 2rpx solid rgba(204, 185, 255, 0.72);
  border-radius: 18rpx;
  background: linear-gradient(135deg, #f6f3ff, #eef8ff);
}

.tutorial-video__player,
.tutorial-video__empty {
  width: 100%;
  height: 188rpx;
}

.tutorial-video__empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #7b6c96;
  font-size: 24rpx;
  font-weight: 800;
}

.tutorial-video__play {
  width: 64rpx;
  height: 64rpx;
  margin-bottom: 12rpx;
  border-radius: 50%;
  background: linear-gradient(135deg, #ffb95a, #ff6b59);
  position: relative;
  box-shadow: 0 8rpx 18rpx rgba(255, 107, 89, 0.2);
}

.tutorial-video__play::after {
  content: '';
  position: absolute;
  left: 25rpx;
  top: 18rpx;
  width: 0;
  height: 0;
  border-top: 14rpx solid transparent;
  border-bottom: 14rpx solid transparent;
  border-left: 20rpx solid #fff;
}
</style>

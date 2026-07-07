<script lang="ts" setup>
import type { ISpreadPoster } from '@/api/huimaidan'
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app'
import { storeToRefs } from 'pinia'
import { computed, ref } from 'vue'
import { getSpreadPoster } from '@/api/huimaidan'
import { useUserStore } from '@/store'
import { useTokenStore } from '@/store/token'

definePage({
  style: {
    navigationBarTitleText: '分享海报',
    navigationStyle: 'custom',
  },
})

const userStore = useUserStore()
const tokenStore = useTokenStore()
const { userInfo } = storeToRefs(userStore)

const posterLoading = ref(false)
const qrcode = ref('')
const pageError = ref('')
const posterInfo = ref<ISpreadPoster | null>(null)

const displayNickname = computed(() => {
  return posterInfo.value?.nickname || userInfo.value.nickname || userInfo.value.username || '微信用户'
})

const userAvatar = computed(() => userInfo.value.avatar || '/static/images/default-avatar.png')

const sharePath = computed(() => {
  const uid = getCurrentUid()
  return uid > 0 ? `/pages/index/index?spid=${uid}` : '/pages/index/index'
})

const canShare = computed(() => Boolean(qrcode.value) && !posterLoading.value)

function getCurrentUid() {
  const uid = Number(userInfo.value.uid || userInfo.value.userId || 0)
  return Number.isFinite(uid) ? uid : 0
}

function getErrorMessage(error: unknown, fallback: string) {
  if (error instanceof Error && error.message)
    return error.message
  if (typeof error === 'object' && error !== null) {
    const payload = error as Record<string, any>
    return payload.message || payload.msg || payload.data?.msg || fallback
  }
  return fallback
}

async function loadSpreadPoster() {
  posterLoading.value = true
  pageError.value = ''
  qrcode.value = ''

  try {
    const res = await getSpreadPoster(tokenStore.hasLogin ? undefined : { skipUnauthorizedHandler: true })
    posterInfo.value = res
    const qrUrl = typeof res?.qrcode === 'string' ? res.qrcode.trim() : ''
    if (!qrUrl)
      throw new Error('分享二维码生成失败')

    qrcode.value = qrUrl
  }
  catch (error) {
    const message = getErrorMessage(error, '分享二维码生成失败')
    pageError.value = message
    console.error('分享二维码获取失败:', error)
    uni.showToast({ title: message, icon: 'none' })
  }
  finally {
    posterLoading.value = false
  }
}

function handleBack() {
  uni.navigateBack({
    delta: 1,
    fail: () => {
      uni.switchTab({ url: '/pages/me/me' })
    },
  })
}

function copyShareLink() {
  const uid = getCurrentUid()
  if (uid <= 0) {
    uni.showToast({ title: '分享链接生成失败', icon: 'none' })
    return
  }

  uni.setClipboardData({
    data: sharePath.value,
    success: () => {
      uni.showToast({ title: '分享链接已复制', icon: 'success' })
    },
    fail: (error) => {
      const message = getErrorMessage(error, '复制分享链接失败')
      uni.showToast({ title: message, icon: 'none' })
    },
  })
}

function handleSharePosterTap() {
  if (posterLoading.value)
    return

  if (!qrcode.value) {
    uni.showToast({ title: pageError.value || '分享二维码生成失败', icon: 'none' })
  }
}

function handleQrcodeImageError(error: unknown) {
  const failedUrl = qrcode.value
  const message = '二维码图片加载失败'
  qrcode.value = ''
  pageError.value = message
  console.error('分享二维码图片加载失败:', { url: failedUrl, error })
  uni.showToast({ title: message, icon: 'none' })
}

onShareAppMessage(() => {
  const payload: Record<string, string> = {
    title: `${displayNickname.value}邀您共享会员卡`,
    path: sharePath.value,
  }
  if (qrcode.value)
    payload.imageUrl = qrcode.value
  return payload
})

onLoad(() => {
  loadSpreadPoster()
})
</script>

<template>
  <view class="share-poster-page">
    <view class="poster-nav">
      <view class="nav-content">
        <view class="nav-back" @tap="handleBack">
          <text class="i-carbon-chevron-left nav-back-icon" />
        </view>
        <text class="nav-title">分享海报</text>
        <view class="nav-placeholder" />
      </view>
    </view>

    <scroll-view class="poster-scroll" scroll-y>
      <view class="poster-shell">
        <view class="poster-card">
          <view class="hero-visual">
            <view class="hero-glow hero-glow--left" />
            <view class="hero-glow hero-glow--right" />
            <view class="hero-medallion" />
            <view class="hero-diamond" />
            <view class="hero-orbit hero-orbit--one" />
            <view class="hero-orbit hero-orbit--two" />
            <view class="hero-orbit hero-orbit--three" />
            <text class="hero-title">惠买单</text>
            <text class="hero-coin">￥</text>
          </view>

          <text class="poster-heading">邀您共享会员卡</text>

          <view class="ticket-cut">
            <view class="ticket-cut__dot ticket-cut__dot--left" />
            <view class="ticket-cut__line" />
            <view class="ticket-cut__dot ticket-cut__dot--right" />
          </view>

          <view class="invite-panel">
            <view class="inviter">
              <image class="inviter-avatar" :src="userAvatar" mode="aspectFill" />
              <view class="inviter-info">
                <text class="inviter-name">{{ displayNickname }}</text>
                <text class="inviter-desc">推荐您加入</text>
              </view>
            </view>

            <view class="qr-card">
              <image v-if="qrcode" class="qr-image" :src="qrcode" mode="aspectFit" @error="handleQrcodeImageError" />
              <view v-else class="qr-state">
                <text class="qr-state-text">{{ posterLoading ? '生成中' : pageError }}</text>
              </view>
            </view>
          </view>

          <view class="ticket-teeth">
            <view v-for="item in 18" :key="item" class="ticket-tooth" />
          </view>
        </view>

        <view class="poster-rules">
          <text class="rule-text">1. 转发链接或图片给微信好友；</text>
          <text class="rule-text">2. 从您转发的链接或图片进入商城的好友，系统将自动锁定成为您的客户，他们在商城中购买商品，您就可以获得佣金；</text>
          <text class="rule-text">3. 您可以在会员中心查看【我的团队】和【分销订单】，好友确认收货后佣金方可提现。</text>
        </view>
      </view>
    </scroll-view>

    <view class="poster-actions">
      <button class="action-btn copy-btn" @tap="copyShareLink">
        复制分享链接
      </button>
      <button
        class="action-btn share-btn"
        :class="{ 'share-btn--disabled': !canShare }"
        :disabled="!canShare"
        open-type="share"
        @tap="handleSharePosterTap"
      >
        分享海报
      </button>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.share-poster-page {
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
  background:
    linear-gradient(138deg, rgba(169, 207, 255, 0.92) 0%, rgba(245, 218, 255, 0.94) 48%, rgba(205, 227, 255, 0.96) 100%);
}

.poster-nav {
  background: rgba(248, 249, 255, 0.96);
  padding-top: var(--status-bar-height, 0px);
}

.nav-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 88rpx;
  padding: 0 30rpx;
}

.nav-back,
.nav-placeholder {
  width: 64rpx;
  height: 64rpx;
}

.nav-back {
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-back-icon {
  color: #5c5c68;
  font-size: 38rpx;
}

.nav-title {
  color: #31313d;
  font-size: 34rpx;
  font-weight: 700;
  line-height: 44rpx;
}

.poster-scroll {
  height: calc(100vh - 88rpx - var(--status-bar-height, 0px));
}

.poster-shell {
  padding: 42rpx 36rpx 156rpx;
  box-sizing: border-box;
}

.poster-card {
  position: relative;
  overflow: hidden;
  border-radius: 26rpx;
  background: #fff;
  box-shadow: 0 18rpx 46rpx rgba(112, 91, 190, 0.14);
}

.hero-visual {
  position: relative;
  height: 628rpx;
  margin: 28rpx 28rpx 0;
  overflow: hidden;
  border-radius: 18rpx;
  background:
    radial-gradient(circle at 78% 18%, rgba(255, 255, 255, 0.88) 0, rgba(255, 255, 255, 0) 25%),
    radial-gradient(circle at 14% 78%, rgba(105, 95, 237, 0.34) 0, rgba(105, 95, 237, 0) 31%),
    linear-gradient(135deg, #a88cf7 0%, #e2b2ff 48%, #ffd7ec 100%);
}

.hero-glow,
.hero-medallion,
.hero-diamond,
.hero-orbit,
.hero-title,
.hero-coin {
  position: absolute;
}

.hero-glow {
  width: 190rpx;
  height: 190rpx;
  border-radius: 50%;
  background: rgba(255, 237, 168, 0.55);
}

.hero-glow--left {
  left: 72rpx;
  top: 235rpx;
}

.hero-glow--right {
  right: 46rpx;
  top: 186rpx;
  background: rgba(255, 176, 239, 0.48);
}

.hero-medallion {
  left: 118rpx;
  top: 74rpx;
  width: 390rpx;
  height: 390rpx;
  border: 24rpx solid #f3c86b;
  border-radius: 50%;
  box-sizing: border-box;
  box-shadow: inset 0 10rpx 26rpx rgba(142, 74, 190, 0.24), 0 18rpx 28rpx rgba(126, 76, 181, 0.2);
}

.hero-diamond {
  left: 190rpx;
  top: 126rpx;
  width: 246rpx;
  height: 246rpx;
  border: 16rpx solid rgba(255, 255, 255, 0.45);
  border-radius: 34rpx;
  background: linear-gradient(135deg, rgba(119, 68, 214, 0.82), rgba(210, 134, 255, 0.72));
  box-shadow: inset 0 10rpx 18rpx rgba(255, 255, 255, 0.25), 0 18rpx 34rpx rgba(90, 50, 156, 0.25);
  transform: rotate(45deg);
}

.hero-orbit {
  left: 70rpx;
  top: 188rpx;
  width: 492rpx;
  height: 166rpx;
  border: 10rpx solid rgba(229, 152, 255, 0.8);
  border-radius: 50%;
  box-sizing: border-box;
  box-shadow: 0 0 18rpx rgba(255, 230, 154, 0.72);
}

.hero-orbit--one {
  transform: rotate(-12deg);
}

.hero-orbit--two {
  border-color: rgba(127, 83, 236, 0.68);
  transform: rotate(22deg);
}

.hero-orbit--three {
  left: 116rpx;
  top: 158rpx;
  width: 410rpx;
  height: 132rpx;
  border-width: 6rpx;
  border-color: rgba(255, 238, 170, 0.7);
  transform: rotate(-34deg);
}

.hero-title {
  left: 88rpx;
  right: 76rpx;
  top: 222rpx;
  z-index: 3;
  color: #fff2a8;
  font-size: 108rpx;
  font-weight: 900;
  letter-spacing: 0;
  line-height: 122rpx;
  text-align: center;
  text-shadow:
    0 6rpx 0 #6d37bc,
    0 12rpx 18rpx rgba(91, 48, 162, 0.48);
}

.hero-coin {
  left: 316rpx;
  top: 238rpx;
  z-index: 4;
  width: 52rpx;
  height: 52rpx;
  border: 6rpx solid rgba(121, 60, 170, 0.92);
  border-radius: 50%;
  background: #fff5b4;
  color: #a14eca;
  font-size: 26rpx;
  font-weight: 900;
  line-height: 44rpx;
  text-align: center;
}

.poster-heading {
  display: block;
  margin: 42rpx 0 34rpx;
  color: #5a22b8;
  font-size: 48rpx;
  font-weight: 900;
  letter-spacing: 0;
  line-height: 58rpx;
  text-align: center;
}

.ticket-cut {
  position: relative;
  display: flex;
  align-items: center;
  height: 34rpx;
}

.ticket-cut__line {
  flex: 1;
  height: 0;
  border-top: 4rpx dashed #ded8f0;
}

.ticket-cut__dot {
  width: 34rpx;
  height: 34rpx;
  border-radius: 50%;
  background: #d2c8fa;
}

.ticket-cut__dot--left {
  margin-left: -17rpx;
}

.ticket-cut__dot--right {
  margin-right: -17rpx;
}

.invite-panel {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 194rpx;
  padding: 30rpx 42rpx 30rpx 38rpx;
  box-sizing: border-box;
}

.inviter {
  display: flex;
  min-width: 0;
  flex: 1;
  align-items: center;
}

.inviter-avatar {
  width: 120rpx;
  height: 120rpx;
  flex-shrink: 0;
  border-radius: 50%;
  background: #eceef2;
}

.inviter-info {
  min-width: 0;
  margin-left: 24rpx;
}

.inviter-name {
  display: block;
  max-width: 250rpx;
  overflow: hidden;
  color: #2d2d33;
  font-size: 32rpx;
  font-weight: 700;
  line-height: 42rpx;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.inviter-desc {
  display: block;
  margin-top: 8rpx;
  color: #b3b1bb;
  font-size: 28rpx;
  font-weight: 600;
  line-height: 36rpx;
}

.qr-card {
  width: 172rpx;
  height: 172rpx;
  flex-shrink: 0;
}

.qr-image,
.qr-state {
  width: 100%;
  height: 100%;
}

.qr-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12rpx;
  box-sizing: border-box;
  border: 2rpx dashed #d6ccef;
  border-radius: 10rpx;
  background: #fbfaff;
}

.qr-state-text {
  color: #8c82a9;
  font-size: 22rpx;
  line-height: 30rpx;
  text-align: center;
}

.ticket-teeth {
  display: flex;
  height: 28rpx;
  padding: 0 24rpx;
  box-sizing: border-box;
  background: #fff;
}

.ticket-tooth {
  width: 0;
  height: 0;
  flex: 1;
  border-top: 28rpx solid #fff;
  border-left: 12rpx solid transparent;
  border-right: 12rpx solid transparent;
}

.poster-rules {
  padding: 32rpx 40rpx 0;
}

.rule-text {
  display: block;
  color: #8f91a0;
  font-size: 24rpx;
  line-height: 42rpx;
}

.poster-actions {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  padding: 24rpx 82rpx calc(24rpx + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: rgba(236, 242, 255, 0.9);
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 84rpx;
  flex: 1;
  margin: 0;
  border: 0;
  border-radius: 0;
  color: #fff;
  font-size: 30rpx;
  font-weight: 800;
  line-height: 84rpx;
}

.action-btn::after {
  border: 0;
}

.copy-btn {
  border-radius: 42rpx 0 0 42rpx;
  background: #ece0ff;
  color: #9a66ff;
}

.share-btn {
  border-radius: 0 42rpx 42rpx 0;
  background: linear-gradient(135deg, #b05cff 0%, #5d83ff 100%);
}

.share-btn--disabled {
  background: #b9b2d0;
  color: rgba(255, 255, 255, 0.82);
}
</style>

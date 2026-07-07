<script lang="ts" setup>
import type { ISpreadTeamMember } from '@/api/huimaidan'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { ref } from 'vue'
import { getSpreadTeamList } from '@/api/huimaidan'
import { LOGIN_PAGE } from '@/router/config'
import { useTokenStore } from '@/store/token'

definePage({
  style: {
    navigationBarTitleText: '我的团队',
    navigationStyle: 'custom',
  },
})

const tokenStore = useTokenStore()
const members = ref<ISpreadTeamMember[]>([])
const page = ref(1)
const limit = 20
const total = ref(0)
const loading = ref(false)
const finished = ref(false)
const pageError = ref('')

function getErrorMessage(error: unknown, fallback: string) {
  if (error instanceof Error && error.message)
    return error.message
  if (typeof error === 'object' && error !== null) {
    const payload = error as Record<string, any>
    return payload.message || payload.msg || payload.data?.msg || fallback
  }
  return fallback
}

function ensureLogin() {
  if (tokenStore.hasLogin)
    return true

  uni.navigateTo({ url: LOGIN_PAGE })
  return false
}

async function fetchTeam(reset = false) {
  if (!ensureLogin())
    return
  if (loading.value)
    return
  if (!reset && finished.value)
    return

  if (reset) {
    page.value = 1
    finished.value = false
    pageError.value = ''
  }

  loading.value = true
  try {
    const res = await getSpreadTeamList({
      page: page.value,
      limit,
      level: 1,
    })
    const list = Array.isArray(res?.list) ? res.list : []
    total.value = Number(res?.count || 0)
    members.value = reset ? list : members.value.concat(list)
    finished.value = members.value.length >= total.value || list.length < limit
    if (!finished.value)
      page.value += 1
  }
  catch (error) {
    const message = getErrorMessage(error, '我的团队获取失败')
    pageError.value = message
    console.error('我的团队获取失败:', error)
    uni.showToast({ title: message, icon: 'none' })
  }
  finally {
    loading.value = false
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

function handleReload() {
  fetchTeam(true)
}

function handleLoadMore() {
  fetchTeam(false)
}

function memberAvatar(member: ISpreadTeamMember) {
  return member.avatar || '/static/images/default-avatar.png'
}

function memberNickname(member: ISpreadTeamMember) {
  return member.nickname || `用户${member.uid}`
}

function formatMoney(value: number | string | undefined) {
  const amount = Number(value || 0)
  if (!Number.isFinite(amount))
    return '0.00'
  return amount.toFixed(2)
}

function formatCount(value: number | undefined) {
  const count = Number(value || 0)
  return Number.isFinite(count) ? count : 0
}

function formatTime(value: string | undefined) {
  return value || '--'
}

onLoad(() => {
  fetchTeam(true)
})

onShow(() => {
  tokenStore.updateNowTime()
})
</script>

<template>
  <view class="team-page">
    <view class="team-nav">
      <view class="nav-content">
        <view class="nav-back" @tap="handleBack">
          <text class="i-carbon-chevron-left nav-back-icon" />
        </view>
        <text class="nav-title">我的团队</text>
        <view class="nav-placeholder" />
      </view>
    </view>

    <scroll-view class="team-scroll" scroll-y @scrolltolower="handleLoadMore">
      <view class="team-shell">
        <view class="summary-band">
          <view class="summary-main">
            <text class="summary-label">邀请用户</text>
            <text class="summary-count">{{ total }}</text>
          </view>
          <view class="summary-side">
            <text class="summary-side-title">团队成员</text>
            <text class="summary-side-desc">仅展示您直接邀请加入的用户</text>
          </view>
        </view>

        <view class="list-header">
          <text class="list-title">团队成员</text>
          <text class="list-total">共 {{ total }} 人</text>
        </view>

        <view v-if="pageError && members.length === 0" class="state-panel" @tap="handleReload">
          <text class="state-title">{{ pageError }}</text>
          <text class="state-desc">点击重试</text>
        </view>

        <view v-else-if="!loading && members.length === 0" class="state-panel">
          <text class="state-title">暂无邀请用户</text>
          <text class="state-desc">分享会员卡后，好友加入会展示在这里</text>
        </view>

        <view
          v-for="member in members"
          :key="member.uid"
          class="member-card"
        >
          <image class="member-avatar" :src="memberAvatar(member)" mode="aspectFill" />
          <view class="member-info">
            <view class="member-top">
              <text class="member-name">{{ memberNickname(member) }}</text>
              <text class="member-uid">UID {{ member.uid }}</text>
            </view>
            <text class="member-time">邀请时间：{{ formatTime(member.spread_time) }}</text>
            <view class="member-stats">
              <text class="member-stat">消费 {{ formatCount(member.pay_count) }} 次</text>
              <text class="member-stat">金额 ￥{{ formatMoney(member.pay_price) }}</text>
              <text class="member-stat">邀请 {{ formatCount(member.spread_count) }} 人</text>
            </view>
          </view>
        </view>

        <view class="load-state">
          <text v-if="loading" class="load-text">加载中...</text>
          <text v-else-if="finished && members.length > 0" class="load-text">已加载全部团队成员</text>
        </view>
      </view>
    </scroll-view>
  </view>
</template>

<style lang="scss" scoped>
.team-page {
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
  background: linear-gradient(180deg, #eef4ff 0%, #f7f3ff 42%, #f8f9fc 100%);
}

.team-nav {
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
  color: #30313b;
  font-size: 34rpx;
  font-weight: 700;
  line-height: 44rpx;
}

.team-scroll {
  height: calc(100vh - 88rpx - var(--status-bar-height, 0px));
}

.team-shell {
  padding: 28rpx 24rpx 48rpx;
  box-sizing: border-box;
}

.summary-band {
  display: flex;
  min-height: 188rpx;
  align-items: center;
  justify-content: space-between;
  padding: 30rpx;
  box-sizing: border-box;
  border-radius: 24rpx;
  background: linear-gradient(135deg, #765dff 0%, #a96cff 48%, #5c91ff 100%);
  box-shadow: 0 14rpx 32rpx rgba(102, 91, 216, 0.18);
}

.summary-main,
.summary-side {
  display: flex;
  flex-direction: column;
}

.summary-label,
.summary-side-desc {
  color: rgba(255, 255, 255, 0.78);
  font-size: 24rpx;
  line-height: 34rpx;
}

.summary-count {
  margin-top: 10rpx;
  color: #fff;
  font-size: 58rpx;
  font-weight: 900;
  line-height: 68rpx;
}

.summary-side {
  max-width: 330rpx;
  align-items: flex-end;
  text-align: right;
}

.summary-side-title {
  color: #fff;
  font-size: 34rpx;
  font-weight: 800;
  line-height: 44rpx;
}

.summary-side-desc {
  margin-top: 10rpx;
}

.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 32rpx 4rpx 18rpx;
}

.list-title {
  color: #262631;
  font-size: 32rpx;
  font-weight: 800;
  line-height: 42rpx;
}

.list-total {
  color: #8d8fa0;
  font-size: 24rpx;
  line-height: 34rpx;
}

.state-panel {
  display: flex;
  min-height: 260rpx;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  border-radius: 18rpx;
  background: #fff;
  box-shadow: 0 8rpx 20rpx rgba(113, 111, 145, 0.08);
}

.state-title {
  color: #5a5b68;
  font-size: 30rpx;
  font-weight: 700;
  line-height: 42rpx;
}

.state-desc {
  margin-top: 12rpx;
  color: #a0a3b2;
  font-size: 24rpx;
  line-height: 34rpx;
}

.member-card {
  display: flex;
  align-items: center;
  min-height: 168rpx;
  margin-bottom: 18rpx;
  padding: 24rpx;
  box-sizing: border-box;
  border-radius: 18rpx;
  background: #fff;
  box-shadow: 0 8rpx 20rpx rgba(113, 111, 145, 0.08);
}

.member-avatar {
  width: 96rpx;
  height: 96rpx;
  flex-shrink: 0;
  border-radius: 50%;
  background: #eef0f5;
}

.member-info {
  min-width: 0;
  flex: 1;
  margin-left: 22rpx;
}

.member-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.member-name {
  max-width: 300rpx;
  overflow: hidden;
  color: #272833;
  font-size: 30rpx;
  font-weight: 800;
  line-height: 40rpx;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.member-uid {
  margin-left: 16rpx;
  color: #a1a4b3;
  font-size: 22rpx;
  line-height: 32rpx;
}

.member-time {
  display: block;
  margin-top: 8rpx;
  color: #8b8e9e;
  font-size: 24rpx;
  line-height: 34rpx;
}

.member-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 10rpx;
  margin-top: 12rpx;
}

.member-stat {
  padding: 4rpx 10rpx;
  border-radius: 6rpx;
  background: #f3f2ff;
  color: #6b62c9;
  font-size: 22rpx;
  line-height: 30rpx;
}

.load-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80rpx;
}

.load-text {
  color: #a0a3b2;
  font-size: 24rpx;
  line-height: 34rpx;
}
</style>

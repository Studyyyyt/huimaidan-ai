<script lang="ts" setup>
import type { IMerInfo } from '@/api/mer'
import { getFinanceOverview, getMerInfo } from '@/api/mer'
import { onShow } from '@dcloudio/uni-app'
import { useMerTokenStore } from '@/store/mer-token'

definePage({
  style: {
    navigationBarTitleText: '个人中心',
    navigationStyle: 'custom',
  },
})

const merTokenStore = useMerTokenStore()

// 商户信息
const merInfo = ref<IMerInfo | null>(null)

// 今日数据
const todayData = ref({
  todayReceived: 0,
  todayOrderCount: 0,
})

// 快捷操作
const quickActions = [
  { name: '店铺码', icon: 'i-carbon-qr-code', path: '/pages/admin/shop-qrcode' },
]

// 买单记录统计
const orderStats = ref({
  todayOrder: 0,
  todayReceived: 0,
  refundOrder: 0,
  allOrder: 0,
})

// 加载商户信息
async function loadMerInfo() {
  try {
    const res = await getMerInfo()
    merInfo.value = res
  }
  catch (error) {
    console.error('获取商户信息失败:', error)
  }
}

// 加载今日数据
async function loadTodayData() {
  try {
    const res = await getFinanceOverview()
    // 防御性处理：确保所有字段都有默认值
    todayData.value = {
      todayReceived: res?.todayReceived || 0,
      todayOrderCount: res?.todayOrderCount || 0,
    }
    // 同步更新买单记录统计
    orderStats.value.todayReceived = res?.todayReceived || 0
    orderStats.value.todayOrder = res?.todayOrderCount || 0
    orderStats.value.refundOrder = res?.refundOrderCount || 0
    orderStats.value.allOrder = res?.allOrderCount || 0
  }
  catch (error) {
    console.error('获取今日数据失败:', error)
    // 保持默认值，避免渲染崩溃
  }
}

// 快捷操作点击
function handleQuickAction(action: { name: string, path: string }) {
  if (action.path) {
    uni.navigateTo({ url: action.path })
  }
  else {
    uni.showToast({ title: '功能开发中', icon: 'none' })
  }
}

// 查看全部记录
function handleViewAll(type: string) {
  if (type === '买单') {
    uni.navigateTo({ url: '/pages/admin/payment-record/payment-record' })
  } else {
    uni.showToast({ title: `${type}功能开发中`, icon: 'none' })
  }
}

// 扫一扫
function handleScan() {
  uni.scanCode({
    success: (res) => {
      console.log('扫码结果:', res)
      uni.showToast({ title: `扫码成功: ${res.result}`, icon: 'none' })
    },
  })
}

// 设置
function handleSettings() {
  uni.showToast({ title: '设置功能开发中', icon: 'none' })
}

// 退出登录
function handleLogout() {
  uni.showModal({
    title: '提示',
    content: '确定要退出登录吗？',
    success: async (res) => {
      if (res.confirm) {
        await merTokenStore.logout()
        uni.showToast({ title: '退出成功', icon: 'success' })
        setTimeout(() => {
          uni.redirectTo({ url: '/pages/admin/login' })
        }, 1500)
      }
    },
  })
}

onShow(() => {
  if (!merTokenStore.hasLogin) {
    uni.navigateBack()
    return
  }
  loadMerInfo()
  loadTodayData()
})
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 顶部导航栏 -->
    <view class="relative h-200rpx from-purple-100 via-purple-50 to-indigo-50 bg-gradient-to-br pt-safe" />

    <!-- 用户信息卡片 -->
    <view class="relative mx-24rpx -mt-80rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-lg">
        <view class="flex items-center">
          <!-- 头像 -->
          <image
            class="h-120rpx w-120rpx border-4rpx border-white rounded-full shadow-md"
            :src="merInfo?.mer_avatar || '/static/images/default-avatar.png'"
            mode="aspectFill"
          />

          <!-- 用户信息 -->
          <view class="ml-24rpx flex-1">
            <text class="block text-32rpx text-gray-800 font-bold">
              {{ merInfo?.real_name || '管理员' }}
            </text>
            <text class="mt-8rpx block text-26rpx text-gray-500">
              {{ merInfo?.mer_phone || '未设置手机号' }}
            </text>
          </view>

          <!-- 右侧图标（已隐藏） -->
          <!--
          <view class="flex items-center gap-24rpx">
            <view class="h-64rpx w-64rpx flex items-center justify-center" @tap="handleScan">
              <text class="i-carbon-qr-code text-40rpx text-gray-400" />
            </view>
            <view class="h-64rpx w-64rpx flex items-center justify-center" @tap="handleSettings">
              <text class="i-carbon-settings text-40rpx text-gray-400" />
            </view>
          </view>
          -->
        </view>
      </view>
    </view>

    <!-- 今日数据卡片 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx from-purple-400 via-purple-300 to-indigo-400 bg-gradient-to-r p-32rpx shadow-lg">
        <view class="flex justify-around">
          <view class="flex-1 text-center">
            <text class="block text-36rpx text-white font-bold">{{ todayData.todayReceived.toFixed(2) }}</text>
            <text class="mt-8rpx block text-24rpx text-white/80">今日收款</text>
          </view>
          <view class="flex-1 text-center">
            <text class="block text-36rpx text-white font-bold">{{ todayData.todayOrderCount }}</text>
            <text class="mt-8rpx block text-24rpx text-white/80">今日订单</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 快捷操作 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <text class="mb-24rpx block text-30rpx text-gray-800 font-bold">快捷操作</text>
        <view class="grid grid-cols-4 gap-24rpx">
          <view
            v-for="action in quickActions"
            :key="action.name"
            class="flex flex-col items-center"
            @tap="handleQuickAction(action)"
          >
            <view class="h-80rpx w-80rpx flex items-center justify-center rounded-20rpx bg-purple-50">
              <text :class="action.icon" class="text-36rpx text-purple-600" />
            </view>
            <text class="mt-12rpx text-center text-22rpx text-gray-600">{{ action.name }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 买单记录 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <view class="mb-24rpx flex items-center justify-between">
          <text class="text-30rpx text-gray-800 font-bold">买单记录</text>
          <view class="flex items-center" @tap="handleViewAll('买单')">
            <text class="text-26rpx text-gray-500">查看全部记录</text>
            <text class="i-carbon-chevron-right ml-8rpx text-24rpx text-gray-400" />
          </view>
        </view>
        <view class="flex justify-between">
          <view class="flex flex-col items-center">
            <text class="text-32rpx text-gray-800 font-bold">{{ orderStats.todayOrder }}</text>
            <text class="mt-8rpx text-22rpx text-gray-500">今日订单</text>
          </view>
          <view class="flex flex-col items-center">
            <text class="text-32rpx text-gray-800 font-bold">¥{{ orderStats.todayReceived.toFixed(2) }}</text>
            <text class="mt-8rpx text-22rpx text-gray-500">今日收款</text>
          </view>
          <view class="flex flex-col items-center">
            <text class="text-32rpx text-gray-800 font-bold">{{ orderStats.refundOrder }}</text>
            <text class="mt-8rpx text-22rpx text-gray-500">退款订单</text>
          </view>
          <view class="flex flex-col items-center">
            <text class="text-32rpx text-gray-800 font-bold">{{ orderStats.allOrder }}</text>
            <text class="mt-8rpx text-22rpx text-gray-500">全部订单</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 退出登录按钮 -->
    <view class="mx-24rpx mb-160rpx mt-24rpx">
      <button
        type="warn"
        class="h-88rpx w-full rounded-24rpx text-30rpx font-bold"
        @click="handleLogout"
      >
        退出登录
      </button>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.grid-cols-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

// 底部安全区域
.safe-area-inset-bottom {
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}
</style>

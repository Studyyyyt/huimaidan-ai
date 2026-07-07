<script lang="ts" setup>
import { onShow } from '@dcloudio/uni-app'
import { storeToRefs } from 'pinia'
import { getOrderStatistics, getUserAssets } from '@/api/huimaidan'
import { LOGIN_PAGE } from '@/router/config'
import { useUserStore } from '@/store'
import { useTokenStore } from '@/store/token'

definePage({
  style: {
    navigationBarTitleText: '个人中心',
    navigationStyle: 'custom',
  },
})

const userStore = useUserStore()
const tokenStore = useTokenStore()
const { userInfo } = storeToRefs(userStore)

// 用户资产数据
const assetsData = ref({
  commission: '0.00',
  points: 0,
  couponCount: 0,
  vipLevel: 0,
})

const orderStatistics = ref({
  unpaid: 0,
  completed: 0,
  refund: 0,
})

// 获取用户资产数据
async function fetchUserAssets() {
  if (!tokenStore.hasLogin) {
    return
  }

  // 先调用 updateNowTime() 确保获取最新的 token
  tokenStore.updateNowTime()
  const token = tokenStore.validToken

  if (!token) {
    uni.showToast({ title: '登录状态失效，请重新登录', icon: 'none' })
    return
  }

  // 使用 http 模块发送请求（会自动添加 Token）
  try {
    const res = await getUserAssets()
    if (res) {
      assetsData.value = {
        commission: res.commission || '0.00',
        points: res.points || 0,
        couponCount: res.couponCount || 0,
        vipLevel: res.vipLevel || 0,
      }
    }
  }
  catch (error: any) {
    console.error('[用户资产] 获取失败:', error)
    uni.showToast({ title: error?.message || '获取用户资产失败', icon: 'none' })
  }
}

async function fetchOrderStatistics() {
  if (!tokenStore.hasLogin) {
    return
  }

  try {
    const res = await getOrderStatistics()
    if (res) {
      orderStatistics.value = res
    }
  }
  catch (error: any) {
    console.error('[订单统计] 获取失败:', error)
    uni.showToast({ title: error?.message || '获取订单统计失败', icon: 'none' })
  }
}

// 页面显示时获取数据
onShow(() => {
  fetchUserAssets()
  fetchOrderStatistics()
})

// 订单状态
const orderStatus = computed(() => [
  { name: '已完成', icon: 'i-carbon-checkmark-filled', count: orderStatistics.value.completed, tab: '已完成' },
  { name: '售后', icon: 'i-carbon-return', count: orderStatistics.value.refund, tab: '退款/售后' },
])

// 常用功能
const commonFunctions = [
  { name: '买单记录', icon: 'i-carbon-document' },
  { name: '浏览足迹', icon: 'i-carbon-time' },
  { name: '分享海报', icon: 'i-carbon-share' },
  { name: '我的团队', icon: 'i-carbon-group' },
  { name: '商户管理', icon: 'i-carbon-user-avatar-filled-alt' },
]

// 跳转我的优惠券
function handleViewCoupons() {
  uni.navigateTo({
    url: '/pages/my-coupon/my-coupon',
  })
}

// 跳转浏览足迹
function handleBrowsingHistory() {
  uni.navigateTo({
    url: '/pages/browsing-history/browsing-history',
  })
}

function handleSharePoster() {
  if (!tokenStore.hasLogin) {
    uni.navigateTo({ url: LOGIN_PAGE })
    return
  }

  uni.navigateTo({ url: '/pages/share-poster/share-poster' })
}

// 常用功能点击
function handleFunctionTap(name: string) {
  switch (name) {
    case '浏览足迹':
      handleBrowsingHistory()
      break
    case '买单记录':
      uni.navigateTo({ url: '/pages/orders/orders' })
      break
    case '分享海报':
      handleSharePoster()
      break
    case '我的团队':
      if (!tokenStore.hasLogin) {
        uni.navigateTo({ url: LOGIN_PAGE })
        return
      }
      uni.navigateTo({ url: '/pages/my-team/my-team' })
      break
    case '商户管理':
      uni.navigateTo({ url: '/pages/admin/login' })
      break
    default:
      uni.showToast({ title: '功能开发中', icon: 'none' })
      break
  }
}

// 跳转订单中心
function handleViewAllOrders() {
  uni.navigateTo({
    url: '/pages/orders/orders',
  })
}

// 跳转订单中心指定页签
function handleOrderTabTap(tab: string) {
  uni.navigateTo({
    url: `/pages/orders/orders?tab=${encodeURIComponent(tab)}`,
  })
}

// 跳转设置页
function handleSettings() {
  uni.navigateTo({
    url: '/pages/settings/settings',
  })
}

// 跳转登录页（小程序内使用微信登录，H5/APP 使用账号密码登录）
function handleLogin() {
  uni.navigateTo({
    url: LOGIN_PAGE,
  })
}

function handleUserNameTap() {
  if (!tokenStore.hasLogin) {
    handleLogin()
  }
}

function handleLogout() {
  uni.showModal({
    title: '提示',
    content: '确定要退出登录吗？',
    success: (res) => {
      if (res.confirm) {
        useTokenStore().logout()
        uni.showToast({
          title: '退出登录成功',
          icon: 'success',
        })
      }
    },
  })
}

function handleCall() {
  uni.makePhoneCall({
    phoneNumber: '17504841818',
  })
}
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 自定义导航栏 -->
    <view class="relative h-200rpx from-purple-100 via-purple-50 to-indigo-50 bg-gradient-to-br pt-safe" />

    <!-- 用户信息卡片 -->
    <view class="relative mx-24rpx -mt-80rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-lg">
        <view class="flex items-center">
          <!-- 头像 -->
          <view class="relative">
            <image
              class="h-120rpx w-120rpx border-4rpx border-white rounded-full shadow-md"
              :src="userInfo.avatar || '/static/images/default-avatar.png'"
              mode="aspectFill"
            />
            <!-- VIP标识 -->
            <view class="absolute h-40rpx w-40rpx flex items-center justify-center border-2rpx border-white rounded-full bg-orange-500 -bottom-8rpx -right-8rpx">
              <text class="text-20rpx text-white font-bold">V</text>
            </view>
          </view>

          <!-- 用户信息 -->
          <view class="ml-24rpx flex-1">
            <view class="flex items-center">
              <text
                class="text-32rpx text-gray-800 font-bold"
                @tap.stop="handleUserNameTap"
              >
                {{ userInfo.nickname || userInfo.username || '未登录' }}
              </text>
              <view class="ml-12rpx rounded-full from-orange-400 to-orange-500 bg-gradient-to-r px-12rpx py-4rpx">
                <text class="text-20rpx text-white font-bold">VIP</text>
              </view>
            </view>
            <text class="mt-8rpx block text-26rpx text-gray-500">{{ userInfo.phone || '请先登录' }}</text>
          </view>

          <!-- 设置图标 -->
          <view
            class="h-64rpx w-64rpx flex items-center justify-center"
            @tap="handleSettings"
          >
            <text class="i-carbon-settings text-40rpx text-gray-400" />
          </view>
        </view>
      </view>
    </view>

    <!-- 资产卡片 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx from-purple-400 via-purple-300 to-indigo-400 bg-gradient-to-r p-32rpx shadow-lg">
        <view class="flex justify-around">
          <view class="flex-1 text-center">
            <text class="block text-36rpx text-white font-bold">{{ assetsData.commission }}</text>
            <text class="mt-8rpx block text-24rpx text-white/80">佣金</text>
          </view>
          <view class="flex-1 text-center">
            <text class="block text-36rpx text-white font-bold">{{ assetsData.points }}</text>
            <text class="mt-8rpx block text-24rpx text-white/80">积分</text>
          </view>
          <view class="flex-1 text-center" @tap.stop="handleViewCoupons">
            <text class="block text-36rpx text-white font-bold">{{ assetsData.couponCount }}</text>
            <text class="mt-8rpx block text-24rpx text-white/80">优惠/团购</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 我的订单 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <view class="mb-24rpx flex items-center justify-between">
          <text class="text-30rpx text-gray-800 font-bold">我的订单</text>
          <view class="flex items-center" @tap="handleViewAllOrders">
            <text class="text-26rpx text-gray-500">查看全部订单</text>
            <text class="i-carbon-chevron-right ml-8rpx text-24rpx text-gray-400" />
          </view>
        </view>

        <view class="grid grid-cols-3 gap-32rpx">
          <view
            v-for="item in orderStatus"
            :key="item.name"
            class="flex flex-col items-center"
            @tap="handleOrderTabTap(item.tab)"
          >
            <view class="h-80rpx w-80rpx flex items-center justify-center rounded-20rpx bg-purple-50">
              <text :class="item.icon" class="text-40rpx text-purple-600" />
            </view>
            <text class="mt-12rpx text-24rpx text-gray-600">{{ item.name }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 常用功能 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <text class="mb-24rpx block text-30rpx text-gray-800 font-bold">常用功能</text>

        <view class="grid grid-cols-5 gap-24rpx">
          <view
            v-for="item in commonFunctions"
            :key="item.name"
            class="flex flex-col items-center"
            @tap="handleFunctionTap(item.name)"
          >
            <view class="h-80rpx w-80rpx flex items-center justify-center rounded-20rpx bg-gray-100">
              <text :class="item.icon" class="text-36rpx text-gray-600" />
            </view>
            <text class="mt-12rpx text-center text-22rpx text-gray-600">{{ item.name }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 客服热线 -->
   <!--  <view class="mx-24rpx mb-48rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <view class="flex items-center justify-between">
          <view>
            <text class="text-30rpx text-gray-800 font-bold">客服热线</text>
            <text class="ml-12rpx text-28rpx text-gray-600">(175 0484 1818)</text>
          </view>
          <view
            class="flex items-center"
            @tap="handleCall"
          >
            <text class="text-26rpx text-gray-500">拨打电话</text>
            <text class="i-carbon-phone ml-8rpx text-24rpx text-gray-400" />
          </view>
        </view>
      </view>
    </view> -->

    <!-- 退出登录按钮 -->
    <!-- <view v-if="tokenStore.hasLogin" class="mx-24rpx mb-48rpx">
      <button
        type="warn"
        class="h-88rpx w-full rounded-24rpx text-30rpx font-bold"
        @click="handleLogout"
      >
        退出登录
      </button>
    </view> -->
  </view>
</template>

<style lang="scss" scoped>
.grid-cols-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}

.grid-cols-5 {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
}
</style>

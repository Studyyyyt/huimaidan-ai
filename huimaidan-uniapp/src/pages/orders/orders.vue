<script lang="ts" setup>
import type { IOrderListViewItem } from './orders.helpers'
import { onReachBottom, onShow } from '@dcloudio/uni-app'
import { getOrderList, getOrderStatistics } from '@/api/huimaidan'
import { buildOrderListParams, mapOrderListItem } from './orders.helpers'

definePage({
  style: {
    navigationBarTitleText: '订单中心',
  },
})

// 当前选中的标签
const activeTab = ref('全部订单')

// 标签列表
const tabs = ['全部订单', '已完成', '退款/售后']

// 订单统计数据
const orderStatistics = ref({
  unpaid: 0,
  completed: 0,
  refund: 0,
})

// 页面显示时获取参数（使用 onShow 替代 onLoad 以确保参数正确传递）
onShow(() => {
  const pages = getCurrentPages()
  const currentPage = pages[pages.length - 1] as any
  const options = currentPage?.$page?.options || currentPage?.options || {}

  if (options.tab) {
    const tabName = decodeURIComponent(options.tab)
    if (tabs.includes(tabName)) {
      activeTab.value = tabName
    }
  }

  // 获取订单统计数据
  fetchOrderStatistics()
  currentPage.value = 1
  isLastPage.value = false
  fetchOrderList()
})

// 获取订单统计数据
async function fetchOrderStatistics() {
  try {
    const res = await getOrderStatistics()
    if (res) {
      orderStatistics.value = res
    }
  }
  catch (error) {
    console.error('获取订单统计失败:', error)
    uni.showToast({
      title: '获取订单统计失败',
      icon: 'none',
    })
  }
}

// 搜索关键词
const searchKeyword = ref('')

const orderList = ref<IOrderListViewItem[]>([])
const currentPage = ref(1)
const pageSize = 15
const isLoading = ref(false)
const isLastPage = ref(false)
const pageError = ref('')

async function fetchOrderList(append = false) {
  if (isLoading.value) {
    return
  }

  isLoading.value = true
  pageError.value = ''

  try {
    const params = buildOrderListParams(activeTab.value, currentPage.value, pageSize)
    const res = await getOrderList(params)
    let list = (res?.list || []).map(mapOrderListItem)

    // 退款/售后订单前端过滤（status === -1 表示退款状态）
    if (activeTab.value === '退款/售后') {
      list = list.filter(item => item.statusCode === -1)
    }

    orderList.value = append ? [...orderList.value, ...list] : list
    isLastPage.value = list.length < pageSize
  }
  catch (error) {
    console.error('获取订单列表失败:', error)
    pageError.value = '获取订单列表失败'
    uni.showToast({
      title: '获取订单列表失败',
      icon: 'none',
    })
  }
  finally {
    isLoading.value = false
  }
}

// 切换标签
function handleTabChange(tab: string) {
  activeTab.value = tab
  currentPage.value = 1
  isLastPage.value = false
  fetchOrderList()
}

// 搜索
function handleSearch() {
  if (searchKeyword.value.trim()) {
    uni.showToast({
      title: '后端订单列表暂未提供关键词搜索',
      icon: 'none',
    })
  }
}

// 点击订单详情
function handleOrderDetail(orderId: number) {
  uni.navigateTo({
    url: `/pages/orders/detail?id=${orderId}`,
  })
}

onReachBottom(() => {
  if (!isLastPage.value && !isLoading.value) {
    currentPage.value += 1
    fetchOrderList(true)
  }
})
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 标签栏 -->
    <view class="bg-white px-24rpx pt-24rpx">
      <view class="flex items-center border-b border-gray-100">
        <view
          v-for="tab in tabs"
          :key="tab"
          class="relative px-16rpx pb-16rpx"
          @tap="handleTabChange(tab)"
        >
          <text
            class="text-28rpx"
            :class="activeTab === tab ? 'text-gray-800 font-bold' : 'text-gray-500'"
          >
            {{ tab }}
            <text v-if="tab === '待付款' && orderStatistics.unpaid > 0" class="text-20rpx text-red-500">({{ orderStatistics.unpaid }})</text>
            <text v-if="tab === '已完成' && orderStatistics.completed > 0" class="text-20rpx text-green-500">({{ orderStatistics.completed }})</text>
            <text v-if="tab === '退款/售后' && orderStatistics.refund > 0" class="text-20rpx text-orange-500">({{ orderStatistics.refund }})</text>
          </text>
          <!-- 选中下划线 -->
          <view
            v-if="activeTab === tab"
            class="absolute bottom-0 left-1/2 h-6rpx w-40rpx rounded-full bg-blue-500 -translate-x-1/2"
          />
        </view>
      </view>
    </view>

    <!-- 搜索框 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="h-80rpx flex items-center rounded-full bg-white px-24rpx shadow-sm">
        <text class="i-carbon-search mr-16rpx text-32rpx text-gray-400" />
        <input
          v-model="searchKeyword"
          class="flex-1 text-28rpx text-gray-800"
          placeholder="请输入关键词搜索"
          placeholder-class="text-gray-400"
          @confirm="handleSearch"
        >
      </view>
    </view>

    <!-- 订单列表 -->
    <view class="mx-24rpx mt-24rpx">
      <view
        v-for="order in orderList"
        :key="order.id"
        class="mb-16rpx rounded-24rpx bg-white p-24rpx shadow-sm"
        @tap="handleOrderDetail(order.id)"
      >
        <!-- 日期时间 -->
        <view class="mb-16rpx flex items-center justify-between">
          <text class="text-24rpx text-gray-500">{{ order.date }}</text>
          <text class="i-carbon-chevron-right text-24rpx text-gray-400" />
        </view>

        <!-- 商家信息 -->
        <view class="flex items-center justify-between">
          <view class="flex items-center">
            <!-- 商家图标 -->
            <image
              v-if="order.image"
              :src="order.image"
              class="mr-16rpx h-80rpx w-80rpx rounded-16rpx"
              mode="aspectFill"
            />
            <view v-else class="mr-16rpx h-80rpx w-80rpx flex items-center justify-center rounded-16rpx bg-orange-100">
              <text class="i-carbon-store text-36rpx text-orange-500" />
            </view>
            <view>
              <text class="text-28rpx text-gray-800 font-bold">{{ order.shopName }}</text>
              <text v-if="order.branchName" class="ml-8rpx text-24rpx text-gray-500">【{{ order.branchName }}】</text>
              <text class="mt-8rpx block text-24rpx text-gray-400">{{ order.payMethod }}</text>
              <text class="mt-4rpx block text-22rpx text-gray-400">{{ order.status }}</text>
            </view>
          </view>

          <!-- 金额 -->
          <text class="text-32rpx text-red-500 font-bold">-¥{{ Math.abs(order.amount) }}</text>
        </view>
      </view>
    </view>

    <!-- 空状态（当没有订单时显示） -->
    <view
      v-if="!isLoading && orderList.length === 0"
      class="mt-120rpx flex flex-col items-center justify-center"
    >
      <text class="i-carbon-shopping-cart mb-24rpx text-120rpx text-gray-300" />
      <text class="text-28rpx text-gray-400">{{ pageError || '暂无订单' }}</text>
    </view>
    <view v-if="isLoading" class="py-40rpx text-center">
      <text class="text-26rpx text-gray-400">加载中...</text>
    </view>
    <view v-else-if="isLastPage && orderList.length > 0" class="py-40rpx text-center">
      <text class="text-26rpx text-gray-400">没有更多了</text>
    </view>
  </view>
</template>

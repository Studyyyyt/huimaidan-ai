<script lang="ts" setup>
import { onLoad } from '@dcloudio/uni-app'
import { getOrderDetail } from '@/api/huimaidan'
import { getMemberDiscountStatusText, mapOrderDetail } from './detail.helpers'

definePage({
  style: {
    navigationBarTitleText: '订单详情',
  },
})

const orderId = ref(0)

// 订单详情数据
const orderDetail = ref({
  orderNo: '',
  shopName: '',
  branchName: '',
  payMethod: '',
  payStatus: '',
  payAmount: 0,
  discount: 0,
  actualAmount: 0,
  payTime: '',
  remark: '',
  // 优惠抵扣详情
  discountDetail: {
    member_discount_enabled: undefined as boolean | undefined,
    rule_type_label: '',
    title: '',
    coupon_deduction_amount: '0',
    integral: 0,
    integral_deduction_amount: '0',
    platform_bear_coupon_amount: '0',
    platform_bear_integral_amount: '0',
  },
})

// 加载状态
const isLoading = ref(true)

// 获取订单详情
async function fetchOrderDetail() {
  if (!orderId.value) {
    return
  }

  try {
    isLoading.value = true
    const res = await getOrderDetail(orderId.value)
    if (res) {
      orderDetail.value = mapOrderDetail(res)
    }
  }
  catch (error) {
    console.error('获取订单详情失败:', error)
    uni.showToast({
      title: '获取订单详情失败',
      icon: 'none',
    })
  }
  finally {
    isLoading.value = false
  }
}

onLoad((options) => {
  if (options?.id) {
    orderId.value = Number(options.id)
    fetchOrderDetail()
  }
})
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 订单详情卡片 -->
    <view class="mx-24rpx mt-24rpx overflow-hidden rounded-24rpx bg-white shadow-sm">
      <!-- 订单编号 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">订单编号：</text>
        <text class="text-28rpx text-gray-800">{{ orderDetail.orderNo }}</text>
      </view>

      <!-- 店铺名称 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">店铺名称：</text>
        <text class="text-28rpx text-gray-800">
          {{ orderDetail.shopName }}<template v-if="orderDetail.branchName"> | {{ orderDetail.branchName }}</template>
        </text>
      </view>

      <!-- 付款方式 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">付款方式：</text>
        <text class="text-28rpx text-gray-800">{{ orderDetail.payMethod }}</text>
      </view>

      <!-- 付款状态 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">付款状态：</text>
        <text class="text-28rpx text-green-500">{{ orderDetail.payStatus }}</text>
      </view>

      <!-- 付款金额 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">付款金额：</text>
        <text class="text-28rpx text-gray-800">¥{{ orderDetail.payAmount.toFixed(2) }}</text>
      </view>

      <!-- 折扣 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">折扣：</text>
        <text class="text-28rpx text-gray-800">- ¥{{ orderDetail.discount.toFixed(2) }}</text>
      </view>

      <!-- 优惠抵扣详情 -->
      <view v-if="orderDetail.discountDetail.rule_type_label" class="border-b border-gray-100 px-32rpx py-24rpx">
        <view v-if="getMemberDiscountStatusText(orderDetail.discountDetail.member_discount_enabled)" class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">会员折扣：</text>
          <text class="text-28rpx text-purple-500">{{ getMemberDiscountStatusText(orderDetail.discountDetail.member_discount_enabled) }}</text>
        </view>
        <view class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">优惠类型：</text>
          <text class="text-28rpx text-purple-500">{{ orderDetail.discountDetail.rule_type_label }}</text>
        </view>
        <view v-if="orderDetail.discountDetail.title" class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">优惠名称：</text>
          <text class="text-28rpx text-gray-800">{{ orderDetail.discountDetail.title }}</text>
        </view>
        <view v-if="Number.parseFloat(orderDetail.discountDetail.coupon_deduction_amount) > 0" class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">优惠券抵扣：</text>
          <text class="text-28rpx text-green-500">- ¥{{ orderDetail.discountDetail.coupon_deduction_amount }}</text>
        </view>
        <view v-if="orderDetail.discountDetail.integral > 0" class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">积分抵扣：</text>
          <text class="text-28rpx text-green-500">{{ orderDetail.discountDetail.integral }}积分 (¥{{ orderDetail.discountDetail.integral_deduction_amount }})</text>
        </view>
        <view v-if="Number.parseFloat(orderDetail.discountDetail.platform_bear_coupon_amount) > 0" class="mb-16rpx flex items-center justify-between">
          <text class="text-28rpx text-gray-500">平台承担优惠券：</text>
          <text class="text-28rpx text-orange-500">¥{{ orderDetail.discountDetail.platform_bear_coupon_amount }}</text>
        </view>
        <view v-if="Number.parseFloat(orderDetail.discountDetail.platform_bear_integral_amount) > 0" class="flex items-center justify-between">
          <text class="text-28rpx text-gray-500">平台承担积分：</text>
          <text class="text-28rpx text-orange-500">¥{{ orderDetail.discountDetail.platform_bear_integral_amount }}</text>
        </view>
      </view>

      <!-- 实付金额 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">实付金额：</text>
        <text class="text-32rpx text-red-500 font-bold">¥{{ orderDetail.actualAmount.toFixed(2) }}</text>
      </view>

      <!-- 付款时间 -->
      <view class="flex items-center justify-between border-b border-gray-100 px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">付款时间：</text>
        <text class="text-28rpx text-gray-800">{{ orderDetail.payTime }}</text>
      </view>

      <!-- 备注 -->
      <view class="flex items-center justify-between px-32rpx py-24rpx">
        <text class="text-28rpx text-gray-500">备注：</text>
        <text class="text-28rpx text-gray-800">{{ orderDetail.remark }}</text>
      </view>
    </view>
  </view>
</template>

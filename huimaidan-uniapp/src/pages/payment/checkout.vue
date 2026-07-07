<script lang="ts" setup>
import type { IDiscountSnapshot, IOrderCreateCombinedResult } from '@/api/huimaidan'
import { onLoad } from '@dcloudio/uni-app'
import { postAiEvent } from '@/api/ai'
import { calculateDiscount, createCombinedOrder, getPayResult, getStoreDetail, getUsableCoupons, getUserAssets } from '@/api/huimaidan'
import { buildMiniProgramPaymentOptions, isWechatMiniProgramPayType } from '@/api/huimaidan.pay'
import { parseStoreQrcodeScene } from '@/pages/scan-entry/scene'
import { buildCombinedOrderParams, getCheckoutBackAction, normalizeCheckoutStoreList } from './checkout.helpers'

defineOptions({
  name: 'PaymentCheckout',
})
definePage({
  style: {
    'navigationStyle': 'custom',
    'navigationBarTitleText': '买单付款',
    'mp-alipay': {
      defaultTitle: '买单付款',
      transparentTitle: 'always',
      titlePenetrate: 'YES',
      titleBarColor: '#ffffff',
    },
  },
})

// 商户ID
const shopId = ref(0)
const aiLogId = ref(0)

const checkoutBackOptions = ref({
  source: '',
  scene: '',
})

// 商家品牌数据（品牌级别，不随门店切换变化）
const merchantData = ref({
  name: '',
  description: '',
  image: '',
})

// 门店状态（在 onLoad/fetchCheckoutMerchant 之前声明，避免 use-before-define）
const showStorePopup = ref(false)
const tempSelectedStoreId = ref(0) // 临时选中的门店ID
const selectedStoreId = ref(0)

// 门店列表
const storeList = ref<Array<{ id: number, name: string, address: string, phone?: string }>>([])

const selectedStore = computed(() => storeList.value.find(s => s.id === selectedStoreId.value))

// 当前门店信息（始终反映 selectedStoreId，门店切换后顶部立即跟随更新）
const currentStoreInfo = computed(() => {
  const store = storeList.value.find(s => s.id === selectedStoreId.value)
  if (store) {
    return {
      id: store.id,
      storeName: store.name,
      storeAddress: store.address || '',
      // 切换门店后显示完整店名，不带括号
      store: store.name || '',
    }
  }
  return {
    id: selectedStoreId.value || shopId.value,
    storeName: '',
    storeAddress: '',
    store: '',
  }
})

// 表单数据 - 优惠金额（支持所有优惠）
const discountFormData = ref({
  amount: '',
  usePoints: false,
})

// 表单数据 - 不参与优惠金额（不使用任何优惠）
const noDiscountFormData = ref({
  amount: '',
})

// 备注（共用）
const remark = ref('')

// 金额输入过滤：只允许正数（过滤负号）
function handleAmountInput(e: any, target: { amount: string }) {
  let value = e.detail.value
  // 过滤负号、负号字符
  value = value.replace(/-/g, '')
  // 确保最多保留两位小数
  const parts = value.split('.')
  if (parts.length > 2) {
    value = `${parts[0]}.${parts.slice(1).join('')}`
  }
  if (parts[1] && parts[1].length > 2) {
    value = `${parts[0]}.${parts[1].slice(0, 2)}`
  }
  target.amount = value
}

// 会员折扣
const memberDiscount = ref({
  title: '',
  discountAmount: 0,
  discountRate: '', // 后端返回的折扣率，如 '0.80'
})

// 优惠券数据
const couponData = ref({
  available: 0,
  discountAmount: 0,
})

// 积分数据
const pointsData = ref({
  available: 0,
})

interface ICheckoutCoupon {
  id: number
  amount: number
  condition: string
  name: string
  expireTime: string
  isUsed: boolean
}

// 优惠券选择弹窗
const showCouponPopup = ref(false)
const selectedCouponId = ref<number | null>(null)

// 优惠券列表
const couponList = ref<ICheckoutCoupon[]>([])

const discountPreview = ref<IDiscountSnapshot | undefined>()
let discountPreviewRequestId = 0

// 会员折扣显示文本（直接用后端返回的 discount_rule + discount_rate）
const memberDiscountLabel = computed(() => {
  const title = memberDiscount.value.title || '会员折扣'
  const rate = memberDiscount.value.discountRate
  if (rate) {
    const zhe = Number.parseFloat(rate) * 10
    return `${title}(${zhe % 1 === 0 ? zhe : zhe.toFixed(1)}折)`
  }
  return title
})

const selectedCoupon = computed(() => {
  return couponList.value.find(c => c.id === selectedCouponId.value)
})

const couponActionText = computed(() => {
  if (selectedCoupon.value) {
    return selectedCoupon.value.name
  }
  if (couponData.value.available > 0) {
    return `${couponData.value.available}张可用`
  }
  return '暂无可用优惠券'
})

// 监听优惠金额变化，获取可用优惠券和会员折扣试算
watch(() => discountFormData.value.amount, () => {
  // 重置已选优惠券
  selectedCouponId.value = null
  couponData.value.discountAmount = 0
  // 获取可用优惠券数量（只针对优惠金额）
  fetchUsableCoupons()
  // 获取会员折扣试算（只针对优惠金额）
  fetchDiscountPreview()
})

// 页面加载
onLoad((options) => {
  checkoutBackOptions.value = {
    source: typeof options?.source === 'string' ? options.source : '',
    scene: typeof options?.scene === 'string' ? options.scene : '',
  }

  if (options?.scene) {
    try {
      const parsed = parseStoreQrcodeScene(options.scene)
      shopId.value = parsed.merId
    }
    catch (error) {
      console.error('店铺二维码参数解析失败:', error)
      uni.showToast({
        title: '二维码参数错误',
        icon: 'none',
      })
      return
    }
  }
  else if (options?.id) {
    shopId.value = Number(options.id)
  }
  if (options?.name) {
    merchantData.value.name = decodeURIComponent(options.name)
  }
  if (options?.ai_log_id) {
    aiLogId.value = Number(options.ai_log_id) || 0
  }
  fetchCheckoutMerchant()
  // 获取用户积分数据
  fetchUserAssets()
})

async function fetchCheckoutMerchant() {
  if (shopId.value <= 0) {
    uni.showToast({
      title: '缺少商户ID',
      icon: 'none',
    })
    return
  }

  try {
    const res = await getStoreDetail(shopId.value)
    const display = res.display
    const merchant = res.merchant
    storeList.value = normalizeCheckoutStoreList(res.branches || [])
    const currentMerId = Number(display.mer_id || merchant.mer_id || shopId.value)
    const initialStore = storeList.value.find(store => store.id === currentMerId) || storeList.value[0]
    selectedStoreId.value = initialStore?.id || currentMerId
    tempSelectedStoreId.value = selectedStoreId.value
    shopId.value = selectedStoreId.value
    // 仅写入品牌级数据，门店信息（store/storeName/storeAddress/id）由 currentStoreInfo 自动跟随 selectedStoreId
    merchantData.value = {
      name: display.mer_name || merchant.mer_name || '',
      description: display.slogan || merchant.mer_info || '',
      image: display.mer_avatar || merchant.mer_avatar || '',
    }
    // 进入页面即获取折扣率，不等用户输入金额
    fetchMemberDiscountRate()
  }
  catch (error) {
    console.error('获取买单商户失败:', error)
    uni.showToast({
      title: '获取商户信息失败',
      icon: 'none',
    })
  }
}

// 获取用户积分数据
async function fetchUserAssets() {
  try {
    const res = await getUserAssets()
    if (res) {
      pointsData.value.available = res.points || 0
    }
  }
  catch (error) {
    console.error('获取用户积分失败:', error)
    uni.showToast({
      title: '获取用户积分失败',
      icon: 'none',
    })
  }
}

// 页面进入即获取会员折扣率（不依赖金额输入）
async function fetchMemberDiscountRate() {
  if (shopId.value <= 0)
    return
  try {
    const res = await calculateDiscount({
      mer_id: shopId.value,
      amount: '1',
      useMemberDiscount: true,
    })
    memberDiscount.value.title = res.discount_rule || ''
    memberDiscount.value.discountRate = res.discount_rate || ''
  }
  catch {
    // 静默失败，不影响页面
  }
}

// 获取会员折扣试算（只针对优惠金额）
async function fetchDiscountPreview() {
  const amount = Number.parseFloat(discountFormData.value.amount) || 0

  if (amount <= 0 || shopId.value <= 0) {
    discountPreview.value = undefined
    // 只清折扣金额，保留折扣率（进入页面已获取）
    memberDiscount.value.discountAmount = 0
    return
  }

  const requestId = ++discountPreviewRequestId

  try {
    // 优惠金额默认使用会员折扣
    const res = await calculateDiscount({
      mer_id: shopId.value,
      amount: discountFormData.value.amount,
      useMemberDiscount: true,
    })

    if (requestId !== discountPreviewRequestId) {
      return
    }

    discountPreview.value = res
    memberDiscount.value = {
      title: res.discount_rule || '',
      discountAmount: Number.parseFloat(res.saved_amount || '0') || 0,
      discountRate: res.discount_rate || '',
    }
  }
  catch (error) {
    if (requestId !== discountPreviewRequestId) {
      return
    }

    console.error('优惠试算失败:', error)
    discountPreview.value = undefined
    memberDiscount.value.discountAmount = 0
  }
}

// 获取可用优惠券（只针对优惠金额）
async function fetchUsableCoupons() {
  const amount = Number.parseFloat(discountFormData.value.amount) || 0
  console.log('[优惠券] shopId:', shopId.value, 'amount:', amount)

  if (amount <= 0 || shopId.value <= 0) {
    console.log('[优惠券] 参数不足，跳过请求')
    couponList.value = []
    couponData.value.available = 0
    return
  }

  try {
    console.log('[优惠券] 请求参数:', { shopId: shopId.value, amount, page: 1, limit: 100 })
    const res = await getUsableCoupons({
      shopId: shopId.value,
      amount,
      page: 1,
      limit: 100,
    })
    console.log('[优惠券] 响应结果:', res)
    if (res && res.list) {
      couponList.value = res.list.map(coupon => ({
        id: coupon.id,
        amount: Number.parseFloat(coupon.amount),
        condition: coupon.condition,
        name: coupon.name,
        expireTime: coupon.expireTime,
        isUsed: false,
      }))
      couponData.value.available = res.count || 0
    }
  }
  catch (error) {
    console.error('获取可用优惠券失败:', error)
    uni.showToast({
      title: '获取可用优惠券失败',
      icon: 'none',
    })
  }
}

// 计算总金额 = 优惠金额 + 不参与优惠金额
const totalAmount = computed(() => {
  const discountAmount = Number.parseFloat(discountFormData.value.amount) || 0
  const noDiscountAmount = Number.parseFloat(noDiscountFormData.value.amount) || 0

  // 优惠金额部分：扣除会员折扣和优惠券
  let discountPayAmount = discountAmount
  if (discountPreview.value?.pay_amount) {
    discountPayAmount = Number.parseFloat(discountPreview.value.pay_amount) || discountAmount
  }
  discountPayAmount = Math.max(0, discountPayAmount - couponData.value.discountAmount)

  // 总金额 = 优惠后金额 + 不参与优惠金额
  return (discountPayAmount + noDiscountAmount).toFixed(2)
})

const storeSelectText = computed(() => {
  if (selectedStore.value) {
    return selectedStore.value.name
  }
  return merchantData.value.name || '请选择门店'
})

// 选择门店
function handleSelectStore() {
  if (storeList.value.length <= 1) {
    uni.showToast({
      title: '当前商户暂无可切换门店',
      icon: 'none',
    })
    return
  }
  tempSelectedStoreId.value = selectedStoreId.value
  showStorePopup.value = true
}

// 选择临时门店
function handleTempSelectStore(id: number) {
  tempSelectedStoreId.value = id
}

// 取消选择门店
function handleCancelStore() {
  tempSelectedStoreId.value = selectedStoreId.value
  showStorePopup.value = false
}

// 确认选择门店
function handleConfirmStore() {
  const selected = storeList.value.find(s => s.id === tempSelectedStoreId.value)
  if (selected) {
    const previousStoreId = selectedStoreId.value
    // 仅更新 selectedStoreId / shopId，顶部展示由 currentStoreInfo 自动跟随
    selectedStoreId.value = selected.id
    shopId.value = selected.id
    if (previousStoreId !== selected.id) {
      selectedCouponId.value = null
      couponData.value.discountAmount = 0
      couponData.value.available = 0
      couponList.value = []
      discountPreview.value = undefined
      void fetchMemberDiscountRate()
      void fetchUsableCoupons()
      void fetchDiscountPreview()
    }
  }
  showStorePopup.value = false
}

// 选择优惠券
function handleSelectCoupon() {
  showCouponPopup.value = true
}

// 使用优惠券
function handleUseCoupon(id: number) {
  const coupon = couponList.value.find(c => c.id === id)
  if (coupon) {
    selectedCouponId.value = id
    couponData.value.discountAmount = coupon.amount
    showCouponPopup.value = false
  }
}

// 取消使用优惠券
function handleCancelCoupon(id: number) {
  const coupon = couponList.value.find(c => c.id === id)
  if (coupon) {
    selectedCouponId.value = null
    couponData.value.discountAmount = 0
    showCouponPopup.value = false
  }
}

// 关闭优惠券弹窗
function handleCloseCouponPopup() {
  showCouponPopup.value = false
}

// 切换积分抵扣（只针对优惠金额）
function handlePointsToggle() {
  discountFormData.value.usePoints = !discountFormData.value.usePoints
}

// 返回
function handleBack() {
  const action = getCheckoutBackAction(checkoutBackOptions.value)
  if (action.type === 'switchTab') {
    uni.switchTab({ url: action.url })
    return
  }

  uni.navigateBack()
}

// 支付加载状态
const isPaying = ref(false)

function finishPayFlow() {
  isPaying.value = false
}

function redirectAfterPay(url: string) {
  setTimeout(() => {
    finishPayFlow()
    uni.redirectTo({ url })
  }, 1500)
}

// 去支付
async function handlePay() {
  const discountAmount = Number.parseFloat(discountFormData.value.amount) || 0
  const noDiscountAmount = Number.parseFloat(noDiscountFormData.value.amount) || 0

  // 至少需要输入一个金额
  if (discountAmount <= 0 && noDiscountAmount <= 0) {
    uni.showToast({
      title: '请输入金额',
      icon: 'none',
    })
    return
  }

  if (isPaying.value) {
    return
  }

  if (storeList.value.length > 0 && selectedStoreId.value <= 0) {
    uni.showToast({
      title: '请选择门店',
      icon: 'none',
    })
    return
  }

  isPaying.value = true

  try {
    // 调用合并下单接口
    const params = buildCombinedOrderParams({
      merId: selectedStoreId.value || shopId.value,
      discountAmount: discountFormData.value.amount || '0',
      noDiscountAmount: noDiscountFormData.value.amount || '0',
      payType: 'routine',
      selectedCouponId: selectedCouponId.value,
      usePoints: discountFormData.value.usePoints,
      useMemberDiscount: true,
      remark: remark.value,
    })

    console.log('下单参数：', params)

    const res = await createCombinedOrder(params)

    console.log('下单成功：', res)

    if (res) {
      // 调起支付
      handlePayment(res)
    }
  }
  catch (error: any) {
    console.error('创建订单失败:', error)
    finishPayFlow()
    uni.showToast({
      title: error?.message || '创建订单失败，请重试',
      icon: 'none',
    })
  }
}

async function resolveDetailOrderId(groupOrderId: number, storeOrderId?: number) {
  if (storeOrderId && storeOrderId > 0) {
    return storeOrderId
  }

  const payResult = await getPayResult(groupOrderId)
  if (payResult.storeOrderId > 0) {
    return payResult.storeOrderId
  }

  throw new Error('未获取到订单详情ID')
}

// 处理支付回调
function handlePayment(paymentData: IOrderCreateCombinedResult) {
  const { order_id, store_order_id, type, ...rest } = paymentData

  // 根据支付类型调起对应支付
  if (isWechatMiniProgramPayType(type)) {
    // #ifdef MP-WEIXIN
    const paymentOptions = buildMiniProgramPaymentOptions(paymentData)
    // uni-app typings要求 orderInfo，但微信小程序实际使用 timeStamp/nonceStr/package/signType/paySign。
    uni.requestPayment(({
      ...paymentOptions,
      success: () => {
        void onPaymentSuccess(order_id, store_order_id)
      },
      fail: (err) => {
        onPaymentFail(err)
      },
    }) as any)
    // #endif
    // #ifdef H5
    // H5环境 - 检查是否在微信环境
    if (typeof window !== 'undefined' && (window as any).WeixinJSBridge) {
      (window as any).WeixinJSBridge.invoke('getBrandWCPayRequest', rest, (payRes: any) => {
        if (payRes.err_msg === 'get_brand_wcpay_request:ok') {
          void onPaymentSuccess(order_id, store_order_id)
        }
        else {
          finishPayFlow()
          uni.showToast({ title: '支付取消', icon: 'none' })
        }
      })
    }
    else {
      finishPayFlow()
      uni.showToast({ title: '当前环境不支持微信支付', icon: 'none' })
    }
    // #endif
  }
  else if (type === 'alipay') {
    // #ifdef MP-ALIPAY
    // 支付宝小程序支付
    const myPayment = (globalThis as any).my
    if (myPayment) {
      myPayment.tradePay({
        tradeNO: rest.tradeNO,
        success: () => {
          void onPaymentSuccess(order_id, store_order_id)
        },
        fail: (err: any) => {
          onPaymentFail(err)
        },
      })
    }
    else {
      finishPayFlow()
      uni.showToast({ title: '当前环境不支持支付宝支付', icon: 'none' })
    }
    // #endif
  }
  else if (type === 'balance') {
    // 余额支付成功
    void onPaymentSuccess(order_id, store_order_id)
  }
  else {
    finishPayFlow()
    uni.showToast({ title: `暂不支持支付方式：${type || '未知'}`, icon: 'none' })
  }
}

// 支付成功回调
async function onPaymentSuccess(orderId: number, storeOrderId?: number) {
  uni.showToast({ title: '支付成功', icon: 'success' })
  reportAiOrderEvent()

  try {
    const detailOrderId = await resolveDetailOrderId(orderId, storeOrderId)
    redirectAfterPay(`/pages/orders/detail?id=${detailOrderId}`)
  }
  catch (error) {
    console.error('支付成功后获取订单详情ID失败：', error)
    redirectAfterPay('/pages/orders/orders')
  }
}

function reportAiOrderEvent() {
  if (aiLogId.value <= 0 || shopId.value <= 0) {
    return
  }
  void postAiEvent({
    log_id: aiLogId.value,
    event: 'order',
    mer_id: selectedStoreId.value || shopId.value,
  }).catch((error) => {
    console.error('AI 买单归因上报失败:', error)
  })
}

// 支付失败回调
function onPaymentFail(err: any) {
  console.error('支付失败：', err)
  finishPayFlow()
  uni.showToast({ title: '支付取消', icon: 'none' })
}
</script>

<template>
  <view class="checkout-page">
    <!-- 顶部导航栏 -->
    <view class="nav-bar">
      <view class="nav-left" @tap="handleBack">
        <text class="back-icon">‹</text>
      </view>
      <view class="nav-title">
        <text class="title-text">买单付款</text>
      </view>
      <view class="nav-right" />
    </view>

    <!-- 内容区域 -->
    <scroll-view scroll-y class="scroll-content">
      <!-- 商家信息 -->
      <view class="merchant-section">
        <view class="merchant-card">
          <image v-if="merchantData.image" :src="merchantData.image" class="merchant-image" mode="aspectFill" />
          <view v-else class="merchant-image merchant-image--empty">
            <text class="i-carbon-store text-36rpx text-orange-500" />
          </view>
          <view class="merchant-info">
            <view class="merchant-name-row">
              <text class="merchant-name">{{ currentStoreInfo.storeName || merchantData.name }}</text>
            </view>
            <view v-if="currentStoreInfo.storeAddress" class="merchant-store-address">
              <text class="merchant-store-address-text">{{ currentStoreInfo.storeAddress }}</text>
            </view>
          </view>
        </view>
        <view v-if="storeList.length > 0 || currentStoreInfo.storeAddress" class="store-select" @tap="handleSelectStore">
          <text class="store-text">选择门店：{{ storeSelectText }}</text>
          <text class="store-arrow">›</text>
        </view>
      </view>

      <!-- 优惠金额区域（支持所有优惠） -->
      <view class="form-section">
        <!-- 金额输入 -->
        <view class="form-item">
          <view class="form-label">
            <text class="label-text">优惠金额(¥)：</text>
          </view>
          <view class="form-input">
            <input
              :value="discountFormData.amount"
              type="digit"
              inputmode="decimal"
              class="input-field"
              placeholder="输入前，请先询问服务员"
              placeholder-class="placeholder-text"
              :maxlength="10"
              @input="handleAmountInput($event, discountFormData)"
            >
          </view>
        </view>

        <!-- 会员折扣 -->
        <view class="form-item">
          <view class="form-label">
            <text class="label-text">{{ memberDiscountLabel }}：</text>
          </view>
          <view class="form-value">
            <text class="value-text discount-text">- ¥{{ memberDiscount.discountAmount.toFixed(2) }}</text>
          </view>
        </view>

        <!-- 优惠券 -->
        <view class="form-item coupon-item" @tap="handleSelectCoupon">
          <view class="form-label">
            <text class="label-text">优惠券：</text>
          </view>
          <view class="form-action">
            <text class="action-text">{{ couponActionText }}</text>
            <text class="action-arrow">›</text>
          </view>
        </view>

        <!-- 积分抵扣 -->
        <view class="form-item points-item">
          <view class="points-info">
            <view class="points-text-row">
              <text class="points-label">可用</text>
              <text class="points-value highlight">{{ pointsData.available }}积分</text>
            </view>
            <text class="points-hint">实际抵扣金额以下单结果为准</text>
          </view>
          <view class="points-checkbox" @tap="handlePointsToggle">
            <view class="checkbox" :class="{ checked: discountFormData.usePoints }">
              <text v-if="discountFormData.usePoints" class="check-icon">✓</text>
            </view>
            <text class="checkbox-label">使用积分抵扣</text>
          </view>
        </view>
      </view>

      <!-- 不参与优惠金额区域（不使用任何优惠） -->
      <view class="form-section">
        <!-- 金额输入 -->
        <view class="form-item">
          <view class="form-label">
            <text class="label-text">不参与优惠金额(¥)：</text>
          </view>
          <view class="form-input">
            <input
              :value="noDiscountFormData.amount"
              type="digit"
              inputmode="decimal"
              class="input-field"
              placeholder="输入不参与优惠的金额"
              placeholder-class="placeholder-text"
              :maxlength="10"
              @input="handleAmountInput($event, noDiscountFormData)"
            >
          </view>
        </view>
      </view>

      <!-- 备注区域 -->
      <view class="remark-section">
        <view class="remark-label">
          <text class="label-text">备注：</text>
        </view>
        <view class="remark-input">
          <textarea
            v-model="remark"
            class="remark-textarea"
            placeholder="请输入备注内容..."
            placeholder-class="placeholder-text"
            :maxlength="200"
          />
        </view>
      </view>

      <!-- 底部占位 -->
      <view class="bottom-placeholder" />
    </scroll-view>

    <!-- 底部支付栏 -->
    <view class="pay-bar-wrap">
      <view class="pay-bar">
        <view class="pay-amount">
          <text class="amount-label">金额：</text>
          <text class="amount-value">¥{{ totalAmount }}</text>
        </view>
        <view class="pay-btn" :class="{ 'pay-btn--disabled': isPaying }" @tap="handlePay">
          <text class="pay-btn-text">{{ isPaying ? '支付中...' : '去支付' }}</text>
        </view>
      </view>
    </view>

    <!-- 门店选择弹窗 -->
    <view v-if="showStorePopup" class="store-popup-mask" @tap="handleCancelStore">
      <view class="store-popup" @tap.stop>
        <!-- 标题 -->
        <view class="popup-header">
          <text class="popup-title">请选择门店</text>
        </view>
        <!-- 门店列表 -->
        <view class="store-list">
          <view
            v-for="store in storeList"
            :key="store.id"
            class="store-item"
            :class="{ active: tempSelectedStoreId === store.id }"
            @tap="handleTempSelectStore(store.id)"
          >
            <text class="store-item-text">{{ store.name }}</text>
            <text v-if="store.address" class="store-item-address">{{ store.address }}</text>
          </view>
        </view>
        <!-- 底部按钮 -->
        <view class="popup-footer">
          <view class="popup-btn cancel-btn" @tap="handleCancelStore">
            <text class="popup-btn-text cancel-text">取消</text>
          </view>
          <view class="popup-btn confirm-btn" @tap="handleConfirmStore">
            <text class="popup-btn-text confirm-text">确认门店</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 优惠券选择弹窗 -->
    <view v-if="showCouponPopup" class="coupon-popup-mask" @tap="handleCloseCouponPopup">
      <view class="coupon-popup" @tap.stop>
        <!-- 标题 -->
        <view class="popup-header">
          <text class="popup-title">请选择优惠券</text>
        </view>
        <!-- 优惠券列表 -->
        <view class="coupon-list">
          <view
            v-for="coupon in couponList"
            :key="coupon.id"
            class="coupon-item"
          >
            <view class="coupon-left">
              <view class="coupon-amount-row">
                <text class="coupon-yen">¥</text>
                <text class="coupon-amount">{{ coupon.amount }}</text>
              </view>
              <text class="coupon-condition">{{ coupon.condition }}</text>
            </view>
            <view class="coupon-center">
              <text class="coupon-name">{{ coupon.name }}</text>
              <text class="coupon-expire">有效期：{{ coupon.expireTime }}</text>
            </view>
            <view class="coupon-right">
              <view
                v-if="coupon.isUsed"
                class="coupon-action-btn used-btn"
                @tap="handleCancelCoupon(coupon.id)"
              >
                <text class="coupon-action-text used-text">取消</text>
              </view>
              <view
                v-else
                class="coupon-action-btn use-btn"
                @tap="handleUseCoupon(coupon.id)"
              >
                <text class="coupon-action-text use-text">使用</text>
              </view>
            </view>
          </view>
        </view>
        <!-- 底部取消按钮 -->
        <view class="coupon-popup-footer">
          <view class="coupon-cancel-btn" @tap="handleCloseCouponPopup">
            <text class="coupon-cancel-text">取消</text>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.checkout-page {
  width: 100vw;
  max-width: 100vw;
  min-height: 100vh;
  background: linear-gradient(180deg, #f0f0ff 0%, #e8e8ff 30%, #f5f5ff 100%);
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
}

// 顶部导航栏
.nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 88rpx;
  padding-top: env(safe-area-inset-top);
  background: transparent;
}

.nav-left {
  width: 88rpx;
  height: 88rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-icon {
  font-size: 48rpx;
  color: #333;
  font-weight: bold;
}

.nav-title {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.title-text {
  font-size: 34rpx;
  font-weight: bold;
  color: #1a1a1a;
}

.nav-right {
  width: 88rpx;
}

// 滚动内容区域
.scroll-content {
  flex: 1;
  width: 100%;
  padding: 0 32rpx;
  box-sizing: border-box;
}

// 商家信息区域
.merchant-section {
  margin-bottom: 24rpx;
  overflow: hidden;
}

.merchant-card {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 24rpx;
  padding: 24rpx;
  box-shadow: 0 4rpx 20rpx rgba(100, 100, 255, 0.08);
  overflow: hidden;
}

.merchant-image {
  width: 120rpx;
  height: 120rpx;
  border-radius: 16rpx;
  margin-right: 24rpx;
}

.merchant-image--empty {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff7ed;
}

.merchant-info {
  flex: 1;
  min-width: 0;
}

.merchant-name-row {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8rpx;
  overflow: hidden;
  min-width: 0;
}

.merchant-name {
  font-size: 32rpx;
  font-weight: bold;
  color: #1a1a1a;
}

.merchant-store {
  font-size: 28rpx;
  color: #666;
}

.merchant-store-address {
  margin-top: 8rpx;
}

.merchant-store-address-text {
  font-size: 24rpx;
  color: #999;
  line-height: 1.4;
}

.merchant-desc {
  font-size: 28rpx;
  color: #999;
}

.store-select {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 40rpx;
  padding: 16rpx 32rpx;
  margin-top: 16rpx;
  overflow: hidden;
}

.store-text {
  font-size: 26rpx;
  color: #fff;
  flex: 1;
}

.store-arrow {
  font-size: 28rpx;
  color: #fff;
  margin-left: 8rpx;
}

// 表单区域
.form-section {
  background: #fff;
  border-radius: 24rpx;
  padding: 8rpx 32rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 4rpx 20rpx rgba(100, 100, 255, 0.08);
  overflow: hidden;
  box-sizing: border-box;
}

// 区域标题
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24rpx 0;
  border-bottom: 1rpx solid #f0f0f5;
}

.section-title {
  font-size: 30rpx;
  font-weight: bold;
  color: #1a1a1a;
}

.section-desc {
  font-size: 24rpx;
  color: #999;
}

.form-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 100rpx;
  border-bottom: 1rpx solid #f0f0f5;
  overflow: hidden;
}

.form-item:last-child {
  border-bottom: none;
}

.form-label {
  flex-shrink: 0;
}

.label-text {
  font-size: 28rpx;
  color: #333;
}

.form-input {
  flex: 1;
  display: flex;
  justify-content: flex-end;
}

.input-field {
  width: 100%;
  text-align: right;
  font-size: 28rpx;
  color: #333;
}

.placeholder-text {
  color: #ccc;
  font-size: 26rpx;
}

.form-value {
  display: flex;
  align-items: center;
}

.value-text {
  font-size: 28rpx;
  color: #333;
}

.discount-text {
  color: #ff6b6b;
  font-weight: bold;
}

.coupon-item {
  cursor: pointer;
}

.form-action {
  display: flex;
  align-items: center;
}

.action-text {
  font-size: 26rpx;
  color: #6366f1;
}

.action-arrow {
  font-size: 28rpx;
  color: #6366f1;
  margin-left: 8rpx;
}

.points-item {
  flex-direction: column;
  align-items: flex-start;
  padding: 24rpx 0;
  overflow: hidden;
}

.points-info {
  width: 100%;
  margin-bottom: 16rpx;
  overflow: hidden;
}

.points-text-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  overflow: hidden;
}

.points-label {
  font-size: 26rpx;
  color: #666;
}

.points-value {
  font-size: 26rpx;
}

.points-value.highlight {
  color: #ff6b6b;
  font-weight: bold;
}

.points-hint {
  font-size: 22rpx;
  color: #999;
  margin-top: 8rpx;
}

.points-checkbox {
  display: flex;
  align-items: center;
  align-self: flex-end;
}

.checkbox {
  width: 40rpx;
  height: 40rpx;
  border: 2rpx solid #ddd;
  border-radius: 8rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12rpx;
  transition: all 0.2s ease;
}

.checkbox.checked {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-color: #6366f1;
}

.check-icon {
  font-size: 24rpx;
  color: #fff;
  font-weight: bold;
}

.checkbox-label {
  font-size: 26rpx;
  color: #666;
}

// 备注区域
.remark-section {
  background: #fff;
  border-radius: 24rpx;
  padding: 32rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 4rpx 20rpx rgba(100, 100, 255, 0.08);
  overflow: hidden;
  box-sizing: border-box;
}

.remark-label {
  margin-bottom: 16rpx;
}

.remark-input {
  width: 100%;
}

.remark-textarea {
  width: 100%;
  min-height: 160rpx;
  font-size: 28rpx;
  color: #333;
  line-height: 1.6;
}

// 底部占位
.bottom-placeholder {
  height: 200rpx;
}

// 底部支付栏
.pay-bar-wrap {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 999;
  background: #fff;
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
  box-shadow: 0 -4rpx 20rpx rgba(100, 100, 255, 0.1);
}

.pay-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100rpx;
  padding: 0 20rpx;
}

.pay-amount {
  display: flex;
  align-items: baseline;
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.amount-label {
  font-size: 24rpx;
  color: #666;
}

.amount-value {
  font-size: 32rpx;
  font-weight: bold;
  color: #ff6b6b;
}

.pay-btn {
  flex-shrink: 0;
  padding: 0 32rpx;
  height: 64rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 32rpx;
  box-shadow: 0 4rpx 12rpx rgba(99, 102, 241, 0.3);
}

.pay-btn--disabled {
  opacity: 0.72;
}

.pay-btn-text {
  font-size: 28rpx;
  font-weight: bold;
  color: #fff;
  white-space: nowrap;
}

// 门店选择弹窗
.store-popup-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 1000;
}

.store-popup {
  width: 100%;
  background: #f5f5f5;
  border-radius: 32rpx 32rpx 0 0;
  overflow: hidden;
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}

.popup-header {
  padding: 40rpx 0 24rpx;
  text-align: center;
}

.popup-title {
  font-size: 34rpx;
  font-weight: bold;
  color: #1a1a1a;
}

.store-list {
  padding: 0 32rpx;
}

.store-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8rpx;
  background: #fff;
  border-radius: 16rpx;
  padding: 28rpx 32rpx;
  margin-bottom: 20rpx;
  text-align: center;
  border: 2rpx solid transparent;
  transition: all 0.2s ease;
}

.store-item.active {
  border-color: #8b5cf6;
}

.store-item-text {
  font-size: 30rpx;
  color: #333;
}

.store-item.active .store-item-text {
  color: #8b5cf6;
}

.store-item-address {
  font-size: 24rpx;
  line-height: 1.4;
  color: #888;
}

.store-item.active .store-item-address {
  color: #7c3aed;
}

.popup-footer {
  display: flex;
  padding: 20rpx 32rpx 32rpx;
  gap: 24rpx;
}

.popup-btn {
  flex: 1;
  height: 88rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 44rpx;
}

.cancel-btn {
  background: #fff;
}

.confirm-btn {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.popup-btn-text {
  font-size: 30rpx;
  font-weight: bold;
}

.cancel-text {
  color: #333;
}

.confirm-text {
  color: #fff;
}

// 优惠券选择弹窗
.coupon-popup-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: 1000;
}

.coupon-popup {
  width: 100%;
  background: #f5f5f5;
  border-radius: 32rpx 32rpx 0 0;
  overflow: hidden;
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}

.coupon-list {
  padding: 0 32rpx;
}

.coupon-item {
  display: flex;
  align-items: center;
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
}

.coupon-left {
  flex-shrink: 0;
  width: 140rpx;
  margin-right: 24rpx;
}

.coupon-amount-row {
  display: flex;
  align-items: baseline;
}

.coupon-yen {
  font-size: 24rpx;
  color: #ff6b6b;
  font-weight: bold;
}

.coupon-amount {
  font-size: 48rpx;
  color: #ff6b6b;
  font-weight: bold;
}

.coupon-condition {
  font-size: 20rpx;
  color: #ff6b6b;
  margin-top: 4rpx;
}

.coupon-center {
  flex: 1;
  min-width: 0;
}

.coupon-name {
  font-size: 30rpx;
  color: #333;
  font-weight: bold;
}

.coupon-expire {
  font-size: 22rpx;
  color: #999;
  margin-top: 8rpx;
}

.coupon-right {
  flex-shrink: 0;
  margin-left: 16rpx;
}

.coupon-action-btn {
  padding: 12rpx 32rpx;
  border-radius: 32rpx;
}

.used-btn {
  background: #f0f0f0;
}

.use-btn {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.coupon-action-text {
  font-size: 26rpx;
  font-weight: bold;
}

.used-text {
  color: #999;
}

.use-text {
  color: #fff;
}

.coupon-popup-footer {
  padding: 20rpx 32rpx 32rpx;
}

.coupon-cancel-btn {
  width: 100%;
  height: 88rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border-radius: 44rpx;
}

.coupon-cancel-text {
  font-size: 30rpx;
  font-weight: bold;
  color: #333;
}
</style>

<script lang="ts" setup>
import type {
  IFinanceRecord,
  IFinanceRecordsParams,
  ISaveAccountParams,
  ISettlementOverview,
  IWithdrawApplyParams,
  IWithdrawCurrentApply,
  IWithdrawRecord,
  IWithdrawRecordsParams,
} from '@/api/mer'
import { onShow } from '@dcloudio/uni-app'
import {
  applyWithdraw,
  getFinanceOverview,
  getFinanceQuota,
  getFinanceRecords,
  getSettlementOverview,
  getWithdrawRecords,
  saveWithdrawAccount,
  uploadQrcode,
} from '@/api/mer'
import { useMerTokenStore } from '@/store/mer-token'

definePage({
  style: {
    navigationBarTitleText: '财务',
  },
})

const merTokenStore = useMerTokenStore()

type TFinanceTab = 'balance' | 'withdraw'

// 当前 Tab：balance=余额明细，withdraw=提现记录
const activeTab = ref<TFinanceTab>('balance')

// 财务概览数据（累计收款/退款）
const overviewData = ref({
  totalReceived: 0,
  yesterdayReceived: 0,
  monthReceived: 0,
  todayReceived: 0,
  totalRefund: 0,
  yesterdayRefund: 0,
  monthRefund: 0,
  todayRefund: 0,
  todayOrderCount: 0,
})

// 已销售额度数据
const quotaData = ref({
  salesQuota: 0,
  totalQuota: 0,
})

// 提现概览数据
const settlementData = ref<ISettlementOverview>({
  mer_id: 0,
  settlement_mode: 0,
  mer_money: '0',
  withdraw_rate: '0',
  account_type: 0,
  account_type_label: '',
  has_account: false,
  has_unfinished_apply: 0,
  current: null,
})

// 当前提现申请信息
const currentApply = ref<IWithdrawCurrentApply | null>(null)

// 余额明细列表
const records = ref<IFinanceRecord[]>([])
const recordsPage = ref(1)
const recordsTotalCount = ref(0)
const isLoadingRecords = ref(false)

// 提现记录列表
const withdrawRecords = ref<IWithdrawRecord[]>([])
const withdrawPage = ref(1)
const withdrawTotalCount = ref(0)
const isLoadingWithdraw = ref(false)

// 筛选参数
const filterParams = ref({
  type: '' as '' | 'income' | 'expense',
  date: '',
  keyword: '',
})

// 提现记录筛选参数
const withdrawFilterParams = ref({
  date: '',
  account_type: '' as '' | 2 | 3,
})

// 提现弹窗状态
const showWithdrawModal = ref(false)
const withdrawForm = ref<IWithdrawApplyParams>({
  extract_money: '0',
  financial_type: 2,
  mark: '',
})
const isWithdrawing = ref(false)

// 收款账户配置弹窗状态
const showAccountModal = ref(false)
const accountForm = ref<ISaveAccountParams>({
  financial_type: 2,
  name: '',
  wechat: '',
  wechat_code: '',
  alipay: '',
  alipay_code: '',
})
const isSavingAccount = ref(false)

// 收款码上传状态
const isUploadingQrcode = ref(false)
const qrcodePreviewUrl = ref('')
const serverBaseURL = import.meta.env.VITE_SERVER_BASEURL || ''

// 提现方式选项
const withdrawMethods = [
  { value: 2, label: '微信', color: 'green' },
  { value: 3, label: '支付宝', color: 'blue' },
]

function buildFinanceRecordsParams(): IFinanceRecordsParams {
  return {
    page: recordsPage.value,
    limit: 10,
    ...(filterParams.value.type ? { type: filterParams.value.type } : {}),
    ...(filterParams.value.date ? { date: filterParams.value.date } : {}),
    ...(filterParams.value.keyword ? { keyword: filterParams.value.keyword } : {}),
  }
}

function buildWithdrawRecordsParams(): IWithdrawRecordsParams {
  return {
    page: withdrawPage.value,
    limit: 10,
    ...(withdrawFilterParams.value.date ? { date: withdrawFilterParams.value.date } : {}),
    ...(withdrawFilterParams.value.account_type ? { account_type: withdrawFilterParams.value.account_type } : {}),
  }
}

// 加载财务概览
async function loadOverview() {
  try {
    const res = await getFinanceOverview()
    // 防御性处理：确保所有字段都有默认值
    overviewData.value = {
      totalReceived: res?.totalReceived || 0,
      yesterdayReceived: res?.yesterdayReceived || 0,
      monthReceived: res?.monthReceived || 0,
      todayReceived: res?.todayReceived || 0,
      totalRefund: res?.totalRefund || 0,
      yesterdayRefund: res?.yesterdayRefund || 0,
      monthRefund: res?.monthRefund || 0,
      todayRefund: res?.todayRefund || 0,
      todayOrderCount: res?.todayOrderCount || 0,
    }
  }
  catch (error) {
    console.error('获取财务概览失败:', error)
    // 保持默认值，避免渲染崩溃
  }
}

// 加载销售额度
async function loadQuota() {
  try {
    const res = await getFinanceQuota()
    // 防御性处理：确保所有字段都有默认值
    quotaData.value = {
      salesQuota: res?.salesQuota || 0,
      totalQuota: res?.totalQuota || 0,
    }
  }
  catch (error) {
    console.error('获取销售额度失败:', error)
    // 保持默认值，避免渲染崩溃
  }
}

// 加载提现概览
async function loadSettlement() {
  try {
    const res = await getSettlementOverview()
    // 防御性处理：确保所有字段都有默认值
    settlementData.value = {
      mer_id: res?.mer_id || 0,
      settlement_mode: res?.settlement_mode || 0,
      mer_money: res?.mer_money || '0',
      withdraw_rate: res?.withdraw_rate || '0',
      account_type: res?.account_type || 0,
      account_type_label: res?.account_type_label || '',
      has_account: res?.has_account || false,
      has_unfinished_apply: res?.has_unfinished_apply || 0,
      current: res?.current || null,
    }
    currentApply.value = res?.current || null
  }
  catch (error) {
    console.error('获取提现概览失败:', error)
    // 保持默认值，避免渲染崩溃
  }
}

// 加载余额明细列表
async function loadRecords(isRefresh = false) {
  if (isLoadingRecords.value) {
    return
  }

  if (isRefresh) {
    recordsPage.value = 1
    records.value = []
  }
  else {
    isLoadingRecords.value = true
  }

  try {
    const res = await getFinanceRecords(buildFinanceRecordsParams())
    if (isRefresh) {
      records.value = res.list
    }
    else {
      records.value.push(...res.list)
    }
    recordsTotalCount.value = res.count
    recordsPage.value++
  }
  catch (error) {
    console.error('获取余额明细失败:', error)
  }
  finally {
    isLoadingRecords.value = false
  }
}

// 加载提现记录列表
async function loadWithdrawRecords(isRefresh = false) {
  if (isLoadingWithdraw.value) {
    return
  }

  isLoadingWithdraw.value = true

  if (isRefresh) {
    withdrawPage.value = 1
    withdrawRecords.value = []
  }

  try {
    const res = await getWithdrawRecords(buildWithdrawRecordsParams())
    if (isRefresh) {
      withdrawRecords.value = res.list
    }
    else {
      withdrawRecords.value.push(...res.list)
    }
    withdrawTotalCount.value = res.count
    withdrawPage.value++
  }
  catch (error) {
    console.error('获取提现记录失败:', error)
  }
  finally {
    isLoadingWithdraw.value = false
  }
}

// 切换 Tab
function handleTabChange(tab: TFinanceTab) {
  activeTab.value = tab
  if (tab === 'balance') {
    if (records.value.length === 0) {
      loadRecords(true)
    }
  }
  else if (tab === 'withdraw') {
    loadWithdrawRecords(true)
  }
}

function handleWithdrawAccountTypeChange(accountType: '' | 2 | 3) {
  withdrawFilterParams.value.account_type = accountType
  loadWithdrawRecords(true)
}

// 下拉刷新
function onPullDownRefresh() {
  Promise.all([loadOverview(), loadQuota(), loadSettlement()]).then(() => {
    if (activeTab.value === 'balance') {
      loadRecords(true).then(() => uni.stopPullDownRefresh())
    }
    else {
      loadWithdrawRecords(true).then(() => uni.stopPullDownRefresh())
    }
  })
}

// 上拉加载更多
function onReachBottom() {
  if (activeTab.value === 'balance' && records.value.length < recordsTotalCount.value) {
    loadRecords()
  }
  else if (activeTab.value === 'withdraw' && withdrawRecords.value.length < withdrawTotalCount.value) {
    loadWithdrawRecords()
  }
}

// 打开提现弹窗
function handleWithdraw() {
  // 检查是否有未完成的提现申请
  if (settlementData.value.has_unfinished_apply > 0) {
    uni.showToast({ title: '当前存在未完成提现申请，请等待处理', icon: 'none' })
    return
  }

  // 检查是否已配置收款账户
  if (!settlementData.value.has_account) {
    uni.showModal({
      title: '提示',
      content: '请先配置收款账户',
      confirmText: '去配置',
      success: (res) => {
        if (res.confirm) {
          openAccountModal()
        }
      },
    })
    return
  }

  withdrawForm.value = {
    extract_money: '0',
    financial_type: settlementData.value.account_type || 2,
    mark: '',
  }
  showWithdrawModal.value = true
}

// 关闭提现弹窗
function closeWithdrawModal() {
  showWithdrawModal.value = false
}

// 提交提现申请
async function submitWithdraw() {
  if (isWithdrawing.value) {
    return
  }

  const amount = Number(withdrawForm.value.extract_money)

  // 验证提现金额
  if (!amount || amount <= 0) {
    uni.showToast({ title: '请输入提现金额', icon: 'none' })
    return
  }

  if (amount > Number(settlementData.value.mer_money)) {
    uni.showToast({ title: '提现金额大于可提现余额', icon: 'none' })
    return
  }

  isWithdrawing.value = true
  try {
    await applyWithdraw({
      extract_money: String(amount),
      financial_type: withdrawForm.value.financial_type,
      mark: withdrawForm.value.mark,
    })
    uni.showToast({ title: '申请成功', icon: 'success' })
    closeWithdrawModal()
    // 刷新数据
    await Promise.all([loadSettlement(), loadOverview()])
    if (activeTab.value === 'withdraw') {
      await loadWithdrawRecords(true)
    }
    else {
      await loadRecords(true)
    }
  }
  catch (error: any) {
    console.error('提现申请失败:', error)
    const msg = error?.message || '提现申请失败，请重试'
    uni.showToast({ title: msg, icon: 'none' })
  }
  finally {
    isWithdrawing.value = false
  }
}

// 打开收款账户配置弹窗
function openAccountModal() {
  accountForm.value = {
    financial_type: settlementData.value.account_type || 2,
    name: '',
    wechat: '',
    wechat_code: '',
    alipay: '',
    alipay_code: '',
  }
  qrcodePreviewUrl.value = ''
  showAccountModal.value = true
}

// 关闭收款账户配置弹窗
function closeAccountModal() {
  showAccountModal.value = false
}

// 选择并上传收款码图片
function handleChooseQrcode() {
  uni.chooseImage({
    count: 1,
    sizeType: ['compressed'],
    sourceType: ['album', 'camera'],
    success: async (res) => {
      const filePath = res.tempFilePaths[0]
      isUploadingQrcode.value = true
      try {
        const data = await uploadQrcode(filePath)
        const url = data.url || data.src
        if (accountForm.value.financial_type === 2) {
          accountForm.value.wechat_code = url
        }
        else {
          accountForm.value.alipay_code = url
        }
        qrcodePreviewUrl.value = filePath
        uni.showToast({ title: '上传成功', icon: 'success' })
      }
      catch (error: any) {
        console.error('上传收款码失败:', error)
        uni.showToast({ title: error?.message || '上传失败', icon: 'none' })
      }
      finally {
        isUploadingQrcode.value = false
      }
    },
  })
}

// 删除已上传的收款码
function handleRemoveQrcode() {
  if (accountForm.value.financial_type === 2) {
    accountForm.value.wechat_code = ''
  }
  else {
    accountForm.value.alipay_code = ''
  }
  qrcodePreviewUrl.value = ''
}

// 获取完整图片URL（兼容完整URL和相对路径）
function getFullImageUrl(path: string) {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return serverBaseURL + path
}

// 保存收款账户
async function submitAccount() {
  if (isSavingAccount.value) {
    return
  }

  // 验证收款人姓名
  if (!accountForm.value.name.trim()) {
    uni.showToast({ title: '请输入收款人姓名', icon: 'none' })
    return
  }

  // 根据账户类型验证必填字段
  if (accountForm.value.financial_type === 2) {
    if (!accountForm.value.wechat?.trim()) {
      uni.showToast({ title: '请输入微信号', icon: 'none' })
      return
    }
    if (!accountForm.value.wechat_code?.trim()) {
      uni.showToast({ title: '请上传微信收款二维码', icon: 'none' })
      return
    }
  }
  else if (accountForm.value.financial_type === 3) {
    if (!accountForm.value.alipay?.trim()) {
      uni.showToast({ title: '请输入支付宝账号', icon: 'none' })
      return
    }
    if (!accountForm.value.alipay_code?.trim()) {
      uni.showToast({ title: '请上传支付宝收款二维码', icon: 'none' })
      return
    }
  }

  isSavingAccount.value = true
  try {
    await saveWithdrawAccount(accountForm.value)
    uni.showToast({ title: '保存成功', icon: 'success' })
    closeAccountModal()
    // 刷新提现概览
    await loadSettlement()
  }
  catch (error: any) {
    console.error('保存收款账户失败:', error)
    const msg = error?.message || '保存失败，请重试'
    uni.showToast({ title: msg, icon: 'none' })
  }
  finally {
    isSavingAccount.value = false
  }
}

// 获取提现方式文本
function getWithdrawMethodText(type: number) {
  const method = withdrawMethods.find(m => m.value === type)
  return method ? method.label : '未知'
}

// 获取提现方式标签颜色
function getWithdrawMethodColor(type: number) {
  if (type === 2) {
    return 'text-green-500'
  }
  if (type === 3) {
    return 'text-blue-400'
  }
  return 'text-gray-500'
}

// 获取审核状态文本
function getStatusText(status: number) {
  if (status === 0) {
    return '处理中'
  }
  if (status === 1) {
    return '审核通过'
  }
  if (status === -1) {
    return '已拒绝'
  }
  return '未知'
}

// 获取审核状态颜色
function getStatusColor(status: number) {
  if (status === 0) {
    return 'text-orange-500'
  }
  if (status === 1) {
    return 'text-green-500'
  }
  if (status === -1) {
    return 'text-red-500'
  }
  return 'text-gray-500'
}

// 获取打款状态文本
function getPaymentStatusText(status: number) {
  if (status === 0) {
    return '待打款'
  }
  if (status === 1) {
    return '已打款'
  }
  return '未知'
}

onShow(() => {
  if (!merTokenStore.hasLogin) {
    uni.reLaunch({ url: '/pages/admin/login' })
    return
  }
  loadOverview()
  loadQuota()
  loadSettlement()
  if (activeTab.value === 'balance' && records.value.length === 0) {
    loadRecords(true)
  }
  else if (activeTab.value === 'withdraw' && withdrawRecords.value.length === 0) {
    loadWithdrawRecords(true)
  }
})
</script>

<template>
  <view class="min-h-screen bg-gray-50 pb-200rpx">
    <!-- 顶部统计卡片 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="flex gap-20rpx">
        <!-- 累计收款 -->
        <view class="flex-1 rounded-20rpx from-blue-400 to-blue-500 bg-gradient-to-br p-28rpx shadow-lg">
          <text class="block text-24rpx text-white/80">累计收款</text>
          <view class="mt-8rpx flex items-baseline">
            <text class="text-44rpx text-white font-bold">{{ overviewData.totalReceived.toFixed(2) }}</text>
            <text class="ml-8rpx text-24rpx text-white/80">元</text>
          </view>
          <view class="mt-16rpx">
            <text class="block text-20rpx text-white/70">昨日新增 ¥{{ overviewData.yesterdayReceived.toFixed(2) }}</text>
            <text class="mt-4rpx block text-20rpx text-white/70">本月新增 ¥{{ overviewData.monthReceived.toFixed(2) }}</text>
          </view>
        </view>

        <!-- 累计退款 -->
        <view class="flex-1 rounded-20rpx from-purple-400 to-purple-500 bg-gradient-to-br p-28rpx shadow-lg">
          <text class="block text-24rpx text-white/80">累计退款</text>
          <view class="mt-8rpx flex items-baseline">
            <text class="text-44rpx text-white font-bold">{{ overviewData.totalRefund.toFixed(2) }}</text>
            <text class="ml-8rpx text-24rpx text-white/80">元</text>
          </view>
          <view class="mt-16rpx">
            <text class="block text-20rpx text-white/70">昨日新增 ¥{{ overviewData.yesterdayRefund.toFixed(2) }}</text>
            <text class="mt-4rpx block text-20rpx text-white/70">本月新增 ¥{{ overviewData.monthRefund.toFixed(2) }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 已销售额度 -->
    <view class="mx-24rpx mt-20rpx">
      <view class="rounded-20rpx from-blue-500 via-blue-400 to-purple-400 bg-gradient-to-r p-28rpx shadow-lg">
        <text class="block text-24rpx text-white/80">已销售额度</text>
        <view class="mt-8rpx flex items-baseline">
          <text class="text-48rpx text-white font-bold">{{ quotaData.salesQuota.toFixed(2) }}</text>
          <text class="ml-8rpx text-24rpx text-white/80">元</text>
        </view>
        <view class="mt-8rpx">
          <text class="text-22rpx text-white/60">
            总额度：{{ quotaData.totalQuota > 0 ? quotaData.totalQuota.toFixed(2) : '无限制' }}
          </text>
        </view>
      </view>
    </view>

    <!-- 提现概览 -->
    <view class="mx-24rpx mt-20rpx">
      <view class="rounded-20rpx bg-white p-28rpx shadow-sm">
        <view class="flex items-center justify-between">
          <text class="block text-28rpx text-gray-800 font-bold">提现概览</text>
          <view
            class="flex items-center"
            @tap="openAccountModal"
          >
            <text class="text-24rpx text-blue-500">
              {{ settlementData.has_account ? `收款账户: ${settlementData.account_type_label}` : '配置收款账户' }}
            </text>
            <text class="i-carbon-chevron-right ml-8rpx text-24rpx text-gray-400" />
          </view>
        </view>

        <!-- 当前提现申请状态 -->
        <view v-if="currentApply" class="mt-16rpx rounded-16rpx bg-orange-50 p-20rpx">
          <view class="flex items-center justify-between">
            <text class="text-24rpx text-orange-600 font-medium">当前申请</text>
            <text
              class="text-22rpx"
              :class="getStatusColor(currentApply.status)"
            >
              {{ getStatusText(currentApply.status) }}
            </text>
          </view>
          <view class="mt-8rpx flex justify-between">
            <text class="text-22rpx text-gray-500">单号: {{ currentApply.financial_sn }}</text>
            <text class="text-22rpx text-gray-500">¥{{ currentApply.extract_money }}</text>
          </view>
          <view class="mt-4rpx flex justify-between">
            <text class="text-22rpx text-gray-500">{{ currentApply.create_time }}</text>
            <text class="text-22rpx text-gray-500">{{ getPaymentStatusText(currentApply.financial_status) }}</text>
          </view>
        </view>

        <view class="mt-16rpx flex justify-between">
          <view class="text-center">
            <text class="block text-24rpx text-gray-500">可提现余额</text>
            <text class="mt-8rpx block text-36rpx text-green-500 font-bold">¥{{ Number(settlementData.mer_money).toFixed(2) }}</text>
          </view>
          <view class="text-center">
            <text class="block text-24rpx text-gray-500">提现手续费</text>
            <text class="mt-8rpx block text-36rpx text-orange-500 font-bold">{{ settlementData.withdraw_rate }}%</text>
          </view>
        </view>
      </view>
    </view>

    <!-- Tab 切换 -->
    <view class="mx-24rpx mt-28rpx">
      <view class="flex border-b border-gray-200">
        <view
          class="flex-1 py-20rpx text-center"
          :class="activeTab === 'balance' ? 'border-b-solid border-b-4 border-blue-500' : ''"
          @tap="handleTabChange('balance')"
        >
          <text
            class="text-28rpx font-medium"
            :class="activeTab === 'balance' ? 'text-blue-500' : 'text-gray-500'"
          >
            余额明细
          </text>
        </view>
        <view
          class="flex-1 py-20rpx text-center"
          :class="activeTab === 'withdraw' ? 'border-b-solid border-b-4 border-blue-500' : ''"
          @tap="handleTabChange('withdraw')"
        >
          <text
            class="text-28rpx font-medium"
            :class="activeTab === 'withdraw' ? 'text-blue-500' : 'text-gray-500'"
          >
            提现记录
          </text>
        </view>
      </view>
    </view>

    <!-- 提现记录筛选 -->
    <view v-show="activeTab === 'withdraw'" class="mx-24rpx mt-16rpx">
      <view class="rounded-20rpx bg-white p-20rpx shadow-sm">
        <!-- 提现方式筛选 -->
        <view>
          <text class="text-24rpx text-gray-500">提现方式</text>
          <view class="mt-8rpx flex gap-16rpx">
            <view
              class="rounded-24rpx px-24rpx py-12rpx"
              :class="withdrawFilterParams.account_type === '' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600'"
              @tap="handleWithdrawAccountTypeChange('')"
            >
              <text class="text-22rpx">全部</text>
            </view>
            <view
              class="rounded-24rpx px-24rpx py-12rpx"
              :class="withdrawFilterParams.account_type === 2 ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600'"
              @tap="handleWithdrawAccountTypeChange(2)"
            >
              <text class="text-22rpx">微信</text>
            </view>
            <view
              class="rounded-24rpx px-24rpx py-12rpx"
              :class="withdrawFilterParams.account_type === 3 ? 'bg-blue-400 text-white' : 'bg-gray-100 text-gray-600'"
              @tap="handleWithdrawAccountTypeChange(3)"
            >
              <text class="text-22rpx">支付宝</text>
            </view>
          </view>
        </view>
      </view>
    </view>

    <!-- 余额明细列表 -->
    <view v-show="activeTab === 'balance'" class="mx-24rpx mt-16rpx">
      <view v-if="records.length === 0 && !isLoadingRecords" class="py-80rpx text-center">
        <text class="text-26rpx text-gray-400">暂无余额明细</text>
      </view>
      <view
        v-for="record in records"
        :key="record.id"
        class="mt-16rpx rounded-20rpx bg-white p-28rpx shadow-sm"
      >
        <view class="flex items-start justify-between">
          <view class="flex-1">
            <text class="block text-26rpx text-gray-800 font-medium">{{ record.mark || '交易' }}</text>
            <text class="mt-8rpx block text-22rpx text-gray-400">{{ record.create_time }}</text>
          </view>
          <view class="text-right">
            <text
              class="block text-28rpx font-bold"
              :class="record.type === 'income' ? 'text-green-500' : 'text-red-500'"
            >
              {{ record.type === 'income' ? '+' : '-' }}¥{{ record.amount.toFixed(2) }}
            </text>
          </view>
        </view>
      </view>
      <view v-if="isLoadingRecords" class="py-40rpx text-center">
        <text class="text-24rpx text-gray-400">加载中...</text>
      </view>
      <view v-if="records.length > 0 && records.length >= recordsTotalCount" class="py-40rpx text-center">
        <text class="text-24rpx text-gray-400">没有更多了</text>
      </view>
    </view>

    <!-- 提现记录列表 -->
    <view v-show="activeTab === 'withdraw'" class="mx-24rpx mt-16rpx">
      <view v-if="withdrawRecords.length === 0 && !isLoadingWithdraw" class="py-80rpx text-center">
        <text class="text-26rpx text-gray-400">暂无提现记录</text>
      </view>
      <view
        v-for="record in withdrawRecords"
        :key="record.id"
        class="mt-16rpx rounded-20rpx bg-white p-28rpx shadow-sm"
      >
        <view class="flex items-start justify-between">
          <view class="flex-1">
            <view class="flex items-center">
              <text class="text-26rpx text-gray-800 font-medium">
                提现-到{{ getWithdrawMethodText(record.financial_type) }}
              </text>
              <text v-if="record.card_last_four" class="ml-8rpx text-24rpx text-gray-500">
                （{{ record.card_last_four }}）
              </text>
            </view>
            <text class="mt-8rpx block text-22rpx text-gray-400">{{ record.create_time }}</text>
            <text class="mt-4rpx block text-22rpx text-gray-400">
              余额：¥{{ Number(record.balance).toFixed(2) }}
            </text>
          </view>
          <view class="text-right">
            <text class="block text-28rpx font-bold text-red-500">
              -¥{{ Number(record.amount).toFixed(2) }}
            </text>
            <text
              class="mt-4rpx block text-20rpx"
              :class="record.status === 1 ? 'text-green-500' : record.status === 2 ? 'text-red-500' : 'text-orange-500'"
            >
              {{ record.status_text }}
            </text>
          </view>
        </view>
      </view>
      <view v-if="isLoadingWithdraw" class="py-40rpx text-center">
        <text class="text-24rpx text-gray-400">加载中...</text>
      </view>
      <view v-if="withdrawRecords.length > 0 && withdrawRecords.length >= withdrawTotalCount" class="py-40rpx text-center">
        <text class="text-24rpx text-gray-400">没有更多了</text>
      </view>
    </view>

    <!-- 底部提现按钮 -->
    <view class="safe-area-inset-bottom fixed bottom-120rpx left-0 right-0 bg-white/95 p-24rpx backdrop-blur">
      <button
        class="h-96rpx w-full rounded-full from-blue-400 via-blue-500 to-purple-500 bg-gradient-to-r text-32rpx text-white font-bold shadow-lg"
        @tap="handleWithdraw"
      >
        我要提现
      </button>
    </view>

    <!-- 提现弹窗 -->
    <view v-if="showWithdrawModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <view class="mx-48rpx w-full max-w-600rpx rounded-24rpx bg-white p-32rpx">
        <view class="flex items-center justify-between">
          <text class="text-32rpx text-gray-800 font-bold">申请提现</text>
          <text class="text-48rpx text-gray-400" @tap="closeWithdrawModal">×</text>
        </view>

        <!-- 可提现余额 -->
        <view class="mt-24rpx rounded-16rpx bg-gray-50 p-24rpx">
          <text class="text-24rpx text-gray-500">可提现余额</text>
          <text class="mt-8rpx block text-40rpx text-green-500 font-bold">¥{{ Number(settlementData.mer_money).toFixed(2) }}</text>
          <text class="mt-8rpx block text-22rpx text-gray-400">手续费{{ settlementData.withdraw_rate }}%</text>
        </view>

        <!-- 提现金额 -->
        <view class="mt-24rpx">
          <text class="text-26rpx text-gray-700">提现金额</text>
          <input
            v-model="withdrawForm.extract_money"
            type="digit"
            class="mt-12rpx h-88rpx rounded-16rpx border border-gray-200 px-24rpx text-32rpx"
            placeholder="请输入提现金额"
          >
        </view>

        <!-- 提现方式 -->
        <view class="mt-24rpx">
          <text class="text-26rpx text-gray-700">提现方式</text>
          <view class="mt-12rpx flex gap-20rpx">
            <view
              v-for="method in withdrawMethods"
              :key="method.value"
              class="flex-1 rounded-16rpx border p-20rpx text-center"
              :class="withdrawForm.financial_type === method.value ? `border-${method.color}-500 bg-${method.color}-50` : 'border-gray-200'"
              @tap="withdrawForm.financial_type = method.value as 2 | 3"
            >
              <text
                class="text-26rpx"
                :class="withdrawForm.financial_type === method.value ? `text-${method.color}-500` : 'text-gray-600'"
              >{{ method.label }}</text>
            </view>
          </view>
        </view>

        <!-- 备注 -->
        <view class="mt-24rpx">
          <text class="text-26rpx text-gray-700">备注（选填）</text>
          <input
            v-model="withdrawForm.mark"
            class="mt-12rpx h-88rpx rounded-16rpx border border-gray-200 px-24rpx text-32rpx"
            placeholder="请输入备注"
          />
        </view>

        <!-- 提交按钮 -->
        <button
          class="mt-32rpx h-88rpx w-full rounded-full from-blue-400 via-blue-500 to-purple-500 bg-gradient-to-r text-32rpx text-white font-bold shadow-lg"
          :disabled="isWithdrawing"
          @tap="submitWithdraw"
        >
          {{ isWithdrawing ? '提交中...' : '提交申请' }}
        </button>
      </view>
    </view>

    <!-- 收款账户配置弹窗 -->
    <view v-if="showAccountModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <view class="mx-48rpx w-full max-w-600rpx rounded-24rpx bg-white p-32rpx">
        <view class="flex items-center justify-between">
          <text class="text-32rpx text-gray-800 font-bold">配置收款账户</text>
          <text class="text-48rpx text-gray-400" @tap="closeAccountModal">×</text>
        </view>

        <!-- 账户类型 -->
        <view class="mt-24rpx">
          <text class="text-26rpx text-gray-700">账户类型</text>
          <view class="mt-12rpx flex gap-20rpx">
            <view
              v-for="method in withdrawMethods"
              :key="method.value"
              class="flex-1 rounded-16rpx border p-20rpx text-center"
              :class="accountForm.financial_type === method.value ? `border-${method.color}-500 bg-${method.color}-50` : 'border-gray-200'"
              @tap="accountForm.financial_type = method.value as 2 | 3"
            >
              <text
                class="text-26rpx"
                :class="accountForm.financial_type === method.value ? `text-${method.color}-500` : 'text-gray-600'"
              >{{ method.label }}</text>
            </view>
          </view>
        </view>

        <!-- 收款人姓名 -->
        <view class="mt-24rpx">
          <text class="text-26rpx text-gray-700">收款人姓名</text>
          <input
            v-model="accountForm.name"
            class="mt-12rpx h-88rpx rounded-16rpx border border-gray-200 px-24rpx text-32rpx"
            placeholder="请输入收款人姓名"
          />
        </view>

        <!-- 微信信息 -->
        <view v-if="accountForm.financial_type === 2">
          <view class="mt-24rpx">
            <text class="text-26rpx text-gray-700">微信号</text>
            <input
              v-model="accountForm.wechat"
              class="mt-12rpx h-88rpx rounded-16rpx border border-gray-200 px-24rpx text-32rpx"
              placeholder="请输入微信号"
            />
          </view>
          <view class="mt-24rpx">
            <text class="text-26rpx text-gray-700">微信收款二维码</text>
            <view class="mt-12rpx">
              <!-- 已上传图片预览 -->
              <view v-if="accountForm.wechat_code" class="relative inline-block">
                <image
                  :src="qrcodePreviewUrl || getFullImageUrl(accountForm.wechat_code)"
                  mode="aspectFill"
                  class="h-240rpx w-240rpx rounded-16rpx"
                />
                <view
                  class="absolute -right-12rpx -top-12rpx flex h-40rpx w-40rpx items-center justify-center rounded-full bg-red-500"
                  @tap="handleRemoveQrcode"
                >
                  <text class="text-24rpx text-white">×</text>
                </view>
              </view>
              <!-- 上传按钮 -->
              <view
                v-else
                class="flex h-240rpx w-240rpx flex-col items-center justify-center rounded-16rpx border-2 border-dashed border-gray-300 bg-gray-50"
                @tap="handleChooseQrcode"
              >
                <text v-if="isUploadingQrcode" class="text-24rpx text-gray-400">上传中...</text>
                <template v-else>
                  <text class="i-carbon-camera text-48rpx text-gray-400" />
                  <text class="mt-8rpx text-22rpx text-gray-400">点击上传收款码</text>
                </template>
              </view>
            </view>
          </view>
        </view>

        <!-- 支付宝信息 -->
        <view v-if="accountForm.financial_type === 3">
          <view class="mt-24rpx">
            <text class="text-26rpx text-gray-700">支付宝账号</text>
            <input
              v-model="accountForm.alipay"
              class="mt-12rpx h-88rpx rounded-16rpx border border-gray-200 px-24rpx text-32rpx"
              placeholder="请输入支付宝账号"
            />
          </view>
          <view class="mt-24rpx">
            <text class="text-26rpx text-gray-700">支付宝收款二维码</text>
            <view class="mt-12rpx">
              <!-- 已上传图片预览 -->
              <view v-if="accountForm.alipay_code" class="relative inline-block">
                <image
                  :src="qrcodePreviewUrl || getFullImageUrl(accountForm.alipay_code)"
                  mode="aspectFill"
                  class="h-240rpx w-240rpx rounded-16rpx"
                />
                <view
                  class="absolute -right-12rpx -top-12rpx flex h-40rpx w-40rpx items-center justify-center rounded-full bg-red-500"
                  @tap="handleRemoveQrcode"
                >
                  <text class="text-24rpx text-white">×</text>
                </view>
              </view>
              <!-- 上传按钮 -->
              <view
                v-else
                class="flex h-240rpx w-240rpx flex-col items-center justify-center rounded-16rpx border-2 border-dashed border-gray-300 bg-gray-50"
                @tap="handleChooseQrcode"
              >
                <text v-if="isUploadingQrcode" class="text-24rpx text-gray-400">上传中...</text>
                <template v-else>
                  <text class="i-carbon-camera text-48rpx text-gray-400" />
                  <text class="mt-8rpx text-22rpx text-gray-400">点击上传收款码</text>
                </template>
              </view>
            </view>
          </view>
        </view>

        <!-- 提交按钮 -->
        <button
          class="mt-32rpx h-88rpx w-full rounded-full from-blue-400 via-blue-500 to-purple-500 bg-gradient-to-r text-32rpx text-white font-bold shadow-lg"
          :disabled="isSavingAccount"
          @tap="submitAccount"
        >
          {{ isSavingAccount ? '保存中...' : '保存' }}
        </button>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
// 底部按钮样式调整，避免被tabbar遮挡
.safe-area-inset-bottom {
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}
</style>

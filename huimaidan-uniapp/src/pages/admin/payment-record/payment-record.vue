<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { getSettlementStats, getSettlementOrders, getSettlementHourly } from '@/api/mer'
import type { ISettlementStats, ISettlementOrder, IHourlyDataResponse } from '@/api/mer'

definePage({
  style: {
    navigationBarTitleText: '买单记录',
    navigationStyle: 'custom',
  },
})

// 当前选中的标签
const activeTab = ref<'stats' | 'detail'>('detail')

// 日期筛选
type DateFilterType = 'today' | 'yesterday' | 'month' | 'custom'
const activeDateFilter = ref<DateFilterType>('today')

// 日期范围（自定义）
const dateRangeStart = ref('')
const dateRangeEnd = ref('')

// 搜索关键词
const searchKeyword = ref('')

// 加载状态
const loading = ref(false)
const statsLoading = ref(false)

// 统计数据
const statsData = ref<ISettlementStats>({
  order_count: 0,
  pay_amount: '0.00',
  merchant_cost_amount: '0.00',
  platform_profit: '0.00',
  pool_platform_profit: '0.00',
  withdraw_income_amount: '0.00',
  withdraw_fee_amount: '0.00',
})

// 今日统计数据
const todayStats = ref({
  order_count: 0,
  pay_amount: '0.00',
})

// 本月统计数据
const monthStats = ref({
  order_count: 0,
  pay_amount: '0.00',
})

// 小时级收款趋势数据
const hourlyLoading = ref(false)
const hourlyData = ref<IHourlyDataResponse | null>(null)

// 图表数据（categories 和 series）
const chartData = ref<{
  categories: string[]
  series: Array<{ name: string, data: number[] }>
}>({ categories: [], series: [] })

// 图表配置项
const chartOpts = ref({
  color: ['#ff4c3f', '#999999'],
  padding: [15, 10, 0, 10],
  dataLabel: false,
  xAxis: {
    disableGrid: true,
    fontColor: '#999999',
    fontSize: 10,
  },
  yAxis: {
    disableGrid: false,
    gridType: 'dash',
    dashLength: 4,
    gridColor: '#eeeeee',
    fontColor: '#999999',
    fontSize: 10,
    data: [{ min: 0 }],
  },
  legend: {
    show: true,
    position: 'top',
    lineHeight: 25,
    fontColor: '#999999',
    fontSize: 11,
  },
  extra: {
    line: {
      type: 'curve',
      width: 2,
      activeType: 'hollow',
    },
  },
})

// 订单列表
const orderList = ref<ISettlementOrder[]>([])

// 按日期分组的订单
const orderGroups = ref<Array<{ date: string, orders: ISettlementOrder[] }>>([])

// 分页参数
const pagination = ref({
  page: 1,
  limit: 20,
  total: 0,
})

// 日期选择器相关
const showDatePicker = ref(false)
const datePickerType = ref<'start' | 'end'>('start')
const datePickerValue = ref('')

// 计算日期范围显示文本
const dateRangeText = computed(() => {
  if (dateRangeStart.value && dateRangeEnd.value) {
    return `${dateRangeStart.value} - ${dateRangeEnd.value}`
  }
  return '自定义日期'
})

// 获取日期字符串
function getDateString(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// 获取今日日期
function getToday(): string {
  return getDateString(new Date())
}

// 获取昨日日期
function getYesterday(): string {
  const date = new Date()
  date.setDate(date.getDate() - 1)
  return getDateString(date)
}

// 加载统计数据
async function loadStats() {
  statsLoading.value = true
  try {
    // 获取今日统计 - 使用 'today' 关键字
    const todayRes = await getSettlementStats({ date: 'today' })
    if (todayRes) {
      todayStats.value = {
        order_count: todayRes.order_count || 0,
        pay_amount: todayRes.pay_amount || '0.00',
      }
    }

    // 获取本月统计 - 使用 'month' 关键字
    const monthRes = await getSettlementStats({ date: 'month' })
    if (monthRes) {
      monthStats.value = {
        order_count: monthRes.order_count || 0,
        pay_amount: monthRes.pay_amount || '0.00',
      }
    }
  }
  catch (error) {
    console.error('加载统计数据失败:', error)
    uni.showToast({ title: '加载统计数据失败', icon: 'none' })
  }
  finally {
    statsLoading.value = false
  }
}

// 加载小时级收款趋势数据
async function loadHourlyData() {
  hourlyLoading.value = true
  try {
    const res = await getSettlementHourly({ date: 'today' })
    if (res && res.list && res.list.length > 0) {
      hourlyData.value = res
      // 展示全部24小时数据，X轴每隔4小时显示一个标签
      const categories = res.list.map((item) => {
        // 仅 0,4,8,12,16,20 显示标签，其余为空字符串
        return item.hour % 4 === 0 ? item.hour_label : ''
      })

      const amountData = res.list.map(item => parseFloat(item.pay_amount) || 0)
      const countData = res.list.map(item => item.order_count)

      chartData.value = {
        categories,
        series: [
          { name: '收款金额', data: amountData },
          { name: '收款笔数', data: countData },
        ],
      }
    }
  }
  catch (error) {
    console.error('加载收款趋势数据失败:', error)
  }
  finally {
    hourlyLoading.value = false
  }
}

// 加载订单列表
async function loadOrders(reset = false) {
  if (loading.value) return

  if (reset) {
    pagination.value.page = 1
    orderList.value = []
    orderGroups.value = []
  }

  loading.value = true
  try {
    const params: Record<string, any> = {
      page: pagination.value.page,
      limit: pagination.value.limit,
      paid: 1, // 只查询已支付的订单
    }

    // 日期筛选 - 使用后端支持的关键字格式
    if (activeDateFilter.value === 'today') {
      params.date = 'today'
    }
    else if (activeDateFilter.value === 'yesterday') {
      params.date = 'yesterday'
    }
    else if (activeDateFilter.value === 'month') {
      params.date = 'month'
    }
    else if (activeDateFilter.value === 'custom' && dateRangeStart.value && dateRangeEnd.value) {
      // 当开始和结束日期相同时，使用单个日期字符串
      if (dateRangeStart.value === dateRangeEnd.value) {
        params.date = dateRangeStart.value
      }
      else {
        // 使用逗号分隔日期范围，避免与日期中的 '-' 冲突
        params.date = `${dateRangeStart.value},${dateRangeEnd.value}`
      }
    }

    // 关键词搜索
    if (searchKeyword.value.trim()) {
      params.order_sn = searchKeyword.value.trim()
    }

    const res = await getSettlementOrders(params)
    if (res) {
      pagination.value.total = res.count || 0

      if (reset) {
        orderList.value = res.list || []
      }
      else {
        orderList.value = [...orderList.value, ...(res.list || [])]
      }

      // 按日期分组
      groupOrdersByDate()
    }
  }
  catch (error) {
    console.error('加载订单列表失败:', error)
    uni.showToast({ title: '加载订单列表失败', icon: 'none' })
  }
  finally {
    loading.value = false
  }
}

// 将订单按日期分组
function groupOrdersByDate() {
  const groups: Record<string, ISettlementOrder[]> = {}

  for (const order of orderList.value) {
    const date = order.pay_time ? order.pay_time.split(' ')[0] : order.create_time?.split(' ')[0] || '未知日期'
    if (!groups[date]) {
      groups[date] = []
    }
    groups[date].push(order)
  }

  // 转换为数组并按日期降序排序
  orderGroups.value = Object.entries(groups)
    .map(([date, orders]) => ({ date, orders }))
    .sort((a, b) => b.date.localeCompare(a.date))
}

// 切换标签
function handleTabChange(tab: 'stats' | 'detail') {
  activeTab.value = tab
  if (tab === 'stats' && statsData.value.order_count === 0) {
    loadStats()
  }
}

// 切换日期筛选
function handleDateFilter(type: DateFilterType) {
  activeDateFilter.value = type
  loadOrders(true)
}

// 打开开始日期选择器
function handleOpenStartDatePicker() {
  datePickerType.value = 'start'
  datePickerValue.value = dateRangeStart.value || getToday()
  showDatePicker.value = true
}

// 打开结束日期选择器
function handleOpenEndDatePicker() {
  datePickerType.value = 'end'
  datePickerValue.value = dateRangeEnd.value || getToday()
  showDatePicker.value = true
}

// 日期选择器确认
function handleDatePickerChange(e: any) {
  const value = e.detail.value
  if (datePickerType.value === 'start') {
    dateRangeStart.value = value
  }
  else {
    dateRangeEnd.value = value
  }

  // 如果两个日期都选择了，自动查询
  if (dateRangeStart.value && dateRangeEnd.value) {
    activeDateFilter.value = 'custom'
    loadOrders(true)
  }
}

// 关闭日期选择器
function handleCloseDatePicker() {
  showDatePicker.value = false
}

// 搜索
function handleSearch() {
  loadOrders(true)
}

// 加载更多
function handleLoadMore() {
  if (orderList.value.length < pagination.value.total) {
    pagination.value.page++
    loadOrders(false)
  }
}

// 返回上一页
function handleBack() {
  uni.navigateBack()
}

// 格式化金额显示
function formatAmount(amount: string | number): string {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  return isNaN(num) ? '0.00' : num.toFixed(2)
}

// 获取支付方式文本
function getPayMethodText(payType: number | string): string {
  const typeMap: Record<string, string> = {
    '1': '余额支付',
    '2': '微信支付',
    '3': '支付宝支付',
    'weixin': '微信支付',
    'alipay': '支付宝支付',
    'balance': '余额支付',
  }
  return typeMap[String(payType)] || '其他支付'
}

// 页面加载时获取数据
onMounted(() => {
  loadStats()
  loadHourlyData()
  loadOrders(true)
})
</script>

<template>
  <view class="payment-record-page">
    <!-- 自定义导航栏 -->
    <view class="custom-nav">
      <view class="nav-status-bar" />
      <view class="nav-content">
        <view class="nav-back" @tap="handleBack">
          <text class="i-carbon-arrow-left text-36rpx text-gray-800" />
        </view>
        <text class="nav-title">买单记录</text>
        <view class="nav-placeholder" />
      </view>
    </view>

    <!-- 标签切换 -->
    <view class="tab-bar">
      <view
        class="tab-item"
        :class="{ 'tab-item--active': activeTab === 'stats' }"
        @tap="handleTabChange('stats')"
      >
        <text class="tab-text" :class="{ 'tab-text--active': activeTab === 'stats' }">买单统计</text>
      </view>
      <view
        class="tab-item"
        :class="{ 'tab-item--active': activeTab === 'detail' }"
        @tap="handleTabChange('detail')"
      >
        <text class="tab-text" :class="{ 'tab-text--active': activeTab === 'detail' }">买单明细</text>
        <view v-if="activeTab === 'detail'" class="tab-indicator" />
      </view>
    </view>

    <!-- 买单统计内容 -->
    <view v-if="activeTab === 'stats'" class="stats-content">
      <view class="stats-card">
        <view v-if="statsLoading" class="stats-loading">
          <text class="loading-text">加载中...</text>
        </view>
        <template v-else>
          <view class="stats-row">
            <view class="stats-item">
              <text class="stats-value">{{ todayStats.order_count }}</text>
              <text class="stats-label">今日订单</text>
            </view>
            <view class="stats-item">
              <text class="stats-value">¥{{ formatAmount(todayStats.pay_amount) }}</text>
              <text class="stats-label">今日收款</text>
            </view>
          </view>
          <view class="stats-divider" />
          <view class="stats-row">
            <view class="stats-item">
              <text class="stats-value">{{ monthStats.order_count }}</text>
              <text class="stats-label">本月订单</text>
            </view>
            <view class="stats-item">
              <text class="stats-value">¥{{ formatAmount(monthStats.pay_amount) }}</text>
              <text class="stats-label">本月收款</text>
            </view>
          </view>
        </template>
      </view>

      <!-- 收款趋势图表 -->
      <view class="trend-card">
        <view class="trend-header">
          <view class="trend-title-bar" />
          <text class="trend-title">收款趋势</text>
        </view>
        <view v-if="hourlyLoading" class="trend-loading">
          <text class="loading-text">加载中...</text>
        </view>
        <view v-else-if="chartData.categories.length > 0" class="trend-chart-wrap">
          <qiun-data-charts
            type="line"
            :chart-data="chartData"
            :opts="chartOpts"
            :animation="true"
            :tooltip-show="true"
            :ontap="false"
            :ontouch="false"
          />
        </view>
        <view v-else class="trend-empty">
          <text class="empty-text">暂无数据</text>
        </view>
      </view>
    </view>

    <!-- 买单明细内容 -->
    <view v-if="activeTab === 'detail'" class="detail-content">
      <!-- 日期筛选按钮 -->
      <view class="date-filter-bar">
        <view
          class="date-filter-btn"
          :class="{ 'date-filter-btn--active': activeDateFilter === 'today' }"
          @tap="handleDateFilter('today')"
        >
          <text class="date-filter-text" :class="{ 'date-filter-text--active': activeDateFilter === 'today' }">今天</text>
        </view>
        <view
          class="date-filter-btn"
          :class="{ 'date-filter-btn--active': activeDateFilter === 'yesterday' }"
          @tap="handleDateFilter('yesterday')"
        >
          <text class="date-filter-text" :class="{ 'date-filter-text--active': activeDateFilter === 'yesterday' }">昨天</text>
        </view>
        <view
          class="date-filter-btn"
          :class="{ 'date-filter-btn--active': activeDateFilter === 'month' }"
          @tap="handleDateFilter('month')"
        >
          <text class="date-filter-text" :class="{ 'date-filter-text--active': activeDateFilter === 'month' }">本月</text>
        </view>
        <view
          class="date-range-btn"
          :class="{ 'date-range-btn--active': activeDateFilter === 'custom' }"
          @tap="handleOpenStartDatePicker"
        >
          <text class="date-range-text">{{ dateRangeText }}</text>
          <view class="date-range-icon">
            <text class="i-carbon-calendar text-20rpx text-blue-500" />
          </view>
        </view>
      </view>

      <!-- 日期选择器弹窗 -->
      <view v-if="showDatePicker" class="date-picker-mask" @tap="handleCloseDatePicker">
        <view class="date-picker-popup" @tap.stop>
          <view class="date-picker-header">
            <text class="date-picker-title">{{ datePickerType === 'start' ? '选择开始日期' : '选择结束日期' }}</text>
            <text class="date-picker-close" @tap="handleCloseDatePicker">关闭</text>
          </view>
          <picker
            mode="date"
            :value="datePickerValue"
            @change="handleDatePickerChange"
          >
            <view class="date-picker-content">
              <text class="date-picker-value">{{ datePickerValue || '请选择日期' }}</text>
            </view>
          </picker>
          <view class="date-picker-actions">
            <view class="date-picker-btn" @tap="handleOpenStartDatePicker">
              <text :class="{ 'date-picker-btn--active': datePickerType === 'start' }">开始日期</text>
            </view>
            <view class="date-picker-btn" @tap="handleOpenEndDatePicker">
              <text :class="{ 'date-picker-btn--active': datePickerType === 'end' }">结束日期</text>
            </view>
          </view>
        </view>
      </view>

      <!-- 搜索框（已隐藏） -->
      <!--
      <view class="search-bar">
        <view class="search-input-wrapper">
          <text class="i-carbon-search search-icon" />
          <input
            v-model="searchKeyword"
            class="search-input"
            placeholder="输入订单号搜索"
            placeholder-class="search-placeholder"
            @confirm="handleSearch"
          />
        </view>
      </view>
      -->

      <!-- 加载状态 -->
      <view v-if="loading && orderGroups.length === 0" class="loading-container">
        <text class="loading-text">加载中...</text>
      </view>

      <!-- 空状态 -->
      <view v-else-if="!loading && orderGroups.length === 0" class="empty-container">
        <text class="empty-text">暂无买单记录</text>
      </view>

      <!-- 订单列表 -->
      <view v-else class="order-list">
        <view v-for="group in orderGroups" :key="group.date" class="order-group">
          <!-- 日期标题 -->
          <view class="group-date">
            <text class="group-date-text">{{ group.date }}</text>
          </view>

          <!-- 订单卡片 -->
          <view v-for="order in group.orders" :key="order.order_id" class="order-card">
            <view class="order-info">
              <view class="order-row">
                <text class="order-label">支付会员:</text>
                <text class="order-value">{{ order.user?.nickname || '微信用户' }}</text>
              </view>
              <view class="order-row">
                <text class="order-label">支付时间:</text>
                <text class="order-value">{{ order.pay_time || order.create_time }}</text>
              </view>
              <view class="order-row">
                <text class="order-label">支付方式:</text>
                <text class="order-value">{{ getPayMethodText(order.settlement_mode) }}</text>
              </view>
              <view class="order-row">
                <text class="order-label">订单编号:</text>
                <text class="order-value order-value--code">{{ order.order_sn }}</text>
              </view>
            </view>
            <view class="order-amount-wrapper">
              <text class="order-amount">¥{{ formatAmount(order.pay_price) }}</text>
              <text class="i-carbon-chevron-right order-arrow" />
            </view>
          </view>
        </view>

        <!-- 加载更多 -->
        <view v-if="orderList.length < pagination.total" class="load-more" @tap="handleLoadMore">
          <text class="load-more-text">{{ loading ? '加载中...' : '加载更多' }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.payment-record-page {
  min-height: 100vh;
  background: linear-gradient(
    180deg,
    rgba(241, 244, 255, 0.96) 0%,
    rgba(247, 242, 255, 0.92) 42%,
    rgba(238, 242, 255, 0.98) 100%
  );
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
.tab-bar {
  display: flex;
  background: #fff;
  padding: 0 48rpx;
  margin-top: 16rpx;

  .tab-item {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24rpx 0;
  }

  .tab-text {
    font-size: 28rpx;
    color: #999;

    &--active {
      color: #333;
      font-weight: 600;
    }
  }

  .tab-indicator {
    position: absolute;
    bottom: 0;
    width: 48rpx;
    height: 6rpx;
    border-radius: 3rpx;
    background: #4a6cf7;
  }
}

/* 买单统计内容 */
.stats-content {
  padding: 24rpx;

  .stats-card {
    background: #fff;
    border-radius: 24rpx;
    padding: 32rpx;
    box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
  }

  .stats-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200rpx;
  }

  .stats-row {
    display: flex;
    justify-content: space-around;
    padding: 24rpx 0;
  }

  .stats-item {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stats-value {
    font-size: 40rpx;
    font-weight: 700;
    color: #333;
  }

  .stats-label {
    font-size: 24rpx;
    color: #999;
    margin-top: 12rpx;
  }

  .stats-divider {
    height: 1rpx;
    background: #eee;
    margin: 0 24rpx;
  }
}

/* 收款趋势卡片 */
.trend-card {
  background: #fff;
  border-radius: 24rpx;
  padding: 32rpx;
  margin-top: 24rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.trend-header {
  display: flex;
  align-items: center;
  margin-bottom: 24rpx;
}

.trend-title-bar {
  width: 6rpx;
  height: 28rpx;
  border-radius: 3rpx;
  background: #ff4c3f;
  margin-right: 12rpx;
}

.trend-title {
  font-size: 30rpx;
  font-weight: 600;
  color: #333;
}

.trend-loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 400rpx;
}

.trend-chart-wrap {
  width: 100%;
  height: 400rpx;
}

.trend-empty {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 400rpx;
}

/* 买单明细内容 */
.detail-content {
  padding: 24rpx;
}

/* 日期筛选栏 */
.date-filter-bar {
  display: flex;
  align-items: center;
  gap: 16rpx;
  margin-bottom: 24rpx;
}

.date-filter-btn {
  padding: 12rpx 24rpx;
  border-radius: 32rpx;
  background: #fff;

  &--active {
    background: #4a6cf7;
  }
}

.date-filter-text {
  font-size: 26rpx;
  color: #666;

  &--active {
    color: #fff;
  }
}

.date-range-btn {
  display: flex;
  align-items: center;
  padding: 12rpx 16rpx;
  border-radius: 32rpx;
  background: #fff;
  border: 2rpx solid #eee;

  &--active {
    border-color: #4a6cf7;
  }
}

.date-range-text {
  font-size: 24rpx;
  color: #666;
}

.date-range-icon {
  width: 36rpx;
  height: 36rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 8rpx;
  border-radius: 50%;
  background: #f0f5ff;
}

/* 日期选择器弹窗 */
.date-picker-mask {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.date-picker-popup {
  width: 100%;
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  padding: 32rpx;
}

.date-picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32rpx;
}

.date-picker-title {
  font-size: 32rpx;
  font-weight: 600;
  color: #333;
}

.date-picker-close {
  font-size: 28rpx;
  color: #999;
}

.date-picker-content {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80rpx;
  background: #f5f7fa;
  border-radius: 16rpx;
  margin-bottom: 32rpx;
}

.date-picker-value {
  font-size: 30rpx;
  color: #333;
}

.date-picker-actions {
  display: flex;
  justify-content: center;
  gap: 32rpx;
}

.date-picker-btn {
  padding: 16rpx 32rpx;
  border-radius: 32rpx;
  background: #f5f7fa;

  text {
    font-size: 28rpx;
    color: #666;
  }

  .date-picker-btn--active {
    color: #4a6cf7;
    font-weight: 600;
  }
}

/* 搜索栏 */
.search-bar {
  margin-bottom: 24rpx;

  .search-input-wrapper {
    display: flex;
    align-items: center;
    height: 72rpx;
    padding: 0 24rpx;
    background: #fff;
    border-radius: 36rpx;
  }

  .search-icon {
    font-size: 28rpx;
    color: #ccc;
    margin-right: 12rpx;
  }

  .search-input {
    flex: 1;
    font-size: 26rpx;
    color: #333;
  }

  .search-placeholder {
    color: #ccc;
    font-size: 26rpx;
  }
}

/* 加载和空状态 */
.loading-container,
.empty-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300rpx;
}

.loading-text,
.empty-text {
  font-size: 28rpx;
  color: #999;
}

/* 订单列表 */
.order-list {
  .order-group {
    margin-bottom: 32rpx;
  }

  .group-date {
    margin-bottom: 16rpx;
  }

  .group-date-text {
    font-size: 30rpx;
    font-weight: 700;
    color: #333;
  }

  .order-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 16rpx;
    padding: 24rpx;
    margin-bottom: 16rpx;
    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
  }

  .order-info {
    flex: 1;
  }

  .order-row {
    display: flex;
    align-items: center;
    margin-bottom: 8rpx;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .order-label {
    font-size: 24rpx;
    color: #999;
    width: 140rpx;
  }

  .order-value {
    font-size: 24rpx;
    color: #333;

    &--code {
      font-size: 22rpx;
      color: #666;
    }
  }

  .order-amount-wrapper {
    display: flex;
    align-items: center;
    margin-left: 24rpx;
  }

  .order-amount {
    font-size: 32rpx;
    font-weight: 700;
    color: #ff4c3f;
    margin-right: 8rpx;
  }

  .order-arrow {
    font-size: 20rpx;
    color: #ccc;
  }

  .load-more {
    display: flex;
    justify-content: center;
    padding: 24rpx 0;
  }

  .load-more-text {
    font-size: 26rpx;
    color: #4a6cf7;
  }
}
</style>

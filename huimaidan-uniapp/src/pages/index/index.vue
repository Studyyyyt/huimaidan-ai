<script lang="ts" setup>
import type { IFilterItem, ILbsGeocoderResult, IStoreGroupTreeNode, IStoreItem } from '@/api/huimaidan'
import { getAiOnboardingConfig } from '@/api/ai'
import { getLbsAddress, getLbsGeocoder, getStoreFilters, getStoreGroupOptions, getStoreList } from '@/api/huimaidan'
import { bindSpread } from '@/api/login'
import AiRobotBanner from '@/components/AiRobotBanner.vue'
import TopLocationPicker from '@/components/TopLocationPicker.vue'
import allCategoryWordmarkIcon from '@/static/images/all-category-wordmark-icon-no-arrow.png'
import buyBtnIcon from '@/static/webp/tabbar/btn_yhmd.webp'
import aiPickIcon from '@/static/webp/tabbar/icon_zx.webp'
import { useLocationStore, useTokenStore } from '@/store'
import { clearPendingSpreadUid, normalizeSpreadUid, setPendingSpreadUid } from '@/utils/invite-spread'
import { getSafeAreaInsets, getSystemInfo } from '@/utils/systemInfo'
import { getWechatUserLocation } from '@/utils/wechat-location'
import {
  buildCategoryPanelItems,
  buildStickySearchBarLayout,
  getCategoryPanelAllTargetId,
} from './index.helpers'

defineOptions({
  name: 'Home',
})
definePage({
  type: 'home',
  style: {
    'navigationStyle': 'custom',
    'navigationBarTitleText': '首页',
    'enablePullDownRefresh': true,
    'mp-alipay': {
      defaultTitle: '首页',
      transparentTitle: 'always',
      titlePenetrate: 'YES',
      titleBarColor: '#ffffff',
    },
  },
})

// 顶部栏状态
const isScrolled = ref(false)
const searchKeyword = ref('')
const isLocating = ref(false)
const aiHomeSubtitle = ref('告诉我你的需求，\n我会给你适合的建议')
const aiSearchPlaceholder = ref('附近，均80，有小孩，可停车')
const aiFeaturedSubtitle = ref('根据您的需求和实时情况，为您推荐')

// 使用位置 store
const locationStore = useLocationStore()

const selectedRegion = computed<[string, string, string]>(() => [
  locationStore.province || '',
  locationStore.city || '',
  locationStore.district || '',
])

// 筛选配置（从后端获取）
const filterConfig = ref<IFilterItem[]>([])

// 筛选条件数据（从 filterConfig 解析）
const filterData = ref({
  distance: [] as Array<{ id: number | string, name: string, value: number }>,
  category: [] as Array<{ id: number | string, name: string, value: number }>,
  sort: [] as Array<{ id: number | string, name: string, value: string }>,
})

// 当前选中的筛选条件
const selectedFilters = ref({
  distance: '' as string | number,
  categoryId: '' as string | number,
  sortBy: 'default' as string,
})

// 商户列表
const shopList = ref<IStoreItem[]>([])
const totalCount = ref(0)
const currentPage = ref(1)
const isLoading = ref(false)
const isLastPage = ref(false)

// 分类标签（一级分类）
const categories = ref<Array<{ id: number | string, name: string }>>([
  { id: 'all', name: '推荐' },
])

// 当前选中的分类
const activeCategory = ref<string | number>('all')
const isCategoryPanelOpen = ref(false)

// 店铺分组树形数据
const storeGroupTree = ref<IStoreGroupTreeNode[]>([])
// 当前展开的分类列表（支持多级）
const currentSubCategories = ref<Array<{ store_group_id: number, name: string, children?: any[] }>>([])
// 当前选中的二级分类ID
const activeSubCategory = ref<number | null>(null)
// 分类面包屑路径（记录选中的分类路径）
const categoryBreadcrumb = ref<Array<{ store_group_id: number, name: string }>>([])

// 各筛选项的"全部"占位项（用于在 picker 列表头部清除当前筛选）
const FILTER_ALL_LABEL = '全部'

const distanceOptions = computed(() => [
  { id: 'all', name: FILTER_ALL_LABEL, value: 0 },
  ...filterData.value.distance,
])
const sortOptions = computed(() => [
  { id: 'all', name: FILTER_ALL_LABEL, value: 'default' },
  ...filterData.value.sort,
])

const selectedDistanceName = computed(() => {
  return filterData.value.distance.find(item => item.value === selectedFilters.value.distance)?.name || FILTER_ALL_LABEL
})

const selectedSortName = computed(() => {
  return filterData.value.sort.find(item => item.value === selectedFilters.value.sortBy)?.name || FILTER_ALL_LABEL
})

// 判断筛选条件是否已激活（非默认值），用于 UI 高亮
const isDistanceActive = computed(() => !!selectedFilters.value.distance)
const isSortActive = computed(() => !!selectedFilters.value.sortBy && selectedFilters.value.sortBy !== 'default')

// 当前选中的分类名称
const selectedCategoryName = computed(() => {
  if (!activeCategory.value || activeCategory.value === 'all') {
    return '全部分类'
  }
  // 先在树形数据中查找（支持多级）
  const found = findCategoryInTree(storeGroupTree.value, activeCategory.value as number)
  return (found?.label ?? found?.name) || '全部分类'
})

const categoryPanelAllTargetId = computed(() => getCategoryPanelAllTargetId(
  storeGroupTree.value,
  activeCategory.value,
))
const isCategoryPanelAllActive = computed(() => String(activeCategory.value) === String(categoryPanelAllTargetId.value))

const stickySearchBarLayout = computed(() => {
  let menuButtonLeft: number | undefined

  // #ifdef MP-WEIXIN
  if (typeof uni.getMenuButtonBoundingClientRect === 'function') {
    menuButtonLeft = uni.getMenuButtonBoundingClientRect().left
  }
  // #endif

  const sysInfo = getSystemInfo()
  const safeArea = getSafeAreaInsets()

  return buildStickySearchBarLayout({
    statusBarHeight: Number(sysInfo?.statusBarHeight || 0),
    safeAreaRight: Number(safeArea?.right || 0),
    windowWidth: Number(sysInfo?.windowWidth || 0),
    menuButtonLeft,
  })
})

const stickySearchBarStyle = computed(() => {
  const layout = stickySearchBarLayout.value
  return {
    paddingTop: `${layout.paddingTop}px`,
    paddingRight: `${layout.paddingRight}px`,
    paddingBottom: `${layout.paddingBottom}px`,
    paddingLeft: `${layout.paddingLeft}px`,
  }
})

function resolveInviteSpreadUid(options: Record<string, any> | undefined) {
  const rawSpread = options?.spread ?? options?.spid
  if (rawSpread === undefined || rawSpread === '') {
    return 0
  }

  const spreadUid = normalizeSpreadUid(rawSpread)
  if (!spreadUid) {
    throw new Error('邀请参数错误')
  }
  return spreadUid
}

async function consumeInviteOptions(options: Record<string, any> | undefined) {
  try {
    const spreadUid = resolveInviteSpreadUid(options)
    if (!spreadUid) {
      return
    }

    setPendingSpreadUid(spreadUid)
    const tokenStore = useTokenStore()
    await tokenStore.loginReady
    const token = await tokenStore.tryGetValidToken()
    if (!token) {
      return
    }

    await bindSpread({ spread_spid: spreadUid })
    clearPendingSpreadUid()
    uni.showToast({
      title: '邀请关系已记录',
      icon: 'none',
    })
  }
  catch (error) {
    console.error('首页邀请参数处理失败:', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '邀请参数处理失败',
      icon: 'none',
    })
  }
}

// 页面加载
onLoad(async (options) => {
  consumeInviteOptions(options)

  // 等待自动登录完成后再加载需要登录态的数据
  const tokenStore = useTokenStore()
  await tokenStore.loginReady

  void fetchFilters()
  void fetchCategories()
  void fetchAiHomeCopy()
  try {
    await refreshCurrentLocation()
  }
  catch (error) {
    console.error('首页定位失败:', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '获取当前位置失败',
      icon: 'none',
    })
  }
  fetchShopList()
})

async function fetchAiHomeCopy() {
  try {
    const config = await getAiOnboardingConfig()
    aiHomeSubtitle.value = config.home_subtitle || aiHomeSubtitle.value
    aiSearchPlaceholder.value = config.home_search_placeholder || aiSearchPlaceholder.value
    aiFeaturedSubtitle.value = config.home_featured_subtitle || aiFeaturedSubtitle.value
  }
  catch (error) {
    console.error('获取 AI 首页文案失败:', error)
  }
}

// 下拉刷新
onPullDownRefresh(() => {
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList().finally(() => {
    uni.stopPullDownRefresh()
  })
})

// 上拉加载更多
onReachBottom(() => {
  if (!isLastPage.value && !isLoading.value) {
    fetchShopList(true)
  }
})

// 获取筛选配置
async function fetchFilters() {
  try {
    const res = await getStoreFilters()
    if (res && Array.isArray(res)) {
      filterConfig.value = res
      res.forEach((item) => {
        if (item.key === 'distance') {
          filterData.value.distance = (item.options || []).map(o => ({ id: o.id, name: o.name, value: o.value as number }))
        }
        else if (item.key === 'category') {
          filterData.value.category = (item.options || []).map(o => ({ id: o.id, name: o.name, value: o.value as number }))
        }
        else if (item.key === 'sort') {
          filterData.value.sort = (item.options || []).map(o => ({ id: o.id, name: o.name, value: o.value as string }))
        }
      })
    }
  }
  catch (error) {
    console.error('获取筛选条件失败:', error)
    uni.showToast({ title: '获取筛选条件失败', icon: 'none' })
  }
}

// 获取商户分类（使用级联选项接口）
async function fetchCategories() {
  try {
    const res = await getStoreGroupOptions()
    if (res && Array.isArray(res)) {
      storeGroupTree.value = res
      // 将一级分类转换为横向标签格式（formatCascaderData 返回 value/label 字段）
      const items = res.map(c => ({ id: c.value ?? c.store_group_id, name: c.label ?? c.name }))
      categories.value = [{ id: 'all', name: '推荐' }, ...items]
    }
  }
  catch (error) {
    console.error('获取商户分类失败:', error)
    uni.showToast({ title: '获取商户分类失败', icon: 'none' })
  }
}

// 获取商户列表
async function fetchShopList(append = false) {
  if (isLoading.value)
    return
  isLoading.value = true
  try {
    const params: Record<string, any> = {
      page: currentPage.value,
      limit: 15,
    }
    // 搜索关键词
    if (searchKeyword.value) {
      params.keyword = searchKeyword.value
    }
    // 分类筛选（使用店铺分组ID）
    if (activeCategory.value && activeCategory.value !== 'all') {
      params.store_group_id = activeCategory.value
    }
    // 距离筛选
    if (selectedFilters.value.distance) {
      params.distance = selectedFilters.value.distance
    }
    // 排序
    if (selectedFilters.value.sortBy) {
      params.order = selectedFilters.value.sortBy
    }
    // 只要已有定位坐标就传给后端，用于普通列表持续展示店铺距离。
    if (locationStore.hasCoordinates) {
      params.latitude = locationStore.latitude
      params.longitude = locationStore.longitude
    }

    const res = await getStoreList(params as any)
    if (res) {
      totalCount.value = res.count || 0
      if (append) {
        shopList.value = [...shopList.value, ...(res.list || [])]
      }
      else {
        shopList.value = res.list || []
      }
      isLastPage.value = (res.list || []).length < 15
    }
  }
  catch (error) {
    console.error('获取商户列表失败:', error)
    uni.showToast({ title: '获取商户列表失败', icon: 'none' })
  }
  finally {
    isLoading.value = false
  }
}

function resetAndFetchShopList() {
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

function normalizeRegionValue(value: unknown): [string, string, string] {
  const region = Array.isArray(value) ? value : []
  return [
    String(region[0] || ''),
    String(region[1] || ''),
    String(region[2] || ''),
  ]
}

function regionAddressText(region: string[]) {
  return region.filter(Boolean).join(' ')
}

function geocoderAddressText(result: ILbsGeocoderResult) {
  return result.formatted_addresses?.recommend
    || result.formatted_addresses?.rough
    || result.address
    || ''
}

function applyGeocoderLocation(result: ILbsGeocoderResult) {
  const component = result.address_component
  const exactAddress = geocoderAddressText(result)
  if (!component && !exactAddress) {
    throw new Error('定位地址解析失败：后端未返回地址')
  }

  locationStore.setLocation(
    component?.province || '',
    component?.city || '',
    component?.district || '',
    exactAddress,
  )
}

function applyAddressLocation(region: [string, string, string], result: ILbsGeocoderResult) {
  const [province, city, district] = region
  const latitude = Number(result.location?.lat)
  const longitude = Number(result.location?.lng)

  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    throw new TypeError('省市区位置解析失败：后端未返回坐标')
  }

  locationStore.setLocation(province, city, district, regionAddressText(region))
  locationStore.setCoordinates(latitude, longitude)
}

async function refreshCurrentLocation() {
  const location = await getWechatUserLocation()
  locationStore.setCoordinates(location.latitude, location.longitude)
  const geocoder = await getLbsGeocoder({
    location: `${location.latitude},${location.longitude}`,
  })
  applyGeocoderLocation(geocoder)
}

async function ensureCoordinates() {
  if (locationStore.hasCoordinates) {
    return
  }

  await refreshCurrentLocation()
}

async function handleRegionChange(e: any) {
  const region = normalizeRegionValue(e.detail?.value)
  const [province, city, district] = region
  const address = regionAddressText(region)
  if (!address) {
    return
  }

  locationStore.setLocation(province, city, district, address)
  locationStore.clearCoordinates()

  try {
    const geocoder = await getLbsAddress({
      region: city || province,
      address,
    })
    applyAddressLocation(region, geocoder)
    resetAndFetchShopList()
  }
  catch (error) {
    console.error('省市区位置解析失败:', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '省市区位置解析失败',
      icon: 'none',
    })
    resetAndFetchShopList()
  }
}

async function handleLocateTap() {
  if (isLocating.value) {
    return
  }

  isLocating.value = true
  try {
    await refreshCurrentLocation()
    resetAndFetchShopList()
  }
  catch (error) {
    console.error('刷新当前位置失败:', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '获取当前位置失败',
      icon: 'none',
    })
    if (locationStore.hasCoordinates) {
      resetAndFetchShopList()
    }
  }
  finally {
    isLocating.value = false
  }
}

async function handleDistanceFilterChange(e: any) {
  const index = Number(e.detail.value)
  const option = distanceOptions.value[index]
  if (!option) {
    return
  }

  // 选择"全部"：清除距离筛选
  if (option.id === 'all') {
    selectedFilters.value.distance = ''
    resetAndFetchShopList()
    return
  }

  try {
    await ensureCoordinates()
    selectedFilters.value.distance = option.value
    resetAndFetchShopList()
  }
  catch {
    uni.showToast({ title: '请授权定位后使用距离筛选', icon: 'none' })
  }
}

async function handleSortFilterChange(e: any) {
  const index = Number(e.detail.value)
  const option = sortOptions.value[index]
  if (!option) {
    return
  }

  // 选择"全部"：清除排序（恢复默认排序）
  if (option.id === 'all') {
    selectedFilters.value.sortBy = 'default'
    resetAndFetchShopList()
    return
  }

  if (option.value === 'location') {
    try {
      await ensureCoordinates()
    }
    catch {
      uni.showToast({ title: '请授权定位后按距离排序', icon: 'none' })
      return
    }
  }

  selectedFilters.value.sortBy = option.value
  resetAndFetchShopList()
}

// 监听页面滚动
onPageScroll((e) => {
  isScrolled.value = (e.scrollTop || 0) > 100
})

// 点击搜索
function handleSearchTap() {
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

// 点击消息
function handleMessageTap() {
  uni.showToast({ title: '消息功能暂未接入后端', icon: 'none' })
}

// 点击会员中心
function handleMemberTap() {
  uni.switchTab({ url: '/pages/me/me' })
}

// AI助手入口（点击搜索框或按钮进入 AI 对话页）
function handleAiSearchTap() {
  uni.navigateTo({ url: '/pages/ai-chat/index' })
}

// 点击 AI 机器人或智能 Banner 跳转 AI 对话页
function handleAiTap() {
  uni.navigateTo({
    url: '/pages/ai-chat/index',
  })
}

function scrollFilterToTop(): void {
  uni.pageScrollTo({
    selector: '#homeFilterAnchor',
    duration: 160,
    fail: (error: UniApp.GeneralCallbackResult) => {
      console.error('筛选栏滚动定位失败', error)
    },
  })
}

function scheduleFilterScrollToTop(): void {
  void nextTick(() => {
    scrollFilterToTop()
  })
}

// 切换分类
function handleCategoryTap(id: string | number) {
  activeCategory.value = id
  activeSubCategory.value = null
  categoryBreadcrumb.value = [] // 清空面包屑
  isCategoryPanelOpen.value = false
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

async function handleAllCategoryTap() {
  const nextOpenState = !isCategoryPanelOpen.value

  if (!nextOpenState) {
    // 收起面板
    isCategoryPanelOpen.value = false
    return
  }

  // 如果树形数据为空，先加载
  if (storeGroupTree.value.length === 0) {
    await fetchCategories()
  }

  // 清空面包屑路径，从根级别开始
  categoryBreadcrumb.value = []
  updateSubCategories()
  isCategoryPanelOpen.value = true
  scheduleFilterScrollToTop()
}

function updateSubCategories() {
  currentSubCategories.value = buildCategoryPanelItems(storeGroupTree.value, activeCategory.value)
}

// 在树形数据中递归查找分类
function findCategoryInTree(tree: IStoreGroupTreeNode[], id: number): IStoreGroupTreeNode | null {
  for (const item of tree) {
    if ((item.value ?? item.store_group_id) === id) {
      return item
    }
    if (item.children && item.children.length > 0) {
      const found = findCategoryInTree(item.children, id)
      if (found)
        return found
    }
  }
  return null
}

function handleSubCategoryTap(item: { store_group_id: number, name: string, children?: any[] }) {
  // 直接执行筛选，不再展开下一级
  activeCategory.value = item.store_group_id
  activeSubCategory.value = null
  isCategoryPanelOpen.value = false
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

function handleCategoryPanelAllTap() {
  activeCategory.value = categoryPanelAllTargetId.value
  activeSubCategory.value = null
  categoryBreadcrumb.value = []
  isCategoryPanelOpen.value = false
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

// 返回上一级分类
function handleBackCategory() {
  if (categoryBreadcrumb.value.length > 0) {
    // 移除最后一级
    categoryBreadcrumb.value.pop()
    // 更新当前显示的分类列表
    updateSubCategories()
  }
}

// 推荐商品列表 — 取商户列表前3个作为推荐
const recommendList = computed(() => {
  return shopList.value.slice(0, 3).map(item => ({
    id: item.mer_id,
    image: item.mer_avatar || 'https://img.yzcdn.cn/vant/cat.jpeg',
    title: item.store_branch_name ? `${item.mer_name}【${item.store_branch_name}】` : item.mer_name,
    discount: item.discount_label || '',
    slogan: item.slogan || '',
    salesText: item.sales_text || '',
  }))
})

function discountNumber(label: string): string {
  return label.slice(0, -1)
}

// 换一批
function handleRefresh() {
  currentPage.value = 1
  isLastPage.value = false
  fetchShopList()
}

// 点击商品/商家 - 跳转到商家详情页
function handleItemTap(item: any) {
  const merId = item.mer_id || item.id
  uni.navigateTo({
    url: `/pages/merchant/detail?id=${merId}`,
  })
}

// 点击优惠买单 - 跳转到支付页面
function handleBuyTap(item: any) {
  const merId = item.mer_id || item.id
  const merName = item.mer_name || item.title || ''
  uni.navigateTo({
    url: `/pages/payment/checkout?id=${merId}&name=${encodeURIComponent(merName)}`,
  })
}
</script>

<template>
  <view class="min-h-screen bg-[#f5f5f5]">
    <!-- 固定顶部栏 -->
    <wd-navbar z-index="50" placeholder safe-area-inset-top fixed>
      <template #left>
        <TopLocationPicker
          :is-scrolled="isScrolled"
          locatable
          :region="selectedRegion"
          :text="isLocating ? '定位中...' : (locationStore.city || '获取位置')"
          @change="handleRegionChange"
          @locate="handleLocateTap"
        />
      </template>

      <template #title>
        <!-- 初始状态：标题 -->
        <view v-if="!isScrolled" class="flex flex-col items-center">
          <text class="text-18px text-gray-900 font-bold">惠买单</text>
          <text class="text-11px text-gray-400">帮你省更多</text>
        </view>
      </template>
    </wd-navbar>

    <view v-if="isScrolled" class="sticky-search-bar" :style="stickySearchBarStyle">
      <view class="sticky-search-bar__field">
        <view class="min-w-0 flex-1 text-14px text-gray-400" @tap="handleAiSearchTap">
          {{ aiSearchPlaceholder }}
        </view>
        <view
          class="ml-3 size-7 flex items-center justify-center rounded-full from-blue-500 to-purple-500 bg-gradient-to-r"
          @tap="handleAiSearchTap"
        >
          <text class="text-18px text-white" style="transform: rotate(-45deg); display: inline-block">➤</text>
        </view>
      </view>
    </view>

    <!-- AI助手区域 -->
    <view class="from-purple-50 to-white bg-gradient-to-b px-4 pb-4 pt-4">
      <view class="flex items-center justify-between">
        <!-- 左侧文字 -->
        <view class="flex flex-1 flex-col justify-center">
          <view class="text-center text-20px text-gray-800 font-bold">
            Hi, 我是<text class="text-purple-500">AI小惠</text>
          </view>
          <view class="mt-2 text-center text-13px text-gray-400">
            <view v-for="(line, index) in aiHomeSubtitle.split('\n')" :key="index">
              {{ line }}
            </view>
          </view>
        </view>

        <!-- 右侧AI机器人动画 -->
        <view class="ml-2 h-100px w-133px">
          <AiRobotBanner @tap="handleAiTap" />
        </view>
      </view>

      <!-- 搜索框 -->
      <view class="mt-4 flex items-center rounded-full bg-white px-4 py-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.08)" @tap="handleAiSearchTap">
        <view class="min-w-0 flex-1 text-14px text-gray-400" @tap="handleAiSearchTap">
          {{ aiSearchPlaceholder }}
        </view>
        <!-- 发送按钮占位图 -->
        <view
          class="ml-3 h-36px w-36px flex items-center justify-center rounded-full from-blue-500 to-purple-500 bg-gradient-to-r"
          @tap="handleAiSearchTap"
        >
          <text class="text-18px text-white" style="transform: rotate(-45deg); display: inline-block">➤</text>
        </view>
      </view>

      <!-- AI智能推荐 Banner -->
      <view class="mt-4">
        <AiSmartBanner />
      </view>
    </view>

    <!-- AI智能精选区域 -->
    <view class="bg-white px-3 pb-3 pt-3">
      <!-- 标题栏 -->
      <view class="mb-3 flex items-center justify-between">
        <view class="min-w-0 flex flex-1 items-center">
          <!-- AI图标 -->
          <image :src="aiPickIcon" class="mr-2 h-24px w-24px flex-shrink-0" mode="aspectFit" />
          <view class="min-w-0 flex flex-1 items-baseline whitespace-nowrap">
            <text class="text-13px" style="color: #333333; font-weight: 600;">AI智能精选</text>
            <text class="ml-2 text-11px text-gray-400">{{ aiFeaturedSubtitle }}</text>
          </view>
        </view>
        <view
          class="flex flex-shrink-0 items-center text-13px text-gray-400"
          @tap="handleRefresh"
        >
          <text class="mr-1">↻</text>
          <text>换一批</text>
        </view>
      </view>

      <!-- 商品卡片列表 -->
      <view v-if="recommendList.length > 0" class="flex gap-8px">
        <view
          v-for="item in recommendList"
          :key="item.id"
          class="min-w-0 flex-1 overflow-hidden rounded-12px bg-white"
          style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06)"
          @tap="handleItemTap(item)"
        >
          <!-- 商品图片 -->
          <view class="relative">
            <image
              :src="item.image"
              class="h-120px w-full"
              mode="aspectFill"
            />
          </view>

          <!-- 商品信息 -->
          <view class="px-6px py-6px">
            <!-- 商户名称，单行截断 -->
            <view class="truncate text-12px font-bold" style="color: #0b0969">
              {{ item.title }}
            </view>
            <!-- 折扣 + 会员优惠标签，同一行不换行 -->
            <view v-if="item.discount" class="mt-4px flex items-baseline whitespace-nowrap">
              <view class="inline-flex items-baseline text-orange-600 font-bold leading-none">
                <text class="text-18px leading-none">{{ discountNumber(item.discount) }}</text>
                <text class="ml-1px text-12px leading-none">{{ item.discount.slice(-1) }}</text>
              </view>
              <view class="ml-4px inline-flex rounded-full bg-red-50 px-4px py-2px">
                <text class="text-8px text-orange-500">会员优惠</text>
              </view>
            </view>
            <view v-else-if="item.salesText" class="mt-4px">
              <text class="text-9px text-gray-400">{{ item.salesText }}</text>
            </view>
          </view>
        </view>
      </view>
      <view v-else class="py-10 text-center text-13px text-gray-400">
        暂无推荐商户
      </view>
    </view>

    <view id="homeFilterAnchor" class="home-filter-sticky mt-2">
      <view class="bg-white">
        <!-- 分类标签 -->
        <view class="px-3">
          <scroll-view scroll-x class="whitespace-nowrap" :show-scrollbar="false">
            <view class="flex">
              <view
                v-for="item in categories"
                :key="item.id"
                class="category-tab"
                @tap="handleCategoryTap(item.id)"
              >
                <text
                  class="text-15px"
                  :class="activeCategory === item.id ? 'font-bold text-gray-900' : 'text-gray-500'"
                >
                  {{ item.name }}
                </text>
                <!-- 选中下划线 -->
                <view
                  class="category-tab__indicator"
                  :class="{ 'category-tab__indicator--active': activeCategory === item.id }"
                />
              </view>
            </view>
          </scroll-view>
        </view>

        <!-- 筛选条件 -->
        <view class="px-3 py-2">
          <view class="flex items-center gap-3">
            <picker
              v-if="distanceOptions.length > 1"
              :range="distanceOptions"
              range-key="name"
              @change="handleDistanceFilterChange"
            >
              <view class="filter-pill" :class="{ 'filter-pill--active': isDistanceActive }">
                <text class="text-14px" :class="isDistanceActive ? 'text-purple-500 font-bold' : 'text-gray-700'">{{ selectedDistanceName }}</text>
                <text class="i-carbon-chevron-down ml-1 text-14px" :class="isDistanceActive ? 'text-purple-500' : 'text-gray-500'" />
              </view>
            </picker>
            <picker
              v-if="sortOptions.length > 1"
              :range="sortOptions"
              range-key="name"
              @change="handleSortFilterChange"
            >
              <view class="filter-pill" :class="{ 'filter-pill--active': isSortActive }">
                <text class="text-14px" :class="isSortActive ? 'text-purple-500 font-bold' : 'text-gray-700'">{{ selectedSortName }}</text>
                <text class="i-carbon-chevron-down ml-1 text-14px" :class="isSortActive ? 'text-purple-500' : 'text-gray-500'" />
              </view>
            </picker>
            <view
              class="filter-pill ml-auto border"
              style="background: rgba(140, 75, 251, 0.06); border-color: #8c4bfb;"
              @tap="handleAllCategoryTap"
            >
              <image
                v-if="selectedCategoryName === '全部分类'"
                class="all-category-wordmark"
                :src="allCategoryWordmarkIcon"
                mode="aspectFit"
              />
              <text v-else class="text-14px font-bold" style="color: #8c4bfb;">
                {{ selectedCategoryName }}
              </text>
              <text
                v-if="isCategoryPanelOpen"
                class="i-carbon-chevron-up ml-1 text-14px"
                style="color: #8c4bfb;"
              />
              <text
                v-else
                class="i-carbon-chevron-down ml-1 text-14px"
                style="color: #8c4bfb;"
              />
            </view>
          </view>
        </view>
      </view>

      <view v-if="isCategoryPanelOpen" class="category-panel">
        <!-- 面包屑导航（返回按钮） -->
        <view
          v-if="categoryBreadcrumb.length > 0"
          class="category-panel__back"
          @tap="handleBackCategory"
        >
          <text class="text-14px text-gray-500">← 返回上级</text>
          <text class="ml-2 text-14px text-purple-500 font-bold">
            {{ categoryBreadcrumb[categoryBreadcrumb.length - 1].name }}
          </text>
        </view>

        <view
          v-if="activeCategory !== 'all'"
          class="category-panel__item"
          @tap="handleCategoryPanelAllTap"
        >
          <text
            class="text-16px"
            :class="isCategoryPanelAllActive ? 'font-bold text-purple-500' : 'text-gray-700'"
          >
            全部分类
          </text>
        </view>

        <!-- 分类列表 -->
        <view
          v-for="item in currentSubCategories"
          :key="item.store_group_id"
          class="category-panel__item"
          @tap="handleSubCategoryTap(item)"
        >
          <text
            class="text-16px"
            :class="activeCategory === item.store_group_id ? 'font-bold text-purple-500' : 'text-gray-700'"
          >
            {{ item.name }}
          </text>
          <!-- 如果有子分类，显示箭头 -->
          <text v-if="item.children && item.children.length > 0" class="text-12px text-gray-400">›</text>
        </view>

        <!-- 空状态 -->
        <view v-if="currentSubCategories.length === 0" class="category-panel__empty">
          <text class="text-14px text-gray-400">暂无子分类</text>
        </view>
      </view>
    </view>

    <!-- 商户列表 -->
    <view class="mt-2">
      <view
        v-for="item in shopList"
        :key="item.mer_id"
        class="store-card"
        @tap="handleItemTap(item)"
      >
        <view class="flex items-start">
          <!-- 商户图片 -->
          <view class="relative mr-3 flex-shrink-0">
            <image
              :src="item.mer_avatar || 'https://img.yzcdn.cn/vant/cat.jpeg'"
              class="h-100px w-100px rounded-lg"
              mode="aspectFill"
            />
            <!-- 营业状态标签 -->
            <view v-if="item.business_status === 0" class="absolute bottom-0 left-0 rounded-tr bg-black/60 px-2 py-1">
              <text class="text-10px text-white">休息中</text>
            </view>
          </view>

          <!-- 商户信息 -->
          <view class="min-w-0 flex-1">
            <!-- Row 1: 店铺名称和分店名 -->
            <view class="flex flex-wrap items-baseline">
              <text class="text-16px font-bold" style="color: #0b0969">{{ item.mer_name }}</text>
              <text v-if="item.store_branch_name" class="text-13px font-bold" style="color: #0b0969"> | 【{{ item.store_branch_name }}】</text>
            </view>

            <!-- Row 2: Tags - 销量 + 分类 -->
            <view class="mt-2 flex items-center gap-2">
              <view v-if="item.sales_text" class="store-tag store-tag--gray">
                <text class="text-11px" style="color: #666">{{ item.sales_text }}</text>
              </view>
              <view v-if="item.category_name" class="store-tag store-tag--orange">
                <text class="text-11px" style="color: #f56c00">{{ item.category_name }}</text>
              </view>
            </view>

            <!-- Row 3: 电话 + 距离 -->
            <!--
            <view class="mt-2 flex items-center" style="color: #999; font-size: 12px;">
              <view class="flex items-center">
                <text v-for="i in 5" :key="i" class="store-star">★</text>
                <text style="margin-left: 4px;">{{ item.rating || '5.0' }}分</text>
              </view>
            </view>
            -->
            <view class="mt-2 flex items-center" style="color: #999; font-size: 12px;">
              <!-- 电话 -->
              <view class="flex items-center">
                <text class="i-carbon-phone-filled text-12px text-gray-400" />
                <text style="margin-left: 4px;">{{ item.phone || '---' }}</text>
              </view>
              <!-- 距离 -->
              <view class="flex items-center" style="margin-left: 12px;">
                <text class="i-carbon-location-filled text-12px text-gray-400" />
                <text style="margin-left: 4px;">{{ item.distance || '--' }}</text>
              </view>
            </view>

            <!-- Row 4: 折扣 + 会员优惠 + 优惠买单 -->
            <view style="padding: 6px 8px;">
              <view class="store-discount-row mt-2 flex items-center justify-between">
                <view class="flex items-center">
                  <view v-if="item.discount_label" class="inline-flex items-baseline font-bold leading-none" style="color: #f56c00;">
                    <text class="text-22px leading-none">{{ discountNumber(item.discount_label) }}</text>
                    <text class="ml-1px text-12px leading-none">{{ item.discount_label.slice(-1) }}</text>
                  </view>
                  <view v-if="item.discount_label" class="store-tag store-tag--orange ml-2 mt-1 rounded-full" style="border-radius: 999px;">
                    <text class="text-10px" style="color: #f56c00">会员优惠</text>
                  </view>
                </view>
                <image
                  :src="buyBtnIcon"
                  class="store-buy-btn"
                  mode="aspectFit"
                  @tap.stop="handleBuyTap(item)"
                />
              </view>
            </view>
          </view>
        </view>
      </view>

      <!-- 加载状态 -->
      <view v-if="isLoading" class="py-6 text-center text-13px text-gray-400">
        加载中...
      </view>
      <view v-else-if="isLastPage && shopList.length > 0" class="py-6 text-center text-13px text-gray-400">
        没有更多了
      </view>
      <view v-else-if="!isLoading && shopList.length === 0" class="py-10 text-center text-13px text-gray-400">
        暂无商户
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.sticky-search-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 60;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fff;
  box-sizing: border-box;
}

.sticky-search-bar__field {
  min-width: 0;
  flex: 1;
  height: 36px;
  display: flex;
  align-items: center;
  border-radius: 999px;
  background: #f3f4f6;
  padding: 0 12px;
}

.home-filter-sticky {
  position: sticky;
  top: 0;
  z-index: 20;
  overflow: visible;
}

.filter-pill {
  min-height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: #f5f5f5;
  padding: 0 12px;
  white-space: nowrap;
}

/* 筛选项激活态：已选择具体条件时高亮，提示用户点开可改回"全部" */
.filter-pill--active {
  background: #faf5ff;
  border: 1px solid #d8b4fe;
}

.all-category-wordmark {
  width: 64px;
  height: 18px;
}

.category-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px 16px 8px;
}

.category-tab__indicator {
  width: 30px;
  height: 4px;
  margin-top: 6px;
  border-radius: 999px;
  background: linear-gradient(142deg, #9644fb 0%, #4675fe 100%);
  opacity: 0;
}

.category-tab__indicator--active {
  opacity: 1;
}

.category-panel {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  padding: 12px 30px 16px;
  background: #fff;
  border-radius: 0 0 18px 18px;
  box-shadow: 0 14px 24px rgba(20, 20, 20, 0.16);
}

.category-panel__item {
  height: 42px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.category-panel__back {
  height: 42px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 8px;
}

.category-panel__empty {
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 商户卡片 */
.store-card {
  background: #fff;
  padding: 12px;
  border-bottom: 1px solid #f5f5f5;
}

/* 标签通用样式 */
.store-tag {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 4px;
  white-space: nowrap;
}

/* 灰色标签（销量等） */
.store-tag--gray {
  background: #f0f0f0;
}

/* 橙色标签（分类、会员优惠） */
.store-tag--orange {
  background: #fff3e8;
}

/* 星级评分 */
.store-star {
  color: #f5a623;
  font-size: 12px;
}

/* 折扣 + 优惠买单行：横向渐变 白→#ffeee9，与优惠买单按钮区域对齐 */
.store-discount-row {
  background: linear-gradient(to right, #fff 0%, #ffeee9 100%);
  border-radius: 8px;
  /* 与优惠买单按钮高度贴合：上下留出按钮(32px)等量的呼吸空间 */

  margin: 8px -8px -8px;
}

/* 优惠买单按钮（图片） */
.store-buy-btn {
  height: 32px;
  width: 88px;
  flex-shrink: 0;
}
</style>

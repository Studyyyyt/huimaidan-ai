<script lang="ts" setup>
import type { IFilterItem, IStoreGroupTreeNode } from '@/api/huimaidan'
import type { ICollectionViewItem } from './collection.helpers'
import { onShow } from '@dcloudio/uni-app'
import { getCollectionList, getLbsAddress, getLbsGeocoder, getStoreFilters, getStoreGroupOptions, removeCollection } from '@/api/huimaidan'
import allCategoryWordmarkIcon from '@/static/images/all-category-wordmark-icon-no-arrow.png'
import TopLocationPicker from '@/components/TopLocationPicker.vue'
import { useLocationStore, useTokenStore } from '@/store'
import { getWechatUserLocation } from '@/utils/wechat-location'
import { toLoginPage } from '@/utils/toLoginPage'
import { mapCollectionItem } from './collection.helpers'

defineOptions({
  name: 'Collection',
})
definePage({
  style: {
    'navigationStyle': 'custom',
    'navigationBarTitleText': '收藏',
    'mp-alipay': {
      defaultTitle: '收藏',
      transparentTitle: 'always',
      titlePenetrate: 'YES',
      titleBarColor: '#ffffff',
    },
  },
})

// 顶部栏状态
const isScrolled = ref(false)
const isLocating = ref(false)

// 使用位置 store
const locationStore = useLocationStore()
const tokenStore = useTokenStore()

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
  sortBy: 'default' as string,
})

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

// 店铺分组树形数据
const storeGroupTree = ref<IStoreGroupTreeNode[]>([])
// 一级分类标签
const categories = ref<Array<{ id: number | string, name: string }>>([
  { id: 'all', name: '全部' },
])
// 当前选中的分类
const activeCategory = ref<string | number>('all')
// 分类面板是否展开
const isCategoryPanelOpen = ref(false)
// 当前展开的子分类列表
const currentSubCategories = ref<Array<{ store_group_id: number, name: string, children?: any[] }>>([])
// 分类面包屑路径
const categoryBreadcrumb = ref<Array<{ store_group_id: number, name: string }>>([])

// 当前选中的分类名称
const selectedCategoryName = computed(() => {
  if (!activeCategory.value || activeCategory.value === 'all') {
    return '全部分类'
  }
  const found = findCategoryInTree(storeGroupTree.value, activeCategory.value as number)
  return (found?.label ?? found?.name) || '全部分类'
})

// "全部分类"清除项的目标 ID：选中任意子分类时，点击"全部分类"应回到当前所在的一级分类
const categoryPanelAllTargetId = computed(() => {
  if (!activeCategory.value || activeCategory.value === 'all') {
    return 'all'
  }
  const path = findCategoryPath(storeGroupTree.value, activeCategory.value)
  return path[0] ? (path[0].value ?? path[0].store_group_id) : 'all'
})
const isCategoryPanelAllActive = computed(() => String(activeCategory.value) === String(categoryPanelAllTargetId.value))

// 监听页面滚动
onPageScroll((e) => {
  isScrolled.value = (e.scrollTop || 0) > 50
})

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

// 查找分类在树中的完整路径（用于"全部分类"清除项定位一级分类）
function findCategoryPath(tree: IStoreGroupTreeNode[], id: string | number): IStoreGroupTreeNode[] {
  for (const item of tree) {
    if (String(item.value ?? item.store_group_id) === String(id)) {
      return [item]
    }
    const childPath = findCategoryPath(item.children || [], id)
    if (childPath.length > 0) {
      return [item, ...childPath]
    }
  }
  return []
}

// 更新子分类列表（沿用首页逻辑：选中分类的子级；否则展示一级）
function updateSubCategories() {
  if (!activeCategory.value || activeCategory.value === 'all') {
    currentSubCategories.value = storeGroupTree.value.map(c => ({
      store_group_id: c.value ?? c.store_group_id,
      name: c.label ?? c.name,
      children: c.children,
    }))
    return
  }

  const found = findCategoryInTree(storeGroupTree.value, activeCategory.value as number)
  const children = found?.children?.length ? found.children : storeGroupTree.value
  currentSubCategories.value = children.map(c => ({
    store_group_id: c.value ?? c.store_group_id,
    name: c.label ?? c.name,
    children: c.children,
  }))
}

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
  }
}

// 获取店铺分组分类
async function fetchCategories() {
  try {
    const res = await getStoreGroupOptions()
    if (res && Array.isArray(res)) {
      storeGroupTree.value = res
      // formatCascaderData 返回的数据字段是 value/label，需要映射
      const items = res.map(c => ({ id: c.value ?? c.store_group_id, name: c.label ?? c.name }))
      categories.value = [{ id: 'all', name: '全部' }, ...items]
    }
  }
  catch (error) {
    console.error('获取商户分类失败:', error)
  }
}

function regionAddressText(region: string[]) {
  return region.filter(Boolean).join(' ')
}

async function refreshCurrentLocation() {
  const location = await getWechatUserLocation()
  locationStore.setCoordinates(location.latitude, location.longitude)
  const geocoder = await getLbsGeocoder({
    location: `${location.latitude},${location.longitude}`,
  })
  const component = geocoder.address_component
  const exactAddress = geocoder.formatted_addresses?.recommend
    || geocoder.formatted_addresses?.rough
    || geocoder.address
    || ''
  locationStore.setLocation(
    component?.province || '',
    component?.city || '',
    component?.district || '',
    exactAddress,
  )
}

async function ensureCoordinates() {
  if (locationStore.hasCoordinates) {
    return
  }
  await refreshCurrentLocation()
}

// 省市区选择变化
async function handleRegionChange(e: any) {
  const region = Array.isArray(e.detail?.value) ? e.detail.value : []
  const province = String(region[0] || '')
  const city = String(region[1] || '')
  const district = String(region[2] || '')
  const address = [province, city, district].filter(Boolean).join(' ')
  if (!address) {
    return
  }

  locationStore.setLocation(province, city, district, address)
  locationStore.clearCoordinates()

  try {
    const geocoder = await getLbsAddress({ region: city || province, address })
    const latitude = Number(geocoder.location?.lat)
    const longitude = Number(geocoder.location?.lng)
    if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
      locationStore.setCoordinates(latitude, longitude)
    }
  }
  catch (error) {
    console.error('省市区位置解析失败:', error)
  }
  fetchCollectionList()
}

// 点击定位按钮
async function handleLocateTap() {
  if (isLocating.value) {
    return
  }
  isLocating.value = true
  try {
    await refreshCurrentLocation()
    fetchCollectionList()
  }
  catch (error) {
    console.error('刷新当前位置失败:', error)
    uni.showToast({
      title: error instanceof Error ? error.message : '获取当前位置失败',
      icon: 'none',
    })
  }
  finally {
    isLocating.value = false
  }
}

// 距离筛选变化
async function handleDistanceFilterChange(e: any) {
  const index = Number(e.detail.value)
  const option = distanceOptions.value[index]
  if (!option) {
    return
  }

  if (option.id === 'all') {
    selectedFilters.value.distance = ''
    fetchCollectionList()
    return
  }

  try {
    await ensureCoordinates()
    selectedFilters.value.distance = option.value
    fetchCollectionList()
  }
  catch {
    uni.showToast({ title: '请授权定位后使用距离筛选', icon: 'none' })
  }
}

// 排序筛选变化
async function handleSortFilterChange(e: any) {
  const index = Number(e.detail.value)
  const option = sortOptions.value[index]
  if (!option) {
    return
  }

  if (option.id === 'all') {
    selectedFilters.value.sortBy = 'default'
    fetchCollectionList()
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
  fetchCollectionList()
}

// 点击一级分类（直接筛选，不再展开面板）
function handleCategoryTap(id: string | number) {
  activeCategory.value = id
  isCategoryPanelOpen.value = false
  fetchCollectionList()
}

// 点击全部分类按钮
async function handleAllCategoryTap() {
  const nextOpenState = !isCategoryPanelOpen.value
  if (!nextOpenState) {
    isCategoryPanelOpen.value = false
    return
  }
  if (storeGroupTree.value.length === 0) {
    await fetchCategories()
  }
  categoryBreadcrumb.value = []
  updateSubCategories()
  isCategoryPanelOpen.value = true
}

// 点击子分类（直接筛选，不再展开下一级）
function handleSubCategoryTap(item: { store_group_id: number, name: string, children?: any[] }) {
  activeCategory.value = item.store_group_id
  isCategoryPanelOpen.value = false
  categoryBreadcrumb.value = []
  fetchCollectionList()
}

// 点击面板"全部分类"：清除到所在一级分类
function handleCategoryPanelAllTap() {
  activeCategory.value = categoryPanelAllTargetId.value
  isCategoryPanelOpen.value = false
  categoryBreadcrumb.value = []
  fetchCollectionList()
}

// 返回上一级分类
function handleBackCategory() {
  if (categoryBreadcrumb.value.length > 0) {
    categoryBreadcrumb.value.pop()
    updateSubCategories()
  }
}

// 点击消息
function handleMessageTap() {
  uni.showToast({ title: '消息功能暂未接入后端', icon: 'none' })
}

// 收藏商户列表
const collectionList = ref<ICollectionViewItem[]>([])
const isLoading = ref(false)
const pageError = ref('')
const needsLogin = ref(false)

function showLoginRequired() {
  collectionList.value = []
  needsLogin.value = true
  pageError.value = '请先登录后查看收藏商户'
}

function isLoginRequiredError(error: unknown) {
  return error instanceof Error && error.message.includes('请登录')
}

async function fetchCollectionList() {
  isLoading.value = true
  pageError.value = ''
  needsLogin.value = false
  try {
    tokenStore.updateNowTime()
    if (!tokenStore.hasLogin) {
      showLoginRequired()
      return
    }

    const params: Record<string, any> = {}
    if (activeCategory.value && activeCategory.value !== 'all') {
      params.store_group_id = activeCategory.value as number
    }
    if (selectedFilters.value.distance) {
      params.distance = selectedFilters.value.distance
    }
    if (selectedFilters.value.sortBy) {
      params.order = selectedFilters.value.sortBy
    }
    // 只要已有定位坐标就传给后端，用于展示距离与按距离排序
    if (locationStore.hasCoordinates) {
      params.latitude = locationStore.latitude
      params.longitude = locationStore.longitude
    }
    const res = await getCollectionList(params)
    collectionList.value = (res?.list || []).map(mapCollectionItem)
  }
  catch (error) {
    console.error('获取收藏列表失败:', error)
    if (isLoginRequiredError(error)) {
      showLoginRequired()
      return
    }

    pageError.value = '获取收藏列表失败'
    uni.showToast({ title: '获取收藏列表失败', icon: 'none' })
  }
  finally {
    isLoading.value = false
  }
}

function handleLoginTap() {
  toLoginPage()
}

onShow(() => {
  void fetchFilters()
  fetchCategories()
  fetchCollectionList()
})

// 点击商户 - 跳转到商家详情页
function handleItemTap(item: any) {
  uni.navigateTo({
    url: `/pages/merchant/detail?id=${item.id}`,
  })
}

// 取消收藏
async function handleCollectTap(item: ICollectionViewItem) {
  try {
    await removeCollection(item.id)
    collectionList.value = collectionList.value.filter(collection => collection.id !== item.id)
    uni.showToast({ title: '已取消收藏', icon: 'success' })
  }
  catch (error) {
    console.error('取消收藏失败:', error)
    uni.showToast({ title: '取消收藏失败', icon: 'none' })
  }
}

// 点击优惠买单 - 跳转到支付页面
function handleBuyTap(item: ICollectionViewItem) {
  uni.navigateTo({
    url: `/pages/payment/checkout?id=${item.id}&name=${encodeURIComponent(item.title)}`,
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
          <text class="text-18px text-gray-900 font-bold">收藏</text>
        </view>
      </template>

      <template #right>
        <view class="flex-shrink-0" @tap="handleMessageTap">
          <text class="text-20px">💬</text>
        </view>
      </template>
    </wd-navbar>

    <!-- 分类导航栏 -->
    <view class="collection-category-section bg-white">
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
              <view
                class="category-tab__indicator"
                :class="{ 'category-tab__indicator--active': activeCategory === item.id }"
              />
            </view>
          </view>
        </scroll-view>
      </view>

      <!-- 筛选条件栏：完全对齐首页 -->
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

      <!-- 分类展开面板 -->
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

        <!-- "全部分类"清除项：点击回到所在一级分类 -->
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

    <!-- 收藏商户列表 -->
    <view class="mt-2">
      <view
        v-for="item in collectionList"
        :key="item.id"
        class="store-card"
        @tap="handleItemTap(item)"
      >
        <view class="flex items-start">
          <!-- 商户图片 -->
          <view class="relative mr-3 flex-shrink-0">
            <image
              v-if="item.image"
              :src="item.image"
              class="h-100px w-100px rounded-lg"
              mode="aspectFill"
            />
            <view v-else class="h-100px w-100px flex items-center justify-center rounded-lg bg-orange-50">
              <text class="i-carbon-store text-28px text-orange-500" />
            </view>
            <!-- 右上角收藏星标（图片内） -->
            <view
              class="collect-badge collect-badge--collected"
              @tap.stop="handleCollectTap(item)"
            >
              <text class="collect-badge__star collect-badge__star--collected">★</text>
            </view>
          </view>

          <!-- 商户信息 -->
          <view class="min-w-0 flex-1">
            <!-- Row 1: 店铺名称和分店名 -->
            <view class="flex flex-wrap items-baseline">
              <text class="text-16px font-bold" style="color: #0b0969">{{ item.title }}</text>
              <text v-if="item.storeBranchName" class="text-13px font-bold" style="color: #0b0969"> | 【{{ item.storeBranchName }}】</text>
            </view>

            <!-- Row 2: Tags - 销量 + 分类 -->
            <view class="mt-2 flex items-center gap-2">
              <view v-if="item.salesText" class="store-tag store-tag--gray">
                <text class="text-11px" style="color: #666">{{ item.salesText }}</text>
              </view>
              <view v-if="item.category" class="store-tag store-tag--orange">
                <text class="text-11px" style="color: #f56c00">{{ item.category }}</text>
              </view>
            </view>

            <!-- Row 3: 电话 + 距离 -->
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

            <!-- Row 4: 折扣 + 到店优惠 + 优惠买单 -->
            <view class="mt-2 flex items-center justify-between">
              <view class="flex items-center">
                <text v-if="item.discount" class="text-18px font-bold" style="color: #f56c00;">{{ item.discount }}</text>
                <view v-if="item.discount" class="store-tag store-tag--orange ml-2">
                  <text class="text-10px" style="color: #f56c00">到店优惠</text>
                </view>
              </view>
            </view>
          </view>
        </view>
      </view>
      <view v-if="isLoading" class="py-8 text-center text-13px text-gray-400">
        加载中...
      </view>
      <view v-else-if="collectionList.length === 0" class="py-10 text-center text-13px text-gray-400">
        <view v-if="needsLogin" class="flex flex-col items-center">
          <text class="text-14px text-gray-500">{{ pageError }}</text>
          <view class="mt-3 rounded-full bg-orange-500 px-5 py-2" @tap="handleLoginTap">
            <text class="text-14px text-white font-bold">去登录</text>
          </view>
        </view>
        <text v-else>{{ pageError || '暂无收藏商户' }}</text>
      </view>
    </view>

    <!-- 底部安全区域 -->
    <view class="h-20px" />
  </view>
</template>

<style lang="scss" scoped>
.collection-category-section {
  position: relative;
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

.category-panel {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  padding: 12px 30px 16px;
  background: #fff;
  border-radius: 0 0 18px 18px;
  box-shadow: 0 14px 24px rgba(20, 20, 20, 0.16);
  z-index: 10;
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

/* 橙色标签（分类、到店优惠） */
.store-tag--orange {
  background: #fff3e8;
}

/* 优惠买单按钮 */
.store-buy-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 16px;
  border-radius: 999px;
  background: #f56c00;
  white-space: nowrap;
}

/* 收藏星标 — 仅星星，无底圆 */
.collect-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 星星通用 */
.collect-badge__star {
  font-size: 22px;
  line-height: 1;
}

/* 已收藏星星：金黄色 */
.collect-badge__star--collected {
  color: #ffd200;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
}

/* 未收藏星星：白色（预留） */
.collect-badge__star--uncollected {
  color: rgba(255, 255, 255, 0.85);
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}
</style>

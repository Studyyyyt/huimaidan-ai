<template>
  <view class="browsing-page">
    <!-- 自定义导航栏 -->
    <view class="custom-nav">
      <view class="page-top-safe" :style="{ height: topSafeHeight }" />
      <view class="nav-content">
        <view class="nav-back" @tap="handleBack">
          <text class="i-carbon-arrow-left text-36rpx text-gray-800" />
        </view>
        <text class="nav-title">浏览足迹</text>
        <view class="nav-placeholder" />
      </view>
    </view>

    <!-- 分类导航栏 -->
    <view class="category-nav">
      <scroll-view scroll-x class="category-nav__scroll" :show-scrollbar="false">
        <view class="category-nav__list">
          <view
            v-for="item in categories"
            :key="item.id"
            class="category-nav__item"
            @tap="handleCategoryTap(item.id)"
          >
            <text
              class="category-nav__text"
              :class="{ 'category-nav__text--active': activeCategory === item.id }"
            >
              {{ item.name }}
            </text>
            <view v-if="activeCategory === item.id" class="category-nav__indicator" />
          </view>
        </view>
      </scroll-view>
    </view>

    <!-- 筛选条件栏 -->
    <view class="filter-bar">
      <view class="filter-bar__content">
        <view
          class="filter-pill filter-pill--purple"
          @tap="handleAllCategoryTap"
        >
          <text class="filter-pill__text filter-pill__text--purple">{{ selectedCategoryName }}</text>
          <text class="filter-pill__arrow filter-pill__arrow--purple">{{ isCategoryPanelOpen ? '▲' : '▼' }}</text>
        </view>
      </view>
    </view>

    <!-- 分类展开面板 -->
    <view v-if="isCategoryPanelOpen" class="category-panel">
      <!-- 面包屑导航 -->
      <view
        v-if="categoryBreadcrumb.length > 0"
        class="category-panel__back"
        @tap="handleBackCategory"
      >
        <text class="category-panel__back-text">← 返回上级</text>
        <text class="category-panel__back-name">{{ categoryBreadcrumb[categoryBreadcrumb.length - 1].name }}</text>
      </view>

      <!-- 分类列表 -->
      <view
        v-for="item in currentSubCategories"
        :key="item.store_group_id"
        class="category-panel__item"
        @tap="handleSubCategoryTap(item)"
      >
        <text
          class="category-panel__item-text"
          :class="{ 'category-panel__item-text--active': activeCategory === item.store_group_id }"
        >
          {{ item.name }}
        </text>
        <text v-if="item.children && item.children.length > 0" class="category-panel__item-arrow">›</text>
      </view>

      <!-- 空状态 -->
      <view v-if="currentSubCategories.length === 0" class="category-panel__empty">
        <text class="category-panel__empty-text">暂无子分类</text>
      </view>
    </view>

    <scroll-view class="shop-list" scroll-y :style="{ height: shopListHeight }">
      <view v-if="isLoading" class="empty-state">
        <text class="empty-text">加载中...</text>
      </view>
      <view v-else-if="filteredShopList.length === 0" class="empty-state">
        <text class="empty-text">{{ pageError || '暂无浏览记录' }}</text>
      </view>
      <view
        v-for="shop in filteredShopList"
        :key="shop.id"
        class="shop-card"
        @tap="handleShopDetail(shop)"
        @longpress.stop="handleDeleteHistory(shop)"
      >
        <view class="shop-card__image">
          <image
            v-if="shop.image"
            class="shop-card__img"
            :src="shop.image"
            mode="aspectFill"
          />
          <view v-else class="shop-card__image-placeholder">
            <text class="shop-card__image-placeholder-text">暂无图片</text>
          </view>
          <text class="shop-card__favorite">★</text>
        </view>

        <view class="shop-card__info">
          <text class="shop-card__name">{{ shop.name }}</text>

          <view class="shop-card__tags">
            <text v-if="shop.salesText" class="shop-card__tag">{{ shop.salesText }}</text>
            <text v-if="shop.categoryName" class="shop-card__tag">{{ shop.categoryName }}</text>
          </view>

          <view class="shop-card__meta">
            <view v-if="shop.rating > 0" class="shop-card__rating">
              <text
                v-for="star in 5"
                :key="star"
                class="shop-card__star"
                :class="{ 'shop-card__star--active': star <= Math.round(shop.rating) }"
              >
                ★
              </text>
              <text class="shop-card__score">{{ formatRating(shop.rating) }}分</text>
            </view>
            <view v-if="shop.phone" class="shop-card__phone-info">
              <text class="i-carbon-phone-filled shop-card__meta-icon" />
              <text class="shop-card__phone">{{ shop.phone }}</text>
            </view>
            <view v-if="shop.distance" class="shop-card__distance-info">
              <text class="i-carbon-location-filled shop-card__meta-icon" />
              <text class="shop-card__distance">{{ shop.distance }}</text>
            </view>
          </view>

          <view v-if="shop.discountLabel" class="shop-card__discount">
            <text class="shop-card__discount-number">{{ discountNumber(shop.discountLabel) }}</text>
            <text class="shop-card__discount-unit">折</text>
            <text class="shop-card__discount-tag">到店优惠</text>
          </view>
        </view>
      </view>
    </scroll-view>
  </view>
</template>

<script lang="ts" setup>
import type { IBrowsingHistoryViewItem } from './browsing-history.helpers'
import type { IStoreGroupTreeNode } from '@/api/huimaidan'
import { onShow } from '@dcloudio/uni-app'
import { computed, ref } from 'vue'
import { deleteBrowsingHistory, getBrowsingHistory, getStoreGroupOptions } from '@/api/huimaidan'
import { useLocationStore } from '@/store/location'
import { mapBrowsingHistoryItem } from './browsing-history.helpers'

defineOptions({
  name: 'BrowsingHistory',
})

definePage({
  style: {
    navigationStyle: 'custom',
    navigationBarTitleText: '浏览足迹',
  },
})

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

const shopList = ref<IBrowsingHistoryViewItem[]>([])
const isLoading = ref(false)
const pageError = ref('')
const locationStore = useLocationStore()
const systemInfo = uni.getSystemInfoSync()
const topSafeHeight = computed(() => `${systemInfo.statusBarHeight || 0}px`)

const filteredShopList = computed(() => shopList.value)
const shopListHeight = computed(() => `calc(100vh - ${topSafeHeight.value} - 88rpx - 92rpx - 88rpx)`)

// 当前选中的分类名称
const selectedCategoryName = computed(() => {
  if (!activeCategory.value || activeCategory.value === 'all') {
    return '全部分类'
  }
  const found = findCategoryInTree(storeGroupTree.value, activeCategory.value as number)
  return (found?.label ?? found?.name) || '全部分类'
})

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

// 更新子分类列表
function updateSubCategories() {
  if (categoryBreadcrumb.value.length > 0) {
    const lastPath = categoryBreadcrumb.value[categoryBreadcrumb.value.length - 1]
    const selectedGroup = storeGroupTree.value.find(c => (c.value ?? c.store_group_id) === lastPath.store_group_id)
      || findCategoryInTree(storeGroupTree.value, lastPath.store_group_id)
    currentSubCategories.value = (selectedGroup?.children || []).map(c => ({
      store_group_id: c.value ?? c.store_group_id,
      name: c.label ?? c.name,
      children: c.children,
    }))
  }
  else if (activeCategory.value && activeCategory.value !== 'all') {
    const selectedGroup = storeGroupTree.value.find(c => (c.value ?? c.store_group_id) === activeCategory.value)
    currentSubCategories.value = (selectedGroup?.children || []).map(c => ({
      store_group_id: c.value ?? c.store_group_id,
      name: c.label ?? c.name,
      children: c.children,
    }))
  }
  else {
    currentSubCategories.value = storeGroupTree.value.map(c => ({
      store_group_id: c.value ?? c.store_group_id,
      name: c.label ?? c.name,
      children: c.children,
    }))
  }
}

// 点击一级分类
function handleCategoryTap(id: string | number) {
  activeCategory.value = id
  categoryBreadcrumb.value = []
  isCategoryPanelOpen.value = false
  fetchBrowsingHistory()
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

// 点击子分类
function handleSubCategoryTap(item: { store_group_id: number, name: string, children?: any[] }) {
  if (item.children && item.children.length > 0) {
    categoryBreadcrumb.value.push({
      store_group_id: item.store_group_id,
      name: item.name,
    })
    currentSubCategories.value = item.children.map(c => ({
      store_group_id: c.value ?? c.store_group_id,
      name: c.label ?? c.name,
      children: c.children,
    }))
    return
  }

  activeCategory.value = item.store_group_id
  isCategoryPanelOpen.value = false
  categoryBreadcrumb.value = []
  fetchBrowsingHistory()
}

// 返回上一级分类
function handleBackCategory() {
  if (categoryBreadcrumb.value.length > 0) {
    categoryBreadcrumb.value.pop()
    updateSubCategories()
  }
}

async function fetchBrowsingHistory() {
  isLoading.value = true
  pageError.value = ''
  try {
    const params: {
      page: number
      limit: number
      latitude?: number
      longitude?: number
      store_group_id?: number
    } = { page: 1, limit: 60 }

    if (locationStore.latitude !== null && locationStore.longitude !== null) {
      params.latitude = locationStore.latitude
      params.longitude = locationStore.longitude
    }

    // 添加分类筛选
    if (activeCategory.value && activeCategory.value !== 'all') {
      params.store_group_id = activeCategory.value as number
    }

    const res = await getBrowsingHistory(params)
    shopList.value = (res?.list || []).map(mapBrowsingHistoryItem)
  }
  catch (error) {
    const message = getErrorMessage(error)
    console.error('获取浏览记录失败:', error)
    pageError.value = message
    uni.showToast({ title: message, icon: 'none' })
  }
  finally {
    isLoading.value = false
  }
}

onShow(() => {
  fetchCategories()
  fetchBrowsingHistory()
})

function handleShopDetail(shop: IBrowsingHistoryViewItem) {
  if (!shop.merchantId) {
    uni.showToast({ title: '该记录缺少可跳转的商户ID', icon: 'none' })
    return
  }
  uni.navigateTo({
    url: `/pages/merchant/detail?id=${shop.merchantId}`,
  })
}

function handleDeleteHistory(shop: IBrowsingHistoryViewItem) {
  if (!shop.id) {
    uni.showToast({ title: '该记录缺少可删除的历史ID', icon: 'none' })
    return
  }

  uni.showModal({
    title: '删除浏览记录',
    content: '确定删除这条浏览记录吗？',
    confirmText: '删除',
    confirmColor: '#ff4c3f',
    success: async (res) => {
      if (!res.confirm)
        return

      try {
        await deleteBrowsingHistory(shop.id)
        uni.showToast({ title: '浏览记录已删除', icon: 'success' })
        await fetchBrowsingHistory()
      }
      catch (error) {
        const message = getErrorMessage(error)
        console.error('删除浏览记录失败:', error)
        uni.showToast({ title: message, icon: 'none' })
      }
    },
  })
}

// 返回上一页
function handleBack() {
  uni.navigateBack()
}

function formatRating(rating: number) {
  return Number.isFinite(rating) ? rating.toFixed(1) : '0.0'
}

function discountNumber(label: string) {
  return label.replace(/折$/, '')
}

function getErrorMessage(error: unknown) {
  if (error instanceof Error && error.message)
    return error.message
  if (typeof error === 'object' && error !== null) {
    const payload = error as Record<string, any>
    return payload.message || payload.msg || payload.data?.msg || '获取浏览记录失败'
  }
  return '获取浏览记录失败'
}
</script>

<style lang="scss" scoped>
.browsing-page {
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
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

  .page-top-safe {
    width: 100%;
    flex-shrink: 0;
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

.category-nav {
  height: 92rpx;
  background: rgba(247, 249, 255, 0.86);

  &__scroll {
    width: 100%;
    height: 100%;
    white-space: nowrap;
  }

  &__list {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 2rpx;
  }

  &__item {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 0 25rpx;
    flex-shrink: 0;
  }

  &__text {
    font-size: 28rpx;
    color: #55545b;
    white-space: nowrap;

    &--active {
      font-weight: 700;
      color: #191923;
    }
  }

  &__indicator {
    position: absolute;
    left: 50%;
    bottom: 9rpx;
    width: 34rpx;
    height: 8rpx;
    border-radius: 999rpx;
    transform: translateX(-50%);
    background: #8068ff;
  }
}

.shop-list {
  padding: 13rpx 14rpx 22rpx;
  box-sizing: border-box;
}

.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 360rpx;
}

.empty-text {
  font-size: 28rpx;
  color: #8d8d96;
}

.shop-card {
  display: flex;
  min-height: 232rpx;
  margin-bottom: 22rpx;
  padding: 16rpx;
  overflow: hidden;
  box-sizing: border-box;
  border-radius: 16rpx;
  background: #fff;
  box-shadow: 0 6rpx 18rpx rgba(96, 91, 164, 0.08);

  &__image {
    position: relative;
    width: 220rpx;
    height: 220rpx;
    overflow: hidden;
    flex-shrink: 0;
    border-radius: 6rpx;
    background: #f2f2f5;
  }

  &__img,
  &__image-placeholder {
    width: 100%;
    height: 100%;
  }

  &__image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__image-placeholder-text {
    font-size: 22rpx;
    color: #a0a0a8;
  }

  &__favorite {
    position: absolute;
    top: 14rpx;
    right: 13rpx;
    font-size: 40rpx;
    line-height: 1;
    color: #ffc107;
    text-shadow: 0 2rpx 5rpx rgba(0, 0, 0, 0.28);
  }

  &__info {
    display: flex;
    min-width: 0;
    flex: 1;
    flex-direction: column;
    padding: 6rpx 0 2rpx 24rpx;
  }

  &__name {
    overflow: hidden;
    margin-bottom: 13rpx;
    color: #0B0969;
    font-size: 31rpx;
    font-weight: 800;
    line-height: 38rpx;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  &__tags {
    display: flex;
    min-height: 32rpx;
    flex-wrap: wrap;
    gap: 10rpx;
    margin-bottom: 10rpx;
  }

  &__tag {
    max-width: 180rpx;
    overflow: hidden;
    padding: 3rpx 10rpx;
    border-radius: 5rpx;
    background: #f3f3f3;
    color: #a4a4a4;
    font-size: 21rpx;
    line-height: 28rpx;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  &__meta {
    display: flex;
    min-height: 30rpx;
    align-items: center;
    gap: 12rpx;
    margin-bottom: 18rpx;
    color: #9d9da5;
    white-space: nowrap;
  }

  &__rating,
  &__phone-info,
  &__distance-info {
    display: flex;
    align-items: center;
    min-width: 0;
  }

  &__star {
    margin-right: 1rpx;
    color: #d8d8d8;
    font-size: 22rpx;
    line-height: 1;

    &--active {
      color: #ffc107;
    }
  }

  &__score,
  &__phone,
  &__distance {
    overflow: hidden;
    color: #999aa1;
    font-size: 22rpx;
    line-height: 28rpx;
    text-overflow: ellipsis;
  }

  &__score {
    margin-left: 6rpx;
  }

  &__phone {
    max-width: 142rpx;
  }

  &__distance {
    max-width: 88rpx;
  }

  &__meta-icon {
    margin-right: 5rpx;
    color: #a0a0a7;
    font-size: 23rpx;
  }

  &__discount {
    display: flex;
    align-items: baseline;
    margin-top: auto;
  }

  &__discount-number {
    color: #ff4637;
    font-size: 40rpx;
    font-weight: 800;
    line-height: 46rpx;
  }

  &__discount-unit {
    margin: 0 18rpx 0 2rpx;
    color: #ff4637;
    font-size: 23rpx;
    font-weight: 700;
  }

  &__discount-tag {
    color: #ff6b55;
    font-size: 22rpx;
    font-weight: 500;
  }
}

/* 筛选条件栏 */
.filter-bar {
  background: #fff;
  padding: 16rpx 24rpx;

  &__content {
    display: flex;
    align-items: center;
    gap: 20rpx;
  }
}

.filter-pill {
  min-height: 56rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12rpx;
  background: #f5f5f5;
  padding: 0 20rpx;
  white-space: nowrap;

  &--purple {
    background: #f3e8ff;
    border: 1rpx solid #d8b4fe;
  }

  &__text {
    font-size: 26rpx;
    color: #374151;

    &--purple {
      color: #7c3aed;
      font-weight: 600;
    }
  }

  &__arrow {
    margin-left: 8rpx;
    font-size: 20rpx;
    color: #6b7280;

    &--purple {
      color: #7c3aed;
    }
  }
}

/* 分类展开面板 */
.category-panel {
  position: relative;
  background: #fff;
  padding: 20rpx 48rpx 24rpx;
  border-radius: 0 0 28rpx 28rpx;
  box-shadow: 0 20rpx 40rpx rgba(20, 20, 20, 0.12);
  min-height: 600rpx;

  &__back {
    height: 72rpx;
    display: flex;
    align-items: center;
    border-bottom: 1rpx solid #f0f0f0;
    margin-bottom: 12rpx;
  }

  &__back-text {
    font-size: 26rpx;
    color: #6b7280;
  }

  &__back-name {
    margin-left: 12rpx;
    font-size: 26rpx;
    color: #7c3aed;
    font-weight: 600;
  }

  &__item {
    height: 72rpx;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  &__item-text {
    font-size: 28rpx;
    color: #374151;

    &--active {
      color: #7c3aed;
      font-weight: 600;
    }
  }

  &__item-arrow {
    font-size: 24rpx;
    color: #9ca3af;
  }

  &__empty {
    height: 120rpx;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__empty-text {
    font-size: 26rpx;
    color: #9ca3af;
  }
}
</style>

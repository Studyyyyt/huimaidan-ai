import type { TabBar } from '@uni-helper/vite-plugin-uni-pages'
import type { CustomTabBarItem, NativeTabBarItem } from './types'

/**
 * tabbar 选择的策略，更详细的介绍见 tabbar.md 文件
 * 0: 'NO_TABBAR' `无 tabbar`
 * 1: 'NATIVE_TABBAR'  `原生 tabbar`
 * 2: 'CUSTOM_TABBAR' `自定义 tabbar`
 *
 * 温馨提示：本文件的任何代码更改了之后，都需要重新运行，否则 pages.json 不会更新导致配置不生效
 */
export const TABBAR_STRATEGY_MAP = {
  NO_TABBAR: 0,
  NATIVE_TABBAR: 1,
  CUSTOM_TABBAR: 2,
}

// TODO: 1/3. 通过这里切换使用tabbar的策略
// 如果是使用 NO_TABBAR(0)，nativeTabbarList 和 customTabbarList 都不生效
// 如果是使用 NATIVE_TABBAR(1)，只需要配置 nativeTabbarList，customTabbarList 不生效
// 如果是使用 CUSTOM_TABBAR(2)，只需要配置 customTabbarList，nativeTabbarList 不生效
export const selectedTabbarStrategy = TABBAR_STRATEGY_MAP.CUSTOM_TABBAR

// TODO: 2/3. 使用 NATIVE_TABBAR 时，更新下面的 tabbar 配置
// 原生 tabbar 不支持运行时按角色动态变更；要按角色动态显示/隐藏，必须切换到自定义 tabbar
export const nativeTabbarList: NativeTabBarItem[] = [
  {
    iconPath: 'static/tabbar/home.png',
    selectedIconPath: 'static/tabbar/homeHL.png',
    pagePath: 'pages/index/index',
    text: '首页',
  },
  {
    iconPath: 'static/tabbar/personal.png',
    selectedIconPath: 'static/tabbar/personalHL.png',
    pagePath: 'pages/me/me',
    text: '个人',
  },
]

// TODO: 3/3. 使用 CUSTOM_TABBAR 时，更新下面的 tabbar 配置
// 如果需要配置鼓包，需要在 'tabbar/store.ts' 里面设置，最后在 `tabbar/index.vue` 里面更改鼓包的图片
export const customTabbarList: CustomTabBarItem[] = [
  {
    text: '首页',
    pagePath: 'pages/index/index',
    iconType: 'image',
    icon: '/static/webp/tabbar/home.webp',
    iconActive: '/static/webp/tabbar/home-active.webp',
  },
  {
    text: '收藏',
    pagePath: 'pages/collection/collection',
    iconType: 'image',
    icon: '/static/webp/tabbar/fav.webp',
    iconActive: '/static/webp/tabbar/fav-active.webp',
  },
  {
    text: '优惠券',
    pagePath: 'pages/coupon/coupon',
    iconType: 'image',
    icon: '/static/webp/tabbar/coupon.webp',
    iconActive: '/static/webp/tabbar/coupon-active.webp',
  },
  {
    pagePath: 'pages/me/me',
    text: '我的',
    iconType: 'image',
    icon: '/static/webp/tabbar/me.webp',
    iconActive: '/static/webp/tabbar/me-active.webp',
  },
  // 管理员页面：注册到原生 tabBar 以支持 switchTab 跳转，通过 roles 限制普通用户不可见
  {
    text: '财务',
    pagePath: 'pages/admin/finance',
    iconType: 'unocss',
    icon: 'i-carbon-money',
    roles: ['mer-admin'],
  },
  {
    text: '我的',
    pagePath: 'pages/admin/profile',
    iconType: 'unocss',
    icon: 'i-carbon-user',
    roles: ['mer-admin'],
  },
]

// 管理员专用 tabbar 配置
export const adminTabbarList: CustomTabBarItem[] = [
  {
    text: '财务',
    pagePath: 'pages/admin/finance',
    iconType: 'unocss',
    icon: 'i-carbon-money',
  },
  {
    text: '我的',
    pagePath: 'pages/admin/profile',
    iconType: 'unocss',
    icon: 'i-carbon-user',
  },
]

/**
 * 是否启用 tabbar 缓存
 * NATIVE_TABBAR(1) 和 CUSTOM_TABBAR(2) 时，需要tabbar缓存
 */
export const tabbarCacheEnable
  = [TABBAR_STRATEGY_MAP.NATIVE_TABBAR, TABBAR_STRATEGY_MAP.CUSTOM_TABBAR].includes(selectedTabbarStrategy)

/**
 * 是否启用自定义 tabbar
 * CUSTOM_TABBAR(2) 时，启用自定义tabbar
 */
export const customTabbarEnable = [TABBAR_STRATEGY_MAP.CUSTOM_TABBAR].includes(selectedTabbarStrategy)

/**
 * 是否需要隐藏原生 tabbar
 * CUSTOM_TABBAR(2) 时，需要隐藏原生tabbar
 */
export const needHideNativeTabbar = selectedTabbarStrategy === TABBAR_STRATEGY_MAP.CUSTOM_TABBAR

const _tabbarList = customTabbarEnable ? customTabbarList.map(item => ({ text: item.text, pagePath: item.pagePath })) : nativeTabbarList
export const tabbarList = customTabbarEnable ? customTabbarList : nativeTabbarList

const _tabbar: TabBar = {
  // #ifdef MP-ALIPAY
  customize: !!needHideNativeTabbar,
  // #endif
  // 只有微信小程序支持 custom。App 和 H5 不生效
  custom: selectedTabbarStrategy === TABBAR_STRATEGY_MAP.CUSTOM_TABBAR,
  color: '#999999',
  selectedColor: '#018d71',
  backgroundColor: '#F8F8F8',
  borderStyle: 'black',
  height: '50px',
  fontSize: '10px',
  iconWidth: '24px',
  spacing: '3px',
  list: _tabbarList as unknown as TabBar['list'],
}

export const tabBar = tabbarCacheEnable ? _tabbar : undefined

import type { IHuimaidanOrderPaymentEnvelope, IHuimaidanOrderPaymentResult } from './huimaidan.pay'
import type { CustomRequestOptions } from '@/http/types'
import { http } from '@/http/http'
import { normalizeMediaUrl } from '@/utils'
import { normalizeHuimaidanOrderPayment } from './huimaidan.pay'

// ==================== 类型定义 ====================

// ---------- 商户信息 ----------

/** 商户评分详情 */
export interface IStoreRatingDetail {
  product_score: number
  service_score: number
  postage_score: number
}

/** 商户设施信息 */
export interface IStoreFacilities {
  has_large_table: boolean
  has_baby_chair: boolean
  can_phone_reserve: boolean
  is_non_smoking: boolean
}

/** 营业时间项 */
export interface IStoreBusinessHours {
  day: string
  time: string
}

/** 商户列表项（列表/详情 display 共用） */
export interface IStoreItem {
  mer_id: number
  mer_name: string
  mer_avatar: string
  mer_address: string
  category_id: number
  category_name: string
  city_id: number
  city_name: string
  longitude: string
  latitude: string
  business_status: number
  business_status_text: string
  has_discount: boolean
  discount_label: string | null
  login_required?: number
  distance_km?: string
  distance?: string
  // 新增展示字段
  phone?: string
  rating?: number
  rating_detail?: IStoreRatingDetail
  real_sales?: number
  configured_sales?: number
  sales?: number
  sales_text?: string | null
  store?: string
  store_branch_name?: string
  price_per_person?: number
  price_per_person_text?: string | null
  business_hours?: IStoreBusinessHours[]
  facilities?: IStoreFacilities
  facility_tags?: string[]
  promo_image?: string
  slogan?: string | null
  settled_years?: number
  settled_years_text?: string | null
}

/** 商户列表响应 */
export interface IStoreListResponse {
  count: number
  list: IStoreItem[]
}

/** 商户详情 - merchant 字段 */
export interface IStoreMerchant {
  mer_id: number
  mer_name: string
  mer_avatar: string
  mer_banner: string
  mer_info: string
  mer_keyword: string
  mer_address: string
  category_id: number
  city_id: number
  city_name: string
  status: number
  mer_state: number
  long: string
  lat: string
  longitude?: string
  latitude?: string
  // 新增展示字段（同 IStoreItem）
  phone?: string
  mer_phone?: string
  rating?: number
  rating_detail?: IStoreRatingDetail
  real_sales?: number
  configured_sales?: number
  sales?: number
  sales_text?: string | null
  store?: string
  store_branch_name?: string
  price_per_person?: number
  price_per_person_text?: string | null
  business_hours?: IStoreBusinessHours[]
  facilities?: IStoreFacilities
  facility_tags?: string[]
  promo_image?: string
  slogan?: string | null
  settled_years?: number
  settled_years_text?: string | null
}

/** 商户详情 - 会员优惠规则 */
export interface IStoreRule {
  rule_type: number
  member_level: number
  member_level_name: string
  member_discount: string
}

/** 商户门店项 */
export interface IStoreBranch {
  id?: number
  mer_id: number
  name?: string
  mer_name: string
  branch_name?: string
  store_branch_name?: string
  address?: string
  mer_address?: string
  phone: string
  longitude: string
  latitude: string
}

/** 商户详情响应 */
export interface IStoreDetailResponse {
  merchant: IStoreMerchant
  rules: IStoreRule[]
  display: IStoreItem
  branches?: IStoreBranch[]
}

/** 商户分类项 */
export interface IStoreCategory {
  merchant_category_id: number
  category_name: string
}

/** 店铺分组树形节点 */
export interface IStoreGroupTreeNode {
  /** 分组ID（用于筛选店铺） */
  store_group_id: number
  /** 父级ID（0=一级分类） */
  pid: number
  /** 分类名称 */
  name: string
  /** 显示名称（同name） */
  label: string
  /** 值（同store_group_id） */
  value: number
  /** 是否禁用 */
  disabled: boolean
  /** DIY模板ID */
  diy_temp_id: number
  /** 子分类列表 */
  children?: IStoreGroupTreeNode[]
}

/** 开通城市项 */
export interface IStoreCity {
  id: number
  name: string
  code: string
}

/** 腾讯地图逆地址解析结果 */
export interface ILbsGeocoderResult {
  address?: string
  formatted_addresses?: {
    recommend?: string
    rough?: string
  }
  address_component?: {
    province?: string
    city?: string
    district?: string
    street?: string
    street_number?: string
  }
  location?: {
    lat?: number
    lng?: number
  }
}

/** 筛选配置项选项 */
export interface IFilterOption {
  id: number
  name: string
  value: number | string
  code?: string
}

/** 筛选配置项 */
export interface IFilterItem {
  id: number
  name: string
  key: string
  sort: number
  options: IFilterOption[]
}

/** 订单统计 */
export interface IOrderStatistics {
  unpaid: number
  completed: number
  refund: number
}

/** 通用分页响应 */
export interface IPaginatedResponse<T> {
  count: number
  list: T[]
}

/** 惠买单订单列表查询参数 */
export interface IOrderListParams {
  paid?: 0 | 1
  date?: string
  page?: number
  limit?: number
  /** 订单状态筛选，用于退款/售后等状态 */
  status?: number | string
}

/** 惠买单订单商户快照 */
export interface IOrderMerchantSnapshot {
  mer_id?: number
  mer_name?: string
  mer_avatar?: string
  mer_address?: string
  store_branch_name?: string
}

/** 惠买单订单列表项 */
export interface IOrderListItem {
  order_id: number
  order_sn?: string
  group_order_id?: number
  create_time?: string
  pay_time?: string
  pay_type?: number | string
  pay_price?: number | string
  paid?: number | boolean
  status?: number
  status_text?: string
  mark?: string
  merchant?: IOrderMerchantSnapshot
  discount?: IDiscountSnapshot
  coupon_price?: number | string
  total_price?: number | string
  amount?: number | string
  discount_amount?: number | string
  mer_id?: number
  mer_name?: string
}

/** 支付结果 */
export interface IPayResult {
  paid: boolean
  orderId: number
  storeOrderId: number
  payTime: string
}

/** 可用优惠券 */
export interface IUsableCoupon {
  id: number
  couponId: number
  name: string
  amount: string
  threshold: string
  condition: string
  expireTime: string
  usedTime: string
  status: string
}

/** 可用优惠券列表响应 */
export interface IUsableCouponResponse {
  count: number
  total: number
  list: IUsableCoupon[]
}

/** 可领取的优惠券项 */
export interface ICouponItem {
  coupon_id: number
  title: string
  coupon_price: string | number
  use_min_price: string | number
  type: number // 0:店铺券 1:商品券 10:平台通用券 11:平台品类券 12:平台跨店券
  send_type: number // 0:领取 1:消费满赠 2:新人券 3:买赠 4:首单赠送 5:会员券 6:后台赠送
  coupon_type: number // 0:领取后N天内 1:指定时间段
  coupon_time: number
  is_timeout: number // 0:不限时 1:限时
  start_time: string
  end_time: string
  is_limited: number // 0:不限量 1:限量
  total_count: number
  remain_count: number
  mer_id: number
  sort: number
  status: number
  mer_name?: string
  is_trader?: number
  issue?: any // null=未领取，有值=已领取
  ProductLst?: any[]
}

/** 可领取的优惠券列表响应 */
export interface ICouponListResponse {
  count: number
  list: ICouponItem[]
}

/** 收藏状态 */
export interface ICollectionStatus {
  isCollected: boolean
}

export interface ICollectionListParams {
  page?: number
  limit?: number
  store_group_id?: number
  order?: 'default' | 'location'
  latitude?: string | number
  longitude?: string | number
  distance?: string | number
}

/** 收藏商户原始项 */
export interface ICollectionMerchantItem {
  relation_id?: number
  type_id: number
  type?: number
  merchant?: Partial<IStoreItem & IStoreMerchant> & { service_phone?: string }
}

/** 我的优惠券原始项 */
export interface IMyCouponItem {
  id?: number
  coupon_user_id?: number
  coupon_id?: number
  couponId?: number
  coupon_title?: string
  name?: string
  title?: string
  coupon_price?: number | string
  amount?: number | string
  use_min_price?: number | string
  threshold?: number | string
  end_time?: string
  expireTime?: string
  use_time?: string
  usedTime?: string
  status?: number | string
  coupon?: {
    coupon_id?: number
    title?: string
    coupon_title?: string
    coupon_price?: number | string
    use_min_price?: number | string
    end_time?: string
  }
  merchant?: Partial<IStoreItem & IStoreMerchant> & { service_phone?: string }
}

/** 惠买单店铺浏览历史原始项 */
export interface IBrowsingHistoryItem {
  history_id: number
  mer_id: number
  browseTime: string
  visitCount: number
  shop: Partial<IStoreItem & IStoreMerchant> & { service_phone?: string }
}

export interface IBrowsingHistoryParams {
  page?: number
  limit?: number
  latitude?: number | string
  longitude?: number | string
  store_group_id?: number
}

/** 创建待支付订单请求参数 */
export interface IOrderPrepareParams {
  mer_id: number
  amount: string
  useMemberDiscount?: boolean
  mark?: string
}

/** 优惠规则快照 */
export interface IDiscountSnapshot {
  rule_id: number
  rule_type: number
  rule_type_label: string
  title: string
  original_amount: string
  pay_amount: string
  saved_amount: string
  discount_rate?: string
  discount_type_label?: string
  discount_rule?: string
  member_discount_enabled?: boolean
  coupon_user_id?: number
  coupon_id?: number
  coupon_deduction_amount?: string
  integral?: number
  integral_deduction_amount?: string
  platform_bear_coupon_amount?: string
  platform_bear_integral_amount?: string
}

/** 创建待支付订单响应 */
export interface IOrderPrepareResult {
  group_order_id: number
  order_id: number
  order_sn: string
  pay_price: string
  discount: IDiscountSnapshot
}

/** 创建订单并支付请求参数（支持优惠抵扣） */
export interface IOrderCreateParams {
  mer_id: number
  amount: string
  pay_type: string
  couponId?: number
  usePoints?: boolean
  useMemberDiscount?: boolean
  mark?: string
  return_url?: string
}

/** 创建订单并支付响应 */
export type IOrderCreateResult = IHuimaidanOrderPaymentResult

/** 合并下单请求参数（优惠金额+不参与优惠金额） */
export interface IOrderCreateCombinedParams {
  /** 商户ID */
  mer_id: number
  /** 优惠金额（支持会员折扣、优惠券、积分），至少一个金额>0 */
  discount_amount: string
  /** 不参与优惠金额（不使用任何优惠），至少一个金额>0 */
  no_discount_amount: string
  /** 支付方式：balance/weixin/routine/h5/alipay/alipayQr/weixinQr */
  pay_type: string
  /** 优惠券ID（只针对优惠金额部分） */
  couponId?: number
  /** 是否使用积分（只针对优惠金额部分） */
  usePoints?: boolean
  /** 是否使用会员折扣（默认true，只针对优惠金额部分） */
  useMemberDiscount?: boolean
  /** 备注（最多128字） */
  mark?: string
}

/** 合并下单响应结果 */
export type IOrderCreateCombinedResult = IHuimaidanOrderPaymentResult

/** 订单详情 */
export interface IOrderDetail {
  order_id: number
  order_sn?: string
  mer_id?: number
  mer_name?: string
  amount?: string
  pay_price?: string
  discount_amount?: string
  total_price?: string
  coupon_price?: string
  status?: number
  status_text?: string
  pay_type?: number | string
  paid?: number | boolean
  pay_time?: string
  create_time?: string
  mark?: string
  merchant?: IOrderMerchantSnapshot
  discount?: IDiscountSnapshot
}

/** 用户资产聚合 */
export interface IUserAssets {
  commission: string
  points: number
  couponCount: number
  vipLevel: number
}

/** 用户权益摘要 */
export interface IUserBenefit {
  can_use: boolean
  benefit_text: string
  order_count: number
  pay_amount: string
  saved_amount: string
}

export interface ISpreadPoster {
  qrcode?: string
  poster?: Array<string | Record<string, any>>
  nickname?: string
  mark?: string
}

export interface ISpreadTeamMember {
  uid: number
  avatar?: string
  nickname?: string
  pay_count?: number
  pay_price?: number | string
  spread_count?: number
  spread_time?: string
}

export interface ISpreadTeamListParams {
  page?: number
  limit?: number
  level?: 1
  keyword?: string
  sort?: string
}

export type ISpreadTeamListResponse = IPaginatedResponse<ISpreadTeamMember>

function normalizeStoreItem<T extends Partial<IStoreItem | IStoreMerchant>>(item: T): T {
  if (!item) {
    return item
  }
  return {
    ...item,
    mer_avatar: normalizeMediaUrl(item.mer_avatar),
    mer_banner: normalizeMediaUrl((item as Partial<IStoreMerchant>).mer_banner),
    promo_image: normalizeMediaUrl(item.promo_image),
  }
}

function normalizeStoreListResponse(data: IStoreListResponse): IStoreListResponse {
  return {
    ...data,
    list: (data.list || []).map(item => normalizeStoreItem(item)),
  }
}

function normalizeStoreDetailResponse(data: IStoreDetailResponse): IStoreDetailResponse {
  return {
    ...data,
    merchant: normalizeStoreItem(data.merchant),
    display: normalizeStoreItem(data.display),
  }
}

/** 优惠试算请求参数 */
export interface IDiscountCalculateParams {
  mer_id: number
  amount: string
  useMemberDiscount?: boolean
}

// ==================== API 接口 ====================

/**
 * 获取筛选条件（距离、分类、城市、排序配置）
 * 登录态：不强制
 */
export function getStoreFilters() {
  return http.get<IFilterItem[]>('/api/huimaidan/store/filters')
}

/**
 * 获取商户列表
 * 登录态：可选（已登录会返回会员优惠标签）
 */
export function getStoreList(params: {
  keyword?: string
  category_id?: number
  store_group_id?: number
  city_id?: number
  order?: 'default' | 'location'
  latitude?: string | number
  longitude?: string | number
  distance?: string | number
  page?: number
  limit?: number
}) {
  return http.get<IStoreListResponse>('/api/huimaidan/store/list', params).then(normalizeStoreListResponse)
}

/**
 * 获取附近商户列表
 * 登录态：可选
 * latitude、longitude 必填
 */
export function getStoreNearby(params: {
  latitude: string | number
  longitude: string | number
  category_id?: number
  city_id?: number
  distance?: string | number
  page?: number
  limit?: number
}) {
  return http.get<IStoreListResponse>('/api/huimaidan/store/nearby', params).then(normalizeStoreListResponse)
}

/**
 * 获取商户详情
 * 登录态：可选（已登录返回会员优惠规则）
 */
export function getStoreDetail(merId: number, params?: { latitude?: number, longitude?: number }) {
  return http.get<IStoreDetailResponse>(`/api/huimaidan/store/detail/${merId}`, { params }).then(normalizeStoreDetailResponse)
}

/**
 * 获取同一商户下的门店列表
 * 登录态：不强制
 */
export function getStoreBranches(merId: number) {
  return http.get<IStoreBranch[]>(`/api/huimaidan/store/branches/${merId}`)
}

/**
 * 获取商户分类列表
 * 登录态：不强制
 */
export function getStoreCategories() {
  return http.get<IStoreCategory[]>('/api/huimaidan/store/categories')
}

/**
 * 获取店铺分组树形结构
 * 用于分类导航展示：全部分类 -> 一级分类 -> 二级分类
 * 登录态：不强制
 */
export function getStoreGroupTree() {
  return http.get<IStoreGroupTreeNode[]>('/api/store/group/tree')
}

/**
 * 获取店铺分组级联选项（方案A，推荐）
 * 用于分类导航展示：全部分类 -> 一级分类 -> 二级分类
 * 登录态：不强制
 */
export function getStoreGroupOptions() {
  return http.get<IStoreGroupTreeNode[]>('/api/store/group/options')
}

/**
 * 获取开通城市列表
 * 登录态：不强制
 */
export function getStoreCities() {
  return http.get<IStoreCity[]>('/api/huimaidan/store/cities')
}

/**
 * 经纬度逆地址解析
 * location 格式：latitude,longitude
 */
export function getLbsGeocoder(params: { location: string }) {
  return http.get<ILbsGeocoderResult>('/api/lbs/geocoder', params)
}

/**
 * 地址解析
 * region 示例：北京市；address 示例：北京市 朝阳区
 */
export function getLbsAddress(params: { region: string, address: string }) {
  return http.get<ILbsGeocoderResult>('/api/lbs/address', params)
}

/**
 * 获取订单统计（我的买单状态数量）
 * 登录态：需要
 */
export function getOrderStatistics() {
  return http.get<IOrderStatistics>('/api/huimaidan/order/statistics')
}

/**
 * 查询支付结果
 * 登录态：需要
 * @param id 订单ID (store_order.order_id 或 store_group_order.group_order_id)
 */
export function getPayResult(id: number) {
  return http.get<IPayResult>(`/api/huimaidan/order/pay_result/${id}`)
}

/**
 * 获取惠买单订单列表
 * 登录态：需要
 */
export function getOrderList(params: IOrderListParams) {
  return http.get<IPaginatedResponse<IOrderListItem>>('/api/huimaidan/order/list', params)
}

/**
 * 查询下单可用优惠券
 * 登录态：需要
 * @param params shopId/mer_id: 商户ID, amount: 订单金额, page: 页码, limit: 每页数量
 */
export function getUsableCoupons(params: {
  shopId: number
  amount: number
  page?: number
  limit?: number
}) {
  return http.get<IUsableCouponResponse>('/api/coupon/usable', params)
}

/**
 * 查询商户收藏状态
 * 登录态：需要
 * @param shopId 商户ID
 */
export function checkCollection(shopId: number) {
  return http.get<ICollectionStatus>(`/api/user/relation/check/${shopId}`)
}

/**
 * 添加收藏
 * 登录态：需要
 * @param shopId 商户ID
 */
export function addCollection(shopId: number) {
  return http.post('/api/user/relation/create', {
    type_id: shopId,
    type: 10,
  })
}

/**
 * 取消收藏
 * 登录态：需要
 * @param shopId 商户ID
 */
export function removeCollection(shopId: number) {
  return http.post('/api/user/relation/delete', {
    type_id: shopId,
    type: 10,
  })
}

/**
 * 获取收藏列表
 * 登录态：需要
 */
export function getCollectionList(params?: ICollectionListParams) {
  return http.get<IPaginatedResponse<ICollectionMerchantItem>>('/api/user/relation/merchant/lst', params)
}

/**
 * 获取我的优惠券列表
 * 登录态：需要
 */
export function getMyCoupons(params: {
  status?: number | string
  statusTag?: number | string
  page?: number
  limit?: number
}) {
  return http.get<IPaginatedResponse<IMyCouponItem>>('/api/coupon/list', params)
}

/**
 * 获取可领取的优惠券列表（平台级）
 * 登录态：不强制，已登录会返回 issue 字段
 * @param params 筛选参数
 */
export function getCouponList(params?: {
  type?: number
  mer_id?: number
  product?: number
  send_type?: number
  page?: number
  limit?: number
}) {
  return http.get<ICouponListResponse>('/api/coupon/getlst', params)
}

/**
 * 领取优惠券
 * 登录态：需要
 * @param couponId 优惠券ID
 */
export function receiveCoupon(couponId: number) {
  return http.post<string>(`/api/coupon/receive/${couponId}`)
}

// ==================== 商户优惠券（预留扩展） ====================
// 商户优惠券接口暂不对接，预留以下接口供后续使用：
//
// /**
//  * 获取商户优惠券列表
//  * 登录态：不强制
//  * @param merId 商户ID
//  * @param all 是否包含已过期 1:包含 0:仅可用
//  */
// export function getStoreCoupons(merId: number, all?: number) {
//   return http.get(`/api/coupon/store/${merId}`, { all })
// }
//
// 商户优惠券页面入口：
// /pages/store/home/coupon?mer_id={商户ID}

/**
 * 获取浏览足迹
 * 登录态：需要
 */
export function getBrowsingHistory(params: IBrowsingHistoryParams) {
  return http.get<IPaginatedResponse<IBrowsingHistoryItem>>('/api/huimaidan/user/merchant_history', params)
}

/**
 * 删除单条浏览足迹
 * 登录态：需要
 */
export function deleteBrowsingHistory(id: number) {
  return http.post(`/api/huimaidan/user/merchant_history/delete/${id}`)
}

/**
 * 批量删除浏览足迹
 * 登录态：需要
 */
export function batchDeleteBrowsingHistory(historyIds: number[]) {
  return http.post('/api/huimaidan/user/merchant_history/batch_delete', {
    history_ids: historyIds,
  })
}

/**
 * 清空浏览足迹
 * 登录态：需要
 */
export function clearBrowsingHistory() {
  return http.post('/api/huimaidan/user/merchant_history/batch_delete', {
    clear: 1,
  })
}

/**
 * 记录小程序访问页面
 * 登录态：可选，未登录后端直接 success
 */
export function recordVisit(page: string) {
  return http.post('/api/common/visit', {
    page,
    type: 'routine',
  })
}

/**
 * 创建待支付订单（惠买单下单非支付）
 * 登录态：需要
 * @param params 商户ID、消费金额、备注
 */
export function prepareOrder(params: IOrderPrepareParams) {
  return http.post<IOrderPrepareResult>('/api/huimaidan/order/prepare', params)
}

/**
 * 优惠试算（只计算商户/会员折扣口径）
 * 登录态：需要
 * @param params 商户ID、消费金额、是否参与会员折扣
 */
export function calculateDiscount(params: IDiscountCalculateParams) {
  return http.post<IDiscountSnapshot>('/api/huimaidan/discount/calculate', params)
}

/**
 * 创建订单并支付（支持优惠券和积分抵扣）
 * 登录态：需要
 * @param params 商户ID、消费金额、支付方式、优惠券ID、是否使用积分、是否参与会员折扣、备注
 */
export function createOrder(params: IOrderCreateParams) {
  return http.post<IHuimaidanOrderPaymentEnvelope>('/api/huimaidan/order/create', params)
    .then(res => normalizeHuimaidanOrderPayment(res))
}

/**
 * 合并下单（优惠金额+不参与优惠金额）
 * 登录态：需要
 * @param params 商户ID、优惠金额、不参与优惠金额、支付方式、优惠券ID、是否使用积分、是否参与会员折扣、备注
 */
export function createCombinedOrder(params: IOrderCreateCombinedParams) {
  return http.post<IHuimaidanOrderPaymentEnvelope>('/api/huimaidan/order/createCombined', params)
    .then(res => normalizeHuimaidanOrderPayment(res))
}

/**
 * 获取订单详情
 * 登录态：需要
 * @param orderId 订单ID
 */
export function getOrderDetail(orderId: number) {
  return http.get<IOrderDetail>(`/api/huimaidan/order/detail/${orderId}`)
}

/**
 * 获取用户资产聚合（佣金、积分、优惠券、VIP等级）
 * 登录态：需要
 */
export function getUserAssets() {
  return http.get<IUserAssets>('/api/huimaidan/user/assets')
}

/**
 * 获取小程序分享海报
 * 登录态：需要
 */
export function getSpreadPoster(options?: Partial<CustomRequestOptions>) {
  return http.get<ISpreadPoster>('/api/user/v2/spread_image', { type: 'routine' }, undefined, options)
}

/**
 * 获取我的团队一级邀请用户列表
 * 登录态：需要
 */
export function getSpreadTeamList(params: ISpreadTeamListParams) {
  return http.get<ISpreadTeamListResponse>('/api/user/spread_list', params)
}

/**
 * 获取用户权益摘要（惠买单专属权益）
 * 登录态：需要
 */
export function getUserBenefit() {
  return http.get<IUserBenefit>('/api/huimaidan/user/benefit')
}

// ==================== 系统配置 ====================

/**
 * 获取小程序全局配置（含 coupon_tutorial_video 等）
 * 登录态：不强制
 */
export function getAppConfig() {
  return http.get<Record<string, any>>('/api/config')
}

// ==================== 协议 ====================

/** 协议 key 常量（与后端 CacheRepository 对应） */
export const AGREEMENT_KEY = {
  /** 用户协议 */
  USER_AGREE: 'sys_user_agree',
  /** 隐私政策 */
  USER_PRIVACY: 'sys_userr_privacy',
} as const

/** 协议内容响应：{ title, [key]: '<富文本html>' } */
export interface IAgreementResult {
  title: string
  [key: string]: string
}

/**
 * 获取协议内容（用户协议 / 隐私政策 等）
 * 登录态：不强制（登录页未登录可调用）
 * @param key 协议 key，见 AGREEMENT_KEY
 */
export function getAgreement(key: string) {
  return http.get<IAgreementResult>(`/api/agreement/${key}`)
}

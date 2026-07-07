import { http } from '@/http/http'

// ==================== 类型定义 ====================

/** 商户登录请求参数 */
export interface IMerLoginParams {
  account: string
  password: string
  code?: string
  key?: string
}

/** 商户登录响应 */
export interface IMerLoginRes {
  token: string
  exp: number
  admin: {
    merchant_admin_id: number
    mer_id: number
    account: string
    real_name: string
    phone: string
    status: number
    level: number
    roles: string
  }
}

/** 图形验证码响应 */
export interface ICaptchaRes {
  key: string
  captcha: string
}

/** 商户信息 */
export interface IMerInfo {
  mer_id: number
  real_name: string
  mer_phone: string
  mer_avatar: string
  mer_banner: string
  mer_info: string
  mer_address: string
  mer_state: number
  status: number
  mer_certificate: any[]
  services_type: number
  huimaidan_profile?: {
    branch_name: string
    configured_sales: number
    per_capita: number
    business_hours: string
    has_large_table: number
    has_baby_chair: number
    can_phone_reserve: number
    is_non_smoking: number
    promo_image: string
    slogan: string
  }
}

// ==================== 财务模块类型定义 ====================

/** 财务概览统计 */
export interface IFinanceOverview {
  /** 累计收款金额 */
  totalReceived: number
  /** 昨日新增收款金额 */
  yesterdayReceived: number
  /** 本月新增收款金额 */
  monthReceived: number
  /** 今日收款金额 */
  todayReceived: number
  /** 累计退款金额 */
  totalRefund: number
  /** 昨日新增退款金额 */
  yesterdayRefund: number
  /** 本月新增退款金额 */
  monthRefund: number
  /** 今日退款金额 */
  todayRefund: number
  /** 今日订单数 */
  todayOrderCount: number
  /** 退款订单数 */
  refundOrderCount: number
  /** 全部订单数（已支付） */
  allOrderCount: number
}

/** 销售额度信息 */
export interface IFinanceQuota {
  /** 已销售额度（累计收款金额） */
  salesQuota: number
  /** 总额度（0表示无限制） */
  totalQuota: number
}

/** 余额明细记录项 */
export interface IFinanceRecord {
  /** 记录ID */
  id: number
  /** 类型：income=收入，expense=支出 */
  type: 'income' | 'expense'
  /** 交易金额（正数） */
  amount: number
  /** 交易后余额（暂未实现，返回0） */
  balance: number
  /** 交易说明/备注 */
  mark: string
  /** 交易时间 */
  create_time: string
}

/** 余额明细查询参数 */
export interface IFinanceRecordsParams {
  /** 页码，默认1 */
  page?: number
  /** 每页数量，默认10 */
  limit?: number
  /** 筛选类型：income=收入，expense=支出，不传则全部 */
  type?: 'income' | 'expense'
  /** 日期筛选，格式：Y-m-d */
  date?: string
  /** 关键词搜索（订单号、流水号） */
  keyword?: string
}

/** 通用分页响应 */
export interface IMerPaginatedResponse<T> {
  count: number
  list: T[]
}

// ==================== 提现模块类型定义 ====================

/** 提现概览信息 */
export interface ISettlementOverview {
  /** 商户ID */
  mer_id: number
  /** 结算模式 */
  settlement_mode: number
  /** 可提现余额 */
  mer_money: string
  /** 提现手续费率(%) */
  withdraw_rate: string
  /** 收款账户类型: 2=微信 3=支付宝 */
  account_type: number
  /** 账户类型文本 */
  account_type_label: string
  /** 是否已配置收款账户 */
  has_account: boolean
  /** 是否有未完成的提现申请 */
  has_unfinished_apply: number
  /** 当前提现申请信息 */
  current: IWithdrawCurrentApply | null
}

/** 当前提现申请信息 */
export interface IWithdrawCurrentApply {
  /** 提现记录ID */
  financial_id: number
  /** 提现单号 */
  financial_sn: string
  /** 商户ID */
  mer_id: number
  /** 提现后余额 */
  mer_money: string
  /** 提现金额 */
  extract_money: string
  /** 提现方式: 2=微信 3=支付宝 */
  financial_type: number
  /** 打款状态: 0=待打款 1=已打款 */
  financial_status: number
  /** 审核状态: 0=处理中 1=通过 -1=拒绝 */
  status: number
  /** 备注 */
  mark: string
  /** 创建时间 */
  create_time: string
}

/** 收款账户保存参数 */
export interface ISaveAccountParams {
  /** 账户类型: 2=微信 3=支付宝 */
  financial_type: number
  /** 收款人姓名 */
  name: string
  /** 微信号（微信必填） */
  wechat?: string
  /** 微信收款二维码URL（微信必填） */
  wechat_code?: string
  /** 支付宝账号（支付宝必填） */
  alipay?: string
  /** 支付宝收款二维码URL（支付宝必填） */
  alipay_code?: string
}

/** 提现申请参数 */
export interface IWithdrawApplyParams {
  /** 提现金额（最低500元） */
  extract_money: string
  /** 提现方式：2=微信, 3=支付宝（不传则使用账户配置的类型） */
  financial_type?: number
  /** 备注说明 */
  mark?: string
}

/** 提现记录项 */
export interface IWithdrawRecord {
  /** 提现记录ID */
  id: number
  /** 提现金额 */
  amount: string
  /** 提现后余额 */
  balance: string
  /** 提现方式: 2=微信 3=支付宝 */
  financial_type: number
  /** 提现方式文本 */
  financial_type_text: string
  /** 银行卡后四位（仅银行卡提现） */
  card_last_four: string
  /** 状态: 0=处理中 1=成功 2=已拒绝 */
  status: number
  /** 状态文本 */
  status_text: string
  /** 备注 */
  mark: string
  /** 创建时间 */
  create_time: string
}

/** 提现记录查询参数 */
export interface IWithdrawRecordsParams {
  /** 页码，默认1 */
  page?: number
  /** 每页数量，默认10 */
  limit?: number
  /** 日期筛选，格式：Y-m-d */
  date?: string
  /** 状态筛选：0=处理中, 1=成功, 2=已拒绝 */
  status?: 0 | 1 | 2
  /** 账户类型筛选：2=微信, 3=支付宝 */
  account_type?: 2 | 3
}

// ==================== 结算模块类型定义 ====================

/** 结算统计数据 */
export interface ISettlementStats {
  /** 订单总数 */
  order_count: number
  /** 支付金额 */
  pay_amount: string
  /** 商户成本金额 */
  merchant_cost_amount: string
  /** 平台利润 */
  platform_profit: string
  /** 垫资池平台利润 */
  pool_platform_profit: string
  /** 提现收入金额 */
  withdraw_income_amount: string
  /** 提现手续费金额 */
  withdraw_fee_amount: string
}

/** 结算订单用户信息 */
export interface ISettlementOrderUser {
  uid: number
  nickname: string
  avatar: string
}

/** 结算订单列表项 */
export interface ISettlementOrder {
  /** 订单ID */
  order_id: number
  /** 订单号 */
  order_sn: string
  /** 商户ID */
  mer_id: number
  /** 用户ID */
  uid: number
  /** 支付金额 */
  pay_price: string
  /** 商户成本金额 */
  merchant_cost_amount: string
  /** 平台利润 */
  platform_profit: string
  /** 结算模式 */
  settlement_mode: number
  /** 支付状态 */
  paid: number
  /** 支付时间 */
  pay_time: string
  /** 创建时间 */
  create_time: string
  /** 用户信息 */
  user?: ISettlementOrderUser
}

/** 结算订单查询参数 */
export interface ISettlementOrdersParams {
  /** 页码，默认1 */
  page?: number
  /** 每页数量，默认10 */
  limit?: number
  /** 日期筛选，格式：Y-m-d 或日期范围 */
  date?: string
  /** 订单号搜索 */
  order_sn?: string
}

/** 结算订单列表响应 */
export interface ISettlementOrdersResponse {
  count: number
  list: ISettlementOrder[]
}

// ==================== 小时级收款趋势模块类型定义 ====================

/** 小时级收款趋势数据项 */
export interface IHourlyDataItem {
  /** 小时（0-23） */
  hour: number
  /** 小时标签，如 "00:00" */
  hour_label: string
  /** 该小时订单数 */
  order_count: number
  /** 该小时收款金额 */
  pay_amount: string
}

/** 小时级收款趋势响应 */
export interface IHourlyDataResponse {
  /** 查询日期 */
  date: string
  /** 24小时数据列表 */
  list: IHourlyDataItem[]
}

// ==================== 店铺二维码模块类型定义 ====================

/** 店铺二维码详情 */
export interface IStoreQrcodeDetail {
  /** 记录ID */
  id: number
  /** 商户ID */
  mer_id: number
  /** 商户名称 */
  mer_name: string
  /** 门店名称快照 */
  branch_name_snapshot: string
  /** 入口码 */
  entry_code: string
  /** scene值，用于联调排查 */
  scene_value: string
  /** scene类型 */
  scene_type: string
  /** 小程序页面路径 */
  page_path: string
  /** 状态：1=可用，0=禁用 */
  status: number
  /** 状态文本 */
  status_text: string
  /** 二维码图片URL（用于预览和下载） */
  qr_image_url: string
  /** 二维码图片路径 */
  qr_image_path: string
  /** 最近成功生成时间 */
  last_generated_at: string
  /** 最近生成状态：1=成功，0=失败 */
  last_generate_status: number
  /** 最近生成状态文本 */
  last_generate_status_text: string
  /** 最近生成失败原因 */
  last_generate_error: string
  /** 生成版本号 */
  generate_version: number
  /** 刷新次数 */
  refresh_count: number
  /** 最近扫码时间 */
  last_access_at: string
  /** 是否使用上一版成功二维码：1=是，0=否 */
  is_using_last_success: number
  /** 更新时间 */
  updated_at: string
}

// ==================== 登录相关 API ====================

/**
 * 获取图形验证码
 * 登录态：不需要
 */
export function getCaptcha() {
  return http.get<ICaptchaRes>('/mer/captcha')
}

/**
 * 商户登录（核心接口）
 * 登录态：不需要
 * @param params 账号、密码、验证码
 */
export function merLogin(params: IMerLoginParams) {
  return http.post<IMerLoginRes>('/mer/login', params)
}

/**
 * 退出登录
 * 登录态：需要
 */
export function merLogout() {
  return http.get<string>('/mer/logout')
}

/**
 * 获取商户信息
 * 登录态：需要
 */
export function getMerInfo() {
  return http.get<IMerInfo>('/mer/info')
}

// ==================== 财务模块 API ====================

/**
 * 获取财务概览统计
 * 包括累计收款、昨日/本月新增收款、累计退款、昨日/本月新增退款
 * 登录态：需要
 */
export function getFinanceOverview() {
  return http.get<IFinanceOverview>('/mer/huimaidan/finance/overview')
}

/**
 * 获取销售额度信息
 * 包括已销售额度和总额度
 * 登录态：需要
 */
export function getFinanceQuota() {
  return http.get<IFinanceQuota>('/mer/huimaidan/finance/quota')
}

/**
 * 获取余额明细列表
 * 支持分页和筛选
 * 登录态：需要
 * @param params 查询参数
 */
export function getFinanceRecords(params?: IFinanceRecordsParams) {
  return http.get<IMerPaginatedResponse<IFinanceRecord>>('/mer/huimaidan/finance/records', params)
}

// ==================== 提现模块 API ====================

/**
 * 获取提现概览信息
 * 包括可提现余额、提现手续费、收款账户状态等
 * 登录态：需要
 */
export function getSettlementOverview() {
  return http.get<ISettlementOverview>('/mer/huimaidan/settlement/overview')
}

/**
 * 获取当前未完成的提现申请详情
 * 登录态：需要
 */
export function getWithdrawCurrent() {
  return http.get<{ current: IWithdrawCurrentApply | null }>('/mer/huimaidan/settlement/withdraw/current')
}

/**
 * 保存收款账户
 * 配置微信或支付宝收款码
 * 登录态：需要
 * @param params 收款账户参数
 */
export function saveWithdrawAccount(params: ISaveAccountParams) {
  return http.post<string>('/mer/huimaidan/settlement/withdraw/account', params)
}

/**
 * 申请提现
 * 最低提现金额500元
 * 登录态：需要
 * @param params 提现申请参数
 */
export function applyWithdraw(params: IWithdrawApplyParams) {
  return http.post<string>('/mer/huimaidan/settlement/withdraw/apply', params)
}

/**
 * 获取提现记录列表（格式化版本）
 * 前端推荐使用此接口
 * 登录态：需要
 * @param params 查询参数
 */
export function getWithdrawRecords(params?: IWithdrawRecordsParams) {
  return http.get<IMerPaginatedResponse<IWithdrawRecord>>('/mer/huimaidan/settlement/withdraw/list', params)
}

/**
 * 上传收款码图片
 * 用于上传微信/支付宝收款二维码
 * 登录态：需要
 * @param filePath 本地文件路径
 */

// ==================== 店铺二维码模块 API ====================

/**
 * 获取店铺二维码详情
 * 后端根据当前登录商户的mer_id查询二维码
 * 如果记录不存在或图片缺失，后端会尝试生成微信小程序码
 * 登录态：需要（商户后台登录态）
 */
export function getStoreQrcodeDetail() {
  return http.get<IStoreQrcodeDetail>('/mer/huimaidan/store_qrcode/detail')
}
export function uploadQrcode(filePath: string) {
  return new Promise<{ url: string, src: string }>((resolve, reject) => {
    // 直接从 storage 读取 token，避免循环依赖（mer-token.ts 已导入 mer.ts）
    let token = ''
    try {
      const stored = uni.getStorageSync('mer_token_info')
      if (stored) {
        const parsed = JSON.parse(stored)
        const now = Date.now()
        const expTime = parsed.exp || 0
        if (parsed.token && expTime > now) {
          token = parsed.token
        }
      }
    }
    catch (e) {
      // ignore
    }

    uni.uploadFile({
      url: '/common/upload',
      filePath,
      name: 'file',
      header: {
        'X-Token': token,
      },
      success: (res) => {
        try {
          const data = typeof res.data === 'string' ? JSON.parse(res.data) : res.data
          const code = Number(data.code ?? data.status)
          if (res.statusCode >= 200 && res.statusCode < 300 && (code === 0 || code === 200)) {
            resolve(data.data || data)
          }
          else {
            reject(new Error(data.msg || data.message || '上传失败'))
          }
        }
        catch (e) {
          reject(new Error('上传失败，响应解析错误'))
        }
      },
      fail: (err) => {
        reject(err)
      },
    })
  })
}

// ==================== 结算模块 API ====================

/**
 * 获取结算统计数据
 * 包括订单总数、支付金额、商户成本、平台利润等
 * 登录态：需要
 * @param params 查询参数（date: 日期筛选，order_sn: 订单号搜索）
 */
export function getSettlementStats(params?: { date?: string, order_sn?: string }) {
  return http.get<ISettlementStats>('/mer/huimaidan/settlement/stats', params)
}

/**
 * 获取结算订单列表
 * 支持分页、日期筛选、订单号搜索
 * 登录态：需要
 * @param params 查询参数
 */
export function getSettlementOrders(params?: ISettlementOrdersParams) {
  return http.get<ISettlementOrdersResponse>('/mer/huimaidan/settlement/orders', params)
}

/**
 * 获取结算订单详情
 * 登录态：需要
 * @param orderId 订单ID
 */
export function getSettlementOrderDetail(orderId: number) {
  return http.get<ISettlementOrder>(`/mer/huimaidan/settlement/order/${orderId}`)
}

/**
 * 获取小时级收款趋势数据
 * 返回指定日期内每小时的收款金额和笔数，用于前端折线图展示
 * 登录态：需要
 * @param params 查询参数（date: 日期，格式 Y-m-d，默认今天）
 */
export function getSettlementHourly(params?: { date?: string }) {
  return http.get<IHourlyDataResponse>('/mer/huimaidan/settlement/hourly', params)
}

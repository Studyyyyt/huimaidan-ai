// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from './request'
/**
 * @description 订单 -- 列表
 */
export function orderListApi(data) {
  return request.get('store/order/lst', data)
}

/**
 * @description 订单 -- 表头
 */
export function chartApi(data) {
  return request.get('store/order/chart', data)
}
/**
 * @description 订单 -- 卡片
 */
export function cardListApi(data) {
  return request.get('store/order/title', data)
}
/**
 * @description 订单 -- 编辑
 */
export function orderUpdateApi(id, data) {
  return request.post(`store/order/update/${id}`, data)
}
/**
 * @description 订单 -- 发货
 */
export function orderDeliveryApi(id, data) {
  return request.post(`store/order/delivery/${id}`, data)
}
/**
 * @description 订单发货 -- 快递业务类型
 */
export function expressTypeApi() {
  return request.get(`store/order/kuaidi_coms`)
}
/**
 * @description 订单发货 -- 商家寄件立即计算快递费用
 */
export function calculateCost(id, data) {
  return request.get(`store/order/shipment/getPrice/${id}`, data)
}
/**
 * @description 订单 -- 详情
 */
export function orderDetailApi(id) {
  return request.get(`store/order/detail/${id}`)
}
/**
 * @description 预约订单 -- 派单
 */
export function orderDispatchApi(id,data) {
  return request.post(`store/order/reservation/dispatch/${id}`,data)
}
/**
 * @description 预约订单 -- 改派
 */
export function orderUpdateDispatchApi(id,data) {
  return request.post(`store/order/reservation/updateDispatch/${id}`,data)
}

/**
 * @description 预约订单 -- 改约
 */
export function orderRescheduleApi(id,data) {
  return request.post(`store/order/reservation/reschedule/${id}`,data)
}
/**
 * @description 订单 -- 核销
 */
export function orderReservationVerifyApi(id) {
  return request.post(`store/order/reservation/verify/${id}`)
}
/**
 * @description 订单 -- 获取商品日历month
 */
export function productShowMonthApi(id,data) {
  return request.get(`store/reservation/product/showMonth/${id}`,data)
}
/**
 * @description 订单 -- 获取商品日历day
 */
export function productShowDayApi(id,data) {
  return request.get(`store/reservation/product/showDay/${id}`,data)
}
/**
 * @description 配送订单 -- 派单
 */
export function deliveryDispatchApi(id,data) {
  return request.post(`store/order/delivery/dispatch/${id}`,data)
}
/**
 * @description 配送订单 -- 改派
 */
export function deliveryUpdateDispatchApi(id,data) {
  return request.post(`store/order/delivery/updateDispatch/${id}`,data)
}
/**
 * @description 配送订单 -- 确认送达
 */
export function deliveryConfirmApi(id) {
  return request.post(`store/order/delivery/confirm/${id}`)
}
/**
 * @description 配送订单 -- 同步配送订单
 */
export function orderSyncApi(data) {
  return request.post(`store/order/deliveryOrderSync`, data)
}
/**
 * @description 订单 -- 子订单
 */
export function getChildrenOrderApi(id) {
  return request.get(`store/order/children/${id}`)
}
/**
 * @description 订单 -- 记录
 */
export function orderLogApi(id, data) {
  return request.get(`store/order/log/${id}`, data)
}
/**
 * @description 订单 -- 备注from
 */
export function orderRemarkApi(id) {
  return request.get(`store/order/remark/${id}/form`)
}
/**
 * @description 订单 -- 删除
 */
export function orderDeleteApi(id) {
  return request.post(`store/order/delete/${id}`)
}
/**
 * @description 订单 -- 打印
 */
export function orderPrintApi(id) {
  return request.get(`store/order/printer/${id}`)
}
/**
 * @description 退款订单 -- 列表
 */
export function refundorderListApi(data) {
  return request.get('store/refundorder/lst', data)
}
/**
 * @description 退款订单 -- 详情
 */
export function refundorderDetailApi(id) {
  return request.get(`store/refundorder/detail/${id}`)
}
/**
 * @description 退款订单 -- 审核from
 */
export function refundorderStatusApi(id) {
  return request.get(`store/refundorder/status/${id}/form`)
}
/**
 * @description 退款订单 -- 备注from
 */
export function refundorderMarkApi(id) {
  return request.get(`store/refundorder/mark/${id}/form`)
}
/**
 * @description 退款订单 -- 记录
 */
export function refundorderLogApi(id,data) {
  return request.get(`store/refundorder/log/${id}`,data)
}
/**
 * @description 退款订单 -- 删除
 */
export function refundorderDeleteApi(id) {
  return request.get(`store/refundorder/delete/${id}`)
}
/**
 * @description 退款订单 -- 确认收货
 */
export function confirmReceiptApi(id, data) {
  return request.post(`store/refundorder/refund/${id}`, data)
}
/**
 * @description 获取物流信息
 */
export function getExpress(id) {
  return request.get(`store/order/express/${id}`)
}
/**
 * @description 退款单获取物流信息
 */
export function refundorderExpressApi(id) {
  return request.get(`store/refundorder/express/${id}`)
}
/**
 * @description 导出订单
 */
export function exportOrderApi(data) {
  return request.get(`store/order/excel`, data)
}
/**
 * @description 生成发货单
 */
export function exportInvoiceApi(data) {
  return request.get(`store/order/delivery_export`, data)
}
/**
 * @description 导出文件列表
 */
export function exportFileLstApi(data) {
  return request.get(`excel/lst`, data)
}
/**
 * @description 下载
 */
export function downloadFileApi(id) {
  return request.get(`excel/download/${id}`)
}
/**
 * @description 订单核销详情
 */
export function orderCancellationApi(code) {
  return request.get(`store/order/verify/${code}`)
}
/**
 * @description 订单核销
 */
 export function goCancellationApi(id, data) {
  return request.post(`store/order/verify/${id}`, data)
}
/**
 * @description 订单 -- 头部
 */
export function orderHeadListApi(data) {
  return request.get(`store/order/filtter`, data)
}
/**
 * @description 核销订单 -- 表头
 */
export function takeChartApi() {
  return request.get('store/order/takechart')
}
/**
 * @description 核销订单 -- 列表
 */
export function takeOrderListApi(data) {
  return request.get('store/order/takelst', data)
}
/**
 * @description 核销订单 -- 卡片
 */
export function takeCardListApi(data) {
  return request.get('store/order/take_title', data)
}
/**
 * @description 发票管理 -- 列表
 */
export function invoiceOrderListApi(data) {
  return request.get('store/receipt/lst', data)
}
/**
 * @description 发票 -- 备注from
 */
export function invoiceorderMarkApi(id) {
  return request.get(`store/receipt/mark/${id}/form`)
}
/**
 * @description 发票 -- 开票信息
 */
export function invoiceInfoApi(data) {
  return request.get(`store/receipt/set_recipt`, data)
}
/**
 * @description 发票 -- 开票
 */
export function invoiceApi(data) {
  return request.post(`store/receipt/save_recipt`, data)
}
/**
 * @description 发票 -- 详情
 */
export function invoiceDetailApi(id) {
  return request.get(`store/receipt/detail/${id}`)
}
/**
 * @description 发票 -- 编辑
 */
export function invoiceUpdateApi(id, data) {
  return request.post(`store/receipt/update/${id}`, data)
}
/**
 * @description 批量发货记录 -- 列表
 */
export function deliveryRecordListApi(data) {
  return request.get('store/import/lst', data)
}
/**
 * @description 批量发货记录 -- 详情
 */
export function deliveryRecordDetailApi(id, data) {
  return request.get(`store/import/detail/${id}`, data)
}
/**
 * @description 批量发货记录 -- 导出
 */
export function deliveryRecordImportApi(id,data) {
  return request.get(`store/import/excel/${id}`, data)
}
/**
 * @description 退款单 -- 导出
 */
export function refundListImportApi(data) {
  return request.get(`store/refundorder/excel`, data)
}
/**
 * @description 发送货 -- 物流公司列表
 */
export function expressLst() {
  return request.get(`expr/options`)
}
/**
 * @description 发送货 -- 电子面单列表
 */
export function exprTempsLst(data) {
  return request.get(`expr/temps`, data)
}
/**
 * @description 发送货 -- 批量发送货
 */
export function batchDeliveryApi(data) {
  return request.post(`store/order/delivery_batch`, data)
}
/**
 * @description 发送货 -- 电子面单默认数据
 */
export function getEleTempData() {
  return request.get(`serve/config`)
}
/**
 * @description 发送货 -- 门店列表
 */
 export function getStoreLst() {
  return request.get(`delivery/station/select`)
}
/**
 * @description 发送货 -- 门店列表
 */
 export function getDeliveryStoreLst() {
  return request.get(`delivery/station/options`)
}
/**
 * @description 同城配送  -- 订单列表
 */
 export function deliveryOrderLst(data) {
  return request.get(`delivery/order/lst`, data)
}
/**
 * @description 同城订单 -- 取消
 */
 export function deliveryOrderCancle(id) {
  return request.get(`delivery/order/cancel/${id}/form`)
}
/**
 * @description 同城配送  -- 充值记录列表
 */
 export function rechargeLst(data) {
  return request.get(`delivery/station/payLst`, data)
}
/**
 * @description 同城配送  -- 充值
 */
 export function rechargeInfoApi(data) {
  return request.get(`delivery/station/code`, data)
}
/**
 * @description 订单  -- 导出
 */
 export function storeOrderApi(data) {
  return request.get(`delivery/station/code`, data)
}
/**
 * @description 订单  -- 手动退款订单信息
 */
export function getRundProductApi(id) {
  return request.get(`store/refundorder/check/${id}`)
}
/**
 * @description 订单  -- 手动退款原因
 */
export function getRundMessageApi() {
  return request.get(`store/refundorder/refund_message`)
}
/**
 * @description 订单  -- 提交手动退款
 */
export function rundCreateApi(data) {
  return request.post(`store/refundorder/create`, data)
}
/**
 * @description 订单  -- 提交手动退款
 */
export function computeRefundPrice(data) {
  return request.post(`store/refundorder/compute`, data)
}
/**
 * @description 订单  -- 线下支付修改为支付成功
 */
export function offlinePay(id) {
  return request.post(`/store/order/offline/${id}`)
}
/*
 * @description 配送员管理 -- 下拉筛选
 */
export function deliveryPersonSelect() {
  return request.get(`delivery/service/options`)
}
/*
 * @description 订单详情 -- 电子面单复打
 */
export function orderReDriving(id) {
  return request.post(`store/order/repeat_dump/${id}`)
}
/*
 * @description 订单详情 -- 修改发货信息
 */
export function modifyOrderAddress(id) {
  return request.get(`store/order/collectCargo/${id}/form`)
}
/*
 * @description 订单详情 -- 打印配货单
 */
export function distributionOrder(data) {
  return request.get(`store/order/note`, data)
}
/*
 * @description 代客下单 -- 添加地址（城市区列表）
 */
export function getCityListApi(id) {
  return request.get(`v2/system/city/lst/${id}`)
}
/*
 * @description 代客下单 -- 余额支付获取验证码
 */
export function verifyCode(data) {
  return request.post(`store/behalf/orderVerify`, data)
}
/*
 * @description 代客下单 -- 商品分类
 */
export function getCategoryLst() {
  return request.get(`store/behalf/productCategory`)
}
/*
 * @description 代客下单 -- 商品列表
 */
export function getProductLst(data) {
  return request.get(`store/behalf/productList`, data)
}
/*
 * @description 代客下单 -- 商品详情
 */
export function getProductDetail(id) {
  return request.get(`store/behalf/productDetail/${id}`)
}
/*
 * @description 代客下单 -- 查询会员
 */
export function searchMembers(data) {
  return request.get(`store/behalf/userQuery`, data)
}
/*
 * @description 代客下单 -- 添加会员
 */
export function addMembersApi(data) {
  return request.post(`store/behalf/userCreate`, data)
}
/*
 * @description 代客下单 -- 地址列表
 */
export function orderAddressLst(data) {
  return request.get(`store/behalf/userAddressList`, data)
}
/*
 * @description 代客下单 -- 添加地址
 */
export function orderAddAddress(data) {
  return request.post(`store/behalf/userAddressCreate`, data)
}
/*
 * @description 代客下单 -- 购物车列表
 */
export function orderCartLst(data) {
  return request.get(`store/behalf/cartList`, data)
}
/*
 * @description 代客下单 -- 购物车添加
 */
export function orderCartAdd(data) {
  return request.post(`store/behalf/cartCreate`, data)
}
/*
 * @description 代客下单 -- 购物车修改
 */
export function orderCartChange(id,data) {
  return request.post(`store/behalf/cartChange/${id}`, data)
}
/*
 * @description 代客下单 -- 购物车单个删除
 */
export function orderCartDelete(id) {
  return request.delete(`store/behalf/cartDelete/${id}`)
}
/*
 * @description 代客下单 -- 购物车清空
 */
export function orderCartClear(data) {
  return request.post(`store/behalf/cartClear`, data)
}
/*
 * @description 代客下单 -- 购物车总数量
 */
export function orderCartNum(data) {
  return request.get(`store/behalf/cartCount`, data)
}
/*
 * @description 代客下单 -- 购物车单个改价
 */
export function orderCartUpdatePrice(id,data) {
  return request.post(`store/behalf/cartUpdatePrice/${id}`, data)
}
/*
 * @description 代客下单 -- 订单检查计算
 */
export function orderCheckCaluate(data) {
  return request.post(`store/behalf/orderCheck`, data)
}
/*
 * @description 代客下单 -- 支付配置
 */
export function orderPayConfig(data) {
  return request.post(`store/behalf/payConfig`, data)
}
/*
 * @description 代客下单 -- 创建订单
 */
export function orderCreate(data) {
  return request.post(`store/behalf/orderCreate`, data)
}
/*
 * @description 代客下单 -- 订单支付
 */
export function orderPayApi(id,data) {
  return request.post(`store/behalf/orderPay/${id}`, data)
}
/*
 * @description 代客下单 -- 余额支付验证码
 */
export function orderVerifyApi(data) {
  return request.get(`store/behalf/orderVerify`, data)
}
/*
 * @description 代客下单 -- 支付状态查询
 */
export function orderPayStatus(id, data) {
  return request.post(`store/behalf/payStatus/${id}`, data)
}
/*
 * @description 代客下单 -- 整单改价
 */
export function orderChangePrice(data) {
  return request.post(`store/behalf/cartBatchUpdatePrice`, data)
}
/*
 * @description 代客下单 -- 获取用户信息
 */
export function getUserInfo(data) {
  return request.get(`store/behalf/userInfo`, data)
}
/*
 * @description 预约服务 -- 获取预约日历信息
 */
export function getReservationList(data) {
  return request.get(`reservation/service/list`, data)
}
/*
 * @description 预约服务 -- 单独修改预约时间
 */
export function editReservationTime(id,data) {
  return request.post(`store/order/reservation/reservationTime/${id}`, data)
}

/**
 * @description 订单统计 -- 订单排行
 */
export function orderTopApi(data) {
  return request.get(`analytics/order/top`, data)
}

/**
 * @description 订单统计 -- 订单折线图
 */
export function orderLineChartApi(data) {
  return request.get(`analytics/order/line_chart`, data)
}

/**
 * @description 订单统计 -- 订单类型饼图
 */
export function orderTypePieApi(type,data) {
  return request.get(`analytics/order/pie_chart/${type}`, data)
}
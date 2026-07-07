// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 公共过滤器
export function filterEmpty(val) {
  let _result = '-'
  if (!val) {
    return _result
  }
  _result = val
  return _result
}
export function filterYesOrNo(value) {
  return value ? '是' : '否'
}

export function filterShowOrHide(value) {
  return value ? '显示' : '不显示'
}

export function filterShowOrHideForFormConfig(value) {
  return value === '‘0’' ? '显示' : '不显示'
}

export function filterYesOrNoIs(value) {
  return value ? '否' : '是'
}

/**
 * @description 支付状态
 */
export function paidFilter(status) {
  const statusMap = {
    0: '未支付',
    1: '已支付'
  }
  return statusMap[status]
}
/**
 * @description 订单支付类型
 */
export function payTypeFilter(status) {
  const statusMap = {
    '0': '余额',
    '1': '微信',
    '2': '微信',
    '3': '微信',
    '4': '支付宝',
    '5': '支付宝',
    '7': '线下支付'
  }
  return statusMap[status]
}

/**
 * @description 订单状态
 */
export function orderStatusFilter(status) {
  const statusMap = {
    '0': '待发货',
    '1': '待收货',
    '2': '待评价',
    '3': '已完成',
    '-1': '已退款',
    '9': '未成团',
    '10': '待付尾款',
    '11': '尾款过期未付'
  }
  return statusMap[status]
}
/**
 * @description 同城配送订单状态
 */
export function deliveryOrderStatusFilter(status) {
  const statusMap = {
    '0': '待配送',
    '1': '配送中',
    '2': '待评价',
    '3': '已完成',
    '-1': '已退款',
    '9': '未成团',
    '10': '待付尾款',
    '11': '尾款过期未付'
  }
  return statusMap[status]
}
// 预约商品的订单状态 上门订单
export function  reservationOrderStatusFilter(status) {
  const statusMap = {
    '-1': '已退款',
    '0': '派单中',
    '1': '待服务',
    '2': '待评价',
    '3': '已完成',
    '20':'服务中'
  }
  return statusMap[status]
}
// 预约商品的订单状态 到店订单enable_assigned =0无需派单
export function  reservationOrderStatusFilter1(obj) {
  // 无需派单
  let statusMap = {}
  if(obj.enable_assigned==0){
statusMap = {
    '-1': '已退款',
    '0': '待核销',
    '2': '待评价',
    '3': '已完成',

  }} else {
  statusMap = {
      '-1': '已退款',
      '0': '派单中',
      '1':'待核销',
      '2': '待评价',
      '3': '已完成',

    }
  }
  return statusMap[obj.status]
}

/**
 * @description 订单活动状态
 */
export function activityOrderStatus(status) {
  const statusMap = {
    '-1': '未完成',
    '10': '已完成',
    '0': '进行中'
  }
  return statusMap[status]
}
export function cancelOrderStatusFilter(status) {
  const statusMap = {
    '0': '待核销',
    '2': '待评价',
    '3': '已完成',
    '-1': '已退款',
    '10': '待付尾款',
    '11': '尾款过期未付'
  }
  return statusMap[status]
}
/**
 *
 * 支付方式
 */

export function orderPayType(type) {

  const typeMap = {
    '0': '余额支付',
    '1': '微信支付',
    '2': '小程序',
    '3': '微信支付',
    '4': '支付宝',
    '5': '支付宝扫码',
    '6': '微信扫码',
    '7': '线下支付',
    '8': '扫码枪支付'
  }
  return typeMap[type]
}
/**
 * @description 自提订单状态
 */
export function takeOrderStatusFilter(status) {
  const statusMap = {
    '0': '待核销',
    '1': '待提货',
    '2': '待评价',
    '3': '已完成',
    '-1': '已退款',
    '9': '未成团',
    '10': '待付尾款',
    '11': '尾款过期未付'
  }
  return statusMap[status]
}

/**
 * @description 退款单状态
 */
export function orderRefundFilter(status) {
  const statusMap = {
    '0': '待审核',
    '-1': '拒绝退款',
    '-2': '已取消',
    '1': '待退货',
    '2': '待收货',
    '3': '已退款',
    '4': '维权中'
  }
  return statusMap[status]
}

/**
 * @description 转账状态
 */
export function accountStatusFilter(status) {
  const statusMap = {
    0: '未转账',
    1: '已转账'
  }
  return statusMap[status]
}

/**
 * @description 订单对账类型
 */
export function reconciliationFilter(value) {
  return value > 0 ? '已对账' : '未对账'
}

/**
 * @description 对账状态
 */
export function reconciliationStatusFilter(status) {
  const statusMap = {
    0: '未确认',
    1: '已拒绝',
    2: '已确认'
  }
  return statusMap[status]
}
/**
 * @description 商品状态
 */
export function productStatusFilter(status) {
  const statusMap = {
    '0': '下架',
    '1': '上架显示',
    '-1': '平台关闭'
  }
  return statusMap[status]
}
/**
 * @description 秒杀商品状态
 */
export function seckillProductStatus(status) {
  const statusMap = {
    '0': '审核中',
    '1': '审核通过',
    '-1': '审核未通过',
    '-2': '下架',
  }
  return statusMap[status]
}
/**
 * @description 优惠券类型
 */
export function couponTypeFilter(status) {
  const statusMap = {
    0: '店铺券',
    1: '商品券'
  }
  return statusMap[status]
}
/**
 * @description 优惠券使用类型
 */
export function couponUseTypeFilter(status) {
  const statusMap = {
    0: '店铺券',
    1: '商品券',
    10: '平台通用券',
    11: '平台品类券',
    12: '平台跨店券'

  }
  return statusMap[status]
}
/**
 * @description 直播状态
 */
export function broadcastStatusFilter(status) {
  const statusMap = {
    101: '直播中',
    102: '未开始',
    103: '已结束',
    104: '禁播',
    105: '暂停',
    106: '异常',
    107: '已过期'
  }
  return statusMap[status]
}
/**
 * @description 直播审核状态
 */
export function liveReviewStatusFilter(status) {
  const statusMap = {
    '0': '未审核',
    '1': '微信审核中',
    '2': '审核通过',
    '-1': '审核未通过'
  }
  return statusMap[status]
}
/**
 * @description 直播间类型
 */
export function broadcastType(type) {
  const typeMap = {
    0: '手机直播',
    1: '推流'
  }
  return typeMap[type]
}
/**
 * @description 直播显示类型
 */
export function broadcastDisplayType(type) {
  const typeMap = {
    0: '竖屏',
    1: '横屏'
  }
  return typeMap[type]
}
/**
 * @description 是否关闭点赞、评论
 */
export function filterClose(value) {
  return value ? '✔' : '✖'
}
/**
 * @description 导出订单状态
 */
export function exportOrderStatusFilter(status) {
  const statusMap = {
    '0': '正在导出，请稍后再来',
    '1': '完成',
    '2': '失败'
  }
  return statusMap[status]
}
/**
 * @description 资金明细订单类型
 */
export function transactionTypeFilter(type) {
  const typeMap = {
    'mer_accoubts': '财务对账',
    'refund_order': '退款订单',
    'brokerage_one': '一级分佣',
    'brokerage_two': '二级分佣',
    'refund_brokerage_one': '返还一级分佣',
    'refund_brokerage_two': '返还二级分佣',
    'order': '订单支付'
  }
  return typeMap[type]
}
/**
 * @description 秒杀状态
 */
export function seckillStatusFilter(status) {
  const statusMap = {
    '0': '未开始',
    '1': '正在进行',
    '-1': '已结束'
  }
  return statusMap[status]
}
/**
 * @description 秒杀审核状态
 */
export function seckillReviewStatusFilter(status) {
  const statusMap = {
    '0': '审核中',
    '1': '审核通过',
    '-2': '强制下架',
    '-1': '未通过'
  }
  return statusMap[status]
}

/**
 * @description 发货记录状态
 */
export function deliveryStatusFilter(status) {
  const statusMap = {
    '0': '处理中',
    '1': '成功',
    '10': '部分完成',
    '-1': '失败'
  }
  return statusMap[status]
}
/**
 * @description 主体类型
 */
export function organizationType(type) {
  const typeMap = {
    2401: '小微商户',
    2500: '个人卖家',
    4: '个体工商户',
    2: '企业',
    3: '党政、机关及事业单位',
    1708: '其他组织'
  }
  return typeMap[type]
}
/**
 * @description 证件类型
 */
export function id_docType(type) {
  const typeMap = {
    1: '中国大陆居民-身份证',
    2: '其他国家或地区居民-护照',
    3: '中国香港居民–来往内地通行证',
    4: '中国澳门居民–来往内地通行证',
    5: '中国台湾居民–来往大陆通行证'
  }
  return typeMap[type]
}
/**
 * @description 证件类型
 */
export function deliveryType(type) {
  const typeMap = {
    1: '发货',
    2: '送货',
    3: '无需物流',
    4: '电子面单'
  }
  return typeMap[type]
}
/**
 * @description 跑腿配送状态
 */
 export function runErrandStatus(status) {
  const statusMap = {
    '-1': '已取消',
    '0': '待接单',
    '2': '待取货',
    '3': '配送中',
    '4': '已完成',
    '9': '物品返回中',
    '10': '物品返回完成',
    '100': '骑士到店'
  }
  return statusMap[status]
}
/**
 * @description 发送方式
 */
export function sendWay(type) {
  const typesMap = {
    null: '-',
    '1': '快递',
    '2': '配送',
    '3': '虚拟发货',
    '4': '快递',
    '5': '配送',
    '6': '自动发货',
    '8': '商家寄件'
  }
  return typesMap[type]
}

/**
 * @description 表单类型
 */
export function formTypeFilter(type) {
  const typesMap = {
    'citys': '城市',
    'dates': '日期',
    'uploadPicture': '图片',
    'texts': '文本框',
    'times': '时间',
    'timeranges': '时间范围',
    'radios': '单选框',
    'selects': '下拉框',
    'checkboxs': '多选框',
    'dateranges': '日期范围'
  }
  return typesMap[type]
}

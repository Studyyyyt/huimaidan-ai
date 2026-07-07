import type { IOrderListItem, IOrderListParams } from '@/api/huimaidan'

export interface IOrderListViewItem {
  id: number
  orderNo: string
  date: string
  shopName: string
  branchName: string
  image: string
  payMethod: string
  amount: number
  status: string
  /** 原始状态数字值，用于退款/售后过滤 */
  statusCode: number
  paid: boolean
}

const PAY_TYPE_TEXT: Record<string, string> = {
  0: '余额支付',
  1: '微信支付',
  2: '微信支付',
  10: '小程序支付',
  routine: '小程序支付',
  weixin: '微信支付',
  balance: '余额支付',
  alipay: '支付宝支付',
  h5: 'H5支付',
}

export function buildOrderListParams(tab: string, page: number, limit: number): IOrderListParams {
  const params: IOrderListParams = { page, limit }
  if (tab === '待付款') {
    params.paid = 0
  }
  if (tab === '已完成') {
    params.paid = 1
  }
  // 退款/售后订单通过前端过滤实现，不传递额外参数
  return params
}

export function mapPayType(payType?: number | string): string {
  if (payType === undefined || payType === null || payType === '') {
    return '未支付'
  }
  return PAY_TYPE_TEXT[String(payType)] || String(payType)
}

export function mapOrderListItem(item: IOrderListItem): IOrderListViewItem {
  const statusCode = Number(item.status ?? 0)
  let statusText = item.status_text || ''

  // 如果没有 status_text，根据状态码生成
  if (!statusText) {
    if (statusCode === -1) {
      statusText = '已退款'
    } else if (statusCode === 3) {
      statusText = '已核销'
    } else if (item.paid) {
      statusText = '已完成'
    } else {
      statusText = '待付款'
    }
  }

  return {
    id: Number(item.order_id),
    orderNo: item.order_sn || String(item.order_id),
    date: item.pay_time || item.create_time || '',
    shopName: item.merchant?.mer_name || item.mer_name || '未知商户',
    branchName: item.merchant?.store_branch_name || '',
    image: item.merchant?.mer_avatar || '',
    payMethod: mapPayType(item.pay_type),
    amount: Number(item.pay_price ?? item.amount ?? 0),
    status: statusText,
    statusCode,
    paid: item.paid === true || item.paid === 1,
  }
}

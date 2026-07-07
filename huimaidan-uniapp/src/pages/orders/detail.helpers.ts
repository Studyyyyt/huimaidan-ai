import type { IOrderDetail } from '@/api/huimaidan'
import { mapPayType } from './orders.helpers'

export interface IOrderDetailView {
  orderNo: string
  shopName: string
  branchName: string
  payMethod: string
  payStatus: string
  payAmount: number
  discount: number
  actualAmount: number
  payTime: string
  remark: string
  discountDetail: {
    member_discount_enabled: boolean | undefined
    rule_type_label: string
    title: string
    coupon_deduction_amount: string
    integral: number
    integral_deduction_amount: string
    platform_bear_coupon_amount: string
    platform_bear_integral_amount: string
  }
}

export function getMemberDiscountStatusText(enabled?: boolean) {
  if (enabled === true) {
    return '已参与会员折扣'
  }
  if (enabled === false) {
    return '未参与会员折扣'
  }
  return ''
}

export function mapOrderDetail(res: IOrderDetail): IOrderDetailView {
  const discountAmount = Number.parseFloat(String(res.discount_amount ?? res.discount?.saved_amount ?? 0)) || 0
  const actualAmount = Number.parseFloat(String(res.pay_price ?? res.discount?.pay_amount ?? 0)) || 0
  const payAmount = Number.parseFloat(String(res.amount ?? res.total_price ?? 0)) || actualAmount + discountAmount

  return {
    orderNo: res.order_sn || String(res.order_id),
    shopName: res.merchant?.mer_name || res.mer_name || '未知商户',
    branchName: res.merchant?.store_branch_name || '',
    payMethod: mapPayType(res.pay_type),
    payStatus: res.status_text || (res.paid ? '已完成' : '待付款'),
    payAmount,
    discount: discountAmount,
    actualAmount,
    payTime: res.pay_time || res.create_time || '',
    remark: res.mark || '',
    discountDetail: {
      member_discount_enabled: res.discount?.member_discount_enabled,
      rule_type_label: res.discount?.rule_type_label || '',
      title: res.discount?.title || '',
      coupon_deduction_amount: res.discount?.coupon_deduction_amount || '0',
      integral: res.discount?.integral || 0,
      integral_deduction_amount: res.discount?.integral_deduction_amount || '0',
      platform_bear_coupon_amount: res.discount?.platform_bear_coupon_amount || '0',
      platform_bear_integral_amount: res.discount?.platform_bear_integral_amount || '0',
    },
  }
}

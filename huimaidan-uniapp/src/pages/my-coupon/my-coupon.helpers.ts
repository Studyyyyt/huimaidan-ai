import type { IMyCouponItem } from '@/api/huimaidan'

export type TCouponTab = 'unused' | 'used' | 'expired'

export interface ICouponViewItem {
  id: number
  couponId: number
  name: string
  amount: number
  threshold: number
  expireTime: string
  usedTime: string
  status: TCouponTab
}

export function mapCouponStatusTag(tab: TCouponTab): number {
  const statusMap: Record<TCouponTab, number> = {
    unused: 0,
    used: 1,
    expired: 2,
  }
  return statusMap[tab]
}

export function mapCouponStatus(status: IMyCouponItem['status']): TCouponTab {
  if (status === 'used' || status === 1 || status === '1') {
    return 'used'
  }
  if (status === 'expired' || status === 2 || status === '2') {
    return 'expired'
  }
  return 'unused'
}

export function mapMyCoupon(item: IMyCouponItem): ICouponViewItem {
  const coupon = item.coupon || {}
  return {
    id: Number(item.coupon_user_id || item.id || 0),
    couponId: Number(item.coupon_id || item.couponId || coupon.coupon_id || 0),
    name: item.coupon_title || item.name || item.title || coupon.coupon_title || coupon.title || '未命名优惠券',
    amount: Number(item.coupon_price ?? item.amount ?? coupon.coupon_price ?? 0),
    threshold: Number(item.use_min_price ?? item.threshold ?? coupon.use_min_price ?? 0),
    expireTime: item.end_time || item.expireTime || coupon.end_time || '',
    usedTime: item.use_time || item.usedTime || '',
    status: mapCouponStatus(item.status),
  }
}

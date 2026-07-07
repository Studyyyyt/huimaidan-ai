import assert from 'node:assert/strict'
import { mapCouponStatusTag, mapMyCoupon } from './my-coupon.helpers'

assert.equal(mapCouponStatusTag('unused'), 0)
assert.equal(mapCouponStatusTag('used'), 1)
assert.equal(mapCouponStatusTag('expired'), 2)

assert.deepEqual(
  mapMyCoupon({
    coupon_user_id: 9,
    coupon_id: 19,
    coupon_title: '满减券',
    coupon_price: '15.00',
    use_min_price: '99.00',
    end_time: '2026-12-31 23:59:59',
    use_time: '',
    status: 0,
  }),
  {
    id: 9,
    couponId: 19,
    name: '满减券',
    amount: '15.00',
    threshold: '99.00',
    expireTime: '2026-12-31 23:59:59',
    usedTime: '',
    status: 'unused',
  },
)

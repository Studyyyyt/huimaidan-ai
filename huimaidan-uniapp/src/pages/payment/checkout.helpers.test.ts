import assert from 'node:assert/strict'
import {
  buildCombinedOrderParams,
  buildCreateOrderParams,
  buildPrepareOrderParams,
  getCheckoutBackAction,
  getPreviewPayAmount,
  getUseMemberDiscount,
  normalizeCheckoutStoreList,
  shouldCreateOrder,
} from './checkout.helpers.ts'

assert.equal(getUseMemberDiscount('discount'), true)
assert.equal(getUseMemberDiscount('noDiscount'), false)

assert.equal(shouldCreateOrder(null, false), false)
assert.equal(shouldCreateOrder(101, false), true)
assert.equal(shouldCreateOrder(null, true), true)

assert.deepEqual(
  buildPrepareOrderParams({
    merId: 1001,
    amount: '88.00',
    remark: '',
    useMemberDiscount: false,
  }),
  {
    mer_id: 1001,
    amount: '88.00',
    useMemberDiscount: false,
  },
)

assert.deepEqual(
  buildCombinedOrderParams({
    merId: 1001,
    discountAmount: '88.00',
    noDiscountAmount: '12.00',
    payType: 'routine',
    selectedCouponId: 101,
    usePoints: true,
    useMemberDiscount: true,
    remark: '少冰',
  }),
  {
    mer_id: 1001,
    discount_amount: '88.00',
    no_discount_amount: '12.00',
    pay_type: 'routine',
    couponId: 101,
    usePoints: true,
    useMemberDiscount: true,
    mark: '少冰',
  },
)

assert.deepEqual(
  normalizeCheckoutStoreList([
    {
      mer_id: 55,
      mer_name: '惠买单',
      store_branch_name: '万达店',
      mer_address: '江汉路 1 号',
      phone: '13800138000',
    },
    {
      mer_id: 56,
      name: '王府井店',
      mer_name: '惠买单',
      branch_name: '',
      store_branch_name: '',
      mer_address: '中山路 2 号',
      phone: '13800138000',
    },
    {
      mer_id: 0,
      mer_name: '无效门店',
      mer_address: '无效地址',
    },
  ]),
  [
    {
      id: 55,
      name: '万达店',
      address: '江汉路 1 号',
      phone: '13800138000',
    },
    {
      id: 56,
      name: '王府井店',
      address: '中山路 2 号',
      phone: '13800138000',
    },
  ],
)

assert.deepEqual(
  buildCreateOrderParams({
    merId: 1001,
    amount: '88.00',
    payType: 'routine',
    selectedCouponId: 101,
    usePoints: true,
    useMemberDiscount: false,
    remark: '少冰',
  }),
  {
    mer_id: 1001,
    amount: '88.00',
    pay_type: 'routine',
    couponId: 101,
    usePoints: true,
    useMemberDiscount: false,
    mark: '少冰',
  },
)

assert.equal(getPreviewPayAmount('100.00', { pay_amount: '80.00' }, 10, false), '70.00')
assert.equal(getPreviewPayAmount('100.00', { pay_amount: '80.00' }, 10, true), '70.00')
assert.equal(getPreviewPayAmount('100.00', undefined, 0, true), '100.00')

assert.deepEqual(getCheckoutBackAction({ source: 'store_qrcode' }), {
  type: 'switchTab',
  url: '/pages/index/index',
})
assert.deepEqual(getCheckoutBackAction({ scene: 'm1001.e8F2K9Q' }), {
  type: 'switchTab',
  url: '/pages/index/index',
})
assert.deepEqual(getCheckoutBackAction({ source: 'merchant_detail' }), {
  type: 'navigateBack',
})
assert.deepEqual(getCheckoutBackAction({}), {
  type: 'navigateBack',
})

import assert from 'node:assert/strict'
import { buildOrderListParams, mapOrderListItem } from './orders.helpers'

assert.deepEqual(buildOrderListParams('全部订单', 2, 15), { page: 2, limit: 15 })
assert.deepEqual(buildOrderListParams('待付款', 1, 15), { page: 1, limit: 15, paid: 0 })
assert.deepEqual(buildOrderListParams('已完成', 1, 15), { page: 1, limit: 15, paid: 1 })

assert.deepEqual(
  mapOrderListItem({
    order_id: 12,
    order_sn: 'HMD001',
    create_time: '2026-06-12 10:00:00',
    pay_type: 10,
    pay_price: '31.50',
    paid: 1,
    status_text: '已完成',
    merchant: {
      mer_name: '惠买单商户',
      mer_avatar: 'https://example.com/a.jpg',
      store_branch_name: '万达店',
    },
  }),
  {
    id: 12,
    orderNo: 'HMD001',
    date: '2026-06-12 10:00:00',
    shopName: '惠买单商户',
    branchName: '万达店',
    image: 'https://example.com/a.jpg',
    payMethod: '小程序支付',
    amount: 31.5,
    status: '已完成',
    paid: true,
  },
)

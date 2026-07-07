import assert from 'node:assert/strict'
import {
  buildMiniProgramPaymentOptions,
  isWechatMiniProgramPayType,
  normalizeHuimaidanOrderPayment,
} from '../src/api/huimaidan.pay.ts'

assert.equal(isWechatMiniProgramPayType('routine'), true)
assert.equal(isWechatMiniProgramPayType('weixin'), true)
assert.equal(isWechatMiniProgramPayType('balance'), false)

assert.deepEqual(
  normalizeHuimaidanOrderPayment({
    status: 'routine',
    result: {
      order_id: 1001,
      store_order_id: 2002,
      config: {
        timestamp: '1717200000',
        nonceStr: 'nonce-value',
        package: 'prepay_id=wx123',
        signType: 'MD5',
        paySign: 'signed-value',
      },
    },
  }),
  {
    type: 'routine',
    order_id: 1001,
    store_order_id: 2002,
    timeStamp: '1717200000',
    timestamp: '1717200000',
    nonceStr: 'nonce-value',
    package: 'prepay_id=wx123',
    signType: 'MD5',
    paySign: 'signed-value',
  },
)

assert.deepEqual(
  buildMiniProgramPaymentOptions({
    type: 'routine',
    order_id: 1001,
    store_order_id: 2002,
    timestamp: '1717200000',
    nonceStr: 'nonce-value',
    package: 'prepay_id=wx123',
    signType: 'MD5',
    paySign: 'signed-value',
  }),
  {
    provider: 'wxpay',
    timeStamp: '1717200000',
    nonceStr: 'nonce-value',
    package: 'prepay_id=wx123',
    signType: 'MD5',
    paySign: 'signed-value',
  },
)

assert.throws(
  () => buildMiniProgramPaymentOptions({
    type: 'routine',
    order_id: 1001,
    package: 'prepay_id=wx123',
  }),
  /微信支付参数不完整/,
)

console.log('huimaidan.pay.test passed')

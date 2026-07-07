import assert from 'node:assert/strict'
import { normalizeBusinessError } from '../src/http/http-error.ts'

const error = normalizeBusinessError({
  status: 400,
  message: '请先配置惠买单优惠叠加策略',
})

assert.equal(error instanceof Error, true)
assert.equal(error.message, '请先配置惠买单优惠叠加策略')
assert.deepEqual(error.response, {
  status: 400,
  message: '请先配置惠买单优惠叠加策略',
})

const dataError = normalizeBusinessError({
  code: 400,
  msg: '优惠券不可用',
  data: { couponId: 101 },
})

assert.equal(dataError.message, '优惠券不可用')
assert.deepEqual(dataError.data, { couponId: 101 })

console.log('http-error.test passed')

import assert from 'node:assert/strict'
import { getMemberDiscountStatusText } from './detail.helpers'

assert.equal(getMemberDiscountStatusText(true), '已参与会员折扣')
assert.equal(getMemberDiscountStatusText(false), '未参与会员折扣')
assert.equal(getMemberDiscountStatusText(undefined), '')

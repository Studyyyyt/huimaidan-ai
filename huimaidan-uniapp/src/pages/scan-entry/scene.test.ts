import assert from 'node:assert/strict'
import { buildInviteEntryUrl, buildStoreQrcodeCheckoutUrl, parseScanScene, parseStoreQrcodeScene } from './scene.ts'

assert.deepEqual(parseStoreQrcodeScene('m1001.e8F2K9Q'), {
  merId: 1001,
  entryCode: '8F2K9Q',
})

assert.deepEqual(parseStoreQrcodeScene('m2002.eABC12345'), {
  merId: 2002,
  entryCode: 'ABC12345',
})

assert.deepEqual(parseStoreQrcodeScene('m3003.eA1B2C3'), {
  merId: 3003,
  entryCode: 'A1B2C3',
})

assert.equal(
  buildStoreQrcodeCheckoutUrl({ merId: 1001, entryCode: '8F2K9Q' }),
  '/pages/payment/checkout?id=1001&source=store_qrcode&entry_code=8F2K9Q',
)

assert.deepEqual(parseScanScene('i12345'), { type: 'invite', spreadUid: 12345 })
assert.equal(buildInviteEntryUrl({ type: 'invite', spreadUid: 12345 }), '/pages/index/index?spread=12345')

assert.throws(() => parseStoreQrcodeScene(''), /二维码参数错误/)
assert.throws(() => parseStoreQrcodeScene('m0.e8F2K9Q'), /二维码参数错误/)
assert.throws(() => parseStoreQrcodeScene('m1001.e12345'), /二维码参数错误/)
assert.throws(() => parseStoreQrcodeScene('id=1001'), /二维码参数错误/)
assert.throws(() => parseScanScene('i0'), /二维码参数错误/)
assert.throws(() => parseScanScene('iabc'), /二维码参数错误/)

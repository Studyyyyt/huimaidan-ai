export const INVITE_SPREAD_UID_STORAGE_KEY = 'invite_spread_uid'

export function normalizeSpreadUid(value: unknown) {
  const spread = Number(value)
  return Number.isInteger(spread) && spread > 0 ? spread : 0
}

export function getPendingSpreadUid() {
  return normalizeSpreadUid(uni.getStorageSync(INVITE_SPREAD_UID_STORAGE_KEY))
}

export function setPendingSpreadUid(spreadUid: number) {
  const normalized = normalizeSpreadUid(spreadUid)
  if (!normalized) {
    throw new Error('邀请参数错误')
  }
  uni.setStorageSync(INVITE_SPREAD_UID_STORAGE_KEY, normalized)
}

export function clearPendingSpreadUid() {
  uni.removeStorageSync(INVITE_SPREAD_UID_STORAGE_KEY)
}

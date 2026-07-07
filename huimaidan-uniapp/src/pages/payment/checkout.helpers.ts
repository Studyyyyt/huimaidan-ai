export type TCheckoutTab = 'discount' | 'noDiscount'

export interface ICheckoutBackOptions {
  source?: string
  scene?: string
}

export type TCheckoutBackAction
  = | { type: 'navigateBack' }
    | { type: 'switchTab', url: string }

interface IBuildPrepareOrderParamsOptions {
  merId: number
  amount: string
  remark?: string
  useMemberDiscount: boolean
}

interface IBuildCreateOrderParamsOptions extends IBuildPrepareOrderParamsOptions {
  payType: string
  selectedCouponId: number | null
  usePoints: boolean
}

interface IBuildCombinedOrderParamsOptions {
  merId: number
  discountAmount: string
  noDiscountAmount: string
  payType: string
  selectedCouponId: number | null
  usePoints: boolean
  useMemberDiscount: boolean
  remark?: string
}

interface IDiscountPreview {
  pay_amount?: string
}

export interface ICheckoutStoreSource {
  id?: number
  mer_id?: number
  station_id?: number
  name?: string
  mer_name?: string
  branch_name?: string
  store_branch_name?: string
  station_name?: string
  address?: string
  mer_address?: string
  station_address?: string
  phone?: string
  service_phone?: string
  mer_phone?: string
}

export interface ICheckoutStoreOption {
  id: number
  name: string
  address: string
  phone?: string
}

function normalizeOptionalText(value?: string) {
  const text = value?.trim()
  return text || undefined
}

function firstOptionalText(...values: Array<string | undefined>) {
  for (const value of values) {
    const text = normalizeOptionalText(value)
    if (text) {
      return text
    }
  }
  return undefined
}

function toMoney(value: number) {
  return Math.max(0, value).toFixed(2)
}

export function getUseMemberDiscount(tab: TCheckoutTab) {
  return tab === 'discount'
}

export function shouldCreateOrder(selectedCouponId: number | null, usePoints: boolean) {
  return selectedCouponId !== null || usePoints
}

export function buildPrepareOrderParams(options: IBuildPrepareOrderParamsOptions) {
  const params: {
    mer_id: number
    amount: string
    useMemberDiscount: boolean
    mark?: string
  } = {
    mer_id: options.merId,
    amount: options.amount,
    useMemberDiscount: options.useMemberDiscount,
  }

  const mark = normalizeOptionalText(options.remark)
  if (mark) {
    params.mark = mark
  }

  return params
}

export function buildCreateOrderParams(options: IBuildCreateOrderParamsOptions) {
  const params: {
    mer_id: number
    amount: string
    pay_type: string
    couponId?: number
    usePoints?: boolean
    useMemberDiscount: boolean
    mark?: string
  } = {
    mer_id: options.merId,
    amount: options.amount,
    pay_type: options.payType,
    useMemberDiscount: options.useMemberDiscount,
  }

  if (options.selectedCouponId !== null) {
    params.couponId = options.selectedCouponId
  }
  if (options.usePoints) {
    params.usePoints = true
  }

  const mark = normalizeOptionalText(options.remark)
  if (mark) {
    params.mark = mark
  }

  return params
}

export function buildCombinedOrderParams(options: IBuildCombinedOrderParamsOptions) {
  const params: {
    mer_id: number
    discount_amount: string
    no_discount_amount: string
    pay_type: string
    couponId?: number
    usePoints?: boolean
    useMemberDiscount: boolean
    mark?: string
  } = {
    mer_id: options.merId,
    discount_amount: options.discountAmount,
    no_discount_amount: options.noDiscountAmount,
    pay_type: options.payType,
    useMemberDiscount: options.useMemberDiscount,
  }

  if (options.selectedCouponId !== null) {
    params.couponId = options.selectedCouponId
  }
  if (options.usePoints) {
    params.usePoints = true
  }

  const mark = normalizeOptionalText(options.remark)
  if (mark) {
    params.mark = mark
  }

  return params
}

export function normalizeCheckoutStoreList(stores: ICheckoutStoreSource[] = []): ICheckoutStoreOption[] {
  return stores
    .map((store) => {
      const id = Number(store.mer_id ?? store.id ?? store.station_id ?? 0)
      const name = firstOptionalText(
        store.store_branch_name,
        store.branch_name,
        store.name,
        store.station_name,
        store.mer_name,
      ) || '门店'
      const address = firstOptionalText(store.mer_address, store.address, store.station_address) || ''
      const phone = firstOptionalText(store.phone, store.service_phone, store.mer_phone)
      return { id, name, address, phone }
    })
    .filter(store => store.id > 0)
}

export function getPreviewPayAmount(
  amount: string,
  discountPreview: IDiscountPreview | undefined,
  couponDiscountAmount: number,
  _usePoints: boolean,
) {
  const baseAmount = Number.parseFloat(discountPreview?.pay_amount || amount) || 0
  return toMoney(baseAmount - couponDiscountAmount)
}

export function getCheckoutBackAction(options: ICheckoutBackOptions): TCheckoutBackAction {
  if (options.source === 'store_qrcode' || Boolean(options.scene)) {
    return {
      type: 'switchTab',
      url: '/pages/index/index',
    }
  }

  return {
    type: 'navigateBack',
  }
}

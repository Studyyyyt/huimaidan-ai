export interface IHuimaidanOrderPaymentEnvelope {
  status?: string
  result?: {
    order_id?: number
    store_order_id?: number
    config?: Record<string, unknown>
    [key: string]: unknown
  }
  [key: string]: unknown
}

export interface IHuimaidanOrderPaymentResult {
  type: string
  order_id: number
  store_order_id?: number
  timeStamp?: string
  timestamp?: string
  nonceStr?: string
  package?: string
  signType?: string
  paySign?: string
  [key: string]: unknown
}

interface IMiniProgramPaymentOptions {
  provider: 'wxpay'
  timeStamp: string
  nonceStr: string
  package: string
  signType: string
  paySign: string
}

function toPositiveInt(value: unknown) {
  const num = Number.parseInt(String(value ?? 0), 10)
  return Number.isFinite(num) && num > 0 ? num : 0
}

function toOptionalString(value: unknown) {
  if (value === undefined || value === null) {
    return undefined
  }
  const text = String(value).trim()
  return text || undefined
}

function isEnvelope(payload: unknown): payload is IHuimaidanOrderPaymentEnvelope {
  return typeof payload === 'object' && payload !== null && ('status' in payload || 'result' in payload)
}

export function isWechatMiniProgramPayType(type: string) {
  return type === 'routine' || type === 'weixin'
}

function normalizePaymentFields(source: Record<string, unknown>) {
  const timeStamp = toOptionalString(source.timeStamp ?? source.timestamp)
  const nonceStr = toOptionalString(source.nonceStr ?? source.nonce_str)
  const packageValue = toOptionalString(source.package ?? source.packageValue)
  const signType = toOptionalString(source.signType ?? source.sign_type)
  const paySign = toOptionalString(source.paySign ?? source.pay_sign)

  return {
    ...source,
    timeStamp,
    timestamp: timeStamp,
    nonceStr,
    package: packageValue,
    signType,
    paySign,
  }
}

export function normalizeHuimaidanOrderPayment(payload: unknown): IHuimaidanOrderPaymentResult {
  if (isEnvelope(payload)) {
    const result = typeof payload.result === 'object' && payload.result !== null ? payload.result : {}
    const config = typeof result.config === 'object' && result.config !== null
      ? result.config as Record<string, unknown>
      : result as Record<string, unknown>

    return {
      type: String(payload.status || ''),
      order_id: toPositiveInt(result.order_id),
      store_order_id: toPositiveInt(result.store_order_id) || undefined,
      ...normalizePaymentFields(config),
    }
  }

  const raw = typeof payload === 'object' && payload !== null ? payload as Record<string, unknown> : {}
  return {
    type: String(raw.type || raw.status || ''),
    order_id: toPositiveInt(raw.order_id),
    store_order_id: toPositiveInt(raw.store_order_id) || undefined,
    ...normalizePaymentFields(raw),
  }
}

export function buildMiniProgramPaymentOptions(payment: IHuimaidanOrderPaymentResult): IMiniProgramPaymentOptions {
  const normalizedPayment = normalizePaymentFields(payment)
  const timeStamp = toOptionalString(normalizedPayment.timeStamp)
  const nonceStr = toOptionalString(normalizedPayment.nonceStr)
  const packageValue = toOptionalString(normalizedPayment.package)
  const signType = toOptionalString(normalizedPayment.signType)
  const paySign = toOptionalString(normalizedPayment.paySign)

  if (!timeStamp || !nonceStr || !packageValue || !signType || !paySign) {
    throw new Error('微信支付参数不完整，无法调起支付')
  }

  return {
    provider: 'wxpay',
    timeStamp,
    nonceStr,
    package: packageValue,
    signType,
    paySign,
  }
}

import type { IStoreItem } from '@/api/huimaidan'
import { http } from '@/http/http'
import { normalizeMediaUrl } from '@/utils'

export type AiMealType = 'breakfast' | 'brunch' | 'lunch' | 'tea' | 'dinner' | 'supper' | 'late_night'

export interface IAiBannerMerchant {
  mer_id: number
  mer_name: string
  mer_avatar?: string
  discount_label?: string
  distance?: string
}

export interface IAiBannerParams {
  latitude?: number
  longitude?: number
  city_id?: number
  city_name?: string
}

export interface IAiBannerResponse {
  meal_type: AiMealType
  title: string
  subtitle: string
  recommend_merchant?: IAiBannerMerchant | null
  background_color: string
  text_color: string
  degraded?: boolean
}

export interface IAiIntentTags {
  category?: string[]
  price?: string
  price_range?: string
  scene?: string[]
  taste?: string[]
  facility?: string[]
  meal?: string[]
  distance?: string
  action?: string
  [key: string]: any
}

export interface IAiRecommendMerchant extends Partial<IStoreItem> {
  mer_id: number
  mer_name: string
  recommend_reason?: string
  score?: number
  score_factors?: Record<string, number>
}

export interface IAiChatContent {
  text: string
  merchants?: IAiRecommendMerchant[]
  intent_tags?: IAiIntentTags
}

export type AiMessageType = 'text' | 'recommend' | 'error'

export interface IAiChatParams {
  session_id?: string
  message: string
  latitude?: number
  longitude?: number
  city_id?: number
  city_name?: string
}

export interface IAiChatResponse {
  log_id?: number
  session_id: string
  type: AiMessageType
  content: IAiChatContent
  degraded?: boolean
  error_message?: string
}

export interface IAiEventParams {
  log_id?: number
  session_id?: string
  event: 'click' | 'detail' | 'navigate' | 'order' | 'feedback'
  mer_id?: number
  feedback?: -1 | 0 | 1
}

export interface IAiEventResponse {
  updated: boolean
  preference_updated?: boolean
  preference_message?: string
}

export function getCurrentMealType(): AiMealType {
  const hour = new Date().getHours()
  if (hour < 9)
    return 'breakfast'
  if (hour < 11)
    return 'brunch'
  if (hour < 14)
    return 'lunch'
  if (hour < 17)
    return 'tea'
  if (hour < 21)
    return 'dinner'
  if (hour < 23)
    return 'supper'
  return 'late_night'
}

export function getAiBanner(params?: IAiBannerParams): Promise<IAiBannerResponse> {
  return http.get<IAiBannerResponse>('/api/huimaidan/ai/banner', params).then(normalizeAiBanner)
}

export function postAiChat(params: IAiChatParams): Promise<IAiChatResponse> {
  return http.post<IAiChatResponse>('/api/huimaidan/ai/chat', params, { timeout: 75000 }).then(normalizeAiChat)
}

export interface IAiOnboardingConfig {
  enabled: number
  title: string
  home_subtitle?: string
  home_search_placeholder?: string
  home_featured_subtitle?: string
  chat_welcome_text?: string
  features: string[]
  examples: string[]
  version?: string
  updated_at?: string
}

export function getAiOnboardingConfig(): Promise<IAiOnboardingConfig> {
  return http.get<IAiOnboardingConfig>('/api/huimaidan/ai/onboarding_config')
}

export function postAiEvent(params: IAiEventParams): Promise<IAiEventResponse> {
  return http.post<IAiEventResponse>('/api/huimaidan/ai/event', params)
}

function normalizeAiBanner(data: IAiBannerResponse): IAiBannerResponse {
  if (data.recommend_merchant?.mer_avatar) {
    data.recommend_merchant.mer_avatar = normalizeMediaUrl(data.recommend_merchant.mer_avatar)
  }
  return data
}

function normalizeAiChat(data: IAiChatResponse): IAiChatResponse {
  if (Array.isArray(data.content?.merchants)) {
    data.content.merchants = data.content.merchants.map(merchant => ({
      ...merchant,
      mer_avatar: normalizeMediaUrl(merchant.mer_avatar),
      promo_image: normalizeMediaUrl(merchant.promo_image),
    }))
  }
  return data
}

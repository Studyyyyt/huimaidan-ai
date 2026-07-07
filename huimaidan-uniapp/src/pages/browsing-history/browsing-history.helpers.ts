import type { IBrowsingHistoryItem } from '@/api/huimaidan'

export interface IBrowsingHistoryViewItem {
  id: number
  merchantId: number
  name: string
  image: string
  salesText: string
  phone: string
  visitedAt: string
  visitCount: number
  rating: number
  distance: string
  discountLabel: string
  categoryName: string
  pricePerPersonText: string
}

function formatDiscountLabel(label?: string | null): string {
  if (!label)
    return ''

  const match = String(label).match(/(\d+(?:\.\d+)?)\s*折/)
  if (!match)
    return String(label)

  const discount = Number(match[1])
  if (!Number.isFinite(discount))
    return String(label)

  return `${discount.toFixed(1)}折`
}

export function mapBrowsingHistoryItem(item: IBrowsingHistoryItem): IBrowsingHistoryViewItem {
  const shop = item.shop || {}
  const merchantId = Number(item.mer_id || shop.mer_id || 0)

  return {
    id: Number(item.history_id || 0),
    merchantId,
    name: shop.mer_name || '',
    image: shop.mer_avatar || '',
    salesText: shop.sales_text || '',
    phone: shop.phone || shop.service_phone || '',
    visitedAt: item.browseTime || '',
    visitCount: Number(item.visitCount || 0),
    rating: Number(shop.rating || 0),
    distance: shop.distance || '',
    discountLabel: formatDiscountLabel(shop.discount_label),
    categoryName: shop.category_name || '',
    pricePerPersonText: shop.price_per_person_text || '',
  }
}

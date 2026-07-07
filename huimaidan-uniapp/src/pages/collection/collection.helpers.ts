import type { ICollectionMerchantItem } from '@/api/huimaidan'

export interface ICollectionViewItem {
  id: number
  image: string
  title: string
  storeBranchName: string
  subtitle: string
  category: string
  salesText: string
  phone: string
  distance: string
  discount: string
}

export function mapCollectionItem(item: ICollectionMerchantItem): ICollectionViewItem {
  const merchant = item.merchant || {}

  // 展示销量 = configured_sales + real_sales
  const totalSales = (merchant.configured_sales || 0) + (merchant.real_sales || 0)
  const sales = merchant.sales_text || (totalSales > 0 ? `已售${totalSales}` : '')

  return {
    id: Number(merchant.mer_id || item.type_id),
    image: merchant.mer_avatar || '',
    title: merchant.mer_name || '未知商户',
    storeBranchName: merchant.store_branch_name || '',
    subtitle: merchant.mer_info || merchant.slogan || '',
    category: merchant.category_name || '',
    salesText: sales,
    phone: merchant.phone || merchant.service_phone || '',
    distance: merchant.distance || merchant.distance_km || '',
    discount: merchant.discount_label || '',
  }
}

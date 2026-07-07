import assert from 'node:assert/strict'
import { mapBrowsingHistoryItem } from './browsing-history.helpers'

assert.deepEqual(
  mapBrowsingHistoryItem({
    history_id: 7,
    mer_id: 66,
    browseTime: '2026-06-12 09:00:00',
    visitCount: 2,
    shop: {
      mer_id: 66,
      mer_name: '浏览商户',
      mer_avatar: 'https://example.com/h.jpg',
      category_name: '美食餐饮',
      sales_text: '半年售35万+',
      phone: '13900000000',
      rating: 4.8,
      distance: '1.20km',
      discount_label: '惠买单 8 折',
      price_per_person_text: '人均 ¥68',
    },
  }),
  {
    id: 7,
    merchantId: 66,
    name: '浏览商户',
    image: 'https://example.com/h.jpg',
    salesText: '半年售35万+',
    phone: '13900000000',
    visitedAt: '2026-06-12 09:00:00',
    visitCount: 2,
    rating: 4.8,
    distance: '1.20km',
    discountLabel: '8.0折',
    categoryName: '美食餐饮',
    pricePerPersonText: '人均 ¥68',
  },
)

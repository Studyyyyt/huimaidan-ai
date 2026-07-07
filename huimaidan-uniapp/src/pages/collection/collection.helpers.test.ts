import assert from 'node:assert/strict'
import { mapCollectionItem } from './collection.helpers'

assert.deepEqual(
  mapCollectionItem({
    type_id: 88,
    merchant: {
      mer_id: 88,
      mer_name: '收藏商户',
      mer_avatar: 'https://example.com/m.jpg',
      mer_info: '真实简介',
      sales: 123,
      service_phone: '13800000000',
    },
  }),
  {
    id: 88,
    image: 'https://example.com/m.jpg',
    title: '收藏商户',
    subtitle: '真实简介',
    salesText: '已售123',
    phone: '13800000000',
  },
)

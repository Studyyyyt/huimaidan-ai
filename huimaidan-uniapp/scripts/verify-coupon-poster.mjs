import { readFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..')
const source = readFileSync(resolve(root, 'src/pages/coupon/coupon.vue'), 'utf8')

const requiredSnippets = [
  'coupon-poster',
  'poster-orb poster-orb--left',
  'tutorial-pill',
  'newcomer-card__shine',
  'coupon-stamp__edge',
  'rules-card__time',
  'planet-decoration__ring',
  'linear-gradient(145deg, #9377f8 0%, #ffa173 48%, #c7f1ff 100%)',
]

const missingSnippets = requiredSnippets.filter(snippet => !source.includes(snippet))

if (missingSnippets.length > 0) {
  throw new Error(`优惠券海报还原缺少关键结构/样式：${missingSnippets.join(', ')}`)
}

console.log('优惠券海报关键结构/样式校验通过')

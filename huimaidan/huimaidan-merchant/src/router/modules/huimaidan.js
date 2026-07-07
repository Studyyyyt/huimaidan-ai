// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
import Layout from '@/layout'
import { roterPre } from '@/settings'

const huimaidanRouter = {
  path: `${roterPre}/huimaidan`,
  name: 'MerchantHuimaidan',
  meta: {
    title: '惠买单'
  },
  alwaysShow: true,
  component: Layout,
  children: [
    {
      path: 'discount',
      name: 'MerchantHuimaidanDiscount',
      meta: {
        title: '优惠规则',
        section: 'discount',
        noCache: true
      },
      component: () => import('@/views/huimaidan/entry')
    },
    {
      path: 'pool',
      name: 'MerchantHuimaidanPool',
      meta: {
        title: '垫资池',
        section: 'pool',
        noCache: true
      },
      component: () => import('@/views/huimaidan/entry')
    },
    {
      path: 'settlement',
      name: 'MerchantHuimaidanSettlement',
      meta: {
        title: '结算订单',
        section: 'settlement',
        noCache: true
      },
      component: () => import('@/views/huimaidan/entry')
    },
    {
      path: 'finance',
      name: 'MerchantHuimaidanFinance',
      meta: {
        title: '财务概览',
        section: 'finance',
        noCache: true
      },
      component: () => import('@/views/huimaidan/entry')
    }
  ]
}

export default huimaidanRouter

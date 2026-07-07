// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import Layout from '@/layout'
import { roterPre } from '@/settings'
const statisticRouter = {
  path: `${roterPre}/statistic`,
  name: 'statistic',
  meta: {
    icon: 'dashboard',
    title: '统计'
  },
  alwaysShow: true,
  component: Layout,
  children: [
    {
      path: 'order',
      name: 'statisticOrder',
      meta: {
        title: '订单统计'
      },
      component: () => import('@/views/statistic/order/index.vue')
    },
    //商品统计
    {
      path: 'product',
      name: 'statisticProduct',
      meta: {
        title: '商品统计'
      },
      component: () => import('@/views/statistic/product/index.vue')
    },
    //会员统计
    {
      path: 'member',
      name: 'statisticMember',
      meta: {
        title: '用户统计'
      },
      component: () => import('@/views/statistic/user/index.vue')
    },
  ]
}

export default statisticRouter

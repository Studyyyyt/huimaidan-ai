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
const deliveryRouter =
  {
    path: `${roterPre}/delivery`,
    name: 'delivery',
    meta: {
      icon: '',
      title: '同城配送'
    },
    alwaysShow: true,
    component: Layout,
    children: [
      {
        path: 'store_manage',
        name: 'StoreManage',
        meta: {
          title: '第三方配送点'
        },
        component: () => import('@/views/cityDelivery/storeManage/index')
      },
      {
        path: 'personnel_manage',
        name: 'PersonnelManage',
        meta: {
          title: '配送员'
        },
        component: () => import('@/views/cityDelivery/personnelManage/index')
      },
      {
        path: 'delivice_statistic',
        name: 'DeliveryStatistic',
        meta: {
          title: '配送员统计'
        },
        component: () => import('@/views/cityDelivery/personnelManage/statistic')
      },
      {
        path: 'delivery_point',
        name: 'DeliveryPoint',
        meta: {
          title: '提货点管理'
        },
        component: () => import('@/views/cityDelivery/deliveryPoint/index')
      },
      {
        path: 'delivery_point/form',
        name: 'DeliveryPointForm',
        meta: {
          title: '添加提货点',
          noCache: true,
          activeMenu: `${roterPre}/delivery/delivery_point`
        },
        component: () => import('@/views/cityDelivery/deliveryPoint/form')
      },
      {
        path: 'usage_record',
        name: 'UsageRecord',
        meta: {
          title: '使用记录'
        },
        component: () => import('@/views/cityDelivery/usageRecord/index')
      },
      {
        path: 'recharge_record',
        name: 'RechargeRecord',
        meta: {
          title: '充值记录'
        },
        component: () => import('@/views/cityDelivery/rechargeRecord/index')
      }
    ]
  }

export default deliveryRouter

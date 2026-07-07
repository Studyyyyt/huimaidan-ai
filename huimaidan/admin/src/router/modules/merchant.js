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
const merchantRouter =
{
  path: `${roterPre}/merchant`,
  name: 'merchant',
  meta: {
    icon: 'dashboard',
    title: '店铺管理'
  },
  alwaysShow: true,
  component: Layout,
  children: [
    {
      path: 'system',
      name: 'MerchantSystem',
      meta: {
        title: '店铺权限管理',
        noCache: true
      },
      component: () => import('@/views/merchant/system/index')
    },
    {
      path: 'list',
      name: 'MerchantList',
      meta: {
        title: '店铺列表',
        noCache: true
      },
      component: () => import('@/views/merchant/list/index')
    },
    {
      path: 'list/reconciliation/:id/:type?',
      name: 'MerchantRecord',
      component: () => import('@/views/merchant/list/record'),
      meta: {
        title: '店铺对账',
        noCache: true,
        activeMenu: `${roterPre}/merchant/list`
      },
      hidden: true
    },
    {
      path: 'classify',
      name: 'MerchantClassify',
      meta: {
        title: '店铺分类',
        noCache: true
      },
      component: () => import('@/views/merchant/classify')
    },
    {
      path: 'index',
      name: 'MerchantIndex',
      meta: {
        title: '商户列表',
        noCache: true
      },
      component: () => import('@/views/merchant/index')
    },
    {
      path: 'create',
      name: 'MerchantCreate',
      meta: {
        title: '创建商户',
        activeMenu: `${roterPre}/merchant/index`,
        noCache: true
      },
      component: () => import('@/views/merchant/create')
    },
    {
      path: 'admin-list',
      name: 'MerchantAdminList',
      meta: {
        title: '商户管理员',
        noCache: true
      },
      component: () => import('@/views/merchant/application/admin-list.vue')
    },
    {
      path: 'application',
      name: 'MerchantApplication',
      meta: {
        title: '店铺申请',
        noCache: true
      },
      component: () => import('@/views/merchant/application')
    },
    {
      path: 'agree',
      name: 'MerchantAgreement',
      meta: {
        title: '入驻协议',
        noCache: true
      },
      component: () => import('@/views/merchant/agreement')
    },
    {
      path: 'type',
      name: 'storeType',
      meta: {
        title: '店铺类型',
        noCache: true
      },
      component: () => import('@/views/merchant/type/index')
    },
    {
      path: 'applyMents',
      name: 'MerchantApplyMents',
      meta: {
        title: '服务申请',
        noCache: true
      },
      component: () => import('@/views/merchant/applyments/index')
    },
    {
      path: 'applyList',
      name: 'ApplyList',
      meta: {
        title: '分账店铺列表'
      },
      component: () => import('@/views/merchant/applyments/list')
    },
    {
      path: 'type/description',
      name: 'MerTypeDesc',
      meta: {
        title: '店铺类型说明',
        noCache: true,
      },
      component: () => import('@/views/merchant/type/description')
    },
    {
      path: 'deposit_list',
      name: 'DepositList',
      meta: {
        title: '店铺保证金管理',
        noCache: true
      },
      component: () => import('@/views/merchant/deposit/index')
    },
    {
      path: 'recharge_record',
      name: 'MerRechargeRecord',
      meta: {
        title: '店铺充值记录',
        noCache: true
      },
      component: () => import('@/views/merchant/rechargeRecord/index')
    },
    {
      path: 'grouping',
      name: 'MerchantGrouping',
      meta: {
        title: '店铺分组',
        noCache: true
      },
      component: () => import('@/views/merchant/grouping/index')
    },
    {
      path: 'grouping/create',
      name: 'MerchantGroupingCreate',
      meta: {
        title: '创建店铺分组',
        activeMenu: `${roterPre}/merchant/grouping`,
        noCache: true
      },
      component: () => import('@/views/merchant/grouping/create')
    },
    {
      path: "review",
      name: "MerchantReview",
      meta: {
        title: '商户入驻审核',
        noCache: true
      },
      component: () => import('@/views/merchant/application/review.vue')
    },
    {
      path: "apply-setting",
      name: "MerchantApplySetting",
      meta: {
        title: '商户设置',
        noCache: true
      },
      component: () => import('@/views/merchant/application/apply-setting.vue')
    }
  ]
}

export default merchantRouter

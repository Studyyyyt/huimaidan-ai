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
const settingRouter =
{
  path: `${roterPre}/setting`,
  name: 'system_group',
  meta: {
    icon: 'dashboard',
    title: '权限管理'
  },
  component: Layout,
  children: [
    {
      path: 'systemRole',
      name: 'setting_role',
      meta: {
        title: '身份管理'
      },
      component: () => import('@/views/setting/systemRole/index')
    },
    {
      path: 'systemAdmin',
      name: 'setting_systemAdmin',
      meta: {
        title: '管理员管理'
      },
      component: () => import('@/views/setting/systemAdmin/index')
    },
    {
      path: 'systemLog',
      name: 'setting_systemLog',
      meta: {
        title: '操作日志'
      },
      component: () => import('@/views/setting/systemLog/index')
    },
    {
      path: 'sms/sms_config/index',
      name: 'smsConfig',
      meta: {
        title: '使用记录'
      },
      component: () => import('@/views/notify/smsConfig/index')
    },
    {
      path: 'sms/sms_account/index',
      name: 'smsAccount',
      meta: {
        title: '一号通账户'
      },
      component: () => import('@/views/notify/smsConfig/account')
    },
    {
      path: 'sms/sms_pay/index',
      name: 'smsPay',
      meta: {
        title: '套餐购买',
        activeMenu: `${roterPre}/setting/sms/sms_config/index`
      },
      component: () => import('@/views/notify/smsPay/index')
    },
    {
      path: 'sms/dumpConfig',
      name: 'dumpConfig',
      meta: {
        title: '电子面单配置'
      },
      component: () => import('@/views/setting/dumpConfig/index')
    },
    {
      path: 'printer/list',
      name: 'PrinterList',
      meta: {
        title: '打印机配置'
      },
      component: () => import('@/views/setting/printer/index')
    },
    {
      path: 'printer/content',
      name: 'PrinterContent',
      meta: {
        title: '小票配置',
        activeMenu: `${roterPre}/setting/printer/list`
      },
      component: () => import('@/views/setting/printer/content')
    },
    {
      path: 'delivery',
      name: 'DeliverySettings',
      meta: {
        title: '配送设置'
      },
      component: () => import('@/views/setting/delivery/index')
    }

  ]
}

export default settingRouter

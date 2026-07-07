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
    name: 'setting',
    meta: {
      icon: 'dashboard',
      title: '权限管理'
    },
    alwaysShow: true,
    component: Layout,
    children: [
      {
        path: 'menu',
        name: 'setting_menu',
        meta: {
          title: '菜单管理'
        },
        component: () => import('@/views/setting/systemMenu/index')
      },
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
          name: 'smsConfgIndex',
          meta: {
            title: '一号通账户'
          },
          component: () => import('@/views/notify/smsConfig/index')
        },
        {
          path: 'sms/sms_template_apply/index',
          name: 'smsTemplate',
          meta: {
            title: '短信模板'
          },
          component: () => import('@/views/notify/smsTemplateApply/index')
        },
        {
          path: 'sms/sms_pay/index',
          name: 'smsPay',
          meta: {
            title: '套餐购买'
          },
          component: () => import('@/views/notify/smsPay/index')
        },
        {
          path: 'sms/sms_template_apply/commons',
          name: 'smsCommons',
          meta: {
            title: '公共短信模板'
          },
          component: () => import('@/views/notify/smsTemplateApply/index')
        },
        {
          path: 'sms/sms_config/config',
          name: 'smsConfig',
          meta: {
            title: '一号通配置',
            noCache: true
          },
          component: () => import('@/views/notify/smsConfig/config')
        },
        {
          path: 'notification/index',
          name: 'Notification',
          meta: {
            title: '一号通消息管理配置',
            noCache: true
          },
          component: () => import('@/views/system/notification/index')
        },
        {
          path: 'system/maintain/auth',
          name: 'MaintainAuthInfo',
          meta: {
            title: '系统信息',
            noCache: true
          },
          component: () => import('@/views/system/auth/index')
        },
        {
          path: 'diy/index',
          name: 'NotificDiyation',
          meta: {
            title: '首页装修',
            noCache: true,
            activeMenu: `${roterPre}/setting/diy/list`
          },
          component: () => import('@/views/setting/devise/index')
        },
        {
          path: 'micro/list',
          name: 'MicroDiyation',
          meta: {
            title: '微页面',
            noCache: true
          },
          component: () => import('@/views/setting/devise/microList')
        },
        {
          path: 'diy/list',
          name: 'DecorationDiyation',
          meta: {
            title: '页面装修',
            noCache: true,
            activeMenu: `${roterPre}/setting/diy/list`
          },
          component: () => import('@/views/setting/devise/list')
        },
        {
          path: 'diy/store',
          name: 'DecorationStore',
          meta: {
            title: '店铺街',
            noCache: true,
            activeMenu: `${roterPre}/setting/diy/store`
          },
          component: () => import('@/views/setting/devise/store')
        },
        {
          path: 'diy/personal',
          name: 'DecorationPersonal',
          meta: {
            title: '个人中心',
            noCache: true,
            activeMenu: `${roterPre}/setting/diy/personal`
          },
          component: () => import('@/views/setting/devise/personal')
        },
        {
          path: 'merchant/diyList',
          name: 'MerchantDiy',
          meta: {
            title: '店铺模板',
            noCache: true
          },
          component: () => import('@/views/setting/devise/merchantList')
        },
        {
          path: 'merchant/diy',
          name: 'DecorationDiyationStore',
          meta: {
            title: '店铺装修模板',
            noCache: true,
            activeMenu: `${roterPre}/setting/merchant/diyList`
          },
          component: () => import('@/views/setting/devise/index')
        },
        {
          path: 'diy/list',
          name: 'DecorationDiyationList',
          meta: {
            title: '装修列表',
            noCache: true,
            activeMenu: `${roterPre}/setting/diy/list`
          },
          component: () => import('@/views/setting/devise/list')
        },
        {
          path: 'diy/product_detail',
          name: 'DecorationProductDetail',
          meta: {
            title: '商品详情',
            noCache: true,
          },
          component: () => import('@/views/setting/devise/productDetail')
        },
        {
          path: 'system_visualization_data',
          name: 'SystemVisualizationData',
          meta: {
            title: '页面配置',
            noCache: true,
          },
          component: () => import('@/views/setting/devise/visualization')
        },
        {
          path: 'fab',
          name: 'Fab',
          meta: {
            title: '悬浮菜单',
            noCache: true,
          },
          component: () => import('@/views/setting/devise/fab')
        },
        {
				  path: "product_category",
				  name: 'ProductCategory',
				  meta: {
            title: '商品分类',
            noCache: true,
          },
          component: () => import('@/views/setting/devise/goodClass')
				},
        {
          path: 'diy/plantform/category/list',
          name: 'categoryPlantform',
          meta: {
            title: '平台分类列表',
            noCache: true
          },
          component: () => import('@/views/setting/devise/catePlantform')
        },
        {
          path: 'diy/merchant/category/list',
          name: 'categoryMerchant',
          meta: {
            title: '店铺分类列表',
            noCache: true
          },
          component: () => import('@/views/setting/devise/cateMerchant')
        },
        {
          path: 'diy/links/list',
          name: 'LinkList',
          meta: {
            title: '平台链接列表',
            noCache: true
          },
          component: () => import('@/views/setting/devise/linkList')
        },
        {
          path: 'diy/merLink/list',
          name: 'merLink',
          meta: {
            title: '店铺链接列表',
            noCache: true
          },
          component: () => import('@/views/setting/devise/merLink')
        },
        {
          path: 'theme_style',
          name: `ThemeStyle`,
          meta: {
            title: '一键换色',
            noCache: true
          },
          component: () => import('@/views/setting/themeStyle/index')
        },

        {
          path: 'agreements',
          name: `Agreements`,
          meta: {
            title: '协议与规则',
            noCache: true
          },
          component: () => import('@/views/setting/agreements/index')
        },
        {
          path: 'storage',
          name: `StorageManage`,
          meta: {
            title: '储存管理',
            noCache: true,
            activeMenu: `${roterPre}/setting/storage`
          },
          component: () => import('@/views/setting/storage/index')
        },
      ]
    }

export default settingRouter

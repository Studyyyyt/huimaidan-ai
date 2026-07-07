import Layout from '@/layout'
import { roterPre } from '@/settings'

const huimaidanAiRouter = {
  path: `${roterPre}/huimaidan`,
  name: 'HuimaidanRoot',
  meta: {
    icon: 's-shop',
    title: '惠买单'
  },
  alwaysShow: true,
  component: Layout,
  children: [
    {
      path: 'guide',
      name: 'HuimaidanGuide',
      meta: {
        title: '新手引导',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'guide' }
    },
    {
      path: 'import',
      name: 'HuimaidanMerchantImport',
      meta: {
        title: '商户导入/维护',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'import' }
    },
    {
      path: 'discount',
      name: 'HuimaidanDiscount',
      meta: {
        title: '惠买单优惠规则',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'discounts' }
    },
    {
      path: 'ai-tags',
      name: 'HuimaidanAiTags',
      meta: {
        title: 'AI标签库',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'tags' }
    },
    {
      path: 'merchant-tags',
      name: 'HuimaidanMerchantTags',
      meta: {
        title: '商家标签管理',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'merchantTags' }
    },
    {
      path: 'banner',
      name: 'HuimaidanBanner',
      meta: {
        title: 'Banner 配置',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'banners' }
    },
    {
      path: 'config',
      name: 'HuimaidanAiConfig',
      meta: {
        title: 'AI 配置向导',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'configs' }
    },
    {
      path: 'logs',
      name: 'HuimaidanAiLogs',
      meta: {
        title: '推荐日志',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'logs' }
    },
    {
      path: 'ai',
      name: 'HuimaidanAi',
      meta: {
        title: 'AI 推荐大脑',
        noCache: true
      },
      component: () => import('@/views/huimaidan/standaloneRedirect'),
      props: { tab: 'guide' }
    }
  ]
}

export default huimaidanAiRouter

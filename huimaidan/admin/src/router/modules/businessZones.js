import Layout from '@/layout'
import { roterPre } from '@/settings'

export default {
  path: `${roterPre}/business-zones`,
  name: 'businessZones',
  meta: {
    title: '区域代理'
  },
  alwaysShow: true,
  component: Layout,
  children: [
    {
      path: 'index',
      name: 'businessZonesIndex',
      meta: {
        title: '区域列表'
      },
      component: () => import('@/views/businessZones/index')
    },
    {
      path: 'create',
      name: 'businessZonesCreate',
      meta: {
        title: '添加区域',
        activeMenu: `${roterPre}/business-zones/index`,
      },
      component: () => import('@/views/businessZones/create')
    },
    {
      path: 'agents',
      name: 'businessZonesAgents',
      meta: {
        title: '代理人员'
      },
      component: () => import('@/views/businessZones/agents')
    },
    {
      path: 'agent-review',
      name: 'businessZonesAgentReview',
      meta: {
        title: '代理审核'
      },
      component: () => import('@/views/businessZones/agentReview')
    },
    {
      path: 'settings',
      name: 'businessZonesSettings',
      meta: {
        title: '代理设置'
      },
      component: () => import('@/views/businessZones/settings')
    }
  ]
}
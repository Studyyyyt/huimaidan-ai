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
const communityRouter =
  {
    path: `${roterPre}/community`,
    name: 'community',
    meta: {
      icon: '',
      title: '内容'
    },
    alwaysShow: true,
    component: Layout,
    children: [

      {
        path: 'list',
        name: 'list',
        meta: {
          title: '逛逛社区',
          noCache: true
        },
        component: () => import('@/views/community/communityList/index')
      },
       {
            path: 'list/addContent/:id?/:edit?',
            component: () => import('@/views/community/addContent'),
              name: 'addContent',
              meta: { title: '内容添加', noCache: true, activeMenu: `${roterPre}/community/list` },
              hidden: true
       }

    ]
  }
export default communityRouter

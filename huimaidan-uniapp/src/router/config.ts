import { getAllPages } from '@/utils'

export const LOGIN_STRATEGY_MAP = {
  DEFAULT_NO_NEED_LOGIN: 0, // 黑名单策略，默认可以进入APP
  DEFAULT_NEED_LOGIN: 1, // 白名单策略，默认不可以进入APP，需要强制登录
}
// TODO: 1/3 登录策略，默认使用`无需登录策略`，即默认不需要登录就可以访问
export const LOGIN_STRATEGY = LOGIN_STRATEGY_MAP.DEFAULT_NO_NEED_LOGIN
export const isNeedLoginMode = LOGIN_STRATEGY === LOGIN_STRATEGY_MAP.DEFAULT_NEED_LOGIN

export const LOGIN_PAGE = '/pages/auth/login'
export const REGISTER_PAGE = '/pages/auth/register'

export const LOGIN_PAGE_LIST = [LOGIN_PAGE, REGISTER_PAGE]

// 在 definePage 里面配置了 excludeLoginPath 的页面，功能与 EXCLUDE_LOGIN_PATH_LIST 相同
export const excludeLoginPathList = getAllPages('excludeLoginPath').map(page => page.path)

// 排除在外的列表，白名单策略指白名单列表，黑名单策略指黑名单列表
// TODO: 2/3 在 definePage 配置 excludeLoginPath，或者在下面配置 EXCLUDE_LOGIN_PATH_LIST
export const EXCLUDE_LOGIN_PATH_LIST = [
  '/pages/xxx/index', // 示例值
  '/pages-sub/xxx/index', // 示例值
  ...excludeLoginPathList, // 都是以 / 开头的 path
]

// 小程序默认放行路由时，仍需要强制登录的业务页面
export const FORCE_LOGIN_PATH_LIST = [
  '/pages/ai-chat/index',
]

// 商户管理页面路径（使用独立的 mer-token 认证，不走用户 token 校验）
export const MER_ADMIN_PATHS = [
  '/pages/admin/login',
  '/pages/admin/finance',
  '/pages/admin/profile',
]

// 在小程序里面是否使用H5的登录页，默认为 false
// 如果为 true 则复用 h5 的登录逻辑
// TODO: 3/3 确定自己的登录页是否需要在小程序里面使用
export const LOGIN_PAGE_ENABLE_IN_MP = false

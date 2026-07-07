// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import defaultSettings from '@/libs/settingMer'
import { roterPre } from "@/settings";
import store from "@/store";

const title = defaultSettings.title

// 部分公用页面的路由前缀
const commonPagePrefix = [
  `${roterPre}/systemForm/Basics/`,
  `${roterPre}/group/config/`,
];

export const getTitleByPath = path => {
  const isCommonPage = commonPagePrefix.some(prefix => path.startsWith(prefix));
  let pageTitle = null;
  if (isCommonPage) {
    const menuInfo = store.getters['user/menuMap'][path];
    if (menuInfo && menuInfo.title) {
      pageTitle = menuInfo.title;
    }
  }

  return pageTitle;
}

export default function getPageTitle(toPageInfo) {
  const { meta, path } = toPageInfo;
  let pageTitle = meta && meta.title ? meta.title : '';
  const commonPageTitle = getTitleByPath(path);
  if (commonPageTitle) {
    pageTitle = commonPageTitle;
  }
  if (pageTitle) {
    return `${pageTitle} - ${title}`
  }
  return `${title}`
}

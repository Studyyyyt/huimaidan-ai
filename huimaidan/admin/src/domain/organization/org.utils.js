import { ORG_TYPE_META } from './org.meta';

// 获取组织类型样式
export const getOrgTypeStyle = (orgType) => {
  return {
    color: ORG_TYPE_META[orgType].color,
    backgroundColor: ORG_TYPE_META[orgType].bgColor,
  }
}
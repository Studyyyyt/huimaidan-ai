import { ORG_TYPE } from './org.enum.js';

// 组织类型相关元数据
export const ORG_TYPE_META = {
  [ORG_TYPE.ZONE]: {
    label: '区域',
    color: "#FF9900",
    bgColor: "rgba(255, 153, 0, 0.0823529411764706)"
  },
  [ORG_TYPE.MERCHANT]: {
    label: '商户',
    color: "#0256FF",
    bgColor: "rgba(2, 86, 255, 0.0784313725490196)"
  },
  [ORG_TYPE.GROUP]: {
    label: '分组',
    color: "#0256FF",
    bgColor: "rgba(2, 86, 255, 0.0784313725490196)"
  }
};
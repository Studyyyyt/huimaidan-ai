// 区域配置数据key
export const ZONE_CONFIG_KEY = "circle_config";

// 代理状态枚举
export const AGENT_STATUS = {
  ALL: null, // 全部
  PENDING: 0, // 待审核
  APPROVED: 1, // 审核通过
  REJECTED: -1, // 审核拒绝
  CANCELLED: -2, // 已撤销
};

// 代理级别枚举
export const AGENT_LEVEL_MAP = {
  0: "一级", // 一级代理
  1: "二级", // 二级代理
  2: "三级", // 三级代理
};
import { AGENT_STATUS } from './agent.enum.js';

// 代理状态标签列表
export const AGENT_STATUS_LIST = [
  {
    label: "全部",
    value: AGENT_STATUS.ALL,
    key: "all",
    bgColor: "#fff",
    color: "#909399"
  },
  {
    label: "待审核", // 标签文本
    value: AGENT_STATUS.PENDING, // 状态枚举值
    key: "review", // 类型key，用于从接口中获取总数
    bgColor: "#fff7ea", // 背景颜色
    color: "#ff9900" // 文字颜色
  },
  {
    label: "已通过",
    value: AGENT_STATUS.APPROVED,
    key: "approver",
    bgColor: "#ebf2ff",
    color: "#025aff"
  },
  {
    label: "已拒绝",
    value: AGENT_STATUS.REJECTED,
    key: "rejected",
    bgColor: "#fef0ed",
    color: "#ed4014"
  },
  {
    label: "已撤销",
    value: AGENT_STATUS.CANCELLED,
    key: "revoked",
    bgColor: "#f8f8f8",
    color: "#909399"
  }
];

// 代理状态标签映射对象
// 状态 -> 数据
// example: { 0: { label: "待审核", value: 0, bgColor: "#fff7ea", color: "#ff9900" } }
export const AGENT_STATUS_MAP = AGENT_STATUS_LIST.slice(1).reduce((acc, item) => {
  acc[item.value] = item;
  return acc;
}, {});


// 用户搜索选项
export const USER_SEARCH_OPTIONS = [
  { label: "UID", value: "uid" },
  { label: "手机号", value: "user_phone" },
  { label: "用户昵称", value: "nickname" }
];
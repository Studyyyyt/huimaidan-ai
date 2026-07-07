import { PAYMENT_TYPE, CREDIT_STATUS } from './settlement.enum.js';

// 提现方式标签列表
export const PAYMENT_LIST = [
  {
    label: "银行卡",
    value: PAYMENT_TYPE.BANK
  },
  {
    label: "微信",
    value: PAYMENT_TYPE.WECHAT
  },
  {
    label: "支付宝",
    value: PAYMENT_TYPE.ALIPAY
  }
];

// 提现方式标签映射对象
export const PAYMENT_TYPE_MAP = PAYMENT_LIST.reduce((acc, item) => {
  acc[item.value] = item;
  return acc;
}, {});

// 到账状态标签列表
export const CREDIT_STATUS_LIST = [
  {
    label: "已到账",
    value: CREDIT_STATUS.SUCCESS,
    bgColor: "#ebf2ff",
    color: "#025aff"
  },
  {
    label: "未到账",
    value: CREDIT_STATUS.PENDING,
    bgColor: "#fff7ea", // 背景颜色
    color: "#ff9900" // 文字颜色
  }
];

// 到账状态标签映射对象
export const CREDIT_STATUS_MAP = CREDIT_STATUS_LIST.reduce((acc, item) => {
  acc[item.value] = item;
  return acc;
}, {});
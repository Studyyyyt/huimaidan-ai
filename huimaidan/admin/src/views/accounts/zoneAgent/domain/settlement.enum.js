// 提现方式枚举
export const PAYMENT_TYPE = {
  BANK: 0, // 银行卡
  WECHAT: 1, // 微信
  ALIPAY: 2, // 支付宝
};

// 到账状态枚举
export const CREDIT_STATUS = {
  SUCCESS: 1, // 已到账
  PENDING: 0 // 未到账
};

// 角色枚举
export const ROLE = {
  PLATFORM: "platform",
  AGENT: "agent",
}
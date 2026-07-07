import { TABLE_ACTION } from './settlement.actions.js';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';
import { CREDIT_STATUS, ROLE } from './settlement.enum.js';

// 生成表格按钮列表
// settlement: 结算记录
// role: 角色，agent -> 代理, platform -> 平台
export const generateActionList = (settlement, role) => {
  const config = [
    {
      text: "通过",
      action: TABLE_ACTION.PASS,
      condition: () => settlement.audit_status === AGENT_STATUS.PENDING,
      role: ROLE.PLATFORM
    },
    {
      text: "拒绝",
      action: TABLE_ACTION.REJECT,
      condition: () => settlement.audit_status === AGENT_STATUS.PENDING,
      role: ROLE.PLATFORM
    },
    {
      text: "转账",
      action: TABLE_ACTION.TRANSFER,
      condition: () => settlement.audit_status === AGENT_STATUS.APPROVED && settlement.status === CREDIT_STATUS.PENDING,
      role: ROLE.PLATFORM
    },
    {
      text: "备注",
      action: TABLE_ACTION.PLATFORM_REMARK,
      condition: () => true,
      role: ROLE.PLATFORM
    },
    {
      text: "撤销",
      action: TABLE_ACTION.CANCEL,
      condition: () => settlement.audit_status === AGENT_STATUS.PENDING && settlement.status === CREDIT_STATUS.PENDING,
      role: ROLE.AGENT
    },
    {
      text: "备注",
      action: TABLE_ACTION.AGENT_REMARK,
      condition: () => true,
      role: ROLE.AGENT
    }
  ];

  return config.filter(item => item.condition() && item.role === role);
}
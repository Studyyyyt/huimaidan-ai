import { PAYMENT_TYPE_MAP, CREDIT_STATUS_MAP } from './settlement.props.js';
import { AGENT_STATUS_MAP } from '@/views/businessZones/domain/agent.props.js';

export const TABLE_FIELD_OPTIONS = [
  {
    field: "withdrawal_sn",
    label: "订单编号"
  },
  {
    field: "withdrawal_amount",
    label: "提现金额",
    formatter: row => `￥${row.withdrawal_amount}`
  },
  {
    field: "withdrawal_type",
    label: "提现方式",
    formatter: row => PAYMENT_TYPE_MAP[row.withdrawal_type].label
  },
  {
    field: "audit_status",
    label: "审核状态",
    formatter: row => AGENT_STATUS_MAP[row.audit_status].label,
    renderTag: {
      bgColor: row => AGENT_STATUS_MAP[row.audit_status].bgColor,
      color: row => AGENT_STATUS_MAP[row.audit_status].color,
      condition: row => !!AGENT_STATUS_MAP[row.audit_status]
    }
  },
  {
    field: "status",
    label: "到账状态",
    formatter: row => CREDIT_STATUS_MAP[row.status].label,
    renderTag: {
      bgColor: row => CREDIT_STATUS_MAP[row.status].bgColor,
      color: row => CREDIT_STATUS_MAP[row.status].color,
      condition: row => !!CREDIT_STATUS_MAP[row.status]
    }
  },
  {
    field: "agent_name",
    label: "代理姓名"
  },
  {
    field: "agent_phone",
    label: "联系电话"
  },
  {
    field: "create_time",
    label: "申请时间"
  }
];
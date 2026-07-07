import { PAYMENT_TYPE } from '@/views/accounts/zoneAgent/domain/settlement.enum.js';
import { PAYMENT_TYPE_MAP } from '@/views/accounts/zoneAgent/domain/settlement.props.js';
import { ORG_TYPE } from '@/domain/organization/org.enum.js';

// 将代理信息格式化为申请表单列表
export function normalizeAgentApplicationInfo(agent) {
  const baseForm = [
    {
      title: agent.type === ORG_TYPE.ZONE ? "代理名称" : "商户名称",
      value: agent.type === ORG_TYPE.ZONE ? agent.name : agent.business_name
    },
    {
      title: "联系电话",
      value: agent.phone
    },
    {
      title: "说明",
      value: agent.remark
    },
    {
      title: agent.type === ORG_TYPE.ZONE ? "身份资质" : "商户资质",
      type: "image",
      value: agent.qualification
    },
    {
      title: "申请时间",
      value: agent.create_time
    }
  ];

  if (agent.type === ORG_TYPE.MERCHANT) {
    baseForm.splice(1, 0, {
      title: "管理姓名",
      value: agent.user ? agent.user.nickname : ""
    });
  }

  if (!agent.extend) return baseForm;

  for (const [key, value] of Object.entries(agent.extend)) {
    const item = {
      title: key,
      value
    };
    if (Array.isArray(value)) {
      if (value.length) {
        if (value[0].startsWith("http")) {
          item.type = "image";
        }
      } else {
        item.value = "";
      }
    }
    baseForm.push(item);
  }

  return baseForm;
}

// 将结算账号信息格式化为列表
export function normalizeSettlementAccount(agent) {
  if (!PAYMENT_TYPE_MAP[agent.payment_method]) return null;
  const info = {
    title: PAYMENT_TYPE_MAP[agent.payment_method].label,
    fields: [
      {
        title: "姓名",
        value: agent.payment_name
      }
    ]
  };

  if (agent.payment_method === PAYMENT_TYPE.BANK) {
    info.fields.push({
      title: "银行卡号",
      value: agent.payment_account
    });

    info.fields.push({
      title: "开户行",
      value: agent.payment_bank
    });
  } else if (agent.payment_method === PAYMENT_TYPE.WECHAT) {
    info.fields.push({
      title: "微信号",
      value: agent.payment_account
    });
    info.fields.push({
      title: "收款二维码",
      type: "image",
      value: agent.payment_qr_img
    });
  } else if (agent.payment_method === PAYMENT_TYPE.ALIPAY) {
    info.fields.push({
      title: "支付宝账号",
      value: agent.payment_account
    });
    info.fields.push({
      title: "收款二维码",
      type: "image",
      value: agent.payment_qr_img
    });
  }

  return info;
}
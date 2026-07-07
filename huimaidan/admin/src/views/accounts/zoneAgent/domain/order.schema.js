import { RECORD_STATUS } from './order.enum.js';
import { ORDER_STATUS_MAP } from './order.props.js';

// 渲染表格和导出表格的相关配置
export const TABLE_FIELD_OPTIONS = [
  {
    field: "order_sn",
    label: "订单编号"
  },
  {
    field: "mer_name",
    label: "店铺名称"
  },
  {
    field: "circle_name",
    label: "所属区域"
  },
  {
    field: "agent_name",
    label: "区域代理"
  },
  {
    field: "order_total_price",
    label: "订单支付",
    formatter: row => `￥${row.order_total_price}`
  },
  {
    field: "order_status",
    label: "订单状态",
    formatter: row => ORDER_STATUS_MAP[row.order_status].label,
    renderTag: {
      bgColor: row => ORDER_STATUS_MAP[row.order_status].bgColor,
      color: row => ORDER_STATUS_MAP[row.order_status].color,
      condition: row => !!ORDER_STATUS_MAP[row.order_status]
    }
  },
  {
    field: "amount",
    label: "提成金额",
    formatter: row => `￥${row.amount}`,
    labelList: row => {
      return [
        {
          label: '不可提现',
          color: '#ED4014',
          condition: row.status === RECORD_STATUS.REFUND
        },
        {
          label: '冻结中',
          color: '#FF9900',
          condition: row.status === RECORD_STATUS.FREEZE
        }
      ].filter(item => item.condition);
    }
  },
  {
    field: "order_create_time",
    label: "下单时间",
    width: 145
  }
];
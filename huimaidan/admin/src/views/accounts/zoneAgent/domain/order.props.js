// 订单状态标签配置
export const ORDER_STATUS_LIST = [
  {
    label: "已退款",
    value: -1,
    color: "#ED4014",
    bgColor: "#ed401414"
  },
  {
    label: "待发货",
    value: 0,
    color: "#FF9900",
    bgColor: "#ff990015"
  },
  {
    label: "待收货",
    value: 1
  },
  {
    label: "待评价",
    value: 2,
    color: "#025aff",
    bgColor: "#025aff15"
  },
  {
    label: "已完成",
    value: 3,
    color: "#00B42A",
    bgColor: "#00B42A15"
  }
];

// 订单状态标签映射对象
export const ORDER_STATUS_MAP = ORDER_STATUS_LIST.reduce((acc, item) => {
  acc[item.value] = item;
  return acc;
}, {});
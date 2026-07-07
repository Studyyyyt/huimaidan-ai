export const SETTLEMENT_INFO_FORM_CONFIG = [
  {
    type: "text",
    field: "settlement_id",
    label: "结算规则ID",
    tips: [
      `详情参见：<a target="_blank" href="https://kf.qq.com/faq/220228IJb2UV220228uEjU3Q.html">费率结算规则对照表</a>`
    ],
    required: true,
  },
  {
    type: "text",
    field: "qualification_type",
    label: "所属行业",
    tips: [
      `详情参见：<a target="_blank" href="https://kf.qq.com/faq/220228IJb2UV220228uEjU3Q.html">费率结算规则对照表</a>`
    ],
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "qualifications",
    label: "资质图片列表",
    max: 5
  },
  // {
  //   type: "text",
  //   field: "activities_id",
  //   label: "优惠费率活动ID",
  //   tips: [
  //     `详情参见：<a target="_blank" href="https://pay.weixin.qq.com/doc/v3/partner/4012082816">优惠费率活动</a>`
  //   ]
  // },
  // {
  //   type: "text",
  //   field: "activities_rate",
  //   label: "优惠费率"
  // },
  // {
  //   type: "upload",
  //   accept: "image",
  //   field: "activities_additions",
  //   label: "优惠费率活动补充材料",
  //   max: 5
  // },
  // {
  //   type: "text",
  //   field: "debit_activities_rate",
  //   label: "非信用卡活动费率值"
  // },
  // {
  //   type: "text",
  //   field: "credit_activities_rate",
  //   label: "信用卡活动费率值"
  // }
];
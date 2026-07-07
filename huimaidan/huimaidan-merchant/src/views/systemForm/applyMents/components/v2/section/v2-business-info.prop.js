import { SALES_SCENE_TYPE } from "../v2.enum";

// 销售场景类型列表
export const SALES_SCENE_TYPE_LIST = [
  {
    value: SALES_SCENE_TYPE.SALES_SCENES_STORE,
    label: "线下场所"
  },
  {
    value: SALES_SCENE_TYPE.SALES_SCENES_MP,
    label: "服务号与公众号"
  },
  {
    value: SALES_SCENE_TYPE.SALES_SCENES_MINI_PROGRAM,
    label: "小程序"
  },
  {
    value: SALES_SCENE_TYPE.SALES_SCENES_WEB,
    label: "互联网网站"
  },
  {
    value: SALES_SCENE_TYPE.SALES_SCENES_APP,
    label: "App"
  },
  // {
  //   value: SALES_SCENE_TYPE.SALES_SCENES_WEWORK,
  //   label: "企业微信"
  // },
];



// 商户简称、客服电话
export const COMMON_FORM_CONFIG = [
  {
    type: "text",
    field: "merchant_shortname",
    label: "商户简称",
    min: 1,
    max: 64,
    required: true,
  },
  {
    type: "text",
    field: "service_phone",
    label: "客服电话",
    min: 1,
    max: 32,
    required: true,
  }
];

// 经营场景
export const SCENE_FORM_CONFIG = [
  {
    type: "checkbox",
    field: "sales_scenes_type",
    label: "经营场景",
    options: SALES_SCENE_TYPE_LIST,
    required: true,
  }
];

// 线下场所场景
export const STORE_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "biz_store_name",
    label: "线下场所名称",
    min: 1,
    max: 50,
    required: true,
  },
  {
    type: "text",
    field: "biz_address_code",
    label: "线下场所省市编码",
    tips: [
      `详情参见：<a target="_blank" href="https://pay.weixin.qq.com/doc/v3/partner/4012082815">微信支付提供的省市对照表</a>`
    ],
    required: true,
  },
  {
    type: "text",
    field: "biz_store_address",
    label: "线下场所地址",
    min: 4,
    max: 512,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "store_entrance_pic",
    label: "线下场所门头照片",
    max: 20,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "indoor_pic",
    label: "线下场所内部照片",
    max: 20,
    required: true,
  },
  {
    type: "text",
    field: "biz_sub_appid",
    label: "线下场所对应的商家AppID",
    min: 1,
    max: 256,
    required: true,
  }
];

// 服务号与公众号场景
export const MP_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "mp_appid",
    label: "服务商服务号或公众号AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "text",
    field: "mp_sub_appid",
    label: "商家服务号或公众号AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "mp_pics",
    label: "服务号或公众号页面截图",
    max: 5,
    required: true,
  }
];

// 小程序场景
export const MINI_PROGRAM_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "mini_program_appid",
    label: "服务商小程序AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "text",
    field: "mini_program_sub_appid",
    label: "商家小程序AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "mini_program_pics",
    label: "小程序截图",
    max: 5,
    required: true,
  }
];

// 互联网网站场景
export const WEB_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "domain",
    label: "互联网网站域名",
    min: 1,
    max: 1024,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "web_authorisation",
    label: "网站授权函",
    required: true,
  },
  {
    type: "text",
    field: "web_appid",
    label: "互联网网站对应的商家AppID",
    min: 1,
    max: 256,
    required: true,
  }
];

// App场景
export const APP_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "app_appid",
    label: "服务商应用AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "text",
    field: "app_sub_appid",
    label: "商家应用AppID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "app_pics",
    label: "App截图",
    max: 4,
    required: true,
  }
];

// 企业微信场景
export const WEWORK_SCENE_FORM_CONFIG = [
  {
    type: "text",
    field: "sub_corp_id",
    label: "商家企业微信CorpID",
    min: 1,
    max: 256,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "wework_pics",
    label: "企业微信页面截图",
    max: 5,
    required: true,
  }
];
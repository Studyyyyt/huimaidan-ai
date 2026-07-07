import { IDENTIFICATION_TYPE, SUBJECT_TYPE, FINANCE_TYPE } from "./v2.enum";

// 证件类型
export const IDENTIFICATION_TYPE_LIST = [
  {
    value: IDENTIFICATION_TYPE.IDCARD,
    label: "中国大陆居民-身份证"
  },
  {
    value: IDENTIFICATION_TYPE.OVERSEA_PASSPORT,
    label: "其他国家或地区居民-护照"
  },
  {
    value: IDENTIFICATION_TYPE.HONGKONG_PASSPORT,
    label: "中国香港居民-来往内地通行证"
  },
  {
    value: IDENTIFICATION_TYPE.MACAO_PASSPORT,
    label: "中国澳门居民-来往内地通行证"
  },
  {
    value: IDENTIFICATION_TYPE.TAIWAN_PASSPORT,
    label: "中国台湾居民-来往大陆通行证"
  },
  {
    value: IDENTIFICATION_TYPE.FOREIGN_RESIDENT,
    label: "外国人居留证"
  },
  {
    value: IDENTIFICATION_TYPE.HONGKONG_MACAO_RESIDENT,
    label: "港澳居民居住证"
  },
  {
    value: IDENTIFICATION_TYPE.TAIWAN_RESIDENT,
    label: "台湾居民居住证"
  }
];

// 主体类型
export const SUBJECT_TYPE_LIST = [
  {
    value: SUBJECT_TYPE.INDIVIDUAL,
    label: "个体户"
  },
  {
    value: SUBJECT_TYPE.ENTERPRISE,
    label: "企业"
  },
  {
    value: SUBJECT_TYPE.GOVERNMENT,
    label: "政府机关"
  },
  {
    value: SUBJECT_TYPE.INSTITUTIONS,
    label: "事业单位"
  },
  {
    value: SUBJECT_TYPE.OTHERS,
    label: "社会组织"
  }
];


// 金融机构类型列表
export const FINANCE_TYPE_LIST = [
  {
    value: FINANCE_TYPE.BANK_AGENT,
    label: "银行业"
  },
  {
    value: FINANCE_TYPE.PAYMENT_AGENT,
    label: "支付机构"
  },
  {
    value: FINANCE_TYPE.INSURANCE,
    label: "保险业"
  },
  {
    value: FINANCE_TYPE.TRADE_AND_SETTLE,
    label: "交易及结算类金融机构"
  },
  {
    value: FINANCE_TYPE.OTHER,
    label: "其他金融机构"
  }
];

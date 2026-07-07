import { SUBJECT_TYPE } from "./subject.enum";

export const SUBJECT_TYPE_META = {
  [SUBJECT_TYPE.PLATFORM]: {
    label: "平台",
    color: "#fff",
    bgColor: "#909399"
  },
  [SUBJECT_TYPE.MERCHANT]: {
    label: "商户",
    color: "#025aff",
    bgColor: "#ebf2ff"
  },
  [SUBJECT_TYPE.REGION]: {
    label: "区域",
    color: "#ff9900",
    bgColor: "#fff7ea"
  }
};

export const SUBJECT_TYPE_LIST = [
  {
    name: "平台",
    value: SUBJECT_TYPE.PLATFORM
  },
  {
    name: "商户",
    value: SUBJECT_TYPE.MERCHANT
  },
  {
    name: "区域",
    value: SUBJECT_TYPE.REGION
  }
];
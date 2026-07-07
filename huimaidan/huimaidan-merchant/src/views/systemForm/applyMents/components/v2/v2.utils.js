import moment from "moment";
import { PERSON_TYPE, SUBJECT_TYPE } from "./v2.enum";

export const currentDate = moment(); // 当前日期
export const minDate = moment("1900-01-01"); // 允许的最小日期

// 生成有效期开始日期选项
export const generateBeginDateOptions = () => {
  return {
    disabledDate: (time) => {
      return moment(time).isBefore(minDate) || moment(time).isAfter(currentDate);
    }
  }
}

// 生成有效期结束日期选项
export const generateEndDateOptions = () => {
  return {
    disabledDate: (time) => {
      return moment(time).isBefore(currentDate);
    }
  }
}

// 生成初始受益人信息
export const generateUboInfo = () => {
  return {
    ubo_id_doc_type: "", // 受益人的证件类型
    ubo_id_doc_copy: null,
    ubo_id_doc_copy_back: null,
    ubo_id_doc_name: "",
    ubo_id_doc_number: "",
    ubo_id_doc_address: "",
    ubo_period_begin: "",
    ubo_period_longterm: true,
    ubo_period_end: ""
  };
}

// 生成经办人类型超级管理员默认信息
export const generateSuperContactInfo = () => {
  return {
    contact_id_doc_type: "", // 超级管理员证件类型
    contact_id_number: "", // 超级管理员身份证件号码
    contact_id_doc_copy: "", // 证件正面照片
    contact_id_doc_copy_back: "", // 证件反面照片
    contact_period_begin: "", // 超级管理员证件有效期开始时间
    contact_period_longterm: false, // 证件是否长期有效
    contact_period_end: "", // 超级管理员证件有效期结束时间
  };
};

// 生成主体经营者/法定代表人身份证件默认信息
export const generateSubjectIdentityInfo = () => {
  return {
    id_holder_type: PERSON_TYPE.LEGAL, // 证件持有人类型
    id_doc_type: "", // 证件类型
    authorize_letter_copy: null, // 法定代表人说明函
    id_card_info: { // 身份证信息
      id_card_copy: null, // 身份证人像面照片
      id_card_national: null, // 身份证国徽面照片
      id_card_name: "", // 身份证姓名
      id_card_number: "", // 身份证号码
      id_card_address: "", // 身份证地址
      card_period_begin: "", // 身份证有效期开始时间
      card_period_longterm: true, // 身份证是否长期有效
      card_period_end: "", // 身份证有效期结束时间
    },
    id_doc_info: { // 其他类型证件信息
      id_doc_copy: null, // 证件照片
      id_doc_copy_back: null, // 证件反面照片
      id_doc_name: "", // 证件姓名
      id_doc_number: "", // 证件号码
      id_doc_address: "", // 证件地址
      doc_period_begin: "", // 证件有效期开始时间
      doc_period_longterm: true, // 证件是否长期有效
      doc_period_end: "", // 证件有效期结束时间
    }
  };
}

// 生成主体资料默认信息
export const generateSubjectInfo = () => {
  return {
    subject_type: SUBJECT_TYPE.ENTERPRISE, // 主体类型
    finance_institution: false, // 是否为金融机构

    business_license_info: { // 营业执照 主体为个体户/企业，必填
      license_copy: null, // 营业执照照片
      license_number: "", // 统一社会信用代码
      merchant_name: "", // 商户名称
      legal_person: "", // 经营者/法人姓名
      license_address: "", // 注册地址
      period_begin: "", // 有效期开始日期
      period_longterm: true, // 是否长期有效
      period_end: "", // 有效期结束日期
    },

    certificate_info: { // 登记证书 主体为政府机关/事业单位/其他组织时，必填
      cert_copy: null, // 登记证书照片
      cert_type: "", // 登记证书类型
      cert_number: "", // 证书号
      merchant_name: "", // 商户名称
      company_address: "", // 登记证书的注册地址
      legal_person: "", //法人
      period_begin: "", //有效期限开始日期
      period_longterm: true, // 是否长期有效
      period_end: "", // 有效期限结束日期
    },

    certificate_letter_copy: null, //单位证明函照片 主体类型为政府机关、事业单位选传

    finance_institution_info: { // 金融机构许可证信息
      finance_type: "", //金融机构类型
      finance_license_pics: [], // 金融机构许可证图片
    },

    identity_info: generateSubjectIdentityInfo(), // 主体经营者/法定代表人身份证件默认信息

    ubo_info_list: [], // 最终受益人信息列表，仅企业填写
  };
};

// 获取目标对象中指定key的值
export const getTargetValue = (target, key) => {
  if (target && key in target) {
    return target[key];
  }
  return undefined;
}

// 判断是否为纯对象（非数组、函数、日期、正则等）
export const isPlainObject = (val) => {
  if (Object.prototype.toString.call(val) !== "[object Object]") return false;
  const proto = Object.getPrototypeOf(val);
  return proto === Object.prototype || proto === null;
};

// 判断对象是否存在指定属性
export const hasOwn = (obj, key) => Object.prototype.hasOwnProperty.call(obj, key);

// 判断是否为布尔值类型
export const isBoolean = (val) => typeof val === "boolean";

// 从源对象中合并默认值到目标对象
// 如果源对象中不存在该属性，则保持源对象的默认值不变
export const mergeDefaultValue = (target, source) => {
  // 如果目标对象或源对象不是纯对象，则直接返回目标对象
  if (!isPlainObject(target) || !isPlainObject(source)) return target;

  for (const key of Object.keys(target)) {
    // 如果源对象不存在该属性，则跳过
    if (!hasOwn(source, key)) continue;
    const value = source[key];

    // 如果源对象的值为undefined或null，则跳过
    if (value === undefined || value === null) continue;
    const defaultValue = target[key];

    // 如果目标对象的值为布尔值类型，而源对象的值为非布尔值类型，则将源对象的值转换为布尔值
    if (isBoolean(defaultValue) && !isBoolean(value)) {
      target[key] = Boolean(value);

    } else if (isPlainObject(defaultValue) && isPlainObject(value)) {
      // 如果默认值和源对象的值都是纯对象，则递归合并
      mergeDefaultValue(defaultValue, value);
    } else {
      target[key] = value;
    }
  }
}
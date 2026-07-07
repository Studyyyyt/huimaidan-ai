// 引入或定义必要的枚举值，与 v2.enum.js 保持一致
const PERSON_TYPE = {
  LEGAL: "LEGAL", // 经营者/法定代表人
  SUPER: "SUPER" // 经办人
};

const SUBJECT_TYPE = {
  INDIVIDUAL: "SUBJECT_TYPE_INDIVIDUAL", // 个体户
  ENTERPRISE: "SUBJECT_TYPE_ENTERPRISE", // 企业
  GOVERNMENT: "SUBJECT_TYPE_GOVERNMENT", // 政府机关
  INSTITUTIONS: "SUBJECT_TYPE_INSTITUTIONS", // 事业单位
  OTHERS: "SUBJECT_TYPE_OTHERS", // 其他组织
};

const SALES_SCENE_TYPE = {
  SALES_SCENES_STORE: "SALES_SCENES_STORE", // 线下场所
  SALES_SCENES_MP: "SALES_SCENES_MP", // 服务号与公众号
  SALES_SCENES_MINI_PROGRAM: "SALES_SCENES_MINI_PROGRAM", // 小程序
  SALES_SCENES_WEB: "SALES_SCENES_WEB", // 互联网网站
  SALES_SCENES_APP: "SALES_SCENES_APP", // App
  SALES_SCENES_WEWORK: "SALES_SCENES_WEWORK", // 企业微信
};

const BANK_ACCOUNT_TYPE = {
  CORPORATE: "BANK_ACCOUNT_TYPE_CORPORATE", // 对公银行账户
  PERSONAL: "BANK_ACCOUNT_TYPE_PERSONAL", // 经营者个人银行卡
};

export const IDENTIFICATION_TYPE = {
  IDCARD: "IDENTIFICATION_TYPE_IDCARD", // 身份证
  OVERSEA_PASSPORT: "IDENTIFICATION_TYPE_OVERSEA_PASSPORT", // 护照
  HONGKONG_PASSPORT: "IDENTIFICATION_TYPE_HONGKONG_PASSPORT", // 中国香港居民-来往内地通行证
  MACAO_PASSPORT: "IDENTIFICATION_TYPE_MACAO_PASSPORT", // 中国澳门居民-来往内地通行证
  TAIWAN_PASSPORT: "IDENTIFICATION_TYPE_TAIWAN_PASSPORT", // 中国台湾居民-来往大陆通行证
  FOREIGN_RESIDENT: "IDENTIFICATION_TYPE_FOREIGN_RESIDENT", // 外国人居留证
  HONGKONG_MACAO_RESIDENT: "IDENTIFICATION_TYPE_HONGKONG_MACAO_RESIDENT", // 港澳居民居住证
  TAIWAN_RESIDENT: "IDENTIFICATION_TYPE_TAIWAN_RESIDENT" // 台湾居民居住证
};

export const IDENTIFICATION_TYPE_MAP = {
  [IDENTIFICATION_TYPE.IDCARD]: "身份证",
  [IDENTIFICATION_TYPE.OVERSEA_PASSPORT]: "护照",
  [IDENTIFICATION_TYPE.HONGKONG_PASSPORT]: "中国香港居民-来往内地通行证",
  [IDENTIFICATION_TYPE.MACAO_PASSPORT]: "中国澳门居民-来往内地通行证",
  [IDENTIFICATION_TYPE.TAIWAN_PASSPORT]: "中国台湾居民-来往大陆通行证",
  [IDENTIFICATION_TYPE.FOREIGN_RESIDENT]: "外国人居留证",
  [IDENTIFICATION_TYPE.HONGKONG_MACAO_RESIDENT]: "港澳居民居住证",
  [IDENTIFICATION_TYPE.TAIWAN_RESIDENT]: "台湾居民居住证"
};

const SALES_SCENE_TYPE_MAP = {
  [SALES_SCENE_TYPE.SALES_SCENES_STORE]: "线下场所",
  [SALES_SCENE_TYPE.SALES_SCENES_MP]: "服务号与公众号",
  [SALES_SCENE_TYPE.SALES_SCENES_MINI_PROGRAM]: "小程序",
  [SALES_SCENE_TYPE.SALES_SCENES_WEB]: "互联网网站",
  [SALES_SCENE_TYPE.SALES_SCENES_APP]: "App",
  [SALES_SCENE_TYPE.SALES_SCENES_WEWORK]: "企业微信"
};

const SUBJECT_TYPE_MAP = {
  [SUBJECT_TYPE.INDIVIDUAL]: "个体户",
  [SUBJECT_TYPE.ENTERPRISE]: "企业",
  [SUBJECT_TYPE.GOVERNMENT]: "政府机关",
  [SUBJECT_TYPE.INSTITUTIONS]: "事业单位",
  [SUBJECT_TYPE.OTHERS]: "其他组织"
};

// 辅助函数：判断是否为私营机构（个体户/企业）
const isPrivateOrg = (form) => {
  return [SUBJECT_TYPE.INDIVIDUAL, SUBJECT_TYPE.ENTERPRISE].includes(form.subject_info.subject_type);
};

// 辅助函数：格式化日期范围
const formatPeriod = (begin, end, isLongTerm) => {
  if (isLongTerm) return `${begin} - 长期`;
  return `${begin} - ${end}`;
};

// --- 配置开始 ---

const subModuleConfig = {
  // 1. 主体资料
  subjectInfo: [
    {
      label: "主体类型",
      value: form => {
        return SUBJECT_TYPE_MAP[form.subject_info.subject_type];
      }
    },
    {
      label: "是否是金融机构",
      value: form => form.subject_info.finance_institution ? "是" : "否"
    },
    {
      label: "单位证明函照片",
      type: "image",
      value: form => form.subject_info.certificate_letter_copy, // 图片URL
      visible: form => !isPrivateOrg(form)
    },
    // 金融机构信息
    {
      label: "金融机构类型",
      value: form => form.subject_info.finance_institution_info.finance_type,
      visible: form => form.subject_info.finance_institution
    },
    {
      label: "金融机构许可证图片",
      type: "image",
      value: form => form.subject_info.finance_institution_info.finance_license_pics, // 图片列表
      visible: form => form.subject_info.finance_institution
    },
    // 营业执照 (个体户/企业)
    {
      label: "营业执照照片",
      type: "image",
      value: form => form.subject_info.business_license_info.license_copy,
      visible: form => isPrivateOrg(form)
    },
    {
      label: "统一社会信用代码",
      value: form => form.subject_info.business_license_info.license_number,
      visible: form => isPrivateOrg(form)
    },
    {
      label: "商户名称",
      value: form => form.subject_info.business_license_info.merchant_name,
      visible: form => isPrivateOrg(form)
    },
    {
      label: "经营者/法人姓名",
      value: form => form.subject_info.business_license_info.legal_person,
      visible: form => isPrivateOrg(form)
    },
    {
      label: "注册地址",
      value: form => form.subject_info.business_license_info.license_address,
      visible: form => isPrivateOrg(form)
    },
    {
      label: "营业执照有效期",
      value: form => formatPeriod(
        form.subject_info.business_license_info.period_begin,
        form.subject_info.business_license_info.period_end,
        form.subject_info.business_license_info.period_longterm
      ),
      visible: form => isPrivateOrg(form)
    },
    // 登记证书 (政府/事业单位/其他)
    {
      label: "登记证书照片",
      type: "image",
      value: form => form.subject_info.certificate_info.cert_copy,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "登记证书类型",
      value: form => form.subject_info.certificate_info.cert_type,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "证书号",
      value: form => form.subject_info.certificate_info.cert_number,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "商户名称",
      value: form => form.subject_info.certificate_info.merchant_name,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "注册地址",
      value: form => form.subject_info.certificate_info.company_address,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "法人姓名",
      value: form => form.subject_info.certificate_info.legal_person,
      visible: form => !isPrivateOrg(form)
    },
    {
      label: "登记证书有效期",
      value: form => formatPeriod(
        form.subject_info.certificate_info.period_begin,
        form.subject_info.certificate_info.period_end,
        form.subject_info.certificate_info.period_longterm
      ),
      visible: form => !isPrivateOrg(form)
    },
    // 经营者/法定代表人身份证件
    {
      label: "证件持有人类型",
      value: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL ? "经营者/法定代表人" : "经办人"
    },
    {
      label: "证件类型",
      value: form => IDENTIFICATION_TYPE_MAP[form.subject_info.identity_info.id_doc_type],
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL
    },
    {
      label: "法定代表人说明函",
      type: "image",
      value: form => form.subject_info.identity_info.authorize_letter_copy,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL
    },
    // 身份证详情 (当证件类型为身份证且是法人时)
    {
      label: "身份证人像面照片",
      type: "image",
      value: form => form.subject_info.identity_info.id_card_info.id_card_copy,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type === IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "身份证国徽面照片",
      type: "image",
      value: form => form.subject_info.identity_info.id_card_info.id_card_national,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type === IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "身份证姓名",
      value: form => form.subject_info.identity_info.id_card_info.id_card_name,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type === IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "身份证号码",
      value: form => form.subject_info.identity_info.id_card_info.id_card_number,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type === IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "身份证有效期",
      value: form => formatPeriod(
        form.subject_info.identity_info.id_card_info.card_period_begin,
        form.subject_info.identity_info.id_card_info.card_period_end,
        form.subject_info.identity_info.id_card_info.card_period_longterm
      ),
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type === IDENTIFICATION_TYPE.IDCARD
    },
    // 其他证件详情 (当证件类型不是身份证且是法人时)
    {
      label: "证件正面照片",
      type: "image",
      value: form => form.subject_info.identity_info.id_doc_info.id_doc_copy,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type !== IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "证件姓名",
      value: form => form.subject_info.identity_info.id_doc_info.id_card_name,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type !== IDENTIFICATION_TYPE.IDCARD
    },
    {
      label: "证件号码",
      value: form => form.subject_info.identity_info.id_doc_info.id_card_number,
      visible: form => form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL && form.subject_info.identity_info.id_doc_type !== IDENTIFICATION_TYPE.IDCARD
    },
    // 最终受益人 (企业类型展示)
    {
      label: "最终受益人列表",
      type: "ubo",
      value: form => form.subject_info.ubo_info_list, // 这是一个数组， 可能需要特殊渲染
      visible: form => form.subject_info.subject_type === SUBJECT_TYPE.ENTERPRISE && form.subject_info.ubo_info_list
    }
  ],

  // 2. 超级管理员信息
  contactInfo: [
    {
      label: "超级管理员类型",
      value: form => form.contact_info.contact_type === PERSON_TYPE.LEGAL ? "经营者/法定代表人" : "经办人"
    },
    {
      label: "超级管理员姓名",
      value: form => form.contact_info.contact_name
    },
    // 经办人特定字段
    {
      label: "证件类型",
      value: form => IDENTIFICATION_TYPE_MAP[form.contact_info.contact_id_doc_type],
      visible: form => form.contact_info.contact_type === PERSON_TYPE.SUPER
    },
    {
      label: "证件号码",
      value: form => form.contact_info.contact_id_number,
      visible: form => form.contact_info.contact_type === PERSON_TYPE.SUPER
    },
    {
      label: "证件正面照片",
      type: "image",
      value: form => form.contact_info.contact_id_doc_copy,
      visible: form => form.contact_info.contact_type === PERSON_TYPE.SUPER
    },
    {
      label: "证件反面照片",
      type: "image",
      value: form => form.contact_info.contact_id_doc_copy_back,
      visible: form => form.contact_info.contact_type === PERSON_TYPE.SUPER
    },
    {
      label: "证件有效期",
      value: form => formatPeriod(
        form.contact_info.contact_period_begin,
        form.contact_info.contact_period_end,
        form.contact_info.contact_period_longterm
      ),
      visible: form => form.contact_info.contact_type === PERSON_TYPE.SUPER
    },
    // 公共字段
    {
      label: "联系手机",
      value: form => form.contact_info.mobile_phone
    },
    {
      label: "联系邮箱",
      value: form => form.contact_info.contact_email
    }
  ],

  // 3. 经营资料
  businessInfo: [
    {
      label: "商户简称",
      value: form => form.business_info.merchant_shortname
    },
    {
      label: "客服电话",
      value: form => form.business_info.service_phone
    },
    {
      label: "经营场景",
      value: form => {
        const scenes = form.business_info.sales_info.sales_scenes_type || [];
        // 这里可以映射回中文，为了简单直接返回代码
        return scenes.map(scene => SALES_SCENE_TYPE_MAP[scene]).join(", ");
      }
    },
    // 线下场所
    {
      label: "线下场所名称",
      value: form => form.business_info.sales_info.biz_store_info.biz_store_name,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_STORE)
    },
    {
      label: "线下场所省市编码",
      value: form => form.business_info.sales_info.biz_store_info.biz_address_code,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_STORE)
    },
    {
      label: "线下场所地址",
      value: form => form.business_info.sales_info.biz_store_info.biz_store_address,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_STORE)
    },
    {
      label: "线下场所门头照片",
      type: "image",
      value: form => form.business_info.sales_info.biz_store_info.store_entrance_pic,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_STORE)
    },
    // 公众号
    {
      label: "服务商服务号或公众号AppID",
      value: form => form.business_info.sales_info.mp_info.mp_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_MP)
    },
    {
      label: "商家服务号或公众号AppID",
      value: form => form.business_info.sales_info.mp_info.mp_sub_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_MP)
    },
    // 小程序
    {
      label: "服务商小程序AppID",
      value: form => form.business_info.sales_info.mini_program_info.mini_program_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_MINI_PROGRAM)
    },
    {
      label: "商家小程序AppID",
      value: form => form.business_info.sales_info.mini_program_info.mini_program_sub_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_MINI_PROGRAM)
    },
    // 网站
    {
      label: "互联网网站域名",
      value: form => form.business_info.sales_info.web_info.domain,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_WEB)
    },
    {
      label: "网站授权函",
      value: form => form.business_info.sales_info.web_info.web_authorisation,
      type: "image",
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_WEB)
    },
    // App
    {
      label: "服务商应用AppID",
      value: form => form.business_info.sales_info.app_info.app_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_APP)
    },
    {
      label: "商家应用AppID",
      value: form => form.business_info.sales_info.app_info.app_sub_appid,
      visible: form => form.business_info.sales_info.sales_scenes_type.includes(SALES_SCENE_TYPE.SALES_SCENES_APP)
    }
  ],

  // 4. 结算规则
  settlementInfo: [
    {
      label: "结算规则ID",
      value: form => form.settlement_info.settlement_id
    },
    {
      label: "所属行业",
      value: form => form.settlement_info.qualification_type
    },
    {
      label: "资质图片列表",
      type: "image",
      value: form => form.settlement_info.qualifications
    }
  ],

  // 5. 结算银行账户
  bankAccountInfo: [
    {
      label: "账户类型",
      value: form => form.bank_account_info.bank_account_type === BANK_ACCOUNT_TYPE.CORPORATE ? "对公银行账户" : "经营者个人银行卡"
    },
    {
      label: "开户名称",
      value: form => form.bank_account_info.account_name
    },
    {
      label: "开户银行",
      value: form => form.bank_account_info.account_bank
    },
    {
      label: "开户银行省市编码",
      value: form => form.bank_account_info.bank_address_code,
      visible: form => form.bank_account_info.need_bank_branch
    },
    {
      label: "开户银行支行",
      value: form => form.bank_account_info.bank_name,
      visible: form => form.bank_account_info.need_bank_branch
    },
    {
      label: "银行账号",
      value: form => form.bank_account_info.account_number
    }
  ],

  // 6. 补充材料
  additionInfo: [
    {
      label: "法定代表人开户承诺函",
      type: "image",
      value: form => form.addition_info.legal_person_commitment
    },
    {
      label: "法定代表人开户意愿视频",
      type: "video",
      value: form => form.addition_info.legal_person_video
    },
    {
      label: "补充材料",
      type: "image",
      value: form => form.addition_info.business_addition_pics
    },
    {
      label: "补充说明",
      value: form => form.addition_info.business_addition_msg
    }
  ]
};

const moduleList = [
  {
    label: "主体资料",
    children: subModuleConfig.subjectInfo
  },
  {
    label: "超级管理员信息",
    children: subModuleConfig.contactInfo
  },
  {
    label: "经营资料",
    children: subModuleConfig.businessInfo
  },
  {
    label: "结算规则",
    children: subModuleConfig.settlementInfo
  },
  {
    label: "结算银行账户",
    children: subModuleConfig.bankAccountInfo
  },
  {
    label: "补充材料",
    children: subModuleConfig.additionInfo
  }
];

// 导出
export { moduleList, subModuleConfig };
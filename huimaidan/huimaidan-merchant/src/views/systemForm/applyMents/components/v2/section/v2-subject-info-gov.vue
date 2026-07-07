<template>
  <div>
    <div class="title">登记证书</div>
    <V2Upload :form.sync="form" field="subject_info.certificate_info.cert_copy" label="登记证书照片" required />

    <el-form-item label="登记证书类型：" prop="subject_info.certificate_info.cert_type" required>
      <el-select v-model="form.subject_info.certificate_info.cert_type" placeholder="请选择登记证书类型" class="selWidth">
        <el-option v-for="item in certTypeOptions" :key="item.value" :label="item.label" :value="item.value" />
      </el-select>
    </el-form-item>

    <V2SchemaForm :form.sync="form.subject_info.certificate_info" path="subject_info.certificate_info"
      :config="FORM_CONFIG" />
  </div>
</template>

<script>
import { SUBJECT_TYPE } from "../v2.enum";
import V2Upload from "../shared/v2-upload.vue";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const CERT_TYPE_LIST = [
  {
    value: "CERTIFICATE_TYPE_2388",
    label: "事业单位法人证书",
    includes: [
      SUBJECT_TYPE.INSTITUTIONS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2389",
    label: "统一社会信用代码证书",
    includes: [
      SUBJECT_TYPE.GOVERNMENT,
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2394",
    label: "社会团体法人登记证书",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2395",
    label: "民办非企业单位登记证书",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2396",
    label: "基金会法人登记证书",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2520",
    label: "执业许可证/执业证",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2521",
    label: "基层群众性自治组织特别法人统一社会信用代码证",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2522",
    label: "农村集体经济组织登记证",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2399",
    label: "宗教活动场所登记证",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  },
  {
    value: "CERTIFICATE_TYPE_2400",
    label: "政府部门下发的其他有效证明文件",
    includes: [
      SUBJECT_TYPE.OTHERS
    ]
  }
];

const FORM_CONFIG = [
  {
    type: "text",
    field: "cert_number",
    label: "证书号",
    max: 32,
    required: true,
  },
  {
    type: "text",
    field: "merchant_name",
    label: "商户名称",
    min: 2,
    max: 128,
    placeholder: "请填写登记证书上的商户名称",
    required: true,
  },
  {
    type: "text",
    field: "company_address",
    label: "注册地址",
    min: 4,
    max: 128,
    placeholder: "请填写登记证书的注册地址",
    required: true,
  },
  {
    type: "text",
    field: "legal_person",
    label: "法人姓名",
    min: 2,
    max: 100,
    placeholder: "请填写登记证书的法人姓名",
    required: true,
  },
  {
    type: "validate-period",
    begin: {
      label: "有效期开始时间",
      field: "period_begin"
    },
    longterm: {
      label: "是否长期有效",
      field: "period_longterm"
    },
    end: {
      label: "有效期结束时间",
      field: "period_end"
    },
    required: true,
  }
];

export default {
  name: "V2SubjectInfoGov",
  props: {
    form: Object
  },
  components: {
    V2Upload,
    V2SchemaForm
  },
  data() {
    return {
      FORM_CONFIG
    };
  },
  computed: {
    certTypeOptions() {
      const subjectType = this.form.subject_info.subject_type;
      return CERT_TYPE_LIST.filter(item => item.includes.includes(subjectType));
    }
  },
  watch: {
    "form.subject_info.certificate_info.period_longterm"(value) {
      const periodEnd = value ? "长期" : "";
      this.form.subject_info.certificate_info.period_end = periodEnd;
    }
  }
}
</script>

<style scoped></style>

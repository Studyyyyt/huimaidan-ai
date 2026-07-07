<template>
  <div>
    <div class="title">营业执照</div>

    <V2SchemaForm :form="form.subject_info.business_license_info" path="subject_info.business_license_info"
      :config="FORM_CONFIG" />
  </div>
</template>

<script>
import V2SchemaForm from "../shared/v2-schema-form.vue";

const FORM_CONFIG = [
  {
    type: "upload",
    accept: "image",
    field: "license_copy",
    label: "营业执照照片",
    required: true,
  },
  {
    type: "text",
    field: "license_number",
    label: "统一社会信用代码",
    max: 18,
    required: true,
  },
  {
    type: "text",
    field: "merchant_name",
    label: "商户名称",
    max: 100,
    required: true,
  },
  {
    type: "text",
    field: "legal_person",
    label: "经营者/法人姓名",
    max: 100,
    required: true,
    placeholder: "请填写营业执照的经营者/法定代表人姓名"
  },
  {
    type: "text",
    field: "license_address",
    label: "注册地址",
    max: 128,
    required: true,
    placeholder: "请填写营业执照的注册地址"
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
  name: "V2SubjectInfoPrivate",
  props: {
    form: Object
  },
  components: {
    V2SchemaForm
  },
  data() {
    return {
      FORM_CONFIG
    }
  }
}
</script>

<style scoped></style>

<template>
  <div>
    <div class="title">金融机构许可证信息</div>

    <V2SchemaForm :form.sync="form.subject_info.finance_institution_info" path="subject_info.finance_institution_info"
      :config="FORM_CONFIG" />
  </div>
</template>

<script>
import { FINANCE_TYPE_LIST } from "../v2.props";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const FORM_CONFIG = [
  {
    type: "select",
    label: "金融机构类型",
    field: "finance_type",
    required: true,
    options: FINANCE_TYPE_LIST
  },
  {
    type: "upload",
    accept: "image",
    label: "金融机构许可证图片",
    field: "finance_license_pics",
    required: true,
    max: 5
  }
];

export default {
  name: "V2SubjectFinanceInfo",
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
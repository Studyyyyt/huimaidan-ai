<template>
  <div>
    <div class="title">经营者/法定代表人身份证件</div>
    <el-form-item label="证件持有人类型：" prop="subject_info.identity_info.id_holder_type" required>
      <el-radio-group v-model="form.subject_info.identity_info.id_holder_type">
        <el-radio :label="PERSON_TYPE.LEGAL">经营者/法定代表人</el-radio>
        <el-radio :label="PERSON_TYPE.SUPER">经办人</el-radio>
      </el-radio-group>
    </el-form-item>

    <template v-if="isLegal">
      <el-form-item label="证件类型：" prop="subject_info.identity_info.id_doc_type" required>
        <el-select v-model="form.subject_info.identity_info.id_doc_type" placeholder="请选择证件类型" class="selWidth">
          <el-option v-for="item in identificationList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>

      <template v-if="idFormConfig">
        <!-- 身份证类型表单 -->
        <V2SchemaForm :form.sync="form.subject_info.identity_info.id_card_info" :config="idFormConfig"
          v-if="isIdCardType" path="subject_info.identity_info.id_card_info" />

        <!-- 非身份证类型表单 -->
        <V2SchemaForm :form.sync="form.subject_info.identity_info.id_doc_info" :config="idFormConfig"
          v-if="isOtherIdDocType" path="subject_info.identity_info.id_card_info" />
      </template>
    </template>


    <V2Upload :form.sync="form" field="subject_info.identity_info.authorize_letter_copy" label="法定代表人说明函" required v-else-if="isSuper" />
  </div>
</template>

<script>
import { PERSON_TYPE, SUBJECT_TYPE, IDENTIFICATION_TYPE } from "../v2.enum";
import { IDENTIFICATION_TYPE_LIST } from "../v2.props";
import { generateBeginDateOptions, generateEndDateOptions } from "../v2.utils";
import V2Upload from "../shared/v2-upload.vue";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const idCardConfig = [
  {
    type: "upload",
    accept: "image",
    field: "id_card_copy",
    label: "身份证人像面照片",
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "id_card_national",
    label: "身份证国徽面照片",
    required: true,
  },
  {
    type: "text",
    field: "id_card_name",
    label: "身份证姓名",
    min: 2,
    max: 100,
    required: true,
  },
  {
    type: "text",
    field: "id_card_number",
    label: "身份证号码",
    min: 18,
    max: 18,
    required: true,
  },
  {
    type: "text",
    field: "id_card_address",
    label: "身份证地址",
    min: 4,
    max: 128,
    required: true,
  },
  {
    type: "validate-period",
    begin: {
      label: "身份证有效期开始时间",
      field: "card_period_begin"
    },
    longterm: {
      label: "身份证是否长期有效",
      field: "card_period_longterm"
    },
    end: {
      label: "身份证有效期结束时间",
      field: "card_period_end"
    },
    required: true,
  }
];

const otherIdCartConfig = [
  {
    type: "upload",
    accept: "image",
    field: "id_doc_copy",
    label: "证件正面照片",
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "id_doc_copy_back",
    label: "证件背面照片",
    required: true,
  },
  {
    type: "text",
    field: "id_card_name",
    label: "证件姓名",
    required: true,
  },
  {
    type: "text",
    field: "id_card_number",
    label: "证件号码",
    required: true,
  },
  {
    type: "text",
    field: "id_card_address",
    label: "证件地址",
    required: true,
  },
  {
    type: "validate-period",
    begin: {
      label: "证件有效期开始时间",
      field: "doc_period_begin"
    },
    longterm: {
      label: "证件是否长期有效",
      field: "doc_period_longterm"
    },
    end: {
      label: "证件有效期结束时间",
      field: "doc_period_end"
    },
    required: true,
  }
];

export default {
  name: "V2SubjectIdentityInfo",
  props: {
    form: Object
  },
  data() {
    return {
      PERSON_TYPE
    }
  },
  components: {
    V2Upload,
    V2SchemaForm
  },
  computed: {
    isLegal() {
      return this.form.subject_info.identity_info.id_holder_type === PERSON_TYPE.LEGAL;
    },
    // 是否经办人
    isSuper() {
      return this.form.subject_info.identity_info.id_holder_type === PERSON_TYPE.SUPER;
    },
    identificationList() {
      const subjectType = this.form.subject_info.subject_type;
      if (subjectType === SUBJECT_TYPE.GOVERNMENT) {
        return IDENTIFICATION_TYPE_LIST.filter(item => item.value === IDENTIFICATION_TYPE.IDCARD);
      } else {
        return IDENTIFICATION_TYPE_LIST;
      }
    },
    // 证件是身份证类型
    isIdCardType() {
      if (!this.isLegal) return false;
      const idDocType = this.form.subject_info.identity_info.id_doc_type;
      if (!idDocType) return false;

      return idDocType === IDENTIFICATION_TYPE.IDCARD;
    },
    // 证件是非身份证类型
    isOtherIdDocType() {
      if (!this.isLegal) return false;
      const idDocType = this.form.subject_info.identity_info.id_doc_type;
      if (!idDocType) return false;

      return idDocType !== IDENTIFICATION_TYPE.IDCARD;
    },
    idFormConfig() {
      if (this.isIdCardType) {
        return idCardConfig;
      } else if (this.isOtherIdDocType) {
        return otherIdCartConfig;
      }

      return null;
    }
  },
  methods: {

  }
}
</script>

<style scoped></style>
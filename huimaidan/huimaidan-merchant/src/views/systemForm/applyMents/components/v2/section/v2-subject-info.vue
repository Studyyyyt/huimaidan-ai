<template>
  <el-row>
    <el-col>
      <div class="title">主体资料</div>

      <V2SchemaForm :form.sync="form.subject_info" :config="FORM_CONFIG" path="subject_info" />

      <V2Upload v-if="!isPrivateOrg" :form.sync="form" field="subject_info.certificate_letter_copy" label="单位证明函照片" />

      <!-- 金融机构许可证信息 -->
      <v2SubjectFinanceInfo :form.sync="form" v-if="isFinanceInstitution" />

      <!-- 个体户/企业营业执照信息 -->
      <V2SubjectInfoPrivate v-if="isPrivateOrg" :form.sync="form" />

      <!-- 政府机关/事业单位/其他组织信息 -->
      <V2SubjectInfoGov v-else :form.sync="form" />

      <!-- 经营者/法定代表人身份证件 -->
      <V2SubjectIdentityInfo :form.sync="form" />

      <!-- 最终受益人信息列表 -->
      <V2SubjectUBO :form.sync="form" v-if="isEnterprise" />
    </el-col>
  </el-row>
</template>

<script>
import { SUBJECT_TYPE } from "../v2.enum";
import { SUBJECT_TYPE_LIST } from "../v2.props";
import V2Upload from "../shared/v2-upload.vue";
import V2SubjectInfoPrivate from "./v2-subject-info-private.vue";
import V2SubjectInfoGov from "./v2-subject-info-gov.vue";
import v2SubjectFinanceInfo from "./v2-subject-finance-info.vue";
import V2SubjectIdentityInfo from "./v2-subject-identity-info.vue";
import V2SubjectUBO from "./v2-subject-ubo.vue";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const FORM_CONFIG = [
  {
    type: "radio",
    field: "subject_type",
    label: "主体类型",
    required: true,
    options: SUBJECT_TYPE_LIST
  },
  {
    type: "radio",
    field: "finance_institution",
    label: "是否是金融机构",
    required: true,
    options: [
      {
        label: "是",
        value: true
      },
      {
        label: "否",
        value: false
      }
    ]
  }
];

export default {
  name: "V2SubJectInfo",
  props: {
    form: Object
  },
  components: {
    V2Upload,
    V2SubjectInfoPrivate,
    V2SubjectInfoGov,
    v2SubjectFinanceInfo,
    V2SubjectIdentityInfo,
    V2SubjectUBO,
    V2SchemaForm
  },
  data() {
    return {
      FORM_CONFIG
    };
  },
  computed: {
    // 是否私营机构
    isPrivateOrg() {
      return [
        SUBJECT_TYPE.INDIVIDUAL,
        SUBJECT_TYPE.ENTERPRISE
      ].includes(this.form.subject_info.subject_type);
    },
    // 是否金融机构
    isFinanceInstitution() {
      return this.form.subject_info.finance_institution;
    },
    // 是否企业
    isEnterprise() {
      return this.form.subject_info.subject_type === SUBJECT_TYPE.ENTERPRISE;
    }
  }
}
</script>

<style scoped lang="scss"></style>

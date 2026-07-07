<template>
  <el-row>
    <el-col>
      <div class="title">超级管理员信息</div>
      <el-form-item label="超级管理员类型：" prop="contact_info.contact_type" required>
        <el-radio-group v-model="form.contact_info.contact_type">
          <el-radio :label="PERSON_TYPE.LEGAL">经营者/法定代表人</el-radio>
          <el-radio :label="PERSON_TYPE.SUPER">经办人</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item label="超级管理员姓名：" prop="contact_info.contact_name" required>
        <el-input v-model="form.contact_info.contact_name" :minlength="2" :maxlength="100" placeholder="请输入超级管理员姓名"
          class="selWidth" />
      </el-form-item>

      <!-- 经办人类型的表单信息 -->
      <template v-if="isSuper">
        <V2SchemaForm :form.sync="form.contact_info" path="contact_info" :config="SUPER_FORM_CONFIG" />
      </template>


      <el-form-item label="联系手机：" prop="contact_info.mobile_phone" required>
        <el-input v-model="form.contact_info.mobile_phone" type="tel" placeholder="请输入联系手机" class="selWidth" />
      </el-form-item>

      <el-form-item label="联系邮箱：" prop="contact_info.contact_email" required>
        <el-input v-model="form.contact_info.contact_email" type="email" placeholder="请输入联系邮箱" class="selWidth" />
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
import { PERSON_TYPE } from "../v2.enum";
import { IDENTIFICATION_TYPE_LIST } from "../v2.props";
import { generateSuperContactInfo } from "../v2.utils";
import V2Upload from "../shared/v2-upload.vue";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const SUPER_FORM_CONFIG = [
  {
    type: "select",
    label: "证件类型",
    field: "contact_id_doc_type",
    options: IDENTIFICATION_TYPE_LIST,
    required: true,
  },
  {
    type: "text",
    label: "证件号码",
    field: "contact_id_number",
    required: true,
  },
  {
    type: "upload",
    label: "证件正面照片",
    field: "contact_id_doc_copy",
    accept: "image",
    required: true,
  },
  {
    type: "upload",
    label: "证件反面照片",
    field: "contact_id_doc_copy_back",
    accept: "image",
    required: true,
  },
  {
    type: "validate-period",
    begin: {
      label: "证件有效期开始时间",
      field: "contact_period_begin"
    },
    longterm: {
      label: "证件是否长期有效",
      field: "contact_period_longterm"
    },
    end: {
      label: "证件有效期结束时间",
      field: "contact_period_end"
    },
    required: true,
  }
];

export default {
  name: "V2ContactInfo",
  components: {
    V2Upload,
    V2SchemaForm
  },
  props: {
    form: Object
  },
  data() {
    return {
      PERSON_TYPE,
      SUPER_FORM_CONFIG
    }
  },
  computed: {
    isSuper() {
      // 是否经办人
      return this.form.contact_info.contact_type === PERSON_TYPE.SUPER;
    }
  },
  watch: {
    // 超级管理员类型发生变化时，重置经办人类型的超级管理员信息
    "form.contact_info.contact_type"() {
      Object.assign(this.form.contact_info, {
        ...this.form.contact_info,
        ...generateSuperContactInfo()
      });
    }
  }
}
</script>

<style scoped lang="scss"></style>

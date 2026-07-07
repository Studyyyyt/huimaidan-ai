<template>
  <div>
    <div class="title">
      最终受益人信息列表
      <el-popover placement="top-start" trigger="hover" width="500">
        <div>
          <div v-for="tip in tipsContent" :key="tip" class="tips-content">{{ tip }}</div>
        </div>
        <i class="el-icon-info" slot="reference" />
      </el-popover>
    </div>
    <div class="ubo-item" v-for="(item, index) of form.subject_info.ubo_info_list" :key="item.field">
      <span class="ubo-item-tip">受益人{{ index + 1 }}</span>
      <V2SchemaForm :config="formConfig" :form="form.subject_info.ubo_info_list[index]"
        :path="`subject_info.ubo_info_list.${index}`" />
      <el-form-item>
        <el-button @click="handleRemoveUBO(index)" type="danger">移除</el-button>
      </el-form-item>
    </div>
    <el-form-item>
      <el-button type="text" class="add-btn" @click="handleAddUBO" v-if="form.subject_info.ubo_info_list.length < 4">
        <i class="el-icon-plus" style="margin-right: 5px;" />新增</el-button>
    </el-form-item>
  </div>
</template>

<script>
import { generateUboInfo } from "../v2.utils";
import { IDENTIFICATION_TYPE_LIST } from "../v2.props";
import V2SchemaForm from "../shared/v2-schema-form.vue";

const formConfig = [
  {
    type: "select",
    field: "ubo_id_doc_type",
    label: "证件类型",
    options: IDENTIFICATION_TYPE_LIST,
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "ubo_id_doc_copy",
    label: "证件正面照片",
    required: true,
  },
  {
    type: "upload",
    accept: "image",
    field: "ubo_id_doc_copy_back",
    label: "证件背面照片",
    required: true,
  },
  {
    type: "text",
    field: "ubo_id_doc_name",
    label: "证件姓名",
    required: true,
  },
  {
    type: "text",
    field: "ubo_id_doc_number",
    label: "证件号码",
    required: true,
  },
  {
    type: "text",
    field: "ubo_id_doc_address",
    label: "证件地址",
    required: true,
  },
  {
    type: "validate-period",
    begin: {
      label: "证件有效期开始时间",
      field: "ubo_period_begin"
    },
    longterm: {
      label: "证件是否长期有效",
      field: "ubo_period_longterm"
    },
    end: {
      label: "证件有效期结束时间",
      field: "ubo_period_end"
    },
    required: true,
  }
];

export default {
  name: "V2SubjectUBO",
  props: {
    form: Object
  },
  components: {
    V2SchemaForm
  },
  data() {
    return {
      formConfig,
      tipsContent: [
        `1）若经营者/法定代表人不是最终受益所有人，则需提填写受益所有人信息，最多上传4个。`,
        `2)若经营者/法定代表人是最终受益所有人之一，可在此填写其他受益所有人信息，最多上传3个。`,
        `3)根据国家相关法律法规，需要提供公司受益所有人信息，受益所有人需符合至少以下条件之一:`,
        `直接或者间接拥有超过25%公司股权或者表决权的自然人。`,
        `通过人事、财务等其他方式对公司进行控制的自然人。`,
        `公司的高级管理人员，包括公司的经理、副经理、财务负责人、上市公司董事会秘书和公司章程规定的其他人员。`
      ]
    }
  },
  methods: {
    handleAddUBO() {
      this.form.subject_info.ubo_info_list.push(generateUboInfo());
    },
    handleRemoveUBO(index) {
      this.form.subject_info.ubo_info_list.splice(index, 1);
    }
  }
}
</script>

<style scoped lang="scss">
.ubo-item {
  padding: 20px;
  border: 1px solid #ccc;
  margin-bottom: 20px;
  position: relative;

  .ubo-item-tip {
    position: absolute;
    font-size: 12px;
    background-color: #fff;
    top: -9px;
    left: 60px;
    padding-inline: 20px;
  }
}

.add-btn {
  margin-left: 20px;
  color: #4073fa;
}

.tips-content {
  &+.tips-content {
    margin-top: 10px;
  }
}
</style>

<!-- 区域代理编辑对话框 -->
<template>
  <el-dialog :title="agentId ? '编辑区域代理' : '新增区域代理'" :visible.sync="visible" :before-close="handleClose" width="586px"
    class="agent-edit-form">
    <form-create :key="formKey" v-model="fApi" :rule="formCreateRule" :option="option" :value.sync="value"
      v-loading="agentDetailLoading" class="form-create-body"></form-create>
    <template #footer>
      <el-button size="small" @click="handleClose">取消</el-button>
      <el-button :loading="submitLoading" size="small" type="primary" @click="handleSubmit">提交</el-button>
    </template>
  </el-dialog>
</template>

<script>
import { getSysConfig } from '@/api/system';
import { ZONE_CONFIG_KEY } from '../domain/agent.enum.js';
import formCreate from "@form-create/element-ui";
import { getUploadProps, systemFormToFormCreateRule, lazyLoadCity } from '@/utils/form-create';
import { createAgentApi, updateAgentApi, getAgentDetailApi } from '@/api/agent';
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';

// 区域代理默认表单规则
const getFormBaseRule = (isEdit = false) => [
  {
    rawType: "input",
    type: "input",
    field: "name",
    title: "代理名称",
    $required: true
  },
  {
    rawType: "input",
    type: "input",
    field: "phone",
    title: "联系电话",
    $required: true
  },
  {
    rawType: "uploadPicture",
    type: "upload",
    field: "qualification",
    title: "身份资质",
    props: getUploadProps()
  },
  {
    rawType: "user",
    type: "frame",
    field: "uid",
    title: "区域代理",
    props: {
      type: "image",
      maxLength: 1,
      title: "请选择用户",
      src: "/admin/setting/userList?field=uid&type=1",
      srcKey: "src",
      width: "1000px",
      height: "600px",
      icon: "el-icon-camera",
      allowRemove: !isEdit,
      disabled: isEdit,
      modal: {
        modal: false,
      },
      onOk() {
        this.$message.warning(`请选择用户`);
        return false;
      }
    },
    validate: [
      {
        message: "请选择用户",
        required: true,
        type: "object",
        trigger: "change"
      }
    ],
    $required: true
  },
  {
    rawType: "texts",
    type: "input",
    props: {
      type: "textarea"
    },
    field: "remark",
    title: "说明"
  }
];

// 新增代理时的默认字段
const defaultFields = new Set(getFormBaseRule().map(item => item.field));

export default {
  name: 'agentEditForm',
  props: {
    agentId: Number,
    visible: {
      type: Boolean,
      default: false
    }
  },
  components: {
    formCreate: formCreate.$form()
  },
  data() {
    return {
      fApi: {},
      //表单数据
      value: {},
      //组件参数配置
      option: {
        form: {
          labelSuffix: "：",
          size: "small",
          labelWidth: "7em"
        },
        submitBtn: {
          show: false
        }
      },
      agentFormOptions: null,

      agentDetailLoading: false,
      submitLoading: false,

      formKey: Date.now()
    }
  },
  created() {
    this.getZoneConfig();
  },
  computed: {
    //表单生成规则
    formCreateRule() {
      const formRule = getFormBaseRule(!!this.agentId);
      if (this.agentFormOptions) {
        formRule.push(...systemFormToFormCreateRule(this.agentFormOptions));
      }

      return formRule;
    }
  },
  watch: {
    visible(visible) {
      if (visible) {
        if (this.agentId) {
          this.handleSetFormDefaultData();
        } else {
          this.agentDetailLoading = false;
        }
      } else {
        this.$nextTick(() => {
          this.fApi.resetFields();
        });
      }
    }
  },
  methods: {
    async getAgentDetail() {
      try {
        const res = await getAgentDetailApi(this.agentId);
        return res.data;
      } catch (error) {
        throw error;
      }
    },
    // 如果是编辑模式，则设置表单默认数据
    async handleSetFormDefaultData() {
      this.agentDetailLoading = true;
      try {
        const agent = await this.getAgentDetail();
        const formData = agent.extend;
        defaultFields.forEach(field => {
          formData[field] = agent[field];
        });

        const nextFormData = {};

        const isValidString = value => typeof value === "string" && value.length;

        // 将表单数据转换为 form-create 所需的格式
        // 由于部分字段需要异步获取数据，因此需要使用 Promise.all 来等待所有异步任务完成
        const tasks = this.formCreateRule.map(async item => {
          let value = formData[item.field];
          if (item.rawType === "checkboxs") {
            value = isValidString(value) ? value.split(",") : [];
          } else if (item.rawType === "citys") {
            value = isValidString(value) ? await this.cityNameToId(value) : [];
          } else if (item.rawType === "dateranges") {
            value = isValidString(value) ? value.split(",") : [];
          } else if (item.rawType === "timeranges") {
            value = isValidString(value) ? value.split(" - ") : [];
          } else if (item.rawType === "user") {
            if (value) {
              value = {
                id: value,
                src: agent.user ? agent.user.avatar : undefined
              };
            } else {
              value = {};
            }
          }
          nextFormData[item.field] = value;
        });

        await Promise.all(tasks);

        this.value = JSON.parse(JSON.stringify(nextFormData));
        this.formKey = Date.now();
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.agentDetailLoading = false;
      }
    },
    // 城市名称列表转换为城市id列表
    async cityNameToId(cityName) {
      const cityNameList = cityName.split("/");
      const cityIdList = [];

      let level = 0, prevCityId = 0;
      for (const cityName of cityNameList) {
        const task = new Promise(resolve => lazyLoadCity({ value: prevCityId, level }, resolve));
        const cityList = await task;
        const city = cityList.find(item => item.label === cityName);
        cityIdList.push(city.value);
        prevCityId = city ? city.value : 0;
        level++;
      }

      return cityIdList;
    },
    // 城市id列表转换为城市名称列表
    async cityIdToName(cityIdList) {
      const cityNameList = [];

      // 有子节点的城市id列表
      // 第一级为 0，获取所有省级行政区
      const hasChildrenIdList = [
        0,
        ...cityIdList.slice(0, -1)
      ];

      // 按照行政区域 ID，获取城市名称列表
      const cityList = await Promise.all(hasChildrenIdList.map((id, level) => {
        return new Promise(resolve => lazyLoadCity({ value: id, level }, resolve));
      }));

      // 遍历城市id列表，按照行政区域 ID，获取城市名称列表
      cityIdList.forEach((id, index) => {
        const city = cityList[index].find(item => item.value === id);
        city && cityNameList.push(city.label);
      });

      return cityNameList.join("/");
    },
    async handleSubmit() {
      try {
        await this.fApi.validate();
      } catch {
        return;
      }

      if (this.submitLoading) return;
      this.submitLoading = true;

      const formData = this.fApi.formData();

      // 构建请求参数
      const payload = {};

      // 扩展字段
      const extendData = {};

      // 遍历表单数据，默认字段放入payload，扩展字段放入extendData
      // 并依次对字段进行处理，和移动端格式保持一致
      for (const item of this.formCreateRule) {
        let value = formData[item.field];
        if (item.rawType === "checkboxs" && value) {
          value = value.join(",");
        } else if (item.rawType === "citys" && value) {
          value = await this.cityIdToName(value);
        } else if (item.rawType === "dateranges" && value && value.length) {
          value = value.join(",");
        } else if (item.rawType === "timeranges" && value && value.length) {
          value = value.join(" - ");
        } else if (item.rawType === "user" && value) {
          value = value.id;
        }

        if (defaultFields.has(item.field)) {
          payload[item.field] = value;
        } else {
          extendData[item.field] = value;
        }
      }

      payload.extend = extendData;
      payload.type = ACCOUNT_TYPE.ZONE;

      try {
        const task = this.agentId ? updateAgentApi(this.agentId, payload) : createAgentApi(payload);
        const res = await task;
        this.$message.success(res.message);
        this.$emit('refresh');
        this.handleClose();
        this.$nextTick(() => {
          this.fApi.resetFields();
        });
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.submitLoading = false;
      }
    },
    handleClose() {
      this.$emit('close');
    },
    // 获取区域代理表单配置
    async getZoneConfig() {
      try {
        const res = await getSysConfig(ZONE_CONFIG_KEY);
        const formOptions = res.data.config_value.agent_application_form;
        if (formOptions && formOptions.value) {
          this.agentFormOptions = formOptions.value;
        }
      } catch (error) {
        this.$message.error(error.message);
      }
    },
  }
}
</script>

<style scoped lang="scss">
::v-deep.agent-edit-form {
  ._fc-upload {
    display: flex;
    flex-wrap: wrap;
    row-gap: 4px;
  }

  .el-dialog__body {
    max-height: 65vh;
  }
}
</style>
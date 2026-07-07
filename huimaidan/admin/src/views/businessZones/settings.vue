<template>
  <div class="divBox">
    <el-card>
      <el-tabs v-model="tabName">
        <el-tab-pane v-for="item of tabList" :key="item.value" :label="item.label" :name="item.value" />
      </el-tabs>
      <template v-if="!dataLoading">
        <zone-default-commission v-model="zoneConfig" v-if="tabName === TAB_NAME.COMMISSION" @save="handleSaveConfig" />
        <system-form-design :readonly-list="READONLY_LIST" :iconfig.sync="zoneConfig.agent_application_form" v-else-if="tabName === TAB_NAME.AGENT_APPLICATION_FORM"
          @save="handleSaveConfig" />
      </template>
    </el-card>
  </div>
</template>

<script>
import ZoneDefaultCommission from './components/zoneDefaultCommission.vue';
import SystemFormDesign from '@/components/systemFormDesign/index.vue';
import { saveSysConfig, getSysConfig } from '@/api/system';
import { ZONE_CONFIG_KEY } from './domain/agent.enum.js';

const TAB_NAME = {
  COMMISSION: "commission",
  AGENT_APPLICATION_FORM: "agent_application_form",
};

const READONLY_LIST = [
  {
    titleConfig: {
      value: "代理名称",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入代理名称",
    },
    name: "home_text"
  },
  {
    titleConfig: {
      value: "联系电话",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入联系电话",
    },
    name: "home_text"
  },
  {
    titleConfig: {
      value: "申请资质",
    },
    tipConfig: {
      value: "请上传申请资质",
    },
    numConfig: {
      val: 3,
    },
    name: "home_upload_picture"
  }
];

export default {
  name: 'businessZonesSettings',
  components: {
    ZoneDefaultCommission,
    SystemFormDesign
  },
  data() {
    return {
      TAB_NAME,
      tabList: [
        { label: "默认提成", value: TAB_NAME.COMMISSION },
        { label: "代理申请表单", value: TAB_NAME.AGENT_APPLICATION_FORM },
      ],
      tabName: TAB_NAME.COMMISSION,
      READONLY_LIST,

      zoneConfig: {
        one_agent_commission: 0,
        two_agent_commission: 0,
        three_agent_commission: 0,
        agent_application_form: {}
      },

      dataLoading: true,
      saveLoading: false
    }
  },
  created() {
    this.getZoneConfig();
  },
  methods: {
    isValidCommissionValue(value) {
      return value >= 0 && value <= 100;
    },
    validateCommissionValue() {
      const {
        one_agent_commission,
        two_agent_commission,
        three_agent_commission
      } = this.zoneConfig;
      let hasInvalidValue = [
        one_agent_commission,
        two_agent_commission,
        three_agent_commission
      ].some(value => !this.isValidCommissionValue(value));

      if (hasInvalidValue) {
        throw new Error("提成比例必须为0 ~ 100之间的数值");
      }

      if (one_agent_commission < two_agent_commission) {
        throw new Error("一级代理提成比例必须大于二级代理提成比例");
      }
      if (two_agent_commission < three_agent_commission) {
        throw new Error("二级代理提成比例必须大于三级代理提成比例");
      }
    },
    async getZoneConfig() {
      try {
        const res = await getSysConfig(ZONE_CONFIG_KEY);
        const config = res.data.config_value;
        const zoneConfig = Object.keys(config)
          .reduce((acc, key) => {
            if (config[key] !== null && config[key] !== undefined) {
              acc[key] = config[key];
            }

            return acc;
          }, {});

        if (!zoneConfig.agent_application_form || !zoneConfig.agent_application_form.value) {
          delete zoneConfig.agent_application_form;
        }

        Object.assign(this.zoneConfig, zoneConfig);
      } catch (error) {
        this.$message.error(error.message);
      }
      this.dataLoading = false;
    },
    async handleSaveConfig() {
      if (this.dataLoading || this.saveLoading) return;
      this.saveLoading = true;
      try {
        this.validateCommissionValue();
        const res = await saveSysConfig(ZONE_CONFIG_KEY, this.zoneConfig);
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.saveLoading = false;
      }
    }
  }
}
</script>

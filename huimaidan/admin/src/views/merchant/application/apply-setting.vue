<template>
  <div class="divBox">
    <el-card v-loading="dataLoading">
      <el-tabs v-model="activeName">
        <el-tab-pane label="入驻页面配置" :name="TAB_TYPE.PAGE"></el-tab-pane>
        <el-tab-pane label="入驻表单设置" :name="TAB_TYPE.FORM"></el-tab-pane>
      </el-tabs>
      <template v-if="!dataLoading">
        <system-form-design :readonly-list="READONLY_LIST" :iconfig.sync="formConfig.business_application_form"
          @save="handleSaveConfig" v-if="activeName === TAB_TYPE.FORM" />
        <template v-else>
          <el-form inline size="small">
            <el-form-item label="背景图：">
              <upload-pictures-dialog title="上传背景图" @select="handleSelectBackgroundImage">
                <template #default="{ open }">
                  <div>
                    <div v-if="formConfig.business_background_image" class="upload-box">
                      <el-image class="upload-avatar-img" :src="formConfig.business_background_image" />
                      <div class="delete-btn" @click="handleDeleteBackgroundImage">
                        <i class="el-icon-delete"></i>
                      </div>
                    </div>
                    <i v-else class="el-icon-plus upload-avatar-btn" @click="open"></i>
                  </div>
                </template>
              </upload-pictures-dialog>
            </el-form-item>
          </el-form>
          <div class="footer">
            <el-button type="primary" @click="handleSaveConfig">保存</el-button>
          </div>
        </template>
      </template>
    </el-card>
  </div>
</template>

<script>
import SystemFormDesign from '@/components/systemFormDesign/index.vue';
import { saveSysConfig, getSysConfig } from '@/api/system';
import { ZONE_CONFIG_KEY } from '@/views/businessZones/domain/agent.enum.js';
import UploadPicturesDialog from '@/components/uploadPictures/dialog.vue';

const TAB_TYPE = {
  PAGE: 'business_application_page',
  FORM: 'business_application_form'
};

const READONLY_LIST = [
  {
    titleConfig: {
      value: "商户名称",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入商户名称",
    },
    name: "home_text"
  },
  {
    titleConfig: {
      value: "联系人姓名",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入联系人姓名",
    },
    name: "home_text"
  },
  {
    titleConfig: {
      value: "联系人电话",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入联系人电话",
    },
    name: "home_text"
  },
  {
    titleConfig: {
      value: "店铺分类"
    },
    tipConfig: {
      value: "请选择店铺分类",
    },
    name: "home_select"
  },
  {
    titleConfig: {
      value: "店铺类型"
    },
    tipConfig: {
      value: "请选择店铺类型",
    },
    name: "home_select"
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
  },
  {
    titleConfig: {
      value: "说明",
    },
    defaultValConfig: {
      value: "",
    },
    tipConfig: {
      value: "请输入说明",
    },
    name: "home_text"
  },
];

export default {
  name: 'MerchantApplySetting',
  components: {
    SystemFormDesign,
    UploadPicturesDialog
  },
  data() {
    return {
      dataLoading: true,
      formConfig: {
        business_background_image: null,
        business_application_form: {}
      },
      TAB_TYPE,
      activeName: TAB_TYPE.PAGE,
      READONLY_LIST,
    };
  },
  created() {
    this.getFormConfig();
  },
  methods: {
    handleDeleteBackgroundImage() {
      this.formConfig.business_background_image = "";
    },
    async getFormConfig() {
      try {
        const res = await getSysConfig(ZONE_CONFIG_KEY);
        const config = res.data.config_value;
        const formConfig = Object.keys(config)
          .reduce((acc, key) => {
            if (config[key] && config[key] !== null && config[key] !== undefined) {
              acc[key] = config[key];
            }

            return acc;
          }, {});

        if (!formConfig.business_application_form || !formConfig.business_application_form.value) {
          delete formConfig.business_application_form;
        }

        Object.assign(this.formConfig, formConfig);
      } catch (error) {
        this.$message.error(error.message);
      }
      this.dataLoading = false;
    },
    async handleSaveConfig() {
      if (this.dataLoading || this.saveLoading) return;
      this.saveLoading = true;
      try {
        const res = await saveSysConfig(ZONE_CONFIG_KEY, this.formConfig);
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.saveLoading = false;
      }
    },
    handleSelectBackgroundImage(result) {
      this.formConfig.business_background_image = result[0];
    }
  }
}
</script>

<style scoped lang="scss">
.footer {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background: #fff;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, .1);
  display: flex;
  justify-content: center;
  align-items: center;
}

.upload-avatar-img,
.upload-avatar-btn {
  width: 52px;
  height: 52px;
  border-radius: 4px;
  cursor: pointer;
}

.upload-avatar-btn {
  border: 1px dashed #dcdfe6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-box {
  position: relative;
  width: fit-content;

  &:hover .delete-btn{
    opacity: 1;
  }

  .delete-btn {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    border-radius: 4px;
    height: 52px;
    opacity: 0;
    transition: opacity 0.3s;
    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, .3);

    .el-icon-delete {
      color: #fff;
    }
  }
}

/* .delete-btn {
  position: absolute;
  top: 0;
  right: 0;
  width: 16px;
  height: 16px;
  background: #fff;
  border-radius: 50%;
  cursor: pointer;
} */
</style>

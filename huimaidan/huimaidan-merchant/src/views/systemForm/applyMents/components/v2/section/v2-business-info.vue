<template>
  <el-row>
    <el-col>
      <div class="title">经营资料</div>

      <!-- 商户简称、客服电话 -->
      <V2SchemaForm :form.sync="form.business_info" :config="COMMON_FORM_CONFIG" path="business_info" />

      <!-- 经营场景 -->
      <V2SchemaForm :form.sync="form.business_info.sales_info" :config="SCENE_FORM_CONFIG"
        path="business_info.sales_info" />

      <!-- 线下场所场景 -->
      <template v-if="isStoreScene">
        <div class="title">线下场所场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.biz_store_info" :config="STORE_SCENE_FORM_CONFIG"
          path="business_info.sales_info.biz_store_info" />
      </template>

      <!-- 服务号与公众号场景 -->
      <template v-if="isMpScene">
        <div class="title">服务号与公众号场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.mp_info" :config="MP_SCENE_FORM_CONFIG"
          path="business_info.sales_info.mp_info" />
      </template>

      <!-- 小程序场景 -->
      <template v-if="isMiniProgramScene">
        <div class="title">小程序场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.mini_program_info"
          :config="MINI_PROGRAM_SCENE_FORM_CONFIG" path="business_info.sales_info.mini_program_info" />
      </template>

      <!-- 互联网网站场景 -->
      <template v-if="isWebScene">
        <div class="title">互联网网站场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.web_info" :config="WEB_SCENE_FORM_CONFIG"
          path="business_info.sales_info.web_info" />
      </template>

      <!-- App场景 -->
      <template v-if="isAppScene">
        <div class="title">App场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.app_info" :config="APP_SCENE_FORM_CONFIG"
          path="business_info.sales_info.app_info" />
      </template>

      <!-- 企业微信场景 -->
      <!-- <template v-if="isWeworkScene">
        <div class="title">企业微信场景资料</div>
        <V2SchemaForm :form.sync="form.business_info.sales_info.wework_info" :config="WEWORK_SCENE_FORM_CONFIG"
          path="business_info.sales_info.wework_info" />
      </template> -->
    </el-col>
  </el-row>
</template>

<script>
import { SALES_SCENE_TYPE } from "../v2.enum";
import V2SchemaForm from "../shared/v2-schema-form.vue";
import {
  COMMON_FORM_CONFIG,
  SCENE_FORM_CONFIG,
  STORE_SCENE_FORM_CONFIG,
  MP_SCENE_FORM_CONFIG,
  MINI_PROGRAM_SCENE_FORM_CONFIG,
  WEB_SCENE_FORM_CONFIG,
  APP_SCENE_FORM_CONFIG,
  WEWORK_SCENE_FORM_CONFIG
} from "./v2-business-info.prop";


export default {
  name: "V2BusinessInfo",
  components: {
    V2SchemaForm
  },
  props: {
    form: Object
  },
  data() {
    return {
      COMMON_FORM_CONFIG,
      SCENE_FORM_CONFIG,
      STORE_SCENE_FORM_CONFIG,
      MP_SCENE_FORM_CONFIG,
      MINI_PROGRAM_SCENE_FORM_CONFIG,
      WEB_SCENE_FORM_CONFIG,
      APP_SCENE_FORM_CONFIG,
      WEWORK_SCENE_FORM_CONFIG
    };
  },
  computed: {
    // 选中的经营场景
    selectedScenes() {
      return this.form.business_info.sales_info.sales_scenes_type;
    },
    // 是否选中线下场所场景
    isStoreScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_STORE);
    },
    // 是否选中服务号与公众号场景
    isMpScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_MP);
    },
    // 是否选中小程序场景
    isMiniProgramScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_MINI_PROGRAM);
    },
    // 是否选中互联网网站场景
    isWebScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_WEB);
    },
    // 是否选中App场景
    isAppScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_APP);
    },
    // 是否选中企业微信场景
    isWeworkScene() {
      return this.selectedScenes.includes(SALES_SCENE_TYPE.SALES_SCENES_WEWORK);
    },
  }
}
</script>

<style scoped lang="scss"></style>

<template>
  <!-- 结算规则 -->
  <el-row>
    <el-col>
      <div class="title">结算规则</div>
      <V2SchemaForm :form.sync="form.settlement_info" :config="SETTLEMENT_INFO_FORM_CONFIG" path="settlement_info" />

    </el-col>
  </el-row>
</template>

<script>
import { SETTLEMENT_INFO_FORM_CONFIG } from "./v2-settlement-info.prop";
import V2SchemaForm from "../shared/v2-schema-form.vue";

export default {
  name: "V2SettlementInfo",
  components: {
    V2SchemaForm
  },
  props: {
    form: Object
  },
  data() {
    return {
      SETTLEMENT_INFO_FORM_CONFIG
    };
  }
}
</script>

<style scoped lang="scss"></style>

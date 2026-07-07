<template>
  <el-row>
    <el-col>
      <div class="title">结算银行账户</div>
      <el-form-item label="账户类型：" prop="bank_account_info.bank_account_type" required>
        <el-radio-group v-model="form.bank_account_info.bank_account_type">
          <el-radio :label="BANK_ACCOUNT_TYPE.CORPORATE">对公银行账户</el-radio>
          <el-radio :label="BANK_ACCOUNT_TYPE.PERSONAL">经营者个人银行卡</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item label="开户名称：" prop="bank_account_info.account_name" required>
        <el-input class="selWidth" v-model="form.bank_account_info.account_name" placeholder="请输入开户名称" />
      </el-form-item>

      <V2BankSection :form.sync="form" />

      <el-form-item label="银行账号：" prop="bank_account_info.account_number" required>
        <el-input class="selWidth" v-model="form.bank_account_info.account_number" placeholder="请输入银行账号" />
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
import { BANK_ACCOUNT_TYPE } from "../v2.enum";
import V2SchemaForm from "../shared/v2-schema-form.vue";
import V2BankSection from "./v2-bank-section.vue";

export default {
  name: "V2BankAccountInfo",
  components: {
    V2SchemaForm,
    V2BankSection
  },
  props: {
    form: Object
  },
  data() {
    return {
      BANK_ACCOUNT_TYPE
    };
  }
}
</script>

<style scoped lang="scss"></style>

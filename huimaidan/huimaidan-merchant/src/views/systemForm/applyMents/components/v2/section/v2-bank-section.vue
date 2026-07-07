<template>
  <div>
    <el-form-item label="开户银行：" prop="bank_account_info.account_bank" required>
      <V2BankPicker :bankAlias="form.bank_account_info.bank_alias"
        :accountType="form.bank_account_info.bank_account_type" @change="handleBankChange">
        <span v-if="bankNameInfo">{{ bankNameInfo }}</span>
      </V2BankPicker>
    </el-form-item>

    <template v-if="form.bank_account_info.need_bank_branch">
      <el-form-item label="开户银行省市编码：" prop="bank_account_info.bank_address_code_full" required>
        <el-cascader :props="cascaderProps" v-model="form.bank_account_info.bank_address_code_full"></el-cascader>
      </el-form-item>


      <el-form-item label="开户银行支行：" prop="bank_account_info.bank_name" v-if="isBranchesPickerVisible" required>
        <V2BankBrancesPicker :bankAliasCode="form.bank_account_info.bank_alias_code" :cityCode="cityCode"
          :bankName="form.bank_account_info.bank_name" @change="handleBankBranchesChange">
          <span v-if="form.bank_account_info.bank_name">{{ form.bank_account_info.bank_name }}</span>
        </V2BankBrancesPicker>
      </el-form-item>
    </template>
  </div>
</template>

<script>
import V2BankPicker from './v2-bank-picker.vue';
import V2BankBrancesPicker from './v2-bank-brances-picker.vue';
import { applymentBankCityCode } from '@/api/system';

export default {
  name: "V2BankSection",
  props: {
    form: Object
  },
  components: {
    V2BankPicker,
    V2BankBrancesPicker
  },
  data() {
    return {
      cascaderProps: {
        lazy: true,
        lazyLoad: this.handleLazyLoadCity
      }
    };
  },
  watch: {
    'form.bank_account_info.bank_address_code_full'(v) {
      if (v && v.length) {
        this.form.bank_account_info.bank_address_code = v[v.length - 1];
      }
    },
    'form.bank_account_info.bank_account_type'() {
      Object.assign(this.form.bank_account_info, {
        account_name: "", // 开户名称
        account_bank: "", // 开户银行
        bank_address_code: null, // 开户银行省市编码
        bank_branch_id: "", // 开户银行银行号
        bank_name: "", // 开户银行全称（含支行）
        account_number: "", // 银行帐号


        // 内部暂存数据，无关微信接口提交
        bank_address_code_full: null, // 开户银行省市完整数组编码，用于回显
        need_bank_branch: false, // 是否需要选择分行
        bank_alias: "", // 银行别名
        bank_alias_code: "", //  银行别名编码
      });
    },
    'form.bank_account_info.bank_alias'() {
      Object.assign(this.form.bank_account_info, {
        bank_address_code_full: null, // 开户银行省市完整数组编码，用于回显
        bank_address_code: null, // 开户银行省市编码
        bank_branch_id: "", // 开户银行支行号
        bank_name: "", // 开户银行全称（含支行）
      });
    },
    'form.bank_account_info.bank_address_code'() {
      Object.assign(this.form.bank_account_info, {
        bank_branch_id: "", // 开户银行银行号
        bank_name: "", // 开户银行全称（含支行）
      });
    },
  },
  computed: {
    cityCode() {
      return this.form.bank_account_info.bank_address_code;
    },
    bankNameInfo() {
      const accountBank = this.form.bank_account_info.account_bank;
      const bankAlias = this.form.bank_account_info.bank_alias;
      if (bankAlias) return bankAlias;
      return null;
    },
    isBranchesPickerVisible() {
      const { bank_address_code, bank_alias_code } = this.form.bank_account_info;
      return bank_address_code && bank_alias_code;
    }
  },
  methods: {
    async handleLazyLoadCity(node, resolve) {
      const params = {};

      const isLeaf = node.level !== 0;

      if (isLeaf) {
        params.province_code = node.value;
      }

      try {
        const res = await applymentBankCityCode(params);
        const combineData = res.data.map(item => {
          return {
            value: item.province_code || item.city_code,
            label: item.province_name || item.city_name,
            leaf: isLeaf
          }
        });
        resolve(combineData);
      } catch (error) {
        resolve([]);
        this.$message.error(error.message);
      }
    },
    // 选择支行
    handleBankBranchesChange(branchBank) {
      const {
        bank_branch_id,
        bank_branch_name
      } = branchBank;

      Object.assign(this.form.bank_account_info, {
        bank_branch_id,
        bank_name: bank_branch_name
      });
    },
    // 选择银行
    handleBankChange(bank) {
      const {
        account_bank,
        bank_alias,
        bank_alias_code,
        need_bank_branch
      } = bank;
      this.form.bank_account_info.account_bank = bank.account_bank;
      this.form.bank_account_info.bank_alias = bank.bank_alias;
      this.form.bank_account_info.bank_alias_code = bank.bank_alias_code;
      Object.assign(this.form.bank_account_info, {
        account_bank,
        bank_alias,
        bank_alias_code,
        need_bank_branch
      });
    }
  }
}
</script>

<style scoped lang="scss"></style>

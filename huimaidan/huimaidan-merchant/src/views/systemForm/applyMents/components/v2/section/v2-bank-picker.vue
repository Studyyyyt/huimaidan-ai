<template>
  <div>
    <div>
      <slot />
      <el-button @click="handleOpenPicker">请选择</el-button>
    </div>
    <el-dialog title="提示" :visible.sync="dialogVisible" width="500px">
      <el-table :data="bankList" v-loading="loading">
        <el-table-column label="选择">
          <template #default="{ row }">
            <el-radio :value="bankAlias" :label="row.bank_alias" @input="handleSelectBank(row)">{{ "" }}</el-radio>
          </template>
        </el-table-column>
        <el-table-column prop="bank_alias" label="银行别名" />
        <el-table-column prop="bank_alias_code" label="银行别名编码" />
      </el-table>
      <el-pagination layout="prev, pager, next" :total="total" :page-size="limit" :current-page="page"
        @current-change="handlePageChange">
      </el-pagination>
    </el-dialog>
  </div>
</template>

<script>
import { applymentBanksApi } from '@/api/system';
import { BANK_ACCOUNT_TYPE } from "../v2.enum";

export default {
  name: "V2BankPicker",
  props: {
    bankAlias: String,
    accountType: String
  },
  data() {
    return {
      dialogVisible: false,
      bankList: [],
      total: 0,
      page: 1,
      limit: 10,
      loading: false
    }
  },
  watch: {
    accountType: {
      handler(v) {
        this.page = 1;
        this.loading = false;
        this.getBankList();
      },
      immediate: true
    }
  },
  methods: {
    handleOpenPicker() {
      this.dialogVisible = true;
    },
    handleSelectBank(bank) {
      this.dialogVisible = false;
      this.$emit("change", bank);
    },
    handlePageChange(page) {
      if (this.loading) return;
      this.page = page;
      this.getBankList();
    },
    async getBankList() {
      if (this.loading) return;
      this.loading = true;

      const isPerson = this.accountType === BANK_ACCOUNT_TYPE.PERSONAL;
      try {
        const offset = (this.page - 1) * this.limit;
        const res = await applymentBanksApi({
          type: isPerson ? 0 : 1,
          offset,
          limit: this.limit
        });
        this.bankList = res.data.data;
        this.total = res.data.total_count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss"></style>

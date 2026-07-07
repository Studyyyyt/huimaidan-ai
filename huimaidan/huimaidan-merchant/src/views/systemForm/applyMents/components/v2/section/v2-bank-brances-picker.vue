<template>
  <div>
    <div>
      <slot />
      <el-button @click="handleOpenPicker">请选择</el-button>
    </div>

    <el-dialog title="提示" :visible.sync="dialogVisible" width="600px">
      <el-table :data="branchBankList" v-loading="loading">
        <el-table-column label="选择" width="50px">
          <template #default="{ row }">
            <el-radio :value="bankName" :label="row.bank_branch_name" @input="handleSelectBank(row)">{{ "" }}</el-radio>
          </template>
        </el-table-column>
        <el-table-column prop="bank_branch_name" label="支行名称" />
        <el-table-column prop="bank_branch_id" label="支行编码" width="130px" />
      </el-table>
      <el-pagination layout="prev, pager, next" :total="total" :page-size="limit" :current-page="page"
        @current-change="handlePageChange">
      </el-pagination>
    </el-dialog>
  </div>
</template>

<script>
import { applymentBankBranchesApi } from '@/api/system';

export default {
  name: "V2BankBrancesPicker",
  props: {
    bankName: String, // 银行分行名称
    bankAliasCode: String, // 银行别名编码
    cityCode: Number, // 城市代码
  },
  data() {
    return {
      dialogVisible: false,

      branchBankList: [],
      page: 1,
      limit: 10,
      total: 0,
      loading: false,
    }
  },
  computed: {
    baseParams() {
      return {
        city_code: this.cityCode,
        bank_alias_code: this.bankAliasCode
      };
    }
  },
  watch: {
    baseParams() {
      this.loading = false;
      this.page = 1;
      this.handleGetBranchesList();
    }
  },
  created() {
    this.handleGetBranchesList();
  },
  methods: {
    handleOpenPicker() {
      this.dialogVisible = true;
    },
    handlePageChange(page) {
      if (this.loading) return;
      this.page = page;
      this.handleGetBranchesList();
    },
    handleSelectBank(bank) {
      this.dialogVisible = false;
      this.$emit("change", bank);
    },
    async handleGetBranchesList() {
      if (this.loading) return;
      this.loading = true;

      const offset = (this.page - 1) * this.limit;

      const params = {
        offset,
        limit: this.limit,
        ...this.baseParams
      };
      try {
        const res = await applymentBankBranchesApi(params);
        this.branchBankList = res.data.data;
        this.total = res.data.total_count;
      } catch (error) {
        this.$message.error(`开户银行支行 - ` + error.message);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss"></style>

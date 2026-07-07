<template>
  <div>
    <el-table ref="mainTable" v-loading="listLoading" :data="tableData.data" row-key="spu_id" size="small"
      max-height="500" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" />
      <el-table-column label="店铺名称" prop="mer_name" />
      <el-table-column label="店铺logo" prop="mer_logo">
        <template slot-scope="scope">
          <img :src="scope.row.mer_avatar" alt="" style="width: 50px; height: 50px;">
        </template>
      </el-table-column>
      <!-- <el-table-column label="店铺类型" prop="mer_type" />
      <el-table-column label="店铺状态" prop="mer_status" /> -->
    </el-table>
    <div class="acea-row row-between row-bottom" style="margin-top: 20px;">
      <el-pagination :page-size="tableFrom.limit" :current-page="tableFrom.page" layout="prev, pager, next, jumper"
        :total="tableData.total" @size-change="handleSizeChange" @current-change="pageChange" />
      <div>
        <el-button size="small" @click="$emit('cancel')">取消</el-button>
        <el-button size="small" type="primary" @click="submitStore">确定</el-button>
      </div>
    </div>
  </div>
</template>

<script>
import { merchantListApi } from '@/api/merchant';

export default {
  name: 'shopList',
  data() {
    return {
      listLoading: false,
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        status: 1,
        page: 1,
        limit: 10,
      },
    }
  },
  created() {
    this.getList();
  },
  methods: {
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList();
    },
    pageChange(val) {
      this.tableFrom.page = val;
      this.getList();
    },
    submitStore() {
      this.$emit('change-store', this.multipleSelection);
    },
    handleSelectionChange(val) {
      this.multipleSelection = val;
    },
    getList() {
      this.listLoading = true;
      merchantListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list;
        this.tableData.total = res.data.count;
      }).finally(() => {
        this.listLoading = false;
      });
    }
  } 
}
</script>

<style scoped lang="scss"></style>

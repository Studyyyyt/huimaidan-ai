<template>
  <el-dialog title="选择模板" :visible.sync="dialogVisible" width="600px" :close-on-click-modal="false">
    <el-form :model="searchForm" inline @submit.native.prevent="getDiyTemplateList">
      <el-form-item label="模板名称:">
        <el-input v-model="searchForm.name" placeholder="请输入模板名称" clearable size="small" @clear="getDiyTemplateList" @keyup.enter.native="getDiyTemplateList" />
      </el-form-item>
      <el-form-item>
        <el-button :loading="tableData.loading" type="primary" size="small" @click="getDiyTemplateList">搜索</el-button>
      </el-form-item>
    </el-form>
    <el-table :data="tableData.data" size="small" max-height="500" :loading="tableData.loading">
      <el-table-column prop="mer_id" label="模板ID">
        <template #default="{ row }">
          <el-radio v-model="currentSelectedId" :label="row.id" />
        </template>
      </el-table-column>
      <el-table-column prop="name" label="模板名称" />
      <el-table-column prop="add_time" label="创建时间" />
    </el-table>
    <div class="block">
      <el-pagination :page-size="searchForm.limit" :current-page="searchForm.page" layout="prev, pager, next, jumper"
        :total="tableData.total" @size-change="handleSizeChange" @current-change="handleCurrentChange" />
    </div>
    <template #footer>
      <div>
        <el-button size="small" @click="handleCancel">取消</el-button>
        <el-button size="small" type="primary" @click="handleConfirm">确定</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script>
import { diyList } from '@/api/diy';

export default {
  name: 'diyTemplateSelect',
  data() {
    return {
      dialogVisible: false, // 对话框是否显示
      searchForm: {
        name: '', // 模板名称
        page: 1, // 页码
        limit: 8, // 每页条数
      },
      tableData: {
        data: [], // diy模板列表
        total: 0, // 总条数
        loading: false,
      },
      currentSelectedId: 0
    }
  },
  created() {
    this.getDiyTemplateList();
  },
  methods: {
    handleSizeChange(size) {
      this.searchForm.limit = size;
      this.getDiyTemplateList();
    },
    handleCurrentChange(page) {
      this.searchForm.page = page;
      this.getDiyTemplateList();
    },
    handleCancel() {
      this.dialogVisible = false;
    },
    handleConfirm() {
      if (!this.currentSelectedId) {
        this.$message.error('请选择模板');
        return;
      }
      this.$emit('confirm', this.currentSelectedId);
      this.handleCancel();
    },
    async getDiyTemplateList() {
      if (this.tableData.loading) return;
      this.tableData.loading = true;
      try {
        const res = await diyList(this.searchForm);
        this.tableData.data = res.data.list;
        this.tableData.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.tableData.loading = false;
      }
    },
    open(diyTemplateId) {
      this.dialogVisible = true;
      this.currentSelectedId = diyTemplateId;
    }
  }
}
</script>

<style scoped lang="scss"></style>

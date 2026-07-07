<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="auto" @submit.native.prevent>
        <el-form-item label="单位名称：" prop="value">
          <el-input v-model="tableFrom.value" @keyup.enter.native="getList(1)" placeholder="请输入单位名称" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14 dataBox">
      <el-button size="small" type="primary" class="mb20" @click="add">添加商品单位</el-button>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column
          prop="product_unit_id"
          label="ID"
          min-width="100"
        />
        <el-table-column
          prop="value"
          label="单位名称"
          min-width="180"
        />
        <el-table-column
          prop="sort"
          label="排序"
          min-width="120"
        />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row.product_unit_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.product_unit_id, scope.$index)">删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { unitCreatApi, unitListApi, unitEdittApi, unitDeleteApi } from '@/api/product'
export default {
  name: 'ProductUnit',
  data() {
    return {
      formDynamic: {
        template_name: '',
        template_value: []
      },
      tableFrom: {
        page: 1,
        limit: 20
      },
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true
    }
  },
  mounted() {
    this.getList()
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 回
    add() {
      this.$modalForm(unitCreatApi()).then(() => this.getList(''))
    },
    // 列表
    getList() {
      this.listLoading = true
      unitListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList()
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList()
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('删除该单位').then(() => {
        unitDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.tableData.data.splice(idx, 1)
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    onEdit(id) {
      this.$modalForm(unitEdittApi(id)).then(() => this.getList(''))
    }
  }
}
</script>
<style scoped lang="scss">
@import '@/styles/form.scss';
</style>
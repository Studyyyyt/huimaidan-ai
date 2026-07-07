<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="95px" @submit.native.prevent>
        <el-form-item label="打印机名称：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            placeholder="请输入打印机名称"
            class="selWidth"
            size="small"
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="打印机类型：" prop="type">
          <el-select v-model="tableFrom.type" placeholder="请选择" class="selWidth" clearable @change="getList(1)">
            <el-option label="易联云" value="0" />
            <el-option label="飞鹅云" value="1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="add">添加打印机</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="printer_id" label="ID" min-width="150" />
        <el-table-column prop="printer_name" label="打印机名称" min-width="150" />
        <el-table-column prop="printer_terminal" label="应用账号" min-width="150" />
        <el-table-column prop="times" label="打印联数" min-width="150" />
        <el-table-column label="是否开启" min-width="90">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              :width="60"
              active-text="开启"
              inactive-text="关闭"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>

        <el-table-column label="创建时间" min-width="150" prop="create_time" />
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="scope">
            <router-link :to="{ path:`${roterPre}` + '/setting/printer/content?id='+scope.row.printer_id }" class="mr10">
              <el-button size="small" type="text">设置</el-button>
            </router-link>
            <el-button type="text" size="small" @click="handleEdit(scope.row.printer_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.printer_id, scope.$index)">删除</el-button>
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
import { printerLstApi, printerAddApi, printerStatusApi, printerUpdateApi, printerDeleteApi  } from '@/api/setting'
import { roterPre } from '@/settings'
export default {
  name: 'PrinterList',
  components: {},
  data() {
    return {
      roterPre: roterPre,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        page: 1,
        limit: 20,
        date: '',
        keyword: ''
      },
      timeVal: [],
      props: {},
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 添加
    add() {
      this.$modalForm(printerAddApi()).then(() => this.getList(''))
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      printerLstApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch((res) => {
          this.listLoading = false
          this.$message.error(res.message)
        })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 是否开启
    onchangeIsShow(row) {
      printerStatusApi(row.printer_id, { status: row.status })
        .then(({ message }) => {
          this.$message.success(message)
          this.getList('')
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    },
    // 编辑
    handleEdit(id) {
      this.$modalForm(printerUpdateApi(id)).then(() => this.getList(''))
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('删除打印机').then(() => {
        printerDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message)
            if (this.tableData.data.length === 1 && this.tableFrom.page > 1)
            this.tableFrom.page = this.tableFrom.page - 1;
            this.getList('');
          })
          .catch(({ message }) => {
            this.$message.error(message)
          })
      })
    }
  }
}
</script>

<style scoped lang="scss">
@import '@/styles/form.scss';
</style>

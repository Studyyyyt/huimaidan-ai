<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="form" inline size="small">
        <el-form-item label="员工姓名：" prop="name">
          <el-input
            v-model="tableFrom.name"
            class="selWidth"
            placeholder="请输入员工姓名"
            @keyup.enter.native="getList(1)">
          </el-input>
        </el-form-item>
        <el-form-item label="人员状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            clearable
            placeholder="请选择人员状态"
            @change="getList(1)"
            class="selWidth">
            <el-option label="是" value="1"></el-option>
            <el-option label="否" value="2"></el-option>
          </el-select>
        </el-form-item>
        <select-search
          ref="selectSearch"
          :select="select"
          :searchSelectList="searchSelectList"
          @search="searchList"
        />
        <el-form-item>


          <el-button type="primary" @click="getSearchList">搜索</el-button>

          <el-button @click="resetForm('form')">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="add">添加配送员</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="ID" prop="service_id" min-width="50" />
        <el-table-column label="头像" min-width="50">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image style="width: 36px; height: 36px" :src="scope.row.avatar ? scope.row.avatar : moren" :preview-src-list="[scope.row.avatar || moren]" />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="名称" min-width="60" />
        <el-table-column prop="nickname" label="昵称" min-width="60" />
        <el-table-column prop="phone" label="手机号码" min-width="100" />
        <el-table-column label="是否显示" min-width="100">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="是"
              inactive-text="否"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="60" />
        <el-table-column prop="create_time" label="添加时间" min-width="100" />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row.service_id)">编辑</el-button>
            <el-button type="text" size="small" @click="onDelete(scope.row.service_id,scope.$index)">删除</el-button>
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
import {
  addDeliveryPerson,
  deliveryPersonLst,
  editDeliveryPerson,
  deleteDeliveryPerson,
  deliveryPersonStatusApi } from '@/api/systemForm';
import { handleDeleteTable } from '@/libs/public';
import selectSearch from "@/components/base/selectSearch";
export default {
  components: {
    selectSearch
  },
  data() {
    return {
      moren: require('@/assets/images/f.png'),
      dialogVisible: false,
      storeDetail: {},
      tableData: {
        data: [],
        total: 0
      },
      mapKey: '',
      keyUrl: '',
      listLoading: true,
      loading: true,
      select: "nickname",
      searchSelectList: [
        { label: "昵称", value: "nickname" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ],
      searchForm: {
        name: '',
        status: '',
        info: '',
        infoSelect: 0,
      },
      userInfoSelect: '',
      tableFrom: {
        page: 1,
        limit: 20,
        name: '',
        status: '',
        info: '',
        infoSelect: 0,

      },
      applyStatus: [
        { value: 1, label: '是' },
        { value: 0, label: '否' }
      ],
      deliveryPoint: [
        { value: 1, label: '达达' },
        { value: 2, label: 'UU' }
      ],

      delivery_type: 1,
      select: 'nickname',
      searchSelectList: [
        { label: '昵称', value: 'nickname' },
        { label: '用户ID', value: 'uid' },
        { label: '手机号', value: 'phone' }
      ]
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    // 添加配送员
    add() {
      this.$modalForm(addDeliveryPerson()).then(() => this.getList())
    },
    // 编辑门店
    onEdit(id) {
      this.$modalForm(editDeliveryPerson(id)).then(() => this.getList())
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      deliveryPersonLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.listLoading = false
        })
    },
    // 搜索
    getSearchList() {
      this.$refs.selectSearch.changeSearch()
    },
    searchList(data) {
      this.tableFrom = { ...this.tableFrom, ...data }
      this.getList(1)
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 删除
    onDelete(id, idx) {
      this.$modalSureDelete('删除该配送员吗').then(
        () => {
          deleteDeliveryPerson(id)
            .then(({ message }) => {
              this.$message.success(message)
              handleDeleteTable(this.tableData.data.length, this.tableFrom)
              this.getList('')
            })
            .catch(({ message }) => {
              this.$message.error(message)
            })
        }
      )
    },
    // 是否显示
    onchangeIsShow(row) {
      deliveryPersonStatusApi(row.service_id, { status: row.status })
        .then(({ message }) => {
          this.$message.success(message)
          this.getList()
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    },
    // 重置表单数据
    resetForm() {
      this.tableFrom = {
        page: 1,
        limit: 20,
        name: '',
        status: '',
        info: '',
        infoSelect: 0,
      };
      // this.$refs.tableFrom.resetFields()
      this.$refs.selectSearch.resetParmas()
      this.getList(1)
    }

  }
}
</script>

<style lang="scss" scoped>
@import '@/styles/form.scss';
.description{
  &-term {
    display: table-cell;
    padding-bottom: 10px;
    line-height: 20px;
    width: 100%;
    font-size: 12px;
  }
}
.info-select {
  ::v-deep.el-input__inner {
    background: red;
  }
}

</style>

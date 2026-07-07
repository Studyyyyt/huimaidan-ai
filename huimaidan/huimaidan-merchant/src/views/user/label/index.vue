<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="85px">
        <el-form-item label="标签类型：" prop="type">
          <el-select
            v-model="tableFrom.type"
            placeholder="请选择"
            class="filter-item selWidth mr20"
            size="small"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in labelTypeList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="标签名称：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" clearable placeholder="请输入标签名称" class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="onAdd">设置自动标签</el-button>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column
          label="ID"
          prop="label_rule_id"
          min-width="60"
        />
        <el-table-column
          label="标签名称"
          prop="label.label_name"
          min-width="120"
        />
        <el-table-column
          label="标签类型"
          min-width="90"
        >
          <template  slot-scope="scope">
            <span>{{ scope.row.type === 0 ? '订单数' : '订单金额' }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="标签规则"
          min-width="180"
        >
          <template  slot-scope="scope">
            <span v-if="scope.row.min === '0.00' && scope.row.max === '0.00'">从未支付的客户</span>
            <span v-else>
              <span v-if="scope.row.type === 1">订单金额在{{ scope.row.min }} - {{ scope.row.max }}的客户</span>
              <span v-else>支付订单数在{{ Math.trunc(scope.row.min) }} - {{ Math.trunc(scope.row.max) }}的客户</span>
            </span>
          </template>
        </el-table-column>
        <el-table-column
          label="客户数"
          prop="user_num"
          min-width="60"
        />
        <el-table-column
          label="更新时间"
          min-width="180"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.update_time ? scope.row.update_time : '未更新' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onUpdate(scope.row.label_rule_id)">更新</el-button>
            <el-button type="text" size="small" @click="onEdit(scope.row)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row.label_rule_id, scope.$index)">删除</el-button>
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
    <!--添加/编辑-->
    <el-dialog :title="title" :visible.sync="dialogVisible" width="660px">
      <el-form ref="formValidate" v-loading="fullscreenLoading" class="formValidate"  :model="formValidate" label-width="110px" @submit.native.prevent>
        <el-form-item label="标签类型：">
          <el-radio-group v-model="formValidate.type" @change="initCount">
            <el-radio :label="0">订单次数标签</el-radio>
            <el-radio :label="1">订单金额标签</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="标签名称：">
          <el-input v-model="formValidate.label_name" size="small" placeholder="请输入标签名称"/>
        </el-form-item>
        <el-form-item v-if="formValidate.type === 1" label="消费金额设置：">
          <el-input type="number" v-model="formValidate.min" :min="1" size="small" class="number_input" />元
          <span>-</span>
          <el-input type="number" v-model="formValidate.max" :min="formValidate.min" size="small" class="number_input" style="margin-left: 10px;"/>元
        </el-form-item>
        <el-form-item v-else label="消费次数设置：">
          <el-input type="number" v-model.number="formValidate.min" :value="Math.trunc(formValidate.min)" :min="1" size="small" class="number_input" @keyup.native="number"/>次
          <span>-</span>
          <el-input type="number" v-model.number="formValidate.max" :min="formValidate.min" size="small" class="number_input" style="margin-left: 10px;" @keyup.native="number"/>次
        </el-form-item>
        <el-form-item style="margin-top:30px;text-align:right;">
          <el-button size="small" @click="dialogVisible=false">取消</el-button>
          <el-button type="primary" size="small" @click="handleSubmit('formValidate')">提交</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
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
  AutolabelLstApi,
  AutolabelCreateApi,
  AutolabelUpdateApi,
  AutolabelSyncApi,
  AutolabelDeleteApi
} from '@/api/user'
export default {
  name: 'UserGroup',
  data() {
    return {
      tableFrom: {
        page: 1,
        limit: 20,
        type: '',
        keyword: ''
      },
      labelTypeList: [
        { label: "订单次数", value: 0 },
        { label: "订单金额", value: 1 }
      ],
      tableData: {
        data: [],
        total: 0
      },
      formValidate: {
        type: 0,
        label_name: '',
        min: '',
        max: ''
      },

      listLoading: true,
      dialogVisible: false,
      fullscreenLoading: false,
      title: '添加标签',
      label_id: ''

    }
  },
  mounted() {
    this.getList('')
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    number(){
      this.formValidate.min = (this.formValidate.min.toString()).replace(/[^\.\d]/g,'');
      this.formValidate.min = (this.formValidate.min.toString()).replace('.','');
      this.formValidate.max = (this.formValidate.max.toString()).replace(/[^\.\d]/g,'');
      this.formValidate.max = (this.formValidate.max.toString()).replace('.','');
    },
    initCount(){
      this.formValidate.min = this.formValidate.max = '';
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      AutolabelLstApi(this.tableFrom).then(res => {
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
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 添加
    onAdd() {
      this.dialogVisible = true;
      this.title = "添加标签"
      this.label_id = '';
      this.formValidate = {
        type: 0,
        label_name: '',
        min: '',
        max: ''
      }
    },
    // 编辑
    onEdit(row) {
      this.dialogVisible = true;
      this.title = "编辑标签"
      this.label_id = row.label_rule_id;
      this.formValidate = {
        type: row.type,
        label_name: row.label && row.label.label_name ? row.label.label_name : '',
        min: row.type === 1 ? row.min : Math.trunc(row.min),
        max: row.type === 1 ? row.max : Math.trunc(row.max)
      }
    },
    // 更新
    onUpdate(id){
      AutolabelSyncApi(id).then(async res => {
        this.$message.success(res.message)
        this.getList('')
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('删除该标签').then(() => {
        AutolabelDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.tableData.data.splice(idx, 1)
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    // 提交
    handleSubmit(name) {
      this.label_id ?
        AutolabelUpdateApi(this.label_id,this.formValidate).then(async res => {
          this.fullscreenLoading = false
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getList('')
        }).catch(res => {
          this.fullscreenLoading = false
          this.$message.error(res.message)
        })
        :
        AutolabelCreateApi(this.formValidate).then(async res => {
          this.fullscreenLoading = false
          this.$message.success(res.message)
          this.dialogVisible = false
          this.getList('')
        }).catch(res => {
          this.fullscreenLoading = false
          this.$message.error(res.message)
        })
    },
  }
}
</script>

<style scoped lang="scss">
  ::v-deep .number_input{
    width: 100px;
    margin-right: 5px;
    input{
      padding-right: 0;
      height: 30px;
      line-height: 30px;
    }
  }
</style>

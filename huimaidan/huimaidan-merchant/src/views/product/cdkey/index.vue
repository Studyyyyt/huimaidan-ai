<template>
  <div class="divBox relative">
    <!--搜索条件-->
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" label-width="90px" inline size="small">
        <el-form-item label="卡密库名称：" prop="name">
          <el-input
            v-model.trim="tableFrom.name"
            placeholder="请输入卡密库名称"
            class="selWidth"
            size="small"
            clearable
            @keyup.enter.native="getList(1)"
          ></el-input>
        </el-form-item>
        <el-form-item label="商品名称：" prop="productName">
          <el-input
            v-model.trim="tableFrom.productName"
            placeholder="请输入商品名称"
            class="selWidth"
            size="small"
            clearable
            @keyup.enter.native="getList(1)"
          ></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <!--列表-->
    <el-card class="box-card mt14" :body-style="{ padding: '20px' }" shadow="never" :bordered="false">
      <el-button
        size="small"
        type="primary"
        class="mb20"
        @click="handleAdd()"
        >添加卡密库</el-button
      >
      <el-table v-loading="listLoading" :data="tableData.data" style="width: 100%" size="small" highlight-current-row>
        <el-table-column prop="id" label="ID" min-width="50" />
        <el-table-column label="卡密库名称" prop="name" min-width="150" :show-overflow-tooltip="true">
        </el-table-column>
        <el-table-column label="关联商品" min-width="200">
          <template slot-scope="scope">
            <div v-if="scope.row.product" class="tabBox acea-row row-middle">
              <div class="demo-image__preview">
                <el-image :src="scope.row.product.image" :preview-src-list="[scope.row.product.image]" />
              </div>
              <span class="tabBox_tit">{{ scope.row.product.store_name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="关联商品规格" prop="productAttrValueName" min-width="150">
          <template slot-scope="scope">
            <span>{{ (scope.row.attrValue && scope.row.attrValue.sku) || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="未使用/卡密总数" min-width="180" :show-overflow-tooltip="true">
          <template slot-scope="scope">
            <span>{{ Number(scope.row.total_num) - Number(scope.row.used_num) + ' / ' + scope.row.total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="备注" prop="remark" min-width="120" :show-overflow-tooltip="true">
          <template slot-scope="scope">
            <span>{{ scope.row.remark | filterEmpty }}</span>
          </template></el-table-column
        >
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column label="操作" width="180" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" class="mr10" @click="handleAdd(scope.row)">编辑</el-button>
            <router-link :to="{ path: `${roterPre}/product/cdkey/creatCdkey?id=${scope.row.id}&name=${scope.row.name}` }" class="mr10">
              <el-button type="text" size="small">管理卡密</el-button>
            </router-link>
            <template
              v-if="!scope.row.productAttrValueName && !scope.row.productName"
            >
              <el-button type="text" size="small" @click="handleDelete(scope.row)">删除</el-button>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, sizes, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        /></div
    ></el-card>
    <el-dialog
      :visible.sync="dialogVisible"
      :title="formValidate.id === 0 ? '新增卡密库' : '编辑卡密库'"
      destroy-on-close
      :close-on-click-modal="false"
      width="540px"
    >
      <el-form
        ref="formValidate"
        class="formValidate"
        :rules="ruleValidate"
        :model="formValidate"
        label-width="90px"
        size="small"
        @submit.native.prevent
      >
        <el-form-item label="卡密库名称:" prop="name">
          <el-input
            v-model.trim="formValidate.name"
            placeholder="请输入卡密库名称，最多32字"
            maxlength="32"
            clearable
          />
        </el-form-item>
        <el-form-item label="备注:">
          <el-input
            v-model.trim="formValidate.remark"
            placeholder="请输入备注，最多可输入200字"
            maxlength="200"
            type="textarea"
            clearable
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="dialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          size="small"
          class="submission"
          @click="handleSubmit('formValidate')"
          :loading="loadingBtn"
          >确定</el-button
        >
      </span>
    </el-dialog>
  </div>
</template>
<script>
import {
  productCdkeyDeleteApi,
  productCdkeyListApi,
  productCdkeysaveApi,
  productUnrelatedUpdateApi,
} from '@/api/product';
import { roterPre } from '@/settings'
import { Debounce } from '@/utils/validate';
const tableFroms = {
  page: 1,
  limit: 20,
  name: '',
  productName: '',
};
export default {
  name: 'cdkey',
  data() {
    return {

      roterPre: roterPre,
      productName: '',
      name: '',
      listLoading: false,
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: Object.assign({}, tableFroms),
      dialogVisible: false,
      formValidate: {
        name: '',
        remark: '',
        id: 0,
      },
      loadingBtn: false,
      ruleValidate: {
        name: [{ required: true, message: '请填写卡密库名称', trigger: 'blur' }],
      },
    };
  },
  mounted() {
   this.getList();
  },
  methods: {
    //重置
    searchReset() {
      this.$refs.searchForm.resetFields()
      this.getList(1);
    },
    // 添加
    handleAdd(row) {
      if (row) {
        Object.assign(this.formValidate, row);
      } else {
        Object.assign(this.formValidate, {
          name: '',
          remark: '',
          id: 0,
        });
      }
      this.dialogVisible = true;
    },
    //提交
    handleSubmit: Debounce(function (name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.loadingBtn = true;
          this.formValidate.id === 0
          ? productCdkeysaveApi(this.formValidate)
            .then((res) => {
              this.$message.success('新增成功');
              this.dialogVisible = false;
              this.getList(1);
              this.loadingBtn = false;
            })
            .catch((res) => {
              this.loadingBtn = false;
            })
          : productUnrelatedUpdateApi(this.formValidate.id,this.formValidate)
            .then((res) => {
              this.$message.success('编辑成功');
              this.loadingBtn = false;
              this.dialogVisible = false;
              this.getList(1);
            })
            .catch((res) => {
              this.loadingBtn = false;
            });
        }
      });
    }),
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      productCdkeyListApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch((res) => {
          this.listLoading = false;
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList();
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList();
    },
    // 删除
    handleDelete(row) {
      if(row.attrValue){
        this.$modalSureDelete('此卡密库存在关联商品，确定删除吗').then(() => {
          productCdkeyDeleteApi(row.id).then(() => {
            this.$message.success('删除成功');
            this.handleDeleteTable(this.tableData.data.length, this.tableFrom);
            this.getList('');
          });
        });
      }else{
         this.$modalSure('删除此卡密库吗').then(() => {
          productCdkeyDeleteApi(row.id).then(() => {
            this.$message.success('删除成功');
            this.handleDeleteTable(this.tableData.data.length, this.tableFrom);
            this.getList('');
          });
        });
      }

    },
  },
};
</script>
<style scoped lang="scss">
.tabBox_tit {
  width: 65%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}</style>

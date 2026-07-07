<template>
  <div class="divBox">
     <div class="selCard">
       <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="60px" @submit.native.prevent>
        <el-form-item label="搜索：" prop="template_name">
          <el-input v-model="tableFrom.template_name" @keyup.enter.native="getList(1)" size="small" clearable placeholder="请输入参数模板名称" class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14 dataBox">
      <el-button size="small" class="mb20" type="primary" @click="onAdd">添加参数模板</el-button>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column prop="template_id" label="ID" min-width="80" />
        <el-table-column prop="template_name" label="参数模板名称" min-width="150" />
        <el-table-column prop="sort" label="排序" min-width="100" />
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row.template_id)">编辑</el-button>
            <el-button type="text" size="small" @click="onDetail(scope.row.template_id)">查看</el-button>
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.template_id, scope.$index)"
            >删除</el-button>
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
    <!--详情-->
    <el-dialog
      :title="title"
      :visible.sync="dialogVisible"
      width="420px"
    >
      <div style="min-height: 500px;">
          <div class="description">
            <div class="acea-row">
              <div v-for="(item, index) in specsInfo.parameter" :key="index" class="description-term">
                <span class="name">{{item.name}}</span>
                <div class="value">
                  <span v-for="itm in item.values" :key="itm.parameter_value_id">{{itm.value}}</span>
                </div>
              </div>
          </div>
        </div>
      </div>
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
import { roterPre } from '@/settings'
import {
  productSpecsList, specsDeteleApi, categoryListApi, specsDetailApi
} from "@/api/product";
export default {
  name: "SpecsList",
  data() {
    return {
      listLoading: true,
      cateList: [],
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        page: 1,
        limit: 20,
      },
      specsInfo: {},
      dialogVisible: false,
      title: ""
    };
  },
  mounted() {
    this.getCategorySelect();
    this.getList('');
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 回
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      productSpecsList(this.tableFrom)
        .then((res) => {
          res.data.list.forEach((item,index)=>{
            item.cate_name= [];
            item.cateId.forEach((val,idx)=>{
              item.cate_name.push(val.category.cate_name)
            })
          })
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch((res) => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList('');
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList('');
    },
    // 平台分类；
    getCategorySelect() {
      categoryListApi().then(res => {
        this.cateList = res.data
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 添加
    onAdd() {
       this.$router.push(`${roterPre}/product/specs/create`);
    },
    // 编辑
    onEdit(id) {
      this.$router.push(`${roterPre}/product/specs/create/${id}`);
    },
    // 查看
    onDetail(id) {
      specsDetailApi(id)
        .then((res) => {
         this.specsInfo = res.data;
         this.title = res.data.template_name;
         this.dialogVisible = true;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure('确定删除该模板').then(() => {
        specsDeteleApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList('');
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
  },
};
</script>

<style scoped lang="scss">
.description{
  padding-bottom: 15px;
  &-term {
    display: flex;
    align-items: center;
    width: 100%;
    line-height: 40px;
    font-size: 12px;
    margin-top: 10px;
    color: #333;
    border-bottom: 1px solid #DCDFE6;
  }
  .name{
    display: inline-block;
    width: 120px;
  }
  .value{
    span {
      display: inline-block;
      margin-right: 6px;
    }
  }
}
</style>

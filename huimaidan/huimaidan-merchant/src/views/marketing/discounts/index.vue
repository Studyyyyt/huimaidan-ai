<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
        <el-form-item label="套餐类型：" prop="type">
          <el-select
            v-model="tableFrom.type"
            placeholder="请选择套餐类型"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option value="0" label="固定套餐">固定套餐</el-option>
            <el-option value="1" label="搭配套餐">搭配套餐</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="套餐状态：" prop="status">
          <el-select
            placeholder="请选择"
            v-model="tableFrom.status"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option value="" label="全部">全部</el-option>
            <el-option value="1" label="上架">上架</el-option>
            <el-option value="0" label="下架">下架</el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="套餐搜索：" prop="title">
          <el-input
            class="selWidth"
            clearable
            placeholder="请输入套餐名称"
            v-model="tableFrom.title"
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button type="primary" size="small" @click="add" class="mb20" >添加套餐</el-button>
      <el-table v-loading="loading" :data="tableData.data" size="small">
       <el-table-column label="ID" prop="discount_id" min-width="80"/>
       <el-table-column label="套餐名称" prop="title" min-width="120">
       </el-table-column>
       <el-table-column label="套餐类型" min-width="120">
          <template slot-scope="scope">
          {{ scope.row.type == 0 ? "固定套餐" : "搭配套餐" }}
          </template>
       </el-table-column>
       <el-table-column label="上架状态" min-width="120">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              :width="55"
              active-text="上架"
              inactive-text="下架"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
       </el-table-column>
        <el-table-column label="限时" min-width="120">
          <template slot-scope="scope">
            <div v-if="scope.row.is_time == 0">不限时</div>
            <div v-else>
              <div>起：{{ scope.row.start_time || "--" }}</div>
              <div>止：{{ scope.row.stop_time || "--" }}</div>
            </div>
          </template>
        </el-table-column>
         <el-table-column label="创建时间" prop="create_time" min-width="120" />
         <el-table-column label="剩余数量" min-width="120">
          <template slot-scope="scope">
            {{scope.row.is_limit?scope.row.limit_num:'不限量'}}
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="120">
          <template slot-scope="scope">
            <router-link
              :to="{path: roterPre + '/marketing/discounts/create/' + scope.row.discount_id}"
            >
              <el-button type="text" size="small" class="mr10">编辑</el-button>
            </router-link>
            <el-button type="text" size="small" @click="handleDelete(scope.row.discount_id, scope.$index)">删除</el-button>
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
import { discountsList, discountsChangeStatus, discountsDelete } from "@/api/marketing";
import { formatDate } from "@/utils/validate";
import { roterPre } from '@/settings'
// 封装接口请求错误处理
const handleApiError = (res, vm) => {
  vm.loading = false;
  vm.$message.error(res.message);
};
export default {
  name: "Discounts",
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd hh:mm");
      }
    },
  },
  data() {
    return {
      loading: false,
      roterPre: roterPre,
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        status: "",
        title: "",
        page: 1,
        type: "",
        limit: 15,
      },
    };
  },
  computed: {
  },
  created() {
    this.getList('');
  },
  methods: {
    /**
     * 重置搜索表单
     */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    /**
     * 添加套餐
     */
    add() {
      this.$router.push({ path: `${roterPre}/marketing/discounts/create` });
    },
    /**
     * 一键复制
     */
    copy(row) {
      this.$router.push({
        name: "create",
        query: {
          id: row.id,
          copy: 1,
        },
      });
    },
    /**
     * 删除套餐
     * @param {number} id - 套餐 ID
     * @param {number} idx - 套餐索引
     */
    handleDelete(id, idx) {
      this.$modalSure('删除该套餐').then(() => {
        discountsDelete(id)
          .then(({
            message
          }) => {
            this.$message.success(message)
            this.getList('')
          })
          .catch((res) => {
            handleApiError(res, this);
          });
      })
    },
    /**
     * 获取套餐列表
     * @param {number|string} num - 页码
     */
    getList(num) {
      this.loading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      discountsList(this.tableFrom)
        .then(async (res) => {
           this.tableData.data = res.data.list;
           this.tableData.total = res.data.count;
           this.loading = false;
        })
        .catch((res) => {
          handleApiError(res, this);
        });
    },
    /**
     * 页码改变事件
     * @param {number} page - 当前页码
     */
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList('');
    },
    /**
     * 每页数量改变事件
     * @param {number} val - 每页数量
     */
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList('');
    },
    /**
     * 修改套餐上架状态
     * @param {object} row - 套餐数据
     */
    onchangeIsShow(row) {
      discountsChangeStatus(row.discount_id,row.is_show)
        .then(async (res) => {
          this.$message.success(res.message);
          this.getList('');
        })
        .catch((res) => {
          this.$message.error(res.message);
          this.getList('');
        });
    },
  },
};
</script>

<style scoped lang="scss">


</style>

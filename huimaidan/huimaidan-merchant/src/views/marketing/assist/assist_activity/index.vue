<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" inline>
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="商品搜索：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getList(1)" placeholder="请输入商品名称/ID" clearable class="selWidth" />
        </el-form-item>
        <el-form-item label="发起人：" prop="user_name">
          <el-input v-model="tableFrom.user_name" @keyup.enter.native="getList(1)" placeholder="请输入发起人昵称" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="product_assist_set_id" label="ID" min-width="50" />
        <el-table-column label="助力商品图片" min-width="90">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image v-if="scope.row.product" :src="scope.row.product.image" :preview-src-list="[scope.row.product.image]" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="商品名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.assist&&scope.row.assist.store_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="助力价格" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.assist && scope.row.assist.assistSku[0].assist_price || '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="assist_count" label="助力人数" min-width="90" />
        <el-table-column label="发起人" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.user && scope.row.user.nickname || '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="发起时间" min-width="120" />
        <el-table-column prop="yet_assist_count" label="已参与人数" min-width="90" />
        <el-table-column label="活动时间" min-width="180">
          <template slot-scope="scope">
            <div>开始日期：{{ scope.row.assist && scope.row.assist.start_time ? scope.row.assist.start_time.slice(0,10) : "" }}</div>
            <div>结束日期：{{ scope.row.assist && scope.row.assist.end_time ? scope.row.assist.end_time.slice(0,10) : "" }}</div>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" class="mr10" @click="goDetail(scope.row.product_assist_set_id)">查看详情</el-button>
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
    <!--查看详情-->
    <details-data ref="detailsData" :is-show="isShowDetail"/>

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
  assistListApi,
} from "@/api/product";
import { roterPre } from "@/settings";
import detailsData from './detail'
import timeOptions from '@/utils/timeOptions';
export default {
  name: "ProductList",
  components: { detailsData },
  data() {
    return {
      pickerOptions: timeOptions,
      props: {
        emitPath: false,
      },
      roterPre: roterPre,
      listLoading: true,
      tableData: {
        data: [],
        total: 0,
      },
      assistStatusList: [
        { label: "未开始", value: 0 },
        { label: "正在进行", value: 1 },
        { label: "已结束", value: 2 },
      ],
      tableFrom: {
        page: 1,
        limit: 20,
        keyword: "",
        date: '',
        type: '',
        user_name: ''
      },
      modals: false,
      dialogVisible: false,
      loading: false,
      manyTabTit: {},
      manyTabDate: {},
      attrInfo: {},
      timeVal: '',
      isShowDetail: false
    };
  },
  mounted() {
    this.getList('');
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 查看详情
    goDetail(id){
      this.$refs.detailsData.dialogVisible = true
      this.isShowDetail = true
      this.$refs.detailsData.getList(id)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList('');
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      assistListApi(this.tableFrom)
        .then((res) => {
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
    }
  }
};
</script>

<style scoped lang="scss">
.el-table .cell{
  white-space: pre-line;
}
.demo-table-expand {
  font-size: 0;
}
.scollhide::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}

</style>

<template>
  <div class="divBox">
    <div class="selCard mb14">
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
            style="width: 280px"
            :picker-options="pickerOptions"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="拼团状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in activityStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="团长搜索：" prop="user_name">
          <el-input
            v-model="tableFrom.user_name"
            @keyup.enter.native="getList(1)"
            placeholder="请输入开团团长昵称/ID"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="商品搜索：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入商品名称/ID"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" />
    <el-card class="box-card">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column prop="group_buying_id" label="ID" min-width="50" />
        <el-table-column label="开团团长" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.initiator && scope.row.initiator.nickname }}</span>
          </template>
        </el-table-column>
        <el-table-column label="拼团商品图片" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                :src="scope.row.productGroup.product.image"
                :preview-src-list="[scope.row.productGroup.product.image]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="拼团商品" min-width="200">
          <template slot-scope="scope">
            <span>{{ scope.row.productGroup.product.store_name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="拼团时间" min-width="160">
          <template slot-scope="scope">
            <div>发起时间：{{ scope.row.create_time }}</div>
            <div>结束时间：{{ scope.row.stop_time }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="buying_count_num" label="几人团" min-width="90" />
        <el-table-column prop="yet_buying_num" label="参与人次" min-width="90" />
        <el-table-column label="状态" min-width="90">
          <template slot-scope="scope">
            <span>{{
              scope.row.status === -1
                ? "未完成"
                : scope.row.status === 0
                ? "进行中"
                : "已完成"
            }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              class="mr10"
              @click="goDetail(scope.row.group_buying_id)"
              >查看详情</el-button
            >
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
    <details-data ref="detailsData" :is-show="isShowDetail" />
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
import { combinationActivityLst } from "@/api/marketing";
import { roterPre } from "@/settings";
import detailsData from "./detail";
import cardsData from "@/components/cards/index";
import timeOptions from '@/utils/timeOptions';
export default {
  name: "ProductList",
  components: { detailsData, cardsData },
  data() {
    return {
      props: {
        emitPath: false,
      },
      pickerOptions: timeOptions,
      roterPre: roterPre,
      listLoading: true,
      cardLists: [],
      tableData: {
        data: [],
        total: 0,
      },
      activityStatusList: [
        { label: "未完成", value: -1 },
        { label: "进行中", value: 0 },
        { label: "已完成", value: 10 }
      ],
      tableFrom: {
        page: 1,
        limit: 20,
        keyword: "",
        date: "",
        status: "",
        user_name: "",
      },
      modals: false,
      dialogVisible: false,
      loading: false,
      manyTabTit: {},
      manyTabDate: {},
      attrInfo: {},
      timeVal: "",
      isShowDetail: false,
    };
  },
  mounted() {
    this.getList("");
  },
  methods: {
    // 重置搜索表单
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 查看详情
    goDetail(id) {
      this.$refs.detailsData.dialogVisible = true;
      this.isShowDetail = true;
      this.$refs.detailsData.getList(id);
    },
    // 处理时间选择改变
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 获取列表数据
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      combinationActivityLst(this.tableFrom)
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
    // 处理分页页码改变
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    // 处理每页数量改变
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList("");
    },
  },
};
</script>

<style scoped lang="scss">
.el-table .cell {
  white-space: pre-line;
}
.add {
  font-style: normal;
  position: relative;
  top: -1.2px;
}
.title {
  margin-bottom: 16px;
  color: #17233d;
  font-size: 14px;
  font-weight: bold;
}
.scollhide::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}
.box-container {
  overflow: hidden;
}
.box-container .list {
  float: left;
  line-height: 40px;
}
.box-container .sp {
  width: 50%;
}
.box-container .sp3 {
  width: 33.3333%;
}
.box-container .sp100 {
  width: 100%;
}
.box-container .list .name {
  display: inline-block;
  color: #606266;
}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
.box-container .list.image {
  margin: 20px 0;
  position: relative;
}
.box-container .list.image img {
  position: absolute;
  top: -20px;
}
.labeltop {
  height: 280px;
  overflow-y: auto;
}
</style>

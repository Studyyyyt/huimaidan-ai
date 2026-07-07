<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        size="small"
        label-width="85px"
        :inline="true"
      >
        <el-form-item label="审核状态：" class="width100" prop="status">
          <el-radio-group
            v-model="tableFrom.status"
            size="small"
            @change="statusChange(tableFrom.status)"
          >
            <el-radio-button
              v-for="(itemn, indexn) in statusList"
              :key="indexn"
              :label="itemn.val"
              >{{ itemn.text }}</el-radio-button
            >
          </el-radio-group>
        </el-form-item>
        <el-form-item label="选择时间：">
          <el-date-picker
            v-model="timeVal"
            type="daterange"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            format="yyyy/MM/dd"
            value-format="yyyy/MM/dd"
            :picker-options="pickerOptions"
            @change="onchangeTime"
            style="width:280px;"
          />
        </el-form-item>
        <el-form-item label="店铺名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option
              v-for="item in merSelect"
              :key="item.mer_id"
              :label="item.mer_name"
              :value="item.mer_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column prop="mer_applyments_id" label="ID" min-width="60" />
        <el-table-column
          prop="applyment_id"
          label="微信支付申请单号"
          min-width="150"
        />
        <el-table-column
          prop="out_request_no"
          label="业务申请编号"
          min-width="260"
        />
        <el-table-column
          prop="merchant.mer_name"
          label="店铺名"
          min-width="100"
        />
        <el-table-column prop="sub_mchid" label="分账店铺ID" min-width="90" />
        <el-table-column prop="message" label="审核结果" min-width="100" />
        <el-table-column prop="create_time" label="申请时间" min-width="150" />
        <el-table-column label="状态" min-width="120">
          <template slot-scope="scope">
            <div v-if="scope.row.status == 0">待审核</div>
            <div v-if="scope.row.status == -1">平台驳回</div>
            <div v-if="scope.row.status == 10">平台提交审核中</div>
            <div v-if="scope.row.status == 1">店铺验证</div>
            <div v-if="scope.row.status == 20">已完成</div>
            <div v-if="scope.row.status == 30">已冻结</div>
            <div v-if="scope.row.status == 40">微信驳回</div>
          </template>
        </el-table-column>
        <el-table-column prop="mark" label="备注" min-width="150" />
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              @click="handleMark(scope.row.mer_applyments_id)"
              >备注</el-button
            >
            <el-button
              v-if="scope.row.status == 0"
              type="text"
              size="small"
              @click="handleDetail(scope.row.mer_id)"
              >审核</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="handleDetail(scope.row.mer_id)"
              >详情</el-button
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
    <!-- 详情 -->
    <detail-drawer ref="detailDrawer" @refresh="getList('')" />

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
  getApplymentLst,
  splitAccountMark
} from "@/api/merchant";
import { merSelectApi } from "@/api/product";
import { fromList } from "@/libs/constants.js";
import { roterPre } from "@/settings";
import timeOptions from "@/utils/timeOptions";
import DetailDrawer from "./components/detail-drawer.vue";

export default {
  name: "MerchantApplyMents",
  components: {
    DetailDrawer
  },
  data() {
    return {
      props: {
        emitPath: false
      },
      pickerOptions: timeOptions,
      fromList: fromList,
      merSelect: [],
      statusList: [
        { text: "全部", val: "" },
        { text: "待审核", val: "0" },
        { text: "平台驳回", val: "-1" },
        { text: "审核中", val: "10" },
        { text: "店铺验证", val: "11" },
        { text: "已完成", val: "20" },
        { text: "已冻结", val: "30" },
        { text: "微信驳回", val: "40" }
      ], //筛选状态列表
      roterPre: roterPre,
      listLoading: true,
      loading: true,
      storeType: [],
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
        status: "",
        mer_id: ""
      },
      timeVal: [],
      visible: false,
      formValidate: {},
    };
  },
  watch: {},
  mounted() {
    this.getMerSelect();
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    // 店铺列表；
    getMerSelect() {
      merSelectApi()
        .then(res => {
          this.merSelect = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    statusChange(tab) {
      this.tableFrom.status = tab;
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      getApplymentLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    },

    // 备注
    handleMark(id) {
      this.$modalForm(splitAccountMark(id)).then(() => this.getList(""));
    },
    // 详情
    handleDetail(id) {
      this.$refs.detailDrawer.open(id);
    }
  }
};
</script>

<style lang="scss" scoped>
.pictures {
  width: 100%;
  max-width: 100%;
}
::v-deep table .el-image {
  display: inline-block !important;
}

</style>

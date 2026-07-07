<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
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
        <select-search
          ref="selectSearch"
          :select="select"
          :searchSelectList="searchSelectList"
          @search="searchList" />
        <el-form-item label="订单搜索：" prop="order_sn">
          <el-input
            v-model="tableFrom.order_sn"
            placeholder="请输入订单号"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="支付方式：" prop="pay_type">
          <el-select
            v-model="tableFrom.pay_type"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
        >
            <el-option label="余额" value="0" />
            <el-option label="微信" value="1" />
            <el-option label="支付宝" value="2" />
            <el-option label="线下支付" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" />
    <el-card>
      <el-button size="small" type="primary" class="mb20" @click="exports">列表导出</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="订单号" min-width="200">
          <template slot-scope="scope">
            <span v-if="scope.row.financial_type != 'mer_accoubts'">{{ scope.row.order_sn }}</span>
            <span v-else>{{ scope.row.financial_record_sn }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="financial_record_sn" label="交易流水号" min-width="200" />
        <el-table-column label="第三方交易单号" min-width="200">
          <template slot-scope="scope">
            <span>{{ (scope.row.orderInfo&&scope.row.orderInfo.transaction_id) || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="交易时间" min-width="200" sortable />
        <el-table-column prop="user_info" label="对方信息" min-width="150" />
        <el-table-column label="交易类型" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.financial_type | transactionTypeFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column label="支付方式" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.pay_type | orderPayType }}</span>
          </template>
        </el-table-column>
        <el-table-column label="收支金额（元）" min-width="150">
          <template slot-scope="scope">
            <span>{{ scope.row.financial_pm === 1 ? scope.row.number : -scope.row.number }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <router-link v-if="scope.row.financial_type == 'mer_accoubts'" :to=" { path:`${roterPre}` + '/accounts/reconciliation?reconciliation_id='+scope.row.order_id } ">
              <el-button type="text" size="small" class="mr10">详情</el-button>
            </router-link>
            <el-button v-else-if="scope.row.financial_type == 'order' || scope.row.financial_type == 'brokerage_one' || scope.row.financial_type == 'brokerage_two' || scope.row.financial_type == 'order_platform_coupon'"  type="text" size="small" class="mr10" @click="onOrderDetail(scope.row.order_id)">详情</el-button>
            <router-link v-else :to=" { path:`${roterPre}` + '/order/refund?refund_order_sn='+scope.row.order_sn } ">
              <el-button type="text" size="small" class="mr10">详情</el-button>
            </router-link>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total,prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!--订单详情-->
    <order-detail
      ref="orderDetail"
      :orderId="orderId"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
      @getList="getList"
      :disabled="true"
      :drawer="drawer"
    ></order-detail>
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
import { capitalFlowLstApi, capitalFlowExportApi, getStatisticsApi } from "@/api/accounts";
import { roterPre } from '@/settings';
import cardsData from "@/components/cards/index";
import createWorkBook from '@/utils/newToExcel.js';
import timeOptions from '@/utils/timeOptions';
import orderDetail from '../../order/orderDetails.vue';
import selectSearch from '@/components/base/selectSearch';
export default {
  components: { orderDetail, cardsData, selectSearch },
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0,
      },
      roterPre: roterPre,
      listLoading: true,
      tableFrom: {
        keyword: "",
        date: "",
        page: 1,
        limit: 20,
      },
      timeVal: [],
      selectionList: [],
      ids: "",
      tableFromLog: {
        page: 1,
        limit: 10,
      },
      tableDataLog: {
        data: [],
        total: 0,
      },
      LogLoading: false,
      dialogVisible: false,
      evaluationStatusList: [
        { value: 1, label: "已回复" },
        { value: 0, label: "未回复" },
      ],
      cardLists: [],
      orderDatalist: null,
      orderId: 0,
      drawer: false,
      select: "nickname",
      searchSelectList: [
        {label: "昵称", value: "nickname"},
        {label: "用户ID", value: "uid"},
      ],
    };
  },
  mounted() {
    this.getList();
    this.getStatisticalData();
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.$refs.selectSearch.resetParmas()
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.getList();
      this.getStatisticalData();
    },
    // 获取统计数据
    getStatisticalData() {
      getStatisticsApi({date:this.tableFrom.date}).then((res) => {
        this.cardLists = res.data;
      }).catch((res) => {
        this.$message.error(res.message);
      });
    },
    async exports(value) {
      let excelData = JSON.parse(JSON.stringify(this.tableFrom)), data = []
      excelData.page = 1
      let pageCount = 1
      let lebData = {};
      for (let i = 0; i < pageCount; i++) {
        lebData = await this.downData(excelData)
        pageCount = Math.ceil(lebData.count/excelData.limit)
        if (lebData.export.length) {
          data = data.concat(lebData.export)
          excelData.page++
        }
      }
      createWorkBook(lebData.header, lebData.title, data, lebData.foot,lebData.filename);
      return
    },
    /**资金流水 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        capitalFlowExportApi(excelData).then((res) => {
          return resolve(res.data)
        })
      })
    },
    // 导出
    exportRecord() {
      capitalFlowExportApi(this.tableFrom)
        .then((res) => {
          const h = this.$createElement;
          this.$msgbox({
            title: '提示',
            message: h('p', null, [
              h('span', null, '文件正在生成中，请稍后点击"'),
              h('span', { style: 'color: teal' }, '导出记录'),
              h('span', null, '"查看~ '),
            ]),
            confirmButtonText: '我知道了',
          }).then(action => {

          });
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
    },
     // 订单详情
    onOrderDetail(order_id) {
      this.orderId = order_id
      this.$refs.orderDetail.getInfo(order_id);
      this.drawer = true;
    },
    searchList(data) {
      this.tableFrom = {...this.tableFrom, ...data};
      this.getList(1)
    },
    getSearchList() {
      this.$refs.selectSearch.changeSearch()
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num || this.tableFrom.page
      capitalFlowLstApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch((res) => {
          this.$message.error(res.message);
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
  },
};
</script>

<style lang="scss" scoped>
.el-icon-arrow-down {
  font-size: 12px;
}
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
.mt20 {
  margin-top: 20px;
}
.demo-image__preview {
  position: relative;
  padding-left: 40px;
}
.demo-image__preview .el-image,
.el-image__error {
  position: absolute;
  left: 0;
}
.maxw180 {
  display: inline-block;
  max-width: 180px;
}
</style>

<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        size="small"
        inline
        :model="tableFrom"
        ref="searchForm"
        label-width="90px"
      >
        <el-form-item label="退款单状态：" class="width100" prop="status">
          <el-radio-group
            v-model="tableFrom.status"
            type="button"
            @change="getList(1)"
          >
            <el-radio-button label
              >全部
              {{
                "(" + orderChartType.all ? orderChartType.all : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="0"
              >待审核
              {{
                "(" + orderChartType.audit ? orderChartType.audit : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="-1"
              >审核未通过
              {{
                "(" + orderChartType.refuse ? orderChartType.refuse : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="1"
              >审核通过
              {{
                "(" + orderChartType.agree ? orderChartType.agree : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="2"
              >待收货
              {{
                "(" + orderChartType.backgood
                  ? orderChartType.backgood
                  : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="4"
              >维权中
              {{
                "(" + orderChartType.platform
                  ? orderChartType.platform
                  : 0 + ")"
              }}</el-radio-button
            >
            <el-radio-button label="3"
              >已完成
              {{
                "(" + orderChartType.end ? orderChartType.end : 0 + ")"
              }}</el-radio-button
            >
          </el-radio-group>
        </el-form-item>
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="店铺类别：" prop="is_trader">
          <el-select
            v-model="tableFrom.is_trader"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="自营" value="1" />
            <el-option label="非自营" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="退款单号：" prop="refund_order_sn">
          <el-input
            v-model="tableFrom.refund_order_sn"
            @keyup.enter.native="getList(1)"
            placeholder="请输入订单号"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="订单单号：" prop="order_sn">
          <el-input
            v-model="tableFrom.order_sn"
            @keyup.enter.native="getList(1)"
            placeholder="请输入订单号"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <select-search
          ref="selectSearch"
          :select="select"
          :searchSelectList="searchSelectList"
          @search="searchList"
        />
        <el-form-item>
          <el-button type="primary" size="small" @click="getSearchList"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="exports"
        >导出列表</el-button
      >
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table"
        highlight-current-row
      >
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-form
              label-position="left"
              inline
              class="demo-table-expand demo-table-expands"
            >
              <el-form-item label="退款商品总价：">
                <span>{{ getTotal(props.row.refundProduct) }}</span>
              </el-form-item>
              <el-form-item label="退款商品总数：">
                <span>{{ props.row.refund_num }}</span>
              </el-form-item>
              <el-form-item label="申请退款时间：">
                <span>{{ props.row.create_time | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="用户备注：">
                <span>{{ props.row.mark | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="店铺备注：">
                <span>{{ props.row.mer_mark | filterEmpty }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column label="退款单号" min-width="180">
          <template slot-scope="scope">
            <span style="display: block;" v-text="scope.row.refund_order_sn" />
            <span
              v-if="scope.row.status == 4"
              style="color: #ED4014;display: block;"
              >平台介入</span
            >
            <span
              v-show="scope.row.is_del > 0"
              style="color: #ED4014;display: block;"
              >用户已删除</span
            >
          </template>
        </el-table-column>
        <el-table-column
          prop="user.nickname"
          label="用户信息"
          min-width="120"
        />
        <el-table-column
          prop="merchant.mer_name"
          label="店铺名称"
          min-width="120"
        />
        <el-table-column prop="mer_name" label="店铺类别" min-width="90">
          <template slot-scope="scope">
            <span v-if="scope.row.merchant" class="spBlock">{{
              scope.row.merchant.is_trader ? "自营" : "非自营"
            }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="refund_price" label="退款金额" min-width="100" />
        <el-table-column prop="nickname" label="商品信息" min-width="260">
          <template slot-scope="scope">
            <div
              v-for="(val, i) in scope.row.refundProduct"
              :key="i"
              class="tabBox acea-row row-middle"
            >
              <div class="demo-image__preview">
                <el-image
                  :src="val.product && val.product.cart_info.product.image"
                  :preview-src-list="[
                    val.product && val.product.cart_info.product.image
                  ]"
                />
              </div>
              <span class="tabBox_tit"
                >{{
                  val.product &&
                    val.product.cart_info.product.store_name + " | "
                }}{{
                  val.product && val.product.cart_info.productAttr.sku
                }}</span
              >
              <span class="tabBox_pice">{{
                "￥" +
                  val.product.cart_info.productAttr.price +
                  " x " +
                  val.product.product_num
              }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="serviceScore" label="退款单状态" min-width="90">
          <template slot-scope="scope">
            <span class="tags" :class="'tag-color' + scope.row.status">{{
              scope.row.status | orderRefundFilter
            }}</span>
          </template>
        </el-table-column>
        <el-table-column label="说明" min-width="220">
          <template slot-scope="scope">
            <span v-if="scope.row.status == -1" style="display: block"
              >拒绝原因：{{ scope.row.fail_message }}</span
            >
            <span style="display: block"
              >退款原因：{{ scope.row.refund_message }}</span
            >
            <span style="display: block"
              >状态变更：{{ scope.row.status_time }}</span
            >
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.status == 4"
              type="text"
              size="small"
              @click="protectionReview(scope.row)"
              >维权审核</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onOrderDetail(scope.row.order.order_id)"
              >订单详情</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onRefundOrderDetail(scope.row.refund_order_id)"
              >退款单详情</el-button
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
    <!--退款单详情-->
    <refund-details
      ref="refundDetails"
      :orderId="orderId"
      @protectionReview="protectionReview"
      @closeDrawer="closeRefundDrawer"
      @changeDrawer="changeRefundDrawer"
      @getList="getList"
      :disabled="true"
      :drawer="refundDrawer"
    ></refund-details>
    <!--退款维权审核-->
    <el-dialog title="退款审核弹窗" :visible.sync="plantVisible" width="600px">
      <el-form
        ref="saveForm"
        :model="forms"
        label-width="100px"
        size="small"
        :rules="ruleValidate"
        @submit.native.prevent
      >
        <el-form-item label="审核结果：">
          <el-radio-group v-model="forms.status">
            <el-radio :label="1">同意</el-radio>
            <el-radio :label="0">拒绝</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item
          v-if="forms.status == 0"
          label="填写原因："
          prop="platform_mark"
        >
          <el-input
            v-model="forms.platform_mark"
            placeholder="请输入拒绝原因，最多可输入50字"
            maxlength="50"
            type="textarea"
            :autosize="{ minRows: 3, maxRows: 5 }"
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="plantVisible = false" size="small">取 消</el-button>
        <el-button type="primary" size="small" @click="submitForm('saveForm')"
          >确 定</el-button
        >
      </span>
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
  refundorderListApi,
  orderUpdateApi,
  orderDeliveryApi,
  exportRefundOrderApi,
  protectionReviewApi
} from "@/api/order";
import createWorkBook from "@/utils/newToExcel.js";
import { fromList } from "@/libs/constants.js";
import { roterPre } from "@/settings";
import timeOptions from "@/utils/timeOptions";
import orderDetail from "../list/orderDetails.vue";
import refundDetails from "./refundDetails";
import selectSearch from "@/components/base/selectSearch";
export default {
  components: { orderDetail, refundDetails, selectSearch },
  name: "OrderRefund",
  data() {
    return {
      pickerOptions: timeOptions,
      orderId: 0,
      roterPre: roterPre,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      select: "nickname",
      searchSelectList: [
        { label: "昵称", value: "nickname" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ],
      tableFrom: {
        refund_order_sn: this.$route.query.refund_order_sn
          ? this.$route.query.refund_order_sn
          : "",
        order_sn: "",
        status: "",
        date: "",
        page: 1,
        limit: 20,
        is_trader: ""
      },
      orderChartType: {},
      timeVal: [],
      fromList: fromList,
      selectionList: [],
      ids: "",
      tableFromLog: {
        page: 1,
        limit: 10
      },
      tableDataLog: {
        data: [],
        total: 0
      },
      LogLoading: false,
      dialogVisible: false,
      cardLists: [],
      orderDatalist: null,
      drawer: false,
      refundDrawer: false,
      plantVisible: false,
      refundOrderId: "",
      forms: {
        status: 1,
        platform_mark: ""
      },
      ruleValidate: {
        platform_mark: [
          { required: true, message: "请输入拒绝原因", trigger: "blur" }
        ]
      }
    };
  },
  mounted() {
    if (this.$route.query.hasOwnProperty("sn")) {
      this.tableFrom.order_sn = this.$route.query.sn;
    } else {
      this.tableFrom.order_sn = "";
    }
    this.getList("");
  },
  // 被缓存接收参数
  activated() {
    if (this.$route.query.hasOwnProperty("sn")) {
      this.tableFrom.order_sn = this.$route.query.sn;
    } else {
      this.tableFrom.order_sn = "";
    }
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.tableFrom.order_sn = "";
      this.$refs.selectSearch.resetParmas();
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
    },
    // 订单详情
    onOrderDetail(order_id) {
      this.orderId = order_id;
      this.$refs.orderDetail.getInfo(order_id);
      this.drawer = true;
    },
    // 退款单详情
    onRefundOrderDetail(id) {
      this.orderId = id;
      this.refundDrawer = true;
      this.$refs.refundDetails.getRefundInfo(id);
    },
    changeRefundDrawer(v) {
      this.refundDrawer = v;
    },
    closeRefundDrawer() {
      this.refundDrawer = false;
    },
    // 维权审核
    protectionReview(row) {
      this.refundOrderId = row.refund_order_id;
      this.plantVisible = true;
    },
    //审核弹窗提交数据
    submitForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          protectionReviewApi(this.refundOrderId, this.forms)
            .then(res => {
              this.$message.success(res.message);
              this.plantVisible = false;
              this.getList();
              this.$refs.refundDetails.getRefundInfo(this.refundOrderId);
            })
            .catch(err => {
              this.$message.error(err.message);
            });
        } else {
          return false;
        }
      });
    },
    async exports() {
      let excelData = JSON.parse(JSON.stringify(this.tableFrom)),
        data = [];
      excelData.page = 1;
      let pageCount = 1;
      let lebData = {};
      for (let i = 0; i < pageCount; i++) {
        lebData = await this.downData(excelData);
        pageCount = Math.ceil(lebData.count / excelData.limit);
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        }
      }
      createWorkBook(
        lebData.header,
        lebData.title,
        data,
        lebData.foot,
        lebData.filename
      );
      return;
    },
    /**订单列表 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        exportRefundOrderApi(excelData).then(res => {
          return resolve(res.data);
        });
      });
    },
    getTotal(row) {
      let sum = 0;
      for (let i = 0; i < row.length; i++) {
        sum += row[i].product.cart_info.productAttr.price * row[i].refund_num;
      }
      return sum;
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page;
      this.getList("");
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val;
      this.getList(1);
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab;
      this.tableFrom.page = 1;
      this.timeVal = [];
      this.getList("");
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList("");
    },
    // 编辑
    edit(id) {
      this.$modalForm(orderUpdateApi(id)).then(() => this.getList(""));
    },
    // 发货
    send(id) {
      this.$modalForm(orderDeliveryApi(id)).then(() => this.getList(""));
    },
    searchList(data) {
      this.tableFrom = { ...this.tableFrom, ...data };
      this.getList(1);
    },
    getSearchList() {
      this.$refs.selectSearch.changeSearch();
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      refundorderListApi(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.orderChartType = res.data.stat;
          this.cardLists = res.data.stat;
          this.listLoading = false;
        })
        .catch(res => {
          this.$message.error(res.message);
          this.listLoading = false;
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    }
  }
};
</script>

<style lang="scss" scoped>
.demo-table-expands ::v-deep label {
  width: 110px !important;
  color: #99a9bf;
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
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
.tags {
  padding: 3px 8px;
  border-radius: 2px;
  border: 1px solid #ccc;
  color: #666;
  font-size: 12px;
}
.tag-color4 {
  border-color: #ff7d00;
  color: #ff7d00;
}
.tag-color-1 {
  border-color: #e93323;
  color: #e93323;
}
.tag-color3 {
  border-color: rgb(15, 198, 194);
  color: rgb(15, 198, 194);
}
</style>

<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form
        size="small"
        inline
        :model="tableFrom"
        ref="searchForm"
        label-width="85px"
      >
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
        <el-form-item label="活动类型：" prop="activity_type">
          <el-select
            v-model="tableFrom.activity_type"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1), headerList()"
          >
            <el-option
              v-for="item in activity"
              :key="item.type"
              :label="item.name"
              :value="item.type"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="店铺名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            remote
            :remote-method="getMerSelect"
            placeholder="请选择"
            class="selWidth my-select"
            @visible-change="getMerSelect()"
            @change="getMerSelect(), getList(1), headerList()"
          >
            <el-option
              v-for="item in merSelect"
              :key="item.mer_id"
              :label="item.mer_name"
              :value="item.mer_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="店铺类别：" prop="is_trader">
          <el-select
            v-model="tableFrom.is_trader"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1), headerList()"
          >
            <el-option label="自营" value="1" />
            <el-option label="非自营" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="商品名称：" prop="store_name">
          <el-input
            v-model="tableFrom.store_name"
            @keyup.enter.native="getList(1), headerList()"
            placeholder="请输入商品名称"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="支付方式：" prop="pay_type">
          <el-select
            v-model="tableFrom.pay_type"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1), headerList()"
          >
            <el-option label="余额" value="0" />
            <el-option label="微信" value="1" />
            <el-option label="支付宝" value="2" />
            <el-option label="线下支付" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="发货方式：" prop="filter_delivery">
          <el-select
            v-model="tableFrom.filter_delivery"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1), headerList()"
          >
            <el-option
              v-for="item in dliveryWayList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="商品类型：" prop="filter_product">
          <el-select
            v-model="tableFrom.filter_product"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1), headerList()"
          >
            <el-option
              v-for="item in productTypeList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="订单搜索：">
          <el-input
            placeholder="请输入内容"
            v-model="orderKeywords"
            class="input-with-select selWidth"
            clearable
            @keyup.enter.native="changeSearch"
          >
            <el-select
              v-model="orderSelect"
              slot="prepend"
              placeholder="请选择"
            >
              <el-option label="订单号" value="order_sn">订单号</el-option>
              <el-option label="收货人" value="delivery_name">收货人</el-option>
              <el-option label="收货电话" value="delivery_phone"
                >收货电话</el-option
              >
            </el-select>
          </el-input>
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
    <!-- <cards-data v-if="cardLists.length > 0" :card-lists="cardLists" /> -->
    <el-card>
      <el-tabs
        v-if="orderChartType"
        v-model="tableFrom.status"
        @tab-click="getList(1)"
      >
        <!-- <el-tab-pane v-for="(item,index) in headeNum" :key="index" :name="item.order_type.toString()" :label="item.title +'('+item.count +')' " /> -->
        <el-tab-pane
          name=""
          :label="'全部' + '(' + (orderChartType.all || 0) + ')'"
        />
        <el-tab-pane
          name="1"
          :label="'待付款' + '(' + (orderChartType.unpaid || 0) + ')'"
        />
        <el-tab-pane
          name="2"
          :label="'待发货' + '(' + (orderChartType.unshipped || 0) + ')'"
        />
        <el-tab-pane
          name="3"
          :label="'待收货' + '(' + (orderChartType.untake || 0) + ')'"
        />
        <el-tab-pane
          name="4"
          :label="'待评价' + '(' + (orderChartType.unevaluate || 0) + ')'"
        />
        <el-tab-pane
          name="5"
          :label="'交易完成' + '(' + (orderChartType.complete || 0) + ')'"
        />
        <el-tab-pane
          name="6"
          :label="'已退款' + '(' + (orderChartType.refund || 0) + ')'"
        />
        <el-tab-pane
          name="7"
          :label="'已删除' + '(' + (orderChartType.del || 0) + ')'"
        />
      </el-tabs>
      <div class="mt5">
        <el-button size="small" type="primary" class="mb20" @click="exports"
          :loading="isExporting">导出列表</el-button
        >
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table"
        highlight-current-row
        :cell-class-name="addTdClass"
      >
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-form label-position="left" inline class="demo-table-expand">
              <el-form-item label="商品总价：">
                <span>{{ props.row.total_price | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="下单时间：">
                <span>{{ props.row.create_time | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="用户备注：">
                <span>{{ props.row.mark | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="店铺备注：">
                <span>{{ props.row.remark | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="总单号：">
                <span>{{
                  props.row.groupOrder
                    ? props.row.groupOrder.group_order_sn
                    : ""
                }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column label="订单编号" width="190">
          <template slot-scope="scope">
            <div v-text="scope.row.order_sn"></div>
            <div
              v-show="scope.row.is_del > 0"
              style="color: #ED4014;"
              >用户已删除</div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="130">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              @click.native="onUserDetails(scope.row.uid)"
              >{{
                scope.row.user && scope.row.user.nickname + "/" + scope.row.uid
              }}</el-button
            >
          </template>
        </el-table-column>
        <el-table-column label="订单类型" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.is_virtual !== 4">{{
              scope.row.is_virtual == 1
                ? "虚拟订单"
                : scope.row.order_type == 0
                ? "普通订单"
                : "核销订单"
            }}</span>
            <span v-else>预约服务</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="real_name"
          label="收货人/订购人"
          min-width="120"
        />
        <el-table-column label="店铺名称" min-width="100">
          <template slot-scope="scope">
            <span>{{
              scope.row.merchant ? scope.row.merchant.mer_name : ""
            }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品信息" min-width="330">
          <template slot-scope="scope">
            <div class="pro-cell fs-12" v-for="(val, i) in scope.row.orderProduct" :key="i">
              <el-tooltip placement="top" :open-delay="300">
                <div slot="content" style="max-width: 300px;">
                  <div>
                    {{ val.cart_info.product.store_name + " | "
                    }}{{ val.cart_info.productAttr.sku || '默认' }}
                  </div>
                  <div>
                    <span>¥{{ val.cart_info.productAttr.price }}</span>
                    <span class="pl-10">x{{ val.product_num }}</span>
                  </div>
                </div>
                <div class="flex">
                  <div class="w-40 h-40">
                    <el-image
                      :src="val.cart_info.product.image"
                      :preview-src-list="[val.cart_info.product.image]"
                      style="width: 40px; height: 40px;"
                      fit="cover"
                    />
                  </div>
                  <div class="lh-20px ml-10 line2">
                    {{ val.cart_info.product.store_name + " | "
                    }}{{ val.cart_info.productAttr.sku || '默认'  }}
                  </div>
                </div>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="实际支付" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.pay_price }}</span>
            <p v-if="scope.row.finalOrder">
              尾款：{{ scope.row.finalOrder.pay_price }}
            </p>
          </template>
        </el-table-column>
        <el-table-column label="支付方式" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.paid === 1">{{
              scope.row.pay_type | orderPayType
            }}</span>
            <span v-else>--</span>
          </template>
        </el-table-column>
        <el-table-column label="支付状态" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.paid == 0 ? "未支付" : "已支付" }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" min-width="130">
          <template slot-scope="scope">
            <!-- 未删除订单 -->
            <span v-if="scope.row.is_del === 0">
              <!-- 未付款订单 -->
              <span
                v-if="scope.row.paid === 0"
                class="statusBox"
                :style="{
                  color: orderColorFilter(1),
                  borderColor: orderColorFilter(1)
                }"
                >待付款</span
              >
              <!-- 已付款订单的状态 -->
              <span v-if="scope.row.paid !== 0 && scope.row.is_virtual !== 4">
                <span
                  class="statusBox"
                  v-if="
                    scope.row.order_type === 0 || scope.row.order_type === 2
                  "
                  :style="{
                    color: orderColorFilter(scope.row.status),
                    borderColor: orderColorFilter(scope.row.status)
                  }"
                >
                  {{ scope.row.status | orderStatusFilter }}
                </span>
                <span
                  v-else
                  class="statusBox"
                  :style="{
                    color: orderColorFilter(scope.row.status),
                    borderColor: orderColorFilter(scope.row.status)
                  }"
                >
                  {{ scope.row.status | cancelOrderStatusFilter }}
                </span>
              </span>
              <!-- 预约商品的订单状态 -->
              <span v-if="scope.row.is_virtual == 4 && scope.row.paid !== 0">
                <!-- order_type == 0上门订单 -->
                <span
                  v-if="scope.row.order_type == 0"
                  class="statusBox"
                  :style="{
                    color: orderColorFilter(scope.row.status),
                    borderColor: orderColorFilter(scope.row.status)
                  }"
                >
                  {{ scope.row.status | reservationOrderStatusFilter }}
                </span>

                <!-- order_type == 1到店订单 -->
                <span
                  v-if="scope.row.order_type == 1"
                  class="statusBox"
                  :style="{
                    color: orderColorFilter(scope.row.status),
                    borderColor: orderColorFilter(scope.row.status)
                  }"
                >
                  {{ scope.row | reservationOrderStatusFilter1 }}
                </span>
              </span>
            </span>
            <span
              v-if="scope.row.is_del !== 0"
              class="statusBox"
              :style="{
                color: orderColorFilter(3),
                borderColor: orderColorFilter(3)
              }"
              >已删除</span
            >
          </template>
        </el-table-column>
        <el-table-column prop="serviceScore" label="下单时间" min-width="150">
          <template slot-scope="scope">
            <span>{{ scope.row.create_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <span v-for="(val, i) in scope.row.orderProduct" :key="i">
              <el-button
                v-if="orderFilter(scope.row)"
                type="text"
                size="small"
                @click="onRefundDetail(scope.row.order_sn)"
                >查看退款单</el-button
              >
            </span>
            <el-button
              type="text"
              size="small"
              @click="onOrderDetails(scope.row.order_id)"
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
    <!--用户信息-->
    <user-details
      ref="userDetails"
      :drawer="userDawer"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
      :uid="uid"
      :isUser="false"
    />
    <!--详情-->
    <order-detail
      ref="orderDetail"
      :orderId="orderId"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
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
import {
  orderListApi,
  chartApi,
  cardListApi,
  exportOrderApi
} from "@/api/order";
import { merSelectListApi } from "@/api/product";
import userDetails from "../../user/list/userDetails";
import orderDetail from "./orderDetails.vue";
import createWorkBook from "@/utils/newToExcel.js";
import cardsData from "@/components/cards/index";
import { fromList } from "@/libs/constants.js";
import timeOptions from "@/utils/timeOptions";
import selectSearch from "@/components/base/selectSearch";
export default {
  components: { orderDetail, cardsData, userDetails, selectSearch },
  data() {
    return {
      isExporting: false,
      pickerOptions: timeOptions,
      orderId: 0,
      tableData: {
        data: [],
        total: 0
      },
      activity: [
        { name: "普通购买", type: 0 },
        { name: "秒杀活动", type: 1 },
        { name: "预售活动", type: 2 },
        { name: "助力活动", type: 3 },
        { name: "拼团活动", type: 4 }
      ],
      listLoading: true,
      orderKeywords: "",
      orderSelect: "order_sn",
      select: "nickname",
      searchSelectList: [
        { label: "昵称", value: "nickname" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ],
      tableFrom: {
        order_sn: this.$route.query.order_sn ? this.$route.query.order_sn : "",
        group_order_sn: "",
        keywords: "",
        username: "",
        store_name: "",
        status: this.$route.query.status || "",
        date: "",
        mer_id: "",
        page: 1,
        limit: 20,
        is_trader: "",
        pay_type: "",
        filter_delivery: "",
        filter_product: "",
        activity_type: ""
      },
      dliveryWayList: [
        { value: 1, label: "快递订单" },
        { value: 2, label: "配送订单" },
        { value: 4, label: "核销订单" },
        { value: 3, label: "虚拟发货" },
        { value: 6, label: "自动发货" }
      ], //发货方式
      productTypeList: [
        { value: 1, label: "实物商品" },
        { value: 2, label: "虚拟商品" },
        { value: 3, label: "卡密商品" },
        { value: 4, label: "预约商品" }
      ], //商品类型
      orderChartType: null,
      timeVal: [],
      fromList: fromList,
      selectionList: [],
      ids: "",
      uid: "",
      visibleDetail: false,
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
      merSelect: [],
      drawer: false,
      userDawer: false
    };
  },
  mounted() {
    if (this.$route.query.hasOwnProperty("order_sn")) {
      this.tableFrom.order_sn = this.$route.query.order_sn;
    } else {
      this.tableFrom.order_sn = "";
    }
    this.headerList();
    this.getMerSelect();
    // this.getCardList();
    this.getList("");
  },
  // 被缓存接收参数
  activated() {
    if (this.$route.query.hasOwnProperty("order_sn")) {
      this.tableFrom.order_sn = this.$route.query.order_sn;
    } else {
      this.tableFrom.order_sn = "";
    }
    this.headerList();
    this.getMerSelect();
    // this.getCardList();
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.orderKeywords = "";
      this.orderSelect = "order_sn";
      this.tableFrom.delivery_name = "";
      this.tableFrom.delivery_phone = "";
      this.tableFrom.order_sn = "";
      this.$refs.selectSearch.resetParmas();
    },
    orderColorFilter(status) {
      const statusMap = {
        "0": "#0FC6C2",
        "1": "#FF7D00",
        "2": "#3491FA",
        "3": "#CCCCCC",
        "-1": "#F56464",
        "9": "#F56464",
        "10": "#FF7D00",
        "11": "#F56464",
        "20": "#4073FA"
      };
      return statusMap[status];
    },
    // 退款详情页
    onRefundDetail(sn) {
      this.$router.push({
        path: "refund",
        query: {
          sn: sn
        }
      });
    },
    // 订单筛选
    orderFilter(item) {
      let status = false;
      item.orderProduct.forEach(el => {
        if (el.refund_num > 0 && el.refund_num < el.product_num) {
          status = true;
        }
      });
      return status;
    },
    // 表格某一行添加特定的样式
    addTdClass(val) {
      if (val.row.status > 0 && val.row.paid == 1) {
        for (let i = 0; i < val.row.orderProduct.length; i++) {
          if (
            val.row.orderProduct[i].refund_num > 0 &&
            val.row.orderProduct[i].refund_num <
              val.row.orderProduct[i].product_num
          ) {
            return "row-bg";
          }
        }
      } else {
        return " ";
      }
    },
    // 店铺列表；
    getMerSelect(query) {
      merSelectListApi({ keyword: query })
        .then(res => {
          this.merSelect = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 用户信息
    onUserDetails(uid) {
      this.uid = uid;
      this.userDawer = true;
      this.$refs.userDetails.getData(uid, false, true);
    },
    closeDrawer() {
      this.drawer = false;
      this.userDawer = false;
    },
    changeDrawer(v) {
      this.drawer = v;
      this.userDawer = v;
    },
    async exports() {
      this.isExporting = true;
      try {
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
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.isExporting = false;
      }
    },
    /**订单列表 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        exportOrderApi(excelData).then(res => {
          return resolve(res.data);
        });
      });
    },
    // 详情
    onOrderDetails(id) {
      this.orderId = id;
      this.$refs.orderDetail.getInfo(id);
      this.drawer = true;
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page;
      this.getList("");
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val;
      this.getList("");
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab;
      this.tableFrom.page = 1;
      this.timeVal = [];
      // this.getCardList();
      this.getList(1);
      this.headerList();
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      // this.getCardList();
      this.getList(1);
      this.headerList();
    },
    changeSearch() {
      this.tableFrom[this.orderSelect] = this.orderKeywords;
      this.searchList(null);
    },
    searchList(data) {
      this.tableFrom = { ...this.tableFrom, ...data };
      this.tableFrom.delivery_phone = "";
      this.tableFrom.delivery_name = "";
      this.tableFrom.order_sn = "";
      this.tableFrom[this.orderSelect] = this.orderKeywords;
      this.getList(1);
      // this.getCardList();
      this.headerList();
    },
    getSearchList() {
      this.$refs.selectSearch.changeSearch();
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      orderListApi(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.$message.error(res.message);
          this.listLoading = false;
        });
    },
    getCardList() {
      cardListApi(this.tableFrom)
        .then(res => {
          this.cardLists = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList("");
    },
    headerList() {
      chartApi(this.tableFrom)
        .then(res => {
          this.orderChartType = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    }
  }
};
</script>

<style lang="scss" scoped>
.demo-table-expand ::v-deep label {
  width: 83px !important;
}
.statusBox {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 4px;
  border: 1px solid #cccccc;
}
.el-dropdown-link {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 12px;
}
.el-icon-arrow-down {
  font-size: 12px;
}
::v-deep .row-bg .cell {
  color: red !important;
}
::v-deep .el-input-group__prepend .el-input {
  min-width: 100px;
}
.w-40 {
  width: 40px;
}
.h-40 {
  height: 40px;
}
.lh-20px {
  line-height: 20px;
}
.ml-10 {
  margin-left: 10px;
}
.pl-10 {
  padding-left: 10px;
}
.pro-cell ~ .pro-cell {
  margin-top: 8px;
}
.fs-12{
  font-size: 12px;
}
</style>

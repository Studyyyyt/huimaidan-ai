<template>
  <div class="divBox">
    <pages-header
      ref="pageHeader"
      title="秒杀统计"
      backUrl="/marketing/seckill/store_seckill/list"
    ></pages-header>
    <div class="selCard mt14 mb14">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        inline
        size="small"
        label-width="85px"
      >
        <el-form-item v-if="type == 'order'" label="订单状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择订单状态"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in orderStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="搜索：" prop="keyword" label-width="59px">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            :placeholder="
              type == 'product'
                ? '请输入商品名称/ID'
                : '请输入用户姓名/手机/用户ID'
            "
            clearable
            class="selWidth"
          />
        </el-form-item>
        <el-form-item v-if="type !== 'product'" label="活动日期：">
          <el-date-picker
            clearable
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
        <!-- <el-form-item label="活动场次：" prop="store_status">
          <el-select
            v-model="tableFrom.seckill_time"
            placeholder="请选择活动场次"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in spikeTimeList"
              :key="item.seckill_time_id"
              :label="item.title"
              :value="item.seckill_time_id.toString()"
            />
          </el-select>
        </el-form-item> -->
        <el-form-item label="店铺名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1), getCardList()"
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
    <cards-data
      v-if="cardLists.length > 0"
      :card-lists="cardLists"
      :more="true"
    />
    <el-card>
      <el-tabs v-model="type" @tab-click="getList(1)">
        <el-tab-pane label="活动参与人" name="participants"></el-tab-pane>
        <el-tab-pane label="活动订单" name="order"></el-tab-pane>
        <el-tab-pane label="活动商品" name="product"></el-tab-pane>
      </el-tabs>
      <el-table
        v-if="type == 'participants'"
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          key="11"
          prop="nickname"
          label="用户姓名"
          min-width="90"
        >
          <template slot-scope="scope">
            <span v-if="scope.row.uid">[{{ scope.row.uid }}]</span
            >{{ scope.row.nickname }}
          </template>
        </el-table-column>
        <el-table-column
          key="10"
          prop="sum_total_num"
          label="购买件数"
          min-width="80"
        />
        <el-table-column
          key="9"
          prop="order_count"
          label="支付订单数"
          min-width="90"
        />
        <el-table-column
          key="8"
          prop="sum_pay_price"
          label="支付金额"
          min-width="90"
        />
        <el-table-column
          key="7"
          prop="create_time"
          label="最近参与时间"
          min-width="90"
        />
      </el-table>
      <el-table
        v-if="type == 'order'"
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column
          key="1"
          prop="order_sn"
          label="订单号"
          min-width="100"
        />
        <el-table-column key="2" prop="nickname" label="用户" min-width="90" />
        <el-table-column key="3" prop="status" label="订单状态" min-width="90">
          <template slot-scope="scope">
              <span v-if="scope.row.paid === 0">待付款</span>
              <span v-else>
                <span
                  v-if="
                    scope.row.order_type === 0 || scope.row.order_type === 2
                  "
                  >{{ scope.row.status | orderStatusFilter }}</span
                >
                <span v-else>{{
                  scope.row.status | takeOrderStatusFilter
                }}</span>
              </span>
              <span v-if="scope.row.is_del === 1" style="color: #F56C6C;">(已删除)</span>
          </template>
        </el-table-column>
        <el-table-column
          key="4"
          prop="pay_price"
          label="订单支付金额"
          min-width="90"
        />
        <el-table-column
          key="5"
          prop="total_num"
          label="订单商品数"
          min-width="90"
        />
        <el-table-column prop="create_time" label="下单时间" min-width="100" />
        <el-table-column
          key="6"
          prop="pay_time"
          label="支付时间"
          min-width="100"
        />
      </el-table>
      <el-table
        v-show="type == 'product'"
        ref="tableList"
        row-key="product_id"
        :data="tableData.data"
        v-loading="listLoading"
        size="small"
        :default-expand-all="isExpand"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column key="12" prop="product_id" label="ID" min-width="80">
          <template slot-scope="scope">
            <span v-if="!scope.row.sku">{{ scope.row.product_id }}</span>
          </template>
        </el-table-column>
        <el-table-column key="13" label="商品图片" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                :src="scope.row.image"
                :preview-src-list="[scope.row.image]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column key="21" label="商品名称" min-width="200">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.store_name }}</div>
              <span class="line2">{{ scope.row.store_name }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column key="14" label="商品分类" min-width="80">
          <template slot-scope="scope">
            <span>{{
              (scope.row.storeCategory && scope.row.storeCategory.cate_name) ||
                "-"
            }}</span>
          </template>
        </el-table-column>
        <el-table-column
          key="15"
          prop="mer_name"
          label="店铺名称"
          min-width="80"
        />
        <el-table-column key="16" prop="ot_price" label="售价" min-width="80" />
        <el-table-column key="17" prop="stock" label="限量" min-width="80" />
        <el-table-column key="18" prop="price" label="秒杀价" min-width="70" />
        <el-table-column
          key="19"
          prop="sales"
          label="秒杀销量"
          min-width="70"
        />
        <el-table-column key="20" label="活动场次" min-width="100">
          <template slot-scope="scope">
            <div v-for="(item, i) in scope.row.seckill_time_text_arr" :key="i">
              {{ item }}<br />
            </div>
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
import {
  seckillCardApi,
  seckillIntervalListApi,
  seckillParticipantList,
  seckillOrderList,
  seckillProductList
} from "@/api/marketing";
import { roterPre } from "@/settings";
import timeOptions from "@/utils/timeOptions";
import cardsData from "@/components/cards/index";
import { mapGetters } from "vuex";
export default {
  name: "SeckillStatistics",
  components: { cardsData },
  data() {
    return {
      activity_id: this.$route.params.id,
      isExpand: false,
      cardLists: [],
      spikeTimeList: [],
      type: "participants",
      pickerOptions: timeOptions,
      getSelectSessionApi: [],
      orderStatusList: [
        { label: "全部", value: "" },
        { label: "待付款", value: 1 },
        { label: "待发货", value: 2 },
        { label: "待收货", value: 3 },
        { label: "待核销", value: 8 },
        { label: "待评价", value: 4 },
        { label: "交易完成", value: 5 },
        { label: "已退款", value: 6 }
      ],
      timeVal: [],
      roterPre: roterPre,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        status: "",
        keyword: "",
        mer_id: "",
        date: ""
      }
    };
  },
  computed: {
    ...mapGetters(["merSelect"])
  },
  mounted() {
    if (!this.merSelect.length) this.$store.dispatch("product/getMerSelect");
    this.getCardList();
    this.getSeckillIntervalList();
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
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList(1);
    },
    getCardList() {
      seckillCardApi(this.activity_id)
        .then(res => {
          this.cardLists = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取活动相关的秒杀场次
    getSeckillIntervalList() {
      seckillIntervalListApi(this.activity_id)
        .then(res => {
          this.spikeTimeList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      let type = this.type;
      switch (type) {
        case "order":
          seckillOrderList(this.activity_id, this.tableFrom)
            .then(res => {
              this.tableData.data = res.data.list;
              this.tableData.total = res.data.count;
              this.listLoading = false;
            })
            .catch(res => {
              this.listLoading = false;
              this.$message.error(res.message);
            });
          break;
        case "product":
          seckillProductList(this.activity_id, this.tableFrom)
            .then(res => {
              this.tableData.total = res.data.count;
              this.getAttrValue(res.data.list);
              this.listLoading = false;
            })
            .catch(res => {
              this.listLoading = false;
              this.$message.error(res.message);
            });
          break;
        default:
          seckillParticipantList(this.activity_id, this.tableFrom)
            .then(res => {
              this.tableData.data = res.data.list;
              this.tableData.total = res.data.count;
              this.listLoading = false;
            })
            .catch(res => {
              this.listLoading = false;
              this.$message.error(res.message);
            });
      }
    },
    // 选中商品
    getAttrValue(row) {
      const _this = this;
      row.map(item => {
        _this.$set(
          item,
          "mer_name",
          item.merchant && item.merchant.mer_name ? item.merchant.mer_name : ""
        );
        _this.$set(
          item,
          "seckill_time_text_arr",
          (item.seckillActive && item.seckillActive.seckill_time_text_arr) || []
        );
        item.attrValue.map(i => {
          _this.$set(i, "store_name", i.sku || "默认");
          _this.$set(i, "image", i.image);
          _this.$set(i, "mer_name", item.mer_name);
          _this.$set(i, "storeCategory", item.storeCategory);
          _this.$set(i, "stock", i.stock || 0);
          _this.$set(i, "sales", i.sales || 0);
          _this.$set(i, "price", i.price || 0);
          _this.$set(i, "product_id", i.value_id || 0);
          _this.$set(i, "seckill_time_text_arr", item.seckill_time_text_arr);
        });
        _this.$set(item, "children", item.attrValue);
      });
      _this.tableData.data = row;
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

<style scoped lang="scss">
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
</style>

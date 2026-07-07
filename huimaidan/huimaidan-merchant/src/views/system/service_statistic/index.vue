<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        inline
        size="small"
        label-width="80px"
      >
        <el-form-item label="服务人员：" prop="staff_name">
          <el-input
            v-model="tableFrom.name"
            placeholder="请输入服务人员姓名"
            clearable
            class="w-250"
          />
        </el-form-item>
        <el-form-item label="用户搜索">
          <el-input clearable v-model="tableFrom.keyword" placeholder="请输入" class="w-250">
            <el-select v-model="select" slot="prepend" style="width: 100px">
              <el-option value="uid" label="用户 ID"></el-option>
              <el-option value="nickname" label="用户昵称"></el-option>
              <el-option value="phone" label="用户电话"></el-option>
            </el-select>
          </el-input>
        </el-form-item>
        <el-form-item label="服务时间：">
          <el-date-picker
            v-model="timeVal"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="yyyy-MM-dd"
            @change="onchangeTime"
            style="width:250px;"
            :picker-options="pickerOptions"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getSearchList">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <el-card class="mt14">
      <el-table v-loading="loading" :data="tableData.data" size="small">
        <el-table-column label="服务人员" min-width="160">
          <template slot-scope="scope">
            <div class="user-box">
              <el-image
                class="avatar"
                :src="scope.row.photo || moren"
              />
              <span class="name">{{ scope.row.name }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="用户信息" min-width="180">
          <template slot-scope="scope">
            <span>{{ scope.row.user.nickname }} | {{ scope.row.user.uid }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="serviceFee" label="服务费" min-width="100" />
        <el-table-column prop="pendingNum" label="待服务" min-width="90" />
        <el-table-column prop="orderCount" label="订单数量" min-width="100" />
        <el-table-column prop="productCount" label="服务商品数量" min-width="120" />
        <el-table-column prop="phone" label="联系电话" min-width="130" />
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onDetail(scope.row)">详情</el-button>
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
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1000px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div class="drawer-box">
        <!-- 顶部人员信息 -->
        <div class="drawer-header">
          <div class="drawer-user">
            <el-image class="avatar-lg" :src="detailData.photo || moren" />
            <div class="user-meta">
              <div class="user-name">{{ detailData.name }}</div>
              <div class="user-phone">{{ detailData.phone }}</div>
            </div>
          </div>
        </div>

        <!-- 汇总指标 -->
        <div class="drawer-summary">
          <div class="summary-item">
            <div class="summary-label">服务费</div>
            <div class="summary-value">¥{{ detailData.serviceFee }}</div>
          </div>
          <div class="summary-item">
            <div class="summary-label">待服务</div>
            <div class="summary-value">{{ detailData.pendingNum }}</div>
          </div>
          <div class="summary-item">
            <div class="summary-label">订单数量</div>
            <div class="summary-value">{{ detailData.orderCount }}</div>
          </div>
          <div class="summary-item">
            <div class="summary-label">服务商品数量</div>
            <div class="summary-value">{{ detailData.productCount }}</div>
          </div>
        </div>

        <!-- 订单明细 -->
        <el-table :data="productList" size="small" class="mt14">
          
          <el-table-column prop="reservation_date" label="服务时间" min-width="160" />
          <el-table-column label="商品信息" min-width="280">
            <template slot-scope="scope">
              <div class="goods-box">
                <el-image class="goods-img" :src="scope.row.product_image || moren" />
                <div class="goods-info flex-1">
                  <div class="goods-title line2">{{ scope.row.product_name }}</div>
                  <!-- <div class="goods-sub">{{ scope.row.spec }}　¥{{ scope.row.pay_amount }}x{{ scope.row.count }}</div> -->
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="settlement_price" label="服务费" min-width="90" />
          <el-table-column prop="count" label="商品数量" min-width="100" />
          <el-table-column prop="pay_price" label="订单实付" min-width="100" />
          <el-table-column prop="order_sn" label="订单编号" min-width="220" />
          <el-table-column label="订单状态" min-width="100">
            <template slot-scope="scope">
              <el-tag type="info" size="medium" effect="plain" v-show="scope.row.status == 1">待服务</el-tag>
              <el-tag size="medium" effect="plain" v-show="scope.row.status == 20">服务中</el-tag>
              <el-tag type="success" effect="plain" size="medium" v-show="scope.row.status == 3">已完成</el-tag>
              <el-tag type="danger" effect="plain" size="medium" v-show="scope.row.status == -1">已退款</el-tag>
              <el-tag type="warning" effect="plain" size="medium" v-show="scope.row.status == 2">待评价</el-tag>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <i class="el-icon-close close-btn" @click="handleClose"></i>
    </el-drawer>
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
import { staffStatisticsListApi, staffStatisticsDetailApi } from "@/api/system";
import timeOptions from "@/utils/timeOptions";

export default {
  name: "ServiceStatistic",
  data() {
    return {
      pickerOptions: timeOptions,
      moren: require("@/assets/images/f.png"),
      loading: false,
      timeVal: [],
      // 展示数据与分页数据
      tableData: {
        data: [],
        total: 0
      },
      // 查询条件
      tableFrom: {
        page: 1,
        limit: 20,
        name: "",
        service_date: "",
        keyword: ""
      },
      // selectSearch 配置
      select: "",
      drawer: false,
      direction: "rtl",
      // 抽屉详情假数据
      detailData: {
        name: "",
        phone: "",
        avatar: "",
        totalFee: "",
        pending: 0,
        orderCount: 0,
        productCount: 0,
        order:[]
      },
      productList: []
    };
  },
  mounted() {
    this.getList(1);
  },
  methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.select = "";
      this.tableFrom = {
        page: 1,
        limit: 20,
        name: "",
        service_date: "",
        keyword: ""
      };
      this.getList(1);
    },
    // 手动触发 selectSearch 内部取值
    getSearchList() {
      if(this.tableFrom.keyword && this.select){
        this.tableFrom[this.select] = this.tableFrom.keyword;
      }else{
        this.tableFrom[this.select] = "";
      }
      this.getList(1);
    },
    // 日期范围变更
    onchangeTime(e) {
      this.timeVal = e || [];
      this.tableFrom.service_date = this.timeVal && this.timeVal.length === 2 ? this.timeVal : [];
      this.getList(1);
    },
    // 本地列表筛选 + 分页
    getList(num) {
      this.loading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      staffStatisticsListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.loading = false
      }).catch(res => {
        this.$message.error(res.msg);
      })
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList();
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList();
    },
    // 详情展示（占位）
    onDetail(row) {
      staffStatisticsDetailApi(row.staffs_id).then(res=>{
        this.detailData = res.data;
          this.drawer = true;
          const list = (res.data.order || []).reduce((acc, order) => {
            const items = order.orderProduct || [];
            items.forEach(op => {
              const product = op;
              this.$set(product,'reservation_date', order.reservation_date);
              this.$set(product,'order_sn', order.order_sn);
              this.$set(product,'pay_price', order.pay_price);
              this.$set(product,'count', order.orderProduct[0].product_num);
              this.$set(product,'settlement_price', order.settlement_price);
              this.$set(product,'status', order.status);
              if (product) acc.push(product);
            });
            return acc;
        }, []);
        console.log(list);
        this.productList = list;
        }).catch(res=>{
        this.$message.error(res.msg);
      })

    },
    handleClose() {
      this.drawer = false;
    },
  }
};
</script>
<style lang="scss">
.drawer-box .el-table__body-wrapper {
  scrollbar-width: thin;
}
</style>
<style scoped lang="scss">
@import "@/styles/form.scss";
.w-250 {
  width: 250px;
}
.user-box {
  display: flex;
  align-items: center;
}
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-right: 8px;
  object-fit: cover;
}
.name {
  color: #333;
}
.drawer-box {
  padding: 20px 35px;
}
.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.drawer-user {
  display: flex;
  align-items: center;
}
.avatar-lg {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}
.user-meta .user-name {
  font-size: 16px;
  font-weight: 600;
}
.user-meta .user-phone {
  color: #606266;
  margin-top: 4px;
}
.drawer-summary {
  display: flex;
  margin-top: 16px;
  padding: 12px 0 24px;
  border-bottom: 1px solid #f0f2f5;
}
.summary-item {
  width: 200px;
}
.summary-label {
  color: #666;
  font-size: 13px;
  margin-bottom: 4px;
}
.summary-value {
  font-size: 14px;
  color: rgba(0,0,0,.85);
}
.goods-box {
  display: flex;
  align-items: center;
}
.goods-img {
  width: 48px;
  height: 48px;
  border-radius: 4px;
  object-fit: cover;
  margin-right: 10px;
}
.goods-info {
  display: flex;
  flex-direction: column;
}
.goods-title {
  color: #303133;
}
.goods-sub {
  color: #909399;
  margin-top: 4px;
}
.flex-1{
  flex: 1;
}
.close-btn {
  position: absolute;
  right: 40px;
  top: 40px;
  cursor: pointer;
  font-size: 20px;
}
</style>
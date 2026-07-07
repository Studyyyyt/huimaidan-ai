<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1000px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div>
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">退款单信息-{{orderDatalist.refund_type == 1 ? '仅退款' : '退货退款'}}</div>
              <div v-if="orderDatalist.order">
                <span class="mr20">订单号：{{ orderDatalist.order.order_sn }}</span>
              </div>
            </div>
            <div>
              <el-button
                v-if="orderDatalist.status == 0"
                type="primary"
                size="small"
                @click="onOrderStatus"
                >{{orderDatalist.refund_type == 1 ? '审核' : '退货审核'}}</el-button
              >
              <el-dropdown @command="handleCommand" class="ml10">
                <el-button icon="el-icon-more" size="small"></el-button>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="mark">订单备注</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">退款单状态</div>
              <div>
                <div class="value1">{{ orderDatalist.status | orderRefundFilter }}</div>
              </div>
            </li>
            <li class="item">
              <div class="title">实际退款</div>
              <div>
                ¥{{ orderDatalist.refund_price }}
              </div>
            </li>
            <li class="item">
              <div class="title">退回方式</div>
              <div>原路返回</div>
            </li>
            <li class="item">
              <div class="title">申请退款时间</div>
              <div>{{ orderDatalist.create_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="订单信息" name="detail">
            <div v-if="orderDatalist.user" class="section">
              <div class="title">用户信息</div>
              <ul class="list">
                <li class="item">
                  <div>用户昵称：</div>
                  <div class="value">
                    {{ orderDatalist.user.nickname +' | '+ orderDatalist.user.uid || '-'}}
                  </div>
                </li>
                <li class="item">
                  <div>用户电话：</div>
                  <div class="value">{{ orderDatalist.order.user_phone }}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">售后提交信息</div>
              <ul class="list">
                <li class="item">
                  <div>退款总件数：</div>
                  <div class="value">{{ orderDatalist.refund_num }}</div>
                </li>
                <li class="item">
                  <div>退款原因：</div>
                  <div class="value">{{ orderDatalist.refund_message }}</div>
                </li>
                <li class="item">
                  <div>退款发起方：</div>
                  <div class="value">{{ orderDatalist.create_user }}</div>
                </li>
                <li class="item item100">
                  <div>用户备注：</div>
                  <div class="value">{{ orderDatalist.mark }}</div>
                </li>
                <li class="item item100">
                  <div>退款凭证：</div>
                  <div class="value">
                    <div class="product_name">
                      <div class="demo-image__preview">
                        <el-image
                          v-for="(item,index) in orderDatalist.pics"
                          :key="index"
                          :src="item"
                          class="mr5"
                          :preview-src-list="[item]"
                        />
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="orderDatalist.refund_type == 2 && orderDatalist.status == 2" class="section">
              <div class="title">退货地址信息</div>
              <ul class="list">
                <li class="item">
                  <div>退货联系人：</div>
                  <div class="value">{{ orderDatalist.mer_delivery_user }}</div>
                </li>
                <li class="item">
                  <div>联系电话：</div>
                  <div class="value">{{ orderDatalist.phone }}</div>
                </li>
                <li class="item item100">
                  <div>退货地址：</div>
                  <div class="value">
                    {{ orderDatalist.mer_delivery_address }}
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="orderDatalist.delivery_phone" class="section">
              <div class="title">用户退货信息</div>
              <ul class="list">
                <li class="item">
                  <div>联系电话：</div>
                  <div class="value">{{ orderDatalist.delivery_phone }}</div>
                </li>
                <li class="item">
                  <div>快递公司：</div>
                  <div class="value">
                    {{ orderDatalist.delivery_type }}
                    <el-button type="text" size="mini" style="margin-left: 5px" @click="getLoginstics">物流查询</el-button>
                  </div>
                </li>
                <li class="item">
                  <div>物流单号：</div>
                  <div class="value">
                    {{ orderDatalist.delivery_id }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">商家备注</div>
              <ul class="list">
                <li class="item item100">
                  <div>商家备注：</div>
                  <div class="value">{{ orderDatalist.mer_mark }}</div>
                </li>
              </ul>
            </div>
            <div v-if="orderDatalist.status == -1" class="section">
              <div class="title">拒绝原因</div>
              <ul class="list">
                <li class="item item100">
                  <div>拒绝原因：</div>
                  <div class="value">{{ orderDatalist.fail_message }}</div>
                </li>
              </ul>
            </div>
            <div v-if="orderDatalist.platform" class="section">
              <div class="title">平台介入</div>
              <ul class="list">
                <li class="item">
                  <div>审核结果：</div>
                  <div class="value">{{ orderDatalist.platform.change_type == 'refund_platform_refuse' ? '拒绝退款' : '同意退款' }}</div>
                </li>
                <li v-if="orderDatalist.platform.change_message" class="item">
                  <div>原因说明：</div>
                  <div class="value">{{ orderDatalist.platform.change_message }}</div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品信息" name="goods">
            <el-table :data="orderDatalist.refundProduct" size="small">
              <el-table-column label="商品ID" prop="order_product_id" min-width="90"/>
              <el-table-column label="商品信息" min-width="300">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="demo-image__preview">
                      <el-image
                        :src="scope.row.product.cart_info.product.image"
                        :preview-src-list="[scope.row.product.cart_info.product.image]"
                      />
                    </div>
                    <div>
                      <div>{{ scope.row.product.cart_info.product.store_name }}</div>
                      <div class="line1 gary">
                        规格：{{
                          scope.row.product.cart_info.productAttr.sku ? scope.row.product.cart_info.productAttr.sku : '默认'
                        }}
                      </div>
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="售价(元)" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.product.cart_info.productAttr.price ? scope.row.product.cart_info.productAttr.price : '-' }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="退款数量" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.refund_num }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="实付金额(元)"  min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.product.total_price || '-' }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="退款金额(元)" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.refund_price ? scope.row.refund_price : '-' }}
                    </div>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
          <el-tab-pane label="订单记录" name="orderList">
            <div>
              <el-form size="small" label-width="80px">
                <div class="acea-row">
                  <el-form-item label="操作端：">
                    <el-select
                      v-model="tableFromLog.user_type"
                      placeholder="请选择"
                      style="width: 140px; margin-right: 20px"
                      clearable
                      filterable
                      @change="onOrderLog(orderId)"
                    >
                      <el-option label="系统" value="0" />
                      <el-option label="用户" value="1" />
                      <el-option label="平台" value="2" />
                      <el-option label="商户" value="3" />
                      <el-option label="商家客服" value="4" />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="操作时间：">
                    <el-date-picker
                      style="width: 380px; margin-right: 20px"
                      v-model="timeVal"
                      type="datetimerange"
                      placeholder="选择日期"
                      value-format="yyyy/MM/dd HH:mm:ss"
                      clearable
                      @change="onchangeTime"
                    >
                    </el-date-picker>
                  </el-form-item>
                </div>
              </el-form>
            </div>
            <el-table :data="tableDataLog.data" size="small">
              <el-table-column prop="order_id" label="订单编号" min-width="200">
                <template slot-scope="scope">
                  <span>{{ scope.row.order_sn }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作记录" min-width="200">
                <template slot-scope="scope">
                  <span>{{ scope.row.change_message }}</span>
                </template>
              </el-table-column>
              <el-table-column label="操作角色" min-width="150">
                <template slot-scope="scope">
                  <div class="tab">
                    <div>{{ operationType(scope.row.user_type) }}</div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="操作人" min-width="150">
                <template slot-scope="scope">
                  <div class="tab">
                    <div>{{ scope.row.nickname }}</div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="操作时间" min-width="150">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">{{ scope.row.change_time }}</div>
                  </div>
                </template>
              </el-table-column>
            </el-table>
            <div class="block">
              <el-pagination :page-size="tableFromLog.limit" :current-page="tableFromLog.page" layout="prev, pager, next, jumper" :total="tableDataLog.total" @size-change="handleSizeChangeLog" @current-change="pageChangeLog" />
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
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
import {
  refundorderLogApi,
  refundorderDetailApi,
  refundorderMarkApi
} from '@/api/order';

export default {
  components: {},
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      loading: true,
      orderId: '',
      direction: 'rtl',
      activeName: 'detail',
      goodsList: [],
      timeVal: [],
      orderConfirm: false,
      sendGoods: false,
      dialogLogistics: false,
      confirmReceiptForm: {
        id: '',
      },
      tableDataLog: {
        data: [],
        total: 0
      },
      contentList: [],
      nicknameList: [],
      result: [],
      orderDatalist: {},
      orderImg: require('@/assets/images/order_icon.png'),
      tableFromLog: {
        user_type: '',
        date: [],
        page: 1,
        limit: 10
      },
    };
  },
  filters: {
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFromLog.date = e ? this.timeVal.join('-') : ''
      this.onOrderLog(this.orderId)
    },
    handleClose() {
      this.activeName = 'detail';
      this.$emit('closeDrawer');
      this.tableFromLog.date = []
    },
    /**查看物流 */
    getLoginstics(){
      this.$emit('get-logistics',this.orderId)
    },
    /**退款审核 */
    onOrderStatus() {
      this.$emit('onOrderStatus',this.orderId)
    },
    /**退款单详情信息 */
    getRefundInfo(id){
      this.loading = true
      this.orderId = id
      refundorderDetailApi(id)
        .then(res => {
          this.orderDatalist = res.data
          this.loading = false
          this.onOrderLog(id)
        })
        .catch(({ message }) => {
          this.loading = false
          this.$message.error(message)
        })
    },
    //下拉
    handleCommand(command) {
      if (command == 'mark') {
        this.$modalForm(refundorderMarkApi(this.orderId)).then(() => this.getRefundInfo(this.orderId))
      }
    },
    tabClick(tab) {
      if (tab.name === 'orderList') {
       this.onOrderLog(this.orderId)
      }
    },
    onOrderLog(id){
      refundorderLogApi(id, this.tableFromLog).then((res) => {
        this.tableDataLog.data = res.data.list
        this.tableDataLog.total = res.data.count
      })
      .catch((res) => {
        this.$message.error(res.message);
      });
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page
      this.onOrderLog(this.orderId)
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val
      this.onOrderLog(this.orderId)
    },
    operationType(type) {
      if (type == 0) {
        return '系统';
      } else if (type == 1) {
        return '用户';
      } else if (type == 2) {
        return '平台';
      } else if (type == 3) {
        return '商户';
      } else if (type == 4) {
        return '商家客服';
      } else {
        return '未知';
      }
    },
  },
};
</script>
<style lang="scss" scoped>
.head {
  padding: 20px 35px;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    .iconfont {
      color: var(--prev-color-primary);
      &.sale-after {
        color: #90add5;
      }
    }
    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;
      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
      .value1 {
        color: #f56022;
      }

      .value2 {
        color: #1bbe6b;
      }

      .value3 {
        color: #437FFD;
      }

      .value4 {
        color: #6a7b9d;
      }

      .value5 {
        color: #f5222d;
      }
    }
  }
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    align-items: center;

    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
    &.item100 {
      flex: 0 0 calc(100% / 1);
      padding-left: 0;
    }
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
.gary {
  color: #aaa;
}

.demo-image__preview {
  .el-image,img{
    width: 40px;
    height: 40px;
  }
}
</style>

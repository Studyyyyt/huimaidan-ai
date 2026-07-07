<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        size="small"
        label-width="85px"
        inline
      >
        <el-form-item
          label="订单类型："
          style="display: block;"
          prop="order_type"
        >
          <el-radio-group
            v-model="tableFrom.order_type"
            type="button"
            @change="getList(1)"
          >
            <el-radio-button label="-1"
              >全部
              <span v-if="headeNum.type">{{
                "(" + headeNum.type.count + ")"
              }}</span></el-radio-button
            >
            <el-radio-button label="0"
              >普通订单
              <span v-if="headeNum.type_0">{{
                "(" + headeNum.type_0.count + ")"
              }}</span></el-radio-button
            >
            <el-radio-button label="1"
              >核销订单
              <span v-if="headeNum.type_1">{{
                "(" + headeNum.type_1.count + ")"
              }}</span></el-radio-button
            >
            <el-radio-button label="2"
              >虚拟订单
              <span v-if="headeNum.type_2">{{
                "(" + headeNum.type_2.count + ")"
              }}</span></el-radio-button
            >
            <el-radio-button label="3"
              >卡密订单
              <span v-if="headeNum.type_3">{{
                "(" + headeNum.type_3.count + ")"
              }}</span></el-radio-button
            >
            <el-radio-button label="4"
              >预约服务订单
              <span v-if="headeNum.type_4">{{
                "(" + headeNum.type_4.count + ")"
              }}</span></el-radio-button
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
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="发货方式：" prop="filter_delivery">
          <el-select
            v-model="tableFrom.filter_delivery"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in dliveryWayList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="商品名称：" prop="store_name">
          <el-input
            v-model="tableFrom.store_name"
            placeholder="请输入商品名称"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="总单单号：" prop="group_order_sn">
          <el-input
            v-model="tableFrom.group_order_sn"
            placeholder="请输入总单订单号"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="商品类型：" prop="filter_product">
          <el-select
            v-model="tableFrom.filter_product"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in productTypeList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="活动类型：" prop="activity_type">
          <el-select
            v-model="tableFrom.activity_type"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in activityList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="支付方式：" prop="pay_type">
          <el-select
            v-model="tableFrom.pay_type"
            clearable
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
        <el-form-item label="关键字：" prop="keywords">
          <el-input
            v-model="tableFrom.keywords"
            placeholder="请输入订单号/收货人/联系方式"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>

        <select-search
          ref="selectSearch"
          :select="select"
          :searchSelectList="searchSelectList"
          @search="searchList"
        />
        <el-form-item label="订单类别：" prop="is_behalf">
          <el-select
            v-model="tableFrom.is_behalf"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="用户下单" value="0" />
            <el-option label="代客下单" value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="快递单号：" prop="delivery_id">
          <el-input
            v-model="tableFrom.delivery_id"
            placeholder="请输入快递单号"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="自提点：" prop="merchant_take_id ">
          <el-select
            v-model="tableFrom.merchant_take_id"
            filterable
            remote
            :remote-method="getMerchantStationList"
            :loading="merchantStationLoading"
            clearable
            placeholder="请选择自提点"
            class="selWidth"
            @change="getList(1),handleStationChange()"
            @clear="handleStationChange"
          >
            <el-option label="用户下单" v-for="item in filterStationList" :key="item.station_id" :label="item.station_name" :value="item.station_id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getSearchList"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <!-- <cards-data v-if="cardLists.length > 0" :card-lists="cardLists" /> -->
    <el-card class="dataBox">
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
        <el-button
          size="small"
          type="primary"
          class="mr14"
          @click="orderCancellation('')"
          >订单核销</el-button
        >
        <el-dropdown class="dropdown" @command="exports">
          <span class="el-dropdown-link">
            导出列表<i class="el-icon-arrow-down el-icon--right" />
          </span>
          <el-dropdown-menu slot="dropdown">
            <el-dropdown-item command="1">导出订单</el-dropdown-item>
            <el-dropdown-item command="2">导出发货单</el-dropdown-item>
          </el-dropdown-menu>
        </el-dropdown>
        <el-upload
          style="display:inline-block;"
          size="small"
          class="mr14 ml5"
          :headers="myHeaders"
          :action="fileUrl"
          :on-success="handleSuccess"
          :show-file-list="false"
        >
          <el-button size="small">导入批量发货</el-button>
        </el-upload>
        <el-button size="small" @click="getDeliveryList"
          >批量发货记录</el-button
        >
        <el-button size="small" @click="batchSend">批量发送货</el-button>
        <el-button size="small" @click="printNote">打印配货单</el-button>
        <div
          v-if="cityTakeFail.length > 0"
          class="ml14"
          style="display: inline-block;"
        >
          <span class="el-icon-info"></span>
          第三方同城配送同步失败，请点击
          <el-button type="text" size="small" @click="syncAgain"
            >再次同步</el-button
          >
        </div>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        class="table mt20"
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
                <span>{{ props.row.create_time }}</span>
              </el-form-item>
              <el-form-item label="交易单号：">
                <span>{{ props.row.transaction_id || "-" }}</span>
              </el-form-item>
              <el-form-item label="用户备注：">
                <span style="display: inline-block; width: 200px;">{{
                  props.row.mark | filterEmpty
                }}</span>
              </el-form-item>
              <el-form-item label="商家备注：">
                <span>{{ props.row.remark | filterEmpty }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column width="50">
          <template slot="header" slot-scope="scope">
            <el-popover
              placement="top-start"
              width="100"
              trigger="hover"
              class="tabPop"
            >
              <div>
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'dan' }"
                  @click="onHandle('dan', scope.$index)"
                  >选中本页</span
                >
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'duo' }"
                  @click="onHandle('duo')"
                  >选中全部</span
                >
              </div>
              <el-checkbox
                slot="reference"
                :value="
                  (chkName === 'dan' &&
                    checkedPage.indexOf(tableFrom.page) > -1) ||
                    chkName === 'duo'
                "
                @change="changeType"
              />
            </el-popover>
          </template>
          <template slot-scope="scope">
            <el-checkbox
              :value="
                checkedIds.indexOf(scope.row.order_id) > -1 ||
                  (chkName === 'duo' &&
                    noChecked.indexOf(scope.row.order_id) === -1)
              "
              @change="v => changeOne(v, scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="订单编号" min-width="170">
          <template slot-scope="scope">
            <span style="display: block;" v-text="scope.row.order_sn" />
            <span v-if="scope.row.is_del !== 0" class="red-text"
              >用户已删除</span
            >
          </template>
        </el-table-column>
        <el-table-column
          prop="real_name"
          label="收货人/订购人"
          min-width="130"
        />
        <el-table-column label="商品信息" min-width="330">
          <template slot-scope="scope">
            <div class="pro-cell fs-12" v-for="(val, i) in scope.row.orderProduct" :key="i">
              <el-tooltip placement="top" :open-delay="300">
                <div slot="content" style="max-width: 300px;">
                  <div>
                    {{ val.cart_info.product.store_name + " | "
                    }}{{ val.cart_info.productAttr.sku || '默认'  }}
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
                    }}{{ val.cart_info.productAttr.sku || "默认" }}
                  </div>
                </div>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="实际支付" min-width="100">
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
            <span>{{ scope.row.paid === 0 ? "未支付" : "已支付" }}</span>
          </template>
        </el-table-column>
        <el-table-column label="订单状态" min-width="130">
          <template slot-scope="scope">
            <!-- 未删除订单 -->
            <!-- <span v-if="scope.row.is_del === 0"> -->
            <span>
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
            <!-- <span
              v-if="scope.row.is_del !== 0"
              class="statusBox"
              :style="{
                color: orderColorFilter(3),
                borderColor: orderColorFilter(3)
              }"
              >已删除</span
            > -->
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="下单时间" min-width="130" />
        <el-table-column key="8" label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="
                scope.row.paid === 0 &&
                  scope.row.is_del === 0 &&
                  scope.row.pay_type === 7
              "
              type="text"
              size="small"
              @click="onConfirmPayment(scope.row.order_id)"
              >确认支付</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onOrderDetails(scope.row.order_id)"
              >详情</el-button
            >
            <el-button
              v-if="orderFilter(scope.row)"
              type="text"
              size="small"
              @click="onRefundDetail(scope.row.order_sn)"
              >查看退款单</el-button
            >
            <el-button
              v-if="
                scope.row.paid === 0 &&
                  scope.row.is_del === 0 &&
                  scope.row.activity_type != 2
              "
              type="text"
              size="small"
              @click="edit(scope.row.order_id)"
              >编辑</el-button
            >
            <el-button
              v-if="
                scope.row.order_type == 0 &&
                  scope.row.status === 0 &&
                  scope.row.paid === 1 &&
                  scope.row.is_virtual !== 4
              "
              type="text"
              size="small"
              @click="send(scope.row, scope.row.order_id)"
              >发送货</el-button
            >
            <el-button
              v-if="scope.row.is_del !== 0"
              type="text"
              size="small"
              @click.native="handleDelete(scope.row, scope.$index)"
              >删除</el-button
            >
            <el-button
              v-if="
                scope.row.order_type == 1 &&
                  scope.row.status === 0 &&
                  scope.row.paid === 1
              "
              type="text"
              size="small"
              @click.native="orderCancellation(scope.row.verify_code)"
              >去核销</el-button
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
    <!--记录-->
    <el-dialog title="操作记录" :visible.sync="dialogVisible" width="700px">
      <el-table
        v-loading="LogLoading"
        border
        :data="tableDataLog.data"
        style="width: 100%"
      >
        <el-table-column
          prop="order_id"
          align="center"
          label="订单ID"
          min-width="80"
        />
        <el-table-column
          prop="change_message"
          label="操作记录"
          align="center"
          min-width="280"
        />
        <el-table-column
          prop="change_time"
          label="操作时间"
          align="center"
          min-width="280"
        />
      </el-table>
      <div class="block">
        <el-pagination
          :page-size="tableFromLog.limit"
          :current-page="tableFromLog.page"
          layout="prev, pager, next, jumper"
          :total="tableDataLog.total"
          @size-change="handleSizeChangeLog"
          @current-change="pageChangeLog"
        />
      </div>
    </el-dialog>
    <!--编辑-->
    <el-dialog title="修改订单" :visible.sync="editVisible" width="700px">
      <el-form
        ref="formValidate"
        :model="formValidate"
        label-width="120px"
        @submit.native.prevent
      >
        <el-form-item label="订单总价：">
          <el-input-number
            v-model="formValidate.total_price"
            :min="0"
            placeholder="请输入订单总价"
            @change="changePrice"
          />
        </el-form-item>
        <el-form-item label="实际支付邮费：">
          <el-input-number
            v-model="formValidate.pay_postage"
            :min="0"
            placeholder="请输入订单邮费"
            @change="changePrice"
          />
        </el-form-item>
        <el-form-item label="优惠金额：">
          <span>{{ formValidate.coupon_price }}</span>
        </el-form-item>
        <el-form-item label="积分抵扣金额：">
          <span>{{ formValidate.integral_price }}</span>
        </el-form-item>
        <el-form-item label="实际支付金额：">
          <span>{{ formValidate.pay_price }}</span>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="editConfirm">确定</el-button>
      </span>
    </el-dialog>
    <!--发送货-->
    <el-dialog
      :title="isBatch ? '批量发货' : '订单发送货'"
      :visible.sync="sendVisible"
      width="900px"
      :before-close="handleClose"
    >
      <el-form
        ref="shipment"
        :model="shipment"
        :rules="rules"
        label-width="120px"
        @submit.native.prevent
      >
        <div class="delivery-info" v-if="
            (shipment.delivery_type == 1 || shipment.delivery_type == 4) &&
              tableFrom.order_type != 2 &&
              orderType != 1
          ">
          <div class="info-item flex mb-10">
            <div class="info-item-label">收货人：</div>
            <div class="info-item-value">{{ rowData.real_name }}</div>
          </div>
          <div class="info-item flex mb-10">
            <div class="info-item-label">收货电话：</div>
            <div class="info-item-value">{{ rowData.user_phone }}</div>
          </div>
          <div class="info-item flex">
            <div class="info-item-label">收件人：</div>
            <div class="info-item-value">{{ rowData.user_address }}</div>
          </div>
        </div>
        <el-form-item
          v-if="isResend && noLogistics != 3 && tableFrom.order_type != 2"
          :label="
            shipment.delivery_type == 1 || shipment.delivery_type == 4
              ? '原快递公司：'
              : '送货人姓名：'
          "
        >
          <span>{{ original.delivery_name }}</span>
        </el-form-item>
        <el-form-item
          v-if="isResend && noLogistics != 3 && tableFrom.order_type != 2"
          :label="
            shipment.delivery_type == 1 || shipment.delivery_type == 4
              ? '原快递单号：'
              : '送货人手机号：'
          "
        >
          <span>{{ original.delivery_id }}</span>
        </el-form-item>
        <el-form-item label="选择类型：" required prop="delivery_type">
          <el-radio-group v-model="selectType" @change="changeSend">
            <el-radio
              :label="item.value"
              v-for="item of selectTypeOptions"
              :key="item.value"
              >{{ item.text }}</el-radio
            >
          </el-radio-group>
        </el-form-item>
        <el-form-item
          label="发货类型："
          v-if="selectType == 1"
          prop="delivery_type"
        >
          <el-radio-group v-model="shipment.delivery_type">
            <el-radio v-if="selectType == 1" :label="1">手动填写</el-radio>
            <!-- <el-radio :label="3" class="radio"> {{orderType == 1 ? '虚拟发货' : '无需物流'}}</el-radio> -->
            <el-radio
              v-if="isDump && mer_dump_switch && selectType == 1"
              :label="4"
              class="radio"
              >电子面单打印</el-radio
            >
            <!-- <el-radio v-if="selectType == 2" :label="2">商家配送</el-radio> -->
            <!-- <el-radio
              v-if="!isBatch && selectType == 2 && delivery_status == 1"
              :label="5"
              >第三方配送{{ third_delivery }}</el-radio
            > -->
            <el-radio
              v-if="!isBatch && selectType == 1"
              :label="8"
              >商家寄件</el-radio
            >
          </el-radio-group>
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 5 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="选择发货点："
          prop="station_id"
        >
          <el-select
            v-model="shipment.station_id"
            size="small"
            placeholder="请选择配送发货点"
            class="pageWidth"
          >
            <el-option
              v-for="(item, index) in storeList"
              :key="item.value + index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item
          v-if="
            (shipment.delivery_type == 1 || shipment.delivery_type == 4) &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="快递公司："
          prop="delivery_name"
        >
          <el-select
            filterable
            v-model="shipment.delivery_name"
            size="small"
            placeholder="请选择快递公司"
            class="pageWidth"
            @change="getTempsLst(shipment.delivery_name)"
          >
            <el-option
              v-for="item in deliveryList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>

        <el-form-item
          v-if="
            shipment.delivery_type == 5 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="包裹重量(kg)："
          prop="cargo_weight"
        >
          <el-input-number
            v-model="shipment.cargo_weight"
            class="pageWidth"
            size="small"
            placeholder="请输入包裹重量"
          />
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 5 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="配送备注："
        >
          <el-input
            type="textarea"
            v-model="shipment.mark"
            size="small"
            class="pageWidth"
            placeholder="请输入配送单备注"
          />
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 1 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="快递单号："
          prop="delivery_id"
        >
          <el-input
            v-model="shipment.delivery_id"
            size="small"
            class="pageWidth"
            placeholder="请输入快递单号"
          />
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 4 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="电子面单："
          prop="temp_id"
        >
          <el-select
            v-model="shipment.temp_id"
            size="small"
            placeholder="请选择电子面单"
            class="pageWidth"
          >
            <el-option
              v-for="(item, index) in eleTempsLst"
              :key="item.temp_id + index"
              :label="item.title"
              :value="item.temp_id"
            />
          </el-select>
          <el-button type="text" @click="getPicture(eleTempsLst)"
            >预览</el-button
          >
        </el-form-item>

        <el-form-item
          v-if="
            (shipment.delivery_type == 4 || shipment.delivery_type == 8) &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="寄件人姓名："
          prop="from_name"
        >
          <el-input
            v-model="shipment.from_name"
            size="small"
            class="pageWidth"
            placeholder="请输入寄件人姓名"
          />
        </el-form-item>
        <el-form-item
          v-if="
            (shipment.delivery_type == 4 || shipment.delivery_type == 8) &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="寄件人电话："
          prop="from_tel"
        >
          <el-input
            v-model="shipment.from_tel"
            size="small"
            class="pageWidth"
            placeholder="请输入寄件人电话"
          />
        </el-form-item>
        <el-form-item
          v-if="
            (shipment.delivery_type == 4 || shipment.delivery_type == 8) &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="寄件人地址："
          prop="from_addr"
        >
          <el-input
            v-model="shipment.from_addr"
            type="textarea"
            size="small"
            class="pageWidth"
            placeholder="请输入寄件人地址"
          />
        </el-form-item>
        <!--商家寄件-->
        <!--商家寄件-快递公司-->
        <el-form-item
          v-if="
            shipment.delivery_type == 8 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="快递公司："
          prop="delivery_name"
        >
          <el-select
            filterable
            clearable
            v-model="shipment.delivery_name"
            size="small"
            placeholder="请输入选择快递公司"
            class="pageWidth"
            @change="getSendData"
          >
            <el-option
              v-for="item in sendExpressList"
              :key="item.value"
              :label="item.label"
              :value="item.code"
            />
          </el-select>
        </el-form-item>
        <!--商家寄件-快递业务类型-->
        <el-form-item
          v-if="shipment.delivery_type == 8"
          label="快递业务类型："
          prop="delivery_name"
        >
          <el-select
            filterable
            clearable
            v-model="shipment.service_type"
            size="small"
            placeholder="请输入选择快递业务类型"
            class="pageWidth"
          >
            <el-option
              v-for="item in expressTypeList"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>
        <!--商家寄件-电子面单-->
        <el-form-item
          v-if="
            shipment.delivery_type == 8 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="电子面单："
          prop="temp_id"
        >
          <el-select
            filterable
            clearable
            v-model="shipment.temp_id"
            size="small"
            placeholder="请输入选择电子面单"
            class="pageWidth"
          >
            <el-option
              v-for="(item, index) in sendTempsLst"
              :key="item.temp_id + index"
              :label="item.title"
              :value="item.temp_id"
            />
          </el-select>
          <el-button type="text" @click="getPicture(sendTempsLst)"
            >预览</el-button
          >
        </el-form-item>
        <!--取件日期-->
        <el-form-item v-if="shipment.delivery_type == 8" label="取件日期：">
          <el-radio-group v-model="shipment.day_type">
            <el-radio :label="0">今天</el-radio>
            <el-radio :label="1">明天</el-radio>
            <el-radio :label="2">后天</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="shipment.delivery_type == 8" label="取件时间：">
          <el-time-picker
            is-range
            value-format="HH:mm"
            format="HH:mm"
            type="datetimerange"
            placement="bottom-end"
            v-model="value1"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            placeholder="选择时间范围"
            @change="onchangeTime1"
          >
          </el-time-picker>
        </el-form-item>
        <el-form-item v-if="shipment.delivery_type == 8" label="预计寄件金额：">
          <span v-if="deliveryPrice">¥{{ deliveryPrice }}</span>
          <el-button
            type="text"
            size="small"
            @click="getDeliveryPrice('shipment')"
            >立即计算</el-button
          >
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 2 &&
              tableFrom.order_type != 2 &&
              orderType != 1
          "
          label="送货人姓名："
          prop="to_name"
        >
          <el-select
            filterable
            v-model="shipment.to_name"
            size="small"
            placeholder="请选择送货人"
            @change="handleChange(shipment.to_name)"
            class="pageWidth"
          >
            <el-option
              v-for="item in deliveryPersonList"
              :key="item.service_id"
              :label="item.name"
              :value="item.service_id"
            />
          </el-select>
          <!-- <el-input v-model="shipment.to_name" size="small" maxlength="10" class="pageWidth" placeholder="请输入送货人姓名" /> -->
        </el-form-item>
        <el-form-item
          v-if="
            shipment.delivery_type == 2 &&
              tableFrom.order_type != 2 &&
              orderType != 2
          "
          label="送货人手机号："
          prop="to_phone"
        >
          <el-input
            v-model="shipment.to_phone"
            size="small"
            class="pageWidth"
            placeholder="请输入送货人手机号"
          />
        </el-form-item>

        <el-form-item
          v-if="
            shipment.type != 4 &&
              activityType != 2 &&
              (productList.length > 1 || productNum > 1)
          "
          label="分单发货："
        >
          <el-switch
            v-model="shipment.is_split"
            :active-value="1"
            :inactive-value="0"
            :width="55"
            active-text="开启"
            inactive-text="关闭"
          />
          <p class="area-desc">
            可选择表格中的商品单独发货，发货后会生成新的订单且不能撤回，请谨慎操作！
          </p>
        </el-form-item>
        <el-form-item
          v-if="
            shipment.is_split == 1 &&
              tableFrom.order_type != 2 &&
              (productList.length > 1 || productNum > 1)
          "
          label=""
        >
          <el-table
            ref="multipleSelection"
            :data="productList"
            tooltip-effect="dark"
            size="mini"
            class="table-line"
            :row-key="
              row => {
                return row.product_id;
              }
            "
            @selection-change="handleSelectionChange"
          >
            <el-table-column
              align="center"
              type="selection"
              :reserve-selection="true"
              min-width="50"
            />
            <el-table-column align="center" label="商品信息" min-width="200">
              <template slot-scope="scope">
                <div class="acea-row" style="align-items: center;">
                  <div class="demo-image__preview">
                    <el-image
                      :src="scope.row.cart_info.product.image"
                      :preview-src-list="[scope.row.cart_info.product.image]"
                    />
                  </div>
                  <span class="priceBox" style="width: 150px;">{{
                    scope.row.cart_info.product.store_name
                  }}</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column align="center" label="规格" min-width="80">
              <template slot-scope="scope">
                <span class="priceBox">{{
                  scope.row.cart_info.productAttr.sku
                }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="商品售价" min-width="80">
              <template slot-scope="scope">
                <span class="priceBox">{{
                  scope.row.cart_info.productAttr.price
                }}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="总数" min-width="80">
              <template slot-scope="scope">
                <span class="priceBox">{{ scope.row.stock_num }}</span>
              </template>
            </el-table-column>
            <el-table-column label="待发数量" align="center" min-width="120">
              <template slot-scope="scope">
                <el-input
                  v-model="scope.row['product_num_input']"
                  type="number"
                  :min="0"
                  :max="scope.row.refund_num"
                  size="small"
                  class="priceBox"
                  @blur="limitCount(scope.row)"
                />
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
        <el-form-item label="备注：" prop="remark">
          <el-input
            v-model="shipment.remark"
            type="textarea"
            class="pageWidth"
            placeholder="请输入备注"
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="handleClose" size="small">取 消</el-button>
        <el-button type="primary" size="small" :loading="sendFormLoading" @click="submitForm('shipment')"
          >提交</el-button
        >
      </span>
    </el-dialog>
    <!--电子面单预览-->
    <el-dialog
      v-if="pictureVisible"
      :visible.sync="pictureVisible"
      width="500px"
    >
      <img :src="pictureUrl" class="pictures" />
    </el-dialog>
    <!--详情-->
    <order-detail
      ref="orderDetail"
      :orderId="orderId"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
      @reSend="reSend"
      @reDriving="reDriving"
      @onOrderRefund="onOrderRefund"
      @send="send"
      @changeAddress="changeAddress"
      @getList="getList"
      :drawer="drawer"
    ></order-detail>
    <delivery-record ref="deliveryList" />
    <!--订单核销-->
    <order-cancellate ref="orderCancellate" @getList="getList" />
    <!--退款-->
    <order-refund ref="orderRefund" @refundSuccess="refundSuccess" />
    <!--修改收货地址-->
    <el-dialog
      v-if="addressVisible"
      :visible.sync="addressVisible"
      title="修改收货地址"
      width="600px"
    >
      <el-form
        ref="addressForm"
        :model="addressForm"
        :rules="addressRules"
        label-width="90px"
        @submit.native.prevent
      >
        <el-form-item label="收货人：" prop="to_name">
          <el-input
            v-model="addressForm.delivery_id"
            size="small"
            maxlength="50"
            placeholder="请输入收货人姓名，最多50字"
          />
        </el-form-item>
        <el-form-item label="收货电话：" prop="to_phone">
          <el-input
            v-model="addressForm.to_phone"
            size="small"
            placeholder="请输入收货电话"
          />
        </el-form-item>
        <el-form-item label="收货地址：" prop="from_addr">
          <el-input
            v-model="addressForm.from_addr"
            type="textarea"
            size="small"
            placeholder="请输入收货地址，最多100字"
          />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="handleClose" size="small">取 消</el-button>
        <el-button
          type="primary"
          size="small"
          @click="submitAddress('addressForm')"
          >提交</el-button
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
  orderListApi,
  chartApi,
  orderUpdateApi,
  orderDetailApi,
  orderDeliveryApi,
  expressTypeApi,
  batchDeliveryApi,
  orderLogApi,
  orderDeleteApi,
  orderRemarkApi,
  orderPrintApi,
  exportOrderApi,
  orderCancellationApi,
  orderHeadListApi,
  exportInvoiceApi,
  cardListApi,
  expressLst,
  deliveryPersonSelect,
  exprTempsLst,
  getEleTempData,
  getDeliveryStoreLst,
  offlinePay,
  orderReDriving,
  calculateCost,
  orderSyncApi
} from "@/api/order";
import { getConfigApi, deliveryStoreLst } from "@/api/systemForm";
import createWorkBook from "@/utils/newToExcel.js";
import { serveInfoApi, deliverySetApi } from "@/api/setting";
import orderDetail from "./orderDetails.vue";
import deliveryRecord from "@/components/deliveryRecord/index";
import orderCancellate from "./orderCancellate";
import orderRefund from "./orderRefund";
import cardsData from "@/components/cards/index";
import { getToken } from "@/utils/auth";
import SettingMer from "@/libs/settingMer";
import { roterPre } from "@/settings";
import timeOptions from "@/utils/timeOptions";
import selectSearch from "@/components/base/selectSearch";
//修改引入打印扩展
import printJS from "print-js";
export default {
  components: {
    orderDetail,
    cardsData,
    deliveryRecord,
    orderCancellate,
    orderRefund,
    selectSearch
  },
  data() {
    return {
      fileUrl: SettingMer.https + "/store/import/delivery",
      pickerOptions: timeOptions,
      myHeaders: { "X-Token": getToken() },
      orderId: 0,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      roterPre: roterPre,
      select: "nickname",
      searchSelectList: [
        { label: "昵称", value: "nickname" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ],
      tableFrom: {
        order_sn: this.$route.query.order_sn ? this.$route.query.order_sn : "",
        group_order_sn: "",
        order_type: this.$route.query.order_type || "-1",
        keywords: "",
        is_behalf: "",
        store_name: "",
        delivery_id: "",
        status: this.$route.query.status || "",
        date: "",
        page: 1,
        limit: 20,
        type: this.$route.query.order_type || "1",
        username: "",
        filter_delivery: "",
        filter_product: "",
        pay_type: "",
        order_id: this.$route.query.id ? this.$route.query.id : "",
        activity_type: "",
        merchant_take_id: null,//自提点
      },
      activityList: [
        { value: 0, label: "普通购买" },
        { value: 1, label: "秒杀活动" },
        { value: 2, label: "预售活动" },
        { value: 3, label: "助力活动" },
        { value: 4, label: "拼团活动" }
      ],
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
      sendFormLoading: false,
      timeVal: [],
      fromList: {
        title: "选择时间",
        custom: true,
        fromTxt: [
          {
            text: "全部",
            val: ""
          },
          {
            text: "今天",
            val: "today"
          },
          {
            text: "昨天",
            val: "yesterday"
          },
          {
            text: "最近7天",
            val: "lately7"
          },
          {
            text: "最近30天",
            val: "lately30"
          },
          {
            text: "本月",
            val: "month"
          },
          {
            text: "本年",
            val: "year"
          }
        ]
      },
      ids: "",
      tableFromLog: {
        page: 1,
        limit: 10
      },
      tableDataLog: {
        data: [],
        total: 0
      },
      initStationList: [],//初始化自提点列表
      filterStationList: [], //过滤后的自提点列表
      merchantStationLoading: false,//自提点加载中
      LogLoading: false,
      dialogVisible: false,
      addressVisible: false,
      fileVisible: false,
      editVisible: false,
      sendVisible: false,
      pictureVisible: false,
      drawer: false,
      cardLists: [],
      orderDatalist: null,
      headeNum: [],
      editId: "",
      formValidate: {
        total_price: "",
        pay_postage: "",
        pay_price: "",
        integral_price: "",
        coupon_price: ""
      },
      deliveryList: [],
      expressTypeList: [], // 快递业务类型
      sendExpressList: [], //商家寄件快递数据
      sendTempsLst: [], //商家寄件电子面单数据
      deliveryPersonList: [],
      eleTempsLst: [],
      productList: [], //订单商品
      productNum: 0,
      storeList: [], //门店列表
      multipleSelection: [],
      selectType: 1,
      value1: ["0:00", "23:00"],
      shipment: {
        delivery_type: 1,
        delivery_name: "",
        cargo_weight: "",
        mark: "",
        remark: "",
        from_name: "",
        from_tel: "",
        to_name: "",
        to_phone: "",
        from_addr: "",
        temp_id: "",
        service_type: "",
        station_id: "",
        is_split: "0",
        split: [],
        pickup_start_time: "0:00",
        pickup_end_time: "23:00",
        day_type: 0
      },
      deliveryPrice: "",
      addressForm: {},
      original: {
        delivery_name: "",
        delivery_id: ""
      },
      isResend: false,
      chkName: "",
      checkedPage: [],
      checkedIds: [], // 订单当前页选中的数据
      noChecked: [], // 订单全选状态下当前页不选中的数据
      allCheck: false,
      isBatch: false,
      delivery_name: "",
      isDump: false,
      noLogistics: false,
      orderType: 0,
      activityType: 0,
      third_delivery: "",
      delivery_status: "",
      mer_dump_switch: false,
      rules: {
        delivery_type: [
          { required: true, message: "请选择发送货方式", trigger: "change" }
        ],
        station_id: [
          { required: true, message: "请选择发货点", trigger: "change" }
        ],
        delivery_name: [
          { required: true, message: "请选择快递公司", trigger: "change" }
        ],
        to_name: [
          { required: true, message: "请选择送货人姓名", trigger: "change" }
        ],
        delivery_id: [
          { required: true, message: "请输入快递单号", trigger: "blur" }
        ],
        cargo_weight: [
          { required: true, message: "请输入包裹重量", trigger: "blur" }
        ],
        to_phone: [
          { required: true, message: "请输入送货人手机号", trigger: "blur" },
          {
            pattern: /^1[3456789]\d{9}$/,
            message: "请输入正确的手机号",
            trigger: "blur"
          }
        ],
        temp_id: [
          { required: true, message: "请选择电子面单", trigger: "change" }
        ],
        from_name: [
          { required: true, message: "请输入寄件人姓名", trigger: "blur" }
        ],
        from_tel: [
          { required: true, message: "请输入寄件人电话", trigger: "blur" },
          {
            pattern: /^1(3|4|5|6|7|8|9)\d{9}$/,
            message: "请输入正确的联系方式",
            trigger: "blur"
          }
        ],
        from_addr: [
          { required: true, message: "请输入寄件人地址", trigger: "blur" }
        ],
        sender_name: [
          { required: true, message: "请输入寄件人姓名", trigger: "blur" }
        ],
        sender_phone: [
          { required: true, message: "请输入寄件人电话", trigger: "blur" },
          {
            pattern: /^1(3|4|5|6|7|8|9)\d{9}$/,
            message: "请输入正确的联系方式",
            trigger: "blur"
          }
        ],
        sender_address: [
          { required: true, message: "请输入寄件人地址", trigger: "blur" }
        ]
      },
      addressRules: {
        to_name: [
          { required: true, message: "请输入收货人姓名", trigger: "change" }
        ],
        to_phone: [
          { required: true, message: "请输入送货人手机号", trigger: "blur" },
          {
            pattern: /^1[3456789]\d{9}$/,
            message: "请输入正确的手机号",
            trigger: "blur"
          }
        ],
        from_addr: [
          { required: true, message: "请输入寄件人地址", trigger: "blur" }
        ]
      },
      cityTakeFail: [],
      merDeliveryType: {}, // 商城配送类型 0：商家配送 1：达达 2： uu
      isCityTake: false, // 订单是否为同城配送
      rowData: {}
    };
  },
  computed: {
    selectTypeOptions() {
      return [
        {
          value: 1,
          text: "发货",
          validate:
            !this.isBatch &&
            this.tableFrom.order_type != 2 &&
            this.orderType != 1
        },
        {
          value: 2,
          text: "商家配送",
          validate:
            this.tableFrom.order_type != 2 &&
            this.orderType != 1 &&
            this.merDeliveryType == 0 &&
            this.isCityTake
        },
        {
          value: 3,
          text: "无需配送",
          validate: true
        },
        {
          value: 4,
          text: "电子面单",
          validate: this.isDump && this.mer_dump_switch && this.isBatch
        }
      ].filter(item => item.validate);
    }
  },
  watch: {
    sendVisible(v) {
      if (!v) return;
      if (!this.sendExpressList.length) {
        this.getExpressType();
      }
      if (!this.deliveryList.length) {
        this.getExpressLst();
      }
      if (!this.deliveryPersonList.length) {
        this.getDeliveryPerson();
      }
      if (!this.storeList.length) {
        this.getStoreList();
      }
      if (!this.merDeliveryType.basicSettings) {
        this.getdeliverySetData();
      }
    }
  },
  mounted() {
    if (this.$route.query.hasOwnProperty("order_sn")) {
      this.tableFrom.order_sn = this.$route.query.order_sn;
    } else {
      this.tableFrom.order_sn = "";
    }
    this.isOpenDump();
    this.getList(1);
    this.getThirdDelivery();
    this.getMerchantStationList("", true);
  },
  methods: {
    handleStationChange() {
      this.$nextTick(() => {
        this.filterStationList = this.initStationList;
      });
    },
    /**获取自提点列表 */
    async getMerchantStationList(query = "", isInit = false) {
      if (this.merchantStationLoading) return;
      this.merchantStationLoading = true;
      try {
        const res = await deliveryStoreLst({ station_name: query, page: 1, limit: 10 });
        if (isInit) {
          this.initStationList = res.data.list;
        } 
        this.filterStationList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.merchantStationLoading = false;
      }
    },
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.$refs.selectSearch.resetParmas();
    },
    limitCount(row) {
      if (row.stock > row.product_num) row.stock = row.product_num;
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
    },
    // 获取第三方配送方式
    getThirdDelivery() {
      getConfigApi()
        .then(res => {
          this.mer_dump_switch = res.data.mer_dump_switch == 1 ? true : false;
          this.delivery_status = res.data.delivery_status;
          this.third_delivery =
            res.data.delivery_type == 1 ? "(达达配送)" : "(UU配送)";
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 同步同城配送订单
    syncAgain() {
      orderSyncApi({ order_ids: this.cityTakeFail })
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 分单发货选择商品
    handleSelectionChange(val) {
      this.multipleSelection = val;
      const data = [];
      this.multipleSelection.map(item => {
        data.push({ id: item.order_product_id, num: item.product_num });
      });
      this.ids = data;
    },
    // 获取订单状态
    orderColorFilter(status) {
      const statusMap = {
        "0": "#0FC6C2",
        "1": "#FF7D00",
        "2": "#3491FA",
        "3": "#666666",
        "-1": "#F56464",
        "9": "#F56464",
        "10": "#FF7D00",
        "11": "#F56464",
        "20": "#4073FA"
      };
      return statusMap[status];
    },
    // 是否开启电子面单
    isOpenDump() {
      serveInfoApi()
        .then(res => {
          this.isDump = res.data.crmeb_serve_dump == 1 ? true : false;
          if (res.data.crmeb_serve_dump == 1) this.getEleTempData();
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取快递公司列表
    getExpressLst() {
      expressLst()
        .then(res => {
          this.deliveryList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取快递业务类型
    getExpressType() {
      expressTypeApi()
        .then(res => {
          this.sendExpressList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 根据选择的快递公司显示对应的业务类型和电子面单数据
    getSendData(value) {
      this.shipment.service_type = "";
      this.shipment.temp_id = "";
      const selectedItem = this.sendExpressList.find(
        item => item.code === value
      );
      console.log(selectedItem); // 完整的选项数据
      this.expressTypeList = selectedItem.types;
      this.sendTempsLst = selectedItem.list;
    },
    // 立即计算
    getDeliveryPrice(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          let data = {
            kuaidicom: this.shipment.delivery_name,
            service_type: this.shipment.service_type,
            send_address: this.shipment.from_addr
          };
          calculateCost(this.orderId, data)
            .then(res => {
              this.deliveryPrice = res.data.price;
            })
            .catch(res => {
              this.$message.error(res.message);
            });
        } else {
          return;
        }
      });
    },
    // 获取配送员列表
    getDeliveryPerson() {
      deliveryPersonSelect()
        .then(res => {
          this.deliveryPersonList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    handleChange(value) {
      const selectedRow = this.deliveryPersonList.find(
        option => option.service_id === value
      );
      this.shipment.to_phone = selectedRow.phone;
    },
    // 获取电子面单列表
    getTempsLst(name) {
      exprTempsLst({ com: name }).then(res => {
        this.eleTempsLst = res.data.data;
      });
    },
    // 获取电子面单默认数据
    getEleTempData() {
      getEleTempData()
        .then(res => {
          const data = res.data;
          const delivery_type = this.shipment.delivery_type;
          this.shipment.from_name = data.mer_from_name;
          this.shipment.from_addr = data.mer_from_addr;
          this.shipment.from_tel = data.mer_from_tel;
          this.shipment.delivery_type = delivery_type;
          this.shipment.delivery_name = data.mer_from_com;
          this.shipment.temp_id = "";
          if (data.mer_from_com != "") {
            this.getTempsLst(data.mer_from_com);
          }
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    //获取门店列表
    getStoreList() {
      getDeliveryStoreLst()
        .then(res => {
          this.storeList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    changeSend(e) {
      this.$refs["shipment"].clearValidate();

      if (e == 2) {
        // 如果是商家配送则将shipment.delivery_type字段改为2
        this.shipment.delivery_type = 2;
      }
      if (e == 3) {
        //如果是无需配送，就重置拆单信息
        this.shipment.is_split = "0";
        this.shipment.delivery_type = 3;
        delete this.shipment.split;
      } else if (e == 4 && this.isBatch) {
        this.shipment.delivery_type = e;
      } else {
        this.shipment.delivery_type = e == 2 ? 2 : 1;
      }
    },
    getPicture(list) {
      if (this.shipment.temp_id) {
        list.forEach((o, i) => {
          if (o["temp_id"] == this.shipment.temp_id) {
            this.pictureVisible = true;
            this.pictureUrl = o["pic"];
          }
        });
      } else {
        this.$message.error("选择电子面单后才可以预览");
      }
    },
    // 批量发送货
    batchSend() {
      if (!this.allCheck && this.checkedIds.length == 0) {
        return this.$message.warning("请先选择订单");
      } else {
        this.isBatch = true;
        this.sendVisible = true;
        this.selectType = 2;
        this.shipment.delivery_type = 2;
        this.shipment.select_type = this.allCheck ? "all" : "select";
        if (this.allCheck) {
          this.shipment.where = this.tableFrom;
        } else {
          this.shipment.order_id = this.checkedIds;
        }
      }
    },
    // 批量打印配货单
    printNote() {
      if (!this.allCheck && this.checkedIds.length == 0) {
        return this.$message.warning("请先选择订单");
      } else {
        this.isBatch = true;
        localStorage.setItem("printIds", this.checkedIds);
        let pathInfo = this.$router.resolve({
          name: "OrderPrint",
          query: {
            id: this.checkedIds.toString()
          }
        });
        window.open(pathInfo.href, "_blank");
      }
    },
    handleClose() {
      this.sendVisible = false;
      this.rowData = {};
      // this.$refs['shipment'].resetFields()
    },
    onHandle(name) {
      this.chkName = this.chkName === name ? "" : name;
      this.changeType(!(this.chkName === ""));
    },
    changeType(v) {
      if (v) {
        if (!this.chkName) {
          this.chkName = "dan";
        }
      } else {
        this.chkName = "";
        this.allCheck = false;
      }
      const index = this.checkedPage.indexOf(this.tableFrom.page);
      if (this.chkName === "dan") {
        this.checkedPage.push(this.tableFrom.page);
      } else if (index > -1) {
        this.checkedPage.splice(index, 1);
      }
      this.syncCheckedId();
    },
    syncCheckedId() {
      const ids = this.tableData.data.map(v => v.order_id);
      if (this.chkName === "duo") {
        this.checkedIds = [];
        this.allCheck = true;
      } else if (this.chkName === "dan") {
        this.allCheck = false;
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index === -1) {
            this.checkedIds.push(id);
          }
        });
      } else {
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index > -1) {
            this.checkedIds.splice(index, 1);
          }
        });
      }
    },
    // 分开选择
    changeOne(v, row) {
      if (v) {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.order_id);
          if (index > -1) this.noChecked.splice(index, 1);
        } else {
          const index = this.checkedIds.indexOf(row.order_id);
          if (index === -1) this.checkedIds.push(row.order_id);
        }
      } else {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.order_id);
          if (index === -1) this.noChecked.push(row.order_id);
        } else {
          const index = this.checkedIds.indexOf(row.order_id);
          if (index > -1) this.checkedIds.splice(index, 1);
        }
      }
    },
    // 头部
    getHeaderList() {
      orderHeadListApi(this.tableFrom)
        .then(res => {
          this.headeNum = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 订单筛选
    orderFilter(item) {
      let status = false;
      item.orderProduct.forEach(el => {
        if (el.refund_num < el.product_num) {
          status = true;
        }
      });
      return status;
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
    // 订单退款
    onOrderRefund(id, type) {
      this.$refs.orderRefund.getOrderDetails(id, type);
    },
    // 退款回调
    refundSuccess() {
      setTimeout(() => {
        this.drawer = false;
        this.getList();
      }, 500);
    },
    // 表格某一行添加特定的样式
    addTdClass(val) {
      if (val.row.status > 0 && val.row.paid == 1) {
        for (let i = 0; i < val.row.orderProduct.length; i++) {
          if (
            val.row.orderProduct[i].refund_num >= 0 &&
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
    // 详情
    onOrderDetails(id) {
      this.orderId = id;
      this.$refs.orderDetail.getInfo(id);
      this.drawer = true;
    },
    async exports(value) {
      let excelData = JSON.parse(JSON.stringify(this.tableFrom)),
        data = [];
      excelData.page = 1;
      excelData.ids = this.checkedIds.toString();
      let pageCount = 1;
      let lebData = {};
      for (let i = 0; i < pageCount; i++) {
        lebData =
          value == 1
            ? await this.downOrderData(excelData)
            : await this.downInvoiceData(excelData);
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
    /**订单 */
    downOrderData(excelData) {
      return new Promise((resolve, reject) => {
        exportOrderApi(excelData)
          .then(res => {
            return resolve(res.data);
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      });
    },
    /**发货单 */
    downInvoiceData(excelData) {
      return new Promise((resolve, reject) => {
        exportInvoiceApi(excelData)
          .then(res => {
            return resolve(res.data);
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      });
    },
    // 批量发货记录
    getDeliveryList() {
      this.$refs.deliveryList.getList();
    },
    // 上传成功
    handleSuccess(response) {
      if (response.status === 200) {
        this.$message.success(response.message);
      } else {
        this.$message.error(response.message);
      }
    },
    // 下载物流公司对照表
    downloadLogistics() {
      window.open(
        SettingMer.https + `/excel/download/express?token=` + getToken()
      );
    },
    // 订单核销
    orderCancellation(code) {
      const that = this;
      that.$refs.orderCancellate.dialogVisible = true;
      if (code) {
        that.$refs.orderCancellate.productDetails(code);
        that.$refs.orderCancellate.isColum = true;
      } else {
        that.$refs.orderCancellate.isColum = false;
        that.$refs.orderCancellate.resetData();
      }
    },
    // 去核销
    handleCancellation(code) {
      this.$confirm("确定核销此订单?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      })
        .then(() => {
          orderCancellationApi(code)
            .then(res => {
              this.$message.success(res.message);
              this.getList("");
            })
            .catch(res => {
              this.$message.error(res.message);
              this.LogLoading = false;
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消核销"
          });
        });
    },
    // 订单记录
    onOrderLog(id) {
      this.dialogVisible = true;
      this.LogLoading = true;
      orderLogApi(id, this.tableFromLog)
        .then(res => {
          this.tableDataLog.data = res.data.list;
          this.tableDataLog.total = res.data.count;
          this.LogLoading = false;
        })
        .catch(res => {
          this.$message.error(res.message);
          this.LogLoading = false;
        });
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page;
      this.getList("");
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val;
      this.getList("");
    },
    // 打印订单
    printOrder(id) {
      orderPrintApi(id)
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 确认支付
    onConfirmPayment(id) {
      this.$modalSure("修改为已支付吗")
        .then(() => {
          offlinePay(id)
            .then(res => {
              this.$message.success(res.message);
              this.getList(1);
              this.headerList();
            })
            .catch(err => {
              this.$message.error(err.message);
            });
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    // 订单删除
    handleDelete(row, idx) {
      if (row.is_del === 1) {
        this.$modalSure("删除该订单吗").then(() => {
          orderDeleteApi(row.order_id)
            .then(({ message }) => {
              this.$message.success(message);
              this.getList(1);
              this.headerList();
            })
            .catch(({ message }) => {
              this.$message.error(message);
            });
        });
      } else {
        this.$confirm(
          "您选择的的订单存在用户未删除的订单，无法删除用户未删除的订单！",
          "提示",
          {
            confirmButtonText: "确定",
            type: "error"
          }
        );
      }
    },
    // 备注
    onOrderMark(id) {
      this.$modalForm(orderRemarkApi(id)).then(() => this.getList(""));
    },
    // 选择时间
    selectChange(tab) {
      this.timeVal = [];
      this.tableFrom.date = tab;
      // this.getCardList();
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.getList(1);
    },
    // 取件时间
    onchangeTime1(e) {
      this.value1 = e;
      this.shipment.pickup_start_time = e[0];
      this.shipment.pickup_end_time = e[1];
    },
    // 编辑
    edit(id) {
      this.editId = id;
      this.editVisible = true;
      orderDetailApi(id)
        .then(res => {
          this.formValidate = {
            pay_postage: res.data.pay_postage,
            total_price: res.data.total_price,
            integral_price: res.data.integral_price,
            coupon_price: (
              Number(res.data.coupon_price) + Number(res.data.svip_discount)
            ).toFixed(2),
            pay_price: (
              Number(res.data.total_price) +
              Number(res.data.pay_postage) -
              Number(res.data.coupon_price) -
              Number(res.data.svip_discount)
            ).toFixed(2)
          };
          this.loading = false;
        })
        .catch(({ message }) => {
          this.loading = false;
          this.$message.error(message);
        });
    },
    editConfirm() {
      orderUpdateApi(this.editId, this.formValidate)
        .then(res => {
          this.editVisible = false;
          this.$message.success(res.message);
          this.getList("");
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    changePrice() {
      this.formValidate.pay_price = (
        this.formValidate.total_price +
        this.formValidate.pay_postage -
        this.formValidate.coupon_price
      ).toFixed(2);
    },
    // 发货
    send(row, id) {
      this.rowData = row;
      this.isBatch = false;
      this.sendVisible = true;
      this.isResend = false;
      this.orderId = id;
      this.activityType = row.activity_type;
      this.orderType = row.is_virtual;
      this.selectType = row.is_virtual == 1 ? 3 : 1;
      this.shipment.delivery_type = this.orderType === 1 ? 3 : 1;
      this.isCityTake = row.order_type == 2;
      row.orderProduct.forEach(item => {
        item.stock_num = item.product_num;
      });
      this.productList = row.orderProduct;
      this.productNum =
        (row.orderProduct &&
          row.orderProduct[0] &&
          row.orderProduct[0]["product_num"]) ||
        0;
      delete this.shipment.order_id;
      if (this.tableFrom.order_type == 2) this.shipment.delivery_type = 3;
    },
    sendReset() {
      this.shipment = {
        delivery_type: 1,
        delivery_name: "",
        delivery_id: "",
        from_name: "",
        from_addr: "",
        from_tel: "",
        temp_id: ""
      };
    },
    // 复打
    reDriving() {
      orderReDriving(this.orderId)
        .then(res => {
          if (res.data.label) this.printImg(res.data.label);
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 修改发货信息
    reSend(id) {
      this.isBatch = false;
      this.sendVisible = true;
      this.orderId = id;
      this.isResend = true;
      delete this.shipment.order_id;
      orderDetailApi(id)
        .then(res => {
          const data = res.data;
          this.shipment.delivery_type =
            !this.isDump && !this.mer_dump_switch && data.delivery_type == 4
              ? 1
              : Number(data.delivery_type);
          this.noLogistics = data.delivery_type;
          this.loading = false;
          this.original = {
            delivery_name: data.delivery_name,
            delivery_id: data.delivery_id
          };
          this.loading = false;
        })
        .catch(({ message }) => {
          this.loading = false;
          this.$message.error(message);
        });
    },
    // 修改收货地址
    changeAddress() {
      this.addressVisible = true;
    },
    // 提交收货地址
    submitAddress(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
        } else {
          return;
        }
      });
    },
    submitForm(name) {
      if (this.isBatch) {
        const values = this.selectTypeOptions.map(item => item.value);
        if (!values.includes(this.selectType)) {
          return this.$message.warning("请选择类型!");
        }
      }
      if (this.shipment.delivery_type == 2) {
        this.shipment.delivery_name = this.shipment.to_name;
        this.shipment.delivery_id = this.shipment.to_phone;
      }
      if (
        this.shipment.is_split != "0" &&
        this.shipment.is_split &&
        this.orderType != 2
      ) {
        if (!this.multipleSelection.length) {
          return this.$message.warning("请选择拆单商品!");
        }
        const data = [];
        this.multipleSelection.map(item => {
          data.push({ id: item.order_product_id, num: item.product_num_input });
        });
        this.ids = data;
        this.shipment.split = this.ids;
      }
      this.$refs[name].validate(async valid => {
        if (valid) {
          if (this.sendFormLoading) return;
          this.sendFormLoading = true;
          delete this.shipment.to_name;
          delete this.shipment.to_phone;
          const task = this.isBatch ? batchDeliveryApi(this.shipment) : orderDeliveryApi(this.orderId, this.shipment);
          try {
            const res = await task;
            this.sendVisible = false;
            this.$message.success(res.message);
            this.getList("");
            this.headerList();
            if (this.drawer) this.$refs.orderDetail.getInfo(this.orderId);
            this.$refs[name].clearValidate()
            this.sendReset();
            if (this.isBatch && res.data && res.data.label) {
              this.printImg(res.data.label);
            }
          } catch (error) {
            this.$message.error(error.message);
          } finally {
            this.sendFormLoading = false;
          }
        } else {
          return;
        }
      });
    },
    //修改增加打印方法
    printImg(url) {
      printJS({
        // printable: 'http://api.kuaidi100.com/label/getImage/20230518/9CBFE5F980044698A54CF19EB1585125',
        printable: url,
        type: "image",
        documentTitle: "快递信息",
        style: `img{
          width: 100%;
          height: 476px;
        }`
      });
    },
    searchList(data) {
      this.tableFrom = { ...this.tableFrom, ...data };
      this.getList(1);
      // this.getCardList();
    },
    getSearchList() {
      this.$refs.selectSearch.changeSearch();
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num || this.tableFrom.page;
      orderListApi(this.tableFrom)
        .then(res => {
          res.data.list.forEach(item => {
            item.orderProduct.forEach(cell => {
              cell.product_num_input = cell.refund_num;
            });
          });
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.cityTakeFail = res.data.cityTakeFail;
          // this.getCardList();
          this.headerList();
          this.getHeaderList();
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
    },
    // 获取配送设置数据
    getdeliverySetData() {
      deliverySetApi()
        .then(res => {
          this.merDeliveryType = res.data.basicSettings.mer_delivery_type;
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    }
  }
};
</script>

<style lang="scss" scoped>
.pictures {
  max-width: 100%;
  display: block;
  margin: 0 auto;
}
.area-desc {
  margin: 0;
  color: #999;
  font-size: 12px;
}
.spBlock {
  cursor: pointer;
  display: block;
  padding: 5px 0;
}
.check {
  color: #00a2d4;
}
.el-icon-arrow-down {
  font-size: 12px;
}
.tabBox_tit {
  width: 53%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
::v-deep .row-bg {
  .cell {
    color: red !important;
  }
}
::v-deep .table-line th.is-leaf {
  line-height: 20px;
}
.headTab {
  position: relative;
  .headBtn {
    position: absolute;
    right: 0;
    top: -6px;
  }
}
.dropdown {
  padding: 0 10px;
  border: 1px solid var(--prev-color-primary);
  margin-right: 10px;
  line-height: 28px;
  border-radius: 4px;
}
.statusBox {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 4px;
  border: 1px solid #cccccc;
}
.red-text {
  color: #ed4014;
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
  margin-top: 10px;
}
.fs-12 {
  font-size: 12px;
}

.mb-10 {
  margin-bottom: 10px;
}

.delivery-info {
  background-color: #fdf6ec;
  border-radius: 5px;
  color: #e6a23c;
  font-size: 14px;
  padding: 20px 20px 15px;
  margin-bottom: 10px;

  .info-item {
    .info-item-label {
      width: 88px;
      text-align: right;
      margin-right: 12px;
    }
  }
}
</style>

<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1000px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div v-loading="loading">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title" v-if="orderDetailList.is_virtual !== 4">
                {{orderDetailList.order_type == 2 ? "同城配送订单" : orderDetailList.order_type == 0 ? "普通订单" : "核销订单" }}
              </div>
              <div class="title" v-else>
                预约服务订单-
                {{ orderDetailList.order_type == 0 ? "上门服务" : "到店服务" }}
              </div>
              <div>
                <span class="mr20"
                  >订单编号：{{ orderDetailList.order_sn }}</span
                >
              </div>
            </div>
            <div v-if="!disabled">
              <!-- 预约商品操作按钮 -->
              <template
                v-if="
                  orderDetailList.is_virtual === 4 && orderDetailList.paid !== 0
                "
              >
                <el-button
                  v-if="orderDetailList.status == 0"
                  type="primary"
                  size="small"
                  @click="dispatchFn(1)"
                  >派单</el-button
                >
                <el-button
                  v-if="orderDetailList.status == 1"
                  type="primary"
                  size="small"
                  @click="dispatchFn(2)"
                  >改派</el-button
                >
                <el-button
                  v-if="
                    orderDetailList.status == 0 || orderDetailList.status == 1
                  "
                  type="primary"
                  size="small"
                  @click="dispatchFn(3)"
                  >改约</el-button
                >

                <el-button
                  v-if="
                    orderDetailList.status < 2 && orderDetailList.status != -1
                  "
                  type="primary"
                  size="small"
                  @click="dispatchFn(4)"
                  >核销</el-button
                >
              </template>
              <!--同城配送-->
              <template v-if="
                orderDetailList.order_type === 2 && orderDetailList.paid !== 0
              ">
                <el-button
                  v-if="orderDetailList.status == 0"
                  type="primary"
                  size="small"
                  @click="cityDispatch(1)"
                  >派单</el-button
                >
                <el-button
                  v-if="orderDetailList.status == 1"
                  type="primary"
                  size="small"
                  @click="cityDispatch(2)"
                  >改派</el-button
                >
                <el-button
                  v-if="orderDetailList.status <= 1"
                  type="primary"
                  size="small"
                  @click="deliveryConfirm"
                  >确认送达</el-button
                >
              </template>
              <el-button
                v-if="
                  orderDetailList.is_virtual !== 4 &&
                  orderDetailList.order_type != 0 &&
                  orderDetailList.order_type != 2 &&
                  orderDetailList.paid == 1
                "
                type="primary"
                size="small"
                @click="orderCancellation"
                >订单核销</el-button
              >
              <el-button
                v-if="
                  orderDetailList.order_type == 0 &&
                  orderDetailList.status === 0 &&
                  orderDetailList.paid === 1 &&
                  orderDetailList.is_virtual !== 4
                "
                type="primary"
                size="small"
                @click="send"
                >发送货</el-button
              >
              <el-button
                v-if="orderDetailList.paid == 1"
                type="success"
                size="small"
                @click="printOrder"
                >小票打印</el-button
              >
              <el-dropdown @command="handleCommand" class="ml10">
                <el-button icon="el-icon-more" size="small"></el-button>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="mark">订单备注</el-dropdown-item>
                  <el-dropdown-item
                    v-if="
                      orderDetailList.order_type == 0 &&
                        orderDetailList.status === 0 &&
                        orderDetailList.paid === 1 &&
                        orderDetailList.is_virtual !== 4
                    "
                    command="address"
                    >修改收货地址</el-dropdown-item
                  >
                  <el-dropdown-item
                    v-if="
                      orderDetailList.order_type == 0 &&
                        orderDetailList.status === 1 &&
                        orderDetailList.paid === 1 &&
                        orderDetailList.is_virtual !== 4
                    "
                    command="modify"
                    >修改发货信息</el-dropdown-item
                  >
                  <el-dropdown-item
                    v-if="
                      orderDetailList.status != -1 &&
                        orderDetailList.paid == 1 &&
                        orderDetailList.status < 9
                    "
                    command="refund"
                    >主动退款</el-dropdown-item
                  >
                  <el-dropdown-item
                    v-if="
                      orderDetailList.status == 1 &&
                        orderDetailList.paid == 1 &&
                        orderDetailList.delivery_type == 4
                    "
                    command="redriving"
                    >复打</el-dropdown-item
                  >
                  <el-dropdown-item command="print"
                    >打印发货单</el-dropdown-item
                  >
                </el-dropdown-menu>
              </el-dropdown>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">订单状态</div>

              <div v-if="!orderDetailList.pay_time" class="value1">
                待付款<span
                  v-if="orderDetailList.is_del > 0"
                  style="color:rgb(237,64,20);font-size:12px;"
                  >（用户已删除）</span
                >
              </div>
              <template v-if="orderDetailList.is_virtual !== 4">
                <div
                  v-if="
                    orderDetailList.order_type === 0 && orderDetailList.pay_time
                  "
                  class="value1"
                >
                  <span
                    :style="{ color: orderColorFilter(orderDetailList.status) }"
                    >{{ orderDetailList.status | orderStatusFilter }}</span
                  >
                </div>
                <div
                  v-if="
                    orderDetailList.order_type === 1 && orderDetailList.pay_time
                  "
                  class="value1"
                >
                  <span
                    :style="{ color: orderColorFilter(orderDetailList.status) }"
                    >{{
                      orderDetailList.status | cancelOrderStatusFilter
                    }}</span
                  >
                </div>
                <div
                  v-if="
                    orderDetailList.order_type === 2 && orderDetailList.pay_time
                  "
                  class="value1"
                >
                  <span
                    :style="{ color: orderColorFilter(orderDetailList.status) }"
                    >{{
                      orderDetailList.status | deliveryOrderStatusFilter
                    }}</span
                  >
                </div>
              </template>
              <template
                v-if="
                  orderDetailList.is_virtual === 4 && orderDetailList.pay_time
                "
              >
                <span
                  v-if="orderDetailList.order_type == 0"
                  :style="{ color: orderColorFilter(orderDetailList.status) }"
                >
                  {{ orderDetailList.status | reservationOrderStatusFilter }}
                </span>
                <span
                  v-if="orderDetailList.order_type == 1"
                  :style="{ color: orderColorFilter(orderDetailList.status) }"
                >
                  {{ orderDetailList | reservationOrderStatusFilter1 }}
                </span>
              </template>

              <!-- <div v-if="!orderDetailList.pay_time" class="value1">待付款<span v-if="orderDetailList.is_del>0" style="color:rgb(237,64,20);font-size:12px;">（用户已删除）</span></div>
                <div v-if="orderDetailList.order_type === 0 && orderDetailList.pay_time" class="value1">
                  <span>{{ orderDetailList.status | orderStatusFilter }}<span v-if="orderDetailList.is_del>0" style="color:rgb(237,64,20);font-size:12px;">（用户已删除）</span></span>
                </div>
                <div v-if="orderDetailList.order_type === 1 && orderDetailList.pay_time" class="value1">
                  <span>{{ orderDetailList.status | cancelOrderStatusFilter }}<span v-if="orderDetailList.is_del>0" style="color:rgb(237,64,20);font-size:12px;">（用户已删除）</span></span>
                </div> -->
            </li>
            <li class="item">
              <div class="title">实际支付</div>
              <div>
                ¥{{ orderDetailList.pay_price }}
                <span
                  v-if="
                    orderDetailList.finalOrder &&
                      orderDetailList.finalOrder.pay_price &&
                      orderDetailList.finalOrder.paid == 1
                  "
                  >尾款¥{{ orderDetailList.finalOrder.pay_price }}</span
                >
              </div>
            </li>
            <li class="item">
              <div class="title">支付方式</div>
              <div v-if="orderDetailList.paid == 1">
                {{ orderDetailList.pay_type | orderPayType }}
              </div>
              <div v-else>未支付</div>
            </li>
            <li
              class="item mr20"
              v-if="
                orderDetailList.is_virtual === 4 &&
                  (orderDetailList.status == 0 || orderDetailList.status == 1)
              "
            >
              <div class="title">预约时间</div>
              <div>
                {{
                  orderDetailList.orderProduct[0].reservation_date
                    ? orderDetailList.orderProduct[0].reservation_date
                    : orderDetailList.orderProduct[0].reservation_time_part
                }}
                {{ orderDetailList.orderProduct[0].reservation_time_part }}

                <!-- 修改预约时间 -->
                <el-popover
                  placement="left-end"
                  width="350"
                  trigger="manual"
                  ref="popoverRef"
                  v-model="visiblePopover"
                >
                  <div>修改预约时间</div>
                  <div class="mt20 mb20">
                    <el-select
                      v-model="reservationForm.reservation_date"
                      size="small"
                      placeholder="请选择"
                      style="width:150px;font-size: 14px;"
                    >
                      <el-option
                        v-for="item in month"
                        :key="item.date"
                        :label="item.date"
                        :value="item.date"
                      ></el-option>
                    </el-select>

                    <TimePicker
                      type="timerange"
                      format="HH:mm"
                      placeholder="选择时间"
                      v-model="reservationForm.reservation_time_part"
                      placement="bottom-end"
                      style="width: 150px;"
                      size="default"
                      class="mr10"
                    ></TimePicker>
                  </div>
                  <div class="flex-end">
                    <el-button size="small" @click="visiblePopover = false"
                      >取消</el-button
                    >
                    <el-button type="primary" size="small" @click="reschedule"
                      >确定</el-button
                    >
                  </div>
                  <span
                    slot="reference"
                    class="iconfont iconbianji1"
                    @click="openPopover"
                    v-if="
                      orderDetailList.status == 0 || orderDetailList.status == 1
                    "
                  />
                </el-popover>
              </div>
            </li>
            <li class="item">
              <div class="title">
                {{ orderDetailList.pay_time ? "支付时间" : "下单时间" }}
              </div>
              <div>{{ orderDetailList.create_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="订单信息" name="detail">
            <div v-if="orderDetailList.user" class="section">
              <div class="title">用户信息</div>
              <ul class="list">
                <li class="item">
                  <div>用户昵称：</div>
                  <div class="value">
                    {{
                      orderDetailList.user.real_name
                        ? orderDetailList.user.real_name
                        : orderDetailList.user.nickname
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>用户ID：</div>
                  <div class="value">
                    {{
                      orderDetailList.user.uid ? orderDetailList.user.uid : "-"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>绑定电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.user.phone
                        ? orderDetailList.user.phone
                        : "-"
                    }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDetailList.is_virtual !== 4">
              <div class="title">收货信息</div>
              <ul class="list">
                <li class="item item100">
                  <div>收货人：</div>
                  <div class="value">
                    {{
                      orderDetailList.real_name
                        ? orderDetailList.real_name
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>收货电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.user_phone
                        ? orderDetailList.user_phone
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>收货地址：</div>
                  <div class="value">
                    {{
                      orderDetailList.user_address
                        ? orderDetailList.user_address
                        : "-"
                    }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="(orderDetailList.order_type == 2 || orderDetailList.order_type == 1)  && orderDetailList.is_virtual !== 4 && orderDetailList.take">
              <div class="title">提货点信息</div>
              <ul class="list">
                <li class="item item100">
                  <div>提货点名称：</div>
                  <div class="value">
                    {{
                      orderDetailList.take.station_name
                        ? orderDetailList.take.station_name
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>联系人姓名：</div>
                  <div class="value">
                    {{
                      orderDetailList.take.contact_name
                        ? orderDetailList.take.contact_name
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>提货点电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.take.phone
                        ? orderDetailList.take.phone
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>提货点地址：</div>
                  <div class="value">
                    {{
                      orderDetailList.take.station_address
                        ? orderDetailList.take.station_address
                        : "-"
                    }}
                  </div>
                </li>
              </ul>
            </div>

            <!-- 预约服务订单的预约信息 -->
            <div class="section" v-if="orderDetailList.is_virtual === 4">
              <div class="title">预约信息</div>
              <ul class="list">
                <li class="item">
                  <div>联系人：</div>
                  <div class="value">
                    {{
                      orderDetailList.real_name
                        ? orderDetailList.real_name
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>联系电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.user_phone
                        ? orderDetailList.user_phone
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>预约时间：</div>
                  <div class="value">
                    {{
                      orderDetailList.orderProduct[0].reservation_date
                        ? orderDetailList.orderProduct[0].reservation_date
                        : orderDetailList.orderProduct[0].reservation_time_part
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>上门地址：</div>
                  <div class="value">
                    {{
                      orderDetailList.user_address
                        ? orderDetailList.user_address
                        : "--"
                    }}
                  </div>
                </li>
              </ul>
            </div>

            <div class="section">
              <div class="title">订单信息</div>
              <ul class="list">
                <li class="item">
                  <div>创建时间：</div>
                  <div class="value">
                    {{
                      orderDetailList.create_time
                        ? orderDetailList.create_time
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>商品总数：</div>
                  <div class="value">
                    {{
                      orderDetailList.total_num
                        ? orderDetailList.total_num
                        : "-"
                    }}
                  </div>
                </li>
                <li v-if="orderDetailList.pay_time" class="item">
                  <div>实际支付：</div>
                  <div class="value">
                    {{ orderDetailList.pay_price }}
                    <span
                      v-if="
                        orderDetailList.finalOrder &&
                          orderDetailList.finalOrder.pay_price &&
                          orderDetailList.finalOrder.paid == 1
                      "
                      >尾款¥{{ orderDetailList.finalOrder.pay_price }}</span
                    >
                  </div>
                </li>
                <li class="item">
                  <div>优惠券金额：</div>
                  <div class="value">
                    {{
                      orderDetailList.coupon_price
                        ? orderDetailList.coupon_price
                        : "-"
                    }}
                  </div>
                </li>
                <li v-if="orderDetailList.integral" class="item">
                  <div>积分抵扣：</div>
                  <div
                    v-if="
                      orderDetailList.integral && orderDetailList.integral != 0
                    "
                    class="value"
                  >
                    使用了{{ orderDetailList.integral }}个积分，抵扣了{{
                      orderDetailList.integral_price
                    }}元
                  </div>
                </li>
                <li class="item">
                  <div>商品总价：</div>
                  <div class="value">
                    {{
                      orderDetailList.total_price
                        ? orderDetailList.total_price
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item" v-if="orderDetailList.svip_discount">
                  <div>会员商品优惠：</div>
                  <div class="value">{{ orderDetailList.svip_discount }}</div>
                </li>
                <li class="item">
                  <div>支付运费：</div>
                  <div class="value">{{ orderDetailList.pay_postage }}</div>
                </li>
                <li v-if="orderDetailList.spread" class="item">
                  <div>推广人：</div>
                  <div class="value">{{ orderDetailList.spread.nickname }}</div>
                </li>
                <li v-if="orderDetailList.TopSpread" class="item">
                  <div>上级推广人：</div>
                  <div class="value">
                    {{ orderDetailList.TopSpread.nickname }}
                  </div>
                </li>
                <li v-if="!orderDetailList.activity_type" class="item">
                  <div>一级佣金：</div>
                  <div class="value">
                    {{
                      parseFloat(orderDetailList.extension_one) +
                        parseFloat(orderDetailList.refund_extension_one)
                    }}
                    <em
                      v-if="orderDetailList.refund_extension_one > 0"
                      style="color: red;font-style: normal;"
                      >(-{{ orderDetailList.refund_extension_one }})</em
                    >
                  </div>
                </li>
                <li v-if="!orderDetailList.activity_type" class="item">
                  <div>二级佣金：</div>
                  <div class="value">
                    {{
                      parseFloat(orderDetailList.extension_two) +
                        parseFloat(orderDetailList.refund_extension_two)
                    }}
                    <em
                      v-if="orderDetailList.refund_extension_two > 0"
                      style="color: red;font-style: normal;"
                      >(-{{ orderDetailList.refund_extension_two }})</em
                    >
                  </div>
                </li>
                <li class="item">
                  <div>赠送积分：</div>
                  <div class="value">
                    {{ orderDetailList.give_integral || "-" }}
                  </div>
                </li>
                <li class="item" v-if="orderDetailList.is_virtual !== 4">
                  <div>发货方式：</div>
                  <div v-if="orderDetailList.order_type == 1" class="value">
                    核销
                  </div>
                  <div v-else class="value">
                    {{ orderDetailList.delivery_type | sendWay }}
                  </div>
                </li>
                <li class="item">
                  <div>商品类型：</div>
                  <div class="value" v-if="orderDetailList.is_virtual !== 4">
                    {{
                      orderDetailList.is_virtual == 1
                        ? "虚拟商品"
                        : orderDetailList.is_virtual == 2
                        ? "卡密商品"
                        : "普通商品"
                    }}
                  </div>
                  <div v-else>预约商品</div>
                </li>
                <li class="item">
                  <div>活动类型：</div>
                  <div class="value">
                    {{
                      orderDetailList.activity_type == 1
                        ? "秒杀"
                        : orderDetailList.activity_type == 2
                        ? "预售"
                        : orderDetailList.activity_type == 3
                        ? "助力"
                        : orderDetailList.activity_type == 4
                        ? "拼团"
                        : "普通"
                    }}
                  </div>
                </li>
              </ul>
            </div>
            <div v-if="orderDetailList.order_type == 2" class="section">
              <div class="title">配送信息</div>
              <ul class="list">
                <li v-if="orderDetailList.deliveryOrder&&orderDetailList.deliveryOrder.deliveryService" class="item">
                  <div>配送员姓名：</div>
                  <div class="value">
                    {{
                      orderDetailList.deliveryOrder.deliveryService.name
                    }}
                  </div>
                </li>
                <li v-if="orderDetailList.deliveryOrder&&orderDetailList.deliveryOrder.deliveryService" class="item">
                  <div>配送员电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.deliveryOrder.deliveryService.phone
                    }}
                  </div>
                </li>
                <li v-if="orderDetailList.merchant_take_info" class="item">
                  <div>配送时间：</div>
                  <div v-if="orderDetailList.merchant_take_info[orderDetailList.mer_id].date" class="value">
                    {{
                      orderDetailList.merchant_take_info[orderDetailList.mer_id].date
                    }}
                    {{orderDetailList.merchant_take_info[orderDetailList.mer_id].time}}
                  </div>
                  <div v-else-if="orderDetailList.merchant_take_info[orderDetailList.mer_id].remarks">
                    {{orderDetailList.merchant_take_info[orderDetailList.mer_id].remarks}}
                  </div>
                </li>
                <li v-if="orderDetailList.merchant_take_info" class="item">
                  <div>配送进度：</div>
                  <div class="value">
                    {{
                      orderDetailList.merchant_take_info[orderDetailList.mer_id].distance
                        ? orderDetailList.merchant_take_info[orderDetailList.mer_id].distance
                        : "-"
                    }}
                  </div>
                </li>
              </ul>
            </div>
            <div
              class="section"
              v-if="
                orderDetailList.order_extend && orderDetailList.is_virtual != 4
              "
            >
              <div class="title">系统表单信息</div>
              <ul
                class="list mb20"
                v-for="(item, i) in orderDetailList.order_extend"
                :key="i"
              >
                <li v-for="(value, key) in item" class="item" :key="key">
                  <div v-if="!Array.isArray(value)">
                    {{ key }}： {{ value || "--" }}
                  </div>
                  <template v-else>
                    <div>{{ key }}：</div>
                    <img
                      v-for="(pic, idx) in value"
                      :key="idx"
                      :src="pic"
                      style="width:40px;height:40px;margin-right:12px;"
                    />
                  </template>
                </li>
              </ul>
            </div>
            <div
              class="section"
              v-if="
                orderDetailList.order_extend && orderDetailList.is_virtual == 4
              "
            >
              <div class="title">系统表单信息</div>
              <ul
                class="list mb20"
                v-for="(item, i) in orderDetailList.order_extend"
                :key="i"
              >
                <li v-for="(value, key) in item" class="item" :key="key">
                  <div v-if="!Array.isArray(value)">
                    {{ key }}： {{ value || "--" }}
                  </div>
                  <template v-else>
                    <div>{{ key }}：</div>
                    <img
                      v-for="(pic, idx) in value"
                      :key="idx"
                      :src="pic"
                      style="width:40px;height:40px;margin-right:12px;"
                    />
                  </template>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDetailList.mark">
              <div class="title">买家留言</div>
              <ul class="list">
                <li class="item item100">
                  <div>
                    {{ orderDetailList.mark ? orderDetailList.mark : "-" }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDetailList.remark">
              <div class="title">商家备注</div>
              <ul class="list">
                <li class="item item100">
                  <div>
                    {{ orderDetailList.remark ? orderDetailList.remark : "-" }}
                  </div>
                </li>
              </ul>
            </div>
            <div
              class="section"
              v-if="
                orderDetailList.delivery_type === '1' ||
                  orderDetailList.delivery_type === '4'
              "
            >
              <div class="title">物流信息</div>
              <ul class="list">
                <li class="item">
                  <div>快递公司：</div>
                  <div class="value">
                    {{
                      orderDetailList.delivery_name
                        ? orderDetailList.delivery_name
                        : "-"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>快递单号：</div>
                  <div class="value">
                    {{
                      orderDetailList.delivery_id
                        ? orderDetailList.delivery_id
                        : "-"
                    }}
                  </div>
                  <el-button
                    type="text"
                    size="mini"
                    style="margin-left: 5px"
                    @click="openLogistics"
                    >物流查询</el-button
                  >
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品信息" name="goods">
            <el-table :data="orderDetailList.orderProduct" size="small">
              <el-table-column
                label="商品ID"
                prop="product_id"
                min-width="90"
              />
              <el-table-column label="商品信息" min-width="300">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="demo-image__preview">
                      <el-image
                        :src="scope.row.cart_info.product.image"
                        :preview-src-list="[scope.row.cart_info.product.image]"
                      />
                    </div>
                    <div>
                      <div>{{ scope.row.cart_info.product.store_name }}</div>
                      <div class="line1 gary">
                        规格：{{
                          scope.row.cart_info.productAttr.sku
                            ? scope.row.cart_info.productAttr.sku
                            : "默认"
                        }}
                      </div>
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="售价" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1" v-if="orderDetailList.is_virtual != 4">
                      {{
                        scope.row.cart_info.productAttr.price
                          ? scope.row.cart_info.productAttr.price
                          : "-"
                      }}
                    </div>
                    <div v-else>
                      {{
                        scope.row.cart_info.productAttr.price
                          ? scope.row.cart_info.productAttr.price
                          : "-"
                      }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="成本价" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{
                        scope.row.cart_info.productAttr.cost
                          ? scope.row.cart_info.productAttr.cost
                          : "-"
                      }}
                    </div>
                  </div>
                </template>
              </el-table-column>
              <!-- <el-table-column label="实付金额" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{
                        scope.row.product_price ? scope.row.product_price : "-"
                      }}
                    </div>
                  </div>
                </template>
              </el-table-column> -->
              <el-table-column label="购买数量" min-width="90">
                <template slot-scope="scope">
                  <div class="tab">
                    <div class="line1">
                      {{ scope.row.product_num }}
                    </div>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
          <!-- 预约商品订单 -->
          <el-tab-pane
            label="服务信息"
            name="reservation"
            v-if="orderDetailList.is_virtual == 4"
          >
            <div class="section">
              <div class="title">服务信息</div>
              <ul class="list">
                <li class="item">
                  <div>服务人员：</div>
                  <div class="value">
                    {{
                      orderDetailList.staffs && orderDetailList.staffs.name
                        ? orderDetailList.staffs.name
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>联系电话：</div>
                  <div class="value">
                    {{
                      orderDetailList.staffs && orderDetailList.staffs.phone
                        ? orderDetailList.staffs.phone
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item" />
                <li class="item">
                  <div>开始服务时间：</div>
                  <div class="value">
                    {{
                      orderDetailList.clock_in_info &&
                      orderDetailList.clock_in_info.clock_time
                        ? orderDetailList.clock_in_info.clock_time
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>结束服务时间：</div>
                  <div class="value">
                    {{
                      orderDetailList.verify_time
                        ? orderDetailList.verify_time
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item">
                  <div>服务时长：</div>
                  <div class="value">
                    {{ getTimes() }}
                  </div>
                </li>
              </ul>
            </div>
            <div class="section" v-if="orderDetailList.order_type == 0">
              <div class="title">
                打卡信息
              </div>
              <ul class="list">
                <li class="item item100">
                  <div>打卡备注：</div>
                  <div class="value">
                    {{
                      orderDetailList.clock_in_info &&
                      orderDetailList.clock_in_info.remark
                        ? orderDetailList.clock_in_info.remark
                        : "--"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div>打卡照片：</div>
                  <template
                    v-if="
                      orderDetailList.clock_in_info &&
                        orderDetailList.clock_in_info.images &&
                        orderDetailList.clock_in_info.images.length > 0
                    "
                  >
                    <div
                      class="flex"
                      v-for="(item, index) in orderDetailList.clock_in_info
                        .images"
                      :key="index"
                    >
                      <el-image
                        :src="item"
                        :preview-src-list="[item]"
                        style="width:40px;height:40px;margin-right:12px;"
                      />
                    </div>
                  </template>
                  <div v-else>--</div>
                </li>
              </ul>
            </div>

            <div
              class="section"
              v-if="
                JSON.stringify(orderDetailList.reservation_service_voucher) !=
                  '[]'
              "
            >
              <div class="title">服务过程留凭</div>
              <ul class="list">
                <li
                  class="item"
                  v-for="(item,
                  i) in orderDetailList.reservation_service_voucher"
                  :key="i"
                >
                  <div>{{ i }}：</div>
                  <template v-if="!Array.isArray(item)">
                    <div class="value">{{ item }}</div>
                  </template>
                  <template v-else>
                    <el-image
                      v-for="(pic, idx) in item"
                      :key="idx"
                      :src="pic"
                      :preview-src-list="[pic]"
                      style="width:40px;height:40px;margin-right:12px;"
                    />
                  </template>
                </li>
              </ul>
            </div>
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
                      type="daterange"
                      placeholder="选择日期"
                      value-format="yyyy/MM/dd"
                      clearable
                      @change="onchangeTime"
                    >
                    </el-date-picker>
                  </el-form-item>
                  <!-- <div>
                    <el-button type="primary" size="small" @click="onOrderLog(orderId)">查询</el-button>
                  </div> -->
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
              <el-pagination
                :page-size="tableFromLog.limit"
                :current-page="tableFromLog.page"
                layout="prev, pager, next, jumper"
                :total="tableDataLog.total"
                @size-change="handleSizeChangeLog"
                @current-change="pageChangeLog"
              />
            </div>
          </el-tab-pane>
          <el-tab-pane
            v-if="childOrder.length > 0"
            label="关联订单"
            name="subOrder"
          >
            <el-table :data="childOrder">
              <el-table-column label="订单编号" prop="order_sn" min-width="150">
                <template slot-scope="scope">
                  <div>{{ scope.row.order_sn }}</div>
                </template>
              </el-table-column>
              <el-table-column label="商品信息" min-width="200">
                <template slot-scope="scope">
                  <div
                    v-for="(val, i) in scope.row.orderProduct"
                    :key="i"
                    class="tabBox acea-row row-middle"
                  >
                    <div class="demo-image__preview">
                      <el-image
                        :src="val.cart_info.product.image"
                        :preview-src-list="[val.cart_info.product.image]"
                      />
                    </div>
                    <span class="tabBox_tit"
                      >{{ val.cart_info.product.store_name + " | "
                      }}{{ val.cart_info.productAttr.sku }}</span
                    >
                    <span class="tabBox_pice">
                      {{
                        "￥" +
                          val.cart_info.productAttr.price +
                          " x " +
                          val.product_num
                      }}
                      <em
                        v-if="
                          val.refund_num < val.product_num && val.refund_num > 0
                        "
                        style="color: red;font-style: normal;"
                        >(-{{ val.product_num - val.refund_num }})</em
                      >
                    </span>
                  </div>
                </template>
              </el-table-column>
              <el-table-column label="实际支付" min-width="80" align="center">
                <template slot-scope="scope">
                  <span>{{ scope.row.pay_price }}</span>
                </template>
              </el-table-column>
              <el-table-column
                label="订单生成时间"
                prop="create_time"
                min-width="120"
              />
              <el-table-column label="操作" min-width="50" fixed="right">
                <template slot-scope="scope">
                  <el-button
                    type="text"
                    size="small"
                    @click="getChildOrderDetail(scope.row.order_id)"
                    >详情</el-button
                  >
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
    <el-dialog
      title="物流查询"
      :visible.sync="dialogLogistics"
      width="350px"
      v-if="dialogLogistics"
    >
      <div class="logistics acea-row row-top">
        <div class="logistics_img">
          <img src="@/assets/images/expressi.jpg" />
        </div>
        <div class="logistics_cent">
          <span>物流公司：{{ orderDetailList.delivery_name }}</span>
          <span>物流单号：{{ orderDetailList.delivery_id }}</span>
        </div>
      </div>
      <div class="acea-row row-column-around trees-coadd">
        <div class="scollhide">
          <el-timeline v-if="result.length > 0">
            <el-timeline-item v-for="(item, i) in result" :key="i">
              <p class="time" v-text="item.time" />
              <p class="content" v-text="item.status" />
            </el-timeline-item>
          </el-timeline>
        </div>
      </div>
    </el-dialog>
    <!--订单核销-->
    <order-cancellate ref="orderCancellate" @getList="getList" />
    <!-- 派单 -->
    <dialogDispatch ref="dialogDispatch" @selectStaff="selectStaff" @deliveryDispatch="deliveryDispatch" />
    <!-- 改约 -->
    <dialogReschedule
      ref="dialogReschedule"
      :orderDetailList="orderDetailList"
      @getInfo="getInfo"
      :month="month"
    />
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
  getExpress,
  orderDeliveryApi,
  orderDetailApi,
  orderLogApi,
  orderPrintApi,
  orderRemarkApi,
  getChildrenOrderApi,
  modifyOrderAddress,
  orderDispatchApi,
  orderUpdateDispatchApi,
  deliveryDispatchApi,
  deliveryUpdateDispatchApi,
  deliveryConfirmApi,
  orderRescheduleApi,
  orderReservationVerifyApi,
  editReservationTime,
  productShowMonthApi
} from "@/api/order";

import { TimePicker } from "view-design";
import "view-design/dist/styles/iview.css";
import orderCancellate from "./orderCancellate";
import dialogDispatch from "./components/dialogDispatch";
import dialogReschedule from "./components/dialogReschedule";
export default {
  components: {
    orderCancellate,
    dialogDispatch,
    dialogReschedule,
    TimePicker
  },
  props: {
    drawer: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      loading: true,
      visiblePopover: false,
      orderId: "",
      direction: "rtl",
      activeName: "detail",
      goodsList: [],
      timeVal: [],
      reservationForm: {
        reservation_date: "",
        part_start: "",
        part_end: "",
        reservation_time_part: []
      },
      month: [],
      orderConfirm: false,
      sendGoods: false,
      dialogLogistics: false,
      confirmReceiptForm: {
        id: ""
      },
      tableDataLog: {
        data: [],
        total: 0
      },
      contentList: [],
      nicknameList: [],
      result: [],
      orderDetailList: {
        user: {
          real_name: ""
        },
        groupOrder: {
          group_order_sn: ""
        }
      },
      orderImg: require("@/assets/images/order_icon.png"),
      tableFromLog: {
        user_type: "",
        date: [],
        page: 1,
        limit: 10
      },
      childOrder: []
    };
  },
  filters: {},

  methods: {
    // 修改预约时间
    reschedule() {
      if (this.reservationForm.reservation_time_part.length > 0) {
        this.reservationForm.part_start = this.reservationForm.reservation_time_part[0];
        this.reservationForm.part_end = this.reservationForm.reservation_time_part[1];
      }

      if (
        this.reservationForm.reservation_date &&
        this.reservationForm.part_start &&
        this.reservationForm.part_end
      ) {
        editReservationTime(this.orderId, this.reservationForm).then(res => {
          this.$message.success(res.message);
          this.visiblePopover = false;
          this.getInfo(this.orderId);
          this.$emit("getlist");
        });
      } else {
        this.$message.error("请选择预约时间");
      }
    },
    openPopover() {
      this.reservationForm.reservation_date = this.orderDetailList.orderProduct[0].reservation_date;
      let time = this.orderDetailList.orderProduct[0].reservation_time_part.split(
        "-"
      );
      this.reservationForm.reservation_time_part = time.map(item =>
        item.trim()
      );
      this.reservationForm.part_start = this.reservationForm.reservation_time_part[0];
      this.reservationForm.part_end = this.reservationForm.reservation_time_part[1];
      this.visiblePopover = true;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFromLog.date = e ? this.timeVal.join("-") : "";
      this.onOrderLog(this.orderId);
    },
    handleClose() {
      this.activeName = "detail";
      this.$emit("closeDrawer");
      this.sendGoods = false;
      this.orderRemark = false;
      this.visiblePopover = false;
    },
    openLogistics() {
      this.getOrderData();
      this.dialogLogistics = true;
    },
    getMonth() {
      let dateTime = this.orderDetailList.orderProduct[0].reservation_date
        ? this.orderDetailList.orderProduct[0].reservation_date
        : this.orderDetailList.orderProduct[0].reservation_time_part;
      const obj = {
        sku_id: this.orderDetailList.orderProduct[0].cart_info.productAttr
          .value_id,
        date: dateTime
      };
      productShowMonthApi(
        this.orderDetailList.orderProduct[0].product_id,
        obj
      ).then(res => {
        this.month = res.data.days;
        this.reservationForm.reservation_time_part = this.o;
      });
    },
    getTimes() {
      let str = "";
      if (
        this.orderDetailList.verify_time &&
        this.orderDetailList.clock_in_info &&
        this.orderDetailList.clock_in_info.clock_time
      ) {
        let startTime = this.orderDetailList.clock_in_info.clock_time;
        let endTime = this.orderDetailList.verify_time;
        const formatISOTime = timeStr => timeStr.replace(" ", "T") + "Z";
        const start = new Date(formatISOTime(startTime));
        const end = new Date(formatISOTime(endTime));
        // 计算时间差
        const diffMs = end - start;
        const diffMinute = Math.ceil(diffMs / 60000);
        return (diffMinute > 0 ? diffMinute : 1) + "分钟"; // 1分钟 = 60,000毫秒
      } else {
        return (str = "--");
      }
    },
    selectStaff(id, type) {
      let api = type == 1 ? orderDispatchApi : orderUpdateDispatchApi;
      api(this.orderId, { staffs_id: id }).then(res => {
        this.$message.success(res.message);
        this.getInfo(this.orderId);
      });
    },
    // 改派
    dispatchFn(type) {
      if (type == 1 || type == 2) {
        this.$refs.dialogDispatch.openBox(type, this.orderId, 'reservation');
      }
      if (type == 3) {
        this.$refs.dialogReschedule.openBox(type, this.orderId, 'reservation');
      }
      if (type == 4) {
        this.$modalSure("此服务已完成吗").then(() => {
          orderReservationVerifyApi(this.orderId)
            .then(({ message }) => {
              this.$message.success(message);
              this.getInfo(this.orderId);
            })
            .catch(({ message }) => {
              this.$message.error(message);
            });
        });
      }
    },
    // 同城配送派单
    cityDispatch(type) {
      if (type == 1 || type == 2) {
        this.$refs.dialogDispatch.openBox(type, this.orderId, 'cityService');
      }
    },
    // 同城配送改派
    deliveryDispatch(id, type) {
      let api = type == 1 ? deliveryDispatchApi : deliveryUpdateDispatchApi;
      api(this.orderId, { service_id: id }).then(res => {
        this.$message.success(res.message);
        this.getInfo(this.orderId);
      })
      .catch(res => {
        this.$message.error(res.message);
      });
    },
    // 同城配送确认送达
    deliveryConfirm() {
      deliveryConfirmApi(this.orderId).then(res => {
        this.$message.success(res.message);
        this.getInfo(this.orderId);
      })
      .catch(res => {
        this.$message.error(res.message);
      });
    },
    // 获取订单状态
    getStatusText(row, type) {
      let statusText = "";
      let statusObj = {
        "-1": "已退款",
        0: "待发货",
        1: "待收货",
        2: "待评价",
        3: "交易完成",
        20: "已打卡"
      };
      let statusObj4 = {
        "-1": "已退款",
        0: "待指派",
        1: "待服务",
        2: "待评价",
        3: "交易完成",
        20: "服务中"
      };
      // 预约商品
      if (type == 4) {
        return statusObj4[row.status];
      } else {
        if (row.is_del == 1) {
          return "已删除";
        } else if (row.paid == 0 && row.status == 0) {
          return "待付款";
        } else {
          return statusObj[row.status];
        }
      }
    },

    // 获取订单状态
    orderColorFilter(status) {
      const statusMap = {
        "0": "#0FC6C2",
        "1": "#FF7D00",
        "2": "#3491FA",
        "3": "rgba(0,0,0,.85)",
        "-1": "#F56464",
        "9": "#F56464",
        "10": "#FF7D00",
        "11": "#F56464",
        "20": "#4073FA"
      };
      return statusMap[status];
    },

    // 订单核销
    orderCancellation() {
      const that = this;
      that.$refs.orderCancellate.dialogVisible = true;
      that.$refs.orderCancellate.productDetails(
        that.orderDetailList.verify_code
      );
      that.$refs.orderCancellate.isColum = true;
    },
    // 发送货
    send() {
      this.$emit("send", this.orderDetailList, this.orderId);
    },
    // 小票打印
    printOrder() {
      orderPrintApi(this.orderId)
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 备注
    onOrderMark() {
      this.$modalForm(orderRemarkApi(this.orderId), { form: { labelWidth: '62px' } }).then(() =>
        this.getInfo(this.orderId)
      );
    },
    //下拉
    handleCommand(command) {
      if (command == "mark") {
        this.onOrderMark();
      } else if (command == "modify") {
        this.reSend(this.orderId);
      } else if (command == "redriving") {
        this.reDriving();
      } else if (command == "refund") {
        this.refund(this.orderId, this.orderDetailList.order_type);
      } else if (command == "print") {
        this.printDelivery(this.orderId);
      } else if (command == "address") {
        this.changeAddress(this.orderId);
      }
    },
    //修改收货地址
    changeAddress(id) {
      this.$modalForm(modifyOrderAddress(id)).then(() => this.getInfo(id));
      // this.$emit('changeAddress',id);
    },
    // 退款
    refund(id,orderType) {
      this.$emit("onOrderRefund", id, orderType);
    },
    // 修改发货信息
    reSend(id) {
      this.$emit("reSend", id);
    },
    // 修改订单信息
    getList() {
      this.$emit("getList", "");
    },
    // 复打
    reDriving() {
      this.$emit("reDriving", "");
    },
    // 打印配货单
    printDelivery() {
      localStorage.setItem("printIds", this.orderId);
      let pathInfo = this.$router.resolve({
        name: "OrderPrint",
        query: {
          id: this.orderId
        }
      });
      window.open(pathInfo.href, "_blank");
    },
    // 获取子订单信息
    getChildOrder() {
      this.loading = true;
      getChildrenOrderApi(this.orderId)
        .then(res => {
          this.activeName = "detail";
          this.childOrder = res.data;
          setTimeout(() => {
            this.loading = false;
          }, 500);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取订单物流信息
    getOrderData() {
      getExpress(this.orderId)
        .then(async res => {
          this.result = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    //发送货
    toSendGoods() {
      this.sendGoods = true;
    },
    getDelivery() {
      orderDeliveryApi(this.orderId)
        .then(res => {
          this.$message.success(res.message);
          this.sendGoods = false;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    getChildOrderDetail(id) {
      this.getInfo(id);
    },
    getInfo(id) {
      this.loading = true;
      this.orderId = id;
      orderDetailApi(id)
        .then(res => {
          this.drawer = true;
          this.orderDetailList = res.data;
          if (
            this.orderDetailList.status == 0 &&
            this.orderDetailList.order_type === 0
          ) {
            this.getMonth();
          }
          this.getChildOrder();
        })
        .catch(res => {
          this.$message.error(res.message);
        });

      this.$emit("getlist");
    },
    tabClick(tab) {
      if (tab.name === "orderList") {
        this.onOrderLog(this.orderId);
      }
    },
    onOrderLog(id) {
      orderLogApi(id, this.tableFromLog).then(res => {
        this.tableDataLog.data = res.data.list;
        this.tableDataLog.total = res.data.count;
      });
    },
    pageChangeLog(page) {
      this.tableFromLog.page = page;
      this.onOrderLog(this.orderId);
    },
    handleSizeChangeLog(val) {
      this.tableFromLog.limit = val;
      this.onOrderLog(this.orderId);
    },
    operationType(type) {
      if (type == 0) {
        return "系统";
      } else if (type == 1) {
        return "用户";
      } else if (type == 2) {
        return "平台";
      } else if (type == 3) {
        return "商户";
      } else if (type == 4) {
        return "商家客服";
      } else {
        return "未知";
      }
    }
  }
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
      width: 20%;
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
        color: #437ffd;
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
    &.item50 {
      flex: 0 0 calc(50% / 1);
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
.flex-end {
  display: flex;
  justify-content: flex-end;
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
.iconbianji1 {
  cursor: pointer;
  font-size: 14px;
  color: var(--prev-color-primary);
  margin-left: 4px;
}
.logistics {
  align-items: center;
  padding: 10px 0px;
  .logistics_img {
    width: 45px;
    height: 45px;
    margin-right: 12px;
    img {
      width: 100%;
      height: 100%;
    }
  }
  .logistics_cent {
    span {
      display: block;
      font-size: 12px;
    }
  }
}
.tabBox_tit {
  width: 53%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
</style>

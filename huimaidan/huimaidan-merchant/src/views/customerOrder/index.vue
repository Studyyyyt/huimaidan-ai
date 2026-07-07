<template>
  <div class="customer-order-page">
    <div class="box-header">
      <div class="acea-row row-middle">
        <!-- <div class="font-sm after-line" @click="goBack">
          <span class="el-icon-arrow-left"></span>
          <span class="pl10">返回</span>
        </div> -->
        <span class="ht_title ml10">代客下单</span>
      </div>
    </div>
    <div class="order-main">
      <div class="order-content acea-row row-between">
        <div class="customer-left" :class="{ nopointer: order_id }">
          <div class="cart">
            <div v-if="userInfo" class="title acea-row row-middle">
              <div class="text acea-row row-middle">
                <div class="picture">
                  <img :src="userInfo.avatar || avatar" />
                </div>
                <div class="textCon">
                  <div v-if="isFit" class="text-wrap">
                    <div class="name-wrap">
                      <span class="phone">{{ userInfo.nickname }}</span>
                    </div>
                  </div>
                  <div class="text-wrap">
                    <div class="acea-row row-between row-middle">
                      <div v-if="!isFit" class="name-wrap">
                        <div v-if="userInfo.nickname" class="phone line1">
                          {{ userInfo.nickname }}
                        </div>
                        <div v-if="userInfo.phone" class="name line1">
                          {{ userInfo.phone }}
                        </div>
                      </div>
                      <div class="textBtn acea-row row-middle row-between">
                        <span
                          v-if="!isFit"
                          class="btn btn-orange"
                          @click="fitUsers"
                          >散客</span
                        >
                        <span v-else></span>
                        <span class="btn btn-blue" @click="switchUsers"
                          >切换用户</span
                        >
                      </div>
                    </div>
                    <div class="acea-row row-middle">
                      <div
                        v-if="userInfo.integral"
                        class="balance acea-row row-middle mr14"
                      >
                        <span>积分：</span>
                        <span class="num">{{ userInfo.integral }}</span>
                      </div>
                      <div
                        v-if="userInfo.now_money"
                        class="balance acea-row row-middle"
                      >
                        <span>余额：</span>
                        <span class="num">{{ userInfo.now_money }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="count acea-row row-between">
              <div class="cart-sel">
                已选购<span class="num">{{ cartSum }}</span
                >件
              </div>
              <div
                v-if="cartList.length > 0 || invalidList.length > 0"
                class="count-r acea-row row-middle"
                @click="delAll"
              >
                <img class="image" src="@/assets/images/clear.png" alt="" />
                <span>清空</span>
              </div>
            </div>
            <div class="listCon">
              <div
                v-if="couponList.length > 0"
                class="cart-coupon acea-row row-between"
              >
                <div class="coupon-title">优惠券:</div>
                <div
                  class="acea-row row-middle coupon-more"
                  @click="openCoupon"
                >
                  <div
                    class="activity"
                    v-for="(item, index) in checkedCoupons"
                    :key="index"
                  >
                    {{
                      item.use_min_price == 0
                        ? "立减" + item.coupon_price
                        : "满" + item.use_min_price + "减" + item.coupon_price
                    }}
                  </div>
                  <div class="iconfont-h5 icon-ic_rightarrow"></div>
                </div>
              </div>
              <div class="list">
                <div
                  v-for="(item, index) in cartList"
                  :key="index + 'data'"
                  class="item"
                >
                  <div class="item-count acea-row row-between">
                    <div class="promotions acea-row row-between">
                      <div class="picture">
                        <img
                          :src="item.productAttr.image || item.product.image"
                        />
                      </div>
                      <div class="text">
                        <div class="name line1">
                          {{ item.product.store_name }}
                        </div>
                        <div
                          v-if="
                            item.productAttr &&
                              item.productAttr.product.spec_type
                          "
                          class="info"
                          @click="cartAttr(item)"
                        >
                          <div class="suk line1">
                            {{ item.productAttr.sku }}
                          </div>
                          <span class="iconfont iconxiayi"></span>
                        </div>
                        <div v-else class="info">默认</div>
                        <div class="sum_price acea-row row-middle">
                          <span class="price"
                            >¥{{
                              item.productAttr.price || item.product.price
                            }}</span
                          >
                          <span
                            v-if="item.productAttr.new_price"
                            class="price orange"
                            >¥{{ item.productAttr.new_price }}</span
                          >
                        </div>
                      </div>
                    </div>
                    <div class="cartOpt">
                      <div class="opt">
                        <span
                          class="opt-item"
                          :class="{ disabled: updatedPriceFlag == 2 }"
                          @click="changePrice(item, 'goods')"
                          >改价</span
                        >
                        <span class="opt-item" @click="delCart(item, index)"
                          >删除</span
                        >
                      </div>
                      <div class="cartBnt acea-row row-center-wrapper">
                        <div class="cart-reduce">
                          <span
                            class="iconfont iconjian"
                            @click="calculate(item, 'reduce')"
                          ></span>
                        </div>
                        <el-input
                          type="number"
                          class="cart-input"
                          v-model="item.cart_num"
                          :max="item.productAttr.product.stock"
                          :min="1"
                          @blur="
                            e => {
                              changeCart(e, item);
                            }
                          "
                        />
                        <div class="cart-increase">
                          <span
                            class="iconfont iconjia"
                            @click="calculate(item, 'add')"
                          ></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                v-if="!invalidList.length && !cartList.length"
                class="noCart acea-row row-center-wrapper v-center"
              >
                <div>
                  <div class="picture">
                    <img src="@/assets/images/no-cart.png" />
                  </div>
                  <div class="tip">暂无商品，快去添加吧~</div>
                </div>
              </div>
            </div>
            <div class="footer">
              <div class="footer-top acea-row row-between row-middle">
                <div class="top-left acea-row row-middle">
                  <div class="acea-row row-middle">
                    <div class="delivery-name">配送方式：</div>
                    <el-dropdown
                      class="delivery-dropdown"
                      trigger="click"
                      @command="changeDelivery"
                    >
                      <span class="el-dropdown-link">
                        {{
                          delivery_way == 0
                            ? "快递配送"
                            : delivery_way == 1
                            ? "到店自提"
                            : "无需物流"
                        }}
                        <i class="el-icon-arrow-down el-icon--right"></i>
                      </span>
                      <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item
                          command="1"
                          :disabled="cartList.length <= 0"
                          >到店自提</el-dropdown-item
                        >
                        <el-dropdown-item
                          command="0"
                          :disabled="cartList.length <= 0"
                          >快递配送</el-dropdown-item
                        >
                        <el-dropdown-item
                          command="4"
                          :disabled="cartList.length <= 0"
                          >无需物流</el-dropdown-item
                        >
                      </el-dropdown-menu>
                    </el-dropdown>
                  </div>
                  <div v-if="delivery_way == 0" class="shipping">
                    <el-checkbox-group
                      v-model="delivery_free"
                      @change="orderCheck"
                    >
                      <el-checkbox
                        class="delivery-label"
                        label="免运费"
                        :disabled="cartList.length <= 0"
                      ></el-checkbox>
                    </el-checkbox-group>
                  </div>
                  <div
                    v-if="
                      priceInfo.totalShippingAmount > 0 || delivery_way == 0
                    "
                    class="fee"
                  >
                    运费: {{ priceInfo.totalShippingAmount }}
                  </div>
                </div>
                <div class="top-address">
                  <el-dropdown
                    v-if="delivery_way == 0"
                    trigger="click"
                    @command="changeAddress"
                  >
                    <span class="el-dropdown-link">
                      收货地址
                      <i class="el-icon-arrow-down el-icon--right"></i>
                    </span>
                    <el-dropdown-menu class="address-dropdown" slot="dropdown">
                      <el-scrollbar
                        style="max-height: 300px;overflow-y:scroll;"
                      >
                        <el-dropdown-item command="add">
                          <div
                            class="add-address acea-row row-middle row-center"
                          >
                            <span class="iconfont iconjia"></span>
                            <span>添加收货地址</span>
                          </div>
                        </el-dropdown-item>
                        <el-dropdown-item
                          v-for="(item, index) in addressList"
                          :key="index"
                          :command="item.address_id"
                        >
                          <div
                            class="address-item"
                            :class="{ on: address_id == item.address_id }"
                          >
                            <div class="address-title acea-row row-middle">
                              <span>{{ item.real_name }}</span>
                              <span class="ml10">{{ item.phone }}</span>
                            </div>
                            <div class="address-info">
                              {{ item.province }}{{ item.city }}{{ item.street
                              }}{{ item.district ? item.district : ""
                              }}{{ item.detail }}
                            </div>
                          </div>
                        </el-dropdown-item>
                      </el-scrollbar>
                    </el-dropdown-menu>
                  </el-dropdown>
                </div>
              </div>
              <div class="footer-bottom acea-row row-between row-middle">
                <div>
                  <div class="total-pay">
                    合计：<span class="symbol">¥</span
                    ><span class="money">{{
                      order_modify_price != ""
                        ? order_modify_price
                        : priceInfo.totalPaymentPrice
                    }}</span>
                  </div>
                  <div class="mt10 acea-row row-middle">
                    <div class="order-count-list">
                      共优惠：{{ priceInfo.totalDiscountAmount }}
                    </div>
                    <el-dropdown
                      v-if="
                        priceInfo.discountDetail &&
                          priceInfo.totalDiscountAmount != 0
                      "
                      class="switchs"
                      trigger="click"
                    >
                      <span class="el-dropdown-link">
                        明细
                      </span>
                      <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item
                          v-if="priceInfo.discountDetail.couponsDiscountAmount"
                          class="acea-row row-between"
                          name="1"
                        >
                          <span class="dropdown-item-name">优惠券</span>
                          <span class="dropdown-item-value"
                            >-￥{{
                              priceInfo.discountDetail.couponsDiscountAmount
                            }}</span
                          >
                        </el-dropdown-item>
                        <el-dropdown-item
                          v-if="priceInfo.discountDetail.vipDiscountAmount"
                          class="acea-row row-between"
                          name="2"
                        >
                          <span class="dropdown-item-name">付费会员</span>
                          <span class="dropdown-item-value"
                            >-￥{{
                              priceInfo.discountDetail.vipDiscountAmount
                            }}</span
                          >
                        </el-dropdown-item>
                        <el-dropdown-item
                          v-if="priceInfo.discountDetail.updatedDiscountAmount"
                          class="acea-row row-between"
                          name="3"
                        >
                          <span class="dropdown-item-name">改价优惠</span>
                          <span class="dropdown-item-value"
                            >-￥{{
                              priceInfo.discountDetail.updatedDiscountAmount
                            }}</span
                          >
                        </el-dropdown-item>
                        <el-dropdown-item
                          v-if="priceInfo.totalShippingAmount && delivery_free"
                          class="acea-row row-between"
                          name="4"
                        >
                          <span class="dropdown-item-name">运费优惠</span>
                          <span class="dropdown-item-value"
                            >-￥{{ priceInfo.totalShippingAmount }}</span
                          >
                        </el-dropdown-item>
                        <el-dropdown-item
                          v-if="
                            discountDetail.integralDiscountAmount &&
                              use_integral
                          "
                          class="acea-row row-between"
                          name="5"
                        >
                          <span class="dropdown-item-name">积分抵扣</span>
                          <span class="dropdown-item-value"
                            >-￥{{
                              discountDetail.integralDiscountAmount
                            }}</span
                          >
                        </el-dropdown-item>
                      </el-dropdown-menu>
                    </el-dropdown>
                  </div>
                </div>
                <div class="acea-row row-between row-middle">
                  <el-button
                    :disabled="
                      !cart_ids || !cart_ids.length || updatedPriceFlag == 1
                    "
                    :class="{
                      disabled:
                        !cart_ids || !cart_ids.length || updatedPriceFlag == 1
                    }"
                    class="btn btn-default mr10"
                    @click="changePrice(priceInfo, 'order')"
                    >整单改价</el-button
                  >
                  <el-button
                    type="primary"
                    :disabled="!cart_ids || !cart_ids.length"
                    class="btn btn-primary"
                    @click="goPay"
                    >去结算({{ cartSum }})</el-button
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="customer-right acea-row row-between">
          <template v-if="step == 1">
            <div class="product-main divBox">
              <div class="product-search acea-row row-middle">
                <el-input
                  v-model="tableFrom.search"
                  prefix-icon="el-icon-search"
                  @keyup.enter.native="getList(1)"
                  class="product-input mr14"
                  style="width: 527px;"
                  placeholder="输入商品名称/ID/扫码枪扫描商品条形码"
                ></el-input>
              </div>
              <template v-if="tableData.data.length > 0">
                <div class="product-list acea-row">
                  <div
                    v-for="(item, index) in tableData.data"
                    :key="index"
                    class="item mb14 acea-row row-between"
                    @click="attrTap(item)"
                  >
                    <div class="picture">
                      <el-image :src="item.image" class="image"></el-image>
                      <div v-if="!item.stock && !item.cart_num" class="trip">
                        <div>暂无</div>
                        <div>库存</div>
                      </div>
                    </div>
                    <div class="list-text">
                      <div class="name line2">
                        {{ item.store_name }}
                      </div>
                      <div class="stock">库存：{{ item.stock }}</div>
                      <div class="acea-row row-middle price-count">
                        <div class="price"><span>¥</span>{{ item.price }}</div>
                        <div
                          v-if="item.svip_price_type"
                          class="svip acea-row row-middle"
                        >
                          <span>¥{{ item.svip_price }}</span>
                          <img src="@/assets/images/svip_price.png" alt="" />
                        </div>
                      </div>
                    </div>
                    <div
                      v-if="!item.stock && !item.cart_num"
                      class="no-stock"
                    ></div>
                    <div
                      v-if="item.cart_num && cartList.length"
                      class="icon-cart-num"
                    >
                      {{ item.cart_num > 99 ? "99+" : item.cart_num }}
                    </div>
                  </div>
                </div>
                <div class="product-page">
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
              </template>
              <div v-else>
                <div class="noGoods acea-row row-center-wrapper v-center">
                  <div>
                    <div class="picture">
                      <img src="@/assets/images/no-goods.png" />
                    </div>
                    <div class="tip">暂无商品，去看看其它的吧~</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="product-cate">
              <div
                v-for="(item, index) in categoryList"
                :key="index"
                class="cate-name mb14"
                :class="{ on: item.store_category_id == tableFrom.mer_cate_id }"
                @click="goodsSearch(item)"
              >
                <span class="cate_name">{{ item.cate_name }}</span>
              </div>
            </div>
          </template>
          <template v-else-if="step == 2">
            <div class="pay_count">
              <!--收货信息-->
              <div v-if="delivery_way == 0" class="receipt acea-row row-middle">
                <div class="title">收货信息：</div>
                <div class="info">
                  {{ address_user }} {{ userInfo.phone }}
                  {{ addressInfo }}
                </div>
              </div>
              <div class="pay_main acea-row row-between">
                <div class="pay_type">
                  <div
                    v-if="integralDetail.flag == 1 && !order_modify_price"
                    class="pay_item mb24"
                  >
                    <div class="title">优惠抵扣</div>
                    <div
                      class="item_row acea-row row-between"
                      :class="{ nopointer: order_id }"
                    >
                      <div class="acea-row row-middle">
                        <img
                          class="image"
                          src="@/assets/images/deduction_icon.png"
                          alt=""
                        />
                        <span class="">积分抵扣：</span>
                      </div>
                      <div class="row-check acea-row row-middle">
                        <el-checkbox-group
                          :disabled="integralDetail.userIntegral <= 0"
                          v-model="use_integral"
                          @change="orderCheck"
                        >
                          <el-checkbox name="1"></el-checkbox>
                        </el-checkbox-group>
                        <div v-if="use_integral">
                          使用了{{ integralDetail.usedNum }}个积分,抵扣了<span
                            >{{ discountDetail.integralDiscountAmount }}元</span
                          >
                        </div>
                        <div v-else>
                          当前积分<span>{{ integralDetail.userIntegral }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="payment">
                    <div class="title">选择支付方式</div>
                    <div class="payment-item">
                      <div
                        v-for="(item, index) in payList"
                        :key="index"
                        class="item acea-row row-middle"
                        :class="{ selected: payType == item.pay_type }"
                        @click="payType = item.pay_type"
                      >
                        <div class="item-icon">
                          <img :src="item.image" alt="" />
                        </div>
                        <div>
                          <div class="name">{{ item.name }}</div>
                          <div v-if="item.pay_type == 'yue'" class="money">
                            余额：¥{{ now_money }}
                          </div>
                        </div>
                        <div
                          v-if="payType == item.pay_type"
                          class="item-selected"
                        >
                          <div class="iconfont-h5 icon-ic_complete"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="pay_amount">
                  <div class="title">金额明细</div>
                  <template v-if="!order_modify_price">
                    <div class="amount-item acea-row row-between">
                      <span class="name">支付金额</span>
                      <span class="value"
                        >¥{{ priceInfo.totalOriginalPrice || 0 }}</span
                      >
                    </div>
                    <div
                      v-if="discountDetail.couponsDiscountAmount"
                      class="amount-item acea-row row-between"
                    >
                      <span class="name">优惠券</span>
                      <span class="value"
                        >-¥{{ discountDetail.couponsDiscountAmount }}</span
                      >
                    </div>
                    <div
                      v-if="discountDetail.vipDiscountAmount"
                      class="amount-item acea-row row-between"
                    >
                      <span class="name">付费会员</span>
                      <span class="value"
                        >-¥{{ discountDetail.vipDiscountAmount }}</span
                      >
                    </div>
                    <div
                      v-if="use_integral"
                      class="amount-item acea-row row-between"
                    >
                      <span class="name">积分抵扣</span>
                      <span class="value"
                        >-¥{{ discountDetail.integralDiscountAmount }}</span
                      >
                    </div>
                    <div
                      v-if="discountDetail.updatedDiscountAmount"
                      class="amount-item acea-row row-between"
                    >
                      <span class="name">改价优惠</span>
                      <span class="value"
                        >-¥{{ discountDetail.updatedDiscountAmount }}</span
                      >
                    </div>
                    <div
                      v-if="priceInfo.totalShippingAmount > 0"
                      class="amount-item acea-row row-between"
                    >
                      <span class="name">运费</span>
                      <span class="value"
                        >¥{{ priceInfo.totalShippingAmount }}</span
                      >
                    </div>
                  </template>
                  <div class="pay_total acea-row row-between">
                    <span class="name">实付款</span>
                    <div class="pay_money">
                      ¥<span>{{
                        order_modify_price != ""
                          ? order_modify_price
                          : priceInfo.totalPaymentPrice
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="pay_footer">
                <el-button size="small" @click="remark">备注</el-button>
                <el-button type="primary" plain size="small" @click="cancel"
                  >取消</el-button
                >
                <el-button
                  v-if="order_id"
                  type="primary"
                  size="small"
                  @click="gotoPay"
                  >立即支付</el-button
                >
                <el-button
                  v-else
                  type="primary"
                  size="small"
                  @click="createOrder"
                  >提交订单</el-button
                >
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
    <!--添加地址-->
    <addAddress
      ref="addAddress"
      :uid="userInfo.uid"
      :touristId="touristId"
      @getAddressList="getAddressList"
    />
    <!--选择优惠券-->
    <selectCoupon
      ref="selectCoupon"
      :couponList="couponList"
      @changeCoupon="changeCoupon"
    />
    <!--商品规格-->
    <productAttr
      ref="attrs"
      :attr="attr"
      :storeInfo="storeInfo"
      :disabled="disabled"
      :isCart="isCart"
      @ChangeAttr="ChangeAttr"
      @ChangeCartNum="ChangeCartNum"
      @goCat="goCat"
    />
    <!--扫码支付-->
    <scanSteps
      ref="scanSteps"
      :orderId="order_id"
      :userInfo="userInfo"
      :payPrice="order_modify_price"
      :payType="payType"
      :priceInfo="priceInfo"
      @paySuccess="paySuccess"
    />
    <!--商品改价-->
    <changePrice
      ref="changePrice"
      :changeType="changeType"
      :cartIds="cart_ids"
      :userInfo="userInfo"
      @getCartList="getCartList"
      @updateOrderPrice="updateOrderPrice"
    />
    <!--余额支付验证弹窗-->
    <payVerify
      ref="payVerify"
      :payInfo="payInfo"
      :orderId="order_id"
      :userInfo="userInfo"
      @paySuccess="paySuccess"
    />
    <!--查询会员-->
    <searchMember ref="searchMember" @getUserDetail="getUserDetail" />
    <!--订单备注-->
    <el-dialog
      v-if="remarkVisible"
      title="备注"
      :visible.sync="remarkVisible"
      width="540px"
    >
      <el-form class="form">
        <el-form-item>
          <el-input
            type="textarea"
            v-model="mark"
            rows="5"
            placeholder="请输入备注信息"
            class="width100"
          ></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="remarkVisible = false" size="small">取消</el-button>
        <el-button type="primary" size="small" @click="remarkVisible = false"
          >确定</el-button
        >
      </span>
    </el-dialog>
    <!--线下付款-->
    <template v-if="offlineDialog">
      <div class="offline-result">
        <div class="result-main">
          <div>
            <div class="iconfont-h5 icon-a-ic_CompleteSelect"></div>
            <div class="text">{{ resultInfo }}</div>
          </div>
          <div class="result-footer acea-row row-between row-midddle">
            <router-link
              class="foot-btn"
              :to="{ path: `${roterPre}` + '/order/list' }"
            >
              <span>查看订单</span>
            </router-link>
            <span class="foot-btn" @click="continueOrder">继续下单</span>
          </div>
        </div>
        <span
          class="iconfont-h5 icon-ic_close"
          @click="
            offlineDialog = false;
            continueOrder();
          "
        ></span>
      </div>
      <div class="result-mask"></div>
    </template>
  </div>
</template>
<script>
import {
  getCategoryLst,
  getProductLst,
  getProductDetail,
  orderAddressLst,
  orderCartAdd,
  orderCartLst,
  orderCartNum,
  orderCartChange,
  orderCartDelete,
  orderCartClear,
  orderCheckCaluate,
  orderPayConfig,
  orderCreate,
  getUserInfo,
  orderPayApi
} from "@/api/order";
import addAddress from "./components/addAddress";
import selectCoupon from "./components/selectCoupon";
import productAttr from "./components/productAttr";
import scanSteps from "./components/scanSteps";
import changePrice from "./components/changePrice";
import payVerify from "./components/payVerify";
import searchMember from "./components/searchMember";
import { roterPre } from "@/settings";
export default {
  name: "CustomerOrder",
  components: {
    addAddress,
    selectCoupon,
    productAttr,
    scanSteps,
    changePrice,
    payVerify,
    searchMember
  },
  data() {
    return {
      roterPre: roterPre,
      resultInfo: "支付成功",
      categoryList: [],
      couponList: [],
      tableData: {
        data: [],
        total: 30
      },
      tableFrom: {
        search: "",
        mer_cate_id: "",
        page: 1,
        limit: 18
      },
      cartList: [],
      avatar: require("@/assets/images/f.png"),
      userInfo: {
        uid: 0,
        avatar: require("@/assets/images/f.png"),
        nickname: "散客"
      },
      cartSum: 0,
      activeHangon: -1,
      isFit: true,
      invalidList: [],
      cart_ids: [],
      delivery_type: "1",
      delivery_free: false,
      addressList: [],
      addressInfo: "",
      address_user: "",
      storeInfo: {}, //商品信息
      attr: {
        productAttr: {},
        productSelect: {}
      },
      isCart: 0,
      cartInfo: {
        //更改属性所需参数
        cart_id: 0,
        product_id: 0,
        cart_num: 0
      },
      disabled: false, //阻止属性弹窗多次提交
      productId: "",
      step: 1,
      use_integral: false,
      payType: "weixinQr",
      now_money: "200.00",
      payMode: [
        {
          name: "余额支付",
          pay_type: "balance",
          image: require("@/assets/images/pay_yue.png"),
          status: 0
        },
        {
          name: "微信支付",
          pay_type: "weixinQr",
          image: require("@/assets/images/pay_weixin.png"),
          status: 1
        },
        {
          name: "支付宝支付",
          pay_type: "alipayQr",
          image: require("@/assets/images/pay_ali.png"),
          status: 1
        },
        {
          name: "线下支付",
          pay_type: "offline",
          image: require("@/assets/images/pay_offline.png"),
          status: 1
        }
      ],
      remarkVisible: false,
      offlineDialog: false,
      attrValue: "", //已选属性
      touristId:
        localStorage.getItem("touristId") || this.generateUniqueRandomString(),
      delivery_way: 4,
      priceInfo: {
        totalPaymentPrice: 0,
        totalDiscountAmount: 0,
        totalShippingAmount: 0
      },
      discountDetail: {},
      integralDetail: {},
      subCoupon: [],
      checkedCoupons: "",
      changeType: "",
      change_fee_type: "",
      orderKey: "",
      payInfo: {
        price: "",
        phone: ""
      },
      order_id: 0,
      order_modify_price: "",
      mark: "",
      address_id: "",
      yueStatus: 0,
      updatedPriceFlag: 0,
      is_wechat_v3: false
    };
  },
  computed: {
    payList: {
      get() {
        return this.payMode.filter(item => item.status == 1);
      },
      set(newValue) {
        return newValue;
      }
    }
  },
  watch: {
    cartList: {
      handler: function(val) {
        this.getProductCartNum(this.cartList, this.tableData.data);
      },
      immediate: false,
      deep: true
    },
    "tableData.data": {
      handler: function(val) {
        this.getProductCartNum(this.cartList, this.tableData.data);
      },
      immediate: false,
      deep: true
    },
    "userInfo.uid": {
      handler: function(val) {
        this.payMode[0]["status"] = val == 0 ? 0 : this.yueStatus;
        this.payList = this.payMode.filter(item => item.status == 1);
      },
      immediate: false,
      deep: true
    }
  },
  created() {},
  mounted() {
    this.userInfo =
      JSON.parse(localStorage.getItem("userInfo")) || this.userInfo;
    this.isFit = this.userInfo.uid == 0 ? true : false;
    this.getCateList();
    localStorage.setItem("touristId", this.touristId);
    this.getCartList();
    this.$nextTick(() => {
      this.getList();
    });
    this.getAddressList();
    this.getPayConfig();
  },
  methods: {
    goBack() {
      this.$router.back();
    },
    /**获取商品分类 */
    getCateList() {
      getCategoryLst()
        .then(res => {
          this.categoryList = [
            { cate_name: "全部商品", store_category_id: "" },
            ...res.data
          ];
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    goodsSearch(item) {
      this.tableFrom.mer_cate_id = item.store_category_id;
      this.getList(1);
    },
    /**获取商品列表 */
    getList(num) {
      this.tableFrom.page = num || this.tableFrom.page;
      getProductLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    /**商品列表加购数量 */
    getProductCartNum(arr1, arr2) {
      arr2.forEach(item2 => {
        // 在第一个数组中查找相同 product_id 的对象
        const matchedItem = arr1.find(
          item1 => item1.product_id === item2.product_id
        );
        // 如果找到，将 cart_num 添加到第二个数组的对象中
        if (matchedItem) {
          item2.cart_num = matchedItem.cart_num;
        } else {
          item2.cart_num = "";
        }
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
    /**散客 */
    fitUsers() {
      if (this.isFit) return;
      this.isFit = true;
      this.step = 1;
      this.userInfo = {
        uid: 0,
        avatar: require("@/assets/images/f.png"),
        nickname: "散客",
        phone: ""
      };
      this.addressInfo = "";
      this.address_id = "";
      localStorage.setItem("userInfo", JSON.stringify(this.userInfo));
      this.touristId = this.generateUniqueRandomString();
      localStorage.setItem("touristId", this.touristId);
      this.getCartList();
      this.getAddressList();
    },
    /**切换用户 */
    switchUsers() {
      this.$refs.searchMember.dialogVisible = true;
      this.$refs.searchMember.keyword = "";
      if (this.userInfo.uid == 0) this.$refs.searchMember.resetData();
      this.step = 1;
    },
    /** 获取选择的用户信息 */
    getUserDetail(data) {
      this.userInfo = data;
      localStorage.setItem("userInfo", JSON.stringify(data));
      this.isFit = false;
      this.getAddressList();
      this.getCartList();
    },
    changeCoupon(item, index) {
      this.subCoupon =
        this.subCoupon.length > 0 ? this.subCoupon : [[], [], []];
      this.getCheckedCoupons(item);
      this.orderCheck();
    },
    /**获取选中优惠券 */
    getCheckedCoupons(item) {
      if (!item.checked) {
        item.checked = true;
        // this.couponList[index] = item
        // 如果 checked 为 true，根据 type 将元素添加到不同的数组
        switch (item.coupon.type) {
          case 0: //店铺券
            this.subCoupon[1] = [item.coupon_user_id];
            break;
          case 1: //商品券
            if (!this.subCoupon[2].includes(item.coupon_user_id))
              this.subCoupon[2].push(item.coupon_user_id);
            break;
          default:
            //平台券
            this.subCoupon[0] = [item.coupon_user_id];
        }
      } else {
        item.checked = false;
        // 如果 checked 为 false，根据 type 从不同的数组中移除元素
        switch (item.coupon.type) {
          case 0: //店铺券
            this.subCoupon[1] = [];
            break;
          case 1: //商品券
            if (this.subCoupon[2].includes(item.coupon_user_id))
              this.subCoupon[2] = this.subCoupon[2].filter(
                itm => itm !== item.coupon_user_id
              );
            break;
          default:
            //平台券
            this.subCoupon[0] = [];
        }
      }
    },
    /**根据接口返回的优惠券设置subCoupon的值 */
    setSubCoupon(couponList) {
      this.subCoupon = [[], [], []];
      couponList.forEach((item, index) => {
        if (item.checked) {
          // 如果 checked 为 true，根据 type 将元素添加到不同的数组
          switch (item.coupon.type) {
            case 0: //店铺券
              this.subCoupon[1] = [item.coupon_user_id];
              break;
            case 1: //商品券
              if (!this.subCoupon[2].includes(item.coupon_user_id))
                this.subCoupon[2].push(item.coupon_user_id);
              break;
            default:
              //平台券
              this.subCoupon[0] = [item.coupon_user_id];
          }
        } else {
          // 如果 checked 为 false，根据 type 从不同的数组中移除元素
          switch (item.coupon.type) {
            case 0: //店铺券
              this.subCoupon[1] = [];
              break;
            case 1: //商品券
              if (this.subCoupon[2].includes(item.coupon_user_id))
                this.subCoupon[2] = this.subCoupon[2].filter(
                  itm => itm !== item.coupon_user_id
                );
              break;
            default:
              //平台券
              this.subCoupon[0] = [];
          }
        }
      });
    },
    /**地址列表 */
    getAddressList() {
      let data = {
        uid: this.userInfo.uid || 0
      };
      if (data.uid == 0) data.tourist_unique_key = this.touristId;
      orderAddressLst(data)
        .then(res => {
          this.addressList = res.data.list;
          if (res.data.list.length > 0) {
            const defaultItem = res.data.list.find(
              item => item.is_default === 1
            );
            this.address_id = defaultItem
              ? defaultItem.address_id
              : res.data.list[0]["address_id"];
            this.address_user = defaultItem
              ? defaultItem.real_name
              : res.data.list[0]["real_name"];
            this.addressInfo = defaultItem
              ? defaultItem.province +
                defaultItem.city +
                defaultItem.street +
                defaultItem.district +
                defaultItem.detail
              : res.data.list[0]["province"] +
                res.data.list[0]["city"] +
                res.data.list[0]["street"] +
                res.data.list[0]["district"] +
                res.data.list[0]["detail"];
          }
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    /**打开优惠券弹窗 */
    openCoupon() {
      this.$refs.selectCoupon.dialogVisible = true;
    },
    // 选择属性
    attrTap(item) {
      this.disabled = false;
      if (this.userInfo && this.userInfo.uid >= 0) {
        this.productId = item.product_id;
        if (!item.stock) return this.$message.error("暂无库存");
        this.isCart = 0; //判断切换属性或是加入购物车：0加入购物车；1切换属性
        this.$refs.attrs.dialogVisible = true;
        // this.attr.productSelect.cart_num = 1
        this.goodsInfo(item.product_id);
      } else {
        this.$message.error("请添加或选择用户");
      }
    },
    // 商品详情
    goodsInfo(id) {
      getProductDetail(id)
        .then(res => {
          let data = res.data;
          this.storeInfo = data;
          this.productValue = data.sku;
          this.$set(this.attr, "productAttr", data.attr);
          this.DefaultSelect(id);
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    /**
     * 默认选中属性
     *
     */
    DefaultSelect: function(id) {
      let hasCart = this.isHasCart(this.cartList, id);
      let productAttr = this.attr.productAttr;
      let value = [];
      let found = false;
      for (var key in this.productValue) {
        if (this.productValue[key].is_default_select == 1) {
          value = this.attr.productAttr.length ? key.split(",") : [];
          found = true;
          break;
        }
      }
      if (!found) {
        for (var key in this.productValue) {
          value = this.attr.productAttr.length ? key.split(",") : [];
          found = true;
          break;
        }
      }
      for (let i = 0; i < productAttr.length; i++) {
        this.$set(productAttr[i], "index", value[i]);
      }
      //isCart 1为触发购物车 0为商品
      if (this.isCart) {
        //购物车默认打开时，随着选中的属性改变
        let attrValue = [];
        this.cartList.forEach(item => {
          if (item.cart_id == this.cartInfo.cart_id) {
            // attrValue = item.productInfo.attrInfo.suk.split(",");
            attrValue = item.productAttr.sku.split(",");
          }
        });
        for (let i = 0; i < productAttr.length; i++) {
          this.$set(productAttr[i], "index", attrValue[i]);
        }
      } else {
        for (let i = 0; i < productAttr.length; i++) {
          this.$set(productAttr[i], "index", value[i]);
        }
      }
      //sort();排序函数:数字-英文-汉字；
      let productSelect = this.productValue[value.join(",")];
      if (productSelect && productAttr.length) {
        this.$set(
          this.attr.productSelect,
          "store_name",
          this.storeInfo.store_name
        );
        this.$set(this.attr.productSelect, "image", productSelect.image);
        this.$set(this.attr.productSelect, "price", productSelect.price);
        this.$set(
          this.attr.productSelect,
          "svip_price",
          productSelect.svip_price
        );
        this.$set(this.attr.productSelect, "stock", productSelect.stock);
        this.$set(this.attr.productSelect, "unique", productSelect.unique);
        this.$set(
          this.attr.productSelect,
          "cart_num",
          hasCart ? hasCart.cart_num : 1
        );
        this.$set(this, "attrValue", value.join(","));
      } else if (!productSelect && productAttr.length) {
        this.$set(
          this.attr.productSelect,
          "store_name",
          this.storeInfo.store_name
        );
        this.$set(this.attr.productSelect, "image", this.storeInfo.image);
        this.$set(this.attr.productSelect, "price", this.storeInfo.price);
        this.$set(this.attr.productSelect, "svip_price", "");
        this.$set(this.attr.productSelect, "stock", 0);
        this.$set(this.attr.productSelect, "unique", "");
        this.$set(this.attr.productSelect, "cart_num", 0);
        this.$set(this, "attrValue", "");
      } else if (!productSelect && !productAttr.length) {
        this.$set(
          this.attr.productSelect,
          "store_name",
          this.storeInfo.store_name
        );
        this.$set(this.attr.productSelect, "image", this.storeInfo.image);
        this.$set(this.attr.productSelect, "price", this.storeInfo.price);
        this.$set(this.attr.productSelect, "svip_price", "");
        this.$set(this.attr.productSelect, "stock", this.storeInfo.stock);
        this.$set(
          this.attr.productSelect,
          "unique",
          this.storeInfo.unique || ""
        );
        this.$set(
          this.attr.productSelect,
          "cart_num",
          hasCart ? hasCart.cart_num : 1
        );
        this.$set(this, "attrValue", "");
      } else if (productSelect && !productAttr.length) {
        this.$set(
          this.attr.productSelect,
          "store_name",
          this.storeInfo.store_name
        );
        this.$set(this.attr.productSelect, "image", productSelect.image);
        this.$set(this.attr.productSelect, "price", productSelect.price);
        this.$set(
          this.attr.productSelect,
          "svip_price",
          productSelect.svip_price
        );
        this.$set(this.attr.productSelect, "stock", productSelect.stock);
        this.$set(this.attr.productSelect, "unique", productSelect.unique);
        this.$set(
          this.attr.productSelect,
          "cart_num",
          hasCart ? hasCart.cart_num : 1
        );
        this.$set(this, "attrValue", value.join(","));
      }
    },
    isHasCart(arr, id) {
      return arr[arr.findIndex(item => item.product.product_id == id)];
    },
    /**修改价格 */
    changePrice(data, type) {
      this.$refs.changePrice.initData(data, type);
    },
    /**整单改价 */
    updateOrderPrice(data) {
      this.order_modify_price = data.price;
      this.change_fee_type = data.type;
    },
    /**购物车删除 */
    delCart(item, index) {
      this.$confirm("确定从下单列表删除商品吗?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      })
        .then(() => {
          orderCartDelete(item.cart_id)
            .then(res => {
              this.$message.success(res.message);
              this.getCartList();
            })
            .catch(res => {
              this.$message.error(res.message);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消"
          });
        });
    },
    /**备注 */
    remark() {
      this.remarkVisible = true;
    },
    /**清空购物车 */
    delAll() {
      this.$confirm("确定从下单列表清空所有商品吗?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      })
        .then(() => {
          let data = { uid: this.userInfo.uid || 0 };
          if (data.uid == 0) data.tourist_unique_key = this.touristId;
          orderCartClear(data)
            .then(res => {
              this.$message.success(res.message);
              this.getCartList();
              this.step = 1;
              this.use_integral = false;
            })
            .catch(res => {
              this.$message.error(res.message);
            });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消"
          });
        });
    },
    /**
     * 属性变动赋值
     *
     */
    ChangeAttr(res) {
      let productSelect = this.productValue[res];
      if (productSelect && productSelect.stock > 0) {
        this.$set(this.attr.productSelect, "image", productSelect.image);
        this.$set(this.attr.productSelect, "price", productSelect.price);
        this.$set(this.attr.productSelect, "stock", productSelect.stock);
        this.$set(this.attr.productSelect, "unique", productSelect.unique);
        this.$set(this.attr.productSelect, "cart_num", 1);
        this.$set(
          this.attr.productSelect,
          "svip_price",
          productSelect.svip_price
        );
        this.$set(this, "attrValue", res);
      } else {
        this.$set(this.attr.productSelect, "image", this.storeInfo.image);
        this.$set(this.attr.productSelect, "price", this.storeInfo.price);
        this.$set(this.attr.productSelect, "stock", 0);
        this.$set(this.attr.productSelect, "unique", "");
        this.$set(this.attr.productSelect, "cart_num", 0);
        this.$set(
          this.attr.productSelect,
          "svip_price",
          this.storeInfo.svip_price
        );
        this.$set(this, "attrValue", "");
      }
    },
    goCat(e) {
      if (e) {
        this.changeCartAttr();
      } else {
        this.joinCart(1);
      }
    },
    changeCartAttr() {
      let unique =
        this.attr.productSelect !== undefined
          ? this.attr.productSelect.unique
          : "";
      let cart_id = this.cartInfo.cart_id;
      let data = {
        product_attr_unique: unique,
        uid: this.userInfo.uid || 0,
        cart_num: this.cartInfo.cart_num
      };
      orderCartChange(cart_id, data)
        .then(res => {
          this.disabled = true;
          this.$message.success(res.message);
          this.$refs.attrs.dialogVisible = false;
          this.getCartList();
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    // 点击切换属性
    cartAttr(item) {
      this.disabled = false;
      this.$refs.attrs.dialogVisible = true;
      this.isCart = 1;
      this.cartInfo.cart_id = item.cart_id;
      this.cartInfo.cart_num = item.cart_num;
      this.cartInfo.product_id = item.product_id;
      this.goodsInfo(item.product_id);
    },
    // 加入购物车
    joinCart(num) {
      let that = this;
      if (num) {
        let productSelect = that.productValue[this.attrValue];
        //如果有属性,没有选择,提示用户选择
        if (that.attr.productAttr.length && productSelect === undefined) {
          return this.$message.warning("产品库存不足，请选择其它");
        }
      }
      let uid = this.userInfo.uid || 0;
      let data = {
        uid: uid,
        product_id: this.productId,
        cart_num: that.attr.productSelect.cart_num,
        product_attr_unique: num
          ? this.attr.productSelect !== undefined
            ? this.attr.productSelect.unique
            : ""
          : ""
      };
      if (uid == 0) data.tourist_unique_key = this.touristId;
      orderCartAdd(data)
        .then(res => {
          this.$refs.attrs.dialogVisible = false;
          this.$message.success("添加购物车成功");
          // this.cart_id = res.data.cart_id
          this.getCartList();
          this.tableFrom.keywords = "";
          this.getList();
          this.disabled = true;
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    getCartNum() {
      let data = { uid: this.userInfo.uid || 0 };
      if (data.uid == 0) data.tourist_unique_key = this.touristId;
      orderCartNum(data)
        .then(res => {
          this.cartSum = res.data[0].count;
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    generateUniqueRandomString(length = 16) {
      const timestamp = Date.now().toString(36); // 将时间戳转换为36进制字符串
      const randomPart = Math.random()
        .toString(36)
        .substr(2, length - timestamp.length); // 生成随机部分
      return (timestamp + randomPart).slice(0, length);
    },
    /**订单计算金额 */
    orderCheck() {
      let data = {
        uid: this.userInfo.uid || 0,
        cart_ids: this.cart_ids,
        address_id: this.address_id,
        delivery_way: this.delivery_way,
        is_free_shipping: this.delivery_free ? 1 : 0,
        use_integral: this.use_integral ? 1 : 0
        // use_coupon: this.subCoupon,
      };
      if (this.subCoupon.length) data.use_coupon = this.subCoupon;
      if (data.uid == 0) data.tourist_unique_key = this.touristId;
      orderCheckCaluate(data)
        .then(res => {
          this.priceInfo = res.data;
          this.couponList = res.data.couponList || [];
          this.discountDetail = res.data.discountDetail;
          this.integralDetail = res.data.integral;
          this.orderKey = res.data.key;
          this.updatedPriceFlag = res.data.updatedPriceFlag;
          if (res.data.couponList && res.data.couponList.length > 0) {
            this.checkedCoupons = res.data.couponList.filter(
              item => item.checked === true
            );
          }
          if (this.checkedCoupons.length > 0) {
            this.setSubCoupon(this.checkedCoupons);
          }
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    changeDelivery(name) {
      this.delivery_way = name;
      this.orderCheck();
    },
    /**选择收货地址 */
    changeAddress(name) {
      if (name == "add") {
        this.$refs.addAddress.open();
      } else {
        this.address_id = name;
        const item = this.addressList.find(obj => obj.address_id === name);
        this.addressInfo =
          item.province + item.city + item.street + item.district + item.detail;
        this.address_user = item.real_name;
      }
    },
    // 购物车列表
    getCartList() {
      let uid = this.userInfo.uid || 0;
      let data = { uid: uid };
      if (uid == 0) data.tourist_unique_key = this.touristId;
      if (uid >= 0) {
        orderCartLst(data)
          .then(res => {
            this.cartList = res.data.list;
            this.invalidList = res.data.fail;
            const cartIds = this.cartList.map(item => item.cart_id);
            this.cart_ids = cartIds.length ? cartIds : [];
            this.getCartNum();
            if (res.data.list.length) {
              this.orderCheck();
            } else {
              this.clear();
            }
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      } else {
        this.$message.error("请添加或选择用户");
      }
    },
    /**继续下单 */
    continueOrder() {
      this.offlineDialog = false;
      this.step = 1;
      this.order_id = 0;
      this.use_integral = false;
      this.getCartList();
    },
    cancel() {
      if (this.order_id) {
        this.continueOrder();
      } else {
        this.step = 1;
        this.order_id = 0;
      }
    },
    clear() {
      this.priceInfo = {
        totalDiscountAmount: 0,
        totalPaymentPrice: 0,
        totalShippingAmount: 0
      };
      this.couponList = [];
      this.discountDetail = {
        couponsDiscountAmount: 0,
        vipDiscountAmount: 0
      };
      this.integralDetail = {};
      this.orderKey = "";
      this.checkedCouponsText = "";
      this.tableFrom.mer_cate_id = "";
    },
    ChangeCartNum(changeValue) {
      //changeValue:是否 加|减
      //获取当前变动属性
      let productSelect = this.productValue[this.attrValue];
      //如果没有属性,赋值给商品默认库存
      if (productSelect === undefined && !this.attr.productAttr.length)
        productSelect = this.attr.productSelect;
      //无属性值即库存为0；不存在加减；
      if (productSelect === undefined) return;
      let stock = productSelect.stock || 0;
      let num = this.attr.productSelect;
      if (changeValue) {
        num.cart_num++;
        if (num.cart_num > stock) {
          this.$set(this.attr.productSelect, "cart_num", stock);
          this.$set(this, "cart_num", stock);
        }
      } else {
        num.cart_num--;
        if (num.cart_num < 1) {
          this.$set(this.attr.productSelect, "cart_num", 1);
          this.$set(this, "cart_num", 1);
        }
      }
      this.$refs.attrs.getCartNum(num);
      this.cartInfo.cart_num = num.cart_num;
    },
    calculate(item, type) {
      if (type === "reduce" && item.cart_num > 1) {
        item.cart_num--;
      } else if (type === "add" && item.cart_num < item.productAttr.stock) {
        item.cart_num++;
      } else {
        return this.$message.error(
          item.cart_num === 1 ? "数量最小为1" : "库存不足"
        );
      }
      let data = {
        cart_num: item.cart_num,
        uid: this.userInfo.uid || 0
      };
      orderCartChange(item.cart_id, data)
        .then(res => {
          this.$message.success(res.message);
          this.getCartList();
          this.getList();
        })
        .catch(err => {
          if (type === "reduce" && item.cart_num > 1) {
            item.cart_num++;
          } else if (type === "add" && item.cart_num < item.productAttr.stock) {
            item.cart_num--;
          }
          this.$message.error(err.message);
        });
    },
    changeCart(e, item) {
      let cart_id = item.cart_id;
      let data = {
        cart_num: item.cart_num,
        uid: this.userInfo.uid || 0
      };
      orderCartChange(cart_id, data)
        .then(res => {
          this.$message.success(res.message);
          this.getCartList();
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    /**去结算 */
    goPay() {
      if (!this.address_id && this.delivery_way == 0) {
        this.$message.error("请先添加地址！");
        return;
      }
      this.step = 2;
    },
    /**生成订单并提交 */
    createOrder() {
      let uid = this.userInfo.uid || 0;
      let data = {
        uid: uid,
        address_id: this.address_id,
        delivery_way: this.delivery_way,
        cart_ids: this.cart_ids,
        key: this.orderKey,
        pay_type: this.payType,
        old_pay_price: this.priceInfo.totalPaymentPrice,
        // use_coupon: this.subCoupon,
        change_fee_type: this.change_fee_type,
        new_pay_price: this.order_modify_price,
        mark: this.mark
      };
      if (this.subCoupon.length) data.use_coupon = this.subCoupon;
      if (uid == 0) data.tourist_unique_key = this.touristId;
      orderCreate(data)
        .then(res => {
          this.order_id = res.data.order_id;
          if (uid != 0) this.getUserInfo(uid);
          this.payOrder();
          this.$message.success(res.message);
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    gotoPay() {
      this.payOrder();
    },
    payOrder() {
      if (
        (this.order_modify_price === 0 && this.change_fee_type === 0) ||
        (this.priceInfo.totalPaymentPrice == 0 && this.change_fee_type !== 0)
      ) {
        this.resultInfo = "支付成功";
        this.offlineDialog = true;
        this.$message.success("支付成功");
        this.subCoupon = [];
        return;
      }
      if (this.payType == "weixinQr" || this.payType == "alipayQr") {
        this.$refs.scanSteps.open(this.is_wechat_v3);
        // this.$refs.scanSteps.open(false)
      } else if (this.payType == "balance") {
        this.$refs.payVerify.dialogVisible = true;
        this.payInfo = {
          price: this.priceInfo.totalPaymentPrice,
          phone: this.userInfo.phone
        };
      } else {
        orderPayApi(this.order_id, {
          pay_type: this.payType,
          uid: this.userInfo.uid || 0
        })
          .then(res => {
            this.resultInfo = "客户支付后，请点击查看订单确认支付";
            this.offlineDialog = true;
            this.subCoupon = [];
            this.clearOrderInfo();
            if (this.delivery_way == 0) {
              this.getAddressList();
              this.delivery_way = 4;
            }
          })
          .catch(err => {
            if (err.data.status == 400) {
              this.$message.error(err.data.data || err.data.message);
            } else {
              this.$message.error(err.message);
            }
          });
      }
    },
    getUserInfo(uid) {
      getUserInfo({ uid: uid })
        .then(res => {
          this.userInfo = res.data;
          localStorage.setItem("userInfo", JSON.stringify(res.data));
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    /**支付配置 */
    getPayConfig() {
      orderPayConfig({ uid: this.userInfo.uid || 0 })
        .then(res => {
          this.yueStatus = res.data.yue_pay_status;
          this.payMode[0].status = res.data.yue_pay_status;
          this.payMode[1].status = res.data.pay_weixin_open;
          this.payMode[2].status = res.data.alipay_open;
          this.payMode[3].status = res.data.offline_switch;
          this.is_wechat_v3 = res.data.is_wechat_v3;
          this.now_money = res.data.now_money;
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    paySuccess() {
      this.offlineDialog = true;
      this.resultInfo =
        this.payType == "offline"
          ? "客户支付后，请点击查看订单确认支付"
          : "支付成功";
      this.clearOrderInfo();
    },
    clearOrderInfo() {
      this.touristId = this.generateUniqueRandomString();
      this.subCoupon = [];
      localStorage.setItem("touristId", this.touristId);
      this.order_id = 0;
    }
  }
};
</script>
<style>
.el-main {
  padding: 0;
}
.product-input .el-input__inner {
  border-radius: 18px;
}
.cart-input .el-input__inner {
  padding: 0;
  width: 100%;
  height: 100%;
  text-align: center;
  border-radius: 0;
  background-color: transparent;
}
</style>
<style lang="scss" scoped>
.customer-order-page {
  min-height: 100vh;
  background: #f0f2f5;
}
.box-header {
  background: linear-gradient(90deg, #4073fa 0%, #40cbfa 100%);
  height: 68px;
  padding: 0 20px;
  display: flex;
  color: #fff;
}
.after-line {
  display: inline-block;
  position: relative;
  margin-right: 16px;
  cursor: pointer;
}
.after-line:after {
  content: "";
  position: absolute;
  top: 3px;
  right: -16px;
  width: 1px;
  height: 16px;
  background: #eee;
}
.font-sm {
  font-size: 14px;
}
.ht_title {
  font-weight: 500;
  font-size: 18px;
}
.order-main {
  padding: 14px 14px 0;
}
.order-content {
  height: calc(100vh - 96px);
}
.customer-left {
  width: 435px;
  height: 100%;
  background: #fff;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  &.nopointer {
    cursor: not-allowed;
    position: relative;
    &::before {
      content: "";
      display: block;
      width: 100%;
      height: 100%;
      background: #f0f2f5;
      opacity: 0.5;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 10;
    }
  }
}
.customer-right {
  flex: 1;
  height: 100%;
  margin-left: 14px;
  .product-cate {
    width: 138px;
    height: calc(100vh - 96px);
    margin-left: 14px;
    background: #fff;
    border-radius: 6px;
    padding: 21px 19px;
    text-align: center;
    overflow-x: hidden;
    .cate-name {
      width: 100px;
      height: 40px;
      line-height: 40px;
      color: #303133;
      cursor: pointer;
      &.on {
        color: #fff;
        border-radius: 4px;
        background: var(--prev-color-primary);
      }
    }
  }
  .product-main {
    flex: 1;
    position: relative;
    .product-search {
      // width: 100%;
      width: calc(100% - 3.5px);
      height: 66px;
      background: #fff;
      border-radius: 6px;
      padding: 0 20px;
    }
  }
  .product-list {
    flex-wrap: wrap;
    max-height: calc(100vh - 200px);
    overflow-x: hidden;
    padding-top: 14px;
    .item {
      width: calc(100% / 3 - 7px); /* 每个元素的宽度，减去你想要的间距 */
      background: #fff;
      border-radius: 6px;
      padding: 15px 14px;
      align-items: center;
      position: relative;
      margin-right: 10px;
      cursor: pointer;
      &:nth-child(3n) {
        margin-right: 0;
      }
      .picture {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        .image {
          width: 100px;
          height: 100px;
          border-radius: 8px;
        }
        .trip {
          width: 60px;
          height: 60px;
          background: rgba(0, 0, 0, 0.6);
          border-radius: 100%;
          position: absolute;
          top: 50%;
          left: 50%;
          margin: -30px 0 0 -30px;
          display: flex;
          align-items: center;
          flex-direction: column;
          justify-content: center;
          color: #fff;
          font-size: 12px;
        }
      }
      .no-stock {
        top: 0;
        left: 0;
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .icon-cart-num {
        position: absolute;
        top: -8px;
        right: 0;
        background-color: var(--prev-color-primary);
        border-radius: 30px;
        padding: 0 8px;
        height: 22px;
        color: #fff;
        text-align: center;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .list-text {
        margin-left: 10px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        padding: 2px 0 5px;
      }
      .name {
        color: #303133;
        font-size: 14px;
      }
      .price-count {
        margin-top: 12px;
        .price {
          font-size: 16px;
          color: #ff7700;
          font-weight: 500;
          span {
            font-size: 13px;
          }
        }
        .svip {
          font-size: 12px;
          color: #303133;
          margin-left: 10px;
        }
        img {
          width: 33px;
          height: 14px;
          margin-left: 4px;
        }
      }
      .stock {
        color: #606266;
        font-size: 12px;
        margin-top: 12px;
      }
    }
  }
}
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px transparent;
}
::-webkit-scrollbar {
  width: 4px !important; /*对垂直流动条有效*/
}
.product-page {
  padding: 14px;
  background: #fff;
  margin-top: 22px;
  // width: 100%;
  width: calc(100% - 3.5px);
  border-radius: 6px;
  position: absolute;
  left: 0;
  bottom: 0;
  .el-pagination {
    margin-top: 0;
  }
}
.cart {
  position: relative;
  display: flex;
  flex-direction: column;
  height: 100%;
  max-width: 500px;
  .title {
    flex-shrink: 0;
    height: 90px;
    background: #f2f6ff;
    border: 2px solid var(--prev-color-primary);
    border-radius: 10px;
    margin: 27px 20px 20px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    overflow: hidden;
    padding: 23px 14px 20px 14px;
    .picture {
      width: 50px;
      height: 50px;
      border-radius: 100%;
      margin-right: 15px;
      img {
        width: 100%;
        height: 100%;
        border-radius: 100%;
      }
    }
    .text {
      flex: 1;
      color: #303133;
      .textCon {
        display: flex;
        align-items: center;
        flex: 1;
        .text-wrap {
          flex: 1;
          display: flex;
          flex-direction: column;
          justify-content: center;
        }
        .name-wrap {
          color: #303133;
          font-size: 16px;
          .phone {
            max-width: 150px;
          }
        }
        .name {
          font-size: 13px;
          color: #606266;
          margin-top: 3px;
        }
      }
      .balance {
        font-size: 13px;
        color: #606266;
        margin-top: 4px;
        .num {
          margin-top: 2px;
        }
      }
    }
    .textBtn {
      width: 130px;
      .btn {
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: 4px;
        font-size: 13px;
        cursor: pointer;
      }
      .btn-orange {
        width: 46px;
        background: #ff7700;
        margin-right: 6px;
      }
      .btn-blue {
        width: 76px;
        background: var(--prev-color-primary);
      }
    }
  }
  .count {
    padding: 0 20px 20px 23px;
    border-bottom: 1px solid #eeeeee;
    .cart-sel {
      color: #303133;
      .num {
        color: #ff7700;
        margin: 0 4px;
        font-weight: 600;
      }
    }
    .count-r {
      color: #606266;
      font-size: 13px;
      cursor: pointer;
      .image {
        width: 14px;
        height: 15px;
        margin-right: 7px;
      }
    }
  }
}
.dropdown-item-name {
  width: 85px;
  font-size: 13px;
  margin-right: 40px;
}
.dropdown-item-value {
  color: #ff7700;
}
.listCon {
  overflow-x: hidden;
  flex: 1;
  .cart-coupon {
    align-items: center;
    padding: 10px 16px 10px 20px;
    color: #606266;
    font-size: 13px;
    .coupon-more {
      color: var(--prev-color-primary);
      cursor: pointer;
      .activity {
        height: 20px;
        padding: 0 10px;
        border: 1px solid var(--prev-color-primary);
        color: var(--prev-color-primary);
        font-size: 12px;
        display: flex;
        align-items: center;
        position: relative;
        margin: 9px 0 9px 7px;
        &::before,
        &::after {
          content: " ";
          position: absolute;
          width: 6px;
          height: 6px;
          border-radius: 100%;
          background-color: #fff;
          bottom: 50%;
          margin-bottom: -3px;
          border: 1px solid var(--prev-color-primary);
        }
        &::before {
          border-left-color: #ffffff;
          left: -2px;
        }
        &::after {
          border-right-color: #ffffff;
          right: -3px;
        }
      }
      .icon-ic_rightarrow {
        color: #303133;
        font-size: 13px;
        margin: 2px 0 0 4px;
      }
    }
  }
  .item {
    padding: 0 20px 0 23px;
    &:hover {
      background: #f2f6ff;
    }
    .item-count {
      border-bottom: 1px dashed #eeeeee;
      padding: 20px 0;
    }
    .promotions {
      width: 284px;
      .picture {
        width: 74px;
        height: 74px;
        img {
          width: 100%;
          height: 100%;
          border-radius: 4px;
        }
      }
      .text {
        width: 200px;
        .name {
          color: #303133;
          font-size: 15px;
        }
        .info {
          cursor: pointer;
          margin-top: 6px;
          display: flex;
          align-items: center;
          color: #909399;
          font-size: 12px;
          .suk {
            max-width: 50%;
          }
          .iconxiayi {
            font-size: 12px;
            margin-left: 5px;
          }
        }
        .sum_price {
          margin-top: 13px;
          .price {
            color: #303133;
            font-size: 16px;
            font-weight: 500;
          }
          .orange {
            color: #ff7700;
            margin-left: 20px;
          }
          .svip-img {
            width: 33px;
            height: 14px;
            margin-left: 8px;
          }
        }
      }
    }
    .cartOpt {
      text-align: right;
      .opt-item {
        color: var(--prev-color-primary);
        font-size: 13px;
        padding: 0 8px;
        border-right: 1px solid rgba(64, 115, 250, 0.2);
        cursor: pointer;
        &:last-child {
          padding-right: 0;
          border: none;
        }
        &.disabled {
          cursor: not-allowed;
          opacity: 0.6;
        }
      }
      .cartBnt {
        margin-top: 30px;
      }
      .cart-reduce,
      .cart-increase {
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        width: 26px;
        height: 26px;
        cursor: pointer;
        &.cart-reduce {
          border-radius: 4px 0 0 4px;
          border-right: none;
        }
        &.cart-increase {
          border-radius: 0 4px 4px 0;
          border-left: none;
        }
        .iconfont {
          color: #303133;
          font-size: 13px;
        }
        &.on {
          .iconfont {
            color: #ddd;
          }
        }
      }
      .cart-input {
        width: 26px;
        height: 26px;
      }
    }
  }
  .noCart {
    max-height: 100%;
    display: flex;
    .tip {
      text-align: center;
      color: #999;
      font-size: 14px;
    }
    .picture {
      width: 180px;
      height: 140px;
      margin: 20px auto 15px;
      img {
        width: 100%;
        height: 100%;
      }
    }
  }
}
.noGoods {
  height: calc(100vh - 176px);
  display: flex;
  background: #fff;
  border-radius: 6px;
  margin-top: 14px;
  .tip {
    text-align: center;
    color: #999;
    font-size: 14px;
  }
  .picture {
    width: 180px;
    height: 140px;
    margin-bottom: 15px;
    img {
      width: 100%;
      height: 100%;
    }
  }
}
.footer {
  .footer-top {
    height: 50px;
    padding: 0 20px 0 23px;
    box-shadow: 0px -1px 11px 0px rgba(0, 0, 0, 0.06);
    .delivery-name {
      font-size: 13px;
      color: #606266;
    }
    .delivery-dropdown .el-dropdown-link {
      color: #303133;
      font-size: 13px;
    }
    .el-icon--right {
      margin-left: 2px;
    }
    .shipping {
      margin: 3px 0 0 20px;
      ::v-deep .el-checkbox__label {
        color: #606266;
        font-size: 13px;
      }
    }
    .fee {
      color: #606266;
      font-size: 13px;
      margin-left: 24px;
    }
  }
}
.top-address .el-dropdown-link {
  color: var(--prev-color-primary);
  font-size: 13px;
}
.address-dropdown {
  padding: 10px;
  .el-dropdown-menu__item {
    padding: 0;
  }
}
.add-address {
  width: 210px;
  height: 50px;
  border: 1px solid #cccccc;
  color: var(--prev-color-primary);
  border-radius: 4px;
  justify-content: center;
  .iconjia {
    margin-right: 5px;
    font-size: 13px;
  }
}
.address-item {
  width: 210px;
  margin-top: 10px;
  padding: 15px 20px;
  border: 1px solid #cccccc;
  border-radius: 4px;
  .address-title {
    color: #333333;
    font-weight: 600;
  }
  .address-info {
    color: #666666;
    line-height: 18px;
    margin-top: 8px;
    font-size: 13px;
  }
  &.on,
  &:hover {
    background: #f2f6ff;
    border-color: var(--prev-color-primary);
  }
}
.footer-bottom {
  height: 80px;
  padding: 0 20px 0 23px;
  .order-count-list {
    color: #606266;
    font-size: 13px;
    margin-right: 12px;
  }
  .total-pay {
    color: #303133;
    font-size: 13px;
    span {
      color: #ff7700;
    }
    .symbol {
      font-size: 16px;
    }
    .money {
      font-size: 22px;
    }
  }
  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    height: 46px;
  }
  .btn-default {
    width: 80px;
    color: #606266;
    border: 1px solid #dcdfe6;
    &.disabled {
      color: #999;
      background: #f5f5f5;
      border-color: #ccc;
    }
  }
  .btn-primary {
    width: 120px;
  }
}
.pay_count {
  flex: 1;
  height: 100%;
  position: relative;
  .receipt {
    height: 50px;
    padding: 0 24px;
    background: #fff;
    border-radius: 6px;
    margin-bottom: 14px;
    font-size: 13px;
    .title {
      color: #909399;
    }
    .info {
      color: #606266;
    }
  }
  .pay_footer {
    width: 100%;
    position: absolute;
    bottom: 0;
    right: 0;
    background: #fff;
    height: 60px;
    padding: 14px 24px;
    box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.15);
    border-radius: 0 0 6px 6px;
    text-align: right;
  }
}
.pay_main {
  background: #fff;
  border-radius: 6px;
  padding: 24px;
  height: calc(100vh - 150px);
  .pay_amount {
    width: 260px;
    height: calc(100vh - 260px);
    background: #f9fafc;
    border-radius: 6px;
    padding: 30px 20px;
    .title {
      color: #303133;
      font-size: 15px;
      font-weight: 600;
      text-align: center;
    }
    .amount-item {
      margin-top: 30px;
      font-size: 13px;
      .name {
        color: #606266;
      }
      .value {
        color: #303133;
        font-weight: 600;
      }
    }
    .pay_total {
      margin-top: 30px;
      border-top: 1px dashed #dddddd;
      padding-top: 30px;
      .name {
        color: #606266;
        font-size: 13px;
      }
      .pay_money {
        color: #ff7700;
        font-weight: 600;
        font-size: 16px;
      }
    }
  }
  .pay_type {
    flex: 1;
    padding-right: 30px;
    .title {
      font-size: 15px;
      color: #303133;
      font-weight: 500;
      padding-left: 10px;
      line-height: 16px;
      border-left: 3px solid var(--prev-color-primary);
    }
    .pay_item {
      margin-bottom: 24px;
      border-bottom: 1px dashed #f5f5f5;

      .item_row {
        margin-top: 18px;
        height: 56px;
        border: 1px solid #dddddd;
        border-radius: 4px;
        padding: 0 20px;
        font-size: 13px;
        &.nopointer {
          cursor: not-allowed;
          position: relative;
          &::before {
            content: "";
            display: block;
            width: 100%;
            height: 100%;
            background: #f0f2f5;
            opacity: 0.5;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
          }
        }
        .image {
          width: 26px;
          height: 26px;
          margin-right: 12px;
        }
        .row-check {
          color: #909399;
          .el-checkbox-group {
            margin: 4px 4px 0 0;
          }
          span {
            color: var(--prev-color-primary);
          }
        }
      }
    }
  }
  .payment {
    .payment-item {
      display: flex;
      flex-wrap: wrap; /* 允许换行 */
      gap: 15px; /* 设置行和列的间距 */
      margin-top: 18px;
      .item {
        flex: 0 0 calc(33.333% - 10px); /* 每个元素占据 1/3 的宽度，减去间距 */
        box-sizing: border-box;
        height: 66px;
        border: 1px solid #dddddd;
        border-radius: 4px;
        padding: 0 20px;
        cursor: pointer;
        position: relative;
        &:nth-child(3n) {
          margin-right: 0;
        }
        &.selected {
          border-color: var(--prev-color-primary);
        }
        .item-icon {
          img {
            width: 26px;
            height: 26px;
            margin-right: 12px;
          }
        }
        .name {
          color: #303133;
          font-size: 13px;
        }
        .money {
          margin-top: 6px;
          color: #999;
          font-size: 11px;
        }
      }
      .item-selected {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 0 24px 24px;
        border-color: transparent transparent var(--prev-color-primary)
          transparent;
      }
      .iconfont-h5 {
        position: absolute;
        bottom: -22px;
        right: 1px;
        color: #fff;
        font-size: 13px;
      }
    }
  }
}
.offline-result {
  width: 320px;
  height: 165px;
  background: #fff;
  border-radius: 8px;
  position: absolute;
  top: 50%;
  left: 50%;
  margin-top: -85px;
  margin-left: -160px;
  z-index: 100;
  .result-main {
    height: 100%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    .iconfont-h5 {
      color: #00b42a;
      font-size: 22px;
      margin-top: 35px;
    }
    .text {
      color: #303133;
      font-weight: 600;
      margin-top: 14px;
    }
  }
  .icon-ic_close {
    color: rgba(0, 0, 0, 0.25);
    position: absolute;
    top: 18px;
    right: 24px;
    cursor: pointer;
  }
  .result-footer {
    width: 100%;
    height: 45px;
    line-height: 45px;
    font-size: 13px;
    color: var(--prev-color-primary);
    border-top: 1px solid #eeeeee;
    .foot-btn {
      width: 50%;
      cursor: pointer;
      &:first-child {
        border-right: 1px solid #eee;
      }
    }
  }
}
.result-mask {
  width: 100%;
  height: 100%;
  position: fixed;
  top: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.1);
  z-index: 90;
}
</style>

<template>
  <div class="users">
    <div class="acea-row row-top content">
      <div class="left" :style="colorStyle">
        <div class="phone">
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 0 }"
              class="tooltip-content"
              style="top: 50px;"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">商品信息</div>
            </div>
            <div
              class="detail-title acea-row h-50 bg--w111-fff row-center-wrapper"
            >
              商品详情
            </div>
            <productInfo
              :showShare="productCardInfo.openShare"
              :showDot="productCardInfo.swiperDot"
              :isOpen="productCardInfo.isOpen"
              :showSvip="productCardInfo.showSvip"
              :shareConfig="productCardInfo.shareConfig"
              :shareList="productCardInfo.shareList"
              :navList="productCardInfo.navList"
              class="cup module"
              :class="{ active: active === 0 }"
              @click.native="editMode(0)"
            ></productInfo>
          </div>

          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 2 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">排行榜</div>
            </div>
            <div
              class="px-10 cup module"
              :class="{ active: active === 2 }"
              @click="editMode(2)"
            >
              <div
                class="h-40 rd-8px bg--w111-fff flex-between-center px-10 mb-8"
              >
                <div class="flex-y-center">
                  <img
                    src="@/assets/images/rank_icon.png"
                    class="rank-icon mx-4"
                  />
                  <span class="fs-13 text--w111-333">紧致抗皱套装·第2名</span>
                </div>
                <span
                  class="iconfont-h5 icon-ic_rightarrow circle_rightarrow"
                ></span>
              </div>
            </div>
            <div
              class="mask"
              v-if="!productCardInfo.showRank"
              @click="editMode(2)"
            >
              已隐藏
            </div>
          </div>
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 1 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">优惠券</div>
            </div>
            <div
              class="px-10 cup module mb8"
              :class="{ active: active === 1 }"
              @click="editMode(1)"
            >
              <div class="bg--w111-fff flex-between-center rd-12px h-40 px-10">
                <div class="acea-row row-middle">
                  <span class="fs-13 text--w111-888">优惠券：</span>
                  <div class="ml-16 coupon-item">
                    <div>满100减10</div>
                  </div>
                </div>
                <span
                  class="iconfont-h5 icon-ic_rightarrow fs-12 text--w111-666"
                ></span>
              </div>
            </div>
            <div
              class="mask"
              v-if="!productCardInfo.showCoupon"
              @click="editMode(1)"
            >
              已隐藏
            </div>
          </div>
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 3 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">商品参数</div>
            </div>
            <servieCard
              :showService="productCardInfo.showService"
              :serviceList="productCardInfo.serviceList"
              class="cup module"
              :class="{ active: active === 3 }"
              @click.native="editMode(3)"
            ></servieCard>
          </div>
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 5 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">优惠套餐</div>
            </div>
            <matchCard
              :showMatch="productCardInfo.showMatch"
              class="cup module"
              :class="{ active: active === 5 }"
              @click.native="editMode(5)"
            ></matchCard>
          </div>
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 4 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">商品评价</div>
            </div>
            <replyCard
              :showReply="productCardInfo.showReply"
              class="cup module"
              :class="{ active: active === 4 }"
              @click.native="editMode(4)"
            ></replyCard>
          </div>
          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 8 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">种草秀</div>
            </div>
            <community
              :showCommunity="productCardInfo.showCommunity"
              class="cup module"
              :class="{ active: active === 8 }"
              @click.native="editMode(8)"
            ></community>
          </div>

          <div class="content-item">
            <div
              :class="{ 'active-tip': active === 6 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">店铺信息</div>
            </div>
            <div
              class="px-10 cup module"
              :class="{ active: active === 6 }"
              @click="editMode(6)"
            >
              <div class="rd-12px bg--w111-fff">
                <div class="store-hd relative">
                  <div class="store-info acea-row row-between-wrapper">
                    <div class="acea-row row-center-wrapper">
                      <img
                        class="logo"
                        src="@/assets/images/product_diy_merlogo.jpg"
                      />
                      <div class="info acea-row flex-col row-between-center">
                        <div class="name fs-15 line1">
                          <span class="fw-500">爱花屋</span>
                          <img src="" class="store-margin" />
                        </div>
                        <div class="txt fs-12">
                          41.6万人关注
                        </div>
                      </div>
                    </div>
                    <div class="link acea-row row-center-wrapper">进店</div>
                  </div>
                  <div class="score-wrapper">
                    <div class="item">商品描述<span>5.0</span></div>
                    <div class="item">卖家服务<span>5.0</span></div>
                    <div class="item">物流服务<span>5.0</span></div>
                  </div>
                  <div
                    class="mask"
                    v-if="
                      !productCardInfo.showStore &&
                        productCardInfo.showRecommend
                    "
                    @click="editMode(6)"
                  >
                    已隐藏
                  </div>
                </div>
                <div class="relative mt14">
                  <div class="px-10 fs-15 fw-500 text--w111-333">店铺推荐</div>
                  <div class="grid-column-3 grid-gap-10px p-10">
                    <div class="" v-for="(item, index) in 6" :key="index">
                      <img :src="pic" class="w-full h-104px rd-8px" />
                      <div class="w-full line-1 mt-6 fs-13">商品名称商品</div>
                      <div class="red regular mt-6">
                        <span class="fs-12">¥</span>
                        <span class="fs-14">199</span>
                        <span class="fs-12">.00</span>
                      </div>
                    </div>
                  </div>
                  <div
                    class="mask"
                    v-if="
                      productCardInfo.showStore &&
                        !productCardInfo.showRecommend
                    "
                    @click="editMode(6)"
                  >
                    已隐藏
                  </div>
                </div>
              </div>
            </div>
            <div
              class="mask"
              v-if="
                !productCardInfo.showStore && !productCardInfo.showRecommend
              "
              @click="editMode(6)"
            >
              已隐藏
            </div>
          </div>
          <div class="content-item" style="margin-top: 6px;">
            <div
              :class="{ 'active-tip': active === 7 }"
              class="tooltip-content"
            >
              <div class="tooltip-arrow"></div>
              <div class="tooltip-inner">底部菜单</div>
            </div>
            <div
              class="px-10 cup module mb-50"
              :class="{ active: active === 7 }"
              @click="editMode(7)"
            >
              <div
                class="bg--w111-fff h-104 flex flex-between-center text--w111-333 pl-16 pr-10"
              >
                <div class="flex text--w111-333">
                  <template v-for="(item, index) in productCardInfo.footerList">
                    <div
                      v-if="productCardInfo.menuList.includes(item.value)"
                      :key="index"
                      class="flex-col acea-row row-center-wrapper mr-14"
                    >
                      <span :class="'iconfont-h5 fs-40 ' + item.icon"></span>
                      <span class="fs-9 text--w111-666">{{ item.label }}</span>
                    </div>
                  </template>
                </div>
                <div
                  v-if="productCardInfo.showCart"
                  class="acea-row row-center-wrapper"
                >
                  <div
                    class="h-36 acea-row row-center-wrapper join_cart text--w111-fff fs-13"
                    :class="'w-menu' + productCardInfo.menuList.length"
                  >
                    加入购物车
                  </div>
                  <div
                    class="h-36 acea-row row-center-wrapper pay_btn text--w111-fff fs-13"
                    :class="'w-menu' + productCardInfo.menuList.length"
                  >
                    立即购买
                  </div>
                </div>
                <div v-else class="acea-row row-between-wrapper">
                  <div
                    class="h-36 acea-row row-center-wrapper pay_btn btn_one text--w111-fff fs-13"
                    :class="'btn_one' + productCardInfo.menuList.length"
                  >
                    立即购买
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="right">
        <infoCardConfig
          :showType="active"
          :productCardInfo="productCardInfo"
        ></infoCardConfig>
      </div>
    </div>
    <el-card
      class="fixed-card"
      :style="{
        left: `${
          isCollapse && !sideBar1
            ? '54px'
            : !isCollapse && sideBar1
            ? '270px'
            : !isCollapse && !sideBar1
            ? '180px'
            : '180px'
        }`
      }"
    >
      <div class="acea-row row-center-wrapper">
        <el-button type="primary" size="small" @click="onSubmit"
          >保存</el-button
        >
      </div>
    </el-card>
  </div>
</template>

<script>
import productInfo from "./template/Product/infoCard";
import servieCard from "./template/Product/serviceCard";
import replyCard from "./template/Product/replyCard";
import matchCard from "./template/Product/matchCard";
import community from "./template/Product/community";
import { mapState } from "vuex";
import infoCardConfig from "./template/Product/templateConfig";
import { getProductDetail, saveProductDetail } from "@/api/diy";
export default {
  name: "ProductPage",
  components: {
    productInfo,
    servieCard,
    replyCard,
    matchCard,
    infoCardConfig,
    community
  },

  data() {
    return {
      sideBar1:
        window.localStorage.getItem("sidebarStyle") == "a" ? false : true,
      active: 0,
      pic: require("@/assets/images/product_diy_menu.webp"),
      productCardInfo: {
        navList: [0, 1, 2, 3, 4], // 顶部菜单内容
        openShare: 1, //是否开启分享
        pictureConfig: 0, //轮播图模式 0 固定方图 1 高度自适应
        swiperDot: 1, //是否展示轮播指示点
        isOpen: [0, 1, 2], //是否展示 0 原价 1 累计销量 2 库存
        shareConfig: [1, 2], //分享收藏按钮
        showCoupon: 1, //是否展示优惠券
        showSvip: 1, //是否展示付费会员入口
        showRank: 1, // 是否展示 排行榜卡片
        showService: [0, 1, 2, 3], //服务区卡片 0 规格选择 2 运费 3 服务保障 4 参数
        showReply: 1, //是否展示评论区
        replyNum: 3, //评论数量
        showMatch: 1, //是否展示套餐
        showStore: 1, //是否显示店铺
        showRecommend: 1, //是否展示推荐商品
        recommendNum: 12, //推荐商品数量
        menuList: [0, 1, 2], //底部左侧菜单
        showCart: 1, //是否显示购物车
        showCommunity: 1, //是否显示种草
        communityNum: 3,
        swiperHeight: "750",
        shareList: [
          { label: "客服", value: 0, icon: "icon-ic_customerservice" },
          { label: "收藏", value: 1, icon: "icon-ic_star" },
          { label: "分享", value: 2, icon: "icon-ic_transmit1" }
        ],
        serviceList: [
          {
            name: "请选择",
            label: "规格选择",
            value: 0,
            info: "蓝色，2件",
            props: "specSelect"
          },
          {
            name: "运费",
            label: "运费说明",
            value: 1,
            info: "免运费",
            props: "freight"
          },
          {
            name: "保障",
            label: "服务保障",
            value: 2,
            info: "假一赔四 极速退款 七天无理由退换",
            props: "serviceGuarantee"
          },
          {
            name: "参数",
            label: "参数说明",
            value: 3,
            info: "品牌 型号...",
            props: "parameter"
          }
        ],
        footerList: [
          { label: "店铺", value: 0, icon: "icon-ic_mall" },
          { label: "客服", value: 1, icon: "icon-ic_customerservice" },
          { label: "购物车", value: 2, icon: "icon-ic_ShoppingCart" },
          { label: "收藏", value: 3, icon: "icon-ic_star" },
          { label: "分享", value: 4, icon: "icon-ic_transmit1" },
          { label: "首页", value: 5, icon: "icon-ic_home" }
        ]
      },
      price: [], // 价格
      value: "",
      current: 1,
      colorStyle: "",
      order: {},
      visible: true
    };
  },
  computed: {
    ...mapState({
      sidebar: state => state.app.sidebar
    }),
    isCollapse() {
      return !this.sidebar.opened;
    }
  },
  created() {
    this.getProductDetail();
  },
  methods: {
    editMode(type) {
      this.active = type;
    },
    // 获取商品详情
    getProductDetail() {
      getProductDetail().then(res => {
        this.productCardInfo =
          res.data.product_detail_diy || this.productCardInfo;
      });
    },
    onSubmit() {
      saveProductDetail({ product_detail_diy: this.productCardInfo })
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    isShowDataItem(value) {
      let result = this.optionstList.price_type.some(ele => ele == value);
      return result;
    }
  }
};
</script>
<style scoped lang="scss">
/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #ddd;
}
::-webkit-scrollbar {
  width: 4px !important; /* 对垂直流动条有效 */
}
.content {
  display: flex;
  min-height: 600px;
  background-color: #f0f2f5;
  padding-right: 400px;
  .left {
    flex-direction: column;
    margin: 0 auto;
    .module {
      border: 2px solid #f5f5f5;
    }
    .module.active {
      border: 2px solid #1890ff;
    }
    .coupon-item {
      height: 22px;
      padding: 0 11px;
      border: 1px solid #e93323;
      color: #e93323;
      font-size: 13px;
      line-height: 22px;
      position: relative;
      margin: 10px 0 10px 8px;
      &::before,
      &::after {
        content: " ";
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 100%;
        background-color: #fff;
        border: 1px solid #e93323;
      }
      &::before {
        bottom: 50%;
        left: -2px;
        margin-bottom: -3px;
        border: 0.5px solid #e93323;
        border-left-color: #fff;
      }
      &::after {
        right: -3px;
        bottom: 50%;
        margin-bottom: -3px;
        border: 0.5px solid #e93323;
        border-right-color: #fff;
      }
    }
    .rank-icon {
      width: 59px;
      height: 19px;
    }
    .circle_rightarrow {
      width: 13px;
      height: 13px;
      border-radius: 13px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 6px;
      color: #fff;
      background: #666666;
    }
    .phone {
      margin-top: 20px;
      width: 375px;
      background-color: #f5f5f5;
      ::v-deep .ivu-tooltip {
        width: 100%;
      }
    }
  }
  .right {
    width: 400px;
    position: fixed;
    right: 0;
    top: 80px;
    height: 100%;
    overflow-y: scroll;
    background: #fff;
    padding-bottom: 150px;
  }
}
.content-item {
  position: relative;
  .tooltip-content {
    position: absolute;
    padding: 8px 12px;
    min-width: 60px;
    background-color: #fff;
    color: #515a6e;
    top: 0;
    left: -90px;
    font-size: 12px;
    text-align: center;
    border-radius: 4px;
    .tooltip-arrow {
      position: absolute;
      width: 0;
      height: 0;
      border-color: transparent;
      border-style: solid;
      right: -7px;
      top: 8px;
      border-width: 7px 0 7px 7px;
      border-left-color: hsla(0, 0%, 85.1%, 0.5);
      &::after {
        content: " ";
        display: block;
        width: 0;
        height: 0;
        position: absolute;
        border-color: transparent;
        border-style: solid;
        border-width: 7px;
        right: 1px;
        border-right-width: 0;
        border-left-width: 7px;
        border-left-color: #fff;
        bottom: -7px;
      }
    }
    &.active-tip {
      background: var(--prev-color-primary);
      color: #fff;
      .tooltip-arrow {
        &::after {
          border-left-color: var(--prev-color-primary);
        }
      }
    }
  }
  .detail-title {
    font-weight: 500;
    font-size: 18px;
    color: #282828;
  }
}
.store-hd {
  padding: 15px 10px;
  border-bottom: 1px solid #f5f5f5;
  .store-info {
    position: relative;
    .logo {
      width: 47px;
      height: 47px;
    }
    .info {
      margin-left: 11px;
      .name {
        color: #282828;
      }
    }
    .txt {
      margin-top: 4px;
      color: #666666;
    }
    .link {
      width: 61px;
      height: 30px;
      background-image: linear-gradient(-90deg, #fa6514 0%, #e93323 100%);
      border-radius: 55px;
      color: #fff;
      font-size: 13px;
    }
  }
  .score-wrapper {
    display: flex;
    justify-content: space-between;
    margin-top: 16px;
    .item {
      font-size: 14px;
      color: #999;
      span {
        margin-left: 4px;
        display: inline-block;
        color: #e93323;
      }
    }
  }
}

.fixed-card {
  position: fixed;
  z-index: 100;
  right: 0;
  bottom: 0;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
  padding-right: 400px;
}
.w-16 {
  width: 16px;
}
.h-16 {
  height: 16px;
}
.w-38 {
  width: 38px;
}
.h-12 {
  height: 12px;
}
.mx-4 {
  margin: 0 4px;
}
.w-menu1 {
  width: 142px;
}
.w-menu2 {
  width: 125px;
}
.w-menu3 {
  width: 108px;
}
.join_cart {
  border-radius: 25px 0 0 25px;
  background: linear-gradient(90deg, #fea10f 0%, #fa8013 100%);
}
.pay_btn {
  background: linear-gradient(90deg, #fa6514 0%, #e93323 100%);
  border-radius: 0 25px 25px 0;
  &.btn_one {
    border-radius: 25px;
  }
  &.btn_one3 {
    width: 216px;
  }
  &.btn_one2 {
    width: 250px;
  }
  &.btn_one1 {
    width: 280px;
  }
}
::v-deep .ivu-tooltip-inner {
  box-shadow: none;
}
::v-deep .active-tip .ivu-tooltip-light .ivu-tooltip-inner {
  background-color: #1890ff;
  color: #fff;
}
::v-deep .ivu-tooltip-light.ivu-tooltip-popper {
  z-index: 10;
}
::v-deep
  .active-tip
  .ivu-tooltip-light.ivu-tooltip-popper[x-placement^="left"]
  .ivu-tooltip-arrow:after {
  border-left-color: #1890ff;
}
::v-deep .main {
  position: relative;
}
::v-deep .mask {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  z-index: 9;
  display: flex;
  align-items: center;
  justify-content: center;
}
.w-105 {
  width: 105px;
}
.mb-50 {
  margin-bottom: 50px;
}
.bg--w111-f1f1f1 {
  background: #f1f1f1;
}
</style>

<template>
  <div
    class="mobile-page"
    :style="compStyle"
  >
    <div class="home_product">
      <div class="hd_nav" v-if="styleConfig == 0" :style="{ paddingInline: prConfig + 'px'}">
        <div class="item" :class="index == tabCur ? 'active' : ''" v-for="(item, index) in navlist" :key="index">
          <p
            class="title"
            :style="{ color: index == tabCur ? (toneConfig ? textColor2 : colorStyle.theme) : '#282828' }"
          >
            {{ item.chiild[0].val || '标题' }}
          </p>
          <span
            class="label"
            :style="{ background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '' }"
            v-if="item.chiild[1].val"
            >{{ item.chiild[1].val || '标题简介' }}</span
          >
        </div>
      </div>
      <div class="menus" :class="styleConfig == 2 ? 'on' : ''" v-else :style="{ paddingInline: prConfig + 'px'}">
        <div
          class="item"
          :class="index == tabCur ? 'on' : ''"
          v-if="styleConfig == 1"
          v-for="(item, index) in navlist"
          :key="index"
          :style="{
            color: index == tabCur ? (toneConfig ? textColor : '#333') : '#282828',
          }"
        >
          {{ item.chiild[0].val || '标题'
          }}<span
            :style="{
              background: toneConfig ? decorateColor : themeColor,
            }"
          ></span>
        </div>
        <div
          class="item"
          :class="index == tabCur ? 'on3' : ''"
          v-if="styleConfig == 2"
          v-for="(item, index) in navlist"
          :key="index"
          :style="{
            color: index == tabCur ? (toneConfig ? textColor2 : colorStyle.theme) : '#282828',
          }"
        >
          {{ item.chiild[0].val || '标题'
          }}<span
            :style="{
              borderColor: toneConfig ? decorateColor2 : colorStyle.theme,
            }"
          ></span>
        </div>
        <div
          class="item"
          :class="index == tabCur ? 'on2' : ''"
          v-if="styleConfig == 3"
          v-for="(item, index) in navlist"
          :key="index"
          :style="{
            color: index == tabCur ? (toneConfig ? textColor3 : '#fff') : '#282828',
            background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '',
          }"
        >
          {{ item.chiild[0].val || '标题' }}
        </div>
        <div class="item pic" v-if="styleConfig == 4" v-for="(item, index) in navlist" :key="index">
          <div
            class="pictrue acea-row row-center-wrapper"
            :style="{ borderColor: index == tabCur ? (toneConfig ? decorateColorLeft : colorStyle.theme) : '#EEEEEE' }"
          >
            <img class="img" :src="item.image" v-if="item.image" />
            <img src="../../assets/images/shan.png" v-else />
          </div>
          <div
            class="title"
            :style="{
              color: index == tabCur ? (toneConfig ? textColor3 : '#fff') : '#282828',
              background: index == tabCur ? (toneConfig ? decorateColor : themeColor) : '',
            }"
          >
            {{ item.chiild[0].val || '标题' }}
          </div>
        </div>
      </div>
      <div class="list-wrapper" :style="{ paddingInline: prConfig + 'px'}">
        <div class="item" v-for="(item, index) in list" :key="index" :style="goodsItemStyle">
          <div class="img-box">
            <img
              class="img"
              v-if="item.image"
              :src="item.image"
              alt=""
              :style="imgStyle"
            />
            <div
              v-else
              class="empty-box"
              :style="imgStyle"
            >
              <img src="../../assets/images/shan.png" />
            </div>
          </div>
          <div class="info">
            <div class="title line1" :style="goodsNameStyle">
              {{ item.store_name || '这里是商品名称展示区域,商品名称展示区域,商品名称展示区域' }}
            </div>
            <div class="price">
              <div class="num" :style="goodsPriceStyle">
                <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
              </div>
              <img src="../../assets/images/goods02.png" />
            </div>
            
            <div class="pictrue">
              <img src="../../assets/images/goods01.png" />
            </div>
            <div class="sales" :style="goodsScoreStyle">{{ item.star || 0 }}评分</div>
            <div class="sales" :style="goodsSalesStyle">已售{{ item.sales || 0 }}</div>
            <div class="sales">{{ item.reply_count || 0 }}条评论</div>
            <div class="store-info">
              <span>32.5km</span>
              小米官方旗舰店
              <i class="iconfont iconyou"></i>
            </div>
          </div>
          <div
            class="jia"
            v-if="!cartConfig"
            :style="{
              background: toneCartConfig ? bntBgColor : themeColor,
            }"
          >
            <div class="jiaCon">
              <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 0"></span>
              <span class="iconfont icongouwuche1" v-else></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { diyUtil } from '@/utils/diy-util';
// import theme from "@/mixins/theme";
export default {
  name: 'home_product',
  cname: '商品分类',
  configName: 'c_home_product',
  icon: '#iconzujian-shangpinxuanxiangka',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'promotionList', // 外面匹配名称
  sortOrder: 90,
  targetScope: "platform", // 组件使用范围，platform 平台，store 门店，无则通用
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
    colorStyle: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
    compStyle() {
      if (!this.renderData) return {};
      return {
        background: this.bottomBgColor,
        marginTop: diyUtil.buildMarginTopOffset(this.renderData.mbConfig, this.renderData.ptConfig),
        paddingTop: this.topConfig + 'px',
        paddingBottom: this.bottomConfig + 'px',
        boxShadow: this.computedBoxShadow,
        borderRadius: this.bgRadius,
        overflow: 'hidden',
      };
    },
    themeColor() {
      return `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.assist} 100%)`;
    },
    computedBoxShadow() {
      if (!this.renderData) return "";
      return diyUtil.buildShadowStyle(this.renderData.shadowConfig);
    },
    imgStyle() {
      return {
        borderRadius: diyUtil.buildBorderRadius(this.renderData.filletImg),
      };
    },
    goodsNameStyle() {
      const { goodsName, goodsToneConfig, goodsNameColor } = this.renderData;
      const style = {
        fontWeight: goodsName.tabList[goodsName.tabVal].style
      };

      if (goodsToneConfig.tabVal == 1) {
        style.color = goodsNameColor.color[0].item;
      }

      return style;
    },
    goodsPriceStyle() {
      const { goodsToneConfig, goodsPriceColor } = this.renderData;
      const style = {
        color: `var(--view-priceColor)`
      };

      if (goodsToneConfig.tabVal == 1) {
        style.color = goodsPriceColor.color[0].item;
      }

      return style;
    },
    goodsSalesStyle() {
      const { goodsToneConfig, soldNumColor } = this.renderData;
      const style = {
      };
      
      if (goodsToneConfig.tabVal == 1) {
        style.color = soldNumColor.color[0].item;
      }

      return style;
    },
    goodsItemStyle() {
      return {
        boxShadow: diyUtil.buildShadowStyle(this.renderData.goodShadowConfig),
      }
    },
    goodsScoreStyle() {
      const { goodsToneConfig, scoreColor } = this.renderData;
      const style = {
      };
      if (goodsToneConfig.tabVal == 1) {
        style.color = scoreColor.color[0].item;
      }

      return style;
    }
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
    num: {
      handler(nVal, oVal) {
        const data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        const data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  // mixins: [theme],
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '商品分类',
        name: 'promotionList',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleTab: '选项卡设置',
        titleRight: '选项卡样式',
        titleCurrency: '卡片样式',
        titleCart: '购物车按钮',
        titleGood: "商品样式",
        styleConfig: {
          title: '选择风格',
          tabVal: 1,
          tabList: [
            {
              name: '样式一',
            },
            {
              name: '样式二',
            },
            {
              name: '样式三',
            },
            {
              name: '样式四',
            },
            {
              name: '样式五',
            },
          ],
        },
        slideConfig: {
          title: '滑动置顶',
          tabVal: 1,
          tabList: [
            {
              name: '启用',
            },
            {
              name: '不启用',
            },
          ],
        },
        tabConfig: {
          title: '点击下方选项卡可进行编辑；鼠标拖拽版块可调整顺序',
          max: '',
          tabCur: 0,
          classList: [],
          list: [
            {
              chiild: [
                {
                  title: '标题',
                  val: '首发新品',
                  max: 4,
                  pla: '选填，不超过四个字',
                },
                {
                  title: '简介',
                  val: '最新出炉',
                  max: 4,
                  pla: '选填，不超过四个字',
                },
              ],
              image: '',
              tabVal: 1,
              brandConfig: {
                brandVal: [],
              },
              selectConfig: {
                activeValue: [],
              },
              goodsLabel: {
                activeValue: [],
                list: [],
              },
              goodsType: {
                title: "商品类型",
                activeValue: [],
                multiple: true,
                list: [
                  {
                    activeValue: 1,
                    title: "普通商品"
                  },
                  {
                    activeValue: 2,
                    title: "虚拟商品"
                  },
                  {
                    activeValue: 3,
                    title: "云盘商品"
                  },
                  {
                    activeValue: 4,
                    title: "卡密商品"
                  },
                  {
                    activeValue: 5,
                    title: "预约商品"
                  }
                ]
              },
              deliveryType: {
                title: "配送方式",
                activeValue: [],
                multiple: true,
                list: [
                  {
                    activeValue: 1,
                    title: "快递配送"
                  },
                  {
                    activeValue: 2,
                    title: "到店自提"
                  },
                  {
                    activeValue: 3,
                    title: "同城服务"
                  },
                  {
                    activeValue: 4,
                    title: "上门服务"
                  },
                  {
                    activeValue: 5,
                    title: "到店服务"
                  }
                ]
              },
              goodsSort: 0,
              numConfig: {
                val: 6,
              },
              goodsList: {
                max: 20,
                list: [],
              },
              productList: {
                list: [],
              },
            },
          ],
        },
        cartConfig: {
          title: '是否显示',
          tabVal: 0,
          tabList: [
            {
              name: '显示',
            },
            {
              name: '隐藏',
            },
          ],
        },
        bntConfig: {
          title: '按钮效果',
          tabVal: 1,
          tabList: [
            {
              name: '进入商品详情页',
            },
            {
              name: '商品加购',
            },
          ],
        },
        bntStyleConfig: {
          typeFrom: 'bnt',
          title: '按钮样式',
          tabVal: 0,
        },
        toneConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        decorateColor: {
          title: '装饰元素',
          default: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
        },
        decorateColor2: {
          title: '装饰元素',
          default: [
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
          ],
        },
        textColor: {
          title: '选中文字',
          default: [
            {
              item: '#333333',
            },
          ],
          color: [
            {
              item: '#333333',
            },
          ],
        },
        textColor2: {
          title: '选中文字',
          default: [
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
          ],
        },
        textColor3: {
          title: '选中文字',
          default: [
            {
              item: '#FFFFFF',
            },
          ],
          color: [
            {
              item: '#FFFFFF',
            },
          ],
        },
        toneCartConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        bntBgColor: {
          title: '按钮颜色',
          name: 'bntBgColor',
          default: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
        },
        bottomBgColor: {
          title: '底部背景',
          default: [
            {
              item: '#f5f5f5',
            },
          ],
          color: [
            {
              item: '#f5f5f5',
            },
          ],
        },
        topConfig: {
          title: '上边距',
          val: 0,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 0,
          min: 0,
        },
        prConfig: {
          title: '左右边距',
          val: 10,
          min: 0,
        },
        mbConfig: {
          title: '页面上间距',
          val: 10,
          min: 0,
        },
        fillet: {
          title: '背景圆角',
          type: 0,
          list: [
            {
              val: '全部',
              icon: 'iconcaozuo-zhengti',
            },
            {
              val: '单个',
              icon: 'iconcaozuo-bianjiao',
            },
          ],
          valName: '圆角值',
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        ptConfig: {
          title: '组件上浮',
          val: 0,
          min: 0,
        },
        shadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },

        filletImg: {
          title: '图片圆角',
          type: 0,
          list: [
            {
              val: '全部',
              icon: 'iconcaozuo-zhengti',
            },
            {
              val: '单个',
              icon: 'iconcaozuo-bianjiao',
            },
          ],
          valName: '圆角值',
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        goodsName: {
          title: '商品名称',
          tabVal: 1,
          tabList: [
            {
              name: '加粗',
              style: 'bold',
            },
            {
              name: '常规',
              style: 'normal',
            },
          ],
        },
        goodsToneConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        goodsNameColor: {
          title: '商品名称',
          name: 'goodsNameColor',
          default: [
            {
              item: '#333333',
            },
          ],
          color: [
            {
              item: '#333333',
            },
          ],
        },
        goodsPriceColor: {
          title: '商品价格',
          name: 'goodsPriceColor',
          default: [
            {
              item: '#E93323',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
          ],
        },
        soldNumColor: {
          title: '已售数量',
          name: 'soldNumColor',
          default: [
            {
              item: '#999999',
            },
          ],
          color: [
            {
              item: '#999999',
            },
          ],
        },
        scoreColor: {
          title: '评分颜色',
          name: 'scoreColor',
          default: [
            {
              item: '#999999',
            },
          ],
          color: [
            {
              item: '#999999',
            },
          ],
        },
        goodShadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },
      },
      navlist: [],
      tabCur: 0,
      list: [],
      pageData: {},
      styleConfig: 0,
      toneConfig: 0,
      textColor: '',
      textColor2: '',
      textColor3: '',
      decorateColor: '',
      decorateColor2: '',
      decorateColorLeft: '',
      // bgColor:'',
      bottomBgColor: '',
      mTop: 0,
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: 0,
      cartConfig: 0,
      toneCartConfig: 0,
      bntBgColor: '',
      bntStyleConfig: 0,
      ptConfig: 0,

      renderData: null
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.mbConfig) {
        this.renderData = JSON.parse(JSON.stringify(data));
        this.styleConfig = data.styleConfig.tabVal;
        this.cartConfig = data.cartConfig.tabVal;
        this.bntStyleConfig = data.bntStyleConfig.tabVal;
        this.toneCartConfig = data.toneCartConfig.tabVal;
        let bntBgColorLeft = data.bntBgColor.color[0].item;
        let bntBgColorRight = data.bntBgColor.color[1].item;
        this.bntBgColor = `linear-gradient(90deg,${bntBgColorLeft} 0%,${bntBgColorRight} 100%)`;
        this.toneConfig = data.toneConfig.tabVal;
        this.textColor = data.textColor.color[0].item;
        this.textColor2 = data.textColor2.color[0].item;
        this.textColor3 = data.textColor3.color[0].item;
        let decorateColorLeft = data.decorateColor.color[0].item;
        let decorateColorRight = data.decorateColor.color[1].item;
        this.decorateColorLeft = decorateColorLeft;
        this.decorateColor = `linear-gradient(90deg,${decorateColorLeft} 0%,${decorateColorRight} 100%)`;
        this.decorateColor2 = data.decorateColor2.color[0].item;
        // let bgColorLeft =  data.moduleColor.color[0].item;
        // let bgColorRight =  data.moduleColor.color[1].item;
        // this.bgColor = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.mTop = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = diyUtil.buildBorderRadius(data.fillet);
        this.navlist = data.tabConfig.list;
        this.tabCur = data.tabConfig.tabCur || 0;
        let goods = data.tabConfig.list[this.tabCur];
        if (goods) {
          if (goods.tabVal == 1 && goods.goodsList.list) {
            this.list = goods.goodsList.list.length ? goods.goodsList.list : 2;
          } else if (goods.goodsList.list) {
            this.list = goods.productList.list.length ? goods.productList.list : 2;
          } 
        } else {
          this.list = 2;
        }
        this.ptConfig = data.ptConfig.val || 0;

      }
    },
  },
};
</script>

<style scoped lang="scss">
.menus {
  display: flex;
  align-items: center;
  width: 10000%;
  cursor: pointer;
  padding-left: 12px;
  padding: 9px 0;

  &.on {
    padding-bottom: 14px;
  }

  .title {
    color: #333;
    font-size: 12px;
    margin-top: 4px;
    height: 20px;
    border-radius: 20px;
    padding: 0 5px;
    line-height: 20px;
    text-align: center;
  }

  .item {
    position: relative;
    color: #333;
    font-size: 14px;
    margin-right: 28px;
    z-index: 9;

    &.pic {
      margin-right: 15px;
    }

    .pictrue {
      width: 46px;
      height: 46px;
      border: 1px solid #eeeeee;
      border-radius: 50%;
      background: #f3f9ff;
      margin: 0 auto;

      .img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
      }

      img {
        width: 27px;
      }
    }

    &.on {
      font-size: 16px;
      font-weight: 600;

      span {
        display: block;
        position: absolute;
        left: 50%;
        bottom: 2px;
        width: 100%;
        height: 4px;
        border-radius: 100px;
        transform: translateX(-50%);
        background: #fff;
        background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
        z-index: -1;
      }
    }

    &.on2 {
      height: 24px;
      text-align: center;
      line-height: 24px;
      color: #fff;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 50px;
      padding: 0 6px;
    }

    &.on3 {
      font-size: 16px;
      font-weight: 600;
      color: #e93323;

      span {
        position: absolute;
        width: 30px;
        height: 30px;
        border: 3px solid #e93323;
        border-left: 3px solid transparent !important;
        border-top: 3px solid transparent !important;
        border-right: 3px solid transparent !important;
        border-radius: 50%;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%);
      }
    }
  }
}

.home_product {
  overflow: hidden;

  .hd_nav {
    display: flex;
    height: 65px;
    overflow: hidden;
    padding: 10px 0;

    .item {
      display: flex;
      flex-direction: column;
      justify-content: center;
      margin-right: 37px;

      .title {
        font-size: 15px;
        color: #282828;
        text-align: center;
      }

      .label {
        width: 56px;
        height: 19px;
        line-height: 19px;
        text-align: center;
        background: transparent;
        border-radius: 10px;
        color: #999999;
        font-size: 11px;
      }

      &.active {
        .title {
          color: #ff4444;
        }

        .label {
          color: #fff;
          background: linear-gradient(270deg, rgba(255, 84, 0, 1) 0%, rgba(255, 0, 0, 1) 100%);
        }
      }
    }
  }

  .list-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;

    .item {
      width: 48.5%;
      margin-bottom: 10px;
      position: relative;

      .title {
        margin-bottom: 16px;
        // min-height: 42px;
      }

      .jia {
        width: 22px;
        height: 22px;
        background-color: #e93323;
        border-radius: 50%;
        position: absolute;
        right: 10px;
        bottom: 8px;

        .jiaCon {
          width: 100%;
          height: 100%;
          text-align: center;
          line-height: 22px;

          .iconfont {
            color: #fff;
            font-size: 13px;
          }
        }
      }

      .img-box {
        width: 100%;
        height: 173px;

        .img {
          width: 100%;
          height: 100%;
        }

        img,
        .box {
          width: 65px;
          height: 50px;
        }

        .empty-box {
          background: #f3f9ff;
        }

        .box {
          background: #d8d8d8;
        }

        .label {
          position: absolute;
          left: 0;
          top: 0;
          width: 46px;
          height: 22px;
          border-radius: 10px 0px 10px 0px;
          color: #fff;
          font-size: 13px;
          text-align: center;
          line-height: 22px;
        }
      }

      .info {
        padding: 7px 10px;
        background: #fff;
        border-radius: 0px 0px 10px 10px;

        .pictrue {
          // width: 99px;
          height: 14px;
          margin: 4px 0;
 
          img {
            // width: 100%;
            height: 100%;
          }
        }

        .title {
          font-size: 14px;
          color: #282828;
        }

        .sales {
          color: #999;
          font-size: 11px;
          display: inline-flex;
          margin-right: 2px;
        }

        .price {
          display: flex;
          align-items: center;
          margin-top: 6px;

          img {
            width: 70px;
            height: 15px;
          }

          .num {
            font-size: 20px;
            margin-right: 4px;
            font-family: SemiBold;

            span {
              font-size: 12px;
            }
          }

          .label {
            width: 16px;
            height: 18px;
            margin-left: 5px;
            text-align: center;
            line-height: 18px;
            font-size: 11px;

            &.on {
              margin-left: 0;
            }
          }
        }
      }
    }
  }
}

.store-info {
  font-size: 10px;
  color: #666666;
  line-height: 10px;
  margin-top: 4px;
  padding-bottom: 2px;

  span {
    color: #282828;
  }

  .iconfont {
    font-size: 8px;
    color: inherit;
  }
}
</style>

<template>
  <div v-if="renderData" :style="commonVars" class="home-store-comp">
    <div class="top-title">
      <img v-if="renderData.titleType.tabVal == 0" :src="renderData.titleImg.url" class="title-img" />
      <div class="title-text" v-else>
        推荐店铺
      </div>
      <div class="filter-box" v-if="renderData.filterBtn.tabVal == 0">
        <span class="filter-item active">综合</span>
        <span class="filter-item-divider"></span>
        <span class="filter-item">距离</span>
      </div>
    </div>
    <!-- 样式一 -->
    <template v-if="renderData.styleConfig.tabVal == 0">
      <div v-for="store of storeList" :key="store.mer_id" class="store-item store-style1"
        :style="getStoreItemVars(store)">
        <div class="top-banner">
          <div class="mer-info-box">
            <img :src="store.mer_avatar" class="mer-avatar">
            <span class="store-name">{{ store.mer_name }}</span>
            <span class="divider"></span>
            <span class="store-desc">1.6km</span>
            <span class="store-type">{{ store.merchantType.type_name }}</span>
          </div>
        </div>
        <div class="good-list">
          <div class="good-item" v-for="good of renderData.goodsNum.val || 3" :key="good">
            <div class="good-img" />
            <p class="good-name line1" v-if="renderData.goodsInfo.type.includes(0)">无线蓝牙耳机</p>
            <p class="good-price" v-if="renderData.goodsInfo.type.includes(1)">￥1299.00</p>
          </div>
        </div>
      </div>
    </template>
    <!-- 样式二 -->
    <template v-else-if="renderData.styleConfig.tabVal == 1">
      <div v-for="store of storeList" :key="store.mer_id" class="store-item store-style2"
        :style="getStoreItemVars(store)">
        <div class="store-info">
          <img :src="store.mer_avatar" class="store-avatar">
          <div>
            <div class="store-name">
              {{ store.mer_name }}
              <span class="store-type">{{ store.merchantType.type_name }}</span>
            </div>
            <div class="store-desc">
              <span class="store-follow-text">
                52.1万人关注
              </span>
              <span class="store-divider"></span>
              <span class="store-distance">
                1.2km
              </span>
            </div>
          </div>
        </div>
        <div class="good-list">
          <div class="good-item" v-for="good of renderData.goodsNum.val || 4" :key="good">
            <div class="good-img" />
            <p class="good-name line1" v-if="renderData.goodsInfo.type.includes(0)">无线蓝牙耳机</p>
            <p class="good-price" v-if="renderData.goodsInfo.type.includes(1)">￥1299.00</p>
          </div>
        </div>
      </div>
    </template>
    <!-- 样式三 -->
    <div class="store-grid" v-else-if="renderData.styleConfig.tabVal == 2">
      <img v-for="store of storeList" :key="store.mer_id" class="store-item store-style3"
        :style="getStoreItemVars(store)" :src="store.mer_avatar" />
    </div>
    <div class="more-btn" v-if="renderData.moreBtn.tabVal == 0">
      更多店铺
      <i class="iconfont iconyou" />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { diyUtil } from '@/utils/diy-util';

export default {
  name: "home_store",
  cname: "店铺街",
  configName: "c_home_store",
  icon: "#iconzujian-dianpujie",
  type: 0,
  defaultName: "homeStore",
  sortOrder: 100,
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
    commonVars() {
      if (!this.renderData) return {};
      const {
        titleColor,
        titleFontSize,
        titleFontStyle,

        storeImgRounded,
        storePadding,
        storeNameStyle,
        storeNameColor,
        selfSupportBgColor,
        selfSupportTextColor,

        goodsImgRounded,
        goodsPadding,
        goodsNameColor,
        goodsNameStyle,
        goodsShadowConfig,

        componentFloat,
        componentBg,
        componentBottomBg,
        marginTop,
        marginBottom,
        marginLr,
        itemMarginTop,
        bgRadius
      } = this.renderData;

      const vars = {
        '--title-color': titleColor.color[0].item,
        '--title-font-size': titleFontSize.val + 'px',

        '--store-img-rounded': diyUtil.buildBorderRadius(storeImgRounded, "top"),
        '--store-gap': storePadding.val + 'px',
        '--store-name-color': storeNameColor.color[0].item,
        '--self-support-bg-color': selfSupportBgColor.color[0].item,
        '--self-support-text-color': selfSupportTextColor.color[0].item,

        '--goods-img-rounded': diyUtil.buildBorderRadius(goodsImgRounded),
        '--goods-gap': goodsPadding.val + 'px',
        '--goods-name-color': goodsNameColor.color[0].item,
        '--goods-shadow': diyUtil.buildShadowStyle(goodsShadowConfig),

        '--comp-bg': diyUtil.buildLinearColor(componentBg),
        '--base-bg': componentBottomBg.color[0].item,
        '--comp-pt': marginTop.val + 'px',
        '--comp-pb': marginBottom.val + 'px',
        '--comp-p-inline': marginLr.val + 'px',
        '--comp-bg-radius': diyUtil.buildBorderRadius(bgRadius),
        '--comp-shadow': diyUtil.buildShadowStyle(this.renderData.shadowConfig),

        marginTop: diyUtil.buildMarginTopOffset(itemMarginTop, componentFloat),
      };

      if (titleFontStyle.tabVal == 1) {
        vars['--title-font-style'] = 'italic';
      } else if (titleFontStyle.tabVal == 2) {
        vars['--title-font-weight'] = 'bold';
      }

      if (storeNameStyle.tabVal == 0) {
        vars['--store-name-font-weight'] = 'bold';
      }

      if (goodsNameStyle.tabVal == 0) {
        vars['--goods-name-font-weight'] = 'bold';
      }

      return vars;
    },
    storeList() {
      if (!this.renderData) return [];
      return this.renderData.storeList.list.slice(0, this.renderData.storeNum.val);
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
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '店铺街',
        name: 'homeStore',
        timestamp: this.num,
        isHide: false,

        setUp: {
          tabVal: 0,
        },
        // 标题设置
        titleSetting: '标题设置',
        titleType: {
          title: '标题类型',
          tabVal: 0,
          tabList: [
            { name: '图片' },
            { name: '文字' },
          ],
        },
        titleImg: {
          type: 'code',
          info: '建议：140px * 44px',
          url: require('@/assets/images/recommend_shop.png'),
          delType: 1,
          name: '标题图片',
        },
        // 展示风格
        displaySetting: '展示风格',
        styleConfig: {
          title: '选择风格',
          tabVal: 2,
          tabList: [
            { name: '样式一' },
            { name: '样式二' },
            { name: '样式三' },
          ],
        },
        moreBtn: {
          title: '更多按钮',
          tabVal: 0,
          tabList: [
            { name: '显示' },
            { name: '隐藏' },
          ],
        },
        filterBtn: {
          title: '筛选条件',
          tabVal: 0,
          tabList: [
            { name: '显示' },
            { name: '隐藏' },
          ],
        },
        // 店铺设置
        storeSetting: '店铺设置',
        selectStoreType: {
          title: '选择方式',
          activeValue: 1,
          list: [
            { activeValue: 1, title: '指定店铺' },
            { activeValue: 2, title: '店铺类型' },
            { activeValue: 3, title: '品牌好店' },
            { activeValue: 4, title: '店铺分类' }
          ],
        },
        storeType: {
          title: '店铺类型',
          activeValue: [],
          list: [],
          multiple: true,
          filterable: true,
          dataSource: 'storeType',
        },
        storeCate: {
          title: '店铺分类',
          activeValue: [],
          list: [],
          multiple: true,
          filterable: true,
          dataSource: 'storeCate',
        },
        storeGroup: {
          title: '店铺分组',
          activeValue: [],
          list: [],
          multiple: true,
          filterable: true,
          dataSource: 'storeGroup',
        },
        storeList: {
          title: '选择店铺',
          list: [],
        },
        storeSort: {
          title: '默认排序',
          tabVal: 0,
          tabList: [
            { name: '默认' },
            { name: '距离' },
          ],
        },
        storeNum: {
          title: '展示数量',
          val: 5,
          min: 1,
        },
        linkConfig: {
          title: '更多链接',
          place: '请输入更多链接',
          max: 100,
          type: "link",
          value: "/pages/store/shopStreet/index"
        },
        // 商品设置
        goodsSetting: '商品设置',
        goodsInfo: {
          title: '展示信息',
          name: "goodsInfo",
          type: [0, 1],
          list: [
            { id: 0, name: '商品名称' },
            { id: 1, name: '商品价格' },
          ],
        },
        goodsNum: {
          title: '展示数量',
          val: 10,
          min: 1,
        },
        // ===== 标题样式 =====
        titleRight: '标题设置',
        titleColor: {
          title: '标题颜色',
          default: [{ item: '#333' }],
          color: [{ item: '#333' }]
        },
        titleFontSize: {
          title: '标题大小',
          val: 13,
          min: 10,
          max: 16
        },
        titleFontStyle: {
          title: '标题样式',
          tabVal: 0,
          tabList: [
            { name: '正常' },
            { name: '倾斜' },
            { name: '加粗' }
          ]
        },
        // ===== 店铺样式 =====
        storeImgTitle: '店铺样式',
        storeImgRounded: {
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
          val: 8,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        storePadding: {
          title: '店铺间距',
          val: 10,
          min: 0
        },
        storeNameStyle: {
          title: '店铺名称',
          tabVal: 0,
          tabList: [
            { name: '加粗' },
            { name: '正常' }
          ]
        },
        storeNameColor: {
          title: '店铺名称',
          default: [{ item: '#282828' }],
          color: [{ item: '#282828' }]
        },
        selfSupportBgColor: {
          title: '自营背景',
          default: [{ item: '#E93323' }],
          color: [{ item: '#E93323' }]
        },
        selfSupportTextColor: {
          title: '自营文字',
          default: [{ item: '#fff' }],
          color: [{ item: '#fff' }]
        },
        // ===== 商品样式 =====
        goodsStyleTitle: '商品样式',
        goodsImgRounded: {
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
          val: 4,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        goodsPadding: {
          title: '内容间距',
          val: 7,
          min: 0
        },
        goodsNameStyle: {
          title: '商品名称',
          tabVal: 1,
          tabList: [
            { name: '加粗' },
            { name: '正常' }
          ]
        },
        goodsNameColor: {
          title: '商品名称',
          default: [{ item: '#282828' }],
          color: [{ item: '#282828' }]
        },
        goodsShadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },
        // ===== 通用样式 =====
        commonStyleTitle: '卡片样式',
        componentFloat: {
          title: '组件上浮',
          val: 0,
          min: 0
        },
        componentBg: {
          title: '组件背景',
          default: [
            { item: '#fff' },
            { item: '#fff' },
          ],
          color:
            [
              { item: '#fff' },
              { item: '#fff' },
            ]
        },
        componentBottomBg: {
          title: '底部背景',
          default: [{ item: '#f5f5f5' }],
          color: [{ item: '#f5f5f5' }]
        },
        marginTop: {
          title: '上边距',
          val: 10,
          min: 0
        },
        marginBottom: {
          title: '下边距',
          val: 0,
          min: 0
        },
        marginLr: {
          title: '左右边距',
          val: 10,
          min: 0
        },
        itemMarginTop: {
          title: '页面上间距',
          val: 10,
          min: 0
        },
        bgRadius: {
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
          val: 8,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        shadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        }
      },


      pageData: {},

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
    getStoreItemVars(item) {
      return {
        '--bg-src': `url(${item.mer_banner})`
      };
    },
    setConfig(data) {
      if (data && data.storeList) {
        this.renderData = JSON.parse(JSON.stringify(data));
      }
    }
  },
};
</script>

<style scoped lang="scss">
.top-title {
  padding: 0 5px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;

  .title-text {
    font-size: var(--title-font-size);
    color: var(--title-color);
    font-style: var(--title-font-style);
    font-weight: var(--title-font-weight);
  }

  .title-img {
    width: 70px;
    height: 16px;
    object-fit: cover;
  }

  .filter-item {
    font-size: 13px;
    line-height: 13px;
    color: #666;

    &.active {
      font-weight: 500;
      color: #E93323;
    }
  }

  .filter-item-divider {
    display: inline-block;
    width: 1px;
    height: 8px;
    background: #CCCCCC;
    margin-inline: 10px;
  }
}

.more-btn {
  background-color: #fff;
  height: 40px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  color: #999;
  margin-top: 10px;

  .iconfont {
    font-size: 10px;
    margin-left: 5px;
  }
}

.store-item {
  background: var(--comp-bg);
  border-radius: var(--comp-bg-radius);
}

.store-style1 {
  background-color: #fff;

  &+.store-item {
    margin-top: var(--store-gap);
  }

  .top-banner {
    position: relative;

    &::before {
      content: '';
      position: absolute;
      inset: 0;
      background: var(--bg-src) no-repeat center / cover;
      border-radius: var(--store-img-rounded);
    }

    aspect-ratio: calc(355 / 100);
    padding: 11px 10px;
  }

  .mer-info-box {
    position: relative;
    width: fit-content;
    height: 24px;
    background: #FFFFFF;
    border-radius: 20px;
    display: flex;
    align-items: center;
    padding: 0 5px 0 2px;

    .mer-avatar {
      width: 19px;
      height: 19px;
      border-radius: 50%;
      margin-right: 6px;
      object-fit: cover;
    }

    .store-name {
      font-weight: var(--store-name-font-weight);
      font-size: 14px;
      color: var(--store-name-color);
    }

    .divider {
      width: 1px;
      height: 12px;
      background: #D8D8D8;
      margin: 0 4px;
    }

    .store-desc {
      font-size: 12px;
      color: #282828;
    }

    .store-type {
      padding-inline: 5px;
      min-width: 28px;
      height: 15px;
      background: var(--self-support-bg-color);
      border-radius: 34px;
      font-size: 9px;
      color: var(--self-support-text-color);
      margin-left: 6px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
  }

  .good-list {
    display: grid;
    gap: var(--goods-gap);
    padding: 10px;
    grid-auto-flow: column;
    grid-auto-columns: calc((100% - var(--goods-gap) * 2) / 3);
    overflow-x: auto;
    scrollbar-width: none;
    &::-webkit-scrollbar {
      display: none;
    }

    .good-item {
      box-shadow: var(--goods-shadow);
    }

    .good-img {
      display: block;
      width: 100%;
      aspect-ratio: 1;
      object-fit: contain;
      border-radius: var(--goods-img-rounded);
      margin-bottom: 9px;
      background: url("../../assets/images/example-good.png") no-repeat center / cover;
    }

    .good-name {
      text-align: center;
      margin-bottom: 13px;
      font-size: 15px;
      color: var(--goods-name-color);
      font-weight: var(--goods-name-font-weight);
    }

    .good-price {
      // font-family: semiBold;
      font-weight: 600;
      font-size: 14px;
      color: #E93323;
      text-align: center;
      line-height: 18px;
    }
  }
}

.store-style2 {
  background: var(--comp-bg);
  padding: 10px;
  padding-right: 0;
  & + .store-item {
    margin-top: var(--store-gap);
  }

  .store-info {
    display: flex;
    align-items: center;

    .store-avatar {
      width: 50px;
      height: 50px;
      border-radius: 6px;
      margin-right: 10px;
    }

    .store-name {
      font-size: 14px;
      color: var(--store-name-color);
      font-weight: var(--store-name-font-weight);
      display: inline-flex;
      align-items: center;
      margin-bottom: 8px;
    }

    .store-type {
      margin-left: 4px;
      background: var(--self-support-bg-color);
      color: var(--self-support-text-color);
      height: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding-inline: 4px;
      border-radius: 2px;
      font-size: 10px;
    }

    .store-divider {
      width: 1px;
      height: 10px;
      background: #CCCCCC;
      margin: 0 4px;
    }

    .store-desc {
      font-size: 12px;
      color: #666666;
      line-height: 17px;
      display: flex;
      align-items: center;
    }
  }

  .good-list {
    margin-top: 3px;
    overflow: auto;
    white-space: nowrap;
    scrollbar-width: none;

    &::-webkit-scrollbar {
      display: none;
    }
  }

  .good-item {
    display: inline-block;
    box-shadow: var(--goods-shadow);

    &+.good-item {
      margin-left: var(--goods-gap);
    }

    &:first-child {
      margin-left: 60px;
    }

    .good-img {
      width: 76px;
      height: 76px;
      border-radius: var(--goods-img-rounded);
      margin-bottom: 6px;
      background: url("../../assets/images/example-good.png") no-repeat center / contain;
    }

    .good-name {
      font-size: 12px;
      line-height: 17px;
      margin-bottom: 4px;
      text-align: center;
      color: var(--goods-name-color);
      font-weight: var(--goods-name-font-weight);
    }

    .good-price {
      font-family: semiBold;
      font-size: 14px;
      color: #E93323;
      font-weight: 600;
    }
  }
}

.store-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--store-gap);

  .store-style3 {
    width: 100%;
    aspect-ratio: 1;
    object-fit: contain;
    border-radius: var(--store-img-rounded);
  }
}

.home-store-comp {
  background: var(--base-bg);
  padding: var(--comp-pt) var(--comp-p-inline) var(--comp-pb);
  box-shadow: var(--comp-shadow);
}
</style>

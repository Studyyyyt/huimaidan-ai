<template>
  <div :style="wrapStyle" class="wrap-box">
    <div :style="compStyle" v-if="renderData" class="search-box">
      <!-- 标题 -->
      <p class="title full" :style="titleStyle" v-if="renderData.styleConfig.tabVal == 0">{{
        renderData.titleConfig.value }}</p>
      <!-- 定位 -->
      <p class="title full" :style="titleStyle" v-else-if="renderData.styleConfig.tabVal == 1">
        <i class="iconfont icondingwei" />
        咸阳市秦都区
        <i class="iconfont iconyou" />
      </p>

      <!-- 搜索 -->
      <template v-else-if="renderData.styleConfig.tabVal == 2">
        <template v-if="renderData.displaySearchStyleConfig.tabVal == 2">
          <img v-if="renderData.logoConfig.url" :src="renderData.logoConfig.url" class="logo mr10" />
        </template>
        <p class="title limit mr10" :style="titleStyle" v-else>
          <i class="iconfont icondingwei" v-if="renderData.displaySearchStyleConfig.tabVal == 1" />
          <template v-if="renderData.displaySearchStyleConfig.tabVal == 0">
            {{ renderData.titleConfig.value }}
          </template>
          <template v-else>
            金湾大厦...
          </template>
          <i class="iconfont iconyou" v-if="renderData.displaySearchStyleConfig.tabVal == 1" />
        </p>
        <div class="input-box" :style="inputWrapperText">
          <i class="iconfont iconsousuo1" />
          <span :style="inputHotWordsTextStyle" v-if="renderData.hotWords.list.length ">{{ hotWordsText }}</span>
          <span :style="inputTipTextStyle" v-else>{{ inputTipText }}</span>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { diyUtil } from '@/utils/diy-util';

export default {
  name: 'search_box',
  cname: '搜索框',
  icon: '#iconzujian-sousuokuang',
  configName: 'c_search_box',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'headerSerch', // 外面匹配名称
  targetScope: "platform",
  sortOrder: 1,
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
    wrapStyle() {
      if (!this.renderData) return {};
      const { bottomBgColor, offsetYConfig } = this.renderData;
      return {
        marginTop: diyUtil.buildMarginTopOffset(undefined, offsetYConfig),
        backgroundColor: bottomBgColor.color[0].item
      };
    },
    compStyle() {
      if (!this.renderData) return {};
      const { moduleColor } = this.renderData;
      return {
        background: diyUtil.buildLinearColor(moduleColor),
        margin: `${this.renderData.topConfig.val}px ${this.renderData.prConfig.val}px ${this.renderData.bottomConfig.val}px ${this.renderData.prConfig.val}px`,
        borderRadius: diyUtil.buildBorderRadius(this.renderData.fillet),
        boxShadow: diyUtil.buildShadowStyle(this.renderData.shadowConfig),
      };
    },
    titleStyle() {
      if (!this.renderData) return {};
      const { textColor, fontSizeConfig, textPosition, textStyle } = this.renderData;

      return {
        fontSize: `${fontSizeConfig.val}px`,
        color: textColor.color[0].item,
        textAlign: textPosition.tabList[textPosition.tabVal].val,
        fontStyle: textStyle.tabVal === 1 ? 'italic' : 'normal',
        fontWeight: textStyle.tabVal === 2 ? 'bold' : 'normal',
      };
    },
    hotWordsText() {
      if (!this.renderData) return "";
      const { hotWords } = this.renderData;
      if (hotWords.list.length > 0) {
        return hotWords.list[0].val;
      }
      return "";
    },
    inputTipText() {
      if (!this.renderData) return "";
      const { tipConfig, hotWords } = this.renderData;
      if (hotWords.list.length > 0 && hotWords.list[0].val) {
        return hotWords.list[0].val;
      }
      return tipConfig.value;
    },
    inputWrapperText() {
      if (!this.renderData) return {};
      const { searchBoxColor, textPosition } = this.renderData;
      return {
        backgroundColor: searchBoxColor.color[0].item,
        justifyContent: textPosition.tabList[textPosition.tabVal].val
      };
    },
    inputTipTextStyle() {
      if (!this.renderData) return {};
      const { tipColor } = this.renderData;
      return {
        color: tipColor.color[0].item,
      };
    },
    inputHotWordsTextStyle() {
      if (!this.renderData) return {};
      const { hotWordsColor } = this.renderData;
      return {
        color: hotWordsColor.color[0].item,
      };
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
        cname: '搜索框',
        name: 'headerSerch',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleSearch: '搜索内容',
        titleHotWords: '搜索热词',
        titleRight: '搜索框',
        titleCurrency: '卡片样式',
        textTitle: "文字设置",
        fixedConfig: {
          title: "搜索设置",
          tabVal: 0,
          tabList: [
            {
              name: '正常显示'
            },
            {
              name: '滚动至顶部固定'
            }
          ]
        },
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          tabList: [
            {
              name: '标题',
            },
            {
              name: '定位',
            },
            {
              name: '搜索',
            }
          ],
        },
        displaySearchStyleConfig: {
          title: '样式类型',
          tabVal: 0,
          tabList: [
            {
              name: '标题',
            },
            {
              name: '定位',
            },
            {
              name: 'logo',
            }
          ],
        },
        logoConfig: {
          info: '建议：144px * 44px',
          url: '',
          type: 'code',
          delType: 1,
          name: 'logo图',
        },
        titleConfig: {
          title: '标题',
          value: '标题',
          place: '请输入标题',
          max: 6,
        },
        linkConfig: {
          title: '链接',
          value: '',
          place: '请选择链接',
          max: 100,
          type: 'link',
        },
        tipConfig: {
          title: '提示文字',
          value: '搜索商品',
          place: '填写内容',
          max: 20,
        },
        hotWords: {
          list: [
            {
              val: '',
            },
          ],
        },
        numConfig: {
          placeholder: '设置搜索热词显示时间',
          title: '显示时间',
          unit: "秒",
          val: 3,
          type: 'words',
        },
        searchBoxColor: {
          title: '搜索框',
          default: [
            {
              item: '#F5F5F5',
            },
          ],
          color: [
            {
              item: '#F5F5F5',
            },
          ],
        },
        tipColor: {
          title: '提示文字',
          default: [
            {
              item: '#CCCCCC',
            },
          ],
          color: [
            {
              item: '#CCCCCC',
            },
          ],
        },
        hotWordsColor: {
          title: '热词文字',
          default: [
            {
              item: '#888',
            },
          ],
          color: [
            {
              item: '#888',
            },
          ],
        },
        textColor: {
          title: '文字颜色',
          default: [
            {
              item: '#888',
            },
          ],
          color: [
            {
              item: '#888',
            },
          ],
        },
        fontSizeConfig: {
          title: '文字大小',
          val: 14,
          min: 10,
        },
        textPosition: {
          title: '文字位置',
          tabVal: 0,
          tabList: [
            {
              name: '左对齐',
              val: "left"
            },
            {
              name: '居中对齐',
              val: "center"
            },
            {
              name: '右对齐',
              val: "right"
            },
          ],
        },
        textStyle: {
          title: '文字样式',
          tabVal: 0,
          tabList: [
            {
              name: '正常'
            },
            {
              name: '倾斜'
            },
            {
              name: '加粗'
            },
          ],
        },
        offsetYConfig: {
          title: '组件上浮',
          val: 0,
          min: 0,
        },
        moduleColor: {
          title: '组件背景',
          default: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
        },
        bottomBgColor: {
          title: '底部背景',
          default: [
            {
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
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
          val: 0,
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
          max: 30,
          valList: [{ val: 0, max: 360 }, { val: 0, max: 360 }, { val: 0, max: 360 }, { val: 0, max: 360 }],
        },

        shadowConfig: {
          color: "#888",
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },
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
    setConfig(data) {
      if (data && data.prConfig) {
        this.renderData = JSON.parse(JSON.stringify(data));
      }
    }
  },
};
</script>

<style scoped lang="scss">
.wrap-box {
  display: flow-root;
}

.search-box {
  height: 48px;
  padding: 0 15px;
  display: flex;
  align-items: center;
  font-size: 15px;
  color: #333;

  .title {
    &.full {
      flex: 1;
    }

    &.limit {
      max-width: 100px;
      overflow: hidden;
      white-space: nowrap;
    }

    .iconfont {
      font-size: 13px;
    }
  }

  .logo {
    width: 62px;
    height: 22px;
    object-fit: cover;
  }

  .input-box {
    flex: 1;
    height: 30px;
    background: #F5F5F5;
    border-radius: 16px;
    display: flex;
    align-items: center;
    padding: 0 15px;

    .iconfont {
      font-size: 13px;
      margin-right: 9px;
    }
  }
}
</style>

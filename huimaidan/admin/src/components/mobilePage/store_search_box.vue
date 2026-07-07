<template>
  <div :style="compStyle" class="wrap-box" v-if="renderData">
    <div class="search-box">
      <img class="search-logo" :src="renderData.logoConfig.url" v-if="renderData.styleConfig.tabVal === 0" />
      <p class="search-title" v-else-if="renderData.styleConfig.tabVal === 1">
        {{ renderData.titleConfig.value }}
      </p>
      <div class="search-content"
        :class="{ 'center-content': renderData.styleConfig.tabVal === 2 && renderData.displaySearchStyleConfig.tabVal === 0 }">
        <p v-if="hotWordsText" class="search-hot-words"><i class="iconfont iconsousuo1" />{{ hotWordsText }}</p>
        <p v-else class="search-tip"><i class="iconfont iconsousuo1" />{{ inputTipText }}</p>
        <div class="float-search-btn"
          v-if="renderData.styleConfig.tabVal === 2 && renderData.displaySearchStyleConfig.tabVal === 1">
          搜索
        </div>
      </div>
      <div class="search-btn"
        v-if="renderData.styleConfig.tabVal === 2 && renderData.displaySearchStyleConfig.tabVal === 2">
        搜索
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { diyUtil } from '@/utils/diy-util';

export default {
  name: 'store_search_box',
  cname: '搜索框',
  icon: '#iconzujian-sousuokuang',
  configName: 'c_store_search_box',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'storeHeaderSerch', // 外面匹配名称
  targetScope: "store",
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
    compStyle() {
      if (!this.renderData) return {};
      const {
        moduleColor,
        styleConfig
      } = this.renderData;
      const style = {
        "--comp-bg": diyUtil.buildLinearColor(moduleColor),
        "--bottom-bg": this.renderData.bottomBgColor.color[0].item,
        "--mr-t": this.renderData.topConfig.val,
        "--mr-b": this.renderData.bottomConfig.val,
        "--mr-l": this.renderData.prConfig.val,
        "--radius": diyUtil.buildBorderRadius(this.renderData.fillet),
        "--shadow": diyUtil.buildShadowStyle(this.renderData.shadowConfig),

        "--search-bg": this.renderData.searchBoxColor.color[0].item,
        "--tip-color": this.renderData.tipColor.color[0].item,
        "--hot-words-color": this.renderData.hotWordsColor.color[0].item,
        marginTop: diyUtil.buildMarginTopOffset(undefined, this.renderData.compOffSetY)
      };

      const btnStyle = this.renderData.displaySearchStyleConfig.tabVal
      if (btnStyle !== 0 && styleConfig.tabVal === 2) {
        if (this.renderData.toneConfig.tabVal === 1) {
          style["--search-text-color"] = this.renderData.searchBtnColor.color[0].item;
          style["--search-box-shadow"] = diyUtil.buildShadowStyle(this.renderData.searchBoxShadowConfig);
        } else {
          if (btnStyle === 2) {
            style["--search-text-color"] = "#E93323";
          } else if (btnStyle === 1) {
            style["--search-text-color"] = "#fff";
          }
        }
      }

      return style;
    },
    hotWordsText() {
      if (!this.renderData) return "";
      const { hotWords } = this.renderData;
      if (hotWords.list.length > 0 && hotWords.list[0].val) {
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
        name: 'storeHeaderSerch',
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
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          tabList: [
            {
              name: 'logo+搜索',
            },
            {
              name: '标题+搜索',
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
              name: '样式1',
            },
            {
              name: '样式2',
            },
            {
              name: '样式3',
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
        topLogoConfig: {
          info: '建议：144px * 44px',
          url: '',
          type: 'code',
          delType: 1,
          name: '吸顶logo图',
        },
        titleConfig: {
          title: '标题',
          value: '标题',
          place: '请输入标题',
          max: 6,
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
          val: 3,
          type: 'words',
        },
        searchBoxColor: {
          title: '搜索框',
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
        searchBtnColor: {
          title: '搜索文字',
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
        searchBoxShadowConfig: {
          color: "#888",
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },
        compOffSetY: {
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
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
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
  background-color: var(--bottom-bg);
  padding: var(--mr-t) var(--mr-l) var(--mr-b) var(--mr-l);
}

.search-box {
  background: var(--comp-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 9px 15px;

  display: flex;
  align-items: center;

  .search-logo {
    width: 62px;
    height: 22px;
    object-fit: cover;
    margin-right: 11px;
  }

  .search-title {
    font-weight: 500;
    font-size: 15px;
    color: #333333;
    line-height: 16px;
    margin-right: 15px;
  }

  .search-content {
    background: var(--search-bg);
    flex: 1;
    height: 30px;
    border-radius: 16px;
    display: inline-flex;
    padding-left: 16px;
    align-items: center;
    box-shadow: var(--search-box-shadow);

    &.center-content {
      justify-content: center;
    }

    .search-hot-words {
      color: var(--hot-words-color);
    }

    .search-tip {
      color: var(--tip-color);
    }

    .iconfont {
      font-size: 14px;
      margin-right: 9px;
      color: inherit;
    }
  }

  .float-search-btn,
  .search-btn {
    color: var(--search-text-color, #fff);
  }

  .search-btn {
    border-radius: 13px;
    font-size: 15px;
    line-height: 21px;
    margin-left: 10px;
  }

  .float-search-btn {
    font-size: 11px;
    width: 46px;
    height: 26px;
    background: #E93323;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-inline: auto 2px;
  }
}
</style>

<template>
  <div :style="commonCssVars" class="home-recommend" :class="{ style2: renderData.styleConfig.tabVal === 1 }"
    v-if="renderData">
    <div class="home-recommend-item" v-for="(item, index) of renderData.recommendList.list" :key="index">
      <div class="item-intro">
        <div class="intro-title line1">{{ item.info[0].value }}</div>
        <div class="intro-desc line1">{{ item.info[1].value }}</div>
      </div>
      <img :src="item.img" class="item-img" v-if="item.img" />
      <div v-else class="item-img placeholder" />
    </div>
  </div>
</template>

<script>
import { diyUtil } from '@/utils/diy-util';
import { mapState } from 'vuex';

export default {
  name: "home_recommend",
  cname: "推荐组",
  configName: "c_home_recommend",
  icon: "#iconzujian-tuijianzu",
  type: 0,
  defaultName: "homeRecommend",
  sortOrder: 120,
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
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
    commonCssVars() {
      if (!this.renderData) return {};
      const {
        imageRound,
        imageShadowConfig,
        ptConfig,
        bottomBgColor,
        topConfig,
        bottomConfig,
        prConfig,
        mbConfig,
        fillet,
        shadowConfig,
      } = this.renderData;
      return {
        '--image-round': diyUtil.buildBorderRadius(imageRound),
        '--image-shadow': diyUtil.buildShadowStyle(imageShadowConfig),

        '--comp-bg-color': diyUtil.buildLinearColor(bottomBgColor),
        '--comp-top-pd': topConfig.val + "px",
        '--comp-bottom-pd': bottomConfig.val + "px",
        '--comp-pr-pd': prConfig.val + "px",
        '--border-radius': diyUtil.buildBorderRadius(fillet),
        '--comp-shadow': diyUtil.buildShadowStyle(shadowConfig),

        marginTop: diyUtil.buildMarginTopOffset(mbConfig, ptConfig),
      }
    },
  },
  data() {
    return {
      defaultConfig: {
        cname: "推荐组",
        name: "homeRecommend",
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        displayTitle: "展示设置",
        styleConfig: {
          title: "选择风格",
          tabVal: 0,
          tabList: [
            {
              name: "样式一"
            },
            {
              name: "样式二"
            }
          ]
        },
        contentTitle: "内容设置",
        recommendList: {
          title: "建议：宽度150*150px；鼠标拖拽版块可调整图片顺序",
          maxList: 4,
          bnt: "添加",
          list: [
            {
              img: "",
              info: [
                {
                  title: "标题",
                  value: "首发新品",
                  tips: "请输入标题",
                  max: 4,
                  showLimit: true
                },
                {
                  title: "简介",
                  value: "新品抢先购",
                  tips: "请输入简介",
                  max: 6,
                  showLimit: true
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                }
              ]
            },
            {
              img: "",
              info: [
                {
                  title: "标题",
                  value: "热门榜单",
                  tips: "请输入标题",
                  max: 4,
                  showLimit: true
                },
                {
                  title: "简介",
                  value: "剁手必备指南",
                  tips: "请输入简介",
                  max: 6,
                  showLimit: true
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                }
              ]
            },
            {
              img: "",
              info: [
                {
                  title: "标题",
                  value: "精品推荐",
                  tips: "请输入标题",
                  max: 4,
                  showLimit: true
                },
                {
                  title: "简介",
                  value: "发现品质好物",
                  tips: "请输入简介",
                  max: 6,
                  showLimit: true
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                }
              ]
            },
            {
              img: "",
              info: [
                {
                  title: "标题",
                  value: "促销单品",
                  tips: "请输入标题",
                  max: 4,
                  showLimit: true
                },
                {
                  title: "简介",
                  value: "惊喜折扣价",
                  tips: "请输入简介",
                  max: 6,
                  showLimit: true
                },
                {
                  title: '链接',
                  value: '',
                  tips: '请输入链接',
                  max: 100,
                }
              ]
            }
          ]
        },

        imageStyleTitle: "图标样式",
        imageRound: {
          title: '图标圆角',
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
        imageShadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },

        compStyleTitle: "卡片样式",
        ptConfig: {
          title: '组件上浮',
          val: 0,
          min: 0,
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
          val: 10,
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
          val: 8,
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
      renderData: null,
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
      if (data.shadowConfig) {
        this.renderData = data;
      }
    }
  }
}
</script>

<style scoped lang="scss">
.wrapper {
  display: flow-root;
}

.home-recommend {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 5px;
  padding-top: var(--comp-top-pd);
  padding-bottom: var(--comp-bottom-pd);
  padding-inline: var(--comp-pr-pd);
  background: var(--comp-bg-color);
  box-shadow: var(--comp-shadow);

  &.style2 {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;

    .home-recommend-item {
      display: flex;
      padding: 12px;

      .item-intro {
        text-align: left;
        flex: 1;
        margin-bottom: 0;
        display: flex;
        flex-flow: column;
        justify-content: center;
      }

      .intro-title {
        margin-bottom: 8px;
      }

      .item-img {
        width: 74px;
      }
    }
  }

  .home-recommend-item {
    background-color: #fff;
    padding: 10px;
    padding-bottom: 6px;
    overflow: hidden;
    border-radius: var(--border-radius);

    .item-intro {
      text-align: center;
      margin-bottom: 7px;
    }

    .intro-title {
      font-weight: 600;
      font-size: 14px;
      color: #282828;
      line-height: 14px;
      margin-bottom: 6px;
    }

    &:nth-child(4n - 3) {
      --desc-color: #8FBBE8;
    }

    &:nth-child(4n - 2) {
      --desc-color: #D797B7;
    }

    &:nth-child(4n - 1) {
      --desc-color: #C49BD1;
    }

    &:nth-child(4n) {
      --desc-color: #A3BF95;
    }

    .intro-desc {
      font-size: 10px;
      color: var(--desc-color);
      line-height: 10px;
    }

    .item-img {
      width: 100%;
      aspect-ratio: 1;
      border-radius: var(--image-round);
      box-shadow: var(--image-shadow);

      &.placeholder {
        background: url(~@/assets/images/shan.png) no-repeat center / 70% #f3f9ff;
      }
    }
  }
}
</style>

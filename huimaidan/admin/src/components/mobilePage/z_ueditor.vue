<template>
  <div
    class="mobile-page"
    :style="wrapperStyle"
  >
    <div
      class="box"
      :style="{
        background: bgColor,
        borderRadius: fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px',
      }"
      v-html="richText"
    ></div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { diyUtil } from "@/utils/diy-util";
export default {
  name: 'z_ueditor',
  cname: '富文本',
  configName: 'c_ueditor_box',
  icon: '#iconzujian-fuwenben',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'richText', // 外面匹配名称
  sortOrder: 340,
  props: {
    index: {
      type: null,
      default: -1,
    },
    num: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
    wrapperStyle() {
      if (!this.renderData) return {};
      return {
        background: this.bottomBgColor,
        marginTop: diyUtil.buildMarginTopOffset(this.renderData.udConfig, this.renderData.offsetYConfig),
        paddingTop: this.topConfig + 'px',
        paddingBottom: this.bottomConfig + 'px',
        paddingLeft: this.edge + 'px',
        paddingRight: this.edge + 'px',
        boxShadow: this.shadowConfig,
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
        cname: '富文本',
        name: 'richText',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '富文本内容',
        titleRight: '卡片样式',
        bgColor: {
          title: '组件背景',
          name: 'bgColor',
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
        bottomBgColor: {
          title: '底部背景',
          name: 'bgColor',
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
        lrConfig: {
          title: '左右边距',
          val: 0,
          min: 0,
        },
        udConfig: {
          title: '页面上间距',
          val: 10,
          min: 0,
        },
        richText: {
          val: '',
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
        offsetYConfig: {
          title: "组件上浮",
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
      },
      cSlider: '',
      bgColor: '',
      confObj: {},
      pageData: {},
      edge: '',
      udEdge: '',
      richText: '',
      bottomBgColor: '',
      topConfig: '',
      bottomConfig: '',
      fillet: 0,
      filletVal: 0,
      valList: [],
      offsetYConfig: 0,
      shadowConfig: 'none',

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
      if (data.lrConfig) {
        this.bgColor = data.bgColor.color[0].item;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.edge = data.lrConfig.val;
        this.udEdge = data.udConfig.val;
        this.richText = data.richText.val;
        this.fillet = data.fillet.type;
        this.filletVal = data.fillet.val;
        this.valList = data.fillet.valList;
        this.offsetYConfig = data.offsetYConfig.val;
        this.shadowConfig = diyUtil.buildShadowStyle(data.shadowConfig);
        this.renderData = data;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.mobile-page ::v-deepvideo {
  width: 100% !important;
}

::v-deep.box {
  li {
    list-style: initial;
  }

  ul, ol {
    padding-left: 40px;
  }
}

.box {
  min-height: 42px;
  padding: 10px;
  background-color: #f5f5f5;
  word-break: break-all;
  border-radius: 12px;
  img {
    max-width: 100%;
    height: auto;
  }
}
</style>

<template>
  <div
    class="mobile-page"
    :style="wrapperStyle"
  >
    <div
      class="box"
      :style="{
        borderBottomColor: lineColor,
        borderBottomStyle: style,
      }"
    ></div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { diyUtil } from "@/utils/diy-util";
export default {
  name: 'z_auxiliary_line',
  cname: '辅助线',
  configName: 'c_auxiliary_line',
  icon: '#iconzujian-fuzhuxian',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'guide', // 外面匹配名称
  sortOrder: 300,
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
        marginTop: diyUtil.buildMarginTopOffset(this.renderData.mbConfig, this.renderData.offsetYConfig),
        backgroundColor: this.bgColor,
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
        cname: '辅助线',
        name: 'guide',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '展示设置',
        titleRight: '线条样式',
        titleCurrent: '卡片样式',
        lineColor: {
          title: '线条颜色',
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
        lineBgColor: {
          title: '底部背景',
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
        lineStyle: {
          title: '选择样式',
          tabVal: 1,
          tabList: [
            {
              name: '虚线',
              style: 'dashed',
            },
            {
              name: '实线',
              style: 'solid',
            },
            {
              name: '点状',
              style: 'dotted',
            },
          ],
        },
        offsetYConfig: {
          title: "组件上浮",
          val: 0,
          min: 0,
        },
        topConfig: {
          title: '上边距',
          val: 6,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 6,
          min: 0,
        },
        lrEdge: {
          title: '左右边距',
          val: 0,
          min: 0,
        },
        mbConfig: {
          title: '页面上间距',
          val: 10,
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
      bgColor: '',
      confObj: {},
      pageData: {},
      edge: '',
      udEdge: '',
      topConfig: '',
      bottomConfig: '',
      style: '',
      lineColor: '',
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
      if (data.mbConfig) {
        let styleType = data.lineStyle.tabVal;
        this.bgColor = data.lineBgColor.color[0].item;
        this.lineColor = data.lineColor.color[0].item;
        this.edge = data.lrEdge.val;
        this.udEdge = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.style = data.lineStyle.tabList[styleType].style;
        this.offsetYConfig = data.offsetYConfig.val;
        this.shadowConfig = diyUtil.buildShadowStyle(data.shadowConfig);
        this.renderData = data;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.mobile-page {
  padding: 7px 0;
}
.box {
  border-bottom-width: 1px;
}
</style>

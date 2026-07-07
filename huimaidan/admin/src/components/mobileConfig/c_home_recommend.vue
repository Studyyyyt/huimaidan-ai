<template>
  <div class="mobile-config">
    <div v-for="(item, key) in renderComp" :key="key">
      <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme"
        :key="key" @getConfig="getConfig" :index="activeIndex" :number="num" :num="item.num"></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';

export default {
  name: 'c_home_recommend',
  componentsName: "home_recommend",
  cname: "推荐组",
  props: {
    activeIndex: {
      type: null,
    },
    index: {
      type: null,
    },
    num: {
      type: null,
    },
  },
  components: {
    ...toolCom,
    rightBtn,
  },
  data() {
    return {
      configObj: {},

      baseComp: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],
      contentConfig: {
        fullComp: [],
        components: [
          { // 展示设置
            components: toolCom.c_title,
            configNme: 'displayTitle',
          },
          { // 选择风格
            components: toolCom.c_radio,
            configNme: 'styleConfig',
          },
          { // 内容设置
            components: toolCom.c_title,
            configNme: 'contentTitle',
          },
          { // 推荐列表
            components: toolCom.c_menu_list,
            configNme: 'recommendList',
          }
        ],
      },
      styleConfig: {
        fullComp: [],
        components: [
          { // 图片样式
            components: toolCom.c_title,
            configNme: 'imageStyleTitle',
          },
          { // 图片圆角
            components: toolCom.c_fillet,
            configNme: 'imageRound',
          },
          { // 图片阴影
            components: toolCom.c_shadow,
            configNme: 'imageShadowConfig',
          },
          { // 通用样式 标题
            components: toolCom.c_title,
            configNme: 'compStyleTitle',
          },
          { // 组件上浮
            components: toolCom.c_slider,
            configNme: 'ptConfig',
          },
          { // 底部背景
            components: toolCom.c_bg_color,
            configNme: 'bottomBgColor',
          },
          { // 上边距
            components: toolCom.c_slider,
            configNme: 'topConfig',
          },
          { // 下边距
            components: toolCom.c_slider,
            configNme: 'bottomConfig',
          },
          { // 左右边距
            components: toolCom.c_slider,
            configNme: 'prConfig',
          },
          { // 页面上间距
            components: toolCom.c_slider,
            configNme: 'mbConfig',
          },
          { // 背景圆角
            components: toolCom.c_fillet,
            configNme: 'fillet',
          },
          { // 阴影颜色
            components: toolCom.c_shadow,
            configNme: 'shadowConfig',
          },
        ],
      },
    };
  },
  computed: {
    renderComp() {
      const isStyleMode = this.configObj.setUp && this.configObj.setUp.tabVal === 1;
      const comp = isStyleMode ? this.styleConfig.fullComp : this.contentConfig.fullComp;
      return this.baseComp.concat(comp);
    },
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  watch: {
    configObj: {
      handler(nVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
        this.updateContentConfig();
        this.updateStyleConfig();
      },
      deep: true,
    },
  },
  methods: {
    filterComp(components) {
      return components
        .filter(item => !item.visible || item.visible(this.configObj))
        .reduce((acc, item) => {
          if (item.children) {
            acc.push(...this.filterComp(item.children));
          } else {
            acc.push(item);
          }
          return acc;
        }, []);
    },
    updateContentConfig() {
      this.contentConfig.fullComp = this.filterComp(this.contentConfig.components);
    },
    updateStyleConfig() {
      this.styleConfig.fullComp = this.filterComp(this.styleConfig.components);
    },
    getConfig(data, name) { },
  },
};
</script>

<style scoped lang="scss"></style>

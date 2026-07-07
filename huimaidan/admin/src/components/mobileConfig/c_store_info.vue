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
  name: 'c_store_info',
  componentsName: 'store_info',
  cname: '店铺信息',
  props: {
    activeIndex: {
      type: null,
    },
    num: {
      type: null,
    },
    index: {
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
        ]
      },
      styleConfig: {
        fullComp: [],
        components: [
          { // 店铺样式
            components: toolCom.c_title,
            configNme: 'storeTitle',
          },
          { // 店铺名称
            components: toolCom.c_bg_color,
            configNme: 'storeName',
          },
          { // 关注按钮
            components: toolCom.c_bg_color,
            configNme: 'followBtn',
          },
          { // 通用样式
            components: toolCom.c_title,
            configNme: 'normalTitle',
          },
          { // 组件背景
            components: toolCom.c_bg_color,
            configNme: 'compBg',
          },
          { // 底部背景
            components: toolCom.c_bg_color,
            configNme: 'bottomBg',
          },
          { // 组件上浮
            components: toolCom.c_slider,
            configNme: 'compOffsetY',
          },
          { // 上边距
            components: toolCom.c_slider,
            configNme: 'mtConfig',
          },
          { // 下边距
            components: toolCom.c_slider,
            configNme: 'mbConfig',
          },
          { // 左右边距
            components: toolCom.c_slider,
            configNme: 'mlConfig',
          },
          { // 页面上边距
            components: toolCom.c_slider,
            configNme: 'pageTopConfig',
          },
          { // 圆角
            components: toolCom.c_fillet,
            configNme: 'fillet',
          },
          { // 阴影
            components: toolCom.c_shadow,
            configNme: 'shadowConfig',
          }
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
  watch: {
    num(nVal) {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
      // if (!value.selectConfig.list || !value.selectConfig.list[0].value) {
        // this.getCategory();
      // }
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
        this.updateContentConfig();
        this.updateStyleConfig();
      },
      deep: true,
    }
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
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
    getConfig(data, name) { }
  },
};
</script>

<style scoped lang="scss"></style>

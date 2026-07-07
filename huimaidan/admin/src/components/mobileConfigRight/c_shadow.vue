<template>
  <div>
    <c_radio :configObj="baseConfig" configNme="visible" />
    <template v-if="baseConfig.visible.tabVal == 1">
      <c_bg_color :configObj="baseConfig" configNme="shadowColor" />
      <c_slider :configObj="baseConfig" configNme="shadowX" />
      <c_slider :configObj="baseConfig" configNme="shadowY" />
      <c_slider :configObj="baseConfig" configNme="shadowBlur" /> 
      <c_slider :configObj="baseConfig" configNme="shadowSpread" />
    </template>
  </div>
</template>

<script>
import c_bg_color from './c_bg_color.vue';
import c_slider from './c_slider.vue';
import c_radio from "./c_radio.vue";

export default {
  name: 'c_shadow',
  components: {
    c_bg_color,
    c_slider,
    c_radio
  },
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    const defaultConfig = this.configObj[this.configNme];
    return {
      baseConfig: {
        shadowColor: {
          title: '阴影颜色',
          default: [
            {
              item: defaultConfig.color || '#888',
            },
          ],
          color: [
            {
              item: defaultConfig.color || '#888',
            },
          ],
        },
        shadowX: {
          title: '横轴',
          val: defaultConfig.x || 0,
          min: 0,
        },
        shadowY: {
          title: '纵轴',
          val: defaultConfig.y || 0,
          min: 0,
        },
        shadowBlur: {
          title: '宽度',
          val: defaultConfig.blur || 0,
          min: 0,
        },
        shadowSpread: {
          title: '扩散',
          val: defaultConfig.spread || 0,
          min: 0,
        },
        visible: {
          title: "开启阴影",
          tabVal: 0,
          tabList: [
            { name: '关闭' },
            { name: '开启' }
          ]
        },
      },

      defaults: {},
      configData: {},

      isVisibleConfig: false
    };
  },
  created() {
    this.defaults = this.configObj;
    this.configData = this.configObj[this.configNme];
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      immediate: true,
      deep: true,
    },
    baseConfig: {
      handler(nVal) {
        const {
          shadowColor,
          shadowX,
          shadowY,
          shadowBlur,
          shadowSpread,
          visible
        } = nVal;

        Object.assign(this.configData, {
          color: shadowColor.color[0].item,
          x: shadowX.val,
          y: shadowY.val,
          blur: shadowBlur.val,
          spread: shadowSpread.val,
          visible: visible.tabVal
        });
      },
      deep: true
    }
  },
};
</script>

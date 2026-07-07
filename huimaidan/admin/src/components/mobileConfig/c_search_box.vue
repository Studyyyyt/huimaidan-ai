<template>
  <div class="mobile-config">
    <Form ref="formInline">
      <div v-for="(item, key) of renderComp" :key="key">
        <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme"
          :key="key" :index="activeIndex" :num="item.num"></component>
      </div>
      <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </Form>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';

export default {
  name: 'c_search_box',
  componentsName: 'search_box',
  cname: '搜索框',
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
      hotIndex: 1,
      configObj: {}, // 配置对象

      baseComp: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],

      contentConfig: {
        fullComp: [],
        components: [
          { // 标题
            components: toolCom.c_title,
            configNme: 'titleLeft',
          },
          { // 滚动设置
            components: toolCom.c_radio,
            configNme: 'fixedConfig',
          },
          { // 选择风格
            components: toolCom.c_radio,
            configNme: 'styleConfig'
          },
          { // 样式类型
            components: toolCom.c_radio,
            configNme: 'displaySearchStyleConfig',
            visible: val => {
              return val.styleConfig.tabVal == 2;
            }
          },
          {
            children: [
              { // 标题
                components: toolCom.c_input_item,
                configNme: 'titleConfig',
              },
              { // 链接
                components: toolCom.c_input_item,
                configNme: 'linkConfig',
              }
            ],
            visible: val => {
              const permission1 = val.styleConfig.tabVal == 0;
              const permission2 = val.styleConfig.tabVal == 2 && val.displaySearchStyleConfig.tabVal == 0;
              return permission1 || permission2;
            }
          },
          { // logo图
            components: toolCom.c_upload_img,
            configNme: 'logoConfig',
            visible: val => {
              return val.styleConfig.tabVal == 2 && val.displaySearchStyleConfig.tabVal == 2;
            }
          },
          { // 搜索内容
            components: toolCom.c_title,
            configNme: 'titleSearch',
          },
          { // 提示文字
            components: toolCom.c_input_item,
            configNme: 'tipConfig',
          },
          { // 搜索热词
            components: toolCom.c_title,
            configNme: 'titleHotWords',
          },
          { // 搜索热词
            components: toolCom.c_hot_word,
            configNme: 'hotWords',
          },
          { // 显示时间
            components: toolCom.c_input_number,
            configNme: 'numConfig',
          },
        ]
      },

      styleConfig: {
        fullComp: [],
        components: [
          { // 标题
            components: toolCom.c_title,
            configNme: 'titleRight',
          },
          { // 搜索框
            components: toolCom.c_bg_color,
            configNme: 'searchBoxColor',
          },
          { // 提示文字
            components: toolCom.c_bg_color,
            configNme: 'tipColor',
          },
          { // 热词文字
            components: toolCom.c_bg_color,
            configNme: 'hotWordsColor',
          },
          { // 文字设置
            components: toolCom.c_title,
            configNme: 'textTitle',
          },
          {
            children: [
              { // 文字颜色
                components: toolCom.c_bg_color,
                configNme: 'textColor',
              },
              { // 文字大小
                components: toolCom.c_slider,
                configNme: 'fontSizeConfig',
              },
            ],
            visible: val => {
              return val.styleConfig.tabVal != 2 || (val.styleConfig.tabVal == 2 && val.displaySearchStyleConfig.tabVal != 2);
            }
          },
          { // 文字位置
            components: toolCom.c_radio,
            configNme: 'textPosition',
          },
          { // 文字样式
            components: toolCom.c_radio,
            configNme: 'textStyle',
            visible: val => {
              return val.styleConfig.tabVal != 2 || (val.styleConfig.tabVal == 2 && val.displaySearchStyleConfig.tabVal != 2);
            }
          },
          { // 标题
            components: toolCom.c_title,
            configNme: 'titleCurrency',
          },
          // 组件上浮
          {
						components: toolCom.c_slider,
						configNme: 'offsetYConfig'
					},
          { // 组件背景
            components: toolCom.c_bg_color,
            configNme: 'moduleColor',
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
          { // 背景圆角 & 圆角值
            components: toolCom.c_fillet,
            configNme: 'fillet',
          },
          { // 阴影颜色
            components: toolCom.c_shadow,
            configNme: 'shadowConfig',
          }
        ]
      },
    };
  },
  watch: {
    num(nVal) {
      // debugger;
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
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
  computed: {
    renderComp() {
      const isStyleMode = this.configObj.setUp && this.configObj.setUp.tabVal === 1;
      const comp = isStyleMode ? this.styleConfig.fullComp : this.contentConfig.fullComp;
      return this.baseComp.concat(comp);
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
            acc.push(...item.children);
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
    }
  },
};
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;

  span {
    margin-right: 14px;
    color: #999;
  }
}
</style>

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
  name: 'c_store_head',
  componentsName: 'store_head',
  cname: '头部组件',
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
          { // tab
            components: toolCom.c_tabs,
            configNme: 'tabConfig',
          },
          { // 搜索框配置
            children: [
              { // 搜索标题
                components: toolCom.c_title,
                configNme: 'titleSearch',
              },
              { // 提示文字
                components: toolCom.c_input_item,
                configNme: 'searchTipConfig',
              },
              { // 搜索热词标题
                components: toolCom.c_title,
                configNme: 'searchHotWordTitle',
              },
              { // 搜索热词
                components: toolCom.c_hot_word,
                configNme: 'searchHotWord',
              },
              { // 滚动时间
                components: toolCom.c_input_number,
                configNme: 'hotwordDisplayTime',
              }
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal === "0";
            }
          },
          { // 店铺配置
            children: [
              { // 店铺配置标题
                components: toolCom.c_title,
                configNme: 'storeInfoTitle'
              },
              { // 显示信息
                components: toolCom.c_checkbox,
                configNme: 'storeHeadVisible'
              }
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal === "1";
            }
          },
          { // 轮播图配置
            children: [
              { // 展示设置标题
                components: toolCom.c_title,
                configNme: 'swiperStyleTitle1',
              },
              { // 轮播图
                components: toolCom.c_radio,
                configNme: 'swiperStyle',
              },
              { // 内容设置标题
                components: toolCom.c_title,
                configNme: 'contentStyleTitle',
              },
              { // 轮播图
                components: toolCom.c_swipers_list,
                configNme: 'swiperConfig',
              }
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal === "2";
            }
          }
        ]
      },
      styleConfig: {
        fullComp: [],
        components: [
          { // 搜索框
            children: [
              { // 搜索框标题
                components: toolCom.c_title,
                configNme: 'searchBoxTitle',
              },
              { // 搜索框颜色
                components: toolCom.c_bg_color,
                configNme: 'searchBoxColor',
              },
              { // 提示文字颜色
                components: toolCom.c_bg_color,
                configNme: 'tipColor',
              },
              { // 热词文字颜色
                components: toolCom.c_bg_color,
                configNme: 'hotWordsColor',
              },
              { // 色调
                components: toolCom.c_radio,
                configNme: 'toneConfig',
              },
              { // 阴影
                components: toolCom.c_shadow,
                configNme: 'shadowConfig',
                visible: (configObj) => {
                  return configObj.toneConfig.tabVal === 1;
                }
              }
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal == 0;
            }
          },
          { // 店铺样式
            children: [
              { // 店铺样式标题
                components: toolCom.c_title,
                configNme: 'storeStyleTitle',
              },
              { // 店铺名称颜色
                components: toolCom.c_bg_color,
                configNme: 'storeNameColor',
              },
              { // 关注按钮颜色
                components: toolCom.c_bg_color,
                configNme: 'followBtnColor',
              },
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal == 1;
            }
          },
          { // 轮播图样式
            children: [
              { // 轮播图样式标题
                components: toolCom.c_title,
                configNme: 'swiperStyleTitle',
              },
              { // 指示器样式
                components: toolCom.c_radio,
                configNme: 'docConfig',
              },
              { // 指示器位置
                components: toolCom.c_radio,
                configNme: 'docPosition',
              },
              { // 色调
                components: toolCom.c_radio,
                configNme: 'docToneConfig',
              },
              {
                children: [
                  { // 选中样式
                    components: toolCom.c_bg_color,
                    configNme: 'docActiveColor'
                  },
                  { // 常规样式
                    components: toolCom.c_bg_color,
                    configNme: 'docNormalColor'
                  }
                ],
                visible: (configObj) => {
                  return configObj.docToneConfig.tabVal == 1;
                }
              },
              { // 图片设置标题
                components: toolCom.c_title,
                configNme: 'imgStyleTitle'
              },
              { // 圆角值
                components: toolCom.c_fillet,
                configNme: 'fillet'
              },
              { // 图片间距
                components: toolCom.c_slider,
                configNme: 'imgGap',
                visible: (configObj) => {
                  return configObj.swiperStyle.tabVal == 2;
                }
              },
              { // 阴影
                components: toolCom.c_shadow,
                configNme: 'imgShadowConfig'
              }
            ],
            visible: (configObj) => {
              return configObj.tabConfig.tabVal == 2;
            }
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
      if (!value.selectConfig.list || !value.selectConfig.list[0].value) {
        // this.getCategory();
      }
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

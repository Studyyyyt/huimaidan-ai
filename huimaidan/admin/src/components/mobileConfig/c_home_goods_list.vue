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
  name: 'c_home_goods_list',
  componentsName: 'home_goods_list',
  cname: '产品列表',
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
          { // 列表设置
            components: toolCom.c_title,
            configNme: 'titleLeft',
          },
          { // 选择风格
            components: toolCom.c_radio,
            configNme: 'styleConfig',
          },
          { // 筛选条件
            components: toolCom.c_radio,
            configNme: 'filterConfig',
          },
          { // 商品设置
            components: toolCom.c_title,
            configNme: 'titleGoods',
          },
          { // 选择方式
            components: toolCom.c_select,
            configNme: 'typeConfig',
          },
          { // 选择商品
            components: toolCom.c_goods,
            configNme: 'goodsList',
            visible: config => {
              return config.typeConfig.activeValue == 1;
            }
          },
          { // 选择分类
            components: toolCom.c_classify,
            configNme: 'classList',
            visible: config => {
              return config.typeConfig.activeValue == 2;
            }
          },
          { // 选择标签
            components: toolCom.c_select,
            configNme: 'goodsLabel',
            visible: config => {
              return config.typeConfig.activeValue == 3;
            }
          },
          { // 选择类型
            components: toolCom.c_select,
            configNme: 'goodsType',
            visible: config => {
              return config.typeConfig.activeValue == 4;
            }
          },
          { // 选择配送方式
            components: toolCom.c_select,
            configNme: 'deliveryType',
            visible: config => {
              return config.typeConfig.activeValue == 5;
            }
          },
          {
            children: [
              { // 选择数量
                components: toolCom.c_slider,
                configNme: 'numberConfig',
              },
              { // 选择排序
                components: toolCom.c_radio,
                configNme: 'goodsSort',
              },
              { // 排序方式
                components: toolCom.c_radio,
                configNme: 'goodsSortType',
              },
            ],
            visible: config => {
              return config.typeConfig.activeValue != 1;
            }
          },
          { // 显示内容
            components: toolCom.c_title,
            configNme: 'titleContents',
          },
          { // 显示信息
            components: toolCom.c_checkbox,
            configNme: 'checkboxInfo',
          },
          { // 购物车按钮 标题
            components: toolCom.c_title,
            configNme: 'titleCart',
          },
          { // 是否显示购物车按钮
            components: toolCom.c_radio,
            configNme: 'cartConfig',
          },
          {
            children: [
              { // 购物车按钮 样式
                components: toolCom.c_button_img,
                configNme: 'bntStyleConfig',
              },
              { // 购物车按钮 效果
                components: toolCom.c_radio,
                configNme: 'bntConfig',
              },
            ],
            visible: config => {
              return config.cartConfig.tabVal == 0;
            }
          }
        ],
      },
      styleConfig: {
        fullComp: [],
        components: [
          { // 商品样式标题
            components: toolCom.c_title,
            configNme: 'titleRight',
          },
          { // 图片圆角
            components: toolCom.c_fillet,
            configNme: 'filletImg',
          },
          { // 商品名称
            components: toolCom.c_radio,
            configNme: 'goodsName',
          },
          { // 色调
            components: toolCom.c_radio,
            configNme: 'toneConfig',
          },
          {
            children: [
              { // 商品名称颜色
                components: toolCom.c_bg_color,
                configNme: 'goodsNameColor',
              },
              { // 商品价格颜色
                components: toolCom.c_bg_color,
                configNme: 'goodsPriceColor',
              },
              { // 已售数量颜色
                components: toolCom.c_bg_color,
                configNme: 'soldNumColor',
                visible: config => {
                  return config.checkboxInfo.type.includes(3);
                }
              },
              { // 评分颜色
                components: toolCom.c_bg_color,
                configNme: 'scoreColor',
                visible: config => {
                  return config.checkboxInfo.type.includes(4);
                }
              },
            ],
            visible: config => {
              return config.toneConfig.tabVal == 1;
            }
          },
          { // 图片阴影
            components: toolCom.c_shadow,
            configNme: 'goodShadowConfig',
          },

          { // 购物车按钮 标题
            components: toolCom.c_title,
            configNme: 'titleCart',
          },
          { // 购物车色调选项
            components: toolCom.c_radio,
            configNme: 'toneCartConfig',
          },
          { // 购物车色调
            components: toolCom.c_bg_color,
            configNme: 'bntBgColor',
            visible: config => {
              return config.toneCartConfig.tabVal == 1;
            }
          },
          { // 通用样式 标题
            components: toolCom.c_title,
            configNme: 'titleCurrency',
          },
          { // 组件上浮
            components: toolCom.c_slider,
            configNme: 'ptConfig',
          },
          { // 组件背景
            components: toolCom.c_bg_color,
            configNme: 'moduleColor',
          },
          { // 底部背景
            components: toolCom.c_bg_color,
            configNme: 'bottomBgColor',
          },
          { // 内容间距
            components: toolCom.c_slider,
            configNme: 'gapConfig',
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

   
      lockStatus: false,
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
    "configObj.typeConfig.activeValue": {
      handler(nVal, oVal) {
        // 选择方式为指定商品时，清空商品列表
        if (nVal == 1 && oVal !== undefined && nVal != oVal) {
          this.configObj.goodsList.list = [];
        }
      }
    },
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
    getConfig(data, name) {}
  },
};
</script>

<style scoped lang="scss">
.pro {
  padding: 15px 15px 0;

  .tips {
    height: 50px;
    line-height: 50px;
    color: #999;
    font-size: 12px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }
}

.btn-box {
  padding-bottom: 20px;
}
</style>

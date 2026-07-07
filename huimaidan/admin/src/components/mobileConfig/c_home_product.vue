<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme"
        :key="key" @getConfig="getConfig" :index="activeIndex" :num="item.num"></component>
    </div>
    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import { mapState, mapMutations, mapActions } from 'vuex';
import rightBtn from '@/components/rightBtn/index.vue';
import { getProduct } from '@/api/diy';
export default {
  name: 'c_home_product',
  componentsName: 'home_product',
  cname: '商品分类',
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
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],
      oneContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'styleConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'slideConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleTab',
        },
        {
          components: toolCom.c_promotion,
          configNme: 'tabConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleCart',
        },
        {
          components: toolCom.c_radio,
          configNme: 'cartConfig',
        },
      ],
      twoContent: [
        {
          components: toolCom.c_button_img,
          configNme: 'bntStyleConfig',
        },
        {
          components: toolCom.c_radio,
          configNme: 'bntConfig',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'decorateColor',
        },
      ],
      twoStyle2: [
        {
          components: toolCom.c_bg_color,
          configNme: 'decorateColor2',
        },
      ],
      threeStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'textColor2',
        },
      ],
      threeStyle2: [
        {
          components: toolCom.c_bg_color,
          configNme: 'textColor',
        },
      ],
      threeStyle3: [
        {
          components: toolCom.c_bg_color,
          configNme: 'textColor3',
        },
      ],
      fourStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCart',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneCartConfig',
        },
      ],
      fourStyle2: [
        {
          components: toolCom.c_bg_color,
          configNme: 'bntBgColor',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_slider,
          configNme: 'ptConfig',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'moduleColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bottomBgColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'prConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbConfig',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'fillet',
        },
        {
          components: toolCom.c_shadow,
          configNme: 'shadowConfig',
        }
      ],
      setUp: 0,
      type: 0,
      type2: 0,
      type3: 0,
      type4: 0,
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
      },
      deep: true,
    },
    'configObj.setUp.tabVal': {
      handler(nVal, oVal) {
        this.setUp = nVal;
        var arr = [this.rCom[0]];
        if (nVal == 0) {
          this.getRComContent(arr, this.type3);
        } else {
          this.getRComStyle(arr, this.type, this.type2, this.type3, this.type4);
        }
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, nVal, this.type2, this.type3, this.type4);
        }
      },
    },
    'configObj.cartConfig.tabVal': {
      handler(nVal, oVal) {
        this.type3 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          this.getRComContent(arr, nVal);
        } else {
          this.getRComStyle(arr, this.type, this.type2, nVal, this.type4);
        }
      },
    },
    'configObj.goodsToneConfig.tabVal': {
      handler() {
        this.type2 = this.configObj.toneConfig.tabVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, this.type2, this.type3, this.type4);
        }
      },
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, nVal, this.type3, this.type4);
        }
      },
    },
    'configObj.toneCartConfig.tabVal': {
      handler(nVal, oVal) {
        this.type4 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          this.getRComStyle(arr, this.type, this.type2, this.type3, nVal);
        }
      },
    },
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
    getGoodStyleConfig() {
      const components = [
        { // 商品样式标题
          components: toolCom.c_title,
          configNme: 'titleGood',
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
          configNme: 'goodsToneConfig',
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
              configNme: 'soldNumColor'
            },
            { // 评分颜色
              components: toolCom.c_bg_color,
              configNme: 'scoreColor'
            },
          ],
          visible: config => {
            return config.goodsToneConfig.tabVal == 1;
          }
        },
        { // 图片阴影
          components: toolCom.c_shadow,
          configNme: 'goodShadowConfig',
        },
      ];

      return this.filterComp(components);
    },
    getRComContent(arr, type3) {
      if (type3 == 0) {
        this.rCom = [...arr, ...this.oneContent, ...this.twoContent];
      } else {
        this.rCom = [...arr, ...this.oneContent];
      }
    },
    getRComStyle(arr, type, type2, type3, type4) {
      let obj = [...arr, ...this.oneStyle, ...this.getGoodStyleConfig(), ...this.currencyStyle, ];
      let obj2 = [...arr, ...this.oneStyle,...this.getGoodStyleConfig(), ...this.fourStyle,  ...this.currencyStyle, ];
      if (type == 0) {
        if (type2 == 0) {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = obj2;
            } else {
              this.rCom = [...arr, ...this.oneStyle,...this.getGoodStyleConfig(), ...this.fourStyle, ...this.fourStyle2, ...this.currencyStyle];
            }
          } else {
            this.rCom = obj;
          }
        } else {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.currencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.fourStyle2,
                ...this.currencyStyle,
              ];
            }
          } else {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.getGoodStyleConfig(),
              ...this.twoStyle,
              ...this.threeStyle,
              ...this.fourStyle,
              ...this.currencyStyle,
            ];
          }
        }
      } else if (type == 1) {
        if (type2 == 0) {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = obj2;
            } else {
              this.rCom = [...arr, ...this.oneStyle, ...this.getGoodStyleConfig(), ...this.fourStyle, ...this.fourStyle2, ...this.currencyStyle];
            }
          } else {
            this.rCom = obj;
          }
        } else {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle2,
                ...this.fourStyle,
                ...this.currencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle2,
                ...this.fourStyle,
                ...this.fourStyle2,
                ...this.currencyStyle,
              ];
            }
          } else {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.getGoodStyleConfig(),
              ...this.twoStyle,
              ...this.threeStyle2,
              ...this.fourStyle,
              ...this.currencyStyle,
            ];
          }
        }
      } else if (type == 2) {
        if (type2 == 0) {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = obj2;
            } else {
              this.rCom = [...arr, ...this.oneStyle, ...this.getGoodStyleConfig(), ...this.fourStyle, ...this.fourStyle2,  ...this.currencyStyle];
            }
          } else {
            this.rCom = obj;
          }
        } else {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle2,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.currencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle2,
                ...this.threeStyle,
                ...this.fourStyle,
                ...this.fourStyle2,
                ...this.currencyStyle,
              ];
            }
          } else {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.getGoodStyleConfig(),
              ...this.twoStyle2,
              ...this.threeStyle,
              ...this.fourStyle,
              ...this.currencyStyle,
            ];
          }
        }
      } else {
        if (type2 == 0) {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = obj2;
            } else {
              this.rCom = [...arr, ...this.oneStyle,  ...this.getGoodStyleConfig(), ...this.fourStyle, ...this.fourStyle2, ...this.currencyStyle];
            }
          } else {
            this.rCom = obj;
          }
        } else {
          if (type3 == 0) {
            if (type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle3,
                ...this.fourStyle,
                ...this.currencyStyle,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.oneStyle,
                ...this.getGoodStyleConfig(),
                ...this.twoStyle,
                ...this.threeStyle3,
                ...this.fourStyle,
                ...this.fourStyle2,
                ...this.currencyStyle,
              ];
            }
          } else {
            this.rCom = [
              ...arr,
              ...this.oneStyle,
              ...this.getGoodStyleConfig(),
              ...this.twoStyle,
              ...this.threeStyle3,
              ...this.fourStyle,
              ...this.currencyStyle,
            ];
          }
        }
      }
    },
    async getConfig(data) {
      if (!data || !data.name) {
        return;
      }

      let configObj = this.configObj.tabConfig.list[this.configObj.tabConfig.tabCur];

      const {
        tabVal, // 选择方式
        numConfig, // 选择数量
        goodsSort, // 商品排序
        selectConfig, // 商品分类
        goodsLabel, // 商品标签
        goodsType, // 商品类型
        deliveryType, // 配送方式
      } = configObj;

      // 指定商品不请求
      if (tabVal == 1) return;

      const query = {
        limit: numConfig.val,
      };
      
      if (tabVal == 2) {
        // 按分类筛选
        query.cate_id = selectConfig.activeValue.join(',');
      } else if (tabVal == 3) {
        // 按商品标签筛选
        query.store_label_id = goodsLabel.activeValue.join(',');
      } else if (tabVal == 4 && goodsType) {
        // 按商品类型筛选
        query.store_type_id = goodsType.activeValue.join(',');
      } else if (tabVal == 5 && deliveryType) {
        // 按配送方式筛选
        query.delivery_type = deliveryType.activeValue.join(',');
      }
      
      if (goodsSort) {
        const sortKeyMap = {
					1: "sales",
					2: "price",
				};
				const sortKey = sortKeyMap[goodsSort];
				query.order = sortKey + "_desc";
      }

      try {
        const res = await getProduct(query);
        configObj.productList.list = res.data.list;
      } catch (err) {
        this.$message.error(err.message);
      }



    },
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

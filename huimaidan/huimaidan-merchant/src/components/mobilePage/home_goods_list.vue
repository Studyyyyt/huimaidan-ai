<template>
  <div>
    <div class="mobile-page paddingBox" :style="commonStyleVar">
      <div class="filter-box" :style="filterBoxStyle" v-if="renderData && renderData.filterConfig.tabVal == 0">
        <div class="filter-item strong">综合</div>
        <div class="filter-item">
          销量
          <i class="iconfont-h5 icon-icon_sort-2"></i>
        </div>
        <div class="filter-item">
          价格
          <i class="iconfont-h5 icon-icon_sort-2"></i>
        </div>
      </div>
      <div class="home_product" :style="goodListStyle">
        <!-- 单列 -->
        <template v-if="styleConfig == 0">
          <div class="list-wrapper itemA" :style="componentStyle">
            <div class="item" v-for="(item, index) in list" :index="index" :style="{
              ...goodStyle,
              borderRadius: bgRadius,
            }">
              <div class="img-box">
                <img class="img" v-if="item.image" :src="item.image" alt="" :style="{
                  borderRadius: imgRadius,
                }" />
                <div v-else class="empty-box" :style="{
                  borderRadius: imgRadius,
                }">
                  <img src="../../assets/images/shan.png" />
                </div>
              </div>
              <div class="info">
                <div class="hd">
                  <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1" :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }">
                    <span class="good-tag">拼团</span>
                    {{ item.store_name || '华为荣耀畅享平板换屏服务 屏幕换外屏主板维修' }}
                  </div>
                </div>
                <div class="price acea-row row-middle"
                  :class="checkboxInfo.indexOf(3) == -1 && checkboxInfo.indexOf(4) == -1 ? 'on' : ''">
                  <div class="num" v-if="checkboxInfo.indexOf(2) != -1" :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }">
                    <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 33
                    }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                  </div>
                  <img class="img" v-if="checkboxInfo.indexOf(5) != -1" src="../../assets/images/goods02.png" />
                </div>
                <div class="tag-list" v-if="checkboxInfo.indexOf(1) != -1">
                  <div class="tag-item">自营</div>
                  <div class="tag-item">领券</div>
                  <div class="tag-item">包邮</div>
                  <div class="tag-item">到店</div>
                </div>
                <div class="bottom">
                  <span v-if="checkboxInfo.indexOf(4) != -1" :style="{
                    color: toneConfig ? scoreColor : '#999999',
                  }">评分 {{ item.star || 0 }}</span>
                  <span class="mr8" v-if="checkboxInfo.indexOf(3) != -1" :style="{
                    color: toneConfig ? soldNumColor : '#999999',
                  }">已售{{ item.sales || 0 }}件</span>
                  <span v-if="checkboxInfo.indexOf(6) != -1">{{ item.reply_count || 0 }}条评论</span>
                </div>
                <!-- <div class="store-info">
                  小米官方旗舰店
                  <span>进店</span>
                  <i class="iconfont iconyou"></i>
                </div> -->
              </div>
              <div v-if="!cartConfig">
                <div class="bnt" v-if="bntStyleConfig == 0" :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }">
                  购买
                </div>
                <div class="jia" v-else :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }">
                  <div class="jiaCon">
                    <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 1"></span>
                    <span class="iconfont icongouwuche1" v-else></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 两列展示(纵向) -->
        <template v-else-if="styleConfig == 1">
          <div class="list-wrapper itemC" :style="componentStyle">
            <div class="item" v-for="(item, index) in list" :index="index" :style="{...goodStyle, borderRadius: bgRadius2}">
              <div class="img-box">
                <img class="img" v-if="item.image" :src="item.image" alt="" :style="{
                  borderRadius: imgRadius2,
                }" />
                <div v-else class="empty-box" :style="{
                  borderRadius: imgRadius2,
                }">
                  <img src="../../assets/images/shan.png" />
                </div>
              </div>
              <div class="info" :class="checkboxInfo.length == 1 && checkboxInfo.indexOf(0) != -1 && !cartConfig
                ? 'on'
                : ((checkboxInfo.length == 1 && checkboxInfo.indexOf(4) != -1) || !checkboxInfo.length) &&
                  !cartConfig
                  ? 'on2'
                  : ''
                ">
                <div class="hd">
                  <div class="title line1" v-if="checkboxInfo.indexOf(0) != -1" :style="{
                    fontWeight: goodsName,
                    color: toneConfig ? goodsNameColor : '#333',
                  }">
                    {{ item.store_name || '这里是商品名称展示区域,商品名称展示区域,商品名称展示区域' }}
                  </div>
                </div>
                <div class="price acea-row row-middle">
                  <div class="num" v-if="checkboxInfo.indexOf(2) != -1" :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }">
                    <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                    }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                  </div>
                  <img class="img" v-if="checkboxInfo.indexOf(5) != -1" src="../../assets/images/goods02.png" />
                </div>
                <div class="tag-list" v-if="checkboxInfo.indexOf(1) != -1">
                  <div class="tag-item">自营</div>
                  <div class="tag-item">领券</div>
                  <div class="tag-item">包邮</div>
                  <div class="tag-item">到店</div>
                </div>
                <div class="bottom">
                  <span v-if="checkboxInfo.indexOf(4) != -1" :style="{
                    color: toneConfig ? scoreColor : '#999999',
                  }">评分 {{ item.star || 0 }}</span>
                  <span v-if="checkboxInfo.indexOf(3) != -1" :style="{
                    color: toneConfig ? soldNumColor : '#999999',
                  }">已售{{ item.sales || 0 }}件</span>
                </div>
                <!-- <div class="store-info">
                  小米官方旗舰店
                  <span>进店</span>
                  <i class="iconfont iconyou"></i>
                </div> -->
              </div>
              <div class="jia" v-if="!cartConfig" :style="{
                background: toneCartConfig ? bntBgColor : themeColor,
              }">
                <div class="jiaCon">
                  <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 0"></span>
                  <span class="iconfont icongouwuche1" v-else></span>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 大图展示 -->
        <template v-else-if="styleConfig == 4">
          <div class="listBig" :style="componentStyle">
            <div class="itemBig" v-for="(item, index) in list" :key="index" :style="{
              borderRadius: bgRadius,
              ...goodStyle,
            }">
              <div class="img-box" style="height: auto;">
                <img class="img" v-if="item.image" :src="item.image" alt="" :style="{
                  borderRadius: imgRadius,
                }" />
                <div v-else class="empty-box" :style="{
                  borderRadius: imgRadius,
                  height: '180px',
                }">
                  <img src="../../assets/images/shan.png" />
                </div>
              </div>
              <div class="conter" :class="((checkboxInfo.length == 1 &&
                (checkboxInfo.indexOf(1) != -1 ||
                  checkboxInfo.indexOf(3) != -1 ||
                  checkboxInfo.indexOf(4) != -1 ||
                  checkboxInfo.indexOf(5) != -1)) ||
                !checkboxInfo.length) &&
                !cartConfig
                ? 'on'
                : ''
                ">
                <div class="name" v-if="checkboxInfo.indexOf(0) != -1" :style="{
                  fontWeight: goodsName,
                  color: toneConfig ? goodsNameColor : '#333',
                }">
                  <span class="good-tag">拼团</span>
                  {{ item.store_name || '商品名称商品商名称商品商…' }}
                </div>
                <div class="price acea-row row-middle">
                  <div class="num" v-if="checkboxInfo.indexOf(2) != -1" :style="{
                    color: toneConfig ? goodsPriceColor : colorStyle.theme,
                  }">
                    <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                    }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                  </div>
                  <img v-if="checkboxInfo.indexOf(5) != -1" src="../../assets/images/goods02.png" />
                </div>
                <div class="tag-list" v-if="checkboxInfo.indexOf(1) != -1">
                  <div class="tag-item">自营</div>
                  <div class="tag-item">领券</div>
                  <div class="tag-item">包邮</div>
                  <div class="tag-item">到店</div>
                </div>
                <div class="bottom">
                  <span v-if="checkboxInfo.indexOf(3) != -1" :style="{
                    color: toneConfig ? soldNumColor : '#999999',
                  }">已售{{ item.sales || 0 }}件</span>
                  <span v-if="checkboxInfo.indexOf(4) != -1" :style="{
                    color: toneConfig ? scoreColor : '#999999',
                  }">评分 {{ item.star || 0 }}</span>
                </div>
                <!-- <div class="store-info">
                  小米官方旗舰店
                  <span>进店</span>
                  <i class="iconfont iconyou"></i>
                </div> -->
              </div>
              <div v-if="!cartConfig">
                <div class="bnt" v-if="bntStyleConfig == 0" :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }">
                  购买
                </div>
                <div class="jia" v-else :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }">
                  <div class="jiaCon">
                    <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 1"></span>
                    <span class="iconfont icongouwuche1" v-else></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 两列展示横向 -->
        <template v-else-if="styleConfig == 3">
          <div class="listCross acea-row row-between-wrapper" :style="{
            borderRadius: bgRadius,
            ...componentStyle,
          }">
            <div class="item acea-row row-middle" v-for="(item, index) in list" :index="index" :style="goodStyle">
              <div class="pictrue acea-row row-center-wrapper" :style="{
                borderRadius: imgRadius,
              }">
                <img class="img" v-if="item.image" :src="item.image" alt="" :style="{
                  borderRadius: imgRadius,
                }" />
                <img v-else src="../../assets/images/shan.png" />
              </div>
              <div class="text">
                <div class="name" v-if="checkboxInfo.indexOf(0) != -1" :style="{
                  fontWeight: goodsName,
                  color: toneConfig ? goodsNameColor : '#333',
                }">
                  <div class="line2">{{ item.store_name || '这里是标题这里是标题这...' }}</div>
                </div>
                <div class="price" v-if="checkboxInfo.indexOf(2) != -1" :style="{
                  color: toneConfig ? goodsPriceColor : colorStyle.theme,
                }">
                  <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                  }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                </div>
              </div>
            </div>
          </div>
        </template>
        <!-- 三列展示 & 左右滑动展示 -->
        <template v-else>
          <div class="list-wrapper itemB" :class="styleConfig == 5 ? 'itemD' : ''" :style="{
            borderRadius: bgRadius,
            ...componentStyle,
          }">
            <div class="list">
              <div class="item" v-for="(item, index) in list" :index="index" :style="goodStyle">
                <div class="img-box">
                  <img class="img" v-if="item.image" :src="item.image" alt="" :style="{
                    borderRadius: imgRadius,
                  }" />
                  <div v-else class="empty-box" :style="{
                    borderRadius: imgRadius,
                  }">
                    <img src="../../assets/images/shan.png" />
                  </div>
                </div>
                <div class="info" :class="checkboxInfo.indexOf(2) == -1 && checkboxInfo.indexOf(0) != -1 && !cartConfig
                  ? 'on'
                  : checkboxInfo.indexOf(2) == -1 && checkboxInfo.indexOf(0) == -1 && !cartConfig
                    ? 'on2'
                    : ''
                  ">
                  <div class="hd" v-if="checkboxInfo.indexOf(0) != -1">
                    <div class="title line2" :style="{
                      fontWeight: goodsName,
                      color: toneConfig ? goodsNameColor : '#333',
                    }">
                      {{ item.store_name || '商品名称商品商名称商品商…' }}
                    </div>
                  </div>
                  <div class="price" v-if="checkboxInfo.indexOf(2) != -1">
                    <div class="num" :style="{
                      color: toneConfig ? goodsPriceColor : colorStyle.theme,
                    }">
                      <span>￥</span>{{ item.price ? $HandlePrice(item.price, 0) : 77
                      }}<span>{{ item.price ? $HandlePrice(item.price, 1) : '' }}</span>
                    </div>
                  </div>
                </div>
                <div class="jia" v-if="!cartConfig" :style="{
                  background: toneCartConfig ? bntBgColor : themeColor,
                }">
                  <div class="jiaCon">
                    <span class="iconfont iconjiahao1" v-if="bntStyleConfig == 0"></span>
                    <span class="iconfont icongouwuche1" v-else></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
// import theme from "@/mixins/theme";
import { getCategory, getProduct } from '@/api/diy';
import { diyUtil } from '@/utils/diy-util';

export default {
  name: 'home_goods_list',
  cname: '商品列表',
  configName: 'c_home_goods_list',
  icon: '#iconzujian-shangpinliebiao',
  type: 0, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'goodList', // 外面匹配名称
  sortOrder: 30,
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
  computed: {
    goodListStyle() {
      if (this.renderData && this.renderData.filterConfig.tabVal === 1) {
        return {
          paddingTop: `var(--content-gap)`
        }
      }
      return {}
    },
    filterBoxStyle() {
      return {
        paddingTop: this.renderData.topConfig.val + "px",
        backgroundColor: this.bottomBgColor
      }
    },
    themeColor() {
      return `linear-gradient(90deg,${this.colorStyle.theme} 0%,${this.colorStyle.assist} 100%)`
    },
    commonStyleVar() {
      if (!this.renderData) return {}
      const { gapConfig } = this.renderData
      return {
        background: this.bottomBgColor,
        marginTop: diyUtil.buildMarginTopOffset({ val: this.mTop }, this.renderData.ptConfig),
        paddingBottom: this.bottomConfig + 'px',
        paddingLeft: this.prConfig + 'px',
        paddingRight: this.prConfig + 'px',
        "--content-gap": gapConfig.val + "px"
      };
    },
    componentStyle() {
      if (!this.renderData) return {};
      return {
        boxShadow: diyUtil.buildShadowStyle(this.renderData.shadowConfig),
      };
    },
    goodStyle() {
      if (!this.renderData) return {};
      return {
        background: this.bgColor,
        boxShadow: diyUtil.buildShadowStyle(this.renderData.goodShadowConfig),
      };
    },
    ...mapState('mobildConfig', ['defaultArray']),
    productQuery() {
      if (!this.renderData) return null;
      const {
        typeConfig, // 选择方式
        goodsSort, // 商品排序
        goodsSortType, // 排序方式
        numberConfig, // 选择数量

        classList, // 分类
        goodsLabel, // 标签
        goodsType, // 商品类型
        deliveryType, // 配送方式
      } = this.renderData;

      const query = {
        limit: numberConfig.val
      };
      const selectType = typeConfig.activeValue;

      if (selectType == 2) {
        // 指定平台商品分类
        query.mer_cate_id = classList.classVal.join(',');
      } else if (selectType == 3) {
        // 指定平台商品标签
        query.mer_store_label_id = goodsLabel.activeValue.join(',');
      } else if (selectType == 4) {
        // 指定平台商品类型
        query.store_type_id = goodsType.activeValue.join(',');
      } else if (selectType == 5) {
        // 指定配送方式
        query.delivery_type = deliveryType.activeValue.join(',');
      }

      if (goodsSort.tabVal) {
        const sortKeyMap = {
					1: "distance",
					2: "sales",
					3: "price",
				};
				const sortKey = sortKeyMap[goodsSort.tabVal];
				const sortOrder = goodsSortType.tabVal == 0 ? "asc" : "desc";
				query.order = sortKey + "_" + sortOrder;
      }

      return query
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
    productQuery(query) {
      // 选择方式为指定商品时，不请求商品列表
      if (this.renderData.typeConfig.activeValue == 1 || !query) return;
      this.getProductList(query);
    }
  },
  // mixins: [theme],
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '商品列表',
        name: 'goodList',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '列表设置',
        titleGoods: '商品设置',
        titleContents: '显示内容',
        titleCart: '购物车按钮',
        titleRight: '商品样式',
        titleCurrency: '卡片样式',
        styleConfig: {
          title: '选择风格',
          tabVal: 0,
          tabList: [
            {
              name: '单列展示',
            },
            {
              name: '两列展示(纵向)',
            },
            {
              name: '三列展示',
            },
            {
              name: '两列展示(横向)',
            },
            {
              name: '大图展示',
            },
            {
              name: '左右滑动展示',
            },
          ],
        },
        filterConfig: {
          title: '筛选条件',
          tabVal: 0,
          tabList: [
            {
              name: '显示',
            },
            {
              name: "隐藏"
            }
          ]
        },
        typeConfig: {
          title: '选择方式',
          activeValue: 1,
          list: [
            {
              activeValue: 1,
              title: '指定商品',
            },
            {
              activeValue: 2,
              title: '指定商家商品分类',
            },
            {
              activeValue: 3,
              title: '指定商品标签',
            },
            {
              activeValue: 4,
              title: '指定商品类型',
            },
            {
              activeValue: 5,
              title: '指定配送方式',
            },
          ],
        },
        goodsType: {
          title: "商品类型",
          activeValue: [],
          multiple: true,
          list: [
            {
              activeValue: 1,
              title: "普通商品"
            },
            {
              activeValue: 2,
              title: "虚拟商品"
            },
            {
              activeValue: 3,
              title: "云盘商品"
            },
            {
              activeValue: 4,
              title: "卡密商品"
            },
            {
              activeValue: 5,
              title: "预约商品"
            }
          ]
        },
        deliveryType: {
          title: "配送方式",
          activeValue: [],
          multiple: true,
          list: [
            {
              activeValue: 1,
              title: "快递配送"
            },
            {
              activeValue: 2,
              title: "到店自提"
            },
            {
              activeValue: 3,
              title: "同城服务"
            },
            {
              activeValue: 4,
              title: "上门服务"
            },
            {
              activeValue: 5,
              title: "到店服务"
            }
          ]
        },
        goodsList: {
          max: 20,
          list: [],
        },
        goodsSort: {
          title: '默认排序',
          tabVal: 0,
          tabList: [
            {
              name: '综合',
            },
            {
              name: "距离"
            },
            {
              name: '销量',
            },
            {
              name: '价格',
            },
          ],
        },
        goodsSortType: {
          title: '排序方式',
          tabVal: 0,
          tabList: [
            {
              name: '升序',
            },
            {
              name: '降序',
            }
          ],
        },
        numberConfig: {
          title: '商品数量',
          val: 3,
          min: 1,
        },
        classList: {
          title: '商品分类',
          classVal: [],
          checkStrictly: false
        },
        checkboxInfo: {
          title: '展示信息',
          name: 'checkboxInfo',
          type: [0, 1, 2, 3, 4, 5, 6],
          list: [
            {
              id: 0,
              name: '商品名称',
            },
            {
              id: 1,
              name: '商品标签',
            },
            {
              id: 2,
              name: '商品价格',
            },
            {
              id: 3,
              name: '商品销量',
            },
            {
              id: 4,
              name: '商品评分',
            },
            {
              id: 5,
              name: '会员价格',
            },
            {
              id: 6,
              name: "商品评论"
            }
          ],
        },
        cartConfig: {
          title: '是否显示',
          tabVal: 0,
          tabList: [
            {
              name: '显示',
            },
            {
              name: '隐藏',
            },
          ],
        },
        bntConfig: {
          title: '按钮效果',
          tabVal: 1,
          tabList: [
            {
              name: '进入商品详情页',
            },
            {
              name: '商品加购',
            },
          ],
        },
        bntStyleConfig: {
          title: '按钮样式',
          tabVal: 0,
        },
        filletImg: {
          title: '图片圆角',
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
        goodsName: {
          title: '商品名称',
          tabVal: 1,
          tabList: [
            {
              name: '加粗',
              style: 'bold',
            },
            {
              name: '常规',
              style: 'normal',
            },
          ],
        },
        toneConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        goodsNameColor: {
          title: '商品名称',
          name: 'goodsNameColor',
          default: [
            {
              item: '#333333',
            },
          ],
          color: [
            {
              item: '#333333',
            },
          ],
        },
        goodsPriceColor: {
          title: '商品价格',
          name: 'goodsPriceColor',
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
        soldNumColor: {
          title: '已售数量',
          name: 'soldNumColor',
          default: [
            {
              item: '#999999',
            },
          ],
          color: [
            {
              item: '#999999',
            },
          ],
        },
        scoreColor: {
          title: '评分颜色',
          name: 'scoreColor',
          default: [
            {
              item: '#999999',
            },
          ],
          color: [
            {
              item: '#999999',
            },
          ],
        },
        goodShadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },

        toneCartConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            {
              name: '跟随主题风格',
            },
            {
              name: '自定义',
            },
          ],
        },
        bntBgColor: {
          title: '按钮颜色',
          name: 'bntBgColor',
          default: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
          color: [
            {
              item: '#E93323',
            },
            {
              item: '#FF7931',
            },
          ],
        },
        ptConfig: {
          title: '组件上浮',
          val: 0,
          min: 0,
        },
        moduleColor: {
          title: '组件背景',
          default: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
          color: [
            {
              item: '#fff',
            },
            {
              item: '#fff',
            },
          ],
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
        gapConfig: {
          title: '内容边距',
          val: 10,
          min: 0,
        },
        topConfig: {
          title: '上边距',
          val: 10,
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
        goodsLabel: {
          title: '商品标签',
          activeValue: [],
          multiple: true,
          filterable: true,
          dataSource: 'goodsLabel',
          list: []
        },
        productList: {
          title: '商品列表',
          list: [],
        },
      },
      list: [],
      pageData: {},
      styleConfig: 0,
      checkboxInfo: [],
      cartConfig: 0,
      bntStyleConfig: 0,
      imgRadius: 0,
      imgRadius2: 0,
      goodsName: '',
      toneConfig: 0,
      goodsNameColor: '',
      goodsPriceColor: '',
      soldNumColor: '',
      scoreColor: '',
      toneCartConfig: 0,
      bntBgColor: '',
      bntBgColorLeft: '',
      bgColor: '',
      bottomBgColor: '',
      mTop: 0,
      topConfig: 0,
      bottomConfig: 0,
      prConfig: 0,
      bgRadius: 0,
      bgRadius2: 0,
      ptConfig: 0,

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
        this.renderData = data;
        this.styleConfig = data.styleConfig.tabVal;
        this.checkboxInfo = data.checkboxInfo.type;
        this.cartConfig = data.cartConfig.tabVal;
        this.bntStyleConfig = data.bntStyleConfig.tabVal;
        let filletImg = data.filletImg.type;
        let filletValImg = data.filletImg.val;
        let valListImg = data.filletImg.valList;
        this.imgRadius = diyUtil.buildBorderRadius(data.filletImg);
        this.imgRadius2 = diyUtil.buildBorderRadius(data.filletImg);
        let goodsTabVal = data.goodsName.tabVal;
        this.goodsName = data.goodsName.tabList[goodsTabVal].style;
        this.toneConfig = data.toneConfig.tabVal;
        this.goodsNameColor = data.goodsNameColor.color[0].item;
        this.goodsPriceColor = data.goodsPriceColor.color[0].item;
        this.soldNumColor = data.soldNumColor.color[0].item;
        this.scoreColor = data.scoreColor.color[0].item;
        this.toneCartConfig = data.toneCartConfig.tabVal;
        let bntBgColorLeft = data.bntBgColor.color[0].item;
        let bntBgColorRight = data.bntBgColor.color[1].item;
        this.bntBgColorLeft = bntBgColorLeft;
        this.bntBgColor = `linear-gradient(90deg,${bntBgColorLeft} 0%,${bntBgColorRight} 100%)`;
        let bgColorLeft = data.moduleColor.color[0].item;
        let bgColorRight = data.moduleColor.color[1].item;
        this.bgColor = `linear-gradient(90deg,${bgColorLeft} 0%,${bgColorRight} 100%)`;
        this.bottomBgColor = data.bottomBgColor.color[0].item;
        this.mTop = data.mbConfig.val;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.prConfig = data.prConfig.val;
        this.ptConfig = data.ptConfig.val;
        let fillet = data.fillet.type;
        let filletVal = data.fillet.val;
        let valList = data.fillet.valList;
        this.bgRadius = fillet
          ? valList[0].val + 'px ' + valList[1].val + 'px ' + valList[3].val + 'px ' + valList[2].val + 'px'
          : filletVal + 'px';
        this.bgRadius2 = diyUtil.buildBorderRadius(data.fillet);
        if (data.typeConfig.activeValue == 1) {
          this.list = data.goodsList.list && data.goodsList.list.length ? data.goodsList.list : 4;
        } else {
          if (data.goodsList.list) {
            const list = data.goodsList.list.slice(0, data.numberConfig.val);
            this.list = list.length ? list : 4;
          } else {
            this.list = 4;
          }
        }
      }
    },
    async getProductList(query) {
      try {
        const res = await getProduct(query)
        this.renderData.goodsList.list = res.data.list
      } catch (err) {
        this.$message.error(err.message)
      }
    }
  }
}
</script>
<style scoped lang="scss">
.itemOn {
  border-radius: 0 !important;

  img,
  .empty-box {
    border-radius: 0 !important;
  }

  .img-box {
    .label {
      border-radius: 0 0 8px 0 !important;
    }
  }
}

.pageOn {
  border-radius: 8px !important;
}

.listCross {
  width: 100%;
  background: #fff;
  padding: 16px 12px 6px 12px;
  gap: var(--content-gap);

  .item {
    width: calc((100% - var(--content-gap)) / 2);

    .pictrue {
      width: 72px;
      height: 72px;
      background: #f3f9ff;
      border-radius: 4px;
      margin-right: 10px;

      img {
        width: 26px;
        height: 20px;
        display: block;
      }

      .img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
      }
    }

    .text {
      flex: 1;
      overflow: hidden;

      .name {
        font-size: 13px;
        color: #282828;
        height: 45px;
      }

      .price {
        font-size: 18px;
        font-weight: 600;
        font-family: semiBold;

        span {
          font-size: 12px;
        }
      }
    }
  }
}

.listBig {
  width: 100%;

  .itemBig {
    width: 100%;
    margin-bottom: var(--content-gap);
    background-color: #fff;
    border-radius: 10px;
    position: relative;

    .bnt {
      width: 48px;
      height: 28px;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 25px;
      text-align: center;
      line-height: 28px;
      font-size: 12px;
      color: #fff;
      position: absolute;
      right: 10px;
      bottom: 12px;
    }

    .jia {
      width: 22px;
      height: 22px;
      background-color: #e93323;
      border-radius: 50%;
      position: absolute;
      right: 10px;
      bottom: 10px;

      .jiaCon {
        width: 100%;
        height: 100%;
        text-align: center;
        line-height: 22px;

        .iconfont {
          color: #fff;
          font-size: 13px;
        }
      }
    }

    .conter {
      padding: 0 12px 12px 12px;

      &.on {
        height: 45px;
      }

      .name {
        margin-top: 10px;
        font-weight: 400;
        color: #333333;
        font-size: 14px;
        padding: 0;
      }

      img {
        // width: 99px;
        height: 14px;
        display: block;
        margin-top: 5px;
      }

      .price {
        margin-top: 8px;
        // margin-bottom: 8px;

        img {
          width: 70px;
          height: 15px;
        }

        .num {
          font-size: 20px;
          font-family: semiBold;

          span {
            font-size: 12px;
          }
        }
      }

      .bottom {
        font-weight: 400;
        color: #999999;
        font-size: 11px;
        margin: 4px 0;
      }
    }

    .img-box {
      width: 100%;
      height: 180px;
      position: relative;

      img {
        width: 65px;
        height: 50px;
      }

      .img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .empty-box {
        border-radius: 8px 8px 0 0;
        background: #f3f9ff;
      }

      .label {
        position: absolute;
        top: 0;
        left: 0;
        width: 59px;
        height: 25px;
        line-height: 25px;
        text-align: center;
        color: #fff;
        font-size: 12px;
        border-radius: 8px 0 8px 0;
      }
    }

    .name {
      font-size: 15px;
      font-weight: bold;
      margin-top: 8px;
      padding: 0 10px;
    }

    .coupon {
      width: 16px;
      height: 18px;
      line-height: 18px;
      text-align: center;
      font-size: 12px;
      margin-right: 5px;
      display: inline-block;
    }

    .price {
      font-weight: bold;
      font-size: 12px;

      .num {
        font-size: 18px;
        margin-right: 5px;
      }
    }
  }
}

.paddingBox {
  padding-bottom: 0;
  padding-top: 2px;
}

.home_product {
  // overflow: hidden;

  .hd_nav {
    display: flex;
    height: 65px;
    padding: 0 5px;

    .item {
      display: flex;
      flex-direction: column;
      justify-content: center;
      width: 25%;

      .title {
        font-size: 16px;
        color: #282828;
      }

      .label {
        width: 62px;
        height: 18px;
        line-height: 18px;
        text-align: center;
        background: transparent;
        border-radius: 8px;
        color: #999999;
        font-size: 12px;
      }

      &.active {
        .title {
          color: #ff4444;
        }

        .label {
          color: #fff;
          background: linear-gradient(270deg, rgba(255, 84, 0, 1) 0%, rgba(255, 0, 0, 1) 100%);
        }
      }
    }
  }
}

.list-wrapper {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: var(--content-gap);

  .item {
    width: calc((100% - var(--content-gap)) / 2);
    background-color: #fff;
    position: relative;

    .bnt {
      width: 48px;
      height: 28px;
      background: linear-gradient(90deg, #e93323 0%, #ff7931 100%);
      border-radius: 25px;
      color: #fff;
      text-align: center;
      line-height: 28px;
      font-size: 12px;
      position: absolute;
      right: 10px;
      bottom: 10px;
    }

    .jia {
      width: 22px;
      height: 22px;
      background-color: #e93323;
      border-radius: 50%;
      position: absolute;
      right: 10px;
      bottom: 10px;

      .jiaCon {
        width: 100%;
        height: 100%;
        text-align: center;
        line-height: 22px;

        .iconfont {
          color: #fff;
          font-size: 13px;
        }
      }
    }

    .img-box {
      position: relative;
      width: 100%;
      height: 173px;

      .img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      img,
      .box {
        width: 65px;
        height: 50px;
      }

      .empty-box {
        background: #f3f9ff;
      }

      .box {
        background: #d8d8d8;
      }

      .label {
        position: absolute;
        left: 0;
        top: 0;
        width: 46px;
        height: 22px;
        border-radius: 10px 0px 10px 0px;
        color: #fff;
        font-size: 13px;
        text-align: center;
        line-height: 22px;
      }
    }

    .info {
      padding: 7px 10px;

      .title {
        font-size: 14px;
        color: #333;
      }

      img {
        height: 14px;
        display: block;
        margin-top: 4px;
      }

      .bottom {
        color: #999999;
        font-size: 11px;
      }

      .price {
        display: flex;
        align-items: center;

        img {
          width: 70px;
          height: 15px;
          display: block;
        }

        .num {
          font-size: 20px;
          margin-right: 4px;
          font-family: semiBold;

          span {
            font-size: 12px;
          }
        }

        .label {
          width: 16px;
          height: 18px;
          margin-left: 5px;
          text-align: center;
          line-height: 18px;
          font-size: 11px;

          &.on {
            margin-left: 0;
          }
        }
      }
    }
  }
}

.itemA {

  /*background #fff*/
  .item {
    display: flex;
    width: 100%;
    padding: 10px;
    position: relative;

    .img-box {
      position: relative;
      width: 112px;
      height: 112px;

      img {
        width: 65px;
        height: 50px;
      }

      .empty-box {
        background: #f3f9ff;
      }
    }

    .info {
      display: flex;
      // justify-content: space-between;
      flex-direction: column;
      flex: 1;
      margin-left: 10px;
      padding: 0;
      overflow: hidden;

      .hd {
        // height: 63px;
      }

      .price {
        margin-top: auto;
        margin-bottom: 8px;

        &.on {
          margin-top: 20px;
        }

        .img {
          margin-top: 0;
        }
      }
    }
  }
}

.itemB {
  justify-content: inherit;
  background-color: #fff;
  padding: 16px 10px 0 10px;
  width: 100%;
  box-sizing: border-box;

  &:not(.itemD) .list {
    flex: 1;
  }

  .list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--content-gap);
    padding-bottom: 10px;
  }

  .item {
    width: calc((100% - var(--content-gap) * 2) / 3);
    // width: 31.3%;
    // margin-right: 10px;
    background: unset;

    .jia {
      right: 2px;
      bottom: 0;
    }

    .info {
      padding: 0;

      &.on {
        height: 70px;
      }

      &.on2 {
        height: 30px;
      }

      .hd {
        margin-top: 7px;
        height: 42px;
      }

      .price {
        margin-top: 7px;
        line-height: 1.2;
      }
    }

    &:nth-child(3n) {
      margin-right: 0;
    }

    .img-box {
      position: relative;
      width: 100%;
      height: 110px;

      img,
      .box,
      .empty-box {
        border-radius: 10px 10px 0 0;
      }
    }
  }
}

.itemD {
  flex-wrap: nowrap;
  display: inline-flex;
  overflow: hidden;

  .list {
    flex-wrap: nowrap;
    justify-content: center;
    align-items: center;
  }

  .item {
    width: 100px;
    background: unset;

    &:nth-child(3n) {
      margin-right: 10px;
    }

    .img-box {
      height: 100px;
    }
  }
}

.itemC {
  .item {
    background-color: transparent;

    .info {
      // background-color: #fff;
    }
  }

  .item .info.on {
    height: 67px;
  }

  .item .info.on2 {
    height: 40px;
  }

  .item .info .price {
    margin-top: 6px;
    // margin-bottom: 8px;
  }

  .item .info .bottom {
    margin-top: 3px;
  }
}

.filter-box {
  display: flex;
  gap: 12px;
  position: sticky;
  top: 0;
  padding-bottom: var(--content-gap);
  z-index: 1;

  .filter-item {
    flex: 1;
    height: 28px;
    background: #FFFFFF;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #282828;
    gap: 2px;

    &.strong {
      font-weight: 500;
    }

    .iconfont-h5 {
      font-size: 12px;
    }
  }
}

.good-tag {
  padding: 2px 4px;
  background: #E93323;
  border-radius: 2px;
  font-size: 8px;
  color: #FFFFFF;
  line-height: 8px;
  vertical-align: middle;
}

.tag-list {
  display: flex;
  gap: 4px;
  margin-bottom: 6px;

  .tag-item {
    width: 24px;
    height: 12px;
    display: inline-flex;
    // line-height: 14px;
    justify-content: center;
    font-size: 8px;
    border-radius: 2px;
    border: 1px solid currentColor;
    box-sizing: content-box;

    &:nth-child(1) {
      background: #E93323;
      color: #FFFFFF;
    }

    &:nth-child(2) {
      color: #E93323;
    }

    &:nth-child(3) {
      color: #FF9000;
    }

    &:nth-child(4) {
      color: #754EC5;
    }
  }
}

.store-info {
  font-size: 10px;
  color: #666666;
  line-height: 10px;
  margin-top: 4px;
  padding-bottom: 2px;

  span {
    color: #282828;
  }

  .iconfont {
    font-size: 8px;
    color: inherit;
  }
}
</style>

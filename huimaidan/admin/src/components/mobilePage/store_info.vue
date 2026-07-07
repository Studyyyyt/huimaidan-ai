<template>
  <div v-if="renderData" :style="commonStyleVars" class="store-info-wrapper">
    <div class="store-info">
      <img src="@/assets/images/goods_icon.png" class="store-logo" />
      <div class="store-ctx">
        <p class="store-name">
          <span class="store-type">自营</span>
          小米官方旗舰店
        </p>
        <el-rate
          disabled-void-color="#e2e2e2"
          :value="4"
          disabled
          :colors="['#e93323', '#e93323', '#e93323']"
        ></el-rate>
      </div>
      <i class="iconfont-h5 icon-ic_customerservice customer-btn"></i>
      <div class="store-follow-btn">
        <i class="iconfont-h5 icon-ic_love" />
        关注
      </div>
    </div>
  </div>
</template>

<script>
import { diyUtil } from "@/utils/diy-util";
import { mapState } from "vuex";

export default {
  name: "store_info",
  cname: "店铺信息",
  icon: "#iconzujian-dianpujie",
  defaultName: "store_info",
  configName: "c_store_info",
  type: 0,
  sortOrder: 55,
  targetScope: "store",
  props: {
    index: {
      type: null
    },
    num: {
      type: null
    },
    colorStyle: {
      type: null
    }
  },
  data() {
    return {
      defaultConfig: {
        cname: "店铺信息",
        name: "store_info",
        timestamp: this.num,
        isHide: false,
        storeTitle: "店铺样式",
        setUp: {
          tabVal: 1,
          hide: true
        },
        storeName: {
          title: "店铺名称",
          default: [
            {
              item: "#000000"
            }
          ],
          color: [
            {
              item: "#000000"
            }
          ]
        },
        followBtn: {
          title: "关注按钮",
          default: [
            {
              item: "#e93323"
            },
            {
              item: "#e93323"
            }
          ],
          color: [
            {
              item: "#e93323"
            },
            {
              item: "#e93323"
            }
          ]
        },
        normalTitle: "通用样式",
        compBg: {
          title: "组件背景",
          default: [
            {
              item: "#fff"
            },
            {
              item: "#fff"
            }
          ],
          color: [
            {
              item: "#fff"
            },
            {
              item: "#fff"
            }
          ]
        },
        bottomBg: {
          title: "底部背景",
          default: [
            {
              item: "#fff"
            }
          ],
          color: [
            {
              item: "#fff"
            }
          ]
        },
        compOffsetY: {
          title: "组件上浮",
          val: 0,
          min: 0
        },
        mtConfig: {
          title: "上边距",
          val: 0,
          min: 0
        },
        mbConfig: {
          title: "下边距",
          val: 0,
          min: 0
        },
        mlConfig: {
          title: "左右边距",
          val: 0,
          min: 0
        },
        pageTopConfig: {
          title: "页面上边距",
          val: 0,
          min: 0
        },
        fillet: {
          title: "圆角值",
          type: 0,
          list: [
            {
              val: "全部",
              icon: "iconcaozuo-zhengti"
            },
            {
              val: "单个",
              icon: "iconcaozuo-bianjiao"
            }
          ],
          valName: "圆角值",
          val: 10,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }]
        },
        shadowConfig: {
          color: "#888",
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        }
      },
      renderData: null
    };
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true
    },
    num: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true
    },
    defaultArray: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true
    }
  },
  computed: {
    ...mapState("mobildConfig", ["defaultArray"]),
    commonStyleVars() {
      if (!this.renderData) return {};
      const {
        storeName,
        followBtn,
        compOffsetY,
        compBg,
        bottomBg,
        mtConfig,
        mbConfig,
        mlConfig,
        pageTopConfig,
        fillet,
        shadowConfig
      } = this.renderData;

      return {
        "--store-name-color": storeName.color[0].item,
        "--follow-btn-color": diyUtil.buildLinearColor(followBtn),
        "--comp-bg": diyUtil.buildLinearColor(compBg),
        "--bottom-bg": bottomBg.color[0].item,
        "--mt": `${mtConfig.val}px`,
        "--mb": `${mbConfig.val}px`,
        "--ml": `${mlConfig.val}px`,
        "--fillet": diyUtil.buildBorderRadius(fillet),
        "--shadow": diyUtil.buildShadowStyle(shadowConfig),
        marginTop: diyUtil.buildMarginTopOffset(pageTopConfig, compOffsetY)
      };
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data || !data.storeName) return;
      this.renderData = JSON.parse(JSON.stringify(data));
    }
  }
};
</script>

<style scoped lang="scss">
.store-info-wrapper {
  background-color: var(--bottom-bg);
  padding-top: var(--mt);
  padding-bottom: var(--mb);
  padding-inline: var(--ml);

  .store-info {
    display: flex;
    align-items: center;
    padding: 15px 10px;

    border-radius: var(--fillet);
    box-shadow: var(--shadow);
    background: var(--comp-bg);

    .store-logo {
      width: 37px;
      height: 37px;
      margin-right: 11px;
    }

    .store-ctx {
      display: flex;
      flex-flow: column;
    }

    .store-name {
      color: var(--store-name-color);
      font-size: 15px;
      display: flex;
      align-items: center;
    }

    .store-type {
      color: #fff;
      width: 24px;
      height: 14px;
      background: #e93323;
      border-radius: 2px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 9px;
      margin-right: 5px;
    }

    ::v-deep .el-rate {
      height: auto;
    }

    ::v-deep .el-rate__icon {
      font-size: 13px;

      &.el-icon-star-on {
        margin-right: 2px;
      }
    }

    .customer-btn {
      margin-left: auto;
      font-size: 17px;
      color: #282828;
      margin-right: 14px;
    }

    .store-follow-btn {
      width: 57px;
      height: 24px;
      background: var(--follow-btn-color);
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      color: #fff;

      .iconfont-h5 {
        font-size: 11px;
        margin-right: 2px;
      }
    }
  }
}
</style>

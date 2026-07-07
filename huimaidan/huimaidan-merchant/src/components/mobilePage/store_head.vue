<template>
  <div v-if="renderData" :style="commonStyleVars" class="store-head-wrap">
    <div class="background-filter">
      <div class="search-box-wrap">
        <img src="@/assets/images/diy-left-nav.png" class="left-nav" />
        <div class="search-box">
          <i class="iconfont iconsearch_1" />
          <span
            class="search-hot-word"
            v-if="
              renderData.searchHotWord.list.length &&
                renderData.searchHotWord.list[0].val
            "
          >
            {{ renderData.searchHotWord.list[0].val }}
          </span>
          <span class="search-tips" v-else>
            {{
              renderData.searchTipConfig.value ||
                renderData.searchTipConfig.place
            }}
          </span>
        </div>
      </div>

      <div class="store-info-box">
        <img src="@/assets/images/goods_icon.png" class="store-logo" />
        <div class="store-info">
          <p class="store-name">
            六福珠宝官方旗舰店
            <i class="iconfont iconyou" />
          </p>
          <div class="store-intro">
            <template v-if="renderData.storeHeadVisible && renderData.storeHeadVisible.type.includes(0)">
              <span class="store-tag">自营</span>
              <span class="store-tag">旗舰店</span>
            </template>
            <el-rate
              v-if="renderData.storeHeadVisible && renderData.storeHeadVisible.type.includes(1)"
              :value="4.5"
              disabled
              :colors="['#ff7d00', '#ff7d00', '#ff7d00']"
            ></el-rate>
          </div>
        </div>
        <div class="store-follow-btn" v-if="renderData.storeHeadVisible && renderData.storeHeadVisible.type.includes(2)">
          <i class="iconfont-h5 icon-ic_love" />
          关注
        </div>
      </div>

      <div
        class="swiper-box"
        v-if="
          renderData.swiperConfig.list.length &&
            renderData.swiperConfig.list[0].img
        "
        :class="swiperCss"
      >
        <div class="style2-bg"></div>
        <div
          class="left-img-box"
          :style="{
                    '--src': `url(${swiperStyle3Info.leftImg})`
                  }"
          v-if="swiperStyle3Info.leftImg"/>
  
        <div 
          class="right-img-box"
          :style="{
            '--src': `url(${swiperStyle3Info.rightImg})`
          }" 
          v-if="swiperStyle3Info.rightImg"></div>
        <!-- <img
          :src="swiperStyle3Info.leftImg"
          v-if="swiperStyle3Info.leftImg"
          alt=""
          class="left-img slider-img"
        />
        <img
          :src="swiperStyle3Info.rightImg"
          v-if="swiperStyle3Info.rightImg"
          alt=""
          class="right-img slider-img"
        /> -->
        <div class="swiper-img-wrap" :style="{ aspectRatio: firstImageRatio }">
          <img :src="renderData.swiperConfig.list[0].img" class="swiper-img" />
        </div>
        <div
          class="dot-wrapper"
          :class="dotInfo"
          v-if="renderData.swiperConfig.list.length > 1"
        >
          <div class="dot-list">
            <div
              class="dot-item"
              v-for="(item, index) in renderData.swiperConfig.list"
              :key="index"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex";
import { diyUtil } from "@/utils/diy-util";

export default {
  name: "store_head",
  cname: "头部组件",
  icon: "#iconzujian-zuhezujian",
  defaultName: "store_head",
  configName: "c_store_head",
  type: 0,
  sortOrder: 15,
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
  computed: {
    ...mapState("mobildConfig", ["defaultArray"]),
    swiperCss() {
      if (!this.renderData) return "";
      const { swiperStyle } = this.renderData;
      return "style" + (swiperStyle.tabVal + 1);
    },
    swiperStyle3Info() {
      if (!this.renderData) return {};
      const { swiperConfig, swiperStyle } = this.renderData;
      if (swiperStyle.tabVal !== 2) return {};
      const imgList = swiperConfig.list.map(item => item.img);
      if (imgList.length < 2) return {};
      const leftImg = imgList[1];
      const rightImg = imgList[imgList.length - 1];

      return {
        leftImg,
        rightImg
      };
    },
    dotInfo() {
      if (!this.renderData) return {};
      const { docConfig, docToneConfig } = this.renderData;

      const cssList = ["style" + (docConfig.tabVal + 1)];

      if (docToneConfig.tabVal === 0) {
        cssList.push("custom-tone");
      }
      return cssList;
    },
    commonStyleVars() {
      if (!this.renderData) return {};
      const {
        swiperConfig,
        searchBoxColor,
        tipColor,
        hotWordsColor,
        toneConfig,
        shadowConfig,
        storeNameColor,
        followBtnColor,
        fillet,
        imgGap,
        imgShadowConfig,
        docPosition,
        docActiveColor,
        docNormalColor
      } = this.renderData;

      const alignMap = {
        0: "left",
        1: "center",
        2: "right"
      };

      const styles = {
        "--search-box-bg": searchBoxColor.color[0].item,
        "--search-box-text": tipColor.color[0].item,
        "--search-box-hot-words": hotWordsColor.color[0].item,
        "--search-box-shadow":
          toneConfig.tabVal === 0
            ? "none"
            : diyUtil.buildShadowStyle(shadowConfig),

        "--store-name-color": storeNameColor.color[0].item,
        "--follow-btn-color": diyUtil.buildLinearColor(followBtnColor),

        "--img-gap": imgGap.val + "px",
        "--img-fillet": diyUtil.buildBorderRadius(fillet),
        "--img-shadow": diyUtil.buildShadowStyle(imgShadowConfig),

        "--dot-align": alignMap[docPosition.tabVal],
        "--dot-color": docNormalColor.color[0].item,
        "--dot-active-color": docActiveColor.color[0].item,
        "--dot-count": swiperConfig.list.length
      };

      if (swiperConfig.list.length && swiperConfig.list[0].img) {
        styles["--swiper-img"] = `url(${swiperConfig.list[0].img})`;
      }

      return styles;
    }
  },
  watch: {
    'renderData.swiperConfig.list': {
      handler(nVal, oVal) {
        if (nVal.length) {
          const newSrc = nVal[0].img
          newSrc && this.updateFirstImageSize(newSrc)
        }
      },
      deep: true
    },
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
  data() {
    return {
      defaultConfig: {
        cname: "头部组件",
        name: "store_head",
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0
        },
        tabConfig: {
          tabList: [
            {
              name: "搜索框"
            },
            {
              name: "店铺信息"
            },
            {
              name: "轮播图"
            }
          ],
          tabVal: "0"
        },
        titleSearch: {
          title: "搜索设置",
          hideTop: true
        },
        searchTipConfig: {
          title: "搜索提示",
          value: "",
          place: "请输入搜索内容",
          max: 10
        },
        searchHotWordTitle: "搜索热词",
        searchHotWord: {
          list: []
        },
        hotwordDisplayTime: {
          placeholder: "设置搜索热词显示时间",
          title: "显示时间",
          val: 3,
          type: "words",
          unit: "秒"
        },

        storeInfoTitle: {
          title: "店铺配置",
          hideTop: true
        },
        storeHeadVisible: {
          title: "展示信息",
          name: "storeHeadVisible",
          type: [0, 1, 2],
          list: [
            {
              id: 0,
              name: "店铺标签"
            },
            {
              id: 1,
              name: "店铺评分"
            },
            {
              id: 2,
              name: "关注按钮"
            }
          ]
        },

        swiperStyleTitle1: {
          title: "展示设置",
          hideTop: true
        },
        swiperStyle: {
          title: "选择风格",
          tabList: [
            {
              name: "样式一",
              value: 0
            },
            {
              name: "样式二",
              value: 2
            }
          ],
          tabVal: 0
        },
        contentStyleTitle: "内容设置",
        swiperConfig: {
          bnt: "添加图片",
          maxList: 10,
          list: [
            {
              img: require("@/assets/images/product_diy_main.webp"),
              imgTitle: "图片",
              info: [
                {
                  title: "链接",
                  value: "",
                  tips: "输入链接",
                  max: 100
                }
              ]
            },
            {
              img: require("@/assets/images/product_diy_main.webp"),
              imgTitle: "图片",
              info: [
                {
                  title: "链接",
                  value: "",
                  tips: "输入链接",
                  max: 100
                }
              ]
            },
            {
              img: require("@/assets/images/product_diy_main.webp"),
              imgTitle: "图片",
              info: [
                {
                  title: "链接",
                  value: "",
                  tips: "输入链接",
                  max: 100
                }
              ]
            }
          ]
        },

        searchBoxTitle: "搜索框",
        searchBoxColor: {
          title: "背景颜色",
          default: [
            {
              item: "rgba(255, 255, 255, 0.4)"
            }
          ],
          color: [
            {
              item: "rgba(255, 255, 255, 0.4)"
            }
          ]
        },
        tipColor: {
          title: "提示文字颜色",
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
        hotWordsColor: {
          title: "热词文字颜色",
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
        toneConfig: {
          title: "色调",
          tabVal: 0,
          tabList: [
            {
              name: "跟随主题风格"
            },
            {
              name: "自定义"
            }
          ]
        },
        shadowConfig: {
          color: "#888",
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },

        storeStyleTitle: "店铺样式",
        storeNameColor: {
          title: "店铺名称",
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
        followBtnColor: {
          title: "关注按钮",
          default: [
            {
              item: "transparent"
            },
            {
              item: "transparent"
            }
          ],
          color: [
            {
              item: "transparent"
            },
            {
              item: "transparent"
            }
          ]
        },

        swiperStyleTitle: "指示器样式",
        docConfig: {
          title: "指示器样式",
          tabVal: 0,
          tabList: [
            {
              name: "样式一"
            },
            {
              name: "样式二"
            },
            {
              name: "样式三"
            },
            {
              name: "样式四"
            }
          ]
        },
        docPosition: {
          title: "指示器位置",
          tabVal: 1,
          tabList: [
            {
              name: "左对齐"
            },
            {
              name: "居中对齐"
            },
            {
              name: "右对齐"
            }
          ]
        },
        docToneConfig: {
          title: "色调",
          tabVal: 0,
          tabList: [
            {
              name: "跟随主题风格"
            },
            {
              name: "自定义"
            }
          ]
        },
        docActiveColor: {
          title: "选中样式",
          default: [
            {
              item: "#e93323"
            }
          ],
          color: [
            {
              item: "#e93323"
            }
          ]
        },
        docNormalColor: {
          title: "常规样式",
          default: [
            {
              item: "#dcdcdc"
            }
          ],
          color: [
            {
              item: "#dcdcdc"
            }
          ]
        },

        imgStyleTitle: "图片设置",
        fillet: {
          title: "图片圆角",
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
        imgGap: {
          title: "图片间距",
          val: 10,
          min: 0
        },
        imgShadowConfig: {
          color: "#888",
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        }
      },

      renderData: null,
      firstImageRatio: 1
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    updateFirstImageSize(src) {
      const img = new Image()
      img.onload = () => {
        this.firstImageRatio = img.width / img.height
      }
      img.src = src
    },
    setConfig(data) {
      if (data && data.setUp) {
        this.renderData = data
      }
    }
  }
};
</script>

<style scoped lang="scss">
.store-head-wrap {
  background: var(--swiper-img) no-repeat center / cover;
  overflow: hidden;
}

.background-filter {
  backdrop-filter: blur(8px);
}

.search-box-wrap {
  padding: 6px 12px;
  display: flex;
  gap: 10px;

  .left-nav {
    width: 77px;
    height: 29px;
  }

  .search-box {
    background-color: var(--search-box-bg);
    box-shadow: var(--search-box-shadow);
    font-size: 12px;

    .iconfont {
      font-size: 12px;
      color: #fff;
      margin-right: 7px;
    }

    .search-tips {
      color: var(--search-box-text);
    }

    .search-hot-word {
      color: var(--search-box-hot-words);
    }

    flex: 1;
    display: flex;
    align-items: center;
    border-radius: 16px;
    padding-inline: 10px;
  }
}

.store-info-box {
  padding: 7px 12px;
  display: flex;
  align-items: center;
  gap: 6px;

  .store-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }

  .store-name {
    font-weight: 500;
    font-size: 15px;
    color: var(--store-name-color);
    line-height: 21px;

    display: flex;
    align-items: center;
    gap: 8px;

    .iconfont {
      font-size: 8px;
    }
  }

  .store-info {
    display: flex;
    flex-direction: column;

    .store-intro {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .store-tag {
      height: 13px;
      background: #faad14;
      border-radius: 2px;
      display: inline-flex;
      align-items: center;
      padding: 0 2px;
      font-size: 9px;
      color: #fff;

      &:first-child {
        background: #ea0000;
      }
    }

    ::v-deep .el-rate {
      height: auto;
    }

    ::v-deep .el-rate__icon {
      font-size: 13px;

      &.el-icon-star-on {
        margin-right: 4px;
      }
    }
  }

  .store-follow-btn {
    .iconfont-h5 {
      font-size: 12px;
      margin-right: 4px;
    }

    margin-left: auto;
    padding-inline: 9px;
    height: 27px;
    border-radius: 13px;
    border: 1px solid #ffffff;
    color: #fff;
    background: var(--follow-btn-color);
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.swiper-box {
  position: relative;

  &.style1 {
    padding-top: 5px;
  }

  .style2-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 50%;
    background-color: #fff;
    display: none;
  }

  &.style2,
  &.style3 {
    .dot-wrapper {
      bottom: 26px;
    }
  }

  &.style3 {
    padding: 5px calc(var(--img-gap) + 16px) 12px;
  }

  &.style2 {
    padding: 16px 10px;

    .style2-bg {
      display: block;
    }
  }

  .swiper-img-wrap {
    width: 100%;
  }

  .swiper-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: relative;
    border-radius: var(--img-fillet);
    box-shadow: var(--img-shadow);
  }

  .left-img-box {
    position: absolute;
    top: 24px;
    bottom: 24px;
    left: 16px;
    width: 144px;
    background: var(--src) no-repeat right center / cover;
    transform: translateX(-100%);
    border-radius: var(--img-fillet);
  }

  .right-img-box {
    position: absolute;
    top: 24px;
    bottom: 24px;
    right: 16px;
    width: 144px;
    background: var(--src) no-repeat left center / cover;
    transform: translateX(100%);
    border-radius: var(--img-fillet);
  }

  .slider-img {
    border-radius: var(--img-fillet);
    box-shadow: var(--img-shadow);
    position: absolute;
    height: 144px;
    width: 144px;
    object-fit: cover;
    top: 24px;

    &.left-img {
      left: 16px;
      transform: translateX(-100%);
    }

    &.right-img {
      right: 16px;
      transform: translateX(100%);
    }
  }

  .dot-wrapper {
    position: absolute;
    bottom: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    justify-content: var(--dot-align);

    --dot-w: 6px;
    --dot-h: 6px;
    --dot-active-w: 6px;
    --dot-active-h: 6px;
    --dot-radius: 50%;
    --dot-gap: 8px;

    &.style1 {
      --dot-gap: 8px;
    }

    &.style2 {
      --dot-gap: 4px;
      --dot-w: 5px;
      --dot-h: 5px;
      --dot-active-w: 9px;
      --dot-active-h: 5px;
      --dot-radius: 4px;
    }

    &.style3,
    &.style4 {
      --dot-gap: 5px;
      --dot-w: 10px;
      --dot-h: 3px;
      --dot-active-w: 10px;
      --dot-active-h: 3px;
      --dot-radius: 4px;
    }

    &.style3 {
      // 样式三下，仅展示一个指示器，轨道长度为单个指示器长度 * 轮播图数量
      .dot-list {
        width: calc(var(--dot-active-w) * var(--dot-count));
        background-color: var(--dot-color);
        border-radius: var(--dot-radius);
      }

      .dot-item:not(:first-child) {
        display: none;
      }
    }

    .dot-list {
      display: flex;
      gap: var(--dot-gap);

      .dot-item {
        background-color: var(--dot-color);

        &:first-child {
          width: var(--dot-active-w);
          height: var(--dot-active-h);
          background-color: var(--dot-active-color);
        }

        width: var(--dot-w);
        height: var(--dot-h);
        border-radius: var(--dot-radius);
      }
    }
  }
}
</style>

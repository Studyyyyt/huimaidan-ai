<template>
  <div class="mobile-page">
    <div
      class="flex-box"
      :style="wrapperStyle"
    >
      <div class="left">
        <div class="img-box">
          <div class="empty-box on">
            <img :src="imgUrl" alt="" v-if="imgUrl" />
            <img src="../../assets/images/noPictrue.png" v-else />
          </div>
        </div>
        <div class="name">{{ txt }}</div>
      </div>
      <div class="right">
        <div class="btn" :style="{ borderColor: themeColor, color: btnTextColor }">关注</div>
        <div class="iconfont iconguanbi5"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { diyUtil } from "@/utils/diy-util";

export default {
  name: 'z_wechat_attention',
  cname: '公众号',
  configName: 'c_wechat_attention',
  icon: '#iconzujian-gongzhonghao',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'follow', // 外面匹配名称
  sortOrder: 350,
  targetScope: "platform", // 组件使用范围，platform 平台，store 门店，无则通用
  props: {
    index: {
      type: null,
      default: -1,
    },
    num: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
    wrapperStyle() {
      if (!this.renderData) return {};
      return {
        background: this.bgColor,
        marginTop: diyUtil.buildMarginTopOffset(this.renderData.mbConfig, this.renderData.offsetYConfig),
        marginLeft: this.mlConfig + 'px',
        marginRight: this.mlConfig + 'px',
        paddingTop: this.topConfig + 'px',
        paddingBottom: this.bottomConfig + 'px',
        boxShadow: this.shadowConfig,
        borderRadius: diyUtil.buildBorderRadius(this.renderData.fillet),
      };
    }
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {},
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
  },
  data() {
    return {
      // 默认初始化数据禁止修改
      defaultConfig: {
        cname: '关注公众号',
        name: 'follow',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '标题设置',
        positionTitle: '位置设置',
        pictrueTitle: '图片设置',
        codeTitle: '关注二维码',
        titleRight: '关注按钮',
        titleCurrency: '卡片样式',
        positionConfig: {
          title: '展示位置',
          tabVal: 0,
          tabList: [
            {
              name: '顶部',
            },
            {
              name: '底部',
            },
          ],
        },
        titleConfig: {
          title: '标题名称',
          value: '标题',
          place: '请输入标题',
          max: 10,
        },
        imgConfig: {
          info: '建议：图片尺寸92px * 92px',
          url: '',
          type: 'code',
          name: '上传图片',
        },
        codeConfig: {
          url: '',
          type: 'code',
          name: '上传二维码',
        },
        themeColor: {
          title: '按钮边框',
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
        btnTextColor: {
          title: '按钮文字',
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
        offsetYConfig: {
          title: "组件上浮",
          val: 0,
          min: 0,
        },
        bgColor: {
          title: '背景颜色',
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
        topConfig: {
          title: '上边距',
          val: 0,
          min: 0,
        },
        bottomConfig: {
          title: '下边距',
          val: 0,
          min: 0,
        },
        prConfig: {
          title: '左右边距',
          val: 0,
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
          val: 0,
          min: 0,
          valList: [{ val: 0 }, { val: 0 }, { val: 0 }, { val: 0 }],
        },
        shadowConfig: {
          color: '#888',
          x: 0,
          y: 0,
          blur: 0,
          spread: 0,
          visible: 0
        },
      },
      cSlider: '',
      bgColor: '',
      confObj: {},
      pageData: {},
      edge: '',
      udEdge: '',
      themeColor: '',
      mTOP: 0,
      imgUrl: '',
      txt: '',
      mlConfig: 0,
      fillet: 0,
      filletVal: 0,
      valList: [],
      btnTextColor: '',
      offsetYConfig: 0,
      topConfig: 0,
      bottomConfig: 0,
      shadowConfig: 'none',

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
        this.bgColor = diyUtil.buildLinearColor(data.bgColor);
        this.themeColor = data.themeColor.color[0].item;
        this.mTOP = data.mbConfig.val;
        this.imgUrl = data.imgConfig.url;
        this.txt = data.titleConfig.value;
        this.mlConfig = data.prConfig.val;
        this.fillet = data.fillet.type;
        this.filletVal = data.fillet.val;
        this.valList = data.fillet.valList;
        this.btnTextColor = data.btnTextColor.color[0].item;
        this.topConfig = data.topConfig.val;
        this.bottomConfig = data.bottomConfig.val;
        this.offsetYConfig = data.offsetYConfig.val;
        this.shadowConfig = diyUtil.buildShadowStyle(data.shadowConfig);
      }
    },
  },
};
</script>

<style scoped lang="scss">
.flex-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 10px;
  height: 60px;

  .iconfont {
    color: #999;
    font-size: 15px;
    margin-left: 10px;
  }

  .right {
    display: flex;
    align-items: center;
    margin-left: 10px;
  }

  .left {
    display: flex;
    align-items: center;

    .img-box,
    .empty-box {
      width: 46px;
      height: 46px;
      border-radius: 50%;

      img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
    }

    .name {
      margin-left: 10px;
      font-size: 15px;
      color: #333;
    }
  }

  .btn {
    width: 56px;
    height: 28px;
    border: 1px solid #e93323;
    opacity: 1;
    border-radius: 25px;
    color: #e93323;
    font-size: 12px;
    text-align: center;
    line-height: 28px;
  }

  .iconfont-diy {
    font-size: 20px;
  }
}
</style>

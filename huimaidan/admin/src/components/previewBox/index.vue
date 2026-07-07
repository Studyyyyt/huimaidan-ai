<template>
  <div class="goods_detail">
    <div class="goods_detail_wrapper" :class="(previewKey || goodsId) ? 'on' : ''">
      <iframe
        :src="iframeSrc"
        style="width: 100%;height:600px;"
        frameborder="0" />
        <div class="tips-box">
          <span class="small-tips">若页面未加载出，请前往系统配置页面填写网站域名</span>
          <el-button type="text" @click="gotoSystemConfig">点击前往</el-button>
        </div>
    </div>
  </div>
</template>
<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { roterPre } from '@/settings'
export default {
  name: 'PreviewBox',
  props: {
    isSeckill: {
      type: Boolean,
      default: false
    },
    goodsId: {
      type: String | Number,
      default: ''
    },
    productType: {
      type: String | Number,
      default: ''
    },
    previewKey: {
      type: String | Number,
      default: ''
    }
  },
  data() {
    return {

    }
  },
  computed: {
    iframeSrc() {
      if (this.isSeckill) {
        return `https://mer1.crmeb.net/pages/activity/goods_seckill_details/index?id=${this.goodsId}&inner_frame=1`;
      } else if (this.previewKey) {
        return `/pages/admin/goods_details/index?preview_key=${this.previewKey}&product_type=${this.productType}&inner_frame=1`
      } else if (this.goodsId) {
        return `/pages/admin/goods_details/index?product_id=${this.goodsId}&product_type=${this.productType}&inner_frame=1`
      }

      return ''
    }
  },
  mounted() {

  },
  methods: {
    gotoSystemConfig() {
      this.$router.push({
        path: `${roterPre}/systemForm/Basics/system_tabs`,
      });
    },
    getProListUrl() {

    }
  }
}
</script>

<style scoped lang="scss">
.goods_detail {
  .goods_detail_wrapper {
    z-index: 200;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 375px;
    background: #F0F2F5;
    &.on{
        position: fixed;
    }
  }
}

.tips-box {
  margin-top: 10px;
  text-align: center;
  .small-tips {
    font-size: 14px;
    color: red;
  }
}
</style>

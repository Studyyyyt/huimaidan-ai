<template>
  <div class="px-10">
    <div class="rd-12px bg--w111-fff py-10 mb-8">
      <div class="px-10 flex-between-center">
        <div>
          <span class="text--w111-282828 fs-15 fw-500">用户评价(20)</span>
        </div>
        <div class="flex-y-center">
          <div class="text--w111-999">
            <span class="fs-14 text-primary-con">99%</span>好评率
          </div>
          <span class="iconfont-h5 icon-ic_rightarrow fs-12 text--w111-666"></span>
        </div>
      </div>
      <!-- 滑动内容 -->
      <div class="px-10 mt-12">
        <div class="evaluateItem rd-8px">
          <div class="pic-text acea-row row-middle">
            <div class="pictrue">
               <img src="@/assets/images/ren.png" />
            </div>
            <div class="acea-row row-middle">
              <div class="name line1">一*兔</div>
              <div class="start"></div>
            </div>
          </div>
          <div class="time">2019.01.04 17:11 蓝色*1盒</div>
          <div class="evaluate-infor">质量挺不错的，用了一段时间也不起球，会推荐朋友 一起回购的~~~</div>
          <div class="imgList acea-row">
            <div class="pictrues">
              <img class="image" src="@/assets/images/product_diy_reply.webp" />
            </div>
            <div class="pictrues">
              <img class="image" src="@/assets/images/product_diy_reply.webp" />
            </div>
            <div class="pictrues">
              <img class="image" src="@/assets/images/product_diy_reply.webp" />
            </div>
            <div class="pictrues">
              <img class="image" src="@/assets/images/product_diy_reply.webp" />
              <span class="morePic">+3</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mask" v-if="!showReply">已隐藏</div>
  </div>
</template>
<script>
export default {
  props:{
    showReply:{
      type: Number,
      default: 1
    }
  },
  data(){
    return {
      pic: require('@/assets/images/product_diy.jpg')
    }
  }
}
</script>
<style scoped lang="scss">
  .evaluateItem {
    background-color: #fff;
    .pic-text  {
      font-size: 14px;
      color: #282828;
      .pictrue {
        width: 30px;
        height: 30px;
        margin-right: 11px;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .name {
        max-width: 248px;
        margin-right: 8px;
        font-size: 14px;
        color: #282828;
      }
      .start {
        width: 68px;
        height: 16px;
        background-repeat: no-repeat;
        background-size: 67px auto;
        background-image: url("../../../../../assets/images/score2.png");
      }
    }
    .time {
      font-size: 12px;
      color: #999999;
      margin-top: 14px;
    }
    .evaluate-infor {
      font-size: 15px;
      color: #282828;
      margin-top: 16px;
      line-height: 24px;
    }
    .imgList {
      margin-top: 11px;
      display: flex;
      .pictrues,.image {
        width: 78px;
        height: 78px;
        margin-right: 6px;
        position: relative;
        border-radius: 6px;
        position: relative;
        &:last-child {
          margin-right: 0;
        }
        .morePic {
          position: absolute;
          width: 78px;
          height: 78px;
          color: #ffffff;
          font-size: 20px;
          text-align: center;
          top: 0;
          left: 0;
          background: rgba(0, 0, 0, 0.5);
          padding-top: 25px;
          box-sizing: border-box;
          border-radius: 6px;
        }
      }
    }
  }
</style>
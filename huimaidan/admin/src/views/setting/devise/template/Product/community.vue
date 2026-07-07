<template>
  <div class="px-10">
    <div class="rd-12px bg--w111-fff py-16 mb-8">
      <div class="px-10 flex-between-center">
        <div>
          <span class="text--w111-282828 fs-15 fw-500">种草秀(3)</span>
        </div>
        <div class="text--w111-999 fs-14">查看全部<span class="iconfont-h5 icon-ic_rightarrow fs-12"></span></div>  
      </div>
      <!-- 滑动内容 -->
      <div class="px-10 mt-12">
        <div class="h-96px w-full rd-8px flex-y-center">
          <img :src="pic" class="w-105 rd-8px block mr-8" v-for="(item,index) in 3" :key="index" />
        </div>
      </div>
    </div>
    <div class="mask" v-if="!showCommunity">已隐藏</div>
  </div>
</template>
<script>
export default {
  props:{
    showCommunity:{
      type: Number,
      default: 1
    }
  },
  data(){
    return {
      pic: require('@/assets/images/product_diy_plant.webp')
    }
  }
}
</script>

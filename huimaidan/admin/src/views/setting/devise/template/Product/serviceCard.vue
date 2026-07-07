<template>
  <div class="px-10">
    <div class="rd-12px bg--w111-fff mb-8">
      <template v-for="(item, index) in serviceList">
        <div v-show="showService.includes(item.value)" class="flex-between-center h-44 px-10" :key="index">
          <div class="flex-y-center">
            <span class="fs-13 text--w111-888">{{item.name}}：</span>
            <div class="ml-16 text--w111-333 fs-13 flex-1 line1">{{item.info}}</div>
          </div>
          <span class="iconfont-h5 icon-ic_rightarrow fs-12 text--w111-666"></span>
        </div>
      </template>
      
      <div class="flex-center h-44 px-10" v-if="!showService.length"></div>
    </div>
    <div class="mask" v-if="!showService.length">已隐藏</div>
  </div>
</template>
<script>
export default {
  props:{
    showService:{
      type: Array,
      default: ()=>[]
    },
    serviceList: {
      type: Array,
      default: ()=>[]
    }
  },
}
</script>
<style>
.bg-primary-light{
  background: rgba(233, 51, 35, 0.1);
}
.text-primary-con{
  color: #e93323;
}
</style>

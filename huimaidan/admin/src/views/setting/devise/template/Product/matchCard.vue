<template>
  <div class="px-10">
    <div class="rd-12px bg--w111-fff py-16 mb-8">
      <div class="px-10 flex-between-center">
        <div>
          <span class="text--w111-333 fs-15 fw-500">优惠套餐(3)</span>
        </div>
        <span class="iconfont-h5 icon-ic_rightarrow fs-12 text--w111-666"></span>
      </div>
      <div class="px-10 mt-12">
        <div class="img-box acea-row">
          <div class="combo_item acea-row row-middle" v-for="(item,index) in comboList" :key="index">
            <div class="img-item acea-row row-middle" v-for="(itemn,indexn) in item.discountsProduct" :key="indexn">
              <img :src="itemn.image" class="img" mode="" />
              <span v-if="indexn != item.count-1" class="iconfont-h5 icon-ic_increase"></span>
            </div>
            <div class="list_total">
              <div class="list_num fs-12">共{{item.count}}件</div>
              <div class="list_price fs-14">省 ¥{{item.max_price}}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mask" v-if="!showMatch">已隐藏</div>
  </div>
</template>
<script>
export default {
  props:{
    showMatch:{
      type: Number,
      default: 1
    }
  },
  data(){
    return {
      pic: require('@/assets/images/product_diy.jpg'),
      comboList: [
        {
          max_price: "32.00",
          count: 3,
          discountsProduct: [
            {
              image: require('@/assets/images/product_diy_menu.webp'),
            },
            {
              image: require('@/assets/images/product_diy_menu.webp'),
            },
            {
              image: require('@/assets/images/product_diy_menu.webp'),
            }
          ]
        }
      ]
    }
  }
}
</script>
<style scoped lang="scss">
  .img-box {
    margin-top: 5px;
    .combo_item {
      position: relative;
      .img {
        width: 64px;
        height: 64px;
        border-radius: 8px;
      }
      .icon-ic_increase {
        color: #666666;
        margin: 0 8px;
        font-size: 8px;
      }
    }
    .list_total {
      padding: 0 15px;
      .list_price {
        color: #E93323;
        margin-top: 5px;
      }
    }
  }
</style>

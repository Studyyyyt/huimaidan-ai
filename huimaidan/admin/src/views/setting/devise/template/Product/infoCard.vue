<template>
  <div>
    <div class="main-pic relative">
      <div class="w-full h-40 flex-between-center abs-lt z-20">
        <div class="menu_box ml10">
          <img src="@/assets/images/back_icon.png">
          <div class="menu_line"></div>
          <img src="@/assets/images/menu_icon.png">
        </div>
        <!--菜单下拉-->
        <div class="dialog_nav">
          <div class="dialog_nav_item" v-for="(item,index) in selectNavList" :key="index" v-if="navList.includes(index)">
            <span class="iconfont-h5" :class="item.icon"></span>
            <span class="pl-20">{{item.name}}</span>
          </div>
        </div>	
        <div class="acea-row flex-between-center">
          <div v-if="showShare" class="share-icon">
            <i class="iconfont-h5 icon-ic_transmit1"/>
          </div>
          <div class="menu_box">
            <span class="iconfont-h5 icon-ic_more fs-20"></span>
            <div class="menu_line"></div>
            <span class="iconfont-h5 icon-ic_jindu1 fs-20"></span>
          </div>
        </div>   
      </div>
      <img :src="pic" />
      <div class="dot-line acea-row row-center-wrapper" v-if="showDot">
        <div class="item" v-for="(item,index) in 5" :key="index"></div>
      </div>
    </div>
    <div class="relative info-box">
      <div class="px-16">
        <div class="acea-row row-between items-center pt-12">
         <div class="acea-row items-baseline">
          <div class="acea-row items-baseline red">
            <span class="fs-16">¥</span>
            <span class="fs-24 semiBold">199</span>
            <span class="fs-16 semiBold">.00</span>
            </div>
          <div class="svip acea-row">
            <div class="regular fs-14">¥26.00</div>
            <img class="svip-img" src="@/assets/images/svip_icon.png">
          </div>
         </div>
         <div class="acea-row items-baseline">
          <template v-for="(item, index) in shareList">
            <div v-show="shareConfig.includes(item.value)" class="text-center share-item" :key="index">
              <div :class="'iconfont-h5 ' + item.icon"></div>
              <div class="name">{{item.label}}</div>
            </div>
          </template>
         </div>
        </div>
        <div v-if="showSvip" class="h-35 rd-5px bg--w111-FFF0D1 flex-between-center px-10 mb-8 mt-10">
          <div class="flex-y-center">
            <img src="@/assets/images/svip_user.png" class="w-20 h-20" />
            <div class="pl-8">
              <span class="fs-12 text--w111-B37400">开通SVIP会员预计省立省29元</span>
            </div>
          </div>
          <div class="fs-12 text--w111-B37400">
            <span>立即开通</span>
            <span class="iconfont-h5 icon-ic_rightarrow fs-12"></span>
          </div>
        </div>
        <!--积分、包邮-->
        <div class="acea-row mt10">
          <span class="text-integral mr-8">积分最高可抵扣14.2元</span>
          <span class="text-integral">包邮</span>
        </div>
        <div class="pro-title">年货节立即抢购兰蔻唇香礼盒 是我香水口红289节日套装礼品</div>
        <div class="acea-row row-between-wrapper mt10 fs-11 text--w111-999 pb-12">
          <div v-show="isOpen.includes(0)" class="text-line">¥234.00</div>
          <div v-show="isOpen.includes(2)">库存:1425件</div>
          <div v-show="isOpen.includes(1)">已售:2399件</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

export default {
  name: 'productBox',
  props:{
    showDot:{
      type: Number,
      default: 1
    },
    showShare:{
      type: Number,
      default: 1
    },
    isOpen:{
      type: Array,
      default: ()=> []
    },
    navList:{
      type: Array,
      default: ()=> []
    },
    showSvip: {
      type: Number,
      default: 1  
    },
    shareConfig:{
      type: Array,
      default: ()=> []
    },
    shareList:{
      type: Array,
      default: ()=> []
    },
  },
 
  data(){
    return {
      pic: require('@/assets/images/product_diy_main.webp'),
      selectNavList: [{
        name: '首页',
        icon: 'icon-ic_home',
        url: '/pages/index/index',

      },
      {
        name: '搜索',
        icon: 'icon-ic_search',
        url: '/pages/columnGoods/goods_search_con/index',
      },
      {
        name: '购物车',
        icon: 'icon-ic_ShoppingCart1',
        url: '/pages/order_addcart/order_addcart',
      },
      {
        name: '我的收藏',
        icon: 'icon-ic_star',
        url: '/pages/users/user_goods_collection/index',
      },
      {
        name: '个人中心',
        icon: 'icon-ic_user1',
        url: '/pages/user/index'
      }],
    }
  },

}
</script>
<style scoped lang="scss">
  .main-pic img{
    width: 100%;
    height: 375px;
  }
  .menu_box{
    width: 77px;
    height: 29px;
    border-radius: 16px;
    background: rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .menu_box .icon-ic_more {
    position: relative;
    font-size: 21px;
    &::after {
      content: "";
      width: 4px;
      height: 4px;
      background: #000;
      border-radius: 3px;
      position: absolute;
      left: 8.5px;
      top: 8px;
    }
  }
  .menu_box .icon-ic_jindu1 {
    font-size: 18px;
  }
  .menu_box img{
    width: 16px;
    height: 16px;
  }
  .menu_box .menu_line {
    width: 1px;
    height: 15px;
    background: #B3B3B3;
    margin: 0 10px;
  }
  .dialog_nav {
    position: absolute;
    left: 15px;
    top: 50px;
    width: 110px;
    background: #FFFFFF;
    box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.08);
    z-index: 310;
    border-radius: 7px;
    &::after {
      content: '';
      width: 0;
      height: 0;
      position: absolute;
      left: 0;
      right: 0;
      margin: auto;
      top: -9px;
      border-bottom: 13px solid #FFFFFF;
      border-left: 13px solid transparent;
      border-right: 13px solid transparent;
    }
    .dialog_nav_item {
      width: 100%;
      height: 38px;
      line-height: 38px;
      padding: 0 10px 0;
      box-sizing: border-box;
      font-size: 13px;
      color: #333;
      position: relative;
      &::after {
        content: '';
        position: absolute;
        width: 86px;
        height: 0.5px;
        background: #EEEEEE;
        bottom: 0;
        right: 0;
      }
      &:last-child {
        &::after {
          display: none;
        }
      }
      .iconfont {
        font-size: 16px;
      }
      .pl-20 {
        padding-left: 10px;
      }
    }
  }
  .share-icon{
    margin-right: 8px;
    width: 29px;
    height: 29px;
    background: rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 100%;
    img{
      width: 16px;
      height: 16px;
    }
  }
  .dot-line{
    width: 100%;
    position: absolute;
    left: 0;
    bottom: 27px;
    .item{
      width: 66px;
      height: 2px;
      border-radius: 2px;
      background: #fff;
      & ~ .item{
        margin-left: 4px;
      }
    }
  }
  .info-box{
    background: linear-gradient(180deg, #ffffff 0%, #ffffff 54%, rgba(255, 255, 255, 0) 100%);
    margin-top: -17px;
    border-radius: 20px 20px 0 0;
  }
  .svip{
    font-size: 9px;
    color: #333;
    margin-left: 10px;
    .svip-img {
      width: 38px;
      height: 16px;
      margin-left: 3px;
    }
  }
  .text-integral {
    color: #FF9000;
    background: #FFF4E6;
    border-radius: 10px;
    font-size: 13px;
    padding: 0 10px;
    height: 20px;
    line-height: 20px;
  }
  .share-item {
    margin-left: 10px;
    .name {
      color: #666;
      font-size: 10px;
    }
    .iconfont-h5 {
      font-size: 19px;
      margin-bottom: 3px;
      // color: #282828;
    }
  }
  .pic-list{
    width: 100%;
    padding-top: 18px;
    .pic{
      width: 40px;
      height: 40px;
      border-radius: 8px;
    }
  }
  .tag{
    display: inline-flex;
    justify-content: center;
    align-items: center;
    height: 22px;
    padding: 0 8px;
    font-size: 11px;
    color: #e93323;
    background: rgba(233, 51, 35, 0.1);
    margin-right: 8px;
    border-radius: 4px;
  }
  .pro-title{
    font-size: 15px;
    font-weight: 500;
    line-height: 21px;
    margin-top: 10px;
  }
  .fs-11{
    font-size: 11px;
  }
  .mt-13{
    margin-top: 13px;
  }
  .red{
    color: #e93323;
  }
  .lh-15{
    line-height: 15px;
  }
</style>

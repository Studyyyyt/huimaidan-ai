<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="选择优惠券"
      :visible.sync="dialogVisible"
      width="710px"
    >
     <div class="main-section">
        <div class="main-bd">
          <div class="list acea-row">
            <div v-for="(item, index) in couponList" :key="index" class="item acea-row row-between" :class="item.coupon.send_type == 5 ? 'svip' : ''" @click="changeCoupon(item,index)">
              <div class="item-left acea-row row-between row-middle" :class="{'disabled' : item.disabled}">
                <div class="text-cont">
                  <div class="money">
                    ￥<span class="num" :title="item.coupon_price">{{ item.coupon_price.length > 5 ? parseInt(item.coupon_price) : parseFloat(item.coupon_price) }}</span>
                  </div>
                  <div class="info">
                    <div>满{{ parseFloat(item.use_min_price)}}可用</div>
                  </div>
                </div>
                <div class="text">
                  <div class="name line1">{{ item.coupon_title }}</div>
                  <template v-if="item.mer_id == 0">
                    <div class="label">平台券</div>
                  </template>
                  <template v-else>
                    <div v-if="item.coupon && item.coupon.type === 0" class="label">店铺券</div>
                    <div v-else-if="item.coupon && item.coupon.type === 11" class="label">品类券</div>
                    <div v-else-if="item.coupon && item.coupon.type === 1" class="label">商品券</div>
                    <div v-else-if="item.coupon && item.coupon.type === 10" class="label">通用券</div>
                    <div v-else-if="item.coupon && item.coupon.type === 12" class="label">跨店券</div>
                  </template>
                  <div class="time">
                    {{ (item.start_time.split(' ')[0]).replace(/-/g,'.') }}-{{ (item.end_time.split(' ')[0]).replace(/-/g,'.') }}
                  </div>
                </div>
              </div>
              <div class="btn acea-row row-middle" :class="{'disabled' : item.disabled}" v-if="!item.checked">{{item.disabled ? '不可使用' : '立即使用'}}</div>
              <div class="btn acea-row row-middle selected" :class="{'disabled' : item.disabled}" v-else>{{item.disabled ? '不可使用' : '已选择'}}</div>
            </div>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
export default {
  name: "seleceCoupon",
  props:{
    couponList:{
      type:Array,
      default: () => []
    },

  },
  components: {
   
  },
  data() {
    return { 
      dialogVisible: false,
      
    }
  },
  
  created() {
    
  },
  mounted() {},
  methods: {
    open(){
      this.dialogVisible = true
    },
    onClose(){
      this.dialogVisible = false
    },
    changeCoupon(item,index) {  
      if(item.disabled) return;
			this.$emit('changeCoupon', item,index)
    }
  },
};
</script>
<style lang="scss" scoped>
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb{
  -webkit-box-shadow: inset 0 0 6px transparent;;
}
::-webkit-scrollbar {
  width: 0px!important; /*对垂直流动条有效*/
}
.main-section {
  height: 455px;
  overflow-x: hidden;
  .item {
    width: 330px;
    height: 110px;
    margin-right: 20px;
    margin-bottom: 20px;   
    border-radius: 8px;
    position: relative;
    cursor: pointer;
    &::before,&::after {
      content: "";
      display: block;
      width: 12px;
      height: 12px;
      background: #fff;
      border-radius: 100%;
      position: absolute;
      left: 100px;
      z-index: 20;
    }
    &::before{
      top: -6px;
    }
    &::after{
      bottom: -6px;
    }
    &:nth-child(2n) {
      margin-right: 0;
    }
    .item-left {
      border-radius: 8px;
      background: #F2F6FF;
      width: 285px;
      position: relative;
      z-index: 10;
      &.disabled {
        background: #eeeeee;
        cursor: not-allowed;
        .label {
          color: #909399;
          border-color: #909399;
        }
        .money,.info,.name,.time {
          color: #909399;
        }
      }
    }
    .text {
      flex: 1;
      padding-left: 14px;
       .label {
        width: 44px;
        height: 17px;
        border-radius: 9px;
        border: 1px solid var(--prev-color-primary);
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--prev-color-primary);
      }
    }
    .text-cont {
      width: 106px;
      font-size: 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-items: center;
      &::after{
        content: "";
        height: 100px;
        border-left: 1px dashed #eeeeee;
        position: absolute;
        left: 106px;
        top:  2.5px;
      }
    }
    .money {
      font-size: 16px;
      color: #FF7700;
      .num {
        font-weight: bold;
        font-size: 34px;
      }
    }
    .info {
      color: #303133;
      margin-top: 5px;
      font-size: 13px;
    }
    .name {
      margin-bottom: 6px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
      font-weight: 600;
      color: #000;
      width: 107px;
    }
    .time {
      margin-top: 14px;
      font-size: 12px;
      color: #969696;
    }
    .btn {
      width: 55px;
      height: 110px;
      padding-right: 10px;
      padding-left: 25px;
      font-size: 14px;
      color: var(--prev-color-primary);
      background: #DBE5FF;
      border-radius: 0 8px 8px 8px;
      position: absolute;
      top: 0;
      right: 0;
      z-index: 1;
      &.selected {
        color: #fff;
        background: var(--prev-color-primary);
      }
      &.disabled {
        background: #CCCCCC;
        color: #909399;
        cursor: not-allowed;
      }
    }
  }
}
</style>

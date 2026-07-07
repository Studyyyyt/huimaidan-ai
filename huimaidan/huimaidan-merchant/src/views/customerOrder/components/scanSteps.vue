<template>
  <el-dialog
    v-if="dialogVisible"
    title="选择扫码方式"
    :visible.sync="dialogVisible"
    :close-on-click-modal="false"
    @close="onClose"
    width="540px"
  >
    <div class="scanMain">
      <div class="scan-title">
        <div class="acea-row row-center row-middle">
          <img class="icon" src="@/assets/images/gold_coin.png" alt="">
          <span class="text">应付金额(元)</span>
        </div>
        <div class="pay_price">
          <span>￥</span>{{payPrice||priceInfo.totalPaymentPrice}}
        </div>
      </div>
      <div v-if="!v3Wechat || (v3Wechat && payType != 'weixinQr')" class="type_switch">
        <span class="switch_btn" :class="{on : type==1}" @click="changeType(1)">扫码枪</span>
        <span class="switch_btn" :class="{on : type==2}" @click="changeType(2)">二维码</span>
      </div>
      <template v-if="type == 1">
        <div class="scan_input">
          <el-input v-model="codeText" ref="myInput" placeholder="请点击输入框聚焦扫码或输入编码号" @keyup.enter.native="getPayData(1)"></el-input>
        </div>
        <div class="scan-steps">
          <img src="@/assets/images/scan_steps.png" alt="">
        </div>
      </template>
      <template v-else-if="type==2">
        <div class="step-erweima">
          <vue-qr class="erweima" :text="qrCode" :size="140" />
          <div class="text acea-row row-center-wrapper">
            <div class="time-text">剩余支付时间</div> 
            <countDown :is-day="false" :is-hour="false" :tip-text="' '" :day-text="' '" :hour-text="' '" :minute-text="'分'" :second-text="'秒'" :datatime="datatime"></countDown>
          </div>
        </div>
      </template>
    </div>
  </el-dialog>
</template>
<script>
import { orderPayApi, orderPayStatus } from '@/api/order'
import countDown from "@/components/countDown";
import VueQr from "vue-qr";
export default {
  name: "scanSteps",
  props:{
    orderId:{
      type: Number,
      default:0
    },
    userInfo:{
      type: Object,
      default:{}
    },
    payType:{
      type: String,
      default:"weixinQr"
    },
    priceInfo: {
      type: Object,
      default:()=>({})
    },
    payPrice: {
      type: String,
      default: '0'
    },

  },
  components: { VueQr, countDown },
  watch: {
    type(nVal, oVal) {
      if(nVal == 2) {
        this.startPollOrderStatus();
      }else {
        clearInterval(this.timer)
        this.timer = null;
      }
    },
    orderId(nVal, oVal) {
      if(nVal && this.v3Wechat) {
        this.getPayData(this.type)
      }
    },
    dialogVisible(nVal, oVal) {
      if(!nVal) {
        clearInterval(this.timer)
        this.timer = null;
      }
    }
	},
  data() {
    return { 
      dialogVisible: false,
      type: 1,
      qrCode: "",
      codeText: "",
      timer: null,
      datatime: 0,
      v3Wechat: false
    }
  },
  created() {},
  mounted() {

  },
  methods: {
    startPollOrderStatus() {
      if (this.timer !== null) return;
      this.timer = setInterval(()=>{
        this.paymentStatus();
      },1000)
    },
    open(v3Wechat) {
      let that = this
      this.v3Wechat = v3Wechat
      that.dialogVisible = true
      that.codeText = ""
      this.type = (v3Wechat && this.payType == 'weixinQr') ? 2 : 1
      if(!v3Wechat){ 
        setTimeout(() => {
          that.$refs.myInput.focus();
        }, 500);
      }
    },
    onClose(){
      this.dialogVisible = false
    },
    changeType(type) {
      this.type = type
      if(type==2)this.getPayData(type) 
    },
    getPayData(type){
      let pay_type = 
      (type == 1 && this.payType == 'alipayQr') ? 'alipayBarCode' :
      (type == 1 && this.payType == 'weixinQr') ? 'weixinBarCode' : this.payType 
      let data = {
        uid: this.userInfo.uid || 0,
        pay_type: pay_type,
        phone: this.userInfo.phone,
      }
      if(type == 1)data.auth_code = this.codeText
      orderPayApi(this.orderId,data)
        .then((res) => {
          this.qrCode = res.data.result.config
          this.datatime = res.data.result.time_expire
          if(type == 1){
            this.$emit('paySuccess')
            this.dialogVisible= false;
          } 
        })
        .catch((err) => {
          if(err.data.status == 400){
            if (err.data.data === "需要用户输入支付密码") {
              this.startPollOrderStatus();
            }
            this.$message.error(err.data.data || err.data.message);
          }else{
            this.$message.error(err.message)
          }
        });
    },
    /**查询微信，支付宝扫支付状态 */
    paymentStatus() {
      let data = {
        uid: this.userInfo.uid || 0,
        pay_type: this.payType,
      }
      orderPayStatus(this.orderId,data)
      .then((res) => {
          if(res.data.paid == 1){
            this.$emit('paySuccess');
            this.dialogVisible= false;
            clearInterval(this.timer)
            this.timer = null;
          }
        })
        .catch((err) => {
          this.$message.error(err.message);
          clearInterval(this.timer)
          this.timer = null;
        });
    }
  },
};
</script>
<style lang="scss" scoped>
  .scan-title {
    display: flex;
    flex-direction: column;
    align-items: center;
    .icon {
      width: 19px;
      height: 19px;
    }
    .text {
      color: #909399;
    }
    .pay_price {
      font-size: 32px;
      color: #303133;
      font-weight: 600;
      margin-top: 15px;
      span {
        font-size: 18px;
      }
    }
  }
  .type_switch {
    display: flex;
    justify-content: center;
    margin-top: 30px;
    .switch_btn {
      display: inline-block;
      width: 140px;
      height: 40px;
      line-height: 40px;
      text-align: center;
      color: #606266;
      font-size: 15px;
      border: 1px solid #DDDDDD;
      cursor: pointer;
      &.on {
        background: var(--prev-color-primary);
        color: #fff;
        border-color: var(--prev-color-primary);
      }
      &:first-child {
        border-radius: 20px 0 0 20px;
      }
      &:last-child {
        border-radius: 0 20px 20px 0;
      }
    }
  }
  .scan_input {
    margin: 24px auto 0;
    width: 460px;
    ::v-deep .el-input__inner {
      border-color: var(--prev-color-primary);
      border-radius: 4px 4px 0 0;
    }
  }
  .scan-steps {
    width: 460px;
    height: 200px;
    margin: 0 auto;
    text-align: center;
    border: 1px dashed #DDDDDD;
    border-top: none;
    img{
      width: 408px;
      height: 124px;
      margin-top: 38px;
    }
  }
  .step-erweima {
    width: 460px;
    height: 249px;
    margin: 30px auto 0;
    border: 1px dashed #DDDDDD;
    border-radius: 0 0 4px 4px;
    text-align: center;
    .erweima {
      margin-top: 40px;
    }
    .text{
      margin-top: 10px;
      color: #999999;
    }
    .time-text {
      margin-right: 6px;
    }
  }
</style>

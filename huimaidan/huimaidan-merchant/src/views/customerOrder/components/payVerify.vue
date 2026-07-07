<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="用户验证"
      :visible.sync="dialogVisible"
      width="540px"
    >
      <el-form class="form" ref="ruleForm" :model="payInfo" label-width="80px" size="small">
        <el-form-item label="应付金额：">
          <el-input v-model="payInfo.price" disabled class="width100"><span slot="suffix">元</span></el-input>  
        </el-form-item>
        <el-form-item label="手机号：">
          <el-input v-model="payInfo.phone" disabled class="width100" /> 
        </el-form-item>
        <el-form-item label="验证码：">
         <div class="captcha">
            <el-input
              ref="username"
              v-model="code"
              class="width100"
              placeholder="验证码"
              name="username"
              type="text"
              tabindex="3"
              autocomplete="on"
            />
            <el-button type="text" class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''"
                @click="getCode">
              {{ text }}
            </el-button>
          </div>
        </el-form-item>    
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false" size="small">取消</el-button>
        <el-button type="primary" @click="handleSubmit" size="small">确定</el-button>
      </span>    
    </el-dialog>
  </div>
</template>
<script>
import { verifyCode, orderPayApi } from '@/api/order'
export default {
  name: "ChangePrice",
  props:{
    payInfo:{
      type: Object,
      default:{}
    },
    orderId:{
      type: Number,
      default:0
    },
    userInfo:{
      type: Object,
      default:{}
    },
  },
  components: {},
  data() {
    return { 
      dialogVisible: false,
      disabled: false,
      text: "获取验证码",
      code: ""
     
    }
  },
  created() {
    
  },
  mounted() {},
  methods: {
    onClose(){
      this.dialogVisible = false
    },
    async getCode() {
      let that = this;
      await verifyCode({
        phone: that.payInfo.phone,
      }).then(res=>{
        that.$message.success(res.message);
        that.sendCode();
      }).catch(err => {
        that.sendCode();
        that.$message.error(err);
      });
    },
    sendCode() {
      if (this.disabled) return;
      this.disabled = true;
      let n = 60;
      this.text = "剩余 " + n + "s";
      const run = setInterval(() => {
        n = n - 1;
        if (n < 0) {
          clearInterval(run);
        }
        this.text = "剩余 " + n + "s";
        if (this.text < "剩余 " + 0 + "s") {
          this.disabled = false;
          this.text = "重新获取";
        }
      }, 1000);
    },
    handleSubmit() {
      let data = {
        uid: this.userInfo.uid || 0,
        pay_type: 'balance',
        phone: this.userInfo.phone,
        sms_code: this.code
      }
      orderPayApi(this.orderId,data)
        .then((res) => {
          this.$message.success(res.message);
          this.dialogVisible = false;
          this.code = "";
          this.$emit('paySuccess')
        })
        .catch((err) => {
          if(err.data.status == 400){
            this.$message.error(err.data.data || err.data.message);
          }else{
            this.$message.error(err.message)
          }
        });
    }
  },
};
</script>
<style lang="scss" scoped>
  .form {
    padding: 0 10px;
  }
  .captcha {
    position: relative;
    ::v-deep .el-input__inner {
      padding-right: 90px;
    }
    .code {
      position: absolute;
      right: 14px;
      top: 0;
      font-size: 13px;
      color: var(--prev-color-primary);
    }
  }
</style>

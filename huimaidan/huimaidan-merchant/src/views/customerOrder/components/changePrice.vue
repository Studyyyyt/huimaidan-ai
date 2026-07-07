<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      :title="changeType == 'goods' ? '商品改价' : '整单改价'"
      :visible.sync="dialogVisible"
      width="540px"
    >
      <el-form class="form" ref="ruleForm" :model="formValidate" label-width="80px" size="small">
        <el-form-item label="商品售价：">
          <el-input v-model="formValidate.old_price" disabled class="width100"><span slot="suffix">元</span></el-input> 
        </el-form-item>
        <el-form-item :label="changeType == 'goods' ? '商品改价：' : '订单改价：'">
          <el-radio-group v-if="changeType == 'goods'" v-model="formValidate.type">
            <el-radio :label="0">一口价</el-radio>
            <el-radio :label="1">立减</el-radio>
            <el-radio :label="2">折扣</el-radio>
          </el-radio-group>
          <el-input v-if="changeType !== 'goods'" v-model="formValidate.new_price" placeholder="请输入改后价格"><span slot="suffix">元</span></el-input>
          <div v-if="changeType == 'goods'" class="mt20">
            <el-input v-if="formValidate.type==0" v-model="formValidate.new_price" placeholder="请输入改后价格"><span slot="suffix">元</span></el-input>
            <el-input v-else-if="formValidate.type==1" v-model="formValidate.reduce_price" placeholder="请输入立减金额"><span slot="suffix">元</span></el-input>
            <el-input v-else-if="formValidate.type==2" v-model="formValidate.discount_rate" placeholder="请输入折扣比例"><span slot="suffix">%</span></el-input>
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
import { orderCartUpdatePrice, orderChangePrice } from '@/api/order';
export default {
  name: "ChangePrice",
  components: {},
  props:{
    cartIds: {
      type: Array,
      default:()=>([])
    },
    userInfo: {
      type: Object,
      default:()=>({})
    }
  },
  data() {
    return { 
      dialogVisible: false,
      cart_id: "",
      changeType: "",
      formValidate: {
        type: 0,
        old_price: "",
        new_price: "",
        reduce_price: "",
        discount_rate: ""
      },
    }
  },
  created() {
    
  },
  mounted() {},
  methods: {
    onClose(){
      this.dialogVisible = false
    },
    initData(item,type) {
      this.dialogVisible = true;
      this.formValidate.type = 0
      this.changeType = type;
      if(type == 'goods') {
        this.formValidate.old_price = item.productAttr.price || item.product.price;
        this.cart_id = item.cart_id;
      }else{
        this.formValidate.old_price = item.totalOriginalPrice;
      }
      this.formValidate.new_price = ""
      this.formValidate.reduce_price = ""
      this.formValidate.discount_rate = ""
    },
    handleSubmit() {
      let type = this.formValidate.type
      if(type == 0 && !this.formValidate.new_price){
        return this.$message.error("请输入改后价格")
      }else if(type == 1 && !this.formValidate.reduce_price) {
        return this.$message.error("请输入立减金额")
      }else if(type == 2 && !this.formValidate.discount_rate) {
        return this.$message.error("请输入折扣比例")
      }
      if(this.changeType == 'goods'){
        orderCartUpdatePrice(this.cart_id,this.formValidate).then(res => {
          this.$message.success(res.message)
          this.$emit("getCartList")
          this.dialogVisible = false
        })
        .catch(res => {
          this.$message.error(res.message)
        })
      }else{
        let data = {
          old_pay_price: this.formValidate.old_price,
          new_pay_price: this.formValidate.new_price,
          change_fee_type: this.formValidate.type,
          uid: this.userInfo.uid,
          cart_ids: this.cartIds
        }
         orderChangePrice(data).then(res => {
          this.$message.success(res.message)
          this.$emit("getCartList")
          this.dialogVisible = false
        })
        .catch(res => {
          this.$message.error(res.message)
        })
      }
    }
  },
};
</script>
<style lang="scss" scoped>
  .form {
    padding: 0 10px;
  }
</style>

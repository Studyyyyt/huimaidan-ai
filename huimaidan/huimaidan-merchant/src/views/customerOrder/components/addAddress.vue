<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="添加收货地址"
      :visible.sync="dialogVisible"
      @click.native="isShowSelect=false"
      @close="onClose"
      width="650px"
    >
      <el-form class="form" ref="ruleForm" :model="ruleForm" :rules="rules" label-width="100px" size="small">
        <el-form-item label="姓名：" prop="real_name">
          <el-input v-model="ruleForm.real_name" placeholder="请输入收货人姓名" class="width100" /> 
        </el-form-item>
        <el-form-item label="联系电话：" prop="phone">
          <el-input v-model="ruleForm.phone" placeholder="请输入收货人手机号" class="width100" /> 
        </el-form-item>
        <el-form-item label="所在地区：" required>
          <div class="input-item text-wrapper">
            <p @click.stop="bindAdd(false)" v-if="!cityData.province.name">请选择省/市/区/街道</p>
              <p @click.stop="bindAdd(true)" v-if="cityData.province.name" style="color: #333">
                <span v-if="cityData.province.name">{{cityData.province.name}}</span>
                <span v-if="cityData.city.name">/{{cityData.city.name}}</span>
                <span v-if="cityData.district.name">/{{cityData.district.name}}</span>
                <span v-if="cityData.street.name">/{{cityData.street.name}}</span>
              </p>
              <div class="select-wrapper" v-if="isShowSelect">
                <div class="title-box" v-if="!cityData.province.id">选择省/自治区</div>
                <div class="title-box" v-if="cityData.step == 2">
                  <span>{{cityData.province.name}}</span>选择市
                </div>
                <div class="title-box" v-if="cityData.step == 3">
                  <span>{{cityData.district.name}}</span>
                  <span>{{cityData.city.name}}</span>选择区县
                </div>
                <div class="title-box" v-if="cityData.step == 4 && !stepStop">
                  <span>{{cityData.city.name}}</span>
                  <span>{{cityData.district.name}}</span>请选择配送区域
                </div>
                <div class="label-txt">
                  <span v-for="(item,index) in cityData.list" :key="index" @click.stop="bindCity(item)" :class="{on:cityData.pid == item.id}">{{item.name}}</span>
                </div>
              </div>
          </div>
        </el-form-item>
        <el-form-item label="详细地址：" prop="detail">
          <el-input v-model="ruleForm.detail" placeholder="请输入收货人详细地址" class="width100" /> 
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false;isShowSelect = false" size="small">取消</el-button>
        <el-button type="primary" @click="handleSubmit('ruleForm')" size="small">确定</el-button>
      </span>    
    </el-dialog>
  </div>
</template>
<script>
import { getCityListApi, orderAddAddress } from '@/api/order';
export default {
  name: "AddAddress",
  props:{
    uid:{
      type: Number | String,
      default: ''
    },
    touristId: {
      type: String,
      default: ''
    }
  },
  components: {
   
  },
  data() {
    return { 
      dialogVisible: false,
      ruleForm: {},
      rules: {
        real_name: [
          { required: true, message: '请输入收货人姓名', trigger: 'blur' }
        ],
        phone: [
          { required: true, message: '请输入收货人手机号', trigger: 'blur' },
          { pattern: /^1[3456789]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
        ],
        cityData: [
          { required: true, message: '请选择收货地址', trigger: 'change' }
        ],
        detail: [
          { required: true, message: '请输入收货人详细地址', trigger: 'blur' }
        ]
      },
      cityData: {
        pid:0,
        step:1,
        list:[],
        province:{},
        city:{},
        district: {},
        street:{}
      },
      isShowSelect:false,
      stepStop: false,
      selectedArr: [],
      region: false
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
      this.resetData()
    },
    bindAdd(isReset){
      if(isReset){this.cityData.step = 1;this.stepStop = false}
      this.isShowSelect = !this.isShowSelect
      if(this.cityData.step == 4 || this.stepStop){
        return
      }else{
        this.cityData.city = {}
        this.cityData.district ={}
        this.cityData.province ={}
        this.cityData.street ={}
        this.getCityList(0,null)
      }
    },
    getCityList(pid,fun){
      this.cityData.list = []
      getCityListApi(pid).then(res => {
        this.cityData.list= res.data
        fun && fun()
      })
      .catch(res => {
        this.$message.error(res.message)
      })
    },
    // 选择城市
    bindCity(item){
      let that = this;
      that.region = false
      if(that.cityData.step == 4){
        that.cityData.street = item
        that.ruleForm.street = item.name
        that.ruleForm.street_id = item.id
        that.selectedArr.push(item);
        that.isShowSelect = false
      }else {
        if(that.cityData.step == 1){
          that.cityData.province = item
          that.ruleForm.province = item.name
          that.ruleForm.province_id = item.id
          that.getCityList(item.id,null)
          that.selectedArr = [item];
          that.cityData.step++
          return
        }else{
          if(that.cityData.step == 2){
            that.cityData.city = item
            that.ruleForm.city = item.name
            that.ruleForm.city_id = item.id
            that.getCityList(item.id,null)
            that.cityData.step++
            that.selectedArr.push(item);
            return
          }
          if(that.cityData.step == 3){
            that.cityData.district = item
            that.ruleForm.district = item.name
            that.ruleForm.district_id = item.id
            that.selectedArr.push(item);
            that.cityData.step++
            that.getCityList(item.id,function(){
              if(that.cityData.list && that.cityData.list.length){
                that.stepStop = false
                return
              }else{
                that.stepStop = true
                that.isShowSelect = false
                return
              }
            })
          }
        }
      }
      that.region = true
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.ruleForm.uid = this.uid
          if(!this.region) return this.$message.error('请选择完整的所在地区');
          if(this.uid == 0)this.ruleForm.tourist_unique_key = this.touristId
          orderAddAddress(this.ruleForm)
            .then((res) => {
              this.$message.success('添加成功');
              this.$emit('getAddressList');
              this.dialogVisible = false;
              this.region = false;
              this.resetData();   
            })
            .catch((err) => {
              this.$message.error(err.message);
            });
        }
      })
    },
    resetData() {
      this.$refs.ruleForm.resetFields();
      this.cityData = {
        pid:0,
        step:1,
        list:[],
        province:{},
        city:{},
        district: {},
        street:{}
      }
      this.ruleForm={}
    }
  },
};
</script>
<style lang="scss" scoped>
.form {
  height: 300px;
}
.text-wrapper{
  position: relative;
  height: 32px;
  line-height: 32px;
  border: 1px solid #DCDFE6;
  padding: 0 15px;
  box-sizing: border-box;
  border-radius: 4px;
  color: #cfcfcf;
  .select-wrapper{
    z-index: 10;
    position: absolute;
    left: 1px;
    top: 30px;
    width: 100%;
    padding:0 15px;
    background: #fff;
    border: 1px solid var(--prev-color-primary);
    border-radius: 4px;
    line-height: 2;
    .title-box{
      height: 40px;
      line-height: 40px;
      border-bottom: 1px solid #EFEFEF;
      color: var(--prev-color-primary);
      font-size: 14px;
      span{
        margin-right: 8px;
        color: #666666;
      }
    }
    .label-txt{
      margin: 8px 0 18px;
      color: #666666;
      font-size: 14px;
      span{
        margin-right: 10px;
        cursor: pointer;
        &.on{
          color: var(--prev-color-primary);
        }
      }
    }
  }
}
</style>

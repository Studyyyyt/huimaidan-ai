<template>
  <div>
      <el-form
        ref="basicForm"
        :model="basicForm"
        :rules="basicRules"
        label-width="160px">
        <!-- 是否开启同城配送 -->
        <!-- <el-form-item label="同城配送：" prop="mer_delivery_status">
          <el-switch
              v-model="basicForm.mer_delivery_status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              :width="60"/>
          <br>
          <span class="remarks">开启后，用户下单可选择同城配送。</span>
        </el-form-item> -->
        <!-- 配送方式选择 -->
        <el-form-item label="配送方式：" prop="mer_delivery_type">
          <el-checkbox-group
            v-model="basicForm.mer_delivery_type">
            <el-checkbox :label="0">商家配送</el-checkbox>
            <el-checkbox :label="2">UU</el-checkbox>
            <el-checkbox :label="1">达达</el-checkbox>
          </el-checkbox-group>
          <span class="remarks">用户下单选择同城配送，配送费用统一根据配送模板进行计算。</span>
        </el-form-item>
        <!-- 是否开启配送员抢单 -->
        <el-form-item
          label="配送员抢单："
          prop="mer_delivery_order_status"
          v-show="basicForm.mer_delivery_type.includes(0)">
          <el-switch
              v-model="basicForm.mer_delivery_order_status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              :width="60"
          />
          <br>
          <span class="remarks">开启后，用户下单后配送员可以抢单进行配送；关闭则只能商家指派配送员。</span>
        </el-form-item>
        <!-- AppKey(UU跑腿) -->
        <el-form-item
          v-if="isShowUU"
          label="AppKey(UU跑腿)："
          prop="uupt_appkey">
          <el-input v-model="basicForm.uupt_appkey" class="width-500"></el-input>
        </el-form-item>
        <!-- AppSercret(UU跑腿) -->
        <el-form-item
          v-if="isShowUU"
          label="AppSercret(UU跑腿)："
          prop="uupt_app_id">
          <el-input v-model="basicForm.uupt_app_id" class="width-500"></el-input>
        </el-form-item>
        <!-- OpenId(UU跑腿) -->
        <el-form-item
          v-if="isShowUU"
          label="OpenId(UU跑腿)："
          prop="uupt_open_id">
          <el-input v-model="basicForm.uupt_open_id" class="width-500"></el-input>
        </el-form-item>
        <!-- AppKey(达达) -->
        <el-form-item
          v-if="isShowDaDa"
          label="AppKey(达达)："
          prop="dada_app_key">
          <el-input v-model="basicForm.dada_app_key" class="width-500"></el-input>
        </el-form-item>
        <!-- AppId(达达) -->
        <el-form-item
          v-if="isShowDaDa"
          label="AppId(达达)："
          prop="dada_app_sercret">
          <el-input v-model="basicForm.dada_app_sercret" class="width-500"></el-input>
        </el-form-item>
        <!-- 商户ID(达达) -->
        <el-form-item
          v-if="isShowDaDa"
          label="商户ID(达达)："
          prop="dada_source_id">
          <el-input v-model="basicForm.dada_source_id" class="width-500"></el-input>
        </el-form-item>
      </el-form>
  </div>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
export default {
  name: 'BasicForm',
  props: {
    basicForm: {
      type: Object,
      default: null
    }
  },
  computed: {
    // 是否展示UU配送数据
    isShowUU() {
      return this.basicForm.mer_delivery_type.includes(2)
    },
    // 是否展示DaDa配送数据
    isShowDaDa() {
      return this.basicForm.mer_delivery_type.includes(1)
    },
  },
  data() {
    return {
      basicRules: {
        mer_delivery_type: [
          {
            required: true,
            type: "array",
            min: 1,
            message: `请选择配送方式`,
            trigger: 'blur'
          }
        ],
        dada_app_key: [
          {required: true, message: `请输入AppKey(达达)`, trigger: 'blur'}
        ],
        dada_app_sercret: [
          {required: true, message: `请输入AppId(达达)`, trigger: 'blur'}
        ],
        dada_source_id: [
          {required: true, message: `请输入商户ID(达达)`, trigger: 'blur'}
        ],
        uupt_appkey: [
          {required: true, message: `请输入AppKey(UU跑腿)`, trigger: 'blur'}
        ],
        uupt_app_id: [
          {required: true, message: `请输入AppSercret(UU跑腿)`, trigger: 'blur'}
        ],
        uupt_open_id: [
          {required: true, message: `请输入OpenId(UU跑腿)`, trigger: 'blur'}
        ]
      }
    }
  },
  methods: {
    // 提交配送设置
    submit() {
      let isValid = true
      this.$refs.basicForm.validate((valid, errData) => {
        if (valid) {
          isValid = true
        } else {
          for (var item in errData) {
            this.$message({
              type: 'error',
              message: errData[item][0].message
            })
            isValid = false
            break
          }
        }
      })
      return isValid
    }
  }
}
</script>

<style scoped lang="scss">
  @import '@/styles/form.scss';
  .title-box {
    height: 18px;
    display: flex;
    align-items: center;
    margin-bottom: 22px;
    .title-text {
      display: flex;
      align-items: center;
    }
    .title-text::before {
      display:block;
      content: '';
      width: 2px;
      height: 14px;
      background-color: #4073FA;
      margin-right: 5px;
    }
  }
  .remarks {
    color: #A4A4A4;
    font-size: 12px;
  }
  .mb-22 {
    margin-bottom: 22px;
  }
  .width-500 {
    width: 500px;
  }
</style>

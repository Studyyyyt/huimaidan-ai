<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="退货审核"
      :visible.sync="dialogVisible"
      width="600px"
    >
      <div class="container">
        <el-form
          ref="saveForm"
          :model="forms"
          label-width="110px"
          size="small"
          :rules="ruleValidate"
          @submit.native.prevent
        >
          <el-form-item label="退货物流公司：">
            <div>{{returnData.delivery_name}}</div>
          </el-form-item>
          <el-form-item label="退货快递单号：">
            <div>{{returnData.delivery_id}}</div>
          </el-form-item>
          <el-form-item label="审核结果：">
            <el-radio-group v-model="forms.status" prop="status">
              <el-radio :label="3">同意</el-radio>
              <el-radio :label="-1">拒绝</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item v-if="forms.status == -1" label="填写原因：" prop="fail_message">
            <el-input v-model="forms.fail_message" placeholder="请输入拒绝原因，最多可输入50字" maxlength="50" type="textarea" :autosize="{minRows: 3,maxRows: 5}" />
          </el-form-item>
        </el-form>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false" size="small">取消</el-button>
        <el-button type="primary" @click="submitForm('saveForm')" :loading="btnloading" size="small">确定</el-button>
      </span>
    </el-dialog>

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
import { confirmReceiptApi  } from '@/api/order'

export default {
  name: 'ReturnReview',
  data() {
    return {
      dialogVisible: false,
      btnloading: false,
      forms: {
        status: 3,
        fail_message: ""
      },
      ruleValidate: {
        status: [
          { required: true, message: '请选择审核结果', trigger: 'blur' }
        ],
        fail_message: [
          { required: true, message: '请输入拒绝原因', trigger: 'blur' }
        ],
      }

    }
  },
  props: {
    returnData: {
      type: Object,
      default: {}
    },

  },
  methods: {
    submitForm(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          confirmReceiptApi(this.returnData.refund_order_id,this.forms).then((res) => {
            this.$message.success(res.message)
            this.dialogVisible = false
            this.$emit('getList')
          })
        } else {
          return false;
        }
      })
    },


  }
}
</script>

<style scoped lang="scss">

</style>

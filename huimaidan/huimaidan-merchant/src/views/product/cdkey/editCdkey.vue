<template>
  <el-dialog
    :visible.sync="cdkeyShow"
    title="编辑卡密"
    width="800px"
    :close-on-click-modal="true"
    :before-close="handleClose"
    class="dialog-bottom"
  >
    <div class="carMywrapper">
      <div class="type-radio">
        <el-form label-width="60px" :model="carMyValidateForm" ref="carMyValidateForm" size="small" :inline="true">
          <el-form-item
            label="卡号"
            prop="pwd"
            :rules="{
              required: true,
              message: '卡号不能为空',
              trigger: 'blur',
            }"
          >
            <el-input
              v-model="carMyValidateForm.key"
              class="selWidth"
              placeholder="请输入卡号"
            ></el-input>
          </el-form-item>
          <el-form-item
            label="卡密"
            prop="key"
            :rules="{
              required: true,
              message: '卡密不能为空',
              trigger: 'blur',
            }"
          >
            <el-input
              v-model="carMyValidateForm.pwd"
              class="selWidth"
              placeholder="请输入卡密"
            ></el-input>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <div slot="footer" class="dialog-footer">
      <el-button class="btns" size="small" @click="handleClose">取消</el-button>
      <el-button
        :loading="btnloading"
        type="primary"
        class="btns"
        size="small"
        @click="submitForm('carMyValidateForm')"
        >保存</el-button
      >
    </div>
  </el-dialog>
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
import { cardSecretUpdateApi } from '@/api/product';
import { Debounce } from '@/utils/validate';
export default {
  name: 'editCdkey',
  props: {
    cdkeyInfo: {
      type: Object,
      default: function () {
        return null;
      },
    },
  },
  data() {
    return {
      cdkeyShow: false,
      carMyValidateForm: {
        pwd: '',
        key: '',
        id: 0,
      },
      btnloading: false,
    };
  },
  watch: {
    cdkeyInfo: {
      handler(nVal, oVal) {
        this.carMyValidateForm = JSON.parse(JSON.stringify(nVal));
        // Object.assign(this.carMyValidateForm, nVal);
      },
      deep: true,
    },
  },
  mounted() {},
  methods: {
    handleClose() {
      this.cdkeyShow = false;
      this.$refs.carMyValidateForm.resetFields();
    },
    //保存
    submitForm: Debounce(function (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.btnloading = true;
          delete this.carMyValidateForm.id
          cardSecretUpdateApi(this.cdkeyInfo.id,this.carMyValidateForm)
            .then((res) => {
              this.$message.success(res.message);
              this.btnloading = false;
              this.handleClose();
              this.$emit('handlerEditSubSuccess');
            })
            .catch((res) => {
              this.$message.error(res.message);
              this.btnloading = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    }),
  },
};
</script>

<style lang="scss" scoped>
.lable {
  text-align: right;
  margin-right: 12px;
}
.width15 {
  width: 150px;
}
::v-deep .el-radio__label {
  font-size: 13px;
}
.carMywrapper {
  .download {
    margin-left: 10px;
  }
  .stock-disk {
    margin: 10px 0 15px 0;
  }
  .scroll-virtual {
    max-height: 320px;
    overflow-y: auto;
    margin-top: 10px;
  }
  .virtual-title {
    width: 50px;
  }
  .deteal-btn {
    color: #5179ea;
    cursor: pointer;
  }
  .add-more {
    margin-top: 20px;
    line-height: 32px;
  }
  .footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 40px;
    button {
      margin-left: 10px;
    }
  }
}
::v-deep .el-input-group--append .el-input__inner {
  padding-right: 0;
}
</style>

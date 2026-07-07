<template>
  <el-dialog
    :visible.sync="cdkeyShow"
    title="添加卡密"
    width="900px"
    :close-on-click-modal="true"
    :before-close="handleClose"
    class="dialog-bottom"
  >
    <div>
      <div class="carMywrapper" id="my-div">
      <div class="type-radio">
        <el-form label-width="85px" :model="carMyValidateForm" ref="carMyValidateForm" size="small" :inline="true">
          <div v-for="(domain, index) in carMyValidateForm.carMyList" :key="index" class="sheet-item">
            <el-form-item
              :label="'卡号' + (index + 1)"
              :prop="'carMyList.' + index + '.key'"
              :rules="{
                required: true,
                message: '卡号不能为空',
                trigger: 'blur',
              }"
            >
              <el-input
                v-model.trim="domain.key"
                class="selWidth mr15"
                placeholder="请输入卡号"
                maxlength="50"
              ></el-input>
            </el-form-item>
            <el-form-item
              :label="'卡密' + (index + 1)"
              :prop="'carMyList.' + index + '.pwd'"
              :rules="{
                required: true,
                message: '卡密不能为空',
                trigger: 'blur',
              }"
            >
              <el-input
                v-model.trim="domain.pwd"
                class="selWidth mr15"
                placeholder="请输入卡密"
                maxlength="50"
              ></el-input>
            </el-form-item>
            <el-button size="small" @click.prevent="removeCard(domain)">删除</el-button>
          </div>
        </el-form>
      </div>
    </div>
    <el-button size="small" @click="handleAddCard" style="margin-left: 85px;">添加行</el-button>
    </div>
    <div slot="footer" class="dialog-footer">
      <el-button size="small" @click="handleClose">取消</el-button>
      <el-button
        :loading="btnloading"
        type="primary"
        size="small"
        :disabled="carMyValidateForm.carMyList.length === 0"
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
import { cardSecretSaveApi } from '@/api/product';
import { Debounce } from '@/utils/validate';
export default {
  name: 'addCarMy',
  props: {
    virtualList: {
      type: Array,
      default: function () {
        return [];
      },
    },
    libraryId: {
      type: Number,
      default: function () {
        return 0;
      },
    },
  },
  data() {
    return {
      cdkeyShow: false,
      header: {}, //请求头部信息
      carMyValidateForm: {
        carMyList: [
          {
            key: '',
            pwd: '',
          },
          {
            key: '',
            pwd: '',
          },
        ],
      },
      btnloading: false,
    };
  },
  mounted() {},
  methods: {
    //取消
    handleClose() {
      this.cdkeyShow = false;
      this.carMyValidateForm = {
        carMyList: [
          {
            key: '',
            pwd: '',
          },
          {
            key: '',
            pwd: '',
          },
        ],
      };
    },
    //添加行
    handleAddCard() {
      this.carMyValidateForm.carMyList.push({
        key: '',
        pwd: '',
      });
      // 使用方法：假设你的div有一个id为"my-div"
      var myDiv = document.getElementById('my-div');
      this.scrollToBottom(myDiv);
    },
    scrollToBottom(element) {
      element.scrollTop = element.scrollHeight - element.clientHeight;
    },
    removeCard(item) {
      var index = this.carMyValidateForm.carMyList.indexOf(item);
      if (index !== -1) {
        this.carMyValidateForm.carMyList.splice(index, 1);
      }
    },
    //保存
    submitForm: Debounce(function (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.btnloading = true;
          cardSecretSaveApi({ csList: this.carMyValidateForm.carMyList, library_id: this.libraryId })
            .then((res) => {
              this.$message.success(res.message);
              this.btnloading = false;
              this.$emit('handlerSubSuccess', this.carMyValidateForm.carMyList);
              this.handleClose();
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
::v-deep.el-form--inline .el-form-item {
  margin-right: 0 !important;
}
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
  max-height: 350px;
  overflow-y: auto;
  .download {
    margin-left: 10px;
  }
  .stock-disk {
    margin: 10px 0 15px 0;
  }
  .scroll-virtual {

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
.btnTop {
  padding-top: 30px;
}
</style>

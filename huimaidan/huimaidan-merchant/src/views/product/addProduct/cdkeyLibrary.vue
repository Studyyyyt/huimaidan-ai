<template>
  <el-dialog
    :visible.sync="cdkeyShow"
    title="设置卡密"
    :append-to-body="true"
    width="610px"
    :close-on-click-modal="false"
  >
    <div class="carMywrapper">
      <div class="type-radio">
        <el-form
          label-width="100px"
          :model="carMyValidateForm"
          ref="carMyValidateForm"
          :inline="true"
        >
          <el-form-item label="关联卡密库：">
            <el-select
              size="small"
              class="pageWidth"
              value-key="id"
              @change="handleChange($event)"
              v-model="carMyValidateForm.cdkeyInfo"
              placeholder="请选择关联卡密库"
              clearable
              filterable
            >
              <el-option
                :value="item"
                v-for="(item, index) in cdkeyLibraryList"
                :key="index"
                :label="item.name"
                :disabled="
                  selectedLibrary.length > 0 &&
                    selectedLibrary.indexOf(item.id) != -1
                "
              ></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <div class="dialog-footer" slot="footer">
      <el-button class="btns" size="small" @click="cdkeyShow = false"
        >取消</el-button
      >
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
import {
  productUnrelatedListApi
} from "@/api/product";
export default {
  name: "cdkeyLibrary",
  props: {
    cdkeyLibraryInfo: {
      type: Object,
      default: function() {
        return null;
      }
    },
    selectedLibrary: {
      type: Array,
      default: function() {
        return [];
      }
    }
  },
  data() {
    return {
      cdkeyShow: false,
      header: {}, //请求头部信息
      carMyValidateForm: { cdkeyInfo: null }, //卡密对象
      btnloading: false,
      cdkeyLibraryList: []
    };
  },
  watch: {
    cdkeyLibraryInfo: {
      handler(nVal, oVal) {
        // this.carMyValidateForm.cdkeyInfo = nVal;
        this.getCdkeyLibraryList();
      },
      deep: true
    }
  },
  mounted() {
    // this.carMyValidateForm.cdkeyInfo = this.cdkeyLibraryInfo;
  },
  methods: {
    //卡密列表
    getCdkeyLibraryList() {
      productUnrelatedListApi().then(res => {
        this.cdkeyLibraryList = res.data.data;
      });
    },
    handleChange(event) {
      this.carMyValidateForm.cdkeyInfo = event;
    },
    //保存
    submitForm(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if (!this.carMyValidateForm.cdkeyInfo) {
            // return this.$message.error('请选择关联卡密库')
            this.$emit("handlerSubSuccess", null);
          } else {
            this.$emit("handlerSubSuccess", this.carMyValidateForm.cdkeyInfo);
          }
          this.cdkeyShow = false;
        } else {
          console.log("error submit!!");
          return false;
        }
      });
    }
  }
};
</script>
<style scoped lang="scss">
.dialog-footer-inner {
  padding-bottom: 20px;
}

::v-deep .el-form-item {
  margin-right: 0 !important;
}
</style>

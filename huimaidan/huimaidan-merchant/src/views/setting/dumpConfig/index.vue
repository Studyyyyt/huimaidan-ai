<template>
  <div class="divBox">
    <el-card class="box-card">
      <el-tabs v-model="headNum">
        <el-tab-pane name="1" label="一号通"/>
        <!-- <el-tab-pane name="2" label="电子面单"/> -->

      </el-tabs>
      <el-form ref="formValidate" :model="formValidate" :rules="rules" label-width="110px" size="small">
        <template v-if="headNum == 1">
          <el-form-item label="APPID：" prop="serve_account">
            <el-input v-model="formValidate.serve_account" class="pageWidth" placeholder="请输入APPID" />
          </el-form-item>
          <el-form-item label="AppSecret：" prop="serve_token">
            <el-input v-model="formValidate.serve_token" type="input" class="pageWidth" placeholder="请输入serve_token" />
          </el-form-item>
        </template>
        <!-- <template v-if="headNum == 2">
          <el-form-item label="快递公司：">
            <el-select
              v-model="formValidate.mer_from_com"
              placeholder="请选择快递公司"
              class="pageWidth"
              @change="getDumpList('')"
              clearable
              filterable
            >
              <el-option
                v-for="item in deliveryList"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="电子面单：">
            <el-select
              v-model="formValidate.mer_config_temp_id"
              placeholder="请选择电子面单"
              clearable
              class="pageWidth"
            >
              <el-option
                v-for="item in dumpList"
                :key="item.temp_id"
                :label="item.title"
                :value="item.temp_id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="云打印机编号：">
            <el-input v-model="formValidate.mer_config_siid" class="pageWidth" placeholder="请填写快递100云打印机编号" />
          </el-form-item>
          <el-form-item label="寄件人姓名：" prop="mer_from_name">
            <el-input v-model="formValidate.mer_from_name" class="pageWidth" placeholder="请输入寄件人姓名" />
          </el-form-item>
          <el-form-item label="寄件人电话：" prop="mer_from_tel">
            <el-input v-model="formValidate.mer_from_tel" class="pageWidth" placeholder="请输入寄件人电话" />
          </el-form-item>
          <el-form-item label="寄件人地址：" prop="mer_from_addr">
            <el-input v-model="formValidate.mer_from_addr" type="textarea" class="pageWidth" placeholder="请输入寄件人地址" />
          </el-form-item>
        </template> -->

        <el-form-item>
          <el-button type="primary" @click="submitForm('formValidate')">提交</el-button>
        </el-form-item>
      </el-form>
    </el-card>
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
import { getdumpLst, updatedumpDataApi, getdumpDataApi } from '@/api/setting'
import { expressLst } from '@/api/order'
export default {
  name: 'Index',
  data() {
    return {
      headNum: '1',
      formValidate: {
        mer_config_temp_id: '',
        mer_from_com: '',
        mer_from_name: '',
        mer_config_siid: '',
        mer_from_tel: '',
        mer_from_addr: '',
        APPID: '',
        AppSecret: ''
      },
      mer_config_temp_id: '',
      deliveryList: [],
      dumpList: [],
      loading: false,
      rules: {
        mer_from_com: [
          { required: true, message: '请选择快递公司', trigger: 'change' }
        ],
        mer_config_temp_id: [
          { required: true, message: '请选择电子面单', trigger: 'change' }
        ],
        mer_from_name: [
          { required: true, message: '请输入寄件人姓名', trigger: 'blur' }
        ],
        mer_config_siid: [
          { required: true, message: '请输入云打印机编号', trigger: 'blur' }
        ],
        mer_from_tel: [
          { required: true, message: '请输入寄件人电话', trigger: 'blur' },
          { pattern: /^1(3|4|5|6|7|8|9)\d{9}$/, message: '请输入正确的联系方式', trigger: 'blur' }
        ],
        mer_from_addr: [
          { required: true, message: '请输入寄件人地址', trigger: 'blur' }
        ]
      }
    }
  },
  mounted() {
    this.getData()
    this.getExpressLst()
  },
  methods: {
    // 获取快递公司列表
    getExpressLst() {
      expressLst().then((res) => {
        this.deliveryList = res.data
      })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    // 获取电子面单列表
    getDumpList(type) {
      getdumpLst({ com: this.formValidate.mer_from_com,type:1 }).then((res) => {
        this.dumpList = res.data.data
        if (res.data.data.length) {
          this.formValidate.mer_config_temp_id = this.mer_config_temp_id
        } else {
          this.formValidate.mer_config_temp_id = ''
        }
        if (type != 'first') this.formValidate.mer_config_temp_id = ''
      })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    getData() {
      getdumpDataApi().then((res) => {
        this.formValidate = res.data
        this.mer_config_temp_id = res.data.mer_config_temp_id
        this.formValidate.mer_config_temp_id = ''
        if (res.data.mer_from_com) {
          this.getDumpList('first')
        }
      })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    submitForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          updatedumpDataApi(this.formValidate).then((res) => {
            this.$message.success(res.message)
          })
            .catch((res) => {
              this.$message.error(res.message)
            })
        } else {
          return
        }
      })
    }

  }
}
</script>

<style scoped lang="scss">
  .item-text{
    display: inline-block;
    margin-left: 30px;
    color: #606266;
    .title{
        font-weight: bold;
    }
  }
  .font-red{
      color: #ff4949;
  }
</style>

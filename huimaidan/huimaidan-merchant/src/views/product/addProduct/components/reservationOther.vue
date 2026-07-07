<template>
    <el-row>
            <el-col>
              <el-form-item label="支持退款：">
                <el-switch
                  v-model="formValidate.refund_switch"
                  :active-value="1"
                  :inactive-value="0"
                  :width="55"
                  active-text="开启"
                  inactive-text="关闭"
                />
              </el-form-item> 
            </el-col>
           
          
            <el-col v-if="formValidate.type!=3" :span="24">
              <el-col>
                <el-form-item label="最少购买件数：">
                  <el-input-number
                    v-model="formValidate.once_min_count"
                    :min="0"
                    size="small"
                    controls-position="right"
                    placeholder="请输入购买件数"
                  />
                  &nbsp;&nbsp;<span class="explanation">默认为0，则不限制购买件数</span>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col v-if="formValidate.type!=3" :span="24">
              <el-form-item label="限购类型：">
                <el-radio-group v-model="formValidate.pay_limit">
                  <el-radio :label="0" class="radio">不限购</el-radio>
                  <el-radio :label="1">单次限购</el-radio>
                  <el-radio :label="2">长期限购</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col v-if="formValidate.pay_limit != 0" :span="24">
              <el-col>
                <el-form-item label="限购数量：" prop="once_max_count">
                  <el-input-number
                    v-model="formValidate.once_max_count"
                    :min="formValidate.once_min_count"
                    size="small"
                    controls-position="right"
                    placeholder="请输入购买件数"
                  />
                  &nbsp;&nbsp;单次限购是限制每次下单最多购买的数量，长期限购是限制一个用户总共可以购买的数量
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="排序：">
                  <el-input-number
                    v-model="formValidate.sort"
                    controls-position="right"
                    placeholder="请输入排序"
                    size="small"
                  />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-form-item label="平台保障服务：" size="mini">
                <div class="acea-row">
                  <el-select
                    v-model="formValidate.guarantee_template_id"
                    placeholder="请选择"
                    clearable
                    size="small"
                    class="pageWidth"
                  >
                    <el-option
                      v-for="item in guaranteeList"
                      :key="item.guarantee_template_id"
                      :label="item.template_name"
                      :value="item.guarantee_template_id"
                    />
                  </el-select>
                  <el-button
                    class="ml15"
                    size="small"
                    @click="addServiceTem"
                  >添加服务说明模板</el-button>
                </div>
              </el-form-item>
            </el-col>
        
          
          </el-row>
  </template>
  
  <script>
  export default {
    name: 'ProductOtherSettings',
    props: {
      formValidate: {
        type: Object,
        required: true
      },
      deliveryList: {
        type: Array,
        default: () => []
      },
      shippingList: {
        type: Array,
        default: () => []
      },
      guaranteeList: {
        type: Array,
        default: () => []
      },
      formList: {
        type: Array,
        default: () => []
      },
      formUrl: {
        type: String,
        default: ''
      },
      roterPre: {
        type: String,
        default: ''
      },
    },
    data() {
      return {
        grid: {
          xl: 8,
          lg: 8,
          md: 12,
          sm: 24,
          xs: 24
        },
      } 
    },
    methods: {
      addTem() {
        this.$emit('addTem')
      },
      addServiceTem() {
        this.$emit('addServiceTem')
      },
      getFormList() {
        this.$emit('getFormList')
      },
      getFormInfo() {
        this.$emit('getFormInfo')
      }
    }
  }
  </script>
  
  <style scoped lang="scss">
  .explanation{
    color: #909399;
  }
  .iframe-box{
    min-height: 300px;
  }
  .pageWidth {
    width: 460px;
  }
  .ml15 {
    margin-left: 15px;
  }
  </style>
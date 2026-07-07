<template>
  <el-form
    ref="merDataField"
    size="small"
    :rules="ruleValidate"
    :model="merData"
    label-width="120px"
    @submit.native.prevent
    >
    <el-tabs v-loading="loading" type="border-card" v-model="activeName">
      <el-tab-pane label="基本信息" name="detail">
        <div class="section">
          <div class="title">基础信息</div>
          <el-row :gutter="24" class="mt20">
            <el-col :span="12">
              <el-form-item label="店铺名称：" prop="mer_name">
                <el-input
                  size="small"
                  v-model="merData.mer_name"
                  placeholder="请填写店铺名称"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="店铺地址：" prop="mer_address">
                <el-input
                  size="small"
                  v-model="merData.mer_address"
                  placeholder="请填写详细地址"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="12">
              <el-form-item label="所属商户：" prop="business_id">
                <el-cascader
                  v-model="merData.business_id"
                  class="selWidth"
                  size="small"
                  :options="businessList"
                  :props="{ checkStrictly: true, emitPath:false }"
                  filterable
                  clearable
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="店铺分组：" prop="store_group_ids">
                <el-cascader
                  v-model="merData.store_group_ids"
                  class="selWidth"
                  size="small"
                  :options="storeGroupList"
                  :props="{ checkStrictly: true, multiple: true, emitPath:false }"
                  filterable
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="12">
              <el-form-item label="店铺分类：" prop="category_id">
               <el-select
                  size="small"
                  v-model="merData.category_id"
                  placeholder="请选择"
                  class="selWidth"
                >
                  <el-option
                    v-for="item in merCateList"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="店铺区域：" prop="region_id">
                <el-cascader
                  v-model="merData.region_id"
                  class="selWidth"
                  size="small"
                  :options="zoneList"
                  :props="{ checkStrictly: true, emitPath:false }"
                  filterable
                  clearable
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="12">
              <el-form-item label="店铺类型：" prop="type_id">
                <el-select
                  size="small"
                  v-model="merData.type_id"
                  placeholder="请选择"
                  class="selWidth"
                >
                  <el-option
                    v-for="item in storeType"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="自营类型：" prop="is_trader">
                <el-radio-group
                  v-model="merData.is_trader"
                >
                  <el-radio :label="1" class="radio">自营</el-radio>
                  <el-radio :label="0">非自营</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="12">
              <el-form-item label="推荐店铺：" prop="is_best">
                <el-switch
                  v-model="merData.is_best"
                  :active-value="1"
                  :inactive-value="0"
                  :width="50"
                  active-text="是"
                  inactive-text="否"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="排序：" prop="sort">
                <el-input-number
                  size="small"
                  v-model="merData.sort"
                  controls-position="right"
                  placeholder="请输入排序"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="12">
              <el-form-item label="备注：" prop="mark">
                <el-input
                  type="textarea"
                  size="small"
                  v-model="merData.mark"
                  placeholder="请填写备注"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="店铺状态：" prop="status">
                <el-switch
                  v-model="merData.status"
                  :active-value="1"
                  :inactive-value="0"
                  :width="55"
                  active-text="开启"
                  inactive-text="关闭"
                />
              </el-form-item>
            </el-col>
          </el-row>
        </div>
      </el-tab-pane>
      <el-tab-pane label="经营信息" name="operate">
        <div class="section">
          <div class="title">费用信息</div>
          <el-row :gutter="24" class="mt20">
            <el-col v-if="!isAdd" :span="24">
              <el-form-item label="店铺保证金：" prop="ot_margin">
                <span>{{merData.is_margin == 0 ? '无' : merData.ot_margin+'元'}}</span>
              </el-form-item>
            </el-col>
            <el-col v-if="!isAdd && merData.is_margin != 0" :span="24">
              <el-form-item label="保证金支付状态：">
                <span>{{merData.is_margin == 1 ? '待缴' : merData.is_margin == 0 ? '无' : '已缴' }}</span>
              </el-form-item>
            </el-col>
            <el-col v-if="!isAdd && merData.is_margin != 0" :span="24">
              <el-form-item label="保证金余额：">
                <span>{{merData.margin}}</span>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="手续费设置：" prop="commission_rate">
                <el-switch
                  v-model="merData.commission_switch"
                  :active-value="1"
                  :inactive-value="0"
                  :width="55"
                  active-text="开启"
                  inactive-text="关闭"
                />
                <span v-if="merData.commission_switch">
                  <el-input-number
                    :min="0"
                    v-model="merData.commission_rate"
                    size="small"
                    controls-position="right"
                    placeholder="请输入手续费"
                  />%
                </span>
                <div class="info info-red">(注：此处如未设置手续费，系统会自动读取店铺分类下对应手续费；此处已设置，则优先以此处设置为准)</div>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col :span="24">
              <el-form-item label="惠买单合作模式：" prop="huimaidan_settlement_mode">
                <el-radio-group v-model="merData.huimaidan_settlement_mode">
                  <el-radio :label="1">垫资池模式</el-radio>
                  <el-radio :label="2">提现模式</el-radio>
                </el-radio-group>
                <div class="info">
                  提现模式用于商户端财务页和提现申请；垫资池模式需要先配置商户垫资池。
                </div>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="提现手续费率：" prop="huimaidan_withdraw_rate">
                <el-input-number
                  v-model="merData.huimaidan_withdraw_rate"
                  :min="0"
                  :max="100"
                  :precision="2"
                  size="small"
                  controls-position="right"
                />%
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="最低提现金额：" prop="huimaidan_min_extract_money">
                <el-input-number
                  v-model="merData.huimaidan_min_extract_money"
                  :min="0"
                  :precision="2"
                  size="small"
                  controls-position="right"
                /> 元
              </el-form-item>
            </el-col>
          </el-row>
        </div>
        <div class="section">
          <div class="title">审核信息</div>
           <el-row :gutter="24" class="mt20">
            <el-col :span="8">
              <el-form-item label="商品审核：" prop="is_audit">
                <el-switch
                  v-model="merData.is_audit"
                  :active-value="1"
                  :inactive-value="0"
                  :width="50"
                  active-text="是"
                  inactive-text="否"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="直播间审核：" prop="is_bro_room">
                <el-switch
                  v-model="merData.is_bro_room"
                  :active-value="1"
                  :inactive-value="0"
                  :width="50"
                  active-text="是"
                  inactive-text="否"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="直播商品审核：" prop="is_bro_goods">
                <el-switch
                  v-model="merData.is_bro_goods"
                  :active-value="1"
                  :inactive-value="0"
                  :width="50"
                  active-text="是"
                  inactive-text="否"
                />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="线下支付：" prop="offline_switch">
                <el-switch
                  v-model="merData.offline_switch"
                  :active-value="1"
                  :inactive-value="0"
                  :width="55"
                  active-text="开启"
                  inactive-text="关闭"
                />
              </el-form-item>
            </el-col>
           </el-row>
        </div>
         <div class="section" v-if="!isAdd">
          <div class="title">经营数据</div>
           <el-row :gutter="24" class="mt20">
            <el-col :span="8">
              <el-form-item label="实际关注人数：">
                <span>{{merData.care_count}}</span>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="已关注人数：">
                <span>{{merData.care_ficti}}</span>
                <el-button type="text" size="small" @click="setCareFicti" style="margin-left: 10px;">修改</el-button>
              </el-form-item>
            </el-col>
          </el-row>
        </div>
        <div class="section">
          <div class="title">其他信息</div>
          <div class="mt20">
            <el-row :gutter="24">
              <el-col :span="12">
                <el-form-item label="店铺关键字：" prop="mer_keyword">
                  <el-input
                    size="small"
                    v-model="merData.mer_keyword"
                    placeholder="请填写店铺关键字"
                    class="selWidth"
                  />
                </el-form-item>
              </el-col>
           </el-row>
           <el-row v-if="!isAdd" :gutter="24">
            <el-col :span="12" style="position: relative;">
              <el-form-item label="商品采集数：">
                <el-input v-model="merData.copy_product_num" size="small" disabled ></el-input>
                <el-button type="text" @click="modifyCopy" style="margin-left: 10px;position:absolute;right: -30px;">修改</el-button>
              </el-form-item>
            </el-col>
           </el-row>
          </div>
        </div>
      </el-tab-pane>
      <el-tab-pane label="账号信息" name="account">
        <div class="section">
          <div class="title">登录账号</div>
           <el-row :gutter="24" class="mt20">
            <el-col :span="12">
              <el-form-item label="店铺账号：" prop="mer_account">
                <el-input
                  type="text"
                  placeholder="请输入店铺账号"
                  size="small"
                  v-model="merData.mer_account"
                  :disabled="!isAdd"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="登录密码：" prop="mer_password">
                <el-input
                  type="password"
                  placeholder="请输入登录密码"
                  size="small"
                  v-model="merData.mer_password"
                  :disabled="!isAdd"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col v-if="!isAdd" :span="12">
              <el-form-item label="联系人：" prop="real_name">
                <el-input
                  type="text"
                  size="small"
                  placeholder="请填写联系人"
                  v-model="merData.real_name"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="联系电话：" prop="mer_phone">
                <el-input
                  size="small"
                  placeholder="请填写联系电话"
                  v-model="merData.mer_phone"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
           </el-row>
        </div>
        <div class="section">
          <div class="title">财务帐号</div>
           <el-row :gutter="24" class="mt20">
            <el-col :span="12">
              <el-form-item label="平台收付通：" prop="sub_mchid">
                <el-input
                  placeholder="请填写平台收付通"
                  size="small"
                  v-model="merData.sub_mchid"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="服务商特约店铺：">
                <el-input
                  placeholder="请填写服务商特约店铺"
                  size="small"
                  v-model="merData.applyment_id"
                  class="selWidth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <div class="info-red" style="margin-left: 120px;">当开启自动分账时，每个子店铺在微信后台的分账店铺号，即特约子店铺号</div>
            </el-col>
           </el-row>
        </div>
      </el-tab-pane>
  </el-tabs>
</el-form>

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
import { merchantUpdate, merchantCreate } from "@/api/merchant";
import { getBusinessZoneSelectApi } from "@/api/business-zone";
import { ORG_TYPE } from "@/domain/organization/org.enum";
export default {
  props: {
    merData: {
      type: Object,
      default: {},
    },
    isAdd: {
      type: Boolean,
      default: false,
    },
    merCateList: {
      type: Array,
      default: [],
    },
    storeType: {
      type: Array,
      default: [],
    },
    zoneList: {
      type: Array,
      default: [],
    },
    storeGroupList: {
      type: Array,
      default: () => [],
    }
  },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写联系方式'))
      } else if (!/^1[3456789]\d{9}$/.test(value) && !/^\d{5,13}$/.test(value)) {
        callback(new Error('请填写手机号或5-13位联系电话'))
      } else {
        callback()
      }
    }
    return {
      loading: false,
      merId: '',
      direction: 'rtl',
      activeName: 'detail',
      businessList: [], // 商户列表
      ruleValidate: {
        mer_name: [
          { required: true, message: '请输入店铺名称', trigger: 'blur' }
        ],
        mer_account: [
          { required: true, message: '请输入店铺账号', trigger: 'blur' }
        ],
        mer_password: [
          { required: true, message: '请输入登录密码', trigger: 'blur' }
        ],
        category_id: [
          { required: true, message: '请选择店铺分类', trigger: 'change' }
        ],
        type_id: [
          { required: true, message: '请选择店铺类型', trigger: 'change' }
        ],
        mer_phone: [{ required: true, validator: validatePhone, trigger: 'blur' }],
      },
    };
  },
  filters: {
  },
  created() {
    this.getBusinessList();
  },
  mounted() {

  },
  methods: {
    async getBusinessList() {
      try {
        const res = await getBusinessZoneSelectApi({
          type: ORG_TYPE.MERCHANT
        });
        this.businessList = res.data;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    /**修改采集次数 */
    modifyCopy() {
      this.$emit('modifyCopy')
    },
    /**重置表单数据 */
    resetData(){
      this.$refs.merDataField.resetFields();
    },
    // 设置虚拟关注数
    setCareFicti(){
      this.$emit('setCareFicti');
    },
    /*提交信息*/
    onSubmit(id){
      this.$refs['merDataField'].validate(valid => {
        if (valid) {
          this.loading = true;
          merchantUpdate(id,this.merData)
          .then(async res => {
            this.$message.success(res.message);
            this.loading = false;
            this.$emit('success');

          })
          .catch(res => {
            this.loading = false;
            this.$message.error(res.message);
          });
        }else {
          this.$message.error('请确保填写完信息后再提交！');
        }
      });
    },
    /**创建店铺 */
    handleCreate(){
      this.$refs['merDataField'].validate((valid) => {
        if (valid) {
          merchantCreate(this.merData)
            .then(async (res) => {
              this.$message.success(res.message);
              this.$emit('success');
            })
            .catch((res) => {
              this.$message.error(res.message);
            });
        } else {
          if(!this.merData.mer_name)return this.$message.error('请填写基本信息-店铺名称');
          if(!this.merData.category_id)return this.$message.error('请选择基本信息-店铺分类');
          if(!this.merData.type_id)return this.$message.error('请选择基本信息-店铺类型');
          if(!this.merData.mer_account)return this.$message.error('请填写账号信息-店铺账号');
          if(!this.merData.mer_phone)return this.$message.error('请填写账号信息-联系电话');
        }
      });
    }
  },
};
</script>
<style lang="scss" scoped>
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;

    &:nth-child(2n + 1) {
      padding-right: 20px;
      padding-left: 20px;
    }
    &:nth-child(2n) {
     padding-right: 20px;
    }
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.info-red{
  color: red;
  font-size: 12px;
}
::v-deep .el-input-number.is-controls-right .el-input__inner{
 padding: 0 40px 0 10px;
}
::v-deep .el-form-item__label{
  font-weight: normal;
  color: #282828;
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
.gary {
  color: #aaa;
}

</style>

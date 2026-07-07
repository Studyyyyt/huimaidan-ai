<template>
  <div class="divBox">
    <el-card class="box-card">
      <el-form ref="settingForm" :model="settingForm" :rules="rules" label-width="200px" >
        <el-form-item prop="extract_minimum_line">
          <span slot="label">
            <span>店铺余额押金：</span>
            <!-- <el-tooltip class="item" effect="dark" content="店铺余额不允许提现押金" placement="top-start">
              <i class="el-icon-warning-outline" />
            </el-tooltip> -->
          </span>
          <el-input-number
            v-model="settingForm.extract_minimum_line"
            :precision="2"
            :step="0.1"
            :min="0"
            controls-position="right"
            class="selWidth">
          </el-input-number>
          <span>（单位： 元）</span>
          <div>店铺余额不允许提现押金</div>
        </el-form-item>
        <el-form-item prop="extract_minimum_num">
          <span slot="label">
            <span>店铺每笔最小提现额度：</span>
            <!-- <el-tooltip class="item" effect="dark" content="指店铺的每次申请转账最小的金额；设置为0时默认不限制最小额度" placement="top-start">
              <i class="el-icon-warning-outline" />
            </el-tooltip> -->
          </span>
          <el-input-number
            v-model="settingForm.extract_minimum_num"
            :precision="2"
            :step="0.1"
            :min="0"
            controls-position="right"
            class="selWidth">
          </el-input-number>
          <span>（单位： 元）</span>
          <div>指店铺的每次申请转账最小的金额；设置为0时默认不限制最小额度</div>
        </el-form-item>
        <el-form-item prop="extract_maxmum_num">
          <span slot="label">
            <span>店铺每笔最高提现额度：</span>
            <!-- <el-tooltip class="item" effect="dark" content="店铺每次提现申请的最高额度，设置0时默认不限制" placement="top-start">
              <i class="el-icon-warning-outline" />
            </el-tooltip> -->
          </span>
          <el-input-number
            v-model="settingForm.extract_maxmum_num"
            :precision="2"
            :step="0.1"
            :min="0"
            controls-position="right"
            class="selWidth">
          </el-input-number>
          <span>（单位： 元）</span>
          <div>店铺每次提现申请的最高额度，设置0时默认不限制</div>
        </el-form-item>
        <el-form-item prop="mer_lock_time" label="店铺余额冻结期：">
          <el-input-number
            v-model="settingForm.mer_lock_time"
            :step="1"
            :min="0"
            controls-position="right"
            class="selWidth">
          </el-input-number>
          <span>（单位： 天）</span>
          <div>冻结期：仅针对线下转账模式，指用户支付成功后多少天，店铺余额可解冻；设置为0，即无冻结期。</div>
        </el-form-item>
        <el-form-item prop="open_wx_combine" required>
          <span slot="label">
            <span>开启自动分账：</span>
          </span>
          <el-radio-group v-model="settingForm.open_wx_combine">
            <el-radio :label="1">开启</el-radio>
            <el-radio :label="0" class="radio">关闭</el-radio>
          </el-radio-group>
          <div class="item-text">
            <div v-if="settingForm.open_wx_combine">
              <span class="title">开启说明：</span>
              <span>系统已对接微信电商收付通，开启此功能时，请注意以下事项：</span>
              <div>第一步：请在微信公众号后台开通电商收付通；</div>
              <div>第二步：请在<span class="color_blue">平台后台 - 设置-支付配置-微信服务商支付配置</span>做相应参数配置，配置好后，请在此处开启该自动分账；</div>
              <div>第三步：需子店铺在<span class="color_blue">店铺后台-财务-申请分账店铺</span> -提交资料-审核完成；</div>
              <div>以上步骤全部完成后，用户通过微信所支付的金额，会在用户确认收货后15天自动到子店铺号。通过余额支付、支付宝支付的金额请前往<span class="color_blue">财务-账单管理</span>查看，需子店铺申请转账，平台审核并线下转账。</div>
            </div>
            <div v-else>
              <span class="title">关闭说明：</span>
              <span>关闭自动分账时，系统默认启用线下手动转账模式， 指后台显示账单及子店铺实时结算的余额，子店铺需要申请转账，平台审核通过后，线下转账给子店铺。</span>
            </div>
          </div>
        </el-form-item>
        <el-form-item prop="open_wx_sub_mch" required>
          <span slot="label">
            <span>开启子店铺入驻：</span>
          </span>
          <el-radio-group v-model="settingForm.open_wx_sub_mch">
            <el-radio :label="1">开启</el-radio>
            <el-radio :label="0" class="radio">关闭</el-radio>
          </el-radio-group>
          <div class="item-text">
            <div>
              <span class="title">注意：</span>
              <span>此处开启子店铺入驻是指针对开启自动分账模式，需在微信支付店铺后台开启&lt;电商收付通&gt;，与商城的店铺入驻功能无关；如不使用自动分账，此处也不需要开启。</span>
            </div>
          </div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :loading="loading" @click="submitForm('settingForm')">保存</el-button>
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
import { getSettingApi, updateSettingApi } from '@/api/accounts'
export default {
  name: 'Index',
  data() {
    return {
      settingForm: {
        open_wx_combine: 0,
        open_wx_sub_mch: 0,
        extract_minimum_line: 0,
        extract_minimum_num: 1,
        extract_maxmum_num: 0,
        mer_lock_time: 0
      },
      status: 0,
      loading: false,
      rules: {
        open_wx_combine: [
          { required: true, message: '请选择是否开启自动分账', trigger: 'change' }
        ],
        open_wx_sub_mch: [
          { required: true, message: '请选择是否开启子店铺入驻', trigger: 'change' }
        ],
        extract_minimum_line: [
          { required: true, message: '请输入店铺最低提现金额', trigger: 'blur' }
        ],
        extract_minimum_num: [
          { required: true, message: '请输入每笔最小提现额度', trigger: 'blur' }
        ],
        extract_maxmum_num: [
          { required: true, message: '请输入店铺每笔最高提现金额', trigger: 'blur' }
        ],
        mer_lock_time: [
          { required: true, message: '请输入店铺余额冻结期', trigger: 'blur' }
        ]
      }
    }
  },
  mounted() {
    this.getDetal()
  },
  methods: {
    getDetal() {
      getSettingApi().then(res => {
        this.settingForm = res.data
        this.settingForm.open_wx_combine = Number(res.data.open_wx_combine)
        this.status = res.data.open_wx_combine
        this.settingForm.open_wx_sub_mch = Number(res.data.open_wx_sub_mch)

      }).catch((res) => {
        this.$message.error(res.message)
      })
    },
    submitForm(formName) {
      let that = this;
      that.$refs[formName].validate((valid) => {
        if (valid) {
          that.loading = true
          if((that.status == 0 && that.settingForm.open_wx_combine == 1)){
                 that.$confirm('开启自动分账后将自动关闭所有未入驻微信子店铺的店铺', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                 }).then(() => {
                    that.submit()
                 }).catch(() => {
                    that.loading = false
                    that.$message({
                        type: 'info',
                        message: '已取消'
                    });
                });
            }else{
                that.submit()
            }
        }else{
            return false
        }
      })
    },
    //提交
    submit(){
        updateSettingApi(this.settingForm).then(res => {
            this.loading = false
            this.$message.success(res.message)
        }).catch((res) => {
            this.$message.error(res.message)
            this.loading = false
        })
    }
  }
}
</script>

<style scoped lang="scss">
  .selWidth{
    width: 300px;
  }
  .item-text{
    color: #606266;
    .title{
        font-weight: bold;
    }
    .color_blue{
        color: var(--prev-color-primary);
    }
  }
  .font-red{
      color: #ff4949;
  }
</style>

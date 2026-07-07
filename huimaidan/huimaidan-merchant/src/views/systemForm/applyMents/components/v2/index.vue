<template>
  <div class="divBox">
    <el-card class="box-card" ref="container">
      <div class="split-guide">
        温馨提示：平台已开通服务商，特约商户可在此处提交相应资料，审核完成后，即可实现自动分账
      </div>
      <el-form v-if="prevDataLoaded" ref="form" :model="form" label-width="auto" size="small"
        @submit.native.prevent="handleSubmit" :validate-on-rule-change="false" :rules="rules" :show-message="false">
        <!-- 主体资料信息 -->
        <v2SubjectInfo :form.sync="form" />

        <!-- 超级管理员信息 -->
        <V2ContactInfo :form.sync="form" />

        <!-- 经营资料 -->
        <v2BusinessInfo :form.sync="form" />

        <!-- 结算规则 -->
        <v2SettlementInfo :form.sync="form" />

        <!-- 结算银行账户 -->
        <v2BankAccountInfo :form.sync="form" />

        <!-- 补充材料 -->
        <V2AdditionInfo :form.sync="form" />

        <!-- 提交按钮 -->
        <el-form-item>
          <el-button type="primary" native-type="submit" :loading="submitLoading">提交</el-button>
        </el-form-item>

      </el-form>
      <div v-else v-loading="true" style="height: 83vh;"></div>
    </el-card>
  </div>
</template>

<script>
import { PERSON_TYPE, BANK_ACCOUNT_TYPE } from "./v2.enum";
import { generateSuperContactInfo, generateSubjectInfo, mergeDefaultValue } from "./v2.utils";
import V2ContactInfo from "./section/v2-contact-info.vue";
import v2SubjectInfo from "./section/v2-subject-info.vue";
import v2BusinessInfo from "./section/v2-business-info.vue";
import v2SettlementInfo from "./section/v2-settlement-info.vue";
import v2BankAccountInfo from "./section/v2-bank-account-info.vue";
import V2AdditionInfo from "./section/v2-addition-info.vue";
import { applymentDetail, applymentCreateApi, applymentUpdateApi } from '@/api/system';

export default {
  name: "V2Form",
  components: {
    V2ContactInfo,
    v2SubjectInfo,
    v2BusinessInfo,
    v2SettlementInfo,
    v2BankAccountInfo,
    V2AdditionInfo
  },
  data() {
    return {
      rules: {},
      form: {

        // 超级管理员信息
        contact_info: {
          contact_type: PERSON_TYPE.LEGAL, // 超级管理员类型
          contact_name: "", // 超级管理员姓名

          ...generateSuperContactInfo(), // 经办人类型超级管理员默认信息

          mobile_phone: "", // 联系手机
          contact_email: "", // 联系邮箱
        },

        // 主体资料
        subject_info: generateSubjectInfo(),

        // 经营资料
        business_info: {
          merchant_shortname: "", // 商户简称
          service_phone: "", // 客服电话
          sales_info: { // 经营场景
            sales_scenes_type: [], // 经营场景类型
            biz_store_info: { // 线下场所场景
              biz_store_name: "", // 线下场所名称
              biz_address_code: "", // 线下场所省市编码
              biz_store_address: "", // 线下场所地址
              store_entrance_pic: [], // 线下场所门头照片
              indoor_pic: [], // 线下场所内部照片
              biz_sub_appid: "", // 线下场所对应的商家AppID
            },
            mp_info: { // 服务号或公众号场景
              mp_appid: "", //服务商服务号或公众号AppID
              mp_sub_appid: "", // 商家服务号或公众号AppID
              mp_pics: [], // 服务号或公众号页面截图
            },
            mini_program_info: { // 小程序场景
              mini_program_appid: "",  // 服务商小程序AppID
              mini_program_sub_appid: "", // 商家小程序AppID
              mini_program_pics: "", // 小程序截图
            },
            app_info: { // App场景
              app_appid: "", // 服务商应用AppID
              app_sub_appid: "", // 商家应用AppID
              app_pics: [], // App截图
            },
            web_info: { // 互联网网站场景
              domain: "", // 互联网网站域名
              web_authorisation: "", //网站授权函
              web_appid: "", // 互联网网站对应的商家AppID
            },
            wework_info: { // 企业微信场景
              sub_corp_id: "", // 商家企业微信CorpID
              wework_pics: [], // 企业微信截图
            }
          }
        },

        // 结算规则
        settlement_info: {
          settlement_id: "", // 结算规则ID
          qualification_type: "", // 所属行业
          qualifications: [], // 资质图片列表
          activities_id: "", // 优惠费率活动ID
          activities_rate: "", // 优惠费率
          activities_additions: [], // 优惠费率活动补充材料
          debit_activities_rate: "", //非信用卡活动费率值
          credit_activities_rate: "", // 信用卡活动费率值
        },

        // 结算银行账户
        bank_account_info: {
          bank_account_type: BANK_ACCOUNT_TYPE.CORPORATE, // 账户类型
          account_name: "", // 开户名称
          account_bank: "", // 开户银行
          bank_address_code: null, // 开户银行省市编码
          bank_branch_id: "", // 开户银行银行号
          bank_name: "", // 开户银行全称（含支行）
          account_number: "", // 银行帐号


          // 内部暂存数据，无关微信接口提交
          bank_address_code_full: null, // 开户银行省市完整数组编码，用于回显
          need_bank_branch: false, // 是否需要选择分行
          bank_alias: "", // 银行别名
          bank_alias_code: "", //  银行别名编码
        },

        // 补充材料
        addition_info: {
          legal_person_commitment: null,  // 法定代表人开户承诺函
          legal_person_video: null, // 法定代表人开户意愿视频
          business_addition_pics: [], // 补充材料
          business_addition_msg: "", // 补充说明
        }
      },

      prevDataLoaded: false,
      submitLoading: false,
      merApplymentsId: null
    };
  },
  created() {
    this.getApplymentDetail()
      .then(() => {
        this._unwatch = this.$watch('form.subject_info.subject_type', this.handleSubjectTypeChange);
      });
  },
  destroyed() {
    this._unwatch();
  },
  methods: {
    handleSubjectTypeChange() {
      this.form.subject_info = {
        ...generateSubjectInfo(),
        subject_type: this.form.subject_info.subject_type
      };
    },
    async getApplymentDetail() {
      try {
        const res = await applymentDetail();
        if (res.data.info && res.data.mer_applyments_id) {
          mergeDefaultValue(this.form, res.data.info);
          this.merApplymentsId = res.data.mer_applyments_id;
        }
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.prevDataLoaded = true;
      }
    },
    async handleSubmit() {
      if (this.submitLoading) return;
      this.submitLoading = true;

      try {
        await this.$refs.form.validate();
      } catch (error) {
        this.submitLoading = false;
        const el = this.$refs.container.$el.querySelector('.el-form-item.is-error');
        if (el) {
          el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }

      const payload = {
        ...this.form,
        type: 1
      };

      try {
        const task = this.merApplymentsId ? applymentUpdateApi(this.merApplymentsId, payload) : applymentCreateApi(payload);
        const res = await task;
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.submitLoading = false;
      }
    }
  },
};
</script>

<style scoped lang="scss">
::v-deep .title {
  margin-bottom: 16px;
  color: #17233d;
  font-size: 14px;
  font-weight: bold;
  padding-bottom: 2px;
  border-bottom: 1px solid #dfe6ec;
}

.split-guide{
  color: #ff4949;
  font-size: 13px;
  margin-bottom: 1em;
}
</style>

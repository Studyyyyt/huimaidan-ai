<template>
  <div class="box-container">
    <div>
      <div class="title" style="margin-top: 20px;">基本信息</div>
      <div class="acea-row">
        <div class="list sp">
          <label class="name">业务申请编号：</label>{{ formValidate.out_request_no }}
        </div>
        <div class="list sp">
          <label class="name">主体类型：</label>{{ formValidate.organization_type | organizationType }}
        </div>
      </div>
    </div>
    <div v-if="
      formValidate.organization_type != 2401 &&
      formValidate.organization_type != 2500
    " class="section">
      <div class="title" style="margin-top: 20px;">
        {{
          formValidate.organization_type == 2 ||
            formValidate.organization_type == 4
            ? "营业执照信息"
            : "登记证书信息"
        }}
      </div>
      <div class="acea-row">
        <div class="list sp100 image">
          <label class="name">证件扫描件：</label>
          <span class="img" v-if="formValidate.business_license_copy">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.business_license_copy['dir']"
              :preview-src-list="[formValidate.business_license_copy['dir']]" />
          </span>
        </div>
        <div class="list sp100">
          <label class="name">证件注册号：</label>{{ formValidate.business_license_number }}
        </div>
        <div class="list sp100">
          <label class="name">店铺名称：</label>{{ formValidate.merchant_name }}
        </div>
        <div class="list sp">
          <label class="name">经营者/法定代表人姓名：</label>{{ formValidate.legal_person }}
        </div>
        <div class="list sp" v-if="formValidate.company_address">
          <label class="name">注册地址：</label>{{ formValidate.company_address }}
        </div>
        <div class="list sp" v-if="formValidate.business_time">
          <label class="name">营业期限：</label>{{
            formValidate.business_start + "-" + formValidate.business_end
          }}
        </div>
      </div>
    </div>
    <div v-if="formValidate.organization_cert_info" class="section">
      <div class="title" style="margin-top: 20px;">
        组织机构代码证信息：
      </div>
      <div class="acea-row">
        <div class="list sp100 image">
          <label class="name">组织机构代码证照片：</label>
          <span class="img" v-if="formValidate.organization_copy">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.organization_copy['dir']" :preview-src-list="[formValidate.organization_copy['dir']]" />
          </span>
        </div>
        <div class="list sp">
          <label class="name">组织机构代码：</label>{{ formValidate.organization_number }}
        </div>
        <div class="list sp">
          <label class="name">组织机构代码有效期限：</label>{{ formValidate.start_time + "-" + formValidate.end_time }}
        </div>
        <div class="list sp">
          <label class="name">经营者/法人证件类型：</label>{{ formValidate.id_doc_type | id_docType }}
        </div>
      </div>
    </div>
    <div v-if="formValidate.id_doc_type == 1" class="section">
      <div class="title" style="margin-top: 20px;">
        经营者/法人身份证信息：
      </div>
      <div class="acea-row">
        <div class="list sp100 image">
          <label class="name">身份证人像面照片：</label>
          <span class="img" v-if="formValidate.id_card_copy">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.id_card_copy['dir']" :preview-src-list="[formValidate.id_card_copy['dir']]" />
          </span>
        </div>
        <div class="list sp100 image">
          <label class="name">身份证国徽面照片：</label>
          <span class="img" v-if="formValidate.id_card_national">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.id_card_national['dir']" :preview-src-list="[formValidate.id_card_national['dir']]" />
          </span>
        </div>
        <div class="list sp">
          <label class="name">身份证姓名：</label>{{ formValidate.id_card_name }}
        </div>
        <div class="list sp">
          <label class="name">身份证号码：</label>{{ formValidate.id_card_number }}
        </div>
        <div class="list sp">
          <label class="name">身份证有效期限：</label>{{ formValidate.id_card_valid_time }}
        </div>
      </div>
    </div>
    <div v-else class="section">
      <div class="title" style="margin-top: 20px;">
        经营者/法人其他类型证件信息：
      </div>
      <div class="acea-row">
        <div class="list sp">
          <label class="name">证件姓名：</label>{{ formValidate.id_doc_name }}
        </div>
        <div class="list sp">
          <label class="name">证件号码：</label>{{ formValidate.id_doc_number }}
        </div>
        <div class="list sp100 image">
          <label class="name">证件照片：</label>
          <span class="img" v-if="formValidate.id_doc_copy">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.id_doc_copy['dir']" :preview-src-list="[formValidate.id_doc_copy['dir']]" />
          </span>
        </div>
        <div class="list sp">
          <label class="name">证件结束日期：</label>{{ formValidate.doc_period_end }}
        </div>
      </div>
    </div>
    <div class="section">
      <div class="title" style="margin-top: 20px;">结算银行账户：</div>
      <div class="acea-row">
        <div class="list sp">
          <label class="name">账户类型：</label>{{
            formValidate.bank_account_type == 74 ? "对公账户" : "对私账户"
          }}
        </div>
        <div class="list sp">
          <label class="name">开户银行：</label>{{ formValidate.account_bank }}
        </div>
        <div class="list sp">
          <label class="name">开户名称：</label>{{ formValidate.account_name }}
        </div>
        <div class="list sp">
          <label class="name">开户银行省市编码：</label>{{ formValidate.bank_address_code }}
        </div>
        <div v-if="formValidate.bank_branch_id" class="list sp">
          <label class="name">开户银行联行号：</label>{{ formValidate.bank_branch_id }}
        </div>
        <div v-if="formValidate.bank_name" class="list sp">
          <label class="name">开户银行全称 （含支行）：</label>{{ formValidate.bank_name }}
        </div>
        <div class="list sp">
          <label class="name">银行帐号：</label>{{ formValidate.account_number }}
        </div>
      </div>
    </div>
    <div class="section">
      <div class="title" style="margin-top: 20px;">超级管理员信息：</div>
      <div class="acea-row">
        <div class="list sp">
          <label class="name">超级管理员类型：</label>{{
            formValidate.contact_type == 65 ? "经营者/法人" : "负责人"
          }}
        </div>
        <div class="list sp">
          <label class="name">超级管理员姓名：</label>{{ formValidate.contact_name }}
        </div>
        <div class="list sp">
          <label class="name">超级管理员身份证件号码：</label>{{ formValidate.contact_id_card_number }}
        </div>
        <div class="list sp">
          <label class="name">超级管理员手机：</label>{{ formValidate.mobile_phone }}
        </div>
        <div v-if="formValidate.contact_email" class="list sp">
          <label class="name">超级管理员邮箱：</label>{{ formValidate.contact_email }}
        </div>
      </div>
    </div>
    <div class="section">
      <div class="title" style="margin-top: 20px;">店铺信息：</div>
      <div class="acea-row">
        <div class="list sp">
          <label class="name">店铺名称：</label>{{ formValidate.store_name }}
        </div>
        <div v-if="formValidate.store_url" class="list sp">
          <label class="name">店铺链接：</label>{{ formValidate.store_url }}
        </div>
        <div v-if="formValidate.store_qr_code" class="list sp100 image">
          <label class="name">店铺二维码：</label>
          <span class="img" v-if="formValidate.store_qr_code">
            <el-image style="max-width: 150px; height: 80px;" :src="formValidate.store_qr_code['dir']" :preview-src-list="[formValidate.store_qr_code['dir']]" />
          </span>
        </div>
        <div v-if="formValidate.mini_program_sub_appid" class="list sp">
          <label class="name">小程序AppID：</label>{{ formValidate.mini_program_sub_appid }}
        </div>
        <div class="list sp">
          <label class="name">店铺简称：</label>{{ formValidate.merchant_shortname }}
        </div>
        <div class="list sp100 image" v-if="
          formValidate.qualifications &&
          formValidate.qualifications.length > 0
        ">
          <label class="name">特殊资质：</label>
          <span class="img" v-if="formValidate.qualifications.length > 0">
            <el-image v-for="(item, index) in formValidate.qualifications" :key="index"
              style="max-width: 150px; height: 80px;" :src="item['dir']"
              :preview-src-list="formValidate.qualifications.map(item => item['dir'])" />
          </span>
        </div>
        <div class="list sp100 image" v-if="
          formValidate.business_addition_pics &&
          formValidate.business_addition_pics.length > 0
        ">
          <label class="name">补充材料：</label>
          <span class="img">
            <el-image v-for="(item, index) in formValidate.business_addition_pics" :key="index"
              style="max-width: 150px; height: 80px;" :src="item['dir']"
              :preview-src-list="formValidate.business_addition_pics.map(item => item['dir'])" />
          </span>
        </div>
        <div v-if="formValidate.business_addition_desc" class="list sp">
          <label class="name">补充说明：</label>{{ formValidate.business_addition_desc }}
        </div>
        <div v-if="formValidate.message" class="list sp">
          <label class="name">{{
            formValidate.status == -1 || formValidate.status == 40
              ? "驳回原因"
              : formValidate.status == 11
                ? "需验证操作"
                : "审核结果"
          }}：</label>{{ formValidate.message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    data: Object
  },
  data() {
    return {
      msg: ''
    }
  },
  computed: {
    formValidate() {
      const info = this.data.info;
      const result = {
        mer_applyments_id: this.data.mer_applyments_id,
        status: this.data.status,
        message: this.data.message,
        id_doc_type: info.id_doc_type,
        out_request_no: info.out_request_no,
        organization_type: info.organization_type,
        business_license_copy: info.business_license_info
          ? info.business_license_info.business_license_copy
          : "",
        business_license_number: info.business_license_info
          ? info.business_license_info.business_license_number
          : "",
        merchant_name: info.business_license_info
          ? info.business_license_info.merchant_name
          : "",
        legal_person: info.business_license_info
          ? info.business_license_info.legal_person
          : "",
        company_address: info.business_license_info
          ? info.business_license_info.company_address
          : "",
        organization_cert_info: info.organization_cert_info,
        organization_copy: info.organization_cert_info
          ? info.organization_cert_info.organization_copy
          : "",
        organization_number: info.organization_cert_info
          ? info.organization_cert_info.organization_number
          : "",
        need_account_info: info.need_account_info,
        contact_type: info.contact_info
          ? info.contact_info.contact_type
          : 65,
        contact_name: info.contact_info
          ? info.contact_info.contact_name
          : "",
        contact_id_card_number: info.contact_info
          ? info.contact_info.contact_id_card_number
          : "",
        mobile_phone: info.contact_info
          ? info.contact_info.mobile_phone
          : "",
        contact_email: info.contact_info
          ? info.contact_info.contact_email
          : "",
        store_name: info.sales_scene_info
          ? info.sales_scene_info.store_name
          : "",
        store_url: info.sales_scene_info
          ? info.sales_scene_info.store_url
          : "",
        store_qr_code: info.sales_scene_info
          ? info.sales_scene_info.store_qr_code
          : "",
        mini_program_sub_appid: info.sales_scene_info
          ? info.sales_scene_info.mini_program_sub_appid
          : "",
        merchant_shortname: info.merchant_shortname,
        qualifications: info.qualifications ? info.qualifications : [],
        business_addition_pics: info.business_addition_pics
          ? info.business_addition_pics
          : [],
        business_addition_desc: info.business_addition_desc,
        business_time: info.business_license_info
          ? info.business_license_info.business_time
          : "",
        business_start:
          info.business_license_info &&
            info.business_license_info.business_time
            ? info.business_license_info.business_time[0]
            : "",
        business_end:
          info.business_license_info &&
            info.business_license_info.business_time
            ? info.business_license_info.business_time[1]
            : "",
        start_time:
          info.organization_cert_info &&
            info.organization_cert_info.organization_time
            ? info.organization_cert_info.organization_time[0]
            : "",
        end_time:
          info.organization_cert_info &&
            info.organization_cert_info.organization_time
            ? info.organization_cert_info.organization_time[1]
            : "",
        bank_account_type:
          (info.account_info && info.account_info.bank_account_type) || 74,
        account_bank: info.account_info
          ? info.account_info.account_bank
          : "",
        account_name: info.account_info
          ? info.account_info.account_name
          : "",
        bank_address_code: info.account_info
          ? info.account_info.bank_address_code
          : "",
        bank_branch_id: info.account_info
          ? info.account_info.bank_branch_id
          : "",
        bank_name: info.account_info ? info.account_info.bank_name : "",
        account_number: info.account_info
          ? info.account_info.account_number
          : ""
      };
      if (info.id_doc_type == 1) {
        result.id_card_copy =
          (info.id_card_info && info.id_card_info.id_card_copy) || [];
        result.id_card_national =
          (info.id_card_info && info.id_card_info.id_card_national) || "";
        result.id_card_name =
          (info.id_card_info && info.id_card_info.id_card_name) || "";
        result.id_card_number =
          (info.id_card_info && info.id_card_info.id_card_number) || "";
        result.id_card_valid_time =
          (info.id_card_info && info.id_card_info.id_card_valid_time) || "";
      } else {
        result.id_doc_name =
          (info.id_doc_info && info.id_doc_info.id_doc_name) || "";
        result.id_doc_number =
          (info.id_doc_info && info.id_doc_info.id_doc_number) || "";
        result.id_doc_copy =
          (info.id_doc_info && info.id_doc_info.id_doc_copy) || "";
        result.doc_period_end =
          (info.id_doc_info && info.id_doc_info.doc_period_end) || "";
      }
      return result;
    }
  },
  methods: {

  }
}
</script>

<style scoped lang="scss">
.box-container {
  overflow: hidden;
  padding: 0 35px;
}

.box-container .list {
  float: left;
  font-size: 13px;
  margin-top: 16px;
  color: #606266;
}

.box-container .sp {
  width: 50%;
}

.box-container .sp3 {
  width: 33.3333%;
}

.box-container .sp100 {
  width: 100%;
}

.box-container .list .blue {
  color: var(--prev-color-primary);
}

.box-container .list.image {
  // margin: 20px 0;
  position: relative;
}

.box-container .list.image .img {
  position: absolute;
  top: -20px;

  img {
    margin-right: 10px;
  }
}

.labeltop {
  max-height: 280px;
  min-height: 120px;
  overflow-y: auto;
}

.title {
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary);
  font-size: 14px;
  line-height: 15px;
  color: #303133;
  font-weight: bold;
}

.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
}
</style>

<template>
  <div class="divBox">
    <el-card>
      <div class="form-wrapper">
        <form-create :rule="rule" :value.sync="value" :option="options" v-model="fApi" @submit="handleSubmit" />
      </div>
    </el-card>
  </div>
</template>

<script>
import formCreate from "@form-create/element-ui";
import { mapGetters } from "vuex"
import { getUploadProps } from "@/utils/form-create";
import { getAgentSettlementAccountApi, updateAgentSettlementAccountApi } from "@/api/accounts";
import { PAYMENT_LIST } from "./domain/settlement.props.js";

export default {
  name: "SettlementAccount",
  components: {
    fromCreate: formCreate.$form,
  },
  computed: {
    ...mapGetters("user", ["agentId"])
  },
  data() {
    return {
      rule: [
        {
          type: "radio",
          field: "payment_method",
          title: "结算方式：",
          options: PAYMENT_LIST,
          control: [
            {
              value: 0,
              append: "payment_name",
              rule: [
                {
                  type: "input",
                  field: "payment_bank",
                  title: "开户行：",
                  props: {
                    placeholder: "请输入开户行"
                  },
                  $required: true
                },
                {
                  type: "input",
                  field: "payment_account",
                  title: "银行卡号：",
                  props: {
                    placeholder: "请输入银行卡号"
                  },
                  $required: true
                }
              ]
            },
            {
              value: 1,
              append: "payment_name",
              rule: [
                {
                  type: "input",
                  field: "payment_account",
                  title: "微信号：",
                  props: {
                    placeholder: "请输入微信号"
                  },
                  $required: true
                },
                {
                  type: "upload",
                  field: "payment_qr_img",
                  title: "收款二维码：",
                  props: {
                    ...getUploadProps(1),
                    accept: "image/*",
                    placeholder: "请上传收款二维码"
                  },
                  $required: true
                }
              ]
            },
            {
              value: 2,
              append: "payment_name",
              rule: [
                {
                  type: "input",
                  field: "payment_account",
                  title: "支付宝账号：",
                  props: {
                    placeholder: "请输入支付宝账号"
                  },
                  $required: true
                },
                {
                  type: "upload",
                  field: "payment_qr_img",
                  title: "收款二维码：",
                  props: {
                    ...getUploadProps(1),
                    accept: "image/*",
                    placeholder: "请上传收款二维码"
                  },
                  $required: true
                }
              ]
            }
          ]
        },
        {
          type: "input",
          field: "payment_name",
          title: "姓名：",
          props: {
            placeholder: "请输入姓名"
          },
          $required: true
        }
      ],
      options: {
        form: {
          size: "small",
          labelWidth: "8em"
        },
      },
      fApi: null,
      value: {
        payment_method: 0,
        payment_name: "",
        payment_account: "",
        payment_bank: "",
        payment_qr_img: ""
      }
    }
  },
  created() {
    this.getSettlementAccount();
  },
  methods: {
    async getSettlementAccount() {
      try {
        const res = await getAgentSettlementAccountApi(this.agentId);
        if (!res.data || Array.isArray(res.data)) return;
        for (const key of Object.keys(this.value)) {
          this.value[key] = res.data[key];
        }
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async handleSubmit(formData) {
      this.fApi.btn.loading(true);
      try {
        const res = await updateAgentSettlementAccountApi(this.agentId, formData);
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.fApi.btn.loading(false);
      }
    }
  }
}
</script>

<style scoped lang="scss">
.form-wrapper {
  width: 520px;
}
</style>

<template>
  <el-dialog title="申请转账" :visible.sync="visible" width="586px" :close-on-click-modal="false" class="dialog-form">
    <div v-if="agentSettlementInfo" v-loading="infoLoading">
      <div class="section">
        <div class="title">申请信息</div>
        <ul class="list double">
          <li class="item">
            <div class="label">代理姓名：</div>
            <div class="value">{{ agentSettlementInfo.name }}</div>
          </li>
          <li class="item">
            <div class="label width-8">提现方式：</div>
            <div class="value">{{ drawTypeLabel }}</div>
          </li>
          <li class="item">
            <div class="label">真实姓名：</div>
            <div class="value">{{ agentSettlementInfo.payment_name }}</div>
          </li>
          <template v-if="agentSettlementInfo.payment_method === PAYMENT_TYPE.WECHAT">
            <li class="item">
              <div class="label width-8">微信收款二维码：</div>
              <div class="value">
                <el-image class="image" style="margin: 0;" :src="agentSettlementInfo.payment_qr_img" :preview-src-list="[agentSettlementInfo.payment_qr_img]" />
              </div>
            </li>
            <li class="item">
              <div class="label">微信号：</div>
              <div class="value">{{ agentSettlementInfo.payment_account }}</div>
            </li>
          </template>
          <template v-if="agentSettlementInfo.payment_method === PAYMENT_TYPE.ALIPAY">
            <li class="item">
              <div class="label width-8">支付宝二维码：</div>
              <div class="value">
                <el-image class="image" style="margin: 0;" :src="agentSettlementInfo.payment_qr_img" :preview-src-list="[agentSettlementInfo.payment_qr_img]" />
              </div>
            </li>
            <li class="item">
              <div class="label width-6">支付宝账号：</div>
              <div class="value">{{ agentSettlementInfo.payment_account }}</div>
            </li>
          </template>
          <template v-if="agentSettlementInfo.payment_method === PAYMENT_TYPE.BANK">
            <li class="item">
              <div class="label width-8">开户行：</div>
              <div class="value">{{ agentSettlementInfo.payment_bank }}</div>
            </li>
            <li class="item">
              <div class="label">银行卡号：</div>
              <div class="value">{{ agentSettlementInfo.payment_account }}</div>
            </li>
          </template>
        </ul>
      </div>

      <div class="section">
        <div class="title">余额信息</div>
        <ul class="list double">
          <li class="item">
            <div class="label">提成余额：</div>
            <div class="value">￥{{ agentSettlementInfo.total_amount }}</div>
          </li>
          <li class="item">
            <div class="label width-8">可提现金额：</div>
            <div class="value">￥{{ agentSettlementInfo.balance }}</div>
          </li>
        </ul>
      </div>

      <div class="section">
        <div class="title">余额信息</div>
        <el-form label-width="7em" @submit.native.prevent size="small" style="margin-top: 16px;">
          <el-form-item label="本次提现：" required>
            <el-input-number v-model="withdrawAmount" placeholder="请输入提现金额" :min="0.01" :max="availableBalance" :step="0.01" :precision="2" :controls="false" class="draw-input" />
          </el-form-item>
        </el-form>
      </div>
    </div>

    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="handleClosePanel">取消</el-button>
      <el-button size="small" type="primary" :loading="submitLoading" @click="handleConfirm">确定</el-button>
    </span>
  </el-dialog>
</template>

<script>
import { getAgentSettlementInfoApi, agentApplySettlementApi } from "@/api/accounts"
import { mapGetters } from "vuex";
import { PAYMENT_TYPE } from '../domain/settlement.enum.js';
import { PAYMENT_TYPE_MAP } from "../domain/settlement.props.js"

export default {
  name: "ApplyForm",
  data() {
    return {
      PAYMENT_TYPE_MAP,
      PAYMENT_TYPE,
      visible: false,
      withdrawAmount: 0,
      qrCode: require("@/assets/images/u101.png"),
      submitLoading: false,
      infoLoading: false,
      agentSettlementInfo: null,
    };
  },
  computed: {
    ...mapGetters("user", ["agentId"]),
    drawTypeLabel() {
      if (!this.agentSettlementInfo) return "";
      return this.PAYMENT_TYPE_MAP[this.agentSettlementInfo.payment_method].label;
    },
    availableBalance() {
      if (!this.agentSettlementInfo) return 0;
      return Number(this.agentSettlementInfo.balance) || 0;
    }
  },
  methods: {
    // 打开申请结算面板
    open() {
      this.visible = true;
      this.getAgentSettlementInfo();
    },
    handleClosePanel() {
      this.visible = false;
    },
    async getAgentSettlementInfo() {
      if (this.infoLoading) return;
      this.infoLoading = true;
      try {
        const res = await getAgentSettlementInfoApi(this.agentId);
        this.agentSettlementInfo = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.infoLoading = false;
      }
    },
    async handleConfirm() {
      if (this.submitLoading) return;
      this.submitLoading = true;
      const amount = this.withdrawAmount;
      if (amount <= 0 || amount > this.availableBalance) {
        this.$message.error("提现金额不合法");
        return;
      }

      const payload = {
        agent_id: this.agentId,
        withdrawal_amount: amount,
        withdrawal_type: this.agentSettlementInfo.payment_method,
      }

      try {
        const res = await agentApplySettlementApi(payload);
        this.$message.success(res.message);
        this.$emit("refresh");
        this.handleClosePanel();
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.submitLoading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss">
.section {
  padding-bottom: 8px;

  &+.section {
    border-top: 1px dashed #eeeeee;
    padding-top: 20px;
  }

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

    &.column {
      flex-direction: column;

      .item {
        padding: 0 !important;
      }
    }

    &.double {
      .item {
        flex: 0 0 calc(100% / 2);

        padding-left: 0;
      }
    }
  }

  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    line-height: 18px;
    align-items: center;

    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
  }

  .label {
    width: 5em;

    &.width-8 {
      width: 8em;
    }

    &.width-6 {
      width: 6em;
    }

    text-align: right;
  }

  .value {
    flex: 1;

    .image {
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}

::v-deep .draw-input {
  width: 100%;
  input {
    text-align: left;
  }
}
</style>

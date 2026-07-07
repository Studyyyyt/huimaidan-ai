<template>
  <el-drawer :with-header="false" :visible.sync="visible" :size="1000" :close-on-click-modal="false"
    :before-close="handleClose">
    <div class="min100vh" v-loading="loading">
      <div v-if="review">
        <div class="head">
          <div class="full">
            <img class="order_icon" src="@/assets/images/u101.png" />
            <div class="text">
              <div class="title">代理提成申请详情</div>
              <div class="acea-row">
                <el-tag size="mini" v-if="tagConfig" :color="tagConfig.bgColor" :style="{ color: tagConfig.color }">
                  {{ tagConfig.label }}
                </el-tag>
              </div>
            </div>
            <div>
              <el-button :type="item.type" size="small" @click="item.action" v-for="item of btnList" :key="item.text">
                {{ item.text }}
              </el-button>
            </div>
          </div>
        </div>

        <div class="content">
          <!-- 申请信息板块 -->
          <div class="section">
            <div class="title">申请信息</div>
            <ul class="list double">
              <li class="item">
                <div class="label">提现金额(元)：</div>
                <div class="value">￥{{ review.withdrawal_amount }}</div>
              </li>
              <li class="item">
                <div class="label">提现方式：</div>
                <div class="value">{{ PAYMENT_TYPE_MAP[review.withdrawal_type].label }}</div>
              </li>
              <li class="item">
                <div class="label">真实姓名：</div>
                <div class="value">{{ review.withdrawal_name }}</div>
              </li>
              <li class="item"
                v-if="review.withdrawal_type === PAYMENT_TYPE.WECHAT || review.withdrawal_type === PAYMENT_TYPE.ALIPAY">
                <div class="label">收款二维码：</div>
                <div class="value">
                  <el-image class="image" style="margin: 0;" :src="review.withdrawal_qr_img"
                    :preview-src-list="[review.withdrawal_qr_img]" />
                </div>
              </li>
              <li class="item" v-if="review.withdrawal_type === PAYMENT_TYPE.BANK">
                <div class="label">银行卡号：</div>
                <div class="value">{{ review.withdrawal_account }}</div>
              </li>
              <li class="item">
                <div class="label">代理姓名：</div>
                <div class="value">{{ review.agent_name }}</div>
              </li>
              <li class="item">
                <div class="label">联系电话：</div>
                <div class="value">{{ review.agent_phone }}</div>
              </li>
              <li class="item">
                <div class="label">申请时间：</div>
                <div class="value">{{ review.create_time }}</div>
              </li>
            </ul>
          </div>

          <!-- 用户信息板块 -->
          <div class="section">
            <div class="title">用户信息</div>
            <ul class="list double">
              <li class="item">
                <div class="label">用户昵称：</div>
                <div class="value">{{ review.agent.user.nickname }} | {{ review.agent.uid }}</div>
              </li>
              <li class="item">
                <div class="label">用户电话：</div>
                <div class="value">{{ review.agent.user.phone }}</div>
              </li>
            </ul>
          </div>

          <!-- 审核结果板块 -->
          <div class="section">
            <div class="title">审核信息</div>
            <ul class="list column">
              <li class="item">
                <div class="label">审核结果：</div>
                <div class="value">{{ AGENT_STATUS_MAP[review.audit_status].label }}</div>
              </li>
              <li class="item" v-if="review.audit_status === AGENT_STATUS.REJECTED">
                <div class="label">拒绝原因：</div>
                <div class="value">{{ review.audit_reason }}</div>
              </li>
              <template v-if="review.audit_admin_id">
                <li class="item">
                  <div class="label">审核人：</div>
                  <div class="value">{{ reviewUserName }}</div>
                </li>
                <li class="item">
                  <div class="label">审核时间：</div>
                  <div class="value">{{ review.audit_time }}</div>
                </li>
              </template>
            </ul>
          </div>

          <!-- 转账凭证板块 -->
          <div class="section" v-if="review.status === CREDIT_STATUS.SUCCESS">
            <div class="title">转账凭证</div>
            <ul class="list column">
              <li class="item">
                <div class="label">转账凭证：</div>
                <div class="value">
                  <el-image class="image" style="margin: 0;" :src="src" :preview-src-list="review.transfer_voucher"
                    v-for="(src, index) of review.transfer_voucher" :key="index" />
                </div>
              </li>
              <li class="item" v-if="review.transfer_remark">
                <div class="label">备注：</div>
                <div class="value">{{ review.transfer_remark }}</div>
              </li>
            </ul>
          </div>

          <!-- 备注板块 -->
          <div class="section" v-if="review.platform_remark || review.remark">
            <div class="title">备注</div>
            <ul class="list column">
              <li class="item" v-if="review.platform_remark">
                <div class="label">平台备注：</div>
                <div class="value">{{ review.platform_remark }}</div>
              </li>
              <li class="item" v-if="review.remark">
                <div class="label">代理备注：</div>
                <div class="value">{{ review.remark }}</div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { zoneAgentSettlementReviewDetailApi } from '@/api/accounts';
import { AGENT_STATUS, AGENT_STATUS_MAP } from '@/views/businessZones/config';
import { CREDIT_STATUS, PAYMENT_TYPE } from '../domain/settlement.enum.js';
import { CREDIT_STATUS_MAP, PAYMENT_TYPE_MAP } from '../domain/settlement.props.js';

export default {
  name: "ReviewDetail",
  data() {
    return {
      CREDIT_STATUS,
      AGENT_STATUS,
      PAYMENT_TYPE,
      PAYMENT_TYPE_MAP,
      AGENT_STATUS_MAP,
      qrCode: require("@/assets/images/u101.png"),
      visible: false,
      review: null,
      reviewId: null,
      loading: false,
    }
  },
  computed: {
    reviewUserName() {
      const admin = this.review.admin;
      if (admin.real_name) return admin.real_name;
      if (admin.length) return admin[0].real_name;
      return "";
    },
    tagConfig() {
      if (!this.review) return null;
      const reviewStatusConfig = AGENT_STATUS_MAP[this.review.audit_status];
      const creditStatusConfig = CREDIT_STATUS_MAP[this.review.status];
      const config = {
        label: reviewStatusConfig.label + "-" + creditStatusConfig.label,
        bgColor: reviewStatusConfig.bgColor,
        color: reviewStatusConfig.color,
      };
      return config;
    },
    btnList() {
      if (!this.review) return [];
      const config = [
        {
          text: "撤销",
          condition: () => this.review.audit_status === AGENT_STATUS.PENDING && this.review.status === CREDIT_STATUS.PENDING,
          action: () => this.$emit("cancel", this.review),
          type: "primary"
        },
        {
          text: "备注",
          condition: () => true,
          action: () => this.$emit("remark", this.review)
        }
      ].filter(item => item.condition());
      return config;
    }
  },
  methods: {
    async refresh() {
      if (this.loading || !this.reviewId) return;
      this.loading = true;
      try {
        const res = await zoneAgentSettlementReviewDetailApi(this.reviewId);
        this.review = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    },
    open(review) {
      this.reviewId = review.checkout_id;
      this.visible = true;
      this.refresh();
    },
    handleClose() {
      this.visible = false;
      setTimeout(() => {
        this.review = null;
        this.reviewId = null;
      }, 200);
    }
  }
}
</script>

<style scoped lang="scss">
.head {
  padding: 20px 35px;

  .full {
    display: flex;
    align-items: center;

    .order_icon {
      width: 60px;
      height: 60px;
      border-radius: 100%;
    }

    .iconfont {
      color: var(--prev-color-primary);

      &.sale-after {
        color: #90add5;
      }
    }

    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;

      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
    }
  }
}

.content {
  padding: 15px;
  border-top: 1px dashed #eee;
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
    width: 8em;
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
</style>

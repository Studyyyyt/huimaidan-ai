<!-- 区域代理 - 区域详情侧滑抽屉 -->
<template>
  <el-drawer :with-header="false" :size="1000" :visible.sync="visible" :before-close="handleClose">
    <div class="min100vh" v-loading="loading">
      <div v-if="agent">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="agent.user.avatar" />
            <div class="text">
              <div class="title">{{ agent.user.nickname }}</div>
              <div class="acea-row">{{ agent.user.phone }}</div>
            </div>
          </div>
          <div>
            <ul class="list">
              <li class="item">
                <div class="title">提成累计(元)</div>
                <div>￥{{ agent.total_amount }}</div>
              </li>
              <li class="item">
                <div class="title">剩余可提(元)</div>
                <div>￥{{ agent.balance }}</div>
              </li>
              <li class="item">
                <div class="title">冻结中提成(元)</div>
                <div>￥{{ agent.frozen_amount }}</div>
              </li>
              <li class="item">
                <div class="title">订单数量</div>
                <div>{{ agent.order_count }}</div>
              </li>
              <li class="item">
                <div class="title">关联店铺</div>
                <div>{{ agent.merchant_count }}</div>
              </li>
            </ul>
          </div>
        </div>
        <el-tabs type="border-card" v-model="activeTab" @tab-click="handleTabChange" class="agent-detail-tabs">
          <el-tab-pane v-for="tab in TABS" :key="tab.value" :label="tab.label" :name="tab.value">

            <!-- 代理信息 -->
            <div v-if="tab.value === TAB_NAME.AGENT_INFO && activeTab === TAB_NAME.AGENT_INFO">
              <!-- 用户信息板块 -->
              <div class="section" v-if="agent.user">
                <div class="title">用户信息</div>
                <ul class="list">
                  <li class="item">
                    <div>用户昵称：</div>
                    <div class="value">{{ agent.user.nickname }} | {{ agent.uid }}</div>
                  </li>
                  <li class="item">
                    <div>用户电话：</div>
                    <div class="value">{{ agent.user.phone }}</div>
                  </li>
                </ul>
              </div>

              <!-- 代理信息板块 -->
              <div class="section">
                <div class="title">申请信息</div>
                <ul class="list column">
                  <li class="item" v-for="item of agentApplicationInfo" :key="item.title">
                    <div class="label">{{ item.title }}：</div>
                    <div class="value">
                      <template v-if="item.type === 'image'">
                        <el-image class="image" v-for="(src, index) in item.value" :key="index" :src="src" fit="cover"
                          :preview-src-list="item.value" :initial-index="index" />
                      </template>
                      <template v-else>
                        {{ item.value }}
                      </template>
                    </div>
                  </li>
                </ul>
              </div>

              <!-- 负责区域信息 -->
              <div class="section" v-if="agent.circle.length">
                <div class="title">负责区域信息</div>
                <div class="list" style="margin-top: 20px;">
                  <el-table :data="agent.circle">
                    <el-table-column label="区域名称" prop="name" />
                    <el-table-column label="代理级别" prop="level">
                      <template #default="{ row }">
                        {{ AGENT_LEVEL_MAP[row.level] }}
                      </template>
                    </el-table-column>
                    <el-table-column label="代理提成" prop="commission">
                      <template #default="{ row }">
                        {{ row.commission_rate }}%
                      </template>
                    </el-table-column>
                    <el-table-column label="关联店铺" prop="merchant_count" />
                  </el-table>
                </div>
              </div>

              <!-- 银行卡信息 -->
              <div class="section" v-if="settlementAccountInfo">
                <div class="title">{{ settlementAccountInfo.title }}</div>
                <ul class="list column">
                  <li class="item" v-for="item of settlementAccountInfo.fields" :key="item.title">
                    <div class="label width-6em">{{ item.title }}：</div>
                    <div class="value">
                      <template v-if="item.type === 'image'">
                        <el-image class="image" :src="item.value" fit="cover" />
                      </template>
                      <template v-else> {{ item.value }} </template>
                    </div>
                  </li>
                </ul>
              </div>
            </div>

            <!-- 关联店铺 -->
            <div v-if="tab.value === TAB_NAME.MERCHANT_INFO && activeTab === TAB_NAME.MERCHANT_INFO">
              <agent-detail-relative-mer :agent-id="agentId" :circles="circles" />
            </div>

            <!-- 提成订单 -->
            <div v-if="tab.value === TAB_NAME.COMMISSION_ORDER && activeTab === TAB_NAME.COMMISSION_ORDER">
              <agent-detail-order :agent-id="agentId" :circles="circles" />
            </div>

            <!-- 提现记录 -->
            <div v-if="tab.value === TAB_NAME.WITHDRAW_RECORD && activeTab === TAB_NAME.WITHDRAW_RECORD">
              <agent-detail-withdraw :agent-id="agentId" />
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { getAgentDetailApi } from '@/api/agent';
import AgentDetailRelativeMer from './agentDetailRelativeMer.vue';
import AgentDetailOrder from './agentDetailOrder.vue';
import AgentDetailWithdraw from './agentDetailWithdraw.vue';
import { AGENT_LEVEL_MAP } from '../domain/agent.enum.js';
import { normalizeAgentApplicationInfo, normalizeSettlementAccount } from '../domain/agent.rules.js';

const TAB_NAME = {
  AGENT_INFO: "agentInfo",
  MERCHANT_INFO: "merchantInfo",
  COMMISSION_ORDER: "commissionOrder",
  WITHDRAW_RECORD: "withdrawRecord"
};

const TABS = [
  {
    label: "代理信息",
    value: TAB_NAME.AGENT_INFO
  },
  {
    label: "关联店铺",
    value: TAB_NAME.MERCHANT_INFO
  },
  {
    label: "提成订单",
    value: TAB_NAME.COMMISSION_ORDER
  },
  {
    label: "提现记录",
    value: TAB_NAME.WITHDRAW_RECORD
  }
];

export default {
  name: "agentDetail",
  props: {
    visible: Boolean,
    agentId: {
      type: Number,
      default: null
    }
  },
  components: {
    AgentDetailRelativeMer,
    AgentDetailOrder,
    AgentDetailWithdraw
  },
  data() {
    return {
      agent: null,
      loading: false,
      AGENT_LEVEL_MAP,
      TAB_NAME,
      activeTab: TAB_NAME.AGENT_INFO,
      TABS,
      circles: []
    }
  },
  computed: {
    agentApplicationInfo() {
      if (!this.agent) return [];
      return normalizeAgentApplicationInfo(this.agent);
    },
    settlementAccountInfo() {
      if (!this.agent) return null;
      return normalizeSettlementAccount(this.agent);
    }
  },
  watch: {
    visible(visible) {
      if (visible) {
        this.agentId && this.handleGetAgentDetail(this.agentId);
      } else {
        this.agent = null;
      }
    }
  },
  methods: {
    handleTabChange(tab) {
      this.activeTab = tab.name;
    },
    handleClose() {
      this.$emit("close");
    },
    async handleGetAgentDetail() {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await getAgentDetailApi(this.agentId);
        this.agent = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss">
.agent-detail-tabs {
  box-shadow: none;
}

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

  .list {
    margin-top: 20px;
  }
}

.list {
  display: flex;
  overflow: hidden;
  list-style: none;
  padding: 0;

  .item {
    flex: none;
    width: 200px;
    font-size: 14px;
    line-height: 14px;
    color: rgba(0, 0, 0, 0.85);

    .title {
      margin-bottom: 12px;
      font-size: 13px;
      line-height: 13px;
      color: #666666;
    }
  }
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
        flex: none;
        width: 100%;
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
    text-align: right;

    &.width-6em {
      width: 6em;
    }
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

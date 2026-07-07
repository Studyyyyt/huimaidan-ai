<!-- 代理审核 - 代理审核详情侧滑抽屉 -->
<template>
  <el-drawer :with-header="false" :size="1000" :visible.sync="visible" :before-close="handleClose">
    <div class="min100vh" v-loading="loading">
      <div v-if="agent">
        <div class="head">
          <div class="full">
            <img class="order_icon" src="@/assets/images/u101.png" />
            <div class="text">
              <div class="title">{{ title }}</div>
              <div class="acea-row">
                <el-tag size="mini" v-if="AGENT_STATUS_MAP[agent.status]"
                  :color="AGENT_STATUS_MAP[agent.status].bgColor"
                  :style="{ color: AGENT_STATUS_MAP[agent.status].color }">
                  {{ AGENT_STATUS_MAP[agent.status].label }}
                </el-tag>
              </div>
            </div>
            <div v-if="isPending">
              <el-button type="primary" size="small" @click="handleApprove">同意</el-button>
              <el-button size="small" @click="handleReject">拒绝</el-button>
            </div>
            <i class="el-icon-close close-btn" @click="handleClose"></i>
          </div>
        </div>
        <div class="content">

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

          <!-- 申请信息板块 -->
          <div class="section">
            <div class="title">申请信息</div>
            <ul class="list column">
              <li class="item" v-for="item of applicationInfo" :key="item.title">
                <div class="label">{{ item.title }}：</div>
                <div class="value">
                  <template v-if="item.type === 'image'">
                    <el-image class="image" v-for="(src, index) in item.value" :key="index" :src="src" fit="cover"
                      :preview-src-list="item.value" :initial-index="index" />
                  </template>
                  <template v-else>
                    {{ item.value || "- -" }}
                  </template>
                </div>
              </li>
            </ul>
          </div>

          <!-- 审核结果板块 -->
          <div class="section">
            <div class="title">审核结果</div>
            <ul class="list column">
              <li class="item">
                <div class="label">审核结果：</div>
                <div class="value">{{ AGENT_STATUS_MAP[agent.status].label }}</div>
              </li>
              <li class="item" v-if="isRejected">
                <div class="label">拒绝原因：</div>
                <div class="value">{{ agent.audit_reason }}</div>
              </li>
              <template v-if="!isPending">
                <li class="item">
                  <div class="label">审核人：</div>
                  <div class="value">
                    <template v-if="agent.auditAdmin">
                      {{ agent.auditAdmin.real_name }}
                    </template>
                    <template v-else>
                      --
                    </template>
                  </div>
                </li>
                <li class="item">
                  <div class="label">审核时间：</div>
                  <div class="value">{{ agent.audit_time }}</div>
                </li>
              </template>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { AGENT_STATUS } from '../domain/agent.enum.js';
import { AGENT_STATUS_MAP } from '../domain/agent.props.js';
import { getAgentDetailApi } from '@/api/agent';
import { normalizeAgentApplicationInfo } from '../domain/agent.rules.js';
import { ORG_TYPE } from '@/domain/organization/org.enum.js';

export default {
  name: 'businessZonesAgentReviewDetail',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    agentId: Number
  },
  data() {
    return {
      ORG_TYPE, // 组织类型枚举
      AGENT_STATUS_MAP, // 申请状态映射对象
      agent: null, // 代理详情
      loading: false, // 加载中
    };
  },
  computed: {
    title() {
      if (!this.agent) return "";
      if (this.agent.type === ORG_TYPE.ZONE) return "代理申请详情";
      if (this.agent.type === ORG_TYPE.MERCHANT) return "商户申请详情";
      return "";
    },
    isPending() {
      // 是否为待审核状态
      return this.agent && this.agent.status === AGENT_STATUS.PENDING;
    },
    isRejected() {
      // 是否为已拒绝状态
      return this.agent && this.agent.status === AGENT_STATUS.REJECTED;
    },
    applicationInfo() {
      if (!this.agent) return [];
      return normalizeAgentApplicationInfo(this.agent);
    }
  },
  watch: {
    agentId: {
      handler(agentId) {
        // 如果代理ID存在，则获取代理详情
        agentId && this.handleGetAgentDetail(agentId);
      },
      immediate: true
    }
  },
  methods: {
    async handleGetAgentDetail(id) {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await getAgentDetailApi(id);
        this.agent = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    },
    handleApprove() {
      // 抛出审核通过事件
      this.$emit("approve", this.agent);
    },
    handleReject() {
      // 抛出审核拒绝事件
      this.$emit("reject", this.agent);
    },
    handleClose() {
      // 抛出关闭事件
      this.$emit("close");
    }
  }
}
</script>

<style scoped lang="scss">
.close-btn {
  cursor: pointer;
  font-size: 20px;
  margin-left: 20px;
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

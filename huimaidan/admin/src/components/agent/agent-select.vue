<template>
  <!-- 区域代理选择器 -->
  <el-select :value="agent_id" placeholder="请输入选择区域代理" filterable remote @change="handleChange" :loading="agentLoading"
    :remote-method="handleGetAgentList" class="selWidth remote" clearable>
    <el-option v-for="item of agentList" :key="item.circle_agent_id" :label="item.name" :value="item.circle_agent_id" />
  </el-select>
</template>

<script>
import { getAgentListApi } from "@/api/agent";
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';

export default {
  name: "AgentSelect",
  model: {
    prop: "agent_id",
    event: "change"
  },
  props: {
    agent_id: Number,
  },
  data() {
    return {
      agentList: [],
      agentLoading: false,
    }
  },
  created() {
    this.handleGetAgentList();
  },
  methods: {
    handleChange(value) {
      this.$emit("change", value || null);
    },
    // 获取区域代理列表
    async handleGetAgentList(query) {
      if (this.agentLoading) return;
      this.agentLoading = true;
      try {
        const res = await getAgentListApi({
          name: query,
          type: ACCOUNT_TYPE.ZONE,
          page: 1,
          limit: 10
        });
        this.agentList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.agentLoading = false;
      }
    },
  }
}
</script>

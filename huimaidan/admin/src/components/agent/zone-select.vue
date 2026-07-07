<template>
  <!-- 区域选择器 -->
  <el-cascader :value="circle_id" @change="handleChange" :options="zoneList" :props="zoneProps" class="selWidth"
    clearable></el-cascader>
</template>

<script>
import { getBusinessZoneListApi } from "@/api/business-zone";

export default {
  name: "ZoneSelect",
  model: {
    prop: "circle_id",
    event: "change"
  },
  props: {
    circle_id: Number,
    agentId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      zoneList: [],
      zoneLoading: false,
      zoneProps: {
        checkStrictly: true,
        emitPath: false,
        value: "circle_id",
        label: "name"
      }
    }
  },
  created() {
    this.handleGetZoneList();
  },
  methods: {
    handleChange(value) {
      this.$emit("change", value || null);
    },
    // 获取区域列表
    async handleGetZoneList() {
      if (this.zoneLoading) return;
      this.loading = true;
      const payload = {};
      if (this.agentId) {
        payload.circle_agent_id = this.agentId;
      }
      try {
        const res = await getBusinessZoneListApi(payload);
        this.zoneList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.zoneLoading = false;
      }
    },
  }
}
</script>

<style scoped lang="scss"></style>

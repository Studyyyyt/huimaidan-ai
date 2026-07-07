<!-- 区域设置 - 默认提成页面 -->
<template>
  <div>
    <zoneCommissionTable />
    <el-form class="commission-form" label-width="100px">
      <el-form-item label="一级代理提成:">
        <el-input-number v-model="config.one_agent_commission" :precision="2" :step="0.01" :min="0" :max="100"
          :controls="false" />
        <span class="percent-unit">%</span>
      </el-form-item>
      <el-form-item label="二级代理提成:">
        <el-input-number v-model="config.two_agent_commission" :precision="2" :step="0.01" :min="0" :max="100"
          :controls="false" />
        <span class="percent-unit">%</span>
      </el-form-item>
      <el-form-item label="三级代理提成:">
        <el-input-number v-model="config.three_agent_commission" :precision="2" :step="0.01" :min="0" :max="100"
          :controls="false" />
        <span class="percent-unit">%</span>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" size="small" @click="$emit('save')">保存</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import zoneCommissionTable from './zoneCommissionTable.vue';

export default {
  model: {
    prop: 'config',
    event: 'change',
  },
  components: {
    zoneCommissionTable
  },
  name: 'DefaultCommission',
  props: {
    config: Object
  }
}
</script>

<style scoped lang="scss">
.commission-form {
  margin-top: 20px;
}

.percent-unit {
  display: inline-block;
  color: #909399;
  font-size: 13px;
  transform: translateX(-200%);
}
</style>

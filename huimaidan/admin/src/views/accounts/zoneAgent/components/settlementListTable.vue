<template>
  <el-table :data="list" size="small" v-loading="loading" class="mt-14">
    <el-table-column v-for="item of TABLE_FIELD_OPTIONS" :key="item.field" :prop="item.field" :label="item.label"
      :width="item.width">
      <template #default="{ row }" v-if="item.renderTag">
        <el-tag size="mini" v-if="item.renderTag.condition(row)" :color="item.renderTag.bgColor(row)"
          :style="{ color: item.renderTag.color(row) }">
          {{ item.formatter(row) }}
        </el-tag>
      </template>
      <template #default="{ row }" v-else-if="item.formatter">
        {{ item.formatter(row) }}
      </template>
    </el-table-column>
    <slot name="action" />
  </el-table>

</template>

<script>
import { TABLE_FIELD_OPTIONS } from '../domain/settlement.schema.js';
export default {
  name: "settlementListTable",
  props: {
    list: Array,
    loading: Boolean
  },
  data() {
    return {
      TABLE_FIELD_OPTIONS
    }
  }
}
</script>

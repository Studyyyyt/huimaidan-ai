<template>
  <div v-if="data.length" class="ubo-list">
    <label class="name">最终受益人列表：</label>
    <div class="acea-row">
      <el-table :data="data" style="width: 100%">
        <el-table-column prop="ubo_id_doc_name" label="姓名" width="60" show-overflow-tooltip />
        <el-table-column prop="ubo_id_doc_type" label="证件类型" width="80">
          <template #default="{ row }">
            {{ IDENTIFICATION_TYPE_MAP[row.ubo_id_doc_type] }}
          </template>
        </el-table-column>
        <el-table-column prop="ubo_id_doc_number" label="证件号码" show-overflow-tooltip />
        <el-table-column prop="ubo_id_doc_address" label="证件地址" show-overflow-tooltip />
        <el-table-column prop="ubo_period_end" label="有效期" show-overflow-tooltip>
          <template #default="{ row }">
            {{ row.ubo_period_begin }}
             - 
            {{ row.ubo_period_longterm ? '长期' : row.ubo_period_end }}
          </template>
        </el-table-column>
        <el-table-column prop="ubo_id_doc_copy" label="正面照片" width="80">
          <template #default="{ row }">
            <el-image :src="row.ubo_id_doc_copy.dir" :preview-src-list="[row.ubo_id_doc_copy.dir]"
              style="width: 50px; height: 50px;" fit="cover" />
          </template>
        </el-table-column>
        <el-table-column prop="ubo_id_doc_copy_back" label="反面照片" width="80">
          <template #default="{ row }">
            <el-image :src="row.ubo_id_doc_copy_back.dir" :preview-src-list="[row.ubo_id_doc_copy_back.dir]"
              style="width: 50px; height: 50px;" fit="cover" />
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>

<script>

import { IDENTIFICATION_TYPE_MAP } from "./v2-config.js";
export default {
  name: "v2-ubo-list",
  props: {
    data: Array
  },
  data() {
    return {
      IDENTIFICATION_TYPE_MAP
    }
  },
  methods: {

  }
}
</script>

<style scoped>
.ubo-list {
  margin-top: 16px;
  width: 100%;
}
.name {
  font-size: 13px;
  color: #606266;
  margin-bottom: 10px;
  display: block;
}
</style>

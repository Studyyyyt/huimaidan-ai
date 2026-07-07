<!-- 区域代理 - 区域详情 - 提成订单列表 -->
<template>
  <div>
    <el-form :model="searchForm" label-width="auto" inline size="small" class="search-form">
      <el-form-item label="店铺名称：">
        <el-input v-model="searchForm.mer_name" placeholder="请输入店铺名称搜索" @change="handleGetList" style="width: 200px;" />
      </el-form-item>
      <el-form-item label="区域筛选：">
        <ZoneSelect v-model="searchForm.circle_id" @change="handleGetList" :agentId="agentId" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleGetList">搜索</el-button>
        <el-button @click="handleReset">重置</el-button>
      </el-form-item>
    </el-form>

    <CommissionRecordTable :list="list" :loading="loadOptions.loading" />

    <el-pagination :current-page.sync="loadOptions.page" :page-size.sync="loadOptions.limit" :total="loadOptions.total"
      @current-change="handleGetList" @size-change="handleGetList" />
  </div>
</template>

<script>
import { getCommissionFlowListApi } from '@/api/accounts';
import ZoneSelect from '@/components/agent/zone-select.vue';
import CommissionRecordTable from '@/views/accounts/zoneAgent/components/commissionRecordTable.vue';
export default {
  name: "agentDetailOrder",
  props: {
    agentId: {
      type: Number,
      default: null
    },
    circles: Array
  },
  components: {
    ZoneSelect,
    CommissionRecordTable
  },
  data() {
    return {
      searchForm: {
        mer_name: "", // 店铺名称
        circle_id: null, // 区域ID
      },
      loadOptions: {
        loading: false,
        total: 0,
        page: 1,
        limit: 10
      },
      list: [],
    }
  },
  created() {
    this.handleGetList();
  },
  methods: {
    handleReset() {
      this.searchForm = {
        mer_name: "",
        circle_id: null
      };
      this.loadOptions.page = 1;
      this.loadOptions.limit = 10;
      this.loadOptions.total = 0;
      this.handleGetList();
    },
    async handleGetList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;
      try {
        const res = await getCommissionFlowListApi({
          ...this.searchForm,
          agent_id: this.agentId,
          page: this.loadOptions.page,
          limit: this.loadOptions.limit
        });
        this.list = res.data.list;
        this.loadOptions.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loadOptions.loading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss">
::v-deep .search-form .el-form-item--small.el-form-item {
  margin-bottom: 0;
}

</style>

<!-- 区域代理 - 代理详情 - 提现记录 -->
<template>
  <div>
    <el-form :model="searchForm" label-width="auto" inline size="small" class="search-form">
      <el-form-item label="订单编号：">
        <el-input v-model="searchForm.withdrawal_sn" placeholder="请输入订单编号" @change="handleGetList"
          style="width: 200px;" />
      </el-form-item>
      <el-form-item label="申请日期：">
        <el-date-picker type="daterange" v-model="searchForm.create_time" placeholder="请选择申请日期" @change="handleGetList"
          range-separator="-" style="width: 240px;" value-format="yyyy-MM-dd" start-placeholder="开始日期" end-placeholder="结束日期" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleGetList">搜索</el-button>
        <el-button @click="handleReset">重置</el-button>
      </el-form-item>
    </el-form>

    <SettlementListTable :list="list" :loading="loadOptions.loading">
      <template #action>
        <el-table-column label="操作" width="100">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleOpenDetail(row.checkout_id)">详情</el-button>
          </template>
        </el-table-column>
      </template>
    </SettlementListTable>

    <el-pagination :current-page="loadOptions.page" :page-size="loadOptions.limit" :total="loadOptions.total"
      @current-change="handleCurrentChange" @size-change="handleSizeChange" />

    <form-dialog ref="formDialog" />

    <settlement-detail ref="settlementDetail" :role="ROLE" @change="handleDetailChange" />
  </div>
</template>

<script>
import SettlementListTable from '@/views/accounts/zoneAgent/components/settlementListTable.vue';
import { ROLE } from '@/views/accounts/zoneAgent/domain/settlement.enum.js';
import { getZoneAgentSettlementReviewListApi } from '@/api/accounts';
import SettlementDetail from '@/views/accounts/zoneAgent/components/settlementDetail.vue';
import settlementReviewMixin from '@/views/accounts/zoneAgent/mixins/settlementReviewMixin';

export default {
  name: "agentDetailWithdraw",
  components: {
    SettlementDetail,
    SettlementListTable
  },
  mixins: [settlementReviewMixin],
  props: {
    agentId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      ROLE: ROLE.PLATFORM,
      searchForm: {
        withdrawal_sn: "", // 订单编号
        create_time: [], // 申请日期
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
    handleDetailFresh() {
      this.$refs.settlementDetail.refresh();
    },
    // 改变每页条数
    handleSizeChange(size) {
      this.loadOptions.limit = size;
      this.handleGetList();
    },
    handleCurrentChange(page) {
      this.loadOptions.page = page;
      this.handleGetList();
    },
    // 打开提现记录详情
    handleOpenDetail(settlementId) {
      this.$refs.settlementDetail.open(settlementId);
    },
    // 重置搜索表单
    handleReset() {
      this.searchForm = {
        keyword: "",
        circle_id: ""
      };
      this.loadOptions.page = 1;
      this.loadOptions.limit = 10;
      this.loadOptions.total = 0;
      this.handleGetList();
    },
    // 获取提现记录列表
    async handleGetList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;
      try {
        const res = await getZoneAgentSettlementReviewListApi({
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

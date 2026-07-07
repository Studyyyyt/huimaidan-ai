<template>
  <div class="divBox">
    <el-card class="pb-2px">
      <el-form v-model="searchForm" inline size="small">
        <el-form-item label="代理名称：">
          <AgentSelect v-model="searchForm.agent_id" @change="handleGetList" />
        </el-form-item>
        <el-form-item label="联系电话：">
          <el-input v-model="searchForm.agent_phone" placeholder="请输入联系电话" @change="handleGetList" class="selWidth"
            clearable />
        </el-form-item>
        <el-form-item label="申请日期：">
          <el-date-picker v-model="searchForm.create_time" type="daterange" range-separator="-" @change="handleGetList"
            value-format="yyyy-MM-dd" style="width: 280px;" clearable start-placeholder="开始日期" end-placeholder="结束日期" />
        </el-form-item>

        <el-form-item label="审核状态：">
          <el-select v-model="searchForm.audit_status" placeholder="请选择审核状态" @change="handleGetList" class="selWidth"
            clearable>
            <el-option :label="item.label" :value="item.value" v-for="item of AGENT_STATUS_LIST" :key="item.label" />
          </el-select>
        </el-form-item>

        <el-form-item label="到账状态：">
          <el-select v-model="searchForm.status" placeholder="请选择到账状态" @change="handleGetList" class="selWidth"
            clearable>
            <el-option :label="item.label" :value="item.value" v-for="item of CREDIT_STATUS_LIST" :key="item.label" />
          </el-select>
        </el-form-item>

        <el-form-item label="提现方式：">
          <el-select v-model="searchForm.withdrawal_type" placeholder="请选择到账状态" @change="handleGetList" class="selWidth"
            clearable>
            <el-option :label="item.label" :value="item.value" v-for="item of PAYMENT_LIST" :key="item.label" />
          </el-select>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleGetList">搜索</el-button>
          <el-button @click="handleResetSearchForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="mt-14">
      <el-button size="small" @click="handleExportList" :loading="isExporting">导出列表</el-button>

      <SettlementListTable :list="list" :loading="loadOptions.loading" style="margin-top: 20px;">
        <template #action>
          <el-table-column label="操作" prop="action" width="200">
            <template #default="{ row }">
              <span v-for="(item, index) of getActionList(row)">
                <el-button type="text" size="small" @click="item.action">{{ item.text }}</el-button>
                <el-divider direction="vertical" v-if="!item.isLast" />
              </span>
            </template>
          </el-table-column>
        </template>
      </SettlementListTable>

      <el-pagination :current-page.sync="searchForm.page" :page-size.sync="searchForm.limit" :total="loadOptions.total"
        @current-change="handleGetList" @size-change="handleGetList" />
    </el-card>

    <form-dialog ref="formDialog" />

    <settlement-detail ref="settlementDetail" @change="handleDetailChange" :role="ROLE" />
  </div>
</template>

<script>
import { getZoneAgentSettlementReviewListApi, } from '@/api/accounts';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';
import { AGENT_STATUS_LIST } from '@/views/businessZones/domain/agent.props.js';
import { PAYMENT_LIST, CREDIT_STATUS_LIST } from './domain/settlement.props.js';
import settlementReviewMixin from './mixins/settlementReviewMixin';
import SettlementDetail from './components/settlementDetail.vue';
import AgentSelect from '@/components/agent/agent-select.vue';
import createWorkBook from '@/utils/newToExcel';
import moment from 'moment';
import { TABLE_FIELD_OPTIONS } from './domain/settlement.schema.js';
import { ROLE } from './domain/settlement.enum.js';
import SettlementListTable from './components/settlementListTable.vue';

const getInitSearchForm = () => {
  return {
    agent_id: null,
    agent_phone: "",
    create_time: [],
    audit_status: "",

    status: "",
    withdrawal_type: "",
    page: 1,
    limit: 10
  };
}

export default {
  name: "SettlementReview",
  components: {
    SettlementDetail,
    AgentSelect,
    SettlementListTable
  },
  mixins: [settlementReviewMixin],
  data() {
    return {
      ROLE: ROLE.PLATFORM,
      isExporting: false, // 是否正在导出
      AGENT_STATUS,
      AGENT_STATUS_LIST,
      CREDIT_STATUS_LIST,
      PAYMENT_LIST,
      searchForm: getInitSearchForm(),
      list: [],
      loadOptions: {
        total: 0,
        loading: false,
      }
    }
  },
  created() {
    this.handleGetList();
  },
  methods: {
    handleDetailFresh() {
      this.$refs.settlementDetail.refresh();
    },
    // 导出列表
    async handleExportList() {
      if (this.isExporting) return;
      this.isExporting = true;
      try {
        let page = 1;
        const data = [];
        while (1) {
          const res = await this.generateGetDataRequest({ page, limit: 20 });
          data.push(...res.data.list);
          if (res.data.list.length === 0 || data.length >= res.data.count) break;
          page++;
        }
        const header = TABLE_FIELD_OPTIONS.map(item => item.label);
        const title = [
          "结算审核列表",
          "导出时间：" + moment().format("YYYY-MM-DD HH:mm:ss"),
        ];
        const foot = "";
        const filename = title[0] + "_" + moment().format("YYYY-MM-DD_HH-mm-ss");
        const exportData = data.map(item => TABLE_FIELD_OPTIONS.map(field => {
          if (field.formatter) {
            return field.formatter(item);
          }
          return item[field.field];
        }));
        createWorkBook(
          header,
          title,
          exportData,
          foot,
          filename
        );
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.isExporting = false;
      }
    },
    // 重置搜索表单
    handleResetSearchForm() {
      this.searchForm = getInitSearchForm();
      this.loadOptions.total = 0;
      this.loadOptions.loading = false;
      this.handleGetList();
    },
    generateGetDataRequest(otherParams) {
      return getZoneAgentSettlementReviewListApi({
        ...this.searchForm,
        ...otherParams
      });
    },
    async handleGetList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;
      try {
        const res = await this.generateGetDataRequest();
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
::v-deep .el-input-group__prepend .el-input {
  width: 100px;
}
</style>

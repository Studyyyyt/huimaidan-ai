<template>
  <div class="divBox">
    <el-card class="pb-2px">
      <el-form v-model="searchForm" inline size="small">
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
          <el-select v-model="searchForm.withdrawal_type" placeholder="请选择提现方式" @change="handleGetList" class="selWidth"
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
      <div>
        <el-button size="small" type="primary" @click="handleOpenApplyDialog">申请结算</el-button>
        <el-button size="small" @click="handleExportList" :loading="isExporting">导出列表</el-button>
      </div>

      <SettlementApplyTable :list="list" :loading="loadOptions.loading" style="margin-top: 20px;">
        <template #action>
          <el-table-column label="操作" prop="action" width="150">
            <template #default="{ row }">
              <span v-for="(item) of getActionList(row)">
                <el-button type="text" size="small" @click="item.action">{{ item.text }}</el-button>
                <el-divider direction="vertical" v-if="!item.isLast" />
              </span>
            </template>
          </el-table-column>
        </template>
      </SettlementApplyTable>

      <el-pagination :current-page.sync="searchForm.page" :page-size.sync="searchForm.limit" :total="loadOptions.total"
        @current-change="handleGetList" @size-change="handleGetList" />
    </el-card>

    <!-- 申请结算面板 -->
    <apply-form ref="applyForm" @refresh="handleGetList" />

    <!-- 申请结算详情面板 -->
    <settlement-detail ref="settlementDetail" @change="handleDetailChange" :role="ROLE" />

    <!-- 生成各种表单弹窗的弹窗，如备注 -->
    <form-dialog ref="formDialog" />
  </div>
</template>

<script>
import { getZoneAgentSettlementReviewListApi } from '@/api/accounts';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';
import { AGENT_STATUS_LIST } from '@/views/businessZones/domain/agent.props.js';
import { PAYMENT_LIST, CREDIT_STATUS_LIST } from './domain/settlement.props.js';
import ApplyForm from './components/applyForm.vue';
import { mapGetters } from "vuex";
import createWorkBook from '@/utils/newToExcel';
import moment from 'moment';
import settlementReviewMixin from './mixins/settlementReviewMixin';
import SettlementApplyTable from './components/settlementListTable.vue';
import SettlementDetail from './components/settlementDetail.vue';
import { TABLE_FIELD_OPTIONS } from './domain/settlement.schema.js';
import { ROLE } from './domain/settlement.enum.js';

const getInitSearchForm = () => {
  return {
    create_time: [],
    audit_status: "",
    status: "",
    withdrawal_type: "",
    page: 1,
    limit: 10
  };
}

export default {
  name: "SettlementApply",
  components: {
    ApplyForm,
    SettlementApplyTable,
    SettlementDetail
  },
  mixins: [settlementReviewMixin],
  data() {
    return {
      ROLE: ROLE.AGENT,
      AGENT_STATUS,
      AGENT_STATUS_LIST,
      CREDIT_STATUS_LIST,
      PAYMENT_LIST,
      searchForm: getInitSearchForm(),
      list: [],
      loadOptions: {
        total: 0,
        loading: false,
      },
      isExporting: false
    }
  },
  computed: {
    ...mapGetters("user", ["agentId"]),
  },
  created() {
    this.handleGetList();
  },
  methods: {
    // 打开申请结算面板
    handleOpenApplyDialog() {
      this.$refs.applyForm.open();
    },
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
          "申请结算列表",
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
        ...otherParams,
        agent_id: this.agentId
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

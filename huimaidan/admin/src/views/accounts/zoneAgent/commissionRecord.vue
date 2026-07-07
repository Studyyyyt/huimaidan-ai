<template>
  <div class="divBox">
    <el-card class="pb-2px">
      <el-form v-model="searchForm" inline size="small">
        <template v-if="!isAgent">
          <el-form-item label="区域名称：">
            <ZoneSelect v-model="searchForm.circle_id" @change="handleGetList" />
          </el-form-item>

          <el-form-item label="区域代理：">
            <AgentSelect v-model="searchForm.agent_id" @change="handleGetList" />
          </el-form-item>
        </template>


        <el-form-item label="下单日期：">
          <el-date-picker v-model="searchForm.order_time" type="daterange" range-separator="-" @change="handleGetList"
            value-format="yyyy-MM-dd" style="width: 280px;" clearable start-placeholder="开始日期" end-placeholder="结束日期" />
        </el-form-item>

        <el-form-item label="订单编号：">
          <el-input v-model="searchForm.order_sn" placeholder="请输入订单编号" @change="handleGetList" class="selWidth"
            clearable />
        </el-form-item>

        <el-form-item label="店铺名称：">
          <el-input v-model="searchForm.mer_name" placeholder="请输入店铺名称" @change="handleGetList" class="selWidth"
            clearable />
        </el-form-item>

        <el-form-item label="订单状态：">
          <el-select v-model="searchForm.order_status" placeholder="请选择订单状态" @change="handleGetList" class="selWidth"
            clearable>
            <el-option :label="item.label" :value="item.value" v-for="item of ORDER_STATUS_LIST" :key="item.label" />
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

      <CommissionRecordTable :list="list" :loading="loadOptions.loading" style="margin-top: 20px;" />

      <el-pagination :current-page.sync="searchForm.page" :page-size.sync="searchForm.limit" :total="loadOptions.total"
        @current-change="handleGetList" @size-change="handleGetList" />
    </el-card>

  </div>
</template>

<script>
import { getCommissionFlowListApi } from '@/api/accounts';
import { mapGetters } from "vuex";
import ZoneSelect from '@/components/agent/zone-select.vue';
import AgentSelect from '@/components/agent/agent-select.vue';
import createWorkBook from '@/utils/newToExcel';
import moment from 'moment';
import {
  ORDER_STATUS_LIST
} from './domain/order.props.js';
import { TABLE_FIELD_OPTIONS } from "./domain/order.schema.js";
import CommissionRecordTable from './components/commissionRecordTable.vue';

const getInitSearchForm = () => {
  return {
    circle_id: null,
    agent_id: null,

    order_time: [],
    order_sn: "",
    mer_name: "",
    order_status: "",

    page: 1,
    limit: 10
  };
}


export default {
  name: "CommissionRecord",
  components: {
    ZoneSelect,
    AgentSelect,
    CommissionRecordTable
  },
  data() {
    return {
      TABLE_FIELD_OPTIONS,
      ORDER_STATUS_LIST,
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
    ...mapGetters("user", ["isAgent", "zoneId"])
  },
  created() {
    if (this.isAgent) {
      this.searchForm.circle_id = this.zoneId;
    }
    this.handleGetList();
  },
  methods: {
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
          "提成流水列表",
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
      return getCommissionFlowListApi({
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

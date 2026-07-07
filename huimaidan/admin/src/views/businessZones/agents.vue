<template>
  <div class="divBox">
    <el-card>
      <el-form v-model="searchForm" inline size="small" class="search-form">
        <el-form-item label="代理名称：">
          <el-input v-model="searchForm.name" placeholder="请输入代理名称" @change="handleGetAgentList" />
        </el-form-item>
        <el-form-item label="联系电话：">
          <el-input v-model="searchForm.phone" placeholder="请输入联系电话" @change="handleGetAgentList" />
        </el-form-item>
        <el-form-item label="创建时间：">
          <el-date-picker v-model="searchForm.createTime" type="daterange" range-separator="-" start-placeholder="开始日期"
            end-placeholder="结束日期" style="width: 250px;" @change="handleGetAgentList" value-format="yyyy-MM-dd" />
        </el-form-item>
        <el-form-item label="用户搜索：">
          <el-input v-model="userSearchContent" placeholder="请输入用户信息" @change="handleGetAgentList">
            <el-select v-model="userSearchSelectField" slot="prepend" placeholder="请选择" @change="handleGetAgentList">
              <el-option :label="item.label" :value="item.value" v-for="item of USER_SEARCH_OPTIONS"
                :key="item.label" />
            </el-select>
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleGetAgentList">搜索</el-button>
          <el-button @click="handleResetSearchForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt14">
      <div class="mb20">
        <el-button size="small" type="primary" @click="handleAddAgent">新增</el-button>
        <el-button size="small" @click="handleExportAgent" :loading="isExporting">导出</el-button>
      </div>
      <el-table :data="agentList" size="small" v-loading="formLoadOptions.loading">
        <el-table-column v-for="item of TABLE_FIELD_OPTIONS" :key="item.field" :prop="item.field" :label="item.label"
          :width="item.width" :min-width="item.minWidth">
          <template #default="{ row }" v-if="item.formatter">
            <span v-if="item.limitHeight" class="line1">
              {{ item.formatter(row) }}
            </span>
            <span v-else>
              {{ item.formatter(row) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="代理操作" prop="action" width="250">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleOpenAgentDetail(row)">详情</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleResetPassword(row.circle_agent_id)">重置密码</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleEditAgent(row)">编辑</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleAgentDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination background :page-size="formLoadOptions.limit" :current-page="formLoadOptions.page"
          layout="total, prev, pager, next, jumper" :total="formLoadOptions.total" @size-change="handleSizeChange"
          @current-change="pageChange" />
      </div>
    </el-card>

    <!-- 代理表单面板 -->
    <agent-edit-form :visible.sync="agentFormVisible" :agentId="currentAgent && currentAgent.circle_agent_id"
      @close="handleCloseAgentFormPanel" @refresh="handleGetAgentList" />

    <!-- 代理详情抽屉 -->
    <agent-detail :visible.sync="agentDetailVisible" :agentId="currentAgent && currentAgent.circle_agent_id"
      @close="handleCloseAgentDetail" />
  </div>
</template>

<script>
import { getAgentListApi, deleteAgentApi, resetAgentPwdApi } from '@/api/agent';
import { AGENT_STATUS } from './domain/agent.enum.js';
import { USER_SEARCH_OPTIONS } from './domain/agent.props.js';
import { getInitSearchForm } from './domain/agent.utils.js';
import { getInitFormLoadOptions } from './utils.js';
import AgentEditForm from './components/agentEditForm.vue';
import AgentDetail from './components/agentDetail.vue';
import createWorkBook from '@/utils/newToExcel';
import moment from 'moment';
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';

const TABLE_FIELD_OPTIONS = [
  {
    field: "name",
    label: "代理名称",
    width: 150
  },
  {
    field: "phone",
    label: "联系电话",
    width: 100
  },
  {
    field: "nickname",
    label: "用户信息",
    formatter: row => `${row.nickname} | ${row.uid}`,
    limitHeight: true,
    width: 180
  },
  {
    field: "circle",
    label: "负责区域",
    formatter: row => row.circle.map(item => `${item.name}(${item.commission_rate}%)`).join('、'),
    minWidth: 200
  },
  {
    field: "audit_time",
    label: "创建时间",
    width: 200
  }
];


export default {
  name: 'businessZonesAgents',
  components: {
    AgentEditForm,
    AgentDetail
  },
  data() {
    return {
      USER_SEARCH_OPTIONS, // 用户搜索选项
      AGENT_STATUS, // 申请状态枚举

      userSearchContent: "", // 用户搜索内容
      userSearchSelectField: USER_SEARCH_OPTIONS[0].value, // 用户搜索选择字段
      searchForm: getInitSearchForm(AGENT_STATUS.APPROVED), // 搜索表单
      formLoadOptions: getInitFormLoadOptions(), // 分页加载选项
      agentList: [], // 代理列表

      currentAgent: null, // 当前代理

      agentDetailVisible: false, // 代理详情面板是否可见
      agentFormVisible: false, // 代理表单面板是否可见

      isExporting: false, // 是否正在导出

      TABLE_FIELD_OPTIONS, // 表格字段选项
    }
  },
  created() {
    this.handleGetAgentList();
  },
  methods: {
    async handleResetPassword(id) {
      try {
        await this.$confirm('您确定要重置此管理员密码吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await resetAgentPwdApi(id);
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 关闭代理详情面板
    handleCloseAgentDetail() {
      this.agentDetailVisible = false;
      this.currentAgent = null;
    },
    // 打开代理详情面板
    handleOpenAgentDetail(agent) {
      this.currentAgent = agent;
      this.agentDetailVisible = true;
    },
    // 新增代理
    handleAddAgent() {
      this.currentAgent = null;
      this.handleOpenAgentFormPanel();
    },
    // 编辑代理
    handleEditAgent(agent) {
      this.currentAgent = agent;
      this.handleOpenAgentFormPanel();
    },
    // 导出代理
    async handleExportAgent() {
      if (this.isExporting) return;
      this.isExporting = true;
      let page = 1;
      const data = [];
      try {
        while (1) {
          const response = await this.generateGetDataRequest(page, 20);
          data.push(...response.data.list);
          if (response.data.list.length === 0 || data.length >= response.data.count) break;
          page++;
        }
        const header = TABLE_FIELD_OPTIONS.map(item => item.label);
        const title = [
          "区域代理列表",
          "导出时间：" + moment().format("YYYY-MM-DD HH:mm:ss"),
        ];
        const foot = "";
        const filename = "区域代理列表_" + moment().format("YYYY-MM-DD_HH-mm-ss");
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
    // 打开代理表单面板
    handleOpenAgentFormPanel() {
      this.agentFormVisible = true;
    },
    // 关闭代理表单面板
    handleCloseAgentFormPanel() {
      this.agentFormVisible = false;
    },
    // 删除代理
    async handleAgentDelete(agent) {
      try {
        await this.$confirm('您确定要删除此代理吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await deleteAgentApi(agent.circle_agent_id, true);
        this.$message.success(res.message);
        this.handleGetAgentList();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 分页大小变化
    handleSizeChange(val) {
      this.formLoadOptions.limit = val;
      this.handleGetAgentList();
    },
    // 分页页码变化
    pageChange(val) {
      this.formLoadOptions.page = val;
      this.handleGetAgentList();
    },
    // 重置搜索表单
    handleResetSearchForm() {
      this.searchForm = getInitSearchForm(AGENT_STATUS.APPROVED);
      this.userSearchContent = "";
      this.userSearchSelectField = USER_SEARCH_OPTIONS[0].value;
      this.handleGetAgentList();
    },
    generateGetDataRequest(page, limit) {
      const {
        name,
        phone,
        createTime,
        status
      } = this.searchForm;
      const params = {
        page,
        limit,
        name,
        phone,
        create_time: createTime,
        status,
        type: ACCOUNT_TYPE.ZONE
      };
      params[this.userSearchSelectField] = this.userSearchContent;
      return getAgentListApi(params);
    },
    // 获取代理列表
    async handleGetAgentList() {
      if (this.formLoadOptions.loading) return;
      this.formLoadOptions.loading = true;
      try {
        const res = await this.generateGetDataRequest(this.formLoadOptions.page, this.formLoadOptions.limit);
        this.agentList = res.data.list;
        this.formLoadOptions.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.formLoadOptions.loading = false;
      }
    }
  }
}
</script>

<style scoped lang="scss">
::v-deep .el-input-group__prepend .el-input {
  width: 100px;
}

::v-deep .search-form .el-form-item--small.el-form-item {
  margin-bottom: 0;
}
</style>

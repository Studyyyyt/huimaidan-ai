<template>
  <div class="divBox">
    <el-card class="search-form-card">
      <el-form v-model="searchForm" inline size="small">
        <el-form-item label="商户名称：">
          <el-input class="selWidth" v-model="searchForm.name" placeholder="请输入商户名称" @change="handleGetAgentList" clearable />
        </el-form-item>
        <el-form-item label="联系电话：">
          <el-input class="selWidth" v-model="searchForm.phone" placeholder="请输入联系电话" @change="handleGetAgentList" clearable />
        </el-form-item>
        <el-form-item label="申请时间：">
          <el-date-picker class="selWidth" v-model="searchForm.createTime" type="daterange" range-separator="-" start-placeholder="开始日期"
            end-placeholder="结束日期" style="width: 250px;" @change="handleGetAgentList" value-format="yyyy-MM-dd" />
        </el-form-item>
        <el-form-item label="用户搜索：">
          <el-input class="selWidth" v-model="userSearchContent" placeholder="请输入用户信息" @change="handleGetAgentList">
            <el-select v-model="userSearchSelectField" slot="prepend" placeholder="请选择"
              @change="handleGetAgentList">
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
      <el-tabs :value="searchForm.status + ''" @tab-click="handleTabChange">
        <el-tab-pane v-for="item of AGENT_STATUS_LIST" :key="item.value" :label="getTabLabel(item)" :name="item.value + ''" />
      </el-tabs>
      <el-table :data="merList" size="small" v-loading="formLoadOptions.loading">
        <el-table-column label="商户名称" prop="business_name" />
        <el-table-column label="联系人名称" prop="name" />
        <el-table-column label="联系电话" prop="phone" />
        <el-table-column label="管理姓名" prop="nickname" />
        <el-table-column label="说明" prop="remark">
          <template #default="{ row }">
            <el-tooltip class="item" effect="dark" :content="row.remark" placement="top-start">
              <span class="line1">
                {{ row.remark }}
              </span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="申请时间" prop="create_time" />
        <el-table-column label="审核状态" prop="status">
          <template #default="{ row }">
            <el-tag size="mini" v-if="AGENT_STATUS_MAP[row.status]" :color="AGENT_STATUS_MAP[row.status].bgColor"
              :style="{ color: AGENT_STATUS_MAP[row.status].color }">
              {{ AGENT_STATUS_MAP[row.status].label }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" prop="action">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleAgentReviewDetail(row)">详情</el-button>
            <template v-if="row.status === AGENT_STATUS.PENDING">
              <el-divider direction="vertical" />
              <el-button type="text" size="small" @click="handleReview(row)">通过</el-button>
              <el-divider direction="vertical" />
              <el-button type="text" size="small" @click="handleOpenRejectPanel(row)">拒绝</el-button>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination background :page-size="formLoadOptions.limit" :current-page="formLoadOptions.page"
          layout="total, prev, pager, next, jumper" :total="formLoadOptions.total" @size-change="handleSizeChange"
          @current-change="pageChange" />
      </div>
    </el-card>

    <!-- 审核拒绝原因面板 -->
    <el-dialog title="拒绝审核" :visible.sync="rejectPanelVisible" width="500px" :close-on-click-modal="false">
      <el-form label-width="100px">
        <el-form-item label="拒绝原因：" required>
          <el-input v-model="rejectReason" type="textarea" placeholder="请输入审核拒绝原因" :rows="7" :maxlength="300" />
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="handleCloseRejectPanel">取消</el-button>
        <el-button size="small" type="primary" :loading="rejectPanelLoading" @click="handleConfirmReject">确定</el-button>
      </span>
    </el-dialog>

    <!-- 商户详情面板 -->
    <mer-review-detail :visible.sync="reviewDetailVisible"
      :agentId="currentMer && currentMer.circle_agent_id" @close="handleCloseAgentReviewDetail"
      @approve="handleReview" @reject="handleOpenRejectPanel" />
  </div>
</template>

<script>
import { getAgentListApi, updateAgentStatusApi } from '@/api/agent';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';
import { AGENT_STATUS_LIST, AGENT_STATUS_MAP, USER_SEARCH_OPTIONS } from '@/views/businessZones/domain/agent.props.js';
import { getInitSearchForm } from '@/views/businessZones/domain/agent.utils.js';
import { getInitFormLoadOptions } from '@/views/businessZones/utils.js';
import MerReviewDetail from '@/views/businessZones/components/agentReviewDetail.vue';
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';

export default {
  name: 'MerchantApplicationReview',
  components: {
    MerReviewDetail
  },
  data() {
    return {
      USER_SEARCH_OPTIONS, // 用户搜索选项
      AGENT_STATUS_LIST, // 申请状态 tab 列表
      AGENT_STATUS_MAP, // 申请状态映射对象
      AGENT_STATUS, // 申请状态枚举

      userSearchContent: "", // 用户搜索内容
      userSearchSelectField: USER_SEARCH_OPTIONS[0].value, // 用户搜索选择字段
      searchForm: getInitSearchForm(), // 搜索表单
      formLoadOptions: getInitFormLoadOptions(), // 分页加载选项
      merList: [], // 商户列表

      currentMer: null, // 当前商户
      rejectReason: "", // 拒绝原因
      rejectPanelVisible: false, // 拒绝审核面板是否可见
      rejectPanelLoading: false, // 拒绝审核面板加载中

      reviewDetailVisible: false, // 商户详情面板是否可见

      typeTotal: null, // 各个状态类型的总数
    }
  },
  created() {
    this.handleGetAgentList();
  },
  methods: {
    getTabLabel(tabItemConfig) {
      if (!this.typeTotal) return tabItemConfig.label;
      const typeTotal = this.typeTotal[tabItemConfig.key];
      return `${tabItemConfig.label}(${typeTotal})`;
    },
    // 打开商户详情面板
    handleAgentReviewDetail(merInfo) {
      this.currentMer = merInfo;
      this.reviewDetailVisible = true;
    },
    // 关闭商户详情面板
    handleCloseAgentReviewDetail() {
      this.reviewDetailVisible = false;
      this.currentMer = null;
    },
    // 通过审核
    async handleReview(merInfo) {
      try {
        await this.$confirm('您确定要通过此商户审核吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await updateAgentStatusApi(merInfo.circle_agent_id, {
          status: AGENT_STATUS.APPROVED
        });
        this.$message.success(res.message);
        this.handleGetAgentList();
        this.handleCloseAgentReviewDetail();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 打开拒绝审核面板
    handleOpenRejectPanel(merInfo) {
      this.currentMer = merInfo;
      this.rejectPanelVisible = true;
    },
    // 关闭拒绝审核面板
    handleCloseRejectPanel() {
      this.rejectPanelVisible = false;
      this.rejectReason = "";
    },
    // 确认拒绝审核
    async handleConfirmReject() {
      if (!this.rejectReason) {
        this.$message.error("请输入审核拒绝原因");
        return;
      }
      if (this.rejectPanelLoading) return;
      this.rejectPanelLoading = true;
      try {
        const res = await updateAgentStatusApi(this.currentMer.circle_agent_id, {
          status: AGENT_STATUS.REJECTED,
          audit_reason: this.rejectReason
        });
        this.$message.success(res.message);
        this.handleCloseRejectPanel();
        this.handleCloseAgentReviewDetail();
        this.handleGetAgentList();
      } catch (error) {
        this.$message.error(error.message);
        return false;
      } finally {
        this.rejectPanelLoading = false;
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
    // 申请状态 tab 变化
    handleTabChange(targetTab) {
      this.searchForm.status = AGENT_STATUS_LIST[targetTab.index].value;
      this.formLoadOptions = getInitFormLoadOptions();
      this.handleGetAgentList();
    },
    // 重置搜索表单
    handleResetSearchForm() {
      this.searchForm = getInitSearchForm();
      this.userSearchContent = "";
      this.userSearchSelectField = USER_SEARCH_OPTIONS[0].value;
      this.handleGetAgentList();
    },
    // 获取商户列表
    async handleGetAgentList() {
      if (this.formLoadOptions.loading) return;
      this.formLoadOptions.loading = true;
      const {
        name,
        phone,
        createTime,
        status
      } = this.searchForm;
      const { page, limit } = this.formLoadOptions;
      try {
        const params = {
          page,
          limit,
          name,
          phone,
          create_time: createTime,
          status,
          type: ACCOUNT_TYPE.MERCHANT,
          is_apply: 1
        };
        params[this.userSearchSelectField] = this.userSearchContent;
        const res = await getAgentListApi(params);
        this.merList = res.data.list;
        this.typeTotal = res.data.typeTotal;
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

.search-form-card ::v-deep .el-card__body{
  padding-bottom: 2px;
}
</style>

<template>
  <div class="divBox">
    <el-card class="card-box">
      <el-form v-model="searchForm" inline size="small" class="search-form">
        <el-form-item label="区域名称：">
          <el-input class="selWidth" v-model="searchForm.name" placeholder="请输入区域名称" @change="handleGetRegionList"
            clearable />
        </el-form-item>
        <el-form-item label="联系人员：">
          <el-select class="selWidth remote" v-model="searchForm.agentId" placeholder="请输入选择联系人员" filterable remote
            @change="handleGetRegionList" :loading="loadOptions.agentLoading" :remote-method="handleGetAgentList"
            clearable>
            <el-option v-for="item of agentList" :key="item.circle_agent_id" :label="item.name"
              :value="item.circle_agent_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-select class="selWidth" v-model="searchForm.status" placeholder="请选择开启状态" @change="handleGetRegionList">
            <el-option :value="null" label="全部"></el-option>
            <el-option :value="1" label="开启"></el-option>
            <el-option :value="0" label="关闭"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleGetRegionList">搜索</el-button>
          <el-button @click="handleResetSearchForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt14">
      <el-button type="primary" size="small" @click="handleAddRegion()">新增区域</el-button>
      <el-table :data="regionList" size="small" v-loading="loadOptions.loading" class="mt20" row-key="circle_id"
        :tree-props="tableProps">
        <el-table-column label="区域名称" prop="name" show-overflow-tooltip />
        <el-table-column label="区域代理" prop="circleAgent.name" />
        <el-table-column label="手机号码" prop="circleAgent.phone" />
        <el-table-column label="代理提成" prop="commission_rate">
          <template #default="{ row }">
            <span v-if="row.type === ORG_TYPE.ZONE">
              {{ row.commission_rate }}%
            </span>
            <span v-else>--</span>
          </template>
        </el-table-column>
        <el-table-column label="关联店铺" prop="merchant_count"></el-table-column>
        <el-table-column label="开启状态" prop="status">
          <template #default="{ row }">
            <el-switch active-text="开启" inactive-text="关闭" v-model="row.status" :active-value="1" :inactive-value="0"
              @change="updateBusinessZoneStatusApi(row)" />
          </template>
        </el-table-column>
        <el-table-column label="排序" prop="sort" width="100" />
        <el-table-column label="操作" prop="action" width="200">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleOpenZoneDetail(row)">详情</el-button>
            <el-divider direction="vertical" />
            <template v-if="orgLevelMap[row.circle_id] < 2">
              <el-button type="text" size="small" @click="handleAddRegion(row.circle_id)">新增下级</el-button>
              <el-divider direction="vertical" />
            </template>
            <el-dropdown @command="command => handleDropdownCommand(command, row)">
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right"></i>
              </span>
              <el-dropdown-menu slot="dropdown" class="el-teleport">
                <el-dropdown-item :command="DROPDOWN_COMMAND.EDIT">编辑</el-dropdown-item>
                <el-dropdown-item :command="DROPDOWN_COMMAND.DELETE">删除</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <region-detail :visible.sync="regionDetailVisible" :orgId="currentOrg && currentOrg.circle_id"
      @close="regionDetailVisible = false" />
  </div>
</template>

<script>
import { getBusinessZoneListApi, updateBusinessZoneStatusApi, deleteBusinessZoneApi } from '@/api/business-zone';
import { getAgentListApi } from "@/api/agent";
import { ORG_TYPE } from '@/domain/organization/org.enum';
import regionDetail from './components/region-detail.vue';

const getInitSearchForm = () => {
  return {
    status: null, // 开启状态
    name: "", // 区域名称
    agentId: "",
  }
}

const DROPDOWN_COMMAND = {
  EDIT: 'edit',
  DELETE: 'delete'
}

export default {
  name: 'businessZonesIndex',
  components: {
    regionDetail,
  },
  data() {
    return {
      ORG_TYPE,
      DROPDOWN_COMMAND,
      searchForm: getInitSearchForm(),
      loadOptions: {
        loading: false,
        total: 0,
        agentLoading: false
      },
      regionList: [],
      agentList: [],

      regionDetailVisible: false,
      currentOrg: null,

      tableProps: {
        children: 'children',
      },
    }
  },
  computed: {
    orgLevelMap() {
      const buildOrgLevelMap = (orgList, level = 0) => {
        return orgList.reduce((acc, item) => {
          acc[item.circle_id] = level;
          if (item.children && item.children.length) {
            acc = { ...acc, ...buildOrgLevelMap(item.children, level + 1) };
          }
          return acc;
        }, {});
      }

      return buildOrgLevelMap(this.regionList);
    },
  },
  created() {
    this.handleGetRegionList();
    this.handleGetAgentList();
  },
  methods: {
    handleDropdownCommand(command, row) {
      switch (command) {
        case DROPDOWN_COMMAND.EDIT:
          this.handleEditZone(row);
          break;
        case DROPDOWN_COMMAND.DELETE:
          this.handleDeleteZone(row);
          break;
        default:
          break;
      }
    },
    async updateBusinessZoneStatusApi(row) {
      const payload = {
        status: row.status,
        is_show: row.is_show,
      };

      try {
        const res = await updateBusinessZoneStatusApi(row.circle_id, payload);
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 打开区域详情
    handleOpenZoneDetail(row) {
      this.currentOrg = row;
      this.regionDetailVisible = true;
    },
    // 编辑区域
    handleEditZone(row) {
      this.$router.push({
        name: 'businessZonesCreate',
        query: {
          id: row.circle_id
        }
      });
    },
    // 删除区域
    async handleDeleteZone(row) {
      try {
        await this.$confirm('您确定要删除此区域吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await deleteBusinessZoneApi(row.circle_id);
        this.$message.success(res.message);
        this.handleGetRegionList();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 新增区域
    handleAddRegion(pid) {
      this.$router.push({
        name: 'businessZonesCreate',
        query: {
          pid
        }
      });
    },
    async handleGetAgentList(name) {
      if (this.loadOptions.agentLoading) return;
      this.loadOptions.agentLoading = true;
      const query = {
        name,
        page: 1,
        limit: 10,
        status: 1,
        type: ORG_TYPE.ZONE
      };
      try {
        const res = await getAgentListApi(query);
        this.agentList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loadOptions.agentLoading = false;
      }
    },
    // 重置搜索表单
    handleResetSearchForm() {
      this.searchForm = getInitSearchForm();
      this.loadOptions.loading = false;
      this.loadOptions.total = 0;
      this.handleGetRegionList();
    },
    // 获取区域列表
    async handleGetRegionList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;

      try {
        const { agentId, ...rest } = this.searchForm;
        const res = await getBusinessZoneListApi({
          circle_agent_id: agentId,
          type: ORG_TYPE.ZONE,
          ...rest,
        });
        this.loadOptions.total = res.data.count;
        this.regionList = res.data.list;
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
::v-deep .card-box .el-card__body {
  padding-bottom: 2px;
}
</style>

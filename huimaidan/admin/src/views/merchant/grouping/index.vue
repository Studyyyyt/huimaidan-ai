<template>
  <div class="divBox">
    <el-card class="card-box">
      <el-form v-model="searchForm" inline size="small" class="search-form">
        <el-form-item label="分组名称：">
          <el-input class="selWidth" v-model="searchForm.name" placeholder="请输入分组名称" @change="handleGetZoneList"
            clearable />
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-select class="selWidth" v-model="searchForm.status" placeholder="请选择开启状态" @change="handleGetZoneList">
            <el-option :value="null" label="全部"></el-option>
            <el-option :value="1" label="开启"></el-option>
            <el-option :value="0" label="关闭"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleGetZoneList">搜索</el-button>
          <el-button @click="handleResetSearchForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt14">
      <el-button type="primary" size="small" @click="handleAddGroup()">新增分组</el-button>
      <el-table :data="orgList" size="small" v-loading="loadOptions.loading" class="mt20" :tree-props="tableProps"
        row-key="store_group_id">
        <el-table-column label="分组名称" prop="name" show-overflow-tooltip />
        <el-table-column label="关联店铺" prop="merchant_count"></el-table-column>
        <el-table-column label="开启状态" prop="status">
          <template #default="{ row }">
            <el-switch active-text="开启" inactive-text="关闭" v-model="row.status" :active-value="1" :inactive-value="0"
              @change="updateBusinessZoneStatusApi(row)" />
          </template>
        </el-table-column>
        <el-table-column label="装修ID" prop="diy_temp_id"></el-table-column>
        <el-table-column label="排序" prop="sort" width="100" />
        <el-table-column label="操作" prop="action" width="200">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleOpenZoneDetail(row)">详情</el-button>
            <el-divider direction="vertical" />
            <template v-if="orgLevelMap[row.store_group_id] < 2">
              <el-button type="text" size="small" @click="handleAddGroup(row.store_group_id)">新增下级</el-button>
              <el-divider direction="vertical" />
            </template>
            <el-dropdown @command="command => handleDropdownCommand(command, row)">
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right"></i>
              </span>
              <el-dropdown-menu slot="dropdown" class="el-teleport">
                <el-dropdown-item :command="DROPDOWN_COMMAND.EDIT">编辑</el-dropdown-item>
                <el-dropdown-item :command="DROPDOWN_COMMAND.DELETE">删除</el-dropdown-item>
                <el-dropdown-item :command="DROPDOWN_COMMAND.SET_TEMPLATE">商城装修</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <group-detail :visible.sync="orgDetailVisible" :orgId="currentOrg && currentOrg.store_group_id"
      @close="orgDetailVisible = false" />

    <diy-template-select ref="diyTemplateSelect" @confirm="handleConfirmDiyTemplate" />
  </div>
</template>

<script>
import { getStoreGroupListApi, setStoreGroupTemplateApi, deleteStoreGroupApi, updateStoreGroupStatusApi } from '@/api/store';
import groupDetail from './components/group-detail.vue';

const getInitSearchForm = () => {
  return {
    status: null, // 开启状态
    name: "", // 分组名称
  }
}

const DROPDOWN_COMMAND = {
  EDIT: 'edit',
  DELETE: 'delete',
  SET_TEMPLATE: 'setTemplate',
}

export default {
  name: 'merchantGrouping',
  components: {
    groupDetail,
  },
  data() {
    return {
      DROPDOWN_COMMAND,

      tableProps: {
        children: 'children',
      },
      searchForm: getInitSearchForm(),
      loadOptions: {
        loading: false,
        total: 0,
      },
      orgList: [],

      orgDetailVisible: false,
      currentOrg: null
    }
  },
  computed: {
    orgLevelMap() {
      const buildOrgLevelMap = (orgList, level = 0) => {
        return orgList.reduce((acc, item) => {
          acc[item.store_group_id] = level;
          if (item.children && item.children.length) {
            acc = { ...acc, ...buildOrgLevelMap(item.children, level + 1) };
          }
          return acc;
        }, {});
      }

      return buildOrgLevelMap(this.orgList);
    },
  },
  created() {
    this.handleGetZoneList();
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
        case DROPDOWN_COMMAND.SET_TEMPLATE:
          this.handleSetZoneTemplate(row);
          break;
        default:
          break;
      }
    },
    async handleConfirmDiyTemplate(diyTemplateId) {
      try {
        const res = await setStoreGroupTemplateApi(this.currentOrg.store_group_id, {
          diy_temp_id: diyTemplateId
        });
        this.currentOrg.diy_temp_id = diyTemplateId;
        this.$message.success(res.message);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.currentOrg = null;
      }
    },
    async updateBusinessZoneStatusApi(row) {
      const payload = {
        status: row.status
      };

      try {
        const res = await updateStoreGroupStatusApi(row.store_group_id, payload);
        this.$message.success(res.message);
        this.handleGetZoneList();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 打开分组详情
    handleOpenZoneDetail(row) {
      this.currentOrg = row;
      this.orgDetailVisible = true;
    },
    // 编辑分组
    handleEditZone(row) {
      this.$router.push({
        name: 'MerchantGroupingCreate',
        query: {
          id: row.store_group_id
        }
      });
    },
    // 设置分组模板
    handleSetZoneTemplate(orgInfo) {
      this.currentOrg = orgInfo;
      this.$refs.diyTemplateSelect.open(orgInfo.diy_temp_id);
    },
    // 删除分组
    async handleDeleteZone(row) {
      try {
        await this.$confirm('您确定要删除此分组吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await deleteStoreGroupApi(row.store_group_id);
        this.$message.success(res.message);
        this.handleGetZoneList();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 新增分组
    handleAddGroup(pid) {
      this.$router.push({
        name: 'MerchantGroupingCreate',
        query: {
          pid
        }
      });
    },
    // 重置搜索表单
    handleResetSearchForm() {
      this.searchForm = getInitSearchForm();
      this.loadOptions.loading = false;
      this.loadOptions.total = 0;
      this.handleGetZoneList();
    },
    // 获取分组列表
    async handleGetZoneList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;

      try {
        const res = await getStoreGroupListApi(this.searchForm);
        this.loadOptions.total = res.data.count;
        this.orgList = res.data.list;
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

$agent-type-select-width: 72px;

.agent-type-select {
  width: $agent-type-select-width;
}
</style>

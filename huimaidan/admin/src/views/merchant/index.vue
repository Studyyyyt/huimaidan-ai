<template>
  <div class="divBox">
    <el-card class="card-box">
      <el-form v-model="searchForm" inline size="small" class="search-form">
        <el-form-item label="商户名称：">
          <el-input class="selWidth" v-model="searchForm.name" placeholder="请输入商户名称" @change="handleGetMerList"
            clearable />
        </el-form-item>
        <el-form-item label="联系人员：">
          <el-select class="selWidth" v-model="searchForm.agentId" placeholder="请输入选择联系人员" filterable remote
            @change="handleGetMerList" :loading="loadOptions.agentLoading" :remote-method="handleGetAgentList"
            clearable>
            <el-option v-for="item of agentList" :key="item.circle_agent_id" :label="item.name"
              :value="item.circle_agent_id" />
          </el-select>
        </el-form-item>
        <el-form-item label="开启状态：">
          <el-select class="selWidth" v-model="searchForm.status" placeholder="请选择开启状态" @change="handleGetMerList">
            <el-option :value="null" label="全部"></el-option>
            <el-option :value="1" label="开启"></el-option>
            <el-option :value="0" label="关闭"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleGetMerList">搜索</el-button>
          <el-button @click="handleResetSearchForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt14">
      <el-button type="primary" size="small" @click="handleAdd()">新增商户</el-button>
      <el-table :data="merList" size="small" v-loading="loadOptions.loading" class="mt20">
        <el-table-column label="商户ID" prop="circle_id" />
        <el-table-column label="商户名称" prop="name" show-overflow-tooltip />
        <el-table-column label="商户管理员" prop="circleAgent.name" />
        <el-table-column label="手机号码" prop="circleAgent.phone" />
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
            <el-button type="text" size="small" @click="handleEditZone(row)">编辑</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleDeleteZone(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <mer-detail :visible.sync="merDetailVisible" :merId="currentMer && currentMer.circle_id"
      @close="merDetailVisible = false" />
  </div>
</template>

<script>
import { getBusinessZoneListApi, updateBusinessZoneStatusApi, deleteBusinessZoneApi } from '@/api/business-zone';
import { getAgentListApi } from "@/api/agent";
import { ORG_TYPE } from "@/domain/organization/org.enum";
import merDetail from './components/mer-detail.vue';

const getInitSearchForm = () => {
  return {
    status: null, // 开启状态
    name: "", // 商户名称
    agentId: "",
  }
}

export default {
  name: 'merchantIndex',
  components: {
    merDetail,
  },
  data() {
    return {
      searchForm: getInitSearchForm(),
      loadOptions: {
        loading: false,
        total: 0,
        agentLoading: false
      },
      merList: [],
      agentList: [],

      merDetailVisible: false,
      currentMer: null
    }
  },
  created() {
    this.handleGetMerList();
    this.handleGetAgentList();
  },
  methods: {
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
    // 打开商户详情
    handleOpenZoneDetail(row) {
      this.currentMer = row;
      this.merDetailVisible = true;
    },
    // 编辑商户
    handleEditZone(row) {
      this.$router.push({
        name: 'MerchantCreate',
        query: {
          id: row.circle_id
        }
      });
    },
    // 删除商户
    async handleDeleteZone(row) {
      try {
        await this.$confirm('您确定要删除此商户吗？', '提示', {
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
        this.handleGetMerList();
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 新增商户
    handleAdd(pid) {
      this.$router.push({
        name: 'MerchantCreate',
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
        type: ORG_TYPE.MERCHANT,
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
      this.handleGetMerList();
    },
    // 获取商户列表
    async handleGetMerList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;

      try {
        const { agentId, ...rest } = this.searchForm;
        const res = await getBusinessZoneListApi({
          circle_agent_id: agentId,
          type: ORG_TYPE.MERCHANT,
          ...rest,
        });
        this.loadOptions.total = res.data.count;
        this.merList = res.data.list;
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

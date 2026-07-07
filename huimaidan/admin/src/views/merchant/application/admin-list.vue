<template>
  <div class="divBox">
    <el-card>
      <el-form inline :model="searchForm" label-width="auto" size="small" class="search-form">
        <el-form-item label="管理搜索：">
          <el-input class="selWidth" v-model="searchForm.name" placeholder="请输入管理员姓名" @change="getList(true)" clearable></el-input>
        </el-form-item>
        <el-form-item label="联系电话：">
          <el-input class="selWidth" v-model="searchForm.phone" placeholder="请输入联系电话" @change="getList(true)" clearable></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="getList">搜索</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    <el-card class="mt14">
      <el-button type="primary" @click="handleClickAddBtn" size="small">新增管理</el-button>
      <el-table :data="list" size="small" v-loading="loadOptions.loading" class="mt14">
        <el-table-column prop="circle_agent_id" label="ID" width="100"></el-table-column>
        <el-table-column prop="name" label="管理员姓名"></el-table-column>
        <el-table-column prop="phone" label="联系电话"></el-table-column>
        <el-table-column prop="circleAgent" label="用户信息">
          <template #default="{ row }">
            <template v-if="row.uid">
              {{ row.nickname }} | {{ row.uid }}
            </template>
            <template v-else>
              -
            </template>
          </template>
        </el-table-column>
        <el-table-column label="负责商户">
          <template #default="{ row }">
            {{ row.circle.map(item => item.name).join('、') }}
          </template>
        </el-table-column>
        <el-table-column type="action" label="操作" width="240">
          <template #default="{ row }">
            <el-button type="text" size="small" @click="handleResetPassword(row.circle_agent_id)">重置密码</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleEdit(row)">编辑</el-button>
            <el-divider direction="vertical" />
            <el-button type="text" size="small" @click="handleDelete(row.circle_agent_id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block mt20">
        <el-pagination background :page-size="loadOptions.limit" :current-page="loadOptions.page"
          layout="total, prev, pager, next, jumper" :total="loadOptions.total" @size-change="handleSizeChange"
          @current-change="pageChange" />
      </div>
    </el-card>

    <mer-admin-edit-form ref="merAdminEditForm" @refresh="getList(true)" />
  </div>
</template>

<script>
import { getAgentListApi, deleteAgentApi, resetAgentPwdApi } from '@/api/agent';
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';
import merAdminEditForm from './components/admin-edit-form.vue';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';

const getSearchForm = () => {
  return {
    name: "",
    phone: ""
  }
}

export default {
  name: 'MerchantAdminList',
  components: {
    merAdminEditForm
  },
  data() {
    return {
      searchForm: getSearchForm(),
      list: [],
      loadOptions: {
        loading: false,
        page: 1,
        limit: 10,
        total: 0
      }
    }
  },
  created() {
    this.getList();
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
        this.getList(true);
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async handleDelete(id) {
      try {
        await this.$confirm('您确定要删除此管理员吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        })
      } catch (error) {
        return;
      }

      try {
        const res = await deleteAgentApi(id);
        this.$message.success(res.message);
        this.getList(true);
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 点击新增管理员按钮
    handleClickAddBtn() {
      this.$refs.merAdminEditForm.open();
    },
    handleEdit(row) {
      this.$refs.merAdminEditForm.open(row);
    },
    async getList(clear = false) {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;
      if (clear) this.loadOptions.page = 1;
      const { page, limit } = this.loadOptions;
      const payload = {
        type: ACCOUNT_TYPE.MERCHANT,
        page,
        limit,
        status: AGENT_STATUS.APPROVED,
        ...this.searchForm
      };
      try {
        const res = await getAgentListApi(payload);
        this.list = res.data.list;
        this.loadOptions.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loadOptions.loading = false;
      }
    },
    resetSearch() {
      this.searchForm = getSearchForm();
      this.getList(true);
    },
    handleSizeChange(limit) {
      this.loadOptions.limit = limit;
      this.getList();
    },
    pageChange(page) {
      this.loadOptions.page = page;
      this.getList();
    }
  }
}
</script>

<style scoped lang="scss">
::v-deep .search-form .el-form-item--small.el-form-item {
  margin-bottom: 0;
}
</style>

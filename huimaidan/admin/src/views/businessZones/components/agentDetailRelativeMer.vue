<!-- 区域代理 - 区域详情 - 关联店铺列表 -->
<template>
  <div>
    <el-form :model="searchForm" label-width="auto" inline size="small" label-position="left">
      <el-form-item label="关键字：" label-width="65px">
        <el-input clearable v-model="searchForm.keyword" placeholder="请输入店铺名称、联系人搜索" @change="handleGetList"
          style="width: 200px;" />
      </el-form-item>
      <el-form-item label="区域筛选：">
        <el-cascader clearable :options="zoneList" v-model="searchForm.circle_id" :props="parentZoneProps" placeholder="请选择区域" style="width: 250px;" @change="handleGetList"></el-cascader>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="handleGetList">搜索</el-button>
        <el-button @click="handleReset">重置</el-button>
      </el-form-item>
    </el-form>

    <el-table :data="list">
      <el-table-column label="店铺名称" prop="mer_name" />
      <el-table-column label="所属区域" prop="merchantRegion.name" />
      <el-table-column label="联系人" prop="real_name" />
      <el-table-column label="联系电话" prop="mer_phone" />
    </el-table>

    <el-pagination :current-page="loadOptions.page" :page-size="loadOptions.limit" :total="loadOptions.total"
      @current-change="handleCurrentChange" @size-change="handleSizeChange" />
  </div>
</template>

<script>
import { getAgentRelativeMerApi } from '@/api/agent';
import { getBusinessZoneListApi } from '@/api/business-zone';

export default {
  name: "agentDetailRelativeMer",
  props: {
    agentId: {
      type: Number,
      default: null
    },
    circles: Array
  },
  data() {
    return {
      parentZoneProps: { // 上级区域级联选择器配置
        checkStrictly: true,
        value: 'circle_id',
        label: 'name',
        emitPath: false,
        children: 'children'
      },
      searchForm: {
        keyword: "", // 关键字
        circle_id: "", // 区域ID
      },
      loadOptions: {
        loading: false,
        total: 0,
        page: 1,
        limit: 10
      },
      list: [],
      zoneList: []
    }
  },
  created() {
    this.handleGetList();
    this.getBusinessZoneList();
  },
  methods: {
    // 获取区域列表
    async getBusinessZoneList() {
      try {
        const res = await getBusinessZoneListApi({
          circle_agent_id: this.agentId,
        });
        this.zoneList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 改变每页条数
    handleSizeChange(size) {
      this.loadOptions.limit = size;
      this.handleGetList();
    },
    // 改变页码
    handleCurrentChange(page) {
      this.loadOptions.page = page;
      this.handleGetList();
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
    // 获取关联店铺列表
    async handleGetList() {
      if (this.loadOptions.loading) return;
      this.loadOptions.loading = true;
      try {
        const res = await getAgentRelativeMerApi(this.agentId, {
          ...this.searchForm,
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

<style scoped lang="scss"></style>

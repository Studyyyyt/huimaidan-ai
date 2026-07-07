<template>
  <el-dialog title="选择店铺" :visible.sync="dialogVisible" width="950px" :close-on-click-modal="false">
    <el-form :model="searchForm" label-width="auto" inline size="small">
      <el-form-item label="店铺名称：">
        <el-input class="form-item-width" v-model="searchForm.keyword" placeholder="请输入店铺名称" clearable @keyup.enter.native="getMerchantList" />
      </el-form-item>
      <el-form-item label="店铺分类：">
        <el-select class="form-item-width" v-model="searchForm.category_id" placeholder="请选择店铺分类" clearable @change="getMerchantList">
          <el-option v-for="item in storeCateList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="店铺类型：">
        <el-select class="form-item-width" v-model="searchForm.type_id" placeholder="请选择店铺类型" clearable @change="getMerchantList">
          <el-option v-for="item in storeTypeList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" size="small" @click="getMerchantList">搜索</el-button>
        <el-button size="small" @click="searchReset">重置</el-button>
      </el-form-item>
    </el-form>
    <el-table :data="tableData.data" size="small" max-height="500" v-loading="tableData.loading">
      <el-table-column prop="mer_id" label="店铺ID">
        <template #default="{ row }">
          <el-checkbox :value="row.mer_id" v-model="selectedMerIdList" :label="row.mer_id" v-if="multiple" />
          <el-radio v-model="selectedMerId" :label="row.mer_id" v-else />
        </template>
      </el-table-column>
      <el-table-column prop="mer_name" label="店铺名称" />
      <el-table-column prop="mer_phone" label="店铺电话" />
    </el-table>
    <div class="block">
      <el-pagination :page-size.sync="searchForm.limit" :current-page.sync="searchForm.page"
        layout="prev, pager, next, jumper" :total="tableData.total" @size-change="getMerchantList"
        @current-change="getMerchantList" />
    </div>
    <template #footer>
      <div>
        <el-button size="small" @click="handleCancel">取消</el-button>
        <el-button size="small" type="primary" @click="handleConfirm">确定</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script>
import {
  merchantListApi,
  getMerCateApi,
  getstoreTypeApi,
} from '@/api/merchant';

const getSearchForm = () => {
  return {
    status: 1, // 店铺状态：1-正常，0-禁用
    page: 1, // 页码
    limit: 8, // 每页条数

    keyword: null, // 店铺名称
    type_id: null, // 店铺类型
    category_id: null, // 店铺分类
  };
}

export default {
  name: 'merSelect',
  props: {
    autoLoad: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      dialogVisible: false, // 对话框是否显示
      searchForm: getSearchForm(),
      tableData: {
        data: [], // 店铺列表
        total: 0, // 总条数
        loading: false,
      },
      multiple: false, // 是否多选
      selectedMerIdList: [], // 选中的店铺ID列表
      selectedMerId: null, // 选中的店铺ID

      storeCateList: [], // 店铺分类列表
      storeTypeList: [], // 店铺类型列表
    }
  },
  created() {
    this.autoLoad && this.getMerchantList();
    this.getStoreCategory();
    this.getStoreType();
  },
  methods: {
    async getStoreCategory() {
      try {
        const res = await getMerCateApi();
        this.storeCateList = res.data;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async getStoreType() {
      try {
        const res = await getstoreTypeApi();
        this.storeTypeList = res.data;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    searchReset() {
      this.searchForm = getSearchForm();
      this.getMerchantList();
    },
    handleCancel() {
      this.dialogVisible = false;
    },
    handleConfirm() {
      if (this.multiple) {
        this.$emit('confirm', [...this.selectedMerIdList]);
      } else {
        this.$emit('confirm', this.selectedMerId);
      }
      this.handleCancel();
    },
    async getMerchantList(extendParams = {}) {
      if (this.tableData.loading) return;
      this.tableData.loading = true;
      try {
        const res = await merchantListApi({ ...this.searchForm, ...extendParams });
        this.tableData.data = res.data.list;
        this.tableData.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.tableData.loading = false;
      }
    },
    open(multiple = false, selectedMerId) {
      this.dialogVisible = true;
      this.multiple = multiple;
      if (selectedMerId) {
        if (this.multiple && Array.isArray(selectedMerId)) {
          this.selectedMerIdList = [...selectedMerId];
        } else if (!this.multiple && Number.isInteger(selectedMerId)) {
          this.selectedMerId = selectedMerId;
        }
      }
    }
  }
}
</script>

<style scoped lang="scss">
.form-item-width {
  width: 150px;
}
</style>

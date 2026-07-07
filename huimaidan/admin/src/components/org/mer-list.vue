<!-- 区域编辑 - 关联店铺页面 -->
<template>
  <div>
    <el-button type="primary" @click="handleSelectMerchant" size="small">选择店铺</el-button>

    <el-table :data="merList" size="small" :loading="merInfoLoading" class="mt14">
      <el-table-column prop="mer_id" label="店铺ID" />
      <el-table-column prop="mer_name" label="店铺名称" />
      <el-table-column prop="real_name" label="联系人" />
      <el-table-column prop="mer_phone" label="联系电话" />
      <el-table-column label="操作" width="100">
        <template #default="{ row }">
          <el-button type="text" @click="handleDeleteMerchant(row.mer_id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <merSelect ref="merSelect" :autoLoad="false" @confirm="handleConfirmMerchant" />
  </div>
</template>

<script>
import { merchantDetail } from '@/api/merchant';

const merInfoMap = new Map();

export default {
  name: 'merList',
  model: {
    prop: 'merIdList',
    event: 'change'
  },
  props: {
    merIdList: {
      type: Array,
      default: () => []
    },
  },
  data() {
    return {
      merList: [],
      merInfoLoading: false,
    }
  },
  watch: {
    merIdList: {
      handler(newVal) {
        this.getMerListDetail();
      },
      immediate: true,
      deep: true,
    }
  },
  methods: {
    // 删除店铺
    handleDeleteMerchant(merId) {
      this.$emit('change', this.merIdList.filter(id => id !== merId));
    },
    // 获取店铺列表详情
    async getMerListDetail() {
      if (!this.merIdList.length) {
        this.merList = [];
        return;
      }
      if (this.merInfoLoading) return;
      this.merInfoLoading = true;
      try {
        const task = this.merIdList.map(async merId => {
          if (!merInfoMap.has(merId)) {
            const res = await merchantDetail(merId);
            merInfoMap.set(merId, res.data);
          }
          return merInfoMap.get(merId);
        });
        this.merList = await Promise.all(task);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.merInfoLoading = false;
      }
    },
    // 打开选择店铺对话框
    handleSelectMerchant() {
      const params = {};
      this.$refs.merSelect.getMerchantList(params);
      this.$refs.merSelect.open(true, this.merIdList);
    },
    // 确认选择店铺
    handleConfirmMerchant(selectedMerIdList) {
      this.$emit('change', Array.from(new Set([...this.merIdList, ...selectedMerIdList])));
    }
  }
}
</script>

<style scoped lang="scss"></style>

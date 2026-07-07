<template>
  <div class="divBox">
    <el-card class="box-card" :body-style="{ padding: '8px 10px' }">
      <div class="flex align-center">
        <el-button style="color: #303133;" icon="el-icon-arrow-left" type="text" class="back"
          @click="goBack">返回</el-button>
        <div class="divider"></div>
        <span class="title">{{ merId ? '编辑商户' : '添加商户' }}</span>
      </div>
    </el-card>
    <el-card class="mt14">
      <el-tabs v-model="activeTab">
        <el-tab-pane :name="TAB_NAME.BASIC_INFO" label="基础信息" />
        <el-tab-pane :name="TAB_NAME.RELATED_MERCHANT" label="关联店铺" />
      </el-tabs>
      <div v-if="!loading">
        <merFormBasic :merInfo.sync="orgInfo" v-show="activeTab === TAB_NAME.BASIC_INFO" ref="basicForm" />
        <merList v-model="orgInfo.merIdList" v-show="activeTab === TAB_NAME.RELATED_MERCHANT" />
      </div>
    </el-card>
    <el-card class="footer-btn">
      <div class="flex-x-center">
        <el-button size="small" v-if="activeTab === TAB_NAME.BASIC_INFO" @click="handleNext">下一步</el-button>
        <el-button size="small" v-else-if="activeTab === TAB_NAME.RELATED_MERCHANT" @click="handleBack">上一步</el-button>
        <el-button size="small" type="primary" :loading="submitLoading" @click="handleSubmit">提交</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import merFormBasic from './components/mer-form-basic.vue';
import merList from '@/components/org/mer-list.vue';
import { getBusinessZoneDetailApi, createBusinessZoneApi, updateBusinessZoneApi } from '@/api/business-zone';
import { ORG_TYPE } from '@/domain/organization/org.enum';

const TAB_NAME = {
  BASIC_INFO: 'basicInfo',
  RELATED_MERCHANT: 'relatedMerchant',
}

export default {
  name: 'merchantCreate',
  components: {
    merFormBasic,
    merList,
  },
  data() {
    return {
      TAB_NAME,
      activeTab: TAB_NAME.BASIC_INFO,
      merId: 0,
      orgInfo: {
        business_store_category: null, // 店铺分类
        business_store_type: null, // 店铺类型
        type: ORG_TYPE.MERCHANT, // 组织类型，0: 区域，1: 商户
        name: '', // 商户名称
        agentId: null, // 商户管理员ID
        roleId: null, // 身份权限ID
        sort: 0, // 排序
        status: 1, // 状态，1: 启用，0: 禁用
        merIdList: [], // 关联店铺列表
      },

      loading: false,
      submitLoading: false
    }
  },
  created() {
    if (this.$route.query.id) {
      this.merId = parseInt(this.$route.query.id);
      this.getMerInfo();
    }
  },
  methods: {
    async handleSubmit() {
      try {
        await this.$refs.basicForm.validate();
      } catch (error) {
        return;
      }
      if (this.submitLoading) return;
      this.submitLoading = true;

      const {
        type,
        name,
        agentId: circle_agent_id,
        roleId: role_id,
        sort,
        status,
        merIdList: merchant_ids,
        ...args
      } = this.orgInfo;
      const payload = {
        type,
        name,
        circle_agent_id,
        role_id,
        sort,
        status,
        merchant_ids,
        ...args
      };

      try {
        const task = this.merId ? updateBusinessZoneApi(this.merId, payload) : createBusinessZoneApi(payload);
        const res = await task;
        this.$message.success(res.message);
        setTimeout(() => {
          this.$router.back();
        }, 1000);
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.submitLoading = false;
      }
    },
    // 获取组织详情
    async getMerInfo() {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await getBusinessZoneDetailApi(this.merId);
        const {
          circle_agent_id: agentId,
          role_id: roleId,
          merchant,
          business_store_category,
          business_store_type,
          ...args
        } = res.data;
        this.orgInfo = {
          agentId,
          roleId,
          merIdList: merchant.map(item => item.mer_id),
          business_store_category: business_store_category || null,
          business_store_type: business_store_type || null,
          ...args
        };
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    },
    goBack() {
      this.$router.back();
    },
    handleNext() {
      this.activeTab = TAB_NAME.RELATED_MERCHANT;
    },
    handleBack() {
      this.activeTab = TAB_NAME.BASIC_INFO;
    }
  }
}
</script>

<style scoped lang="scss">
.divider {
  width: 1px;
  height: 16px;
  background: #e8eaec;
  margin-inline: 15px;
}

.title {
  font-weight: 500;
  color: #303133;
}

.footer-btn {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
}
</style>

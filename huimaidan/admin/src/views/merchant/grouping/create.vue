<template>
  <div class="divBox">
    <el-card class="box-card" :body-style="{ padding: '8px 10px' }">
      <div class="flex align-center">
        <el-button style="color: #303133;" icon="el-icon-arrow-left" type="text" class="back"
          @click="goBack">返回</el-button>
        <div class="divider"></div>
        <span class="title">{{ groupId ? '编辑分组' : '添加分组' }}</span>
      </div>
    </el-card>
    <el-card class="mt14">
      <el-tabs v-model="activeTab">
        <el-tab-pane :name="TAB_NAME.BASIC_INFO" label="基础信息" />
        <el-tab-pane :name="TAB_NAME.RELATED_MERCHANT" label="关联店铺" />
      </el-tabs>
      <div v-if="!loading">
        <groupFormBasic :rawOrgInfo="rawOrgInfo" :groupInfo.sync="orgInfo" v-show="activeTab === TAB_NAME.BASIC_INFO"  />
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
import groupFormBasic from './components/group-form-basic.vue';
import merList from '@/components/org/mer-list.vue';
import { ORG_TYPE } from '@/domain/organization/org.enum';
import { getStoreGroupDetailApi, createStoreGroupApi, updateStoreGroupApi } from '@/api/store';

const TAB_NAME = {
  BASIC_INFO: 'basicInfo',
  RELATED_MERCHANT: 'relatedMerchant',
}

export default {
  name: 'merchantGroupingCreate',
  components: {
    groupFormBasic,
    merList,
  },
  data() {
    return {
      TAB_NAME,
      activeTab: TAB_NAME.BASIC_INFO,
      groupId: 0,
      orgInfo: {
        type: ORG_TYPE.ZONE, // 组织类型，0: 区域，1: 商户
        pid: 0, // 父级ID，顶级为 0
        name: '', // 组织名称
        agentId: null, // 组织代理ID
        roleId: null, // 身份权限ID
        commissionType: 0, // 提成类型，0: 默认，1: 单独设置
        commissionRate: 0, // 提成比例，如果 commissionType = 1，则该字段必填
        sort: 0, // 排序
        status: 1, // 状态，1: 启用，0: 禁用
        isShow: 0, // 是否显示 1：显示，0：不显示
        positioningStatus: 0, // 定位状态，1: 启用，0: 禁用
        address: "陕西省西安市未央区凤城八路109号", // 组织中心地址
        longitude: 108.9395398, // 经度 定位启用时，坐标必填
        latitude: 34.3431464, // 纬度 定位启用时，坐标必填
        merIdList: [], // 关联店铺列表
      },

      rawOrgInfo: null, // 原始组织信息

      loading: false,
      submitLoading: false
    }
  },
  created() {
    if (this.$route.query.id) {
      this.groupId = parseInt(this.$route.query.id);
      this.getOrgInfo();
    } else if (this.$route.query.pid) {
      this.orgInfo.pid = parseInt(this.$route.query.pid);
    }
  },
  methods: {
    async handleSubmit() {
      if (this.submitLoading) return;
      this.submitLoading = true;

      const {
        agentId,
        commissionType,
        commissionRate,
        isShow,
        positioningStatus,
        merIdList,
        roleId,
        ...args
      } = this.orgInfo;
      const payload = {
        ...args,
        role_id: roleId,
        circle_agent_id: agentId,
        commission_type: commissionType,
        commission_rate: commissionRate,
        is_show: isShow,
        positioning_status: positioningStatus,
        merchant_ids: merIdList,
      };

      try {
        const task = this.groupId ? updateStoreGroupApi(this.groupId, payload) : createStoreGroupApi(payload);
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
    async getOrgInfo() {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await getStoreGroupDetailApi(this.groupId);
        const {
          circle_agent_id,
          commission_type,
          commission_rate,
          is_show,
          positioning_status,
          merchant,
          role_id,
          ...args
        } = res.data;
        this.orgInfo = {
          ...args,
          agentId: circle_agent_id,
          commissionType: commission_type,
          commissionRate: commission_rate,
          isShow: is_show,
          positioningStatus: positioning_status,
          merIdList: merchant.map(item => item.mer_id),
          roleId: role_id,
        };
        this.rawOrgInfo = JSON.parse(JSON.stringify(this.orgInfo));
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

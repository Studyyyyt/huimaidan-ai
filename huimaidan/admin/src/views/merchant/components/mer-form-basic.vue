<!-- 商户编辑 - 基础信息页面 -->
<template>
  <div>
    <el-form size="small" :model="merInfo" ref="form" label-width="auto" :rules="rules">

      <el-form-item label="商户名称：" prop="name">
        <el-input v-model="merInfo.name" placeholder="请输入商户名称" class="form-item" maxlength="20" show-word-limit />
      </el-form-item>

      <el-form-item label="店铺分类：" prop="business_store_category">
        <el-select v-model="merInfo.business_store_category" placeholder="请选择店铺分类" class="form-item">
          <el-option v-for="item of storeCateList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>

      <el-form-item label="店铺类型：" prop="business_store_type">
        <el-select v-model="merInfo.business_store_type" placeholder="请选择店铺类型" class="form-item">
          <el-option v-for="item of storeTypeList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>

      <el-form-item label="商户管理员：" prop="agentId">
        <div style="width: 460px; display: flex;">
          <el-button type="primary" plain class="mr10" @click="handleClickAddAdminBtn">
            <i class="el-icon-plus"></i>
            添加管理员
          </el-button>

          <el-select filterable remote :remote-method="handleGetAgentList" v-model="merInfo.agentId"
            placeholder="请输入选择商户管理员" class="form-item remote" :loading="agentLoading">
            <el-option v-for="item of agentList" :key="item.circle_agent_id" :label="item.name"
              :value="item.circle_agent_id" />
          </el-select>
        </div>
      </el-form-item>


      <el-form-item label="身份权限：" prop="roleId" ref="roleFormItem">
        <el-select filterable remote :remote-method="handleGetRoleList" v-model="merInfo.roleId" placeholder="请输入选择身份权限"
          class="form-item" :loading="roleLoading">
          <el-option v-for="item of roleList" :key="item.role_id" :label="item.role_name" :value="item.role_id" />
        </el-select>
      </el-form-item>

      <el-form-item label="排序：" prop="sort">
        <CustomInputNumber v-model="merInfo.sort" :precision="0" :step="1" :min="0" :max="9999" />
        <p class="agent-tip">数字越大越靠前</p>
      </el-form-item>

      <el-form-item label="开启状态：" prop="status" class="switch-width-double">
        <el-switch v-model="merInfo.status" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" />
        <p class="agent-tip">关闭后，该商户禁止登录</p>
      </el-form-item>
    </el-form>

    <!-- 新增商户管理员表单 -->
    <mer-admin-edit-form ref="merAdminEditForm" @refresh="handleAdminAddAfter" />
  </div>
</template>

<script>
import { getAgentListApi } from '@/api/agent';
import { ORG_TYPE } from '@/domain/organization/org.enum';
import merAdminEditForm from '@/views/merchant/application/components/admin-edit-form.vue';
import {
  menuRoleApi,
} from "@/api/setting";
import { SUBJECT_TYPE } from '@/domain/subject/subject.enum';
import { getMerCateApi, getstoreTypeApi } from '@/api/merchant';

export default {
  name: 'merFormBasic',
  props: {
    merInfo: Object
  },
  components: {
    merAdminEditForm
  },
  data() {
    return {

      rules: {
        name: {
          message: "请输入商户名称",
          required: true,
          trigger: "blur"
        },
        business_store_category: {
          message: "请选择店铺分类",
          required: true,
          trigger: "change"
        },
        business_store_type: {
          message: "请选择店铺类型",
          required: true,
          trigger: "change"
        },
        agentId: {
          message: "请选择商户管理员",
          required: true,
          trigger: "change"
        },
        roleId: {
          message: "请选择身份权限",
          required: true,
          trigger: "change"
        }
      },

      roleList: [], // 身份列表
      roleLoading: false, // 身份列表加载状态

      agentList: [], // 商户管理员列表
      agentLoading: false, // 商户管理员列表加载状态

      storeCateList: [],
      storeTypeList: [],
    };
  },
  created() {
    this.handleGetRoleList();
    this.handleGetAgentList();
    this.handleGetStoreCateList();
    this.handleGetStoreTypeList();
  },
  methods: {
    async handleGetStoreCateList() {
      try {
        const res = await getMerCateApi();
        this.storeCateList = res.data;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async handleGetStoreTypeList() {
      try {
        const res = await getstoreTypeApi();
        this.storeTypeList = res.data;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 添加管理员后刷新管理员列表
    handleAdminAddAfter() {
      this.handleGetAgentList()
        .then(() => {
          this.merInfo.agentId = this.agentList[0].circle_agent_id;
        });
    },
    // 点击新增管理员按钮
    handleClickAddAdminBtn() {
      this.$refs.merAdminEditForm.open();
    },
    // 获取商户管理员列表
    async handleGetAgentList(query) {
      if (this.agentLoading) return;
      this.agentLoading = true;
      const payload = {
        name: query,
        page: 1,
        limit: 10,
        type: ORG_TYPE.MERCHANT,
      };
      try {
        const res = await getAgentListApi(payload);
        this.agentList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.agentLoading = false;
      }
    },
    // 获取身份列表
    async handleGetRoleList(query) {
      if (this.roleLoading) return;
      this.roleLoading = true;
      const payload = {
        role_name: query,
        page: 1,
        limit: 10,
        is_agent: SUBJECT_TYPE.MERCHANT
      };
      try {
        const res = await menuRoleApi(payload);
        this.roleList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.roleLoading = false;
      }
    },
    validate(callback) {
      return this.$refs.form.validate(callback);
    }
  }
}
</script>

<style scoped lang="scss">
.form-item {
  width: 460px;
}

.agent-tip {
  font-size: 12px;
  color: #909399;
}

.percent-unit {
  display: inline-block;
  color: #909399;
  font-size: 13px;
  transform: translateX(-200%);

  &+span {
    margin-left: -10px;
  }
}

.parent-zone-commission {
  color: var(--prev-color-primary);
  font-size: 12px;
  margin-left: 10px;
}
</style>

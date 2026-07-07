<!-- 商户管理员编辑对话框 -->
<template>
  <form-dialog ref="formDialog" @update="handleUpdate" />
</template>

<script>
import { createAgentApi, updateAgentApi, getAgentDetailApi } from '@/api/agent';
import { getDefaultAdminEditForm, generateAdminEditForm } from '../admin.form.js';
import { ACCOUNT_TYPE } from '@/domain/access/account.enum.js';

export default {
  name: 'merAdminEditForm',
  data() {
    return {
      adminInfo: null
    };
  },
  methods: {
    async handleUpdate([newVal, oldVal]) {
      // 编辑模式下账号字段处于禁用状态，无需进行联动更新
      if (this.adminInfo) return;
      if (!newVal || !newVal.phone) return;
      if (oldVal && oldVal.phone && newVal.phone === oldVal.phone) return;
      this.$refs.formDialog.fApi.setValue({
        account: newVal.phone
      });
    },
    // 获取商户管理员关联用户详情
    async getAgentDetail(agentId) {
      try {
        const res = await getAgentDetailApi(agentId);
        return res.data;
      } catch (error) {
        throw error;
      }
    },
    async handleGenerateEditRule(adminInfo) {
      let userInfo = null;
      if (adminInfo.uid) {
        try {
          const agent = await this.getAgentDetail(adminInfo.circle_agent_id);
          if (agent.user) {
            userInfo = agent.user;
          }
        } catch (error) {
          this.$message.error(error.message);
        }
      }

      const uidValue = userInfo ? {
        id: userInfo.uid,
        src: userInfo.avatar
      } : null;

      const accountValue = adminInfo.admin ? adminInfo.admin.account : null;

      // 将表单默认值转换为表单配置
      const config = {
        uid: {
          value: uidValue,
          props: {
            allowRemove: !uidValue,
            disabled: !!uidValue,
          }
        },
        name: {
          value: adminInfo.name
        },
        phone: {
          value: adminInfo.phone
        },
        account: {
          value: accountValue,
          props: {
            disabled: true
          }
        },
        // status: {
        //   value: adminInfo.status
        // }
      };

      // 生成编辑表单配置
      return generateAdminEditForm(config);
    },
    async open(adminInfo) {
      this.adminInfo = adminInfo;
      let config = null;
      const action = formData => this.handleSubmitAdmin(formData, adminInfo ? adminInfo.circle_agent_id : null);
      if (adminInfo) {
        const rule = await this.handleGenerateEditRule(adminInfo);
        if (!rule) return;
        config = {
          title: "编辑商户管理员",
          rule,
          action
        };
      } else {
        config = {
          title: "添加商户管理员",
          rule: getDefaultAdminEditForm(),
          action
        };
      }

      this.$refs.formDialog.open(config);
    },
    // 添加/编辑商户管理员
    async handleSubmitAdmin(formData, id) {
      const payload = {
        ...formData,
        type: ACCOUNT_TYPE.MERCHANT
      }
      if (formData.uid) {
        payload.uid = formData.uid.id;
      }
      const isEdit = !!id;
      if (isEdit) {
        delete formData.account;
      }
      const task = isEdit ? updateAgentApi(id, payload) : createAgentApi(payload);
      try {
        const res = await task;
        this.$message.success(res.message);
        this.onFormConfirm();
        return true;
      } catch (error) {
        this.$message.error(error.message);
        return false;
      }
    },
    onFormConfirm() {
      this.$emit('refresh');
      setTimeout(() => {
        this.$refs.formDialog.fApi.resetFields();
      }, 200);
    }
  }
}
</script>

<style scoped lang="scss"></style>
<template>
  <el-drawer title="店铺分账详情" :visible.sync="visible" size="800px" :before-close="handleClose" class="dia">
    <div v-loading="loading">
      <template v-if="data">
        <div class="drawer-body">
          <detail-drawer-v1 v-if="isV1" :data="data" />
          <detail-drawer-v2 v-else-if="isV2" :data="data" />
        </div>
        <el-form v-if="data.status == 0" ref="ruleForm" :model="ruleForm" :rules="rules" label-width="80px"
          class="demo-ruleForm">
          <el-form-item label="审核状态" prop="status">
            <el-radio-group v-model="ruleForm.status">
              <el-radio :label="10">通过</el-radio>
              <el-radio :label="-1">拒绝</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item v-if="ruleForm.status === -1" label="原因" prop="message">
            <el-input v-model="ruleForm.message" type="textarea" placeholder="请输入原因" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="onSubmit">提交</el-button>
          </el-form-item>
        </el-form>
      </template>
    </div>
  </el-drawer>
</template>

<script>
import DetailDrawerV1 from "./detail-drawer-v1.vue";
import DetailDrawerV2 from "./detail-drawer-v2.vue";

import {
  applymentDetailApi,
  applymentStatusApi,
} from "@/api/merchant";

export default {
  name: "DetailDrawer",
  components: {
    DetailDrawerV1,
    DetailDrawerV2
  },
  data() {
    return {
      visible: false,
      id: null,
      loading: false,

      data: null,

      ruleForm: {
        message: "",
        status: 10
      },
      rules: {
        status: [
          { required: true, message: "请选择审核状态", trigger: "change" }
        ],
        message: [
          { required: true, message: "请填写拒绝原因", trigger: "blur" }
        ]
      }
    }
  },
  watch: {
    id(newVal) {
      newVal && this.getInfo();
    }
  },
  computed: {
    isV1() {
      return this.data && this.data.type == 0;
    },
    isV2() {
      return this.data && this.data.type == 1;
    }
  },
  methods: {
    async getInfo() {
      this.loading = true;
      try {
        const res = await applymentDetailApi(this.id);
        this.data = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    },
    handleClose() {
      this.visible = false;
    },
    open(id) {
      this.id = id;
      this.visible = true;
    },
    // 审核
    onSubmit() {
      applymentStatusApi(this.data.mer_applyments_id, this.ruleForm)
        .then(res => {
          this.$message.success(res.message);
          this.visible = false;
          this.$emit('refresh');
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
  }
}
</script>

<style scoped>
.drawer-body {
  padding-bottom: 20px;
}
.demo-ruleForm {
  margin-left: 30px;
}
</style>

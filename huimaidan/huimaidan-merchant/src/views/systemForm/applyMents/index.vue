<template>
  <div>
    <!-- 旧版分账表单 -->
    <V1Form v-if="formVersion === 1" />

    <!-- 新版分账表单 -->
    <V2Form v-else-if="formVersion === 2" />
  </div>
</template>

<script>
import V1Form from './components/v1.vue';
import V2Form from './components/v2/index.vue';
import { applymentIndexApi } from '@/api/system';

export default {
  name: "ApplyMents",
  components: {
    V1Form,
    V2Form
  },
  data() {
    return {
      formVersion: null
    };
  },
  created() {
    this.getApplymentIndex();
  },
  methods: {
    async getApplymentIndex() {
      try {
        const res = await applymentIndexApi();
        const isVersion2 = res.data.config && res.data.config.wechat_service_tool == "1";
        this.formVersion = isVersion2 ? 2 : 1;
      } catch (error) {
        this.$message.error(error.message);
      };
    }
  }
}
</script>

<template>
  <div>
    <el-dialog
      title="选择发布作者"
      :visible.sync="dialogVisible"
      width="600px"
      :before-close="handleClose"
    >
      <userList @submit="submit" :api="'getUserlistApi'"></userList>

      <!-- <span slot="footer" class="dialog-footer">
    <el-button @click="dialogVisible = false">取 消</el-button>
    <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
  </span> -->
    </el-dialog>
  </div>
</template>
<script>
import userList from "@/components/userList/index.vue";
export default {
  name: "",
  components: { userList },
  props: {},
  data() {
    return {
      dialogVisible: false
    };
  },
  mounted() {},
  methods: {
    handleClose() {
      this.dialogVisible = false;
    },
    submit(data) {
      this.$emit("getUserData", data);
      this.handleClose();
    },
    openBox() {
      this.dialogVisible = true;
    }
  }
};
</script>
<style scoped lang="scss"></style>

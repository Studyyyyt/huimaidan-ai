<template>
  <el-col style="margin-top: -14px;">
    <el-form-item>
      <div class="form-dialog-tip">{{ tip }}</div>
    </el-form-item>
  </el-col>
</template>

<script>
export default {
  name: 'FormDialogTip',
  props: {
    tip: {
      type: String,
      default: ''
    }
  }
}
</script>

<style scoped lang="scss">
.form-dialog-tip {
  color: #909399;
  font-size: 12px;
  line-height: 20px;
}
</style>

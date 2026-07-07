<template>
  <div class="divBox">
    <el-card class="box-card FromData">
      <div class="auth acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <span class="el-icon-s-opportunity"></span>
          <div class="text">
            <div>版权设置</div>
            <div class="code">请前往“安全维护-版权信息”页面设置底部版权内容。</div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script>
export default {
  name: 'CopyRight',
}
</script>

<style scoped lang="scss">
.el-icon-s-opportunity{
  color: var(--prev-color-primary);
  font-size: 40px;
  margin-right: 10px;
}
.text {
  .code {
    font-size: 14px;
    color: rgba(0, 0, 0, 0.5);
    margin-top: 5px;
  }
}
</style>

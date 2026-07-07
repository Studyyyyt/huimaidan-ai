<template>
  <!-- 页面头部卡片组件 -->
  <el-card shadow="never" class="box-card" :bordered="false">
    <div class="acea-row row-middle">
      <!-- 返回按钮，点击触发 goBack 方法 -->
      <div class="font-sm after-line" @click="goBack">
        <span class="el-icon-arrow-left"></span>
        <span class="pl10">返回</span>
      </div>
      <!-- 显示页面标题 -->
      <span class="ht_title ml10">{{ title }}</span>
    </div>
  </el-card>
</template>

<script>
// 导入路由前缀配置
import { roterPre } from '@/settings'
export default {
  name: 'pagesHeader',
  props: {
    // 页面标题，默认为空字符串
    title: {
      type: String,
      default: '',
    },
    // 返回的路由地址，默认为空字符串
    backUrl: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      // 这里可以添加组件内部的数据
    }
  },
  methods: {
    /**
     * 点击返回按钮时触发的方法
     * 如果 backUrl 存在，则跳转到指定的路由地址
     * 否则返回上一页
     */
    goBack() {
      if (this.backUrl) {
        this.$router.push({
          path: `${roterPre}${this.backUrl}`
        });
      } else {
        this.$router.back();
      }
    },
  },
};
</script>

<style scoped>
.after-line {
  display: inline-block;
  position: relative;
  margin-right: 16px;
  color: rgba(0, 0, 0, 0.85);
  cursor: pointer;
}
.after-line:after {
  content: '';
  position: absolute;
  top: 3px;
  right: -16px;
  width: 1px;
  height: 16px;
  background: #eee;
}
.font-sm {
  font-size: 14px;
}
.ht_title {
  font-weight: 500;
  font-size: 18px;
}
</style>

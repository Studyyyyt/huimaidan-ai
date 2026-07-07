<template>
  <el-breadcrumb class="app-breadcrumb" separator="/">
    <transition-group name="breadcrumb">
      <el-breadcrumb-item v-for="(item,index) in levelList" :key="index">
        <span class="no-redirect">{{ item.meta.title }}</span>
        <!--<span v-if="item.redirect==='noRedirect'||index==levelList.length-1" class="no-redirect">{{ item.meta.title }}</span>-->
        <!--<a v-else @click.prevent="handleLink(item)">{{ item.meta.title }}</a>-->
      </el-breadcrumb-item>
    </transition-group>
  </el-breadcrumb>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import pathToRegexp from 'path-to-regexp'
import { roterPre } from '@/settings'
export default {
  data() {
    return {
      levelList: null,
      roterPre: roterPre
    }
  },
  watch: {
    $route(route) {
      // if you go to the redirect page, do not update the breadcrumbs
      if (route.path.startsWith('/redirect/')) {
        return
      }
      this.getBreadcrumb()
    }
  },
  created() {
    this.getBreadcrumb()
  },
  methods: {
    /**
   * 获取面包屑数据
   */
    getBreadcrumb() {
      // 过滤出带有 meta.title 的路由
      let matched = this.$route.matched.filter(item => item.meta && item.meta.title)
      const first = matched[0]
      // 如果第一个路由不是仪表盘，添加仪表盘路由
      if (!this.isDashboard(first)) {
        matched = [{ path: roterPre + '/dashboard', meta: { title: '控制台' }}].concat(matched)
      }

      this.levelList = matched.filter(item => item.meta && item.meta.title && item.meta.breadcrumb !== false)
    },
    isDashboard(route) {
      const name = route && route.name
      if (!name) {
        return false
      }
      return name.trim().toLocaleLowerCase() === 'Dashboard'.toLocaleLowerCase()
    },
    pathCompile(path) {
      try {
        // 解决路径参数问题
        const { params } = this.$route;
        const toPath = pathToRegexp.compile(path);
        return toPath(params);
      } catch (error) {
        console.error('路径编译出错:', error);
        return path;
      }
    },
    /**
     * 处理面包屑项点击事件
     * @param {Object} item - 面包屑项
     */
    handleLink(item) {
      const { redirect, path } = item
      if (redirect) {
        this.$router.push(redirect)
        return
      }
      this.$router.push(this.pathCompile(path))
    }
  }
}
</script>

<style lang="scss" scoped>
.app-breadcrumb.el-breadcrumb {
  display: inline-block;
  font-size: 14px;
  line-height: 50px;
  margin-left: 8px;

  .no-redirect {
    color: #97a8be;
    cursor: text;
    font-size: 12px;
  }
}
</style>

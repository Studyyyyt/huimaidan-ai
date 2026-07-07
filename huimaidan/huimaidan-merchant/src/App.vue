<template>
  <div v-if="isRouterAlive" id="app">
    <router-view />
    <Setings ref="setingsRef" />
  </div>
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
import Setings from "@/layout/navBars/breadcrumb/setings.vue";
import { Local } from "@/utils/storage.js";

export default {
  name: "App",
  components: { Setings },
  provide() {
    return {
      reload: this.reload
    };
  },
  data() {
    return {
      isRouterAlive: true
    };
  },
  watch: {
    // 监听路由 控制侧边栏显示 标记当前顶栏菜单（如需要）
    $route(to, from) {
      const onRoutes = to.meta.activeMenu ? to.meta.activeMenu : to.meta.path;
      this.$store.commit('menu/setActivePath', onRoutes);
    },
  },
  mounted() {
    this.openSetingsDrawer();
    this.getLayoutThemeConfig();
  },
  methods: {
    reload() {
      this.isRouterAlive = false;
      this.$nextTick(function() {
        this.isRouterAlive = true;
      });
    },
    // 布局配置弹窗打开
    openSetingsDrawer() {
      this.bus.$on("openSetingsDrawer", () => {
        this.$refs.setingsRef.openDrawer();
      });
    },
    // 获取缓存中的布局配置
    getLayoutThemeConfig() {
      if (Local.get("themeConfigPrev")) {
        this.$store.dispatch(
          "themeConfig/setThemeConfig",
          Local.get("themeConfigPrev")
        );
        document.documentElement.style.cssText = Local.get("themeConfigStyle");
      } else {
        Local.set("themeConfigPrev", this.$store.state.themeConfig.themeConfig);
      }
    }
  },
  destroyed() {
    this.bus.$off("openSetingsDrawer");
  }
};
</script>

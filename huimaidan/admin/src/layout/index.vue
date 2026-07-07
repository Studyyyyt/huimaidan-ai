<template>
  <el-main v-if="standalonePage">
    <router-view></router-view>
  </el-main>
  <Mains v-else-if="menusReady && headMenuNoShow" />
  <Defaults v-else-if="menusReady && getThemeConfig.layout === 'defaults'" />
  <Classic v-else-if="menusReady && getThemeConfig.layout === 'classic'" />
  <Transverse v-else-if="menusReady && getThemeConfig.layout === 'transverse'" />
  <Columns v-else-if="menusReady && getThemeConfig.layout === 'columns'" />
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
import { Local } from "@/utils/storage.js";
import { mapMutations } from "vuex";
import { getNewTagList } from "@/utils/util";

export default {
  name: "layout",
  components: {
    Defaults: () => import("@/layout/main/defaults.vue"),
    Classic: () => import("@/layout/main/classic.vue"),
    Transverse: () => import("@/layout/main/transverse.vue"),
    Columns: () => import("@/layout/main/columns.vue"),
    Mains: () => import("@/layout/component/main.vue")
  },
  data() {
    return {
      headMenuNoShow: false,
      menusReady: false
    };
  },
  computed: {
    // 获取布局配置信息
    standalonePage() {
      const routeNameList = [
        "NotificDiyation",
        "DecorationDiyationStore"
      ];
      return routeNameList.includes(this.$route.name);
    },
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    tagNavList() {
      return this.$store.state.menu.tagNavList;
    }
  },
  watch: {
    $route(newRoute) {
      const { name, query, params, meta, path } = newRoute;
      this.addTag({
        route: { name, query, params, meta, path },
        type: "push"
      });
      this.setBreadCrumb(newRoute);
      this.setTagNavList(getNewTagList(this.tagNavList, newRoute));
    }
  },
  created() {
    this.ensureMenus();
    this.onLayoutResize();
    window.addEventListener("resize", this.onLayoutResize);
  },
  methods: {
    ...mapMutations("menu", [
      "setBreadCrumb",
      "setTagNavList",
      "addTag",
      "setLocal",
      "setHomeRoute",
      "closeTag"
    ]),

    ensureMenus() {
      if (this.$store.state.user.menuList && this.$store.state.user.menuList.length) {
        this.menusReady = true;
        return;
      }
      this.$store.dispatch("user/getMenus").finally(() => {
        this.menusReady = true;
      });
    },

    // 窗口大小改变时(适配移动端)
    onLayoutResize() {
      if (!Local.get("oldLayout"))
        Local.set(
          "oldLayout",
          this.$store.state.themeConfig.themeConfig.layout
        );
      const clientWidth = document.body.clientWidth;
      if (clientWidth < 1000) {
        this.$store.state.themeConfig.themeConfig.isCollapse = false;
        this.bus.$emit("layoutMobileResize", {
          layout: "defaults",
          clientWidth
        });
      } else {
        this.bus.$emit("layoutMobileResize", {
          layout: Local.get("oldLayout")
            ? Local.get("oldLayout")
            : this.$store.state.themeConfig.themeConfig.layout,
          clientWidth
        });
      }
    }
  },
  distroyed() {
    window.removeEventListener("resize", this.onLayoutResize);
  }
};
</script>

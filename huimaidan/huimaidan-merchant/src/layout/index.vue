<template>
  <el-main v-if="standalonePage">
    <router-view />
  </el-main>
  <Mains v-else-if="headMenuNoShow" />
  <Defaults v-else-if="getThemeConfig.layout === 'defaults'" />
  <Classic v-else-if="getThemeConfig.layout === 'classic'" />
  <Transverse v-else-if="getThemeConfig.layout === 'transverse'" />
  <Columns v-else-if="getThemeConfig.layout === 'columns'" />
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
      headMenuNoShow: false
    };
  },
  computed: {
    // 获取布局配置信息
    standalonePage() {
      const routeNameList = [
        "NotificDiyation"
      ];
      return routeNameList.includes(this.$route.name);
    },
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    tagNavMerList() {
      return this.$store.state.menu.tagNavMerList;
    }
  },
  watch: {
    $route(newRoute) {
      this.headMenuNoShow = this.$route.meta.fullScreen;
      const { name, query, params, meta, path } = newRoute;
      this.addTag({
        route: { name, query, params, meta, path },
        type: "push"
      });
      this.setBreadCrumb(newRoute);
      this.setTagNavList(getNewTagList(this.tagNavMerList, newRoute));
    }
  },
  created() {
    this.headMenuNoShow = this.$route.meta.fullScreen;
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

    // 窗口大小改变时(适配移动端)
    onLayoutResize() {
      if (!Local.get("oldMerLayout"))
        Local.set(
          "oldMerLayout",
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
          layout: Local.get("oldMerLayout")
            ? Local.get("oldMerLayout")
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

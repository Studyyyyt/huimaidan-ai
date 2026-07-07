<template>
  <div>
    <el-popover placement="bottom" trigger="click" popper-class="zone-selector-wrap" v-if="isAgent" v-model="popoverVisible">
      <div class="zone-selector">
        <p class="selector-title">{{ title }}</p>
        <div class="selector-content" @click="handleListClick">
          <template v-for="(zone, index) of zoneList">
            <div class="divider-box" v-if="index % 2 !== 0">
              <el-divider direction="vertical" class="divider-vertical"/>
            </div>
            <div class="zone-item" :class="{ active: zone.circle_id === zoneId }" :data-id="zone.circle_id">
              <span class="line1" :data-id="zone.circle_id">
                {{ zone.name }}
              </span>
            </div>
          </template>
        </div>
      </div>
      <div slot="reference">
        <slot :zoneName="zoneName" />
      </div>
    </el-popover>
    <slot v-else />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
export default {
  name: 'zoneSelector',
  data() {
    return {
      popoverVisible: false
    };
  },
  computed: {
    ...mapGetters("user", ["isAgent", "zoneList", "zoneId", "zoneName", "isMerAdmin"]),
    title() {
      const userType = this.isMerAdmin ? '商户' : '区域';
      return `${userType}(${this.zoneList.length})`;
    }
  },
  methods: {
    async handleListClick(e) {
      const id = e.target.dataset.id;
      if (id === undefined) return;
      this.$store.commit("user/SET_ZONE_ID", Number(id));
      this.popoverVisible = false;
      
      // 刷新菜单列表
      await this.$store.dispatch("user/getMenus");
      
      // 通知关闭所有遗留路由标签
      this.bus.$emit("onCurrentContextmenuClick", { id: 3 });

      // 通知侧边栏更新路由列表
      this.bus.$emit("routesListChange");
      
      // 查找第一个有效路由
      const findFirstRoutePath = (routeList) => {
        const firstRoute = routeList[0];
        if (firstRoute.children && firstRoute.children.length) {
          return findFirstRoutePath(firstRoute.children);
        }
        return firstRoute.path;
      };

      // 导航到第一个有效路由
      const route = findFirstRoutePath(this.$store.state.user.menuList);
      this.$router.replace(route);
    }
  }
}
</script>

<style scoped lang="scss">
.zone-selector {
  width: 260px;
  max-height: 310px;
  overflow: auto;
}

.selector-title {
  font-weight: 500;
  font-size: 14px;
  color: #666666;
  position: sticky;
  top: 0;
  margin-bottom: 10px;
  background-color: #fff;
}

.selector-content {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 0;
}

.divider-box {
  width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zone-item {
  height: 28px;
  font-size: 12px;
  border-radius: 4px;
  flex: 0 1 calc(50% - 10px);
  display: flex;
  align-items: center;
  padding-inline: 10px;
  cursor: pointer;
  transition: all .3s;
  overflow: hidden;

  &.active {
    background-color: rgba(55, 125, 255, 0.1);
  }

  &:hover {
    background-color: #F7F7F7 !important;
  }
}

.divider-vertical {
  background-color: #eee;
}
</style>

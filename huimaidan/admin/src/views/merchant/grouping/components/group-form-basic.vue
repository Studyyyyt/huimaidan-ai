<!-- 分组编辑 - 基础信息页面 -->
<template>
  <div>
    <el-form size="small" :model="groupInfo" ref="form" label-width="90px" :rules="rules">
      <el-form-item label="上级分组：" prop="pid">
        <el-cascader :options="cascaderOptions" v-model="groupInfo.pid" :props="parentZoneProps" placeholder="请选择上级分组"
          class="form-item" filterable></el-cascader>
      </el-form-item>

      <el-form-item label="分组名称：" prop="name">
        <el-input v-model="groupInfo.name" placeholder="请输入分组名称" class="form-item" maxlength="20" show-word-limit />
      </el-form-item>

      <el-form-item label="排序：" prop="sort">
        <CustomInputNumber v-model="groupInfo.sort" :precision="0" :step="1" :min="0" :max="9999" />
        <p class="agent-tip">数字越大越靠前</p>
      </el-form-item>

      <el-form-item label="开启状态：" prop="status" class="switch-width-double">
        <el-switch v-model="groupInfo.status" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" />
        <p class="agent-tip">关闭后，移动端商城不展示该分组</p>
      </el-form-item>


      <el-form-item label="区域定位：" prop="positioningStatus" class="switch-width-double">
        <el-switch v-model="groupInfo.positioningStatus" :active-value="1" :inactive-value="0" active-text="开启"
          inactive-text="关闭" />
        <p class="agent-tip">开启定位后，用户进入商城选择区域，会自动根据用户定位距离区域中心进行推荐</p>
      </el-form-item>

      <el-form-item v-if="groupInfo.positioningStatus === 1" label="区域中心：" required>
        <el-input placeholder="请输入区域中心地址" class="form-item" @change="handleSearchAddress" v-model="groupInfo.address">
          <el-button slot="append">查找位置</el-button>
        </el-input>
        <div style="width: 800px;" class="mt-14">
          <Map v-if="mapKey" :mapKey="mapKey" :location="location" @updateLocation="handleUpdateLocation"
            :address="searchAddress" />
        </div>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { getStoreGroupListApi, getStoreGroupDetailApi } from '@/api/store';
import MapX from '@/components/map/Map.vue';
import { configApi } from "@/api/systemForm";
import { ORG_TYPE } from '@/domain/organization/org.enum';

const getTopLevelZone = () => {
  return {
    store_group_id: 0,
    name: '顶级分组'
  };
}

export default {
  name: 'groupFormBasic',
  props: {
    rawOrgInfo: Object,
    groupInfo: Object
  },
  components: {
    Map: MapX,
  },
  data() {
    return {
      ORG_TYPE,

      searchAddress: '', // 搜索地址
      rules: {
        pid: {
          message: "请选择上级分组",
          required: true,
          trigger: "change"
        },
        name: {
          message: "请输入分组名称",
          required: true,
          trigger: "blur"
        }
      },
      parentZoneProps: { // 上级分组级联选择器配置
        checkStrictly: true,
        value: 'store_group_id',
        label: 'name',
        emitPath: false,
        children: 'children'
      },
      orgList: [], // 分组列表


      mapKey: null,

      parentZoneInfo: null, // 上级分组信息
    };
  },
  computed: {
    isZone() {
      return this.groupInfo.type === ORG_TYPE.ZONE;
    },

    // 分组中心位置
    location() {
      return {
        lat: this.groupInfo.latitude,
        lng: this.groupInfo.longitude
      }
    },
    // 仅包含一级和二级的分组列表
    twoLevelOrgList() {
      const list = this.orgList.map(item => {
        if (item.children && item.children.length) {
          return {
            ...item,
            children: item.children.map(({ children, ...args }) => args)
          }
        }
        return item;
      });
      return [
        getTopLevelZone(),
        ...list
      ];
    },
    // 分组信息映射表
    orgMetaMap() {
      const map = new Map();
      const buildMap = list => {
        for (const org of list) {
          map.set(org.store_group_id, org);
          if (org.children && org.children.length) {
            buildMap(org.children);
          }
        }
      }
      buildMap(this.orgList);
      return map;
    },
    // 分组级联选择器选项
    cascaderOptions() {
      // 新建分组时，仅显示一级和二级的分组列表
      if (!this.rawOrgInfo) return this.twoLevelOrgList;

      const selfId = this.rawOrgInfo.store_group_id;
      const groupInfo = this.orgMetaMap.get(selfId);
      if (!groupInfo) return [];
      // 计算当前分组子层级深度 (0:无子, 1:有子, 2:有孙)
      const getDepth = (node) => {
        if (!node.children || !node.children.length) return 0;
        return 1 + Math.max(...node.children.map(getDepth));
      };

      const depth = getDepth(groupInfo);
      const topLevel = getTopLevelZone();
      // 如果有两层子分组，只能放入顶层
      if (depth >= 2) {
        return [topLevel];
      }

      // 根据深度决定是否显示二级选项
      const showLevel2 = depth === 0;
      const filterList = (list) => {
        return list.reduce((arr, item) => {
          if (item.store_group_id === selfId) return arr;

          const newItem = { ...item };
          if (showLevel2 && newItem.children && newItem.children.length) {
            const children = newItem.children
              .filter(c => c.store_group_id !== selfId)
              .map(({ children, ...rest }) => rest);

            if (children.length) {
              newItem.children = children;
            } else {
              delete newItem.children;
            }
          } else {
            delete newItem.children;
          }

          arr.push(newItem);
          return arr;
        }, []);
      };

      return [topLevel, ...filterList(this.orgList)];
    }
  },
  watch: {
    "groupInfo.pid": {
      handler() {
        this.handlegetParentZoneInfo();
      },
      immediate: true,
    }
  },
  created() {
    this.getMapKey();
    this.handleGetZoneList();
  },
  methods: {
    // 获取上级分组信息
    async handlegetParentZoneInfo() {
      if (this.groupInfo.pid === 0) {
        this.parentZoneInfo = null;
      } else {
        try {
          const res = await getStoreGroupDetailApi(this.groupInfo.pid);
          this.parentZoneInfo = res.data;
        } catch (error) {
          this.$message.error(error.message);
          this.parentZoneInfo = null;
        }
      }
    },
    validate(callback) {
      return this.$refs.form.validate(callback);
    },
    // 获取分组列表
    async handleGetZoneList() {
      try {
        const res = await getStoreGroupListApi();
        this.orgList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 更新位置
    handleUpdateLocation({ location, address }) {
      const { lat, lng } = location;
      this.groupInfo.latitude = lat;
      this.groupInfo.longitude = lng;
      if (address) {
        this.groupInfo.address = address;
      }
    },
    // 获取地图key
    async getMapKey() {
      try {
        const res = await configApi('extend_tabs');
        const mapConfig = res.data.rule[0].children.find(i => i.props.name === "tab_map");
        this.mapKey = mapConfig.children[0].value;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 搜索地址
    handleSearchAddress(e) {
      this.searchAddress = e;
    },
  }
}
</script>

<style scoped lang="scss">
.form-item {
  width: 460px;
}

.agent-tip {
  font-size: 12px;
  color: #909399;
}

.percent-unit {
  display: inline-block;
  color: #909399;
  font-size: 13px;
  transform: translateX(-200%);

  &+span {
    margin-left: -10px;
  }
}

.parent-zone-commission {
  color: var(--prev-color-primary);
  font-size: 12px;
  margin-left: 10px;
}
</style>

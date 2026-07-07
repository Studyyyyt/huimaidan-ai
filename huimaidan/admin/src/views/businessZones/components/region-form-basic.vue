<!-- 区域编辑 - 基础信息页面 -->
<template>
  <div>
    <el-form size="small" :model="orgInfo" ref="form" label-width="90px" :rules="rules">
      <el-form-item label="上级区域：" prop="pid">
        <el-cascader :options="cascaderOptions" v-model="orgInfo.pid" :props="parentZoneProps" placeholder="请选择上级区域"
          class="form-item" filterable></el-cascader>
      </el-form-item>

      <el-form-item label="区域名称：" prop="name">
        <el-input v-model="orgInfo.name" placeholder="请输入区域名称" class="form-item" maxlength="20" show-word-limit />
      </el-form-item>

      <el-form-item label="区域代理：" prop="agentId">
        <div style="width: 460px; display: flex;">
          <el-button type="primary" plain class="mr10" @click="handleClickAddAdminBtn">
            <i class="el-icon-plus"></i>
            添加代理人
          </el-button>

          <el-select filterable remote :remote-method="handleGetAgentList" v-model="orgInfo.agentId"
            placeholder="请输入选择代理人姓名" class="form-item remote" :loading="agentLoading">
            <el-option v-for="item of agentList" :key="item.circle_agent_id" :label="item.name"
              :value="item.circle_agent_id" />
          </el-select>
        </div>
        <p class="agent-tip">区域代理可以查看/管理该区域下的店铺数据。</p>
      </el-form-item>

      <el-form-item label="身份权限：" prop="roleId" ref="roleFormItem">
        <el-select filterable remote :remote-method="handleGetRoleList" v-model="orgInfo.roleId" placeholder="请输入选择身份权限"
          class="form-item" :loading="roleLoading">
          <el-option v-for="item of roleList" :key="item.role_id" :label="item.role_name" :value="item.role_id" />
        </el-select>
      </el-form-item>

      <el-form-item label="代理提成：" prop="commissionType">
        <el-radio-group v-model="orgInfo.commissionType">
          <el-radio :label="0">默认设置</el-radio>
          <el-radio :label="1">单独设置</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item label="提成比例：" prop="commissionRate" v-show="orgInfo.commissionType === 1">
        <el-input-number v-model="orgInfo.commissionRate" :precision="2" :step="0.01" :min="0" :max="100"
          :controls="false" class="form-item" />
        <span class="percent-unit">%</span>
        <el-popover placement="top" width="1000" trigger="hover" @show="handleReflowPopover" ref="rulePopover">
          <zoneCommissionTable />
          <i class="el-icon-info" slot="reference"></i>
        </el-popover>
        <p class="agent-tip">
          下级区域订单代理提成=平台抽成*（本级提成比例-下级提成比例）；本区域店铺订单代理提成=平台抽成*本级提成比例
          <span class="parent-zone-commission" v-if="parentZoneInfo">上级区域提成：{{ parentZoneInfo.commission_rate
            }}%</span>
        </p>
      </el-form-item>

      <el-form-item label="排序：" prop="sort">
        <CustomInputNumber v-model="orgInfo.sort" :precision="0" :step="1" :min="0" :max="9999" />
        <p class="agent-tip">数字越大越靠前</p>
      </el-form-item>

      <el-form-item label="开启状态：" prop="status" class="switch-width-double">
        <el-switch v-model="orgInfo.status" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" />
        <p class="agent-tip">关闭后，该区域禁止登录</p>
      </el-form-item>
    </el-form>

    <!-- 新增区域代理表单 -->
    <agent-edit-form :visible.sync="agentFormVisible" @close="handleCloseAgentFormPanel"
      @refresh="handleAdminAddAfter" />
  </div>
</template>

<script>
import { getAgentListApi } from '@/api/agent';
import { getBusinessZoneListApi, getBusinessZoneDetailApi } from '@/api/business-zone';
import AgentEditForm from '@/views/businessZones/components/agentEditForm.vue';
import zoneCommissionTable from '@/views/businessZones/components/zoneCommissionTable.vue';
import { ORG_TYPE } from '@/domain/organization/org.enum';
import { menuRoleApi } from "@/api/setting";
import { ACCOUNT_TYPE } from '@/domain/access/account.enum';
import { SUBJECT_TYPE } from '@/domain/subject/subject.enum';

const getTopLevelZone = () => {
  return {
    circle_id: 0,
    name: '顶级区域'
  };
}

export default {
  name: 'orgEditFormBasic',
  props: {
    orgInfo: Object,
    rawOrgInfo: Object
  },
  components: {
    AgentEditForm,
    zoneCommissionTable
  },
  data() {
    return {
      ORG_TYPE,

      searchAddress: '', // 搜索地址
      rules: {
        pid: {
          message: "请选择上级区域",
          required: true,
          trigger: "change"
        },
        name: {
          message: "请输入区域名称",
          required: true,
          trigger: "blur"
        },
        agentId: {
          message: "请选择区域代理",
          required: true,
          trigger: "change"
        },
        roleId: {
          message: "请选择身份权限",
          required: true,
          trigger: "change"
        }
      },
      parentZoneProps: { // 上级区域级联选择器配置
        checkStrictly: true,
        value: 'circle_id',
        label: 'name',
        emitPath: false,
        children: 'children'
      },
      orgList: [], // 区域列表
      agentList: [], // 代理人列表
      agentLoading: false, // 代理人列表加载状态
      agentFormVisible: false, // 代理人表单面板是否可见


      roleList: [], // 身份列表
      roleLoading: false, // 身份列表加载状态

      parentZoneInfo: null, // 上级区域信息
    };
  },
  computed: {
    // 仅包含一级和二级的区域列表
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
    // 区域信息映射表
    orgMetaMap() {
      const map = new Map();
      const buildMap = list => {
        for (const org of list) {
          map.set(org.circle_id, org);
          if (org.children && org.children.length) {
            buildMap(org.children);
          }
        }
      }
      buildMap(this.orgList);
      return map;
    },
    // 区域级联选择器选项
    cascaderOptions() {
      // 新建区域时，仅显示一级和二级的区域列表
      if (!this.rawOrgInfo) return this.twoLevelOrgList;

      const selfId = this.rawOrgInfo.circle_id;
      const orgInfo = this.orgMetaMap.get(selfId);
      if (!orgInfo) return [];
      // 计算当前区域子层级深度 (0:无子, 1:有子, 2:有孙)
      const getDepth = (node) => {
        if (!node.children || !node.children.length) return 0;
        return 1 + Math.max(...node.children.map(getDepth));
      };

      const depth = getDepth(orgInfo);
      const topLevel = getTopLevelZone();
      console.log(depth);
      // 如果有两层子区域，只能放入顶层
      if (depth >= 2) {
        return [topLevel];
      }

      // 根据深度决定是否显示二级选项
      const showLevel2 = depth === 0;

      const filterList = (list) => {
        return list.reduce((arr, item) => {
          if (item.circle_id === selfId) return arr;

          const newItem = { ...item };
          if (showLevel2 && newItem.children && newItem.children.length) {
            const children = newItem.children
              .filter(c => c.circle_id !== selfId)
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
    "orgInfo.pid": {
      handler() {
        this.handlegetParentZoneInfo();
      },
      immediate: true,
    }
  },
  created() {
    this.handleGetZoneList();
    this.handleGetAgentList();
    this.handleGetRoleList();
  },
  methods: {
    // 获取上级区域信息
    async handlegetParentZoneInfo() {
      if (this.orgInfo.pid === 0) {
        this.parentZoneInfo = null;
      } else {
        try {
          const res = await getBusinessZoneDetailApi(this.orgInfo.pid);
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
    // 获取区域列表
    async handleGetZoneList() {
      try {
        const res = await getBusinessZoneListApi({
          type: ORG_TYPE.ZONE,
        });
        this.orgList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    // 更新位置
    handleUpdateLocation({ location, address }) {
      const { lat, lng } = location;
      this.orgInfo.latitude = lat;
      this.orgInfo.longitude = lng;
      if (address) {
        this.orgInfo.address = address;
      }
    },
    // 搜索地址
    handleSearchAddress(e) {
      this.searchAddress = e;
    },
    // 重绘提成比例弹窗
    handleReflowPopover() {
      setTimeout(() => {
        this.$refs.rulePopover.updatePopper();
      });
    },
    // 添加管理员后刷新管理员列表
    handleAdminAddAfter() {
      this.handleGetAgentList()
        .then(() => {
          this.orgInfo.agentId = this.agentList[0].circle_agent_id;
        });
    },
    // 关闭区域代理表单面板
    handleCloseAgentFormPanel() {
      this.agentFormVisible = false;
    },
    // 打开区域代理表单面板
    handleOpenAgentFormPanel() {
      this.agentFormVisible = true;
    },
    // 点击新增管理员按钮
    handleClickAddAdminBtn() {
      this.handleOpenAgentFormPanel();
    },
    // 获取代理列表
    async handleGetAgentList(query) {
      if (this.agentLoading) return;
      this.agentLoading = true;
      try {
        const res = await getAgentListApi({
          name: query,
          type: ACCOUNT_TYPE.ZONE,
          status: 1,
          page: 1,
          limit: 10
        });
        this.agentList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.agentLoading = false;
      }
    },
    // 获取身份列表
    async handleGetRoleList(query) {
      if (this.roleLoading) return;
      this.roleLoading = true;
      const payload = {
        role_name: query,
        page: 1,
        limit: 10,
        status: 1,
        is_agent: SUBJECT_TYPE.REGION
      };
      try {
        const res = await menuRoleApi(payload);
        this.roleList = res.data.list;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.roleLoading = false;
      }
    }
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

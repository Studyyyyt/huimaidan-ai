<template>
  <div class="divBox">
    <el-card>
      <el-tabs v-model="tabActive" @tab-click="handleTabClick" v-if="!isAgent">
        <el-tab-pane v-for="item in tabList" :key="item.value" :label="item.name" :name="item.value + ''">
        </el-tab-pane>
      </el-tabs>
      <div class="mb20">
        <el-button size="small" type="primary" @click="onAdd(0)"
          >添加菜单</el-button
        >
        <el-button plain size="small" @click="toggleExpandAll"
          >展开/收起</el-button
        >
      </div>
      <el-table
        :key="tabActive"
        v-loading="listLoading"
        v-if="refreshTable"
        :data="tableData.data"
        size="small"
        row-key="menu_id"
        highlight-current-row
        ref="table"
        :default-expand-all="isExpandAll"
        lazy
        :load="handleLazyLoadChildren"
        :tree-props="{ children: isExpandAll ? 'children' : 'vb732', hasChildren: 'children' }"
      >
        <!-- 必须使用懒加载加载展开的数据，否则数据量过大时会卡顿 -->
        <!-- children 指向一个不存在的属性，以免 table 不使用懒加载，直接使用 children 的数据 -->
        <!-- hasChildren 指向 children，原始数据中拥有 children 属性数据才能进行展开 -->

        <el-table-column label="菜单名称" min-width="200">
          <template slot-scope="scope">
            <span>{{
              scope.row.menu_name + "  [ " + scope.row.menu_id + "  ]"
            }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="route" label="菜单地址" min-width="150" />
        <el-table-column label="菜单图标" min-width="120">
          <template slot-scope="scope">
            <div class="listPic">
              <i
                :class="'el-icon-' + scope.row.icon"
                style="font-size: 20px;"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column prop="sort" label="排序" min-width="100" />
        <el-table-column label="操作" min-width="120" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              :disabled="isChecked"
              @click="onAdd(scope.row.menu_id)"
              >添加子菜单</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onEdit(scope.row.menu_id)"
              >编辑</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.menu_id, scope.row.pid)"
              >删除</el-button
            >
          </template>
        </el-table-column>
      </el-table>
    </el-card>
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
import {
  menuListApi,
  menuCreateApi,
  menuUpdateApi,
  menuDeleteApi
} from "@/api/system";
import { SUBJECT_TYPE } from "@/domain/subject/subject.enum";
import { mapGetters } from "vuex";

export default {
  name: "Menu",
  data() {
    return {
      isChecked: false,
      listLoading: true,
      // 是否展开，默认全部折叠
      isExpandAll: false,
      // 重新渲染表格状态
      tableHeight: 0,
      refreshTable: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20
      },

      tabList: [
        {
          name: "平台",
          value: SUBJECT_TYPE.PLATFORM
        },
        {
          name: "商户",
          value: SUBJECT_TYPE.MERCHANT
        },
        {
          name: "区域",
          value: SUBJECT_TYPE.REGION
        }
      ],
      tabActive: SUBJECT_TYPE.PLATFORM
    };
  },
  computed: {
    ...mapGetters("user", ["isAgent", "zoneId", "roleType"]),
    formQuery() {
      // 非平台用户，则传递角色类型和对应ID
      if (this.isAgent) {
        return {
          is_agent: this.roleType,
          circle_id: this.zoneId
        };
      } else {
        // 平台用户，则传递选中的 tab 值
        return {
          is_agent: this.tabActive,
        };
      }
    }
  },
  mounted() {
    this.getList();
  },
  methods: {
    handleTabClick() {
      this.getList();
    },
    handleLazyLoadChildren(row, treeNode, resolve) {
      if (!this._lazyResolveMap) this._lazyResolveMap = {};
      this._lazyResolveMap[row.menu_id] = resolve;
      resolve(row.children);
    },
    // 列表
    getList() {
      this.listLoading = true;
      return menuListApi({
        ...this.tableFrom,
        ...this.formQuery
      })
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableData.page = page;
      this.getList();
    },
    handleSizeChange(val) {
      this.tableData.limit = val;
      this.getList();
    },
    /** 展开/折叠操作 */
    toggleExpandAll() {
      this.refreshTable = false;
      this.isExpandAll = !this.isExpandAll;
      this.$nextTick(() => {
        this.refreshTable = true;
      });
    },
    // 添加
    onAdd(id) {
      const config = {};
      if (Number(id) > 0) config.formData = { pid: id };
      this.$modalForm(menuCreateApi(this.formQuery), config).then(() => {
        this.getList()
          .then(() => {
            if (!config.formData || config.formData.pid === 0) return;
            this.updateLazyTreeNode(config.formData.pid);
          });
        this.$store.dispatch("user/getMenus");
      });
    },
    // 编辑
    onEdit(id) {
      let formData = null;
      const callback = (_formData) => {
        formData = _formData;
      };
      this.$modalForm(menuUpdateApi(id, this.formQuery), { callback }).then(() => {
        this.getList()
          .then(() => {
            if (!formData || formData.pid === 0) return;
            this.updateLazyTreeNode(formData.pid);
          });
        this.$store.dispatch("user/getMenus");
      });
    },
    updateLazyTreeNode(menuId) {
      const resolve = this._lazyResolveMap[menuId];
      if (!resolve) return;

      const menu = this.findMenuById(menuId);
      menu && resolve(menu.children);
    },
    findMenuById(id) {
      const findMenu = list => {
        for (const item of list) {
          if (item.menu_id === id) return item;
          if (item.children && item.children.length) {
            const menu = findMenu(item.children);
            if (menu) return menu;
          }
        }
      }
      const menu = findMenu(this.tableData.data);
      return menu;
    },
    // 删除
    handleDelete(id, pid) {
      this.$modalSure("删除菜单吗").then(() => {
        menuDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList()
              .then(() => {
                if (pid === 0) return;
                this.updateLazyTreeNode(pid);
              });
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    }
  }
};
</script>

<style scoped lang="scss">
// use_form.scss;
</style>

<template>
  <div class="divBox">
    <el-card>
      <el-tabs v-model="tabActive" @tab-click="handleTabClick" v-if="!isAgent">
        <el-tab-pane v-for="item in tabList" :key="item.value" :label="item.name" :name="item.value + ''">
        </el-tab-pane>
      </el-tabs>
      <div class="mb20">
        <el-button size="small" type="primary" @click="onAdd"
          >添加身份管理</el-button
        >
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="role_id" label="ID" min-width="60" />
        <el-table-column prop="role_name" label="身份名称" min-width="150" />
        <el-table-column prop="role_type" label="身份类型" min-width="150">
          <template #default="{ row }">
            <template v-if="SUBJECT_TYPE_META[row.is_agent]">
              <el-tag size="mini" :style="getRoleTypeStyle(row.is_agent)">{{ SUBJECT_TYPE_META[row.is_agent].label }}</el-tag>
            </template>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="是否开启" min-width="150">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              :width="55"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column prop="update_time" label="更新时间" min-width="150" />
        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              @click="onEdit(scope.row.role_id)"
              >编辑</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.role_id, scope.$index)"
              >删除</el-button
            >
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
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
  menuRoleApi,
  roleCreateApi,
  roleUpdateApi,
  roleDeleteApi,
  roleStatusApi
} from "@/api/setting";
import { mapGetters } from "vuex";
import { SUBJECT_TYPE } from "@/domain/subject/subject.enum";
import { SUBJECT_TYPE_META,  SUBJECT_TYPE_LIST } from "@/domain/subject/subject.meta";
export default {
  name: "SystemRole",
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24
      },
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        page: 1,
        limit: 20
      },
      tabList: SUBJECT_TYPE_LIST,
      tabActive: SUBJECT_TYPE.PLATFORM,
      SUBJECT_TYPE_META
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
    getRoleTypeStyle(roleType) {
      const { color, bgColor } = SUBJECT_TYPE_META[roleType];
      return {
        color: color,
        backgroundColor: bgColor
      };
    },
    handleTabClick() {
      this.getList();
    },
    // 列表
    getList() {
      this.listLoading = true;
      menuRoleApi({
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
      this.tableFrom.page = page;
      this.getList();
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList();
    },
    onchangeIsShow(row) {
      roleStatusApi(row.role_id, row.status)
        .then(({ message }) => {
          this.$message.success(message);
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    // 添加
    onAdd() {
      this.$modalForm(roleCreateApi(this.formQuery)).then(() => this.getList());
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(roleUpdateApi(id, this.formQuery)).then(() => this.getList());
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure().then(() => {
        roleDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1);
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

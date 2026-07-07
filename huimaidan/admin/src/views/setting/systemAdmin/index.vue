<template>
  <el-card>
    <div class="div-count">
      <!-- <div class="Nav">
        <div class="trees-coadd">
          <div class="scollhide">
            <div class="trees">
              <el-tree
                ref="tree"
                :data="treeData2"
                node-key="region_id"
                :highlight-current="true"
                :expand-on-click-node="false"
                :default-expanded-keys="[expandedId]"
                :props="defaultProps"
                @node-click="appendBtn"
                :current-node-key="treeId"
              >
                <div slot-scope="{ node, data }" class="custom-tree-node">
                  <div>
                    <span class="line1 node-label">{{ node.label }}</span>
                  </div>
                  <span>
                    <el-dropdown @command="command => clickMenu(data, command)">
                      <span class="add el-icon-more" />
                      <template slot="dropdown">
                        <el-dropdown-menu>
                          <el-dropdown-item command="1">新增</el-dropdown-item>
                          <el-dropdown-item v-if="data.region_id" command="2"
                            >编辑</el-dropdown-item
                          >
                          <el-dropdown-item v-if="data.region_id" command="3"
                            >删除</el-dropdown-item
                          >
                        </el-dropdown-menu>
                      </template>
                    </el-dropdown>
                  </span>
                </div>
              </el-tree>
            </div>
          </div>
        </div>
      </div> -->
      <div class="colLeft">
        <div class="col-pad">
          <el-form
            :model="tableFrom"
            ref="searchForm"
            size="small"
            label-width="80px"
            :inline="true"
          >
            <el-form-item label="选择时间：">
              <el-date-picker
                v-model="timeVal"
                type="daterange"
                start-placeholder="开始时间"
                end-placeholder="结束时间"
                format="yyyy/MM/dd"
                value-format="yyyy/MM/dd"
                clearable
                style="width: 230px;"
                :picker-options="pickerOptions"
                @change="onchangeTime"
              />
            </el-form-item>
            <el-form-item label="账号状态：" prop="status">
              <el-select
                v-model="tableFrom.status"
                clearable
                placeholder="请选择"
                class="miniWidth"
                @change="getList(1)"
              >
                <el-option label="正常" value="1" />
                <el-option label="停用" value="0" />
              </el-select>
            </el-form-item>
            <el-form-item label="关键字：" prop="keyword">
              <el-input
                v-model="tableFrom.keyword"
                @keyup.enter.native="getList(1)"
                placeholder="请输入账号/呢称"
                clearable
                class="miniWidth"
              />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" size="small" @click="getList(1)"
                >搜索</el-button
              >
              <el-button size="small" @click="searchReset()">重置</el-button>
            </el-form-item>
          </el-form>
        </div>
        <div class="col-pad divBox">
          <div class="mb20">
            <el-button size="small" type="primary" @click="add"
              >添加管理员</el-button
            >
          </div>
          <el-table
            v-loading="listLoading"
            :data="tableData.data"
            style="width: 100%"
            size="small"
          >
            <el-table-column prop="admin_id" label="ID" min-width="60" />
            <el-table-column
              prop="real_name"
              label="管理员姓名"
              min-width="100"
            />
            <el-table-column prop="rule_name" label="身份" min-width="100" />
            <el-table-column
              prop="region_name"
              label="所属区域"
              min-width="100"
            >
              <template #default="{ row }">
                <el-tag size="small" v-for="region of row.region_name" :key="region.circle_id">{{ region.name }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="account" label="账号" min-width="100" />
            <el-table-column prop="phone" label="手机号" min-width="100" />
            <el-table-column prop="status" label="帐号状态" min-width="100">
              <template slot-scope="scope">
                <el-switch
                  v-model="scope.row.status"
                  :active-value="1"
                  :inactive-value="0"
                  active-text="正常"
                  inactive-text="停用"
                  @change="onchangeIsShow(scope.row)"
                />
              </template>
            </el-table-column>
            <el-table-column
              prop="create_time"
              label="创建时间"
              min-width="150"
            />
            <el-table-column label="操作" min-width="180" fixed="right">
              <template slot-scope="scope">
                <el-button
                  type="text"
                  size="small"
                  @click="onPassword(scope.row.admin_id)"
                  >修改密码</el-button
                >
                <el-button
                  type="text"
                  size="small"
                  @click="edit(scope.row.admin_id)"
                  >编辑</el-button
                >
                <el-button
                  type="text"
                  size="small"
                  @click="handleDelete(scope.row.admin_id, scope.$index)"
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
        </div>
      </div>
    </div>
  </el-card>
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
  adminListApi,
  adminCreateApi,
  adminUpdateApi,
  adminDeleteApi,
  adminStatusApi,
  adminPasswordApi
} from "@/api/setting";
import {
  merchantGroupLst,
  merchantGroupAdd,
  merchantGroupEdit,
  merchantGroupDelete
} from "@/api/merchant";
import timeOptions from "@/utils/timeOptions";
import { mapGetters } from "vuex";

export default {
  name: "SystemRole",
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        page: 1,
        limit: 20,
        keyword: "",
        status: "",
        date: "",
        region_id: ""
      },
      timeVal: [],
      treeId: 0,
      treeData: [],
      treeData2: [],
      defaultProps: {
        children: "children",
        label: "name"
      },
      expandedId: ""
    };
  },
  mounted() {
    // this.getCategory();
    this.getList();
  },
  computed: {
    ...mapGetters("user", ["isAgent", "zoneId"]),
    formQuery() {
      if (!this.isAgent) return;
      return {
        is_agent: 1,
        region_ids: this.zoneId
      };
    }
  },
  methods: {
    // 所有分类
    getCategory() {
      const data = {
        name: "全部",
        region_id: ""
      };
      merchantGroupLst({ status: 1 })
        .then(res => {
          this.treeData2 = JSON.parse(JSON.stringify([...res.data]));
          this.treeData = res.data;
          this.treeData.unshift(data);
          this.treeData2 = [...this.treeData];
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 点击菜单
    clickMenu(data, name) {
      if (name == 1) {
        this.onAdd(data.region_id);
      } else if (name == 2) {
        this.onEdit(data.region_id);
      } else if (name == 3) {
        this.onDelete(data.region_id);
      }
    },
    // 添加分组
    onAdd(id) {
      this.treeId = id;
      const config = {};
      if (Number(id) > 0)
        config.formData = {
          pid: id
        };
      this.$modalForm(merchantGroupAdd(), config).then(({ message }) => {
        // this.$message.success(message)
        this.getCategory();
      });
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(merchantGroupEdit(id)).then(() => {
        this.expandedId = id;
        this.getCategory();
      });
    },
    // 删除
    onDelete(id) {
      this.$modalSure().then(() => {
        merchantGroupDelete(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getCategory();
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    /**账号状态切换 */
    onchangeIsShow(row) {
      adminStatusApi(row.admin_id, row.status)
        .then(({ message }) => {
          this.$message.success(message);
          this.getList();
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    // 点击树
    appendBtn(data) {
      this.treeId = data.region_id;
      this.tableFrom.region_id = data.region_id;
      this.tableFrom.page = 1;
      this.getList();
    },
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = this.timeVal ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList();
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      adminListApi({
        ...this.tableFrom,
        ...this.formQuery
      })
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.tableData.data = [];
          this.tableData.total = 0;
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
    // 添加
    add() {
      this.$modalForm(adminCreateApi(this.formQuery)).then(() => this.getList());
    },
    // 编辑
    edit(id) {
      this.$modalForm(adminUpdateApi(id, this.formQuery)).then(() => this.getList());
    },
    // 修改密码表单
    onPassword(id) {
      this.$modalForm(adminPasswordApi(id));
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure("删除该管理员").then(() => {
        adminDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList();
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
.div-count {
  height: calc(100vh - 134px);
  display: flex;
  flex-wrap: nowrap;
}
.Nav {
  max-width: 220px;
  min-width: 220px;
  border-right: 1px solid #eee;
}
.colLeft {
  width: 100%;
  .col-pad {
    // padding: 0 20px;
  }
  .miniWidth {
    width: 230px;
  }
}
.custom-tree-node {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  padding-right: 17px;
  height: 36px;
  color: rgba(0, 0, 0, 0.6);
  .node-label {
    width: 150px;
    display: block;
  }
  .add {
    font-size: 10px;
    color: rgba(0, 0, 0, 0.6);
  }
}
.is-current .custom-tree-node,
.is-current .el-icon-more {
  color: var(--prev-color-primary);
}
::v-deep .el-tree-node {
  position: relative;
}
::v-deep .is-current::after {
  content: "";
  display: block;
  width: 2px;
  height: 40px;
  background: var(--prev-color-primary);
  position: absolute;
  right: 0;
  top: 0;
}
::v-deep
  .el-tree--highlight-current
  .el-tree-node.is-current
  > .el-tree-node__content {
  background: rgba(67, 127, 253, 0.04);
}
.el-ic {
  display: none;
  i,
  span {
    font-size: 18px;
    font-weight: 600;
  }
  .svg-icon {
    color: var(--prev-color-primary);
  }
}
.el-tree-node__expand-icon {
  color: var(--prev-color-primary);
}
::v-deep .el-tree-node__content {
  height: 40px;
}
.el-tree-node__content:hover .el-ic {
  color: var(--prev-color-primary) !important;
  display: inline-block;
}
</style>

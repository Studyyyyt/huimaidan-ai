<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        inline
        size="small"
        label-width="80px"
      >
        <el-form-item label="人员状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择人员状态"
            clearable
            @change="getList(1)"
            class="selWidth"
          >
            <el-option
              v-for="item in optionsData"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <select-search
          ref="selectSearch"
          :select="select"
          :searchSelectList="searchSelectList"
          @search="searchList"
        />
        <el-form-item>
          <el-button type="primary" size="small" @click="getSearchList"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="onAdd"
        >添加</el-button
      >
      <el-table v-loading="loading" :data="tableData.data" size="small">
        <el-table-column prop="staffs_id" label="ID" min-width="60" />
        <el-table-column prop="name" label="员工姓名" min-width="130" />
        <el-table-column label="证件照" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                class="tabImage"
                :src="scope.row.photo"
                :preview-src-list="[scope.row.photo]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column
          prop="user.nickname"
          label="用户昵称"
          min-width="130"
        />
        <el-table-column prop="phone" label="联系电话" min-width="130" />
        <el-table-column label="人员状态" min-width="100">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="130" />
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.user && !scope.row.user.cancel_time"
              type="text"
              size="small"
              @click="onEdit(scope.row.staffs_id)"
              >编辑</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onDel(scope.row.staffs_id, scope.$index)"
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
  staffListApi,
  staffCreateApi,
  staffUpdateApi,
  staffStatusApi,
  staffDeleteApi
} from "@/api/system";
import selectSearch from "@/components/base/selectSearch";
const optionsData = [
  {
    value: "1",
    label: "开启"
  },
  {
    value: "0",
    label: "关闭"
  }
];
export default {
  name: "Service",
  components: { selectSearch },
  data() {
    return {
      dialogTableVisible: false,
      optionsData: optionsData,
      loading: false,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        keyword: "",
        status: ""
      },
      uid: "",
      select: "name",
      searchSelectList: [
        { label: "姓名", value: "name" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ]
    };
  },
  mounted() {
    this.getList();
  },
  methods: {
    /**重置 */
    searchReset() {
      this.$refs.searchForm.resetFields();
      this.$refs.selectSearch.resetParmas();
      this.getList(1);
    },
    searchList(data) {
      this.tableFrom = { ...this.tableFrom, ...data };
      this.getList(1);
    },
    getSearchList() {
      this.$refs.selectSearch.changeSearch();
    },
    // 列表
    getList(num) {
      this.loading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      staffListApi(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.loading = false;
        })
        .catch(res => {
          this.$message.error(res.message);
          this.loading = false;
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
    onAdd() {
      this.$modalForm(staffCreateApi()).then(() => this.getList());
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(staffUpdateApi(id)).then(() => this.getList());
    },
    onDel(id, idx) {
      this.$modalSure("删除该员工吗").then(() => {
        staffDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1);
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    // 修改状态
    onchangeIsShow(row) {
      staffStatusApi(row.staffs_id, row.status)
        .then(({ message }) => {
          this.$message.success(message);
          this.getList();
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    }
  }
};
</script>

<style scoped lang="scss">
@import "@/styles/form.scss";
.tabImage {
  width: 36px;
  height: px;
}
</style>

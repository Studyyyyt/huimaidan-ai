<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        size="small"
        label-width="85px"
        inline
        @submit.native.prevent
      >
        <el-form-item label="模板名称：" prop="name">
          <el-input
            v-model="tableFrom.name"
            placeholder="请输入模板名称"
            class="selWidth"
            clearable
            @keyup.enter.native="getList"
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
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="add"
        >添加运费模板</el-button
      >
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column
          prop="shipping_template_id"
          label="ID"
          min-width="60"
        />
        <el-table-column prop="name" label="模板名称" min-width="150" />
        <el-table-column label="计费方式" min-width="100">
          <template slot-scope="{ row }">
            <span>{{ row.type | typeFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column label="指定包邮" min-width="100">
          <template slot-scope="{ row }">
            <span>{{ row.appoint | statusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column label="指定区域不配送" min-width="150">
          <template slot-scope="{ row }">
            <span>{{ row.undelivery | statusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="100" />
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.is_default == 0"
              type="text"
              size="small"
              @click="setDefault(scope.row.shipping_template_id)"
              >设为默认</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="onEdit(scope.row.shipping_template_id)"
              >编辑</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="
                handleDelete(scope.row.shipping_template_id, scope.$index)
              "
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
    <templatesFrom
      ref="templateForm"
      :tempId="id"
      :componentKey="componentKey"
      @getList="getList">
    </templatesFrom>
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
  templateListApi,
  templateDeleteApi,
  templateSetDefault,
} from "@/api/freight";
import templatesFrom from "@/components/templatesFrom";

export default {
  name: "ShippingTemplates",
  components: { templatesFrom },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "自定义",
        2: "开启",
        0: "关闭",
      };
      return statusMap[status];
    },
    typeFilter(status) {
      const statusMap = {
        0: "按件数",
        1: "按重量",
        2: "按体积",
      };
      return statusMap[status];
    },
  },
  data() {
    return {
      dialogVisible: false,
      tableFrom: {
        page: 1,
        limit: 20,
        name: "",
      },
      tableData: {
        data: [],
        total: 0,
      },
      id: 0,
      listLoading: true,
      componentKey: 0,
    };
  },
  mounted() {
    this.getList();
  },
  methods: {
    /**重置 */
    searchReset() {
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    add() {
      this.$refs.templateForm.dialogVisible = true;
      this.$refs.templateForm.resetData();
      // const _this = this;
      // this.$modalTemplates(
      //   0,
      //   function () {
      //     _this.getList();
      //   },
      //   (this.componentKey += 1)
      // );
    },
    // 列表
    getList() {
      this.listLoading = true;
      templateListApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch((res) => {
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
    // 编辑
    onEdit(id) {
      this.id = id;
      this.$refs.templateForm.dialogVisible = true;
      this.$refs.templateForm.getInfo(id)
      // const _this = this;
      // this.$modalTemplates(
      //   id,
      //   function () {
      //     _this.getList();
      //   },
      //   (this.componentKey += 1)
      // );
      // this.$refs.addTemplates.getCityList()
      // this.$refs.addTemplates.getInfo(id)
    },
    // 设为默认
    setDefault(id) {
      this.$modalSure("将该模板设为默认模板").then(() => {
        templateSetDefault(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList();
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure("删除该模板").then(() => {
        templateDeleteApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1);
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
  },
};
</script>

<style scoped lang="scss">
@import "@/styles/form.scss";
</style>

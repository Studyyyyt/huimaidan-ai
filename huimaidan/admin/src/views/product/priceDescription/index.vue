<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        inline
        size="small"
        :model="tableFrom"
        ref="searchForm"
        label-width="85px"
      >
        <el-form-item label="商品分类：" prop="cate_id">
          <el-cascader
            v-model="tableFrom.cate_id"
            class="selWidth"
            :options="merCateList"
            :props="{ checkStrictly: true }"
            clearable
            @change="getList(1)"
          />
        </el-form-item>
        <el-form-item label="显示状态：" prop="is_show">
          <el-select
            v-model="tableFrom.is_show"
            clearable
            filterable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="显示" value="1" />
            <el-option label="隐藏" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="搜索：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入须知名称"
            class="selWidth"
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
      <el-button size="small" type="primary" class="mb20" @click="onAdd"
        >添加价格说明</el-button
      >
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="rule_id" label="ID" min-width="50" />
        <el-table-column prop="rule_name" label="名称" min-width="100" />
        <el-table-column label="使用分类" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.cate_name.toString() || "默认全部" }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" min-width="100" />
        <el-table-column prop="is_show" label="是否显示" min-width="100">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="隐藏"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="update_time" label="更新时间" min-width="100" />
        <el-table-column prop="create_time" label="创建时间" min-width="100" />
        <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row)"
              >编辑</el-button
            >
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.rule_id, scope.$index)"
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
    <!--添加编辑弹窗-->
    <el-dialog
      :title="rule_id ? '编辑说明' : '添加说明'"
      :visible.sync="dialogVisible"
      custom-class="dialog-custom"
      width="900px"
      :before-close="handleClose"
    >
      <el-form
        ref="formValidate"
        v-loading="fullscreenLoading"
        class="formValidate mt20"
        :rules="ruleValidate"
        :model="formValidate"
        label-width="120px"
        @submit.native.prevent
      >
        <el-form-item label="名称：" prop="rule_name">
          <el-input v-model="formValidate.rule_name" placeholder="请输入名称" />
        </el-form-item>
        <el-form-item label="使用商品分类：">
          <el-cascader
            v-model="formValidate.cate_id"
            class="selWidth"
            :options="merCateList"
            :props="{ multiple: true, checkStrictly: true, emitPath: false }"
            clearable
          />
          <br />
          <span style="color: #999"> 注: 当不选择任何分类时，默认全部商品</span>
        </el-form-item>
        <el-form-item label="是否显示：" prop="is_show" class="switch-width-double">
          <el-switch
            v-model="formValidate.is_show"
            :active-value="1"
            :inactive-value="0"
            active-text="显示"
            inactive-text="隐藏"
          />
        </el-form-item>
        <el-form-item label="排序：" prop="sort">
          <el-input-number
            v-model="formValidate.sort"
            placeholder="请输入排序序号"
          />
        </el-form-item>
        <el-col v-if="dialogVisible" :span="24">
          <el-form-item label="价格说明详情：" prop="content">
            <!-- <ueditor-from v-model="formValidate.content" :content="formValidate.content" /> -->
            <WangEditor
              :content="formValidate.content"
              @editorContent="getEditorContent"
              style="width: 100%;"
            ></WangEditor>
          </el-form-item>
        </el-col>
      </el-form>
      <span slot="footer" class="dialog-footer" style="padding-right: 14px;">
        <el-button @click="dialogVisible = false" size="small">取 消</el-button>
        <el-button
          type="primary"
          size="small"
          @click="handleSubmit('formValidate')"
          >确 定</el-button
        >
      </span>
    </el-dialog>
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
import WangEditor from "@/components/wangEditor/index.vue";
import {
  categoryListApi,
  priceRuleLstApi,
  createPriceRuleApi,
  updatePriceRuleApi,
  deletePriceRuleApi,
  priceRuleStatusApi
} from "@/api/product";
export default {
  name: "LabelList",
  components: {
    WangEditor
  },
  data() {
    return {
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        cate_id: [],
        is_show: "",
        keyword: "",
        page: 1,
        limit: 20
      },
      rule_id: "",
      merCateList: [],
      dialogVisible: false,
      fullscreenLoading: false,
      formValidate: {},
      ruleValidate: {
        rule_name: [{ required: true, message: "请输入名称", trigger: "blur" }],
        sort: [{ required: true, message: "请输入排序", trigger: "change" }],
        content: [
          { required: true, message: "请输入说明详情", trigger: "blur" }
        ]
      }
    };
  },
  mounted() {
    this.getCategorySelect();
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset() {
      this.tableFrom.cate_id = [];
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    // 商品分类；
    getCategorySelect() {
      categoryListApi()
        .then(res => {
          this.merCateList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      priceRuleLstApi(this.tableFrom)
        .then(res => {
          res.data.list.forEach((item, index) => {
            item.cate_id = [];
            item.cate_name = [];
            let cate_name = "";
            item.cate.forEach((val, idx) => {
              cate_name =
                val.category && val.category.cate_name
                  ? val.category.cate_name
                  : "";
              item.cate_id.push(val.right_id);
              item.cate_name.push(cate_name);
            });
          });
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
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList("");
    },
    // 添加
    onAdd() {
      this.rule_id = "";
      this.dialogVisible = true;
      this.reset();
    },
    // 编辑
    onEdit(row) {
      this.rule_id = row.rule_id;
      this.formValidate = row;
      this.dialogVisible = true;
    },
    reset() {
      this.formValidate = {
        cate_id: [],
        rule_name: "",
        content: "",
        is_show: 0,
        sort: ""
      };
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure("确定删除该说明").then(() => {
        deletePriceRuleApi(id)
          .then(({ message }) => {
            this.$message.success(message);
            this.getList("");
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    onchangeIsShow(row) {
      priceRuleStatusApi(row.rule_id, { is_show: row.is_show })
        .then(({ message }) => {
          this.$message.success(message);
          this.getList("");
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    getEditorContent(data) {
      this.formValidate.content = data;
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          this.rule_id
            ? updatePriceRuleApi(this.rule_id, this.formValidate)
                .then(async res => {
                  this.fullscreenLoading = false;
                  this.$message.success(res.message);
                  this.dialogVisible = false;
                  this.getList("");
                })
                .catch(res => {
                  this.fullscreenLoading = false;
                  this.$message.error(res.message);
                })
            : createPriceRuleApi(this.formValidate)
                .then(async res => {
                  this.fullscreenLoading = false;
                  this.$message.success(res.message);
                  this.dialogVisible = false;
                  this.getList("");
                })
                .catch(res => {
                  this.fullscreenLoading = false;
                  this.$message.error(res.message);
                });
        } else {
          this.$nextTick(() => {
            const errorDom = document.querySelector(".dialog-custom .el-form-item__error");
            if (errorDom) {
              errorDom.scrollIntoView({ behavior: "smooth", block: "center" });
            }
          });
          return false;
        }
      });
    },
    handleClose() {
      this.dialogVisible = false;
    }
  }
};
</script>

<style scoped lang="scss">
.formValidate {
  ::v-deep .el-form-item__label {
    color: #606266;
  }
}

::v-deep .dialog-custom {
  height: 90vh;
  margin-top: 4vh !important;
}
</style>

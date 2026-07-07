<template>
  <div class="divBox">
    <pages-header
      ref="pageHeader"
      :title="`${$route.query.name}`"
      backUrl="/product/cdkey"
    ></pages-header>
    <div class="selCard mt14">
      <el-form
        inline
        size="small"
        :model="tableFrom"
        ref="searchForm"
        label-width="85px"
      >
        <el-form-item label="卡密状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option label="未出售" value="1" />
            <el-option label="已出售" value="-1" />
          </el-select>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14" shadow="never" :bordered="false">
      <div class="acea-row mb20">
        <el-button size="small" type="primary" class="mr14" @click="handleAdd"
          >添加卡密</el-button
        >
        <el-upload
          style="display:inline-block;"
          size="small"
          class="mr14 ml5"
          :headers="myHeaders"
          action
          :http-request="handleUploadForm"
          :on-success="handleSuccess"
          :before-upload="beforeUpload"
          :show-file-list="false"
        >
          <el-button size="small">导入卡密</el-button>
        </el-upload>
        <el-button size="small" @click="handleDownload">下载模板</el-button>
        <el-button size="small" @click="handleBatchDel">批量删除</el-button>
      </div>
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        row-key="cdkey_id"
        :highlight-current-row="true"
        @selection-change="handleSelectionChange"
        class="operation tableSelection"
      >
        <el-table-column width="50">
          <template slot="header" slot-scope="scope">
            <el-popover
              placement="top-start"
              width="100"
              trigger="hover"
              class="tabPop"
            >
              <div>
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'dan' }"
                  @click="onHandle('dan', scope.$index)"
                  >选中本页</span
                >
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'duo' }"
                  @click="onHandle('duo')"
                  >选中全部</span
                >
              </div>
              <el-checkbox
                slot="reference"
                :value="
                  (chkName === 'dan' &&
                    checkedPage.indexOf(tableFrom.page) > -1) ||
                    chkName === 'duo'
                "
                @change="changeType"
              />
            </el-popover>
          </template>
          <template slot-scope="scope">
            <el-checkbox
              :value="
                checkedIds.indexOf(scope.row.cdkey_id) > -1 ||
                  (chkName === 'duo' &&
                    noChecked.indexOf(scope.row.cdkey_id) === -1)
              "
              @change="v => changeOne(v, scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="cdkey_id" label="卡密ID" min-width="100" />
        <el-table-column prop="library_id" label="卡密库ID" min-width="100" />
        <el-table-column
          label="卡号"
          prop="key"
          min-width="230"
          :show-overflow-tooltip="true"
        >
        </el-table-column>
        <el-table-column
          prop="pwd"
          label="密码"
          min-width="230"
          :show-overflow-tooltip="true"
        />
        <el-table-column
          label="出售情况"
          min-width="120"
          :show-overflow-tooltip="true"
        >
          <template slot-scope="scope">
            <span class="colorAuxiliary">{{
              scope.row.status == -1 ? "已出售" : "未出售"
            }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="200" />
        <el-table-column label="操作" width="100" fixed="right">
          <template slot-scope="scope">
            <el-button
              v-if="scope.row.status != -1"
              type="text"
              size="small"
              clsss="mr10"
              @click="handleEdit(scope.row)"
              :disabled="scope.row.is_use == 1"
              >编辑</el-button
            >
            <el-button
              v-if="scope.row.status != -1"
              type="text"
              size="small"
              :disabled="scope.row.is_use == 1"
              @click="handleDelete(scope.row.cdkey_id)"
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
          layout="total, sizes, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!-- 添加卡密-->
    <add-cdkey
      ref="addCarMy"
      :libraryId="tableFrom.library_id"
      @handlerSubSuccess="handlerSubSuccess"
    ></add-cdkey>
    <!-- 编辑卡密-->
    <edit-cdkey
      ref="editCarMy"
      :cdkeyInfo="cdkeyInfo"
      @handlerEditSubSuccess="handlerEditSubSuccess"
    ></edit-cdkey>
  </div>
</template>
<script>
import {
  cardSecretBatchDeleteApi,
  cardSecretDeleteApi,
  cardSecretImportExcelApi,
  cardSecretPageListApi,
  cardSecretExportExcelApi
} from "@/api/product";
import addCdkey from "./addCdkey.vue";
import EditCdkey from "./editCdkey.vue";
import Setting from "@/settings";
import SettingMer from "@/libs/settingMer";
import { getToken } from "@/utils/auth";
import { isFileUpload } from "@/utils/validate";
import { roterPre } from "@/settings";
export default {
  name: "creatCdkey",
  components: { addCdkey, EditCdkey },
  data() {
    return {
      roterPre: roterPre,
      listLoading: false,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        library_id: 0,
        status: ""
      },
      multipleSelectionAll: [],
      chkName: "",
      checkedIds: [], // 订单当前页选中的数据
      noChecked: [], // 订单全选状态下当前页不选中的数据
      checkedPage: [],
      allCheck: false,
      cdkeyInfo: null, //卡密详情
      myHeaders: { "X-Token": getToken() }, // 上传参数
      fileUrl: SettingMer.https + "/product/cdkey/library/import/cdkey"
    };
  },
  mounted() {
    this.tableFrom.library_id = Number(this.$route.query.id);
    this.getList(1);
  },
  methods: {
    // 选择商品
    onHandle(name) {
      this.chkName = this.chkName === name ? "" : name;
      this.changeType(!(this.chkName === ""));
    },
    changeType(v) {
      if (v) {
        if (!this.chkName) {
          this.chkName = "dan";
        }
      } else {
        this.chkName = "";
        this.allCheck = false;
      }
      const index = this.checkedPage.indexOf(this.tableFrom.page);
      if (this.chkName === "dan") {
        this.checkedPage.push(this.tableFrom.page);
      } else if (index > -1) {
        this.checkedPage.splice(index, 1);
      }
      this.syncCheckedId();
    },
    syncCheckedId() {
      const ids = this.tableData.data.map(v => {
        return v.cdkey_id;
      });
      if (this.chkName === "duo") {
        this.checkedIds = [];
        this.allCheck = true;
      } else if (this.chkName === "dan") {
        this.allCheck = false;
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index === -1) {
            this.checkedIds.push(id);
          }
        });
      } else {
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index > -1) {
            this.checkedIds.splice(index, 1);
          }
        });
      }
    },
    // 分开选择
    changeOne(v, row) {
      if (v) {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.product_id);
          if (index > -1) this.noChecked.splice(index, 1);
        } else {
          const index = this.checkedIds.indexOf(row.product_id);
          if (index === -1) this.checkedIds.push(row.product_id);
        }
      } else {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.product_id);
          if (index === -1) this.noChecked.push(row.product_id);
        } else {
          const index = this.checkedIds.indexOf(row.product_id);
          if (index > -1) this.checkedIds.splice(index, 1);
        }
      }
    },
    //判断勾选
    selectable(row) {
      if (row.is_use) {
        return false;
      } else {
        return true;
      }
    },
    //导入卡密前校验
    beforeUpload(file) {
      return isFileUpload(file);
    },
    // 上传
    handleUploadForm(param) {
      const formData = new FormData();
      formData.append("file", param.file);
      formData.append("library_id", this.tableFrom.library_id);
      const loading = this.$loading({
        lock: true,
        text: "上传中，请稍候...",
        spinner: "el-icon-loading",
        background: "rgba(0, 0, 0, 0.7)"
      });
      cardSecretImportExcelApi(formData)
        .then(res => {
          loading.close();
          this.getList(1);
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
          loading.close();
        });
    },
    // 上传成功
    handleSuccess(response) {
      if (response.status === 200) {
        this.$message.success(response.message);
      } else {
        this.$message.error(response.message);
      }
    },
    //下载模板
    handleDownload() {
      //使用相对路径
      const fileUrl = "/cdkey_template.xlsx";
      // 创建一个隐藏的a标签
      const link = document.createElement("a");
      link.href = fileUrl;
      link.download = "卡密模板.xlsx";
      // 将a标签添加到DOM并触发点击事件
      document.body.appendChild(link);
      link.click();
      //移除a标签
      document.body.removeChild(link);
    },
    //批量删除
    handleBatchDel() {
      if (this.checkedIds.length === 0)
        return this.$message.warning("请至少选择一项卡密");
      this.$modalSure("要将选中卡密删除吗").then(() => {
        cardSecretBatchDeleteApi({ ids: this.checkedIds.join(",") })
          .then(() => {
            this.$message.success("批量删除成功");
            if (this.tableData.data.length === 1 && this.tableFrom.page > 1)
              this.tableFrom.page = this.tableFrom.page - 1;
            this.getList("");
          })
          .catch(res => {
            this.$message.error(res.message);
          });
      });
    },
    // 设置选中的方法
    handleSelectionChange(val) {
      this.multipleSelectionAll = val;
      const data = [];
      this.multipleSelectionAll.map(item => {
        data.push(item.cdkey_id);
      });
      this.checkedIds = data;
    },
    selectAll(data) {
      let id = data.map((i, index) => {
        return i.cdkey_id;
      });
      this.checkedIds = Array.from(new Set([...this.checkedIds, ...id]));
    },
    selectOne(data, row) {
      let id = data.map((i, index) => {
        return i.cdkey_id;
      });
      let index = this.checkedIds.findIndex(e => {
        return e == row.cdkey_id;
      });
      this.checkedIds.splice(index, 1);
      this.checkedIds = Array.from(new Set([...this.checkedIds, ...id]));
    },
    //确认提交卡密
    handlerSubSuccess(e) {
      this.$refs.addCarMy.cdkeyShow = false;
      this.getList(1);
    },
    closeCarMy() {
      this.$refs.addCarMy.cdkeyShow = false;
    },
    //编辑卡密回调
    handlerEditSubSuccess() {
      this.getList(1);
    },
    //添加卡密
    handleAdd() {
      this.$refs.addCarMy.cdkeyShow = true;
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      cardSecretPageListApi(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
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
    // 删除
    handleDelete(id) {
      this.$modalSure("删除此卡密吗").then(() => {
        cardSecretDeleteApi(id).then(() => {
          this.$message.success("删除成功");
          if (this.tableData.data.length === 1 && this.tableFrom.page > 1)
            this.tableFrom.page = this.tableFrom.page - 1;
          this.getList("");
        });
      });
    },
    //编辑
    handleEdit(row) {
      this.$refs.editCarMy.cdkeyShow = true;
      this.cdkeyInfo = {
        key: row.key,
        pwd: row.pwd,
        id: row.cdkey_id
      };
    }
  }
};
</script>
<style scoped lang="scss"></style>

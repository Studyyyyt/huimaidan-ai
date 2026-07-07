<template>
  <div class="divBox">
    <!-- 选择商品对话框 -->
    <el-dialog title="选择商品" :visible.sync="dialogVisible" width="700px" v-if="dialogVisible" custom-class="customHeight">
    <!-- 商品列表表格 -->
    <el-table
      size="small"
      ref="multipleSelection"
      v-loading="listLoading"
      :data="tableData.data"
      :row-key="(row) => row.product_id"
      @selection-change="handleSelectionChange"
    >
      <el-table-column type="selection" :reserve-selection="true" width="55" />
      <el-table-column prop="product_id" label="ID" min-width="50" />
      <el-table-column label="商品图" min-width="80">
        <template slot-scope="scope">
          <div class="demo-image__preview">
            <el-image
              style="width: 36px; height: 36px"
              :src="scope.row.image"
              :preview-src-list="[scope.row.image]"
            />
          </div>
        </template>
      </el-table-column>
      <el-table-column prop="store_name" label="商品名称" min-width="200" />
    </el-table>
    <!-- 分页组件 -->
    <div class="block mb20">
      <el-pagination
        :page-size="tableFrom.limit"
        :current-page="tableFrom.page"
        layout="prev, pager, next, jumper"
        :total="tableData.total"
        @size-change="handleSizeChange"
        @current-change="pageChange"
      />
    </div>
     <!-- 对话框底部按钮 -->
    <div slot="footer" class="use-template-dialog-footer">
      <el-button size="small" @click="dialogVisible=false">取消</el-button>
      <el-button type="primary" size="small" @click="submitProduct">确定</el-button>
    </div>
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
import { productLstApi } from "@/api/product";
import { roterPre } from "@/settings";

export default {
  name: "GoodList",
  data() {
    return {
      dialogVisible: false,
      templateRadio: 0,
      merCateList: [],
      roterPre: roterPre,
      listLoading: true,
      selectedGoods: [],
      tableData: {
        data: [],
        total: 0,
      },
      tableFrom: {
        page: 1,
        limit: 5,
        mer_cate_id: "",
        cate_id: "",
        keyword: "",
        type: "1",
        is_gift_bag: "",
      },
      multipleSelection: [],
      checked: [],
      broadcast_room_id: "",
    };
  },
  mounted() {

  },
  methods: {
     /**
     * 处理表格选择变化事件
     * @param {Array} val - 选中的行数据
     */
    handleSelectionChange(val) {
      this.multipleSelection = val;
    },
    /**
     * 提交选中的商品
     */
    submitProduct(){
      // 去除重复的商品对象
      const uniqueGoods = this.deleteDuplicateObjects(this.multipleSelection)
      this.$emit('get-goods',uniqueGoods);
      this.dialogVisible = false;
    },
    /**
     * 去除数组中重复的对象
     * @param {Array} arr - 包含对象的数组
     * @returns {Array} - 去重后的数组
     */
     deleteDuplicateObjects(obj) {
      var uniques = [];
      var stringify = {};
      for (var i = 0; i < obj.length; i++) {
        var keys = Object.keys(obj[i]);
        keys.sort(function(a, b) {
          return (Number(a) - Number(b));
        });
        var str = '';
        for (var j = 0; j < keys.length; j++) {
          str += JSON.stringify(keys[j]);
          str += JSON.stringify(obj[i][keys[j]]);
        }
        if (!stringify.hasOwnProperty(str)) {
          uniques.push(obj[i]);
          stringify[str] = true;
        }
      }
      return uniques;
  },
    /**
     * 获取商品列表
     * @param {Array} arr - 选中的商品数组
     * @param {number} num - 是否重置页码
     */
    getList(arr,num) {
      this.listLoading = true;
      this.selectedGoods = arr;
      productLstApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          if(arr && arr .length > 0){
            this.$nextTick(() => {
              arr.forEach((row) => {
                this.$refs.multipleSelection.toggleRowSelection(row, true);
              });
            });
          }
          if(num) this.pageChange(1)
          this.listLoading = false;
        })
        .catch((res) => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    /**
     * 处理分页页码变化事件
     * @param {number} page - 新的页码
     */
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList();
    },
    /**
     * 处理分页每页数量变化事件
     * @param {number} val - 新的每页数量
     */
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList();
    },
  },
};
</script>

<style scoped lang="scss">
.customHeight{
  height: 800px;
}
</style>

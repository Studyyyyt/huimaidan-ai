<template>
  <div class="goodList">
    <div v-if="isDiscount" class="mb14" style="font-size: 12px;color:#F56464;"> 注:虚拟商品、卡密商品、含有自定义表单的商品不适合添加套餐组合，此处商品列表不予展示。</div>
    <el-form size="small" inline label-width="80px">
      <el-form-item label="商品分类：">
        <el-select v-model="tableFrom.mer_cate_id" placeholder="请选择" class="dialogWidth" clearable @change="getList(1)">
          <el-option v-for="item in merCateList" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="商品搜索：">
        <el-input
          placeholder="请输入商品名称,关键字,编号"
          class="dialogWidth"
          v-model="tableFrom.keyword"
          @keyup.enter.native="getList(1)"
        >
        </el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" size="small" @click="getList(1)">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      ref="table"
      :data="tableData.data"
      :row-key="getRowKeys"
      @selection-change="changeCheckbox"
      size="small"
      v-loading="loading"
    >
    <el-table-column type="selection" :reserve-selection="true" :selectable="checkSelectSet" width="55" />
      <el-table-column
        prop="product_id"
        label="商品id"
        min-width="80"
      />
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
      <el-table-column
        prop="store_name"
        label="商品名称"
        min-width="200"
      />
      <el-table-column label="售价" prop="price" min-width="80"/>
      <el-table-column label="会员价" prop="svip_price" min-width="80">
        <template slot-scope="scope">
          <span v-if="scope.row.svip_price_type == 0">未设置</span>
          <span v-else>{{scope.row.svip_price}}</span>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-between mt20 mb15">
      <el-pagination
        :page-size="tableFrom.limit"
        :current-page="tableFrom.page"
        layout="prev, pager, next, jumper"
        :total="tableData.total"
        @size-change="handleSizeChange"
        @current-change="pageChange"
      />
      <div class="footer" slot="footer">
        <el-button size="small" @click="close">取消</el-button>
        <el-button
          type="primary"
          size="small"
          :loading="modal_loading"
          @click="ok"
          >提交</el-button
        >
      </div>
    </div>
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
import { categoryListApi, goodLstApi, categorySelectApi } from "@/api/product";

export default {
  name: "index",
  props: {
    is_new: {
      type: String,
      default: "",
    },
    diy: {
      type: Boolean,
      default: false,
    },
    isdiy: {
      type: Boolean,
      default: false,
    },
    ischeckbox: {
      type: Boolean,
      default: false,
    },
    liveStatus: {
      type: Boolean,
      default: false,
    },
    isLive: {
      type: Boolean,
      default: false,
    },
    isGood: {
      type: Boolean,
      default: false,
    },
    isDiscount: {
      type: Boolean,
      default: false,
    },
    datas: {
      type: Object,
      default: function () {
        return {};
      },
    },
    selectedArr: {
      type: Array,
      default: () => [],
    },
    product_id: {
      type: String,
      default: "",
    }
  },
  data() {
    return {
			cateIds:[],
      merCateList: [], // 商户分类筛选
      modal_loading: false,
      treeSelect: [],
       props: {
        emitPath: false
      },
      tableFrom: {
        mer_cate_id: "",
        keyword: "",
        is_gift_bag: 0,
        is_good: this.isGood ? 1 : "",
        page: 1,
        limit: 6,
      },
      total: 0,
      loading: false,
      tableData: {
        total: 0,
        data: []
      },
      currentid: 0,
      productRow: {},
      images: [],
      diyVal: [],
      many: "",
      idKey: "product_id",
      multipleSelectionAll: this.selectedArr,
      // multipleSelectionAll: window.form_create_helper.get(this.$route.query.field) || [],
      // idKey: "product_id",
    };
  },
  computed: {

  },
  created() {
    let many = "";
    if (this.ischeckbox) {
      many = "many";
    } else {
      many = this.$route.query.type;
    }
    this.many = many;

  },
  mounted() {
    this.getCategorySelect();
    this.getList('');
    setTimeout(()=>{
      this.setSelectRow();
    },500)

  },
  methods: {
    getRowKeys(row) {
      return row.product_id
    },
    checkSelectSet(row, index) {
      //row 就是每行的数据
      //return : false  就是禁用
      //return : true  不禁用
      return row.product_id != this.product_id
    },
		handleSelectAll () {
		  this.$refs.table.selectAll(false);
		},
    changeCheckbox(selection) {
      // let images = [];
      // selection.forEach(function (item) {
      //   let imageObject = item
      //   images.push(imageObject);
      // });
      // this.multipleSelectionAll = selection
      this.images = selection;
      this.diyVal = selection;
      if(this.isdiy)this.$emit("getProductDiy", selection);
    },
    // 商品分类；
    goodsCategory() {
      categoryListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 商户分类；
    getCategorySelect() {
      categorySelectApi()
        .then(res => {
          this.merCateList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
     pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 列表
    getList(num) {
      let that = this
      that.loading = true;
      that.tableFrom.page = num ? num : that.tableFrom.page
      if(that.isDiscount){
        that.tableFrom.is_ficti = 0
        that.tableFrom.mer_form_id = 0
      }
      if(this.isdiy){
        delete(that.tableFrom.is_good)
      }
        goodLstApi(that.tableFrom)
          .then(async (res) => {
            that.tableData.data = res.data.list;
            that.tableData.total = res.data.count;
            this.$nextTick(function () {
              this.setSelectRow(); //调用跨页选中方法
            });
            that.loading = false;
          })
          .catch((res) => {
            that.loading = false;
            that.$message.error(res.message);
          });
    },
   // 设置选中的方法
    setSelectRow() {
      if (!this.multipleSelectionAll || this.multipleSelectionAll.length <= 0) {
        return;
      }
      // 标识当前行的唯一键的名称
      let idKey = this.idKey;
      let selectAllIds = [];
      this.multipleSelectionAll.forEach((row) => {
        selectAllIds.push(row[idKey]);
      });
      // this.images.forEach((row) => {
      //   selectAllIds.push(row[idKey]);
      // });
      // this.$refs.table.clearSelection();
      for (var i = 0; i < this.tableData.data.length; i++) {
        if (selectAllIds.indexOf(this.tableData.data[i][idKey]) >= 0) {
          // 设置选中，记住table组件需要使用ref="table"
          this.$refs.table.toggleRowSelection(this.tableData.data[i], true);
        }
      }
    },
    close(){
      this.$emit("close");
    },
    ok() {
      if (this.images.length > 0) {
        if (this.$route.query.fodder === "image") {
          let imageValue = form_create_helper.get("image");
          form_create_helper.set("image", imageValue.concat(this.images));
          form_create_helper.close("image");
        } else {
          if(this.isdiy){
            this.$emit("getProductId", this.diyVal);
          }else {
            this.$emit("getProductId", this.images);
          }
        }
      } else {
        this.$message.warning("请先选择商品");
      }
    },
  },
};
</script>

<style scoped lang="scss">
.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.tabform {
   .ivu-form-item {
    margin-bottom: 16px !important;
  }
}

.btn {
  margin-top: 20px;
  float: right;
}

.goodList {
   table {
    width: 100% !important;
  }

  ::v-deep .el-table_1_column_1.el-table-column--selection.is-leaf.el-table__cell {
    .cell {
      margin-left: 4px;
    }
  }
}
</style>

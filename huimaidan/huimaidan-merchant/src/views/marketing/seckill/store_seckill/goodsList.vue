<template>
  <el-dialog v-if="dialogVisible" title="商品信息" :visible.sync="dialogVisible" width="1100px">
    <div class="divBox">
      <div class="header clearfix">
        <div class="container mt14">
          <el-form size="small" inline label-width="80px">
            <el-form-item label="商品搜索：">
              <el-input v-model="tableFrom.keyword" placeholder="请输入商品关键字" class="selWidth" clearable @keyup.enter.native="getList(1)">
                <el-button slot="append" icon="el-icon-search" @click="getList(1)"></el-button>
              </el-input>
            </el-form-item>
            <el-form-item label="平台分类：">
              <el-select
                class="selWidth"
                v-model="tableFrom.cate_ids"
                filterable
                clearable
                default-first-option
                placeholder="请选择平台商品分类"
                @change="getList(1)">
                <el-option
                  v-for="item in sysCateList"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                  @change="getList(1)"
                  :disabled="pid&&pid.length>0&&pid.indexOf(item.value.toString())==-1">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="商户分类：">
              <el-select
                class="selWidth"
                v-model="tableFrom.category_id"
                filterable
                clearable
                default-first-option
                @change="getList(1)"
                placeholder="请选择商户商品分类">
                <el-option
                  v-for="item in merCateList"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="商品标签：">
              <el-select v-model="tableFrom.sys_labels" placeholder="请选择" class="filter-item selWidth" clearable filterable @change="getList(1)">
                <el-option v-for="item in merProductLabelList" :key="item.id" :label="item.name" :value="item.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="商品状态：" prop="us_status">
              <el-select v-model="tableFrom.us_status" placeholder="请选择" class="filter-item selWidth" clearable @change="getList(1)">
                <el-option v-for="item in productStatusList" :key="item.value" :label="item.label" :value="item.value" />
              </el-select>
            </el-form-item>
          </el-form>
        </div>
      </div>
      <el-table
        ref="mainTable"
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        max-height="400"
      >
        <el-table-column width="55">
          <template slot="header" slot-scope="scope">
            <el-checkbox
              slot="reference"
              :value="isChecked && checkedPage.indexOf(tableFrom.page) > -1"
              @change="changeType"
            />
          </template>
          <template slot-scope="scope">
            <el-checkbox :value="checkedIds.indexOf(scope.row.product_id) > -1" @change="(v) => changeOne(v, scope.row)" />
          </template>
        </el-table-column>
        <el-table-column label="ID" prop="product_id" width="55" />
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
        <el-table-column label="商品名称" min-width="200">
          <template slot-scope="scope">
            <div class="row_title line2">{{ scope.row.store_name }}</div>
          </template>
        </el-table-column>
        <el-table-column label="商品分类" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.storeCategory?scope.row.storeCategory.cate_name:'-' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="old_stock" label="库存" min-width="100" />
        <el-table-column label="商品状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.is_show==0?"下架":"上架" }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-between row-bottom">
        <el-pagination
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
        <div>
          <el-button size="small" @click="dialogVisible = false">取消</el-button>
          <el-button size="small" type="primary" @click="submitProduct">确定</el-button>
        </div>
      </div>
    </div>
  </el-dialog>
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
import { getSelectProductApi } from '@/api/marketing';
import { mapGetters } from 'vuex';
import { roterPre } from '@/settings';
export default {
  name: 'GoodsList',
  props: {
    pid: {
      type: Array,
      default: () => [],
    },
    proData: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      dialogVisible: false,
      roterPre: roterPre,
      listLoading: true,
      productStatusList: [
        { label: "上架", value: 1 },
        { label: "下架", value: 0 },
      ],
      tableData: {
        data: [],
        total: 0,
      },
      cate_ids: '',
      tableFrom: {
        page: 1,
        limit: 5,
        cate_ids: '',
        keyword: '',
        category_id: '',
        us_status: '',
        sys_labels: [],
        type_id: '',
        active_id: this.$route.params.id
      },
      mer_ids:[],
      props: { emitPath: false },
      multipleSelection: [],
      checked: [],
      selectAllPage: false,
      checkedPage: [],
      checkBox: [],
      isChecked: false,
      checkedIds: [],
    };
  },
  computed: {
    ...mapGetters(['merCateList','merProductLabelList','sysCateList']),
  },
  mounted() {
    if (!this.merCateList.length) this.$store.dispatch('product/getMerCateSelect');
    if (!this.sysCateList.length) this.$store.dispatch('product/getSysCategoryList',{lv:1});
    if (!this.merProductLabelList.length) this.$store.dispatch('product/getProductLabel');
  },
  methods: {
    // 默认勾选
    setCheckedProduct(proData){
      if (proData.length) {
        let [...arr2] = proData;
        this.checkBox = arr2;
        this.checkedIds = arr2.map((item) => {
          return item.old_product_id;
        });
      }else{
        this.checkedIds = []
      }
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page
      this.tableFrom.level_one_cate_ids = (this.pid.length&&this.pid.toString()) || ""
      getSelectProductApi(this.tableFrom)
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
    changeType(v) {
      this.isChecked = v;
      const index = this.checkedPage.indexOf(this.tableFrom.page);
      this.isIndex = index;
      this.checkedPage.push(this.tableFrom.page);
      if (index > -1) {
        this.checkedPage.splice(index, 1);
      }
      this.syncCheckedId(v);
    },
    changeOne(v, row) {
      if (v) {
        const index = this.checkedIds.indexOf(row.product_id);
        if (index === -1) {
          this.checkedIds.push(row.product_id);
          this.checkBox.push(row);
        }
      } else {
        const index = this.checkedIds.indexOf(row.product_id);
        if (index > -1) {
          this.checkedIds.splice(index, 1);
          this.checkBox.splice(index, 1);
        }
      }
    },
    syncCheckedId(o) {
      const ids = this.tableData.data.map((v) => v.product_id);
      if (o) {
        this.tableData.data.forEach((item) => {
          const index = this.checkedIds.indexOf(item.product_id);
          if (index === -1) {
            this.checkedIds.push(item.product_id);
            this.checkBox.push(item);
          }
        });
      } else {
        this.tableData.data.forEach((item) => {
          const index = this.checkedIds.indexOf(item.product_id);
          if (index > -1) {
            this.checkedIds.splice(index, 1);
            this.checkBox.splice(index, 1);
          }
        });
      }
    },
    handleSelectionChange(val) {
      const tables = [];
      val.map((item) => {
        tables.push({ src: item.image, product_id: item.product_id });
      });
      this.multipleSelection = tables;
    },
    submitProduct() {
      if (this.checkBox.length > 100) return this.$message.warning('最多可选择100个商品');
      this.dialogVisible = false;
      this.$emit('onSelectList', this.checkBox);
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList('');
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList('');
    },
  },
};
</script>

<style scoped lang="scss">
.selWidth {
  width: 250px !important;
}
.seachTiele {
  line-height: 35px;
}
.fr {
  float: right;
}
.row_title{
  max-width: 240px;
}
</style>

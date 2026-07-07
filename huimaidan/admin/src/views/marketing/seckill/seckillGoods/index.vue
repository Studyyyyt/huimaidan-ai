<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" inline size="small" label-width="85px" >
        <el-form-item label="活动状态：" prop="active_status">
          <el-select
            v-model="tableFrom.active_status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1),getLstFilter()"
          >
            <el-option
              v-for="item in seckillStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="商品状态：" prop="us_status">
          <el-select
            v-model="tableFrom.us_status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1),getLstFilter()"
          >
            <el-option
              v-for="item in productStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="标签：" prop="sys_labels">
          <el-select v-model="tableFrom.sys_labels" placeholder="请选择" class="selWidth" clearable filterable @change="getList(1),getLstFilter()">
            <el-option v-for="item in merProductLabelList" :key="item.id" :label="item.name" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="店铺名称：" prop="mer_id">
          <el-select
            v-model="tableFrom.mer_id"
            clearable
            filterable
            remote
            :remote-method="getMerSelect"
            placeholder="请选择"
            class="selWidth my-select"
            @visible-change="getMerSelect()"
            @change="getMerSelect(),getList(1),getLstFilter()"
          >
            <el-option
              v-for="item in merSelect"
              :key="item.mer_id"
              :label="item.mer_name"
              :value="item.mer_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="活动名称：" prop="active_name">
          <el-input
            clearable
            v-model="tableFrom.active_name"
            @keyup.enter.native="getList(1),getLstFilter()"
            placeholder="请输入活动名称"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item label="店铺类别：" prop="is_trader">
          <el-select
            v-model="tableFrom.is_trader"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1),getLstFilter()"
          >
            <el-option label="自营" value="1" />
            <el-option label="非自营" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="推荐级别：" prop="star">
          <el-select
            v-model="tableFrom.star"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1),getLstFilter()"
          >
            <el-option
              v-for="item in recommendedLevelStatus"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
       <el-form-item label="商品搜索：" prop="keyword">
        <el-input
          v-model="tableFrom.keyword"
          @keyup.enter.native="getList(1),getLstFilter()"
          placeholder="请输入商品名称，关键字，产品编号"
          class="selWidth"
        />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1),getLstFilter()">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-tabs v-model="tableFrom.type" @tab-click="getList(1),getLstFilter()">
        <el-tab-pane
          v-for="(item,index) in headeNum"
          :key="index"
          :name="item.type.toString()"
          :label="item.name +'('+item.count +')' "
        />
      </el-tabs>
      <div v-if="tableFrom.type == 6||tableFrom.type == 1" class="mt5">
        <el-button v-if="tableFrom.type == 6" type="primary" size="small" :disabled="multipleSelection.length<=0" @click="batch">批量审核</el-button>
        <el-button v-if="tableFrom.type == 1" type="primary" size="small" :disabled="multipleSelection.length<=0" @click="batchOff">批量强制下架</el-button>
      </div>
      <el-table
        class="mt20"
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        @selection-change="handleSelectionChange"
      >
        <el-table-column key="2" type="selection" width="55" />
        <el-table-column type="expand">
          <template slot-scope="scope">
            <el-form label-position="left" inline class="demo-table-expand">
              <el-form-item label="平台分类：">
                <span>{{ scope.row.storeCategory ? scope.row.storeCategory.cate_name:'-' }}</span>
              </el-form-item>
              <el-form-item label="品牌：">
                <span>{{ scope.row.brand ? scope.row.brand.brand_name: '其它' }}</span>
              </el-form-item>
              <el-form-item label="划线价：">
                <span>{{ scope.row.ot_price | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="成本价：">
                <span>{{ scope.row.cost | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="收藏：">
                <span>{{ scope.row.care_count | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="已售数量：">
                <span>{{ scope.row.ficti | filterEmpty }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column prop="product_id" label="ID" min-width="50" />
        <el-table-column label="店铺名称" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.merchant ? scope.row.merchant.mer_name : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="mer_name" label="店铺类别" min-width="90">
          <template slot-scope="scope">
            <span v-if="scope.row.merchant" class="spBlock">{{ scope.row.merchant.is_trader ? '自营' : '非自营' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品图片" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image :src="scope.row.image" :preview-src-list="[scope.row.image]" />
            </div>
          </template>
        </el-table-column>
         <el-table-column label="商品名称" min-width="160">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.store_name }}</div>
              <span class="line2">{{ scope.row.store_name }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="seckillActive.name" label="活动名称" min-width="100" />
        <el-table-column prop="sales" label="已售数量" min-width="80" />
        <el-table-column prop="stock" label="限量剩余" min-width="90" />
        <el-table-column prop="price" label="秒杀价" min-width="80" />
        <el-table-column label="推荐星级"  min-width="150">
          <template slot-scope="scope">
            <el-rate
            disabled
            v-model="scope.row.star"
            :colors="colors">
           </el-rate>
          </template>
        </el-table-column>
        <el-table-column prop="rank" label="排序" min-width="70" />
         <el-table-column prop="stock" label="商品状态" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.us_status | productStatusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column v-if="tableFrom.type == 7" key="1" label="审核状态" min-width="120">
          <template slot-scope="scope">
            <span>审核未通过</span>
            <span style="display: block; font-size: 12px; color: red;">
              原因：{{ scope.row.refusal }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="seckillActive.status_text" label="活动状态" min-width="90" />
        <el-table-column  v-if="tableFrom.type!=5" key="10" prop="status" label="是否显示" fixed="right" min-width="90">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_used"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="隐藏"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column
          v-if="tableFrom.type!=5"
          key="8"
          label="操作"
          :min-width="tableFrom.type<7 ? '180' : '120'"
          fixed="right"
        >
          <template slot-scope="scope">
            <el-button v-if="tableFrom.type!=5" type="text" size="small" @click="goDetail(scope.row.product_id)">详情</el-button>
            <el-button type="text" size="small" v-if="tableFrom.type==6" @click="toExamine(scope.row.product_id)">审核</el-button>
            <el-button v-if="tableFrom.type<7 && tableFrom.type!=5" type="text" size="small" @click="handlePreview(scope.row.product_id)">预览</el-button>
            <el-dropdown v-if="tableFrom.type!=5 || tableFrom.type==1" class="ml10">
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right" />
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item v-if="tableFrom.type!=5" @click.native="onEdit(scope.row.product_id)">编辑</el-dropdown-item>
                <el-dropdown-item v-if="tableFrom.type!=5" @click.native="onEditLabel(scope.row)">编辑标签</el-dropdown-item>
                <el-dropdown-item v-if="tableFrom.type==1" @click.native="toOff(scope.row.product_id)">强制下架</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
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
    <info-from ref="infoFrom" :ids="OffId" @subSuccess="subSuccess" />
    <!--预览商品-->
    <div v-if="previewVisible">
      <div class="bg" @click.stop="previewVisible = false" />
      <preview-box v-if="previewVisible" ref="previewBox" :goods-id="goodsId" :product-type="1" :preview-key="previewKey" is-seckill />
    </div>
    <!--编辑标签-->
    <el-dialog
      v-if="dialogLabel"
      title="选择标签"
      :visible.sync="dialogLabel"
      width="470px"
      :before-close="handleClose"
    >
      <el-form ref="labelForm" :model="labelForm" size="small" @submit.native.prevent label-suffix="：" inline>
        <el-form-item label="标签">
          <el-select v-model="labelForm.sys_labels" clearable filterable multiple placeholder="请选择" style="width: 360px;">
            <el-option
              v-for="item in merProductLabelList"
              :key="item.id"
              :label="item.name"
              :value="item.id.toString()"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="dialogLabel=false">取消</el-button>
        <el-button type="primary" size="small" @click="submitForm('labelForm')">提交</el-button>
      </span>
    </el-dialog>
    <!--商品详情-->
    <pro-detail
      ref="proDetail"
      :productId="product_id"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
      @handleAudit="toExamine"
      @getList="getList"
      :drawer="drawer"
    ></pro-detail>
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
  seckillChangeApi,
  seckillProductLstApi,
  seckillProductUpdateApi,
  seckillLstFilterApi,
  seckillProductOffApi,
  merSelectListApi,
  updatetSeckillLabel
} from '@/api/product'
import { seckillProductExamine } from "@/api/marketing";
import { mapGetters } from 'vuex';
import { roterPre } from '@/settings'
import infoFrom from './info'
import proDetail from './proDetails.vue';
import previewBox from '@/components/previewBox/index'
export default {
  name: 'SeckilProductList',
  components: { infoFrom, proDetail, previewBox },
  data() {
    return {
      ruleValidate: {},
      dialogVisible: false,
      drawer: false,
      checkboxGroup: [],
      formValidate: {
        is_hot: 0,
        is_best: 0,
        is_new: 0,
        is_benefit: 0,
        ficti: 1,
        content: '',
        store_name: ''
      },
      attrInfo: {},
      colors: ['#99A9BF', '#F7BA2A', '#FF9900'],
       seckillStatusList: [
        { label: "正在进行", value: 1 },
        { label: "已结束", value: -1 },
        { label: "未开始", value: 0 },
      ],
      productStatusList: [
        { label: "上架显示", value: 1 },
        { label: "下架", value: 0 },
        { label: "平台关闭", value: -1 },
      ],
      recommendedLevelStatus: [
        { label: "全部", value: "" },
        { label: "5星", value: 5 },
        { label: "4星", value: 4 },
        { label: "3星", value: 3 },
        { label: "2星", value: 2 },
        { label: "1星", value: 1 }
      ],
      fullscreenLoading: false,
      roterPre: roterPre,
      listLoading: true,
      labelLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        cate_id: '',
        mer_id: '',
        type: "1",
        seckill_status: "",
        sys_labels: "",
        keyword: '',
        is_trader: '',
        us_status: "",
        star: "",
        active_name: ""
      },
      product_id: '',
      categoryList: [],
      merCateList: [],
      merSelect: [],
      multipleSelection: [],
      headeNum: [],
      OffId: [],
      productId: 0,
      previewVisible: false,
      goodsId: '',
      previewKey: '',
      dialogLabel: false,
      labelForm: {}
    }
  },
  computed: {
    ...mapGetters(['merProductLabelList']),
  },
  mounted() {
    this.getMerSelect()
    if (!this.merProductLabelList.length) this.$store.dispatch('product/getProductLabel');
    this.getList('')
    this.getLstFilter()
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
      this.getLstFilter()
    },
    subSuccess() {
      this.getList('')
      this.getLstFilter()
    },
    // 店铺列表；
    getMerSelect(query) {
      merSelectListApi({keyword: query})
        .then((res) => {
          this.merSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    onchangeIsShow(row) {
      seckillChangeApi(row.product_id, row.is_used)
        .then(({ message }) => {
          this.$message.success(message)
          this.getList('')
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
    },
    // 编辑标签
    onEditLabel(row) {
      this.dialogLabel = true
      this.product_id = row.product_id
      this.labelForm = {
        sys_labels: row.sys_labels
      }
    },
    submitForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          updatetSeckillLabel(this.product_id, this.labelForm).then(({ message }) => {
            this.$message.success(message)
            this.getList('')
            this.dialogLabel = false
          }).catch((res) => {
              this.$message.error(res.message)
            })
        } else {
          return
        }
      })
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          seckillProductUpdateApi(this.productId, this.formValidate)
            .then(async(res) => {
              this.fullscreenLoading = false
              this.$message.success(res.message)
              this.dialogVisible = false
              this.getList('')
            })
            .catch((res) => {
              this.fullscreenLoading = false
              this.$message.error(res.message)
            })
        } else {
          return false
        }
      })
    },
    onChangeGroup() {
      this.checkboxGroup.includes('is_benefit') ? this.formValidate.is_benefit = 1 : this.formValidate.is_benefit = 0 && this.checkboxGroup.remove('is_benefit')
      this.checkboxGroup.includes('is_best') ? this.formValidate.is_best = 1 : this.formValidate.is_best = 0 && this.checkboxGroup.remove('is_best')
      this.checkboxGroup.includes('is_new') ? this.formValidate.is_new = 1 : this.formValidate.is_new = 0 && this.checkboxGroup.remove('is_new')
      this.checkboxGroup.includes('is_hot') ? this.formValidate.is_hot = 1 : this.formValidate.is_hot = 0 && this.checkboxGroup.remove('is_hot')
    },
    handleClose() {
      this.dialogVisible = false
      this.dialogLabel = false
    },
    watCh(val) {

    },
    // 预览
    handlePreview(id) {
      this.previewVisible = true
      this.goodsId = id
      this.previewKey = ''
    },
    // 查看详情
    goDetail(id){
      this.product_id = id;
      this.drawer = true;
      this.$refs.proDetail.getInfo(id,false);
      this.loading = true;
    },
    // 编辑
    onEdit(id){
      this.product_id = id;
      this.drawer = true;
      this.$refs.proDetail.getInfo(id,true);
      this.loading = true;
    },
    // 批量下架
    batchOff() {
      if (this.multipleSelection.length === 0) { return this.$message.warning('请先选择商品') }
      this.toOff(this.OffId)
    },
    // 下架
    toOff(id) {
      this.$prompt('','强制下架', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputErrorMessage: '请输入强制下架原因',
        inputType: 'textarea',
        inputPlaceholder: '请输入强制下架原因',
        inputValidator: (value) => {
          if (!value) {
            return '请输入强制下架原因'
          }
        }
      })
        .then(({ value }) => {
          seckillProductOffApi({ id: id, status: -2, refusal: value })
            .then((res) => {
              this.$message({
                type: 'success',
                message: '提交成功'
              })
              this.getLstFilter()
              this.getList('')
            })
            .catch((res) => {
              this.$message.error(res.message)
            })
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '取消输入'
          })
        })
    },
    // 列表表头；
    getLstFilter() {
      seckillLstFilterApi(this.tableFrom)
        .then((res) => {
          this.headeNum = res.data
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    batch() {
      if (this.multipleSelection.length === 0) { return this.$message.warning('请先选择商品') }
      this.$refs.infoFrom.dialogVisible = true
    },
    handleSelectionChange(val) {
      this.multipleSelection = val
      const data = []
      this.multipleSelection.map((item) => {
        data.push(item.product_id)
      })
      this.OffId = data
    },
    toExamine(id) {
      this.$modalForm(seckillProductExamine(id)).then(() => this.getList(''))
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      seckillProductLstApi(this.tableFrom)
        .then((res) => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch((res) => {
          this.listLoading = false
          this.$message.error(res.message)
        })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList(1)
    }
  }
}
</script>
<style lang="scss">
.contentPic img{
  max-width: 100%;
}
</style>
<style scoped lang="scss">
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.1);
}
.demo-table-expand {
  font-size: 0;
}
.demo-table-expand ::v-deep label {
  width: 77px;
  color: #99a9bf;
}
.demo-table-expand .el-form-item {
  margin-right: 0;
  margin-bottom: 0;
  width: 33.33%;
}
.title{
  color: #17233d;
  font-size: 14px;
  font-weight: bold;
  line-height: 15px;
  padding-left: 5px;
  border-left: 3px solid var(--prev-color-primary);
}


</style>

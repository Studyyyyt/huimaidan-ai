<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" :inline="true">
        <el-form-item label="商品名称：" prop="keyword">
          <el-input v-model="tableFrom.keyword" placeholder="请输入商品名称，关键字，产品编号" clearable class="selWidth" @keyup.enter.native="getList(1),getLstFilterApi()" />
        </el-form-item>
        <el-form-item label="活动名称：" prop="active_name">
          <el-input
            clearable
            v-model="tableFrom.active_name"
            @keyup.enter.native="getList(1),getLstFilterApi()"
            placeholder="请输入活动名称"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item label="商品状态：" prop="us_status">
          <el-select
            v-model="tableFrom.us_status"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1),getLstFilterApi()"
          >
            <el-option
              v-for="item in productStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="活动状态：" prop="active_status">
          <el-select
            v-model="tableFrom.active_status"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1),getLstFilterApi()"
          >
            <el-option
              v-for="item in seckillStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <!-- <el-form-item label="商品标签：" prop="mer_labels">
          <el-select
            v-model="tableFrom.mer_labels"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
          >
            <el-option
              v-for="item in labelList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item> -->

        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1),getLstFilterApi()">搜索</el-button>
          <el-button size="small" @click="searchReset(),getLstFilterApi()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-tabs v-model="tableFrom.type" @tab-click="getList(1),getLstFilterApi()">
        <el-tab-pane
          v-for="(item,index) in headeNum"
          :key="index"
          :name="item.type.toString()"
          :label="item.name +'('+item.count +')' "
        />
      </el-tabs>
      <el-table v-loading="listLoading" :data="tableData.data" class="mt14" size="small" :row-class-name="tableRowClassName" @rowclick.stop="closeEdit">
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-form label-position="left" inline class="demo-table-expand demo-table-expand1">
              <el-form-item label="品牌：">
                <span class="mr10">{{ props.row.brand?props.row.brand.brand_name:'其他' }}</span>
              </el-form-item>
              <el-form-item label="划线价格：">
                <span>{{ props.row.ot_price | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="成本价：">
                <span>{{ props.row.cost | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="收藏：">
                <span>{{ props.row.care_count | filterEmpty }}</span>
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column prop="product_id" label="ID" min-width="50" />
        <el-table-column label="商品图片" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image :src="scope.row.image" :preview-src-list="[scope.row.image]" />
            </div>
          </template>
        </el-table-column>
         <el-table-column label="商品名称" min-width="200">
          <template slot-scope="scope">
            <el-tooltip placement="top" :open-delay="600">
              <div slot="content">{{ scope.row.store_name }}</div>
              <span class="line2"><span class="tags_name" :class="'name'+scope.row.spec_type">{{ scope.row.spec_type==0 ? '[单规格]' : '[多规格]' }}</span>{{ scope.row.store_name }}</span>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="seckillActive.name" label="活动名称" min-width="120" />
        <el-table-column prop="sales" label="已售数量" min-width="90" />
        <el-table-column prop="stock" label="限量剩余" min-width="90" />
        <el-table-column prop="price" label="秒杀价" min-width="90" />
        <el-table-column prop="sort" align="center" label="排序" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.index === tabClickIndex">
              <el-input v-model.number="scope.row['sort']" type="number" maxlength="300" size="mini" autofocus @blur="inputBlur(scope)" />
            </span>
            <span v-else @dblclick.stop="tabClick(scope.row)">{{ scope.row['sort'] }}</span>
          </template>
        </el-table-column>
        <el-table-column label="商品状态" min-width="90">
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
        <el-table-column prop="status" label="上/下架" min-width="80">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              :width="55"
              active-text="上架"
              inactive-text="下架"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column
          key="8"
          label="操作"
          :min-width="tableFrom.type==5 ? '120' : '160'"
          fixed="right"
        >
        <template slot-scope="scope">
          <el-button type="text" size="small" @click="goDetail(scope.row.product_id)">详情</el-button>
          <el-button v-if="tableFrom.type!=5" type="text" size="small" @click="handlePreview(scope.row.product_id)">预览</el-button>
          <el-dropdown class="ml10">
            <span class="el-dropdown-link">
              更多<i class="el-icon-arrow-down el-icon--right" />
            </span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item v-if="tableFrom.type!=5" @click.native="onEdit(scope.row.product_id)">编辑</el-dropdown-item>
              <el-dropdown-item v-if="tableFrom.type!=5" @click.native="onEditLabel(scope.row)">编辑标签</el-dropdown-item>
              <el-dropdown-item v-if="tableFrom.type==2 || tableFrom.type==5 || tableFrom.type==7" @click.native="handleDelete(scope.row.product_id)">{{(tableFrom.type==2 || tableFrom.type==7) ? '加入回收站' : '删除'}}</el-dropdown-item>
              <el-dropdown-item v-if="tableFrom.type==5" @click.native="handleRestore(scope.row.product_id)">恢复商品</el-dropdown-item>
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
    <el-dialog v-if="dialogVisible" title="秒杀商品详情" center :visible.sync="dialogVisible" width="660px">
      <div v-loading="loading" style="margin-top: 5px;">
        <div class="box-container">
          <div class="title">基本信息</div>
          <div class="acea-row">
            <div class="list sp100"><label class="name">商品ID：</label>{{ formValidate.product_id }}</div>
            <div class="list sp100"><label class="name">商品名称：</label><span>{{ formValidate.store_name }}</span></div>
            <div class="list sp100 image">
              <label class="name">商品图：</label>
              <img
                style="max-width: 150px; height: 80px;"
                :src="formValidate.image"
              >
            </div>
            <div class="list sp100">
              <label class="name">商品信息:</label>
              <!-- 单规格表格-->
              <div v-if="formValidate.spec_type === 0">
                <el-table :data="OneattrValue" size="small">
                  <el-table-column align="center" label="秒杀价格" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row['price'] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="已售商品数量" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row['sales'] }}</span>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
              <!-- 多规格表格-->
              <div
                v-if="formValidate.spec_type === 1"
                class="labeltop"
                label="规格列表："
              >
                <el-table
                  :data="ManyAttrValue"
                  height="250"
                  size="small"
                  tooltip-effect="dark"
                  :row-key="(row) => { return row.id }"
                >
                  <template v-if="manyTabDate">
                    <el-table-column v-for="(item,iii) in manyTabDate" :key="iii" align="center" :label="manyTabTit[iii].title" min-width="80">
                      <template slot-scope="scope">
                        <span class="priceBox" v-text="scope.row[iii]" />
                      </template>
                    </el-table-column>
                  </template>
                  <el-table-column align="center" label="秒杀价格" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row['price'] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="已售商品数量" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row['sales'] }}</span>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>
          </div>
          <div class="title" style="margin-top: 20px;">秒杀商品活动信息</div>
          <div class="acea-row">
             <div v-if="formValidate.mer_labels_data&&formValidate.mer_labels_data.length" class="list sp100">
              <label class="name">商户标签：</label>
               <template>
                <span v-for="(item,index) in formValidate.mer_labels_data" :key="index" class="value-item"> {{item}} </span>
              </template>
            </div>
            <div class="list sp100"><label class="name">秒杀简介：</label>{{ formValidate.store_info }}</div>
            <div class="list sp100"><label class="name">秒杀活动日期：</label>{{ formValidate.start_day + '-' + formValidate.end_day }}</div>
            <div class="list sp100"><label class="name">秒杀活动时间：</label>{{ formValidate.start_time + '-' + formValidate.end_time }}</div>
            <div class="list sp100"><label class="name">活动日期内最多购买次数：</label>{{ formValidate.all_pay_count }}</div>
            <div class="list sp100"><label class="name">秒杀时间段内最多购买次数：</label>{{ formValidate.once_pay_count }}</div>
            <div class="list sp"><label class="name">审核状态：</label>{{ formValidate.status === 1 ? '审核通过' : formValidate.status === 0 ? '未审核' : '审核未通过' }}</div>
            <div class="list sp"><label class="name">商品状态：</label>{{ formValidate.us_status === 1 ? '上架显示' : formValidate.us_status === -1 ? '平台关闭' : '下架' }}</div>
            <div class="list sp"><label class="name">秒杀活动状态：</label>{{ formValidate.seckill_status === 0 ? '未开始' : formValidate.seckill_status === 1 ? '正在进行' : '已结束' }}</div>
            <div class="list sp"><label class="name">创建时间：</label>{{ formValidate.create_time }}</div>
          </div>
        </div>
      </div>
    </el-dialog>
    <!--预览商品-->
    <div v-if="previewVisible">
      <div class="bg" @click.stop="previewVisible = false" />
      <preview-box v-if="previewVisible" ref="previewBox" :goods-id="goodsId" :product-type="1" :preview-key="previewKey" />
    </div>
    <!--编辑标签-->
    <el-dialog
      v-if="dialogLabel"
      title="选择标签"
      :visible.sync="dialogLabel"
      width="470px"
      :before-close="handleClose"
    >
      <el-form ref="labelForm" :model="labelForm" @submit.native.prevent>
        <el-form-item>
          <el-select v-model="labelForm.mer_labels" clearable multiple size="small" placeholder="请选择" style="width: 100%;">
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
  seckillProSort,
  seckillProductLstApi,
  spikeDestoryApi,
  spikeRestoreApi,
  updatetSeckillLabel,
  spikeStatusApi,
  spikelstFilterApi,
  spikeProductDeleteApi,
} from '@/api/marketing'
import proDetail from './proDetails.vue';
import previewBox from '@/components/previewBox/index'
import { roterPre } from '@/settings'
import { mapGetters } from 'vuex';
export default {
  name: 'SeckillProductList',
  components: { previewBox, proDetail },
  data() {
    return {
      props: {
        emitPath: false
      },
      roterPre: roterPre,
      headeNum: [],
      listLoading: true,
      labelLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      seckillStatusList: [
        { label: "正在进行", value: 1 },
        { label: "未开始", value: 0 },
        { label: "已结束", value: -1 },
      ],
      productStatusList: [
        { label: '上架显示', value: 1 },
        { label: '下架', value: 0 },
        { label: '平台关闭', value: -1 }
      ],
      tableFrom: {
        page: 1,
        limit: 20,
        active_name: "",
        mer_cate_id: '',
        cate_id: '',
        mer_labels: '',
        keyword: '',
        type: this.$route.query.type ? this.$route.query.type : '1',
        seckill_status: '',
        us_status: '',
      },
      modals: false,
      dialogVisible: false,
      manyTabTit: {},
      manyTabDate: {},
      attrInfo: {},
      tabClickIndex: '',
      previewVisible: false,
      goodsId: '',
      product_id: "",
      previewKey: '',
      drawer: false,
      dialogLabel: false,
      labelForm: {}
    }
  },
   computed: {
    ...mapGetters(['merCateList','merProductLabelList','sysCateList']),
  },
  mounted() {
    if (!this.merProductLabelList.length) this.$store.dispatch('product/getProductLabel');
    this.getLstFilterApi()
    this.getList('')
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 把每一行的索引放进row
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex
    },
    // 添加明细原因 row 当前行 column 当前列
    tabClick(row) {
      this.tabClickIndex = row.index
    },
    // 失去焦点初始化
    inputBlur(scope) {
      if (!scope.row.sort || scope.row.sort < 0)scope.row.sort = 0
      seckillProSort(scope.row.product_id, { sort: scope.row.sort })
        .then((res) => {
          this.$message.success(res.message)
          this.closeEdit()
          this.getList('')
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
    },
    closeEdit() {
      this.tabClickIndex = null
    },
    handleClose() {
      this.dialogLabel = false
    },
    // 编辑
    onEdit(id){
      this.product_id = id;
      this.drawer = true;
      this.$refs.proDetail.getInfo(id,true);
      this.loading = true;
    },
    handleRestore(id) {
      this.$modalSure('恢复商品').then(() => {
        spikeRestoreApi(id)
          .then((res) => {
            this.$message.success(res.message)
            this.getLstFilterApi()
            this.getList('')
          })
          .catch((res) => {
            this.$message.error(res.message)
          })
      })
    },
    // 列表表头；
    getLstFilterApi() {
      spikelstFilterApi(this.tableFrom)
        .then((res) => {
          this.headeNum = res.data
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    watCh(val) {
      const tmp = {}
      const tmpTab = {}
      this.formValidate.attr.forEach((o, i) => {
        tmp['value' + i] = { title: o.value }
        tmpTab['value' + i] = ''
      })
      this.ManyAttrValue.forEach((val, index) => {
        const key = Object.values(val.detail).sort().join('/')
        if (this.attrInfo[key]) this.ManyAttrValue[index] = this.attrInfo[key]
      })
      this.attrInfo = {}
      this.ManyAttrValue.forEach((val) => {
        this.attrInfo[Object.values(val.detail).sort().join('/')] = val
      })
      this.manyTabTit = tmp
      this.manyTabDate = tmpTab
      // this.formThead = Object.assign({}, this.formThead, tmp);
    },
    // 查看详情
    goDetail(id) {
      this.product_id = id;
      this.drawer = true;
      this.$refs.proDetail.getInfo(id,false);
      this.loading = true;
    },
    // 编辑标签
    onEditLabel(row) {
      this.dialogLabel = true
      this.labelLoading = true
      this.product_id = row.product_id
      this.labelForm = {
        mer_labels: row.mer_labels
      }
    },
    submitForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          updatetSeckillLabel(this.product_id, this.labelForm).then(({ message }) => {
            this.$message.success(message)
            this.getList('')
            this.dialogLabel = false
          })
        } else {
          return
        }
      })
    },
    // 预览
    handlePreview(id) {
      this.previewVisible = true
      this.goodsId = id
      this.previewKey = ''
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
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
      this.getList('')
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure(this.tableFrom.type !== '5' ? '加入回收站' : '删除该商品').then(
        () => {
          this.tableFrom.type === '5'
            ? spikeDestoryApi(id)
              .then(({ message }) => {
                this.$message.success(message)
                this.getList('')
                this.getLstFilterApi()
              })
              .catch(({ message }) => {
                this.$message.error(message)
              })
            : spikeProductDeleteApi(id)
              .then(({ message }) => {
                this.$message.success(message)
                this.getList('')
                this.getLstFilterApi()
              })
              .catch(({ message }) => {
                this.$message.error(message)
              })
        }
      )
    },
    onchangeIsShow(row) {
      spikeStatusApi(row.product_id, row.is_show)
        .then(({ message }) => {
          this.$message.success(message)
          this.getList('')
          this.getLstFilterApi()
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    }
  }
}
</script>

<style scoped lang="scss">
::v-deep .priceBox .el-input__inner {
  text-align: center;
}
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
.tags_name{
  font-size: 10px;
  height: 16px;
  line-height: 16px;
  padding: 0 2px;
  margin-right: 2px;
  &.name0{
    color: var(--prev-color-primary);
  }
  &.name1{
    color: #FF8A4D;
  }
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
.goods_detail .goods_detail_wrapper{
    z-index: -10
}
::v-deep table .el-input__inner{padding: 0;}
.add {
  font-style: normal;
  position: relative;
  top: -1.2px;
}
.demo-table-expand {
  font-size: 0;
}
.demo-table-expand1 ::v-deep label {
  width: 77px !important;
  color: #99a9bf;
}
.demo-table-expand .el-form-item {
  margin-right: 0;
  margin-bottom: 0;
  width: 33.33%;
}
.box-container {
  overflow: hidden;
}
.box-container .list {
  float: left;
  margin-top: 15px;
}
.box-container .sp {
  width: 50%;
  font-size: 13px;
}
.box-container .sp3 {
  width: 33.3333%;
}
.box-container .sp100 {
  width: 100%;
  font-size: 13px;
}
.box-container .list .name {
  display: inline-block;
  color: #606266;
}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
.box-container .list.image {
  position: relative;
  display: flex;
  align-items: center;
}

.labeltop{
  max-height: 280px;
  overflow-y: auto;
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

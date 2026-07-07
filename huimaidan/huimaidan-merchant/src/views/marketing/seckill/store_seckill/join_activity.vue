<template>
  <div class="divBox">
    <div class="container_box">
      <pages-header
        ref="pageHeader"
        :title="isLook? '查看活动' : '参加活动'"
        backUrl="/marketing/seckill/store_seckill/list"
      ></pages-header>
      <el-card class="box-card box-body mt14" :bordered="false" shadow="never">
        <el-tabs v-model="activeName">
          <el-tab-pane label="活动信息" name="first"></el-tab-pane>
          <el-tab-pane v-if="isEdit" label="秒杀商品" name="second"></el-tab-pane>
          <el-tab-pane label="已加商品" name="third"></el-tab-pane>
        </el-tabs>
        <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="120px" size="small" class="demo-ruleForm">
          <div v-loading="loading">
            <template v-if="activeName == 'first'">
              <el-form-item label="活动名称：" prop="name">
                <el-input
                  disabled
                  v-model="ruleForm.name"
                  placeholder="请输入活动名称"
                  class="pageWidth"
                ></el-input>
              </el-form-item>
              <el-form-item label="活动日期：" prop="timeVal">
                <el-date-picker
                  disabled
                  v-model="ruleForm.timeVal"
                  value-format="yyyy-MM-dd"
                  align="right"
                  unlink-panels
                  format="yyyy-MM-dd"
                  size="small"
                  type="daterange"
                  placement="bottom-end"
                  placeholder="自定义时间"
                  style="width: 460px;"
                  @change="onchangeTime"
                  :picker-options="pickerOptions"
                />
                <p class="desc mt10">设置活动开始日期与结束日期，用户可以在有效时间内参与秒杀</p>
              </el-form-item>
              <el-form-item label="秒杀场次：" prop="seckill_time_ids">
                <el-select
                  disabled
                  v-model="ruleForm.seckill_time_ids"
                  placeholder="请选择秒杀场次"
                  multiple
                  cearable
                  class="pageWidth disabled"
                >
                  <el-option
                    v-for="item in spikeTimeList"
                    :key="item.seckill_time_id + 'onl'"
                    :label="item.title + ' | ' + item.start_time + '-' + item.end_time"
                    :value="item.seckill_time_id.toString()"
                    :disabled="item.status === 0"
                  />
                </el-select>
                <p class="desc mt10">
                  选择商品开始时间段，该时间段内用户可参与购买；其它时间段会显示活动未开始或已结束，可多选
                </p>
              </el-form-item>
              <el-form-item label="活动限购：">
                <el-input-number
                  disabled
                  v-model="ruleForm.all_pay_count"
                  controls-position="right"
                  :min="0"
                  :max="99999"
                  class="pageWidth"
                ></el-input-number>
                <p class="desc mt10">
                  活动有效期内每个用户可购买该商品总数限制。例如设置为4，表示本次活动有效期内，每个用户最多可购买总数4个，0和空为不限购
                </p>
              </el-form-item>
              <el-form-item label="单次限购：">
                <el-input-number
                  disabled
                  v-model="ruleForm.once_pay_count"
                  controls-position="right"
                  :min="0"
                  :max="99999"
                  class="pageWidth"
                ></el-input-number>
                <p class="desc mt10">
                  用户参与秒杀时，一次购买最大数量限制。例如设置为2，表示参与秒杀时，用户一次购买数量最大可选择2个，0和空为不限购
                </p>
              </el-form-item>
              <el-form-item v-if="ruleForm.product_category_ids&&ruleForm.product_category_ids.length>0" label="商品范围：">
                <el-select
                  disabled
                  class="pageWidth disabled"
                  v-model="ruleForm.product_category_ids"
                  multiple
                  filterable
                  allow-create
                  default-first-option
                  placeholder="请选择商品分类">
                  <el-option
                    v-for="item in categoryList"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value.toString()">
                  </el-option>
                </el-select>
                <p class="desc mt10">设置秒杀活动可以参与的商品分类，可多选，不选为全品类商品。</p>
              </el-form-item>
              <el-form-item v-if="ruleForm.border_pic" label="活动边框图：">
                <div class="acea-row row-middle">
                  <div class="upLoadPicBox disabled">
                    <div class="pictrue">
                      <img :src="ruleForm.border_pic" />
                    </div>
                  </div>
                  <p class="desc ml10">宽750px，高750px
                    <el-popover
                      placement="bottom-start"
                      title=""
                      min-width="200"
                      trigger="hover"
                      >
                      <img :src="`${baseURL}/statics/system/activityBackground.png`" style="height:270px;" alt="">
                      <el-button type="text" slot="reference">查看示例</el-button>
                    </el-popover>
                  </p>
                </div>
                <p class="desc mt10">展示在商品列表的活动边框图</p>  
              </el-form-item>
              <el-form-item label="是否开启:">
                <el-switch
                  disabled
                  :width="56"
                  v-model="ruleForm.status"
                  :active-value="1"
                  :inactive-value="0"
                  active-text="开启"
                  inactive-text="关闭"
                />
              </el-form-item>
            </template>
            <template v-if="activeName == 'second' && isEdit">
              <div v-if="!isLook" class="acea-row row-between-wrapper">
                <div class="acea-row mb20">
                  <el-button class="mr14" size="small" type="primary" @click="addGoods()">添加商品</el-button>
                  <!-- <el-button size="small" :disabled="isShowCheck" @click="setPrice()">批量设置</el-button> -->
                  <el-dropdown class="dropdown mr14" :class="{'disabled' : isShowCheck}" :disabled="isShowCheck" @command="setPrice">
                    <span class="el-dropdown-link">
                      批量设置<i class="el-icon-arrow-down el-icon--right" />
                    </span>
                    <el-dropdown-menu v-if="!isShowCheck" slot="dropdown">
                      <el-dropdown-item command="limit">设置限量</el-dropdown-item>
                      <el-dropdown-item command="price">设置价格</el-dropdown-item>
                      <el-dropdown-item command="open">批量开启</el-dropdown-item>
                      <el-dropdown-item command="close">批量关闭</el-dropdown-item>
                    </el-dropdown-menu>
                  </el-dropdown>
                  <el-button size="small" @click="batchDel" :disabled="isShowCheck">批量删除</el-button>
                </div>
              </div>
              <el-table
                ref="tableList"
                row-key="product_id"
                :data="proData"
                v-loading="listLoading"
                size="small"
                default-expand-all
                :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
              >
                <el-table-column key="1" min-width="30"></el-table-column>
                <el-table-column v-if="!isLook" key="2" min-width="50">
                  <template slot="header" slot-scope="scope">
                    <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll" @change="handleCheckAllChange" />
                  </template>
                  <template slot-scope="scope">
                    <el-checkbox :value="scope.row.checked" @change="(v) => handleCheckOneChange(v, scope.row)" />
                  </template>
                </el-table-column>
                <el-table-column key="3" prop="product_id" label="商品ID" min-width="80">
                  <template slot-scope="scope">
                    <span v-if="!scope.row.sku">{{ scope.row.product_id }}</span>
                  </template>
                </el-table-column>
                <el-table-column key="4" min-width="220" label="商品信息">
                  <template slot-scope="scope">
                    <div class="acea-row">
                      <div class="demo-image__preview mr10 line-heightOne">
                        <el-image :src="scope.row.image" :preview-src-list="[scope.row.image]" />
                      </div>
                      <div class="row_title line2">{{ scope.row.store_name }}</div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column key="5" label="商品分类" min-width="100">
                  <template slot-scope="scope">
                    <span>{{ scope.row.storeCategory?scope.row.storeCategory.cate_name:'-' }}</span>
                  </template>
                </el-table-column> 
                <el-table-column key="7" prop="price" label="售价" min-width="80" />
                <el-table-column key="10" prop="price" label="秒杀价" min-width="120">
                  <template slot-scope="scope" v-if="scope.row.sku">
                    <span v-if="isLook">{{ scope.row.price }}</span>
                    <el-input-number
                      v-else
                      v-model="scope.row.price"
                      type="number"
                      :precision="2"
                      :min="0.01"
                      :max="99999"
                      :controls="false"
                      class="input_width"
                    >
                    </el-input-number>
                  </template>
                </el-table-column>
                <el-table-column key="8" prop="old_stock" label="库存" min-width="80" />
                <el-table-column key="9" label="限量" prop="stock" min-width="120">
                  <template v-if="scope.row.sku" slot-scope="scope">
                    <span v-if="isLook">{{scope.row.stock}}</span>
                    <el-input-number
                      v-else
                      v-model="scope.row.stock"
                      :precision="0"
                      :min="0"
                      :max="scope.row.old_stock"
                      :controls="false"
                      class="input_width"
                    >
                    </el-input-number>
                  </template>
                </el-table-column>
                
                <el-table-column key="11" v-if="!isLook" prop="is_show" label="是否开启" min-width="90">
                  <template slot-scope="scope">
                    <el-switch
                      :width="56"
                      v-model="scope.row.is_show"
                      :active-value="1"
                      :inactive-value="0"
                      active-text="开启"
                      inactive-text="关闭"
                      @change="changeStatus(scope.row)"
                    />
                  </template>
                </el-table-column>
                <el-table-column key="12" prop="sort" label="排序" min-width="110">
                  <template slot-scope="scope" v-if="!scope.row.sku && scope.row.sku!=''">
                    <span v-if="isLook">{{ scope.row.sort }}</span>
                    <el-input-number
                      v-else
                      v-model="scope.row.sort"
                      type="number"
                      :precision="0"
                      :min="0"
                      :max="99999"
                      :controls="false"
                      class="input_width"
                    >
                    </el-input-number>
                  </template>
                </el-table-column>
                <el-table-column v-if="!isLook" key="13" label="操作" min-width="60" fixed="right">
                  <template slot-scope="scope">
                    <el-button
                      type="text"
                      size="small"
                      v-if="!scope.row.sku && scope.row.sku!=''"
                      @click="handleDelete(scope.$index, scope.row)"
                      >删除</el-button
                    >
                  </template>
                </el-table-column>
              </el-table>
            </template>
            <template v-if="activeName == 'third'"> 
              <el-form size="small" inline>
                <el-form-item label="商品审核状态：" prop="status">
                  <el-select v-model="tableFrom.status" placeholder="请选择" class="filter-item selWidth" clearable @change="getJoinedList(1)">
                    <el-option label="待审核" value="0" />
                    <el-option label="审核通过" value="1" />
                    <el-option label="审核失败" value="-1" />
                  </el-select>
                </el-form-item>
                <el-form-item label="商品搜索：">
                  <el-input v-model="tableFrom.keyword" placeholder="请输入商品名称或ID" class="selWidth" clearable @keyup.enter.native="getJoinedList(1)" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" size="small" @click="getJoinedList(1)">搜索</el-button>
                </el-form-item>
              </el-form>
              <el-table
                ref="selectedData"
                row-key="product_id"
                :data="tableData.data"
                v-loading="selectedLoading"
                size="small"
                :default-expand-all="isExpand"
                :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
              >
                <el-table-column key="14" prop="product_id" label="商品ID" min-width="80">
                  <template slot-scope="scope">
                    <!-- <span v-if="scope.row.sku">{{ scope.row.id}}-{{scope.row.product_id}}</span> -->
                    <span v-if="!scope.row.sku">{{ scope.row.product_id }}</span>
                  </template>
                </el-table-column>
                <el-table-column key="15" min-width="220" label="商品信息">
                  <template slot-scope="scope">
                    <div class="acea-row">
                      <div class="demo-image__preview mr10 line-heightOne">
                        <el-image :src="scope.row.image" :preview-src-list="[scope.row.image]" />
                      </div>
                      <div class="row_title line2">{{ scope.row.store_name }}</div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column key="16" label="商品分类" min-width="100">
                  <template slot-scope="scope">
                    <span>{{ scope.row.storeCategory?scope.row.storeCategory.cate_name:'-' }}</span>
                  </template>
                </el-table-column> 
                <el-table-column key="18" prop="ot_price" label="售价" min-width="80" />
                <el-table-column key="21" prop="price" label="秒杀价" min-width="120">
                  <template slot-scope="scope" v-if="scope.row.sku">
                    <span>{{ scope.row.price }}</span>
                  </template>
                </el-table-column>
                <el-table-column key="19" prop="old_stock" label="库存" min-width="80" />
                <el-table-column key="20" label="限量" prop="stock" min-width="120">
                  <template v-if="scope.row.sku" slot-scope="scope">
                    <span>{{scope.row.stock}}</span>
                  </template>
                </el-table-column>
                <el-table-column key="22" prop="sort" label="排序" min-width="110">
                  <template slot-scope="scope" v-if="!scope.row.sku && scope.row.sku!=''">
                    <span>{{ scope.row.sort }}</span> 
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
            </template>
          </div>
        </el-form>
      </el-card>
      <el-card v-if="!isLook" dis-hover class="fixed-card box-card" :bordered="false" shadow="never">
        <div class="acea-row row-center-wrapper">
          <el-button
            v-show="activeName == 'second'"
            size="small"
            type="primary"
            @click="activeName = 'first'"
            >上一步</el-button
          >
          <el-button v-show="activeName == 'first'" size="small" type="primary" @click="activeName = 'second'"
            >下一步</el-button
          >
          <el-button
            type="primary"
            @click="submitForm('ruleForm')"
            size="small"
            >保存</el-button
          >
        </div>
      </el-card>
    </div>
    <activity ref="activityModal" key="keyNum" @onChange="setActivity" />
    <goodsList ref="goodsList" :pid="ruleForm.product_category_ids" :proData="proData" :activeId="active_id" @onSelectList="selectList" />
  </div>
</template>

<script>
import {
  seckillIntervalListApi,
  addProductApi,
  seckillActivityDetailApi,
  seckillProductLstApi,
  getSelectProductList
} from '@/api/marketing';
import { categoryListApi } from '@/api/product';
import goodsList from './goodsList.vue';
import activity from './activity.vue';
import { Debounce } from '@/utils/validate';
import SettingMer from '@/libs/settingMer'
import { roterPre } from '@/settings'
export default {
  name: 'StoreCreatSeckill',
  components: {
    activity,goodsList
  },
  data() {
    return {
      pageType: "",
      roterPre: roterPre,
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      activeName: 'first',
      props: { multiple: true, emitPath: true},
      ruleForm: {
        seckill_time_ids: [],
        start_day: '',
        end_day: '',
        once_pay_count: '',
        all_pay_count: '',
        name: '',
        product_category_ids: [],
        product_list: [],
        timeVal: [],
        proCategorylist: [],
        atmosphere_pic: "",
        border_pic: "",
      },
      rules: {
        name: [{ required: true, message: '请输入活动名称', trigger: 'blur' }],
        seckill_time_ids: [{ required: true, message: '请选择秒杀场次', trigger: 'change' }],
        timeVal: [{ required: true, message: '请选择活动日期' }],
        timeVal2: [{ type: 'array', required: true, message: '请选择秒杀场次', trigger: 'change' }],
        merStars: [{ required: true, message: '请选择商户星级', trigger: 'change' }],
      },
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() < Date.now() - 8.64e7;
        },
      },
      categoryList: [], // 平台分类筛选
      spikeTimeList: [],
      multipleSelection: [],
      activityType: null,
      proData: [],
      spu_ids: [],
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 5,
        status: "",
        keyword: ""
      },
      listLoading: false,
      checkAll: true,
      isIndeterminate: true,
      tempRoute: {},
      isShowCheck: false,
      keyNum: 0,
      loading: false,
      selectedLoading: false,
      isExpand: false,
      active_id: ""
    };
  },
  created() {
   
  },
  watch: {
    '$route.params.id': {
      handler: function(nVal,oVal) {
        if (nVal !== oVal) this.getInfo();
      },
      immediate: false,
      deep: true
    }
  },
  mounted() {
    // this.setTagsViewTitle();
    this.getSeckillIntervalList();
    if (this.$route.params.id) {
      this.getInfo();
    }
    this.getCategoryList()
    this.isCkecked()
  },
  computed: {
    //是否是编辑
    isEdit() {
      return (this.$route.params.type == 'edit' && this.$route.params.id)  ? true : false;
    },
    isLook() {
      return (this.$route.params.type == 'look' && this.$route.params.id) ? true : false;
    },
    title() {
      return (this.$route.params.type == 'look' && this.$route.params.id) ? '查看' : '添加';
    },
  },
  methods: {
    // 平台分类；
    getCategoryList() {
      categoryListApi({type:0,lv:0})
        .then(res => {
          this.categoryList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    // 是否开启
    changeStatus(row){
      if(row.children&&row.children.length>0){
        row.children.forEach((item) => { 
         item.is_show = row.is_show;
        });
      }else{
        let i = this.findFirstIndex(this.proData,row)
        if(i!=-1&&this.proData[i]['children']&&this.proData[i]['children'].length>0){
          if(this.proData[i]['children'].every(item => item.is_show == 0)){
            this.proData[i]['is_show']=0
          }else if(this.proData[i]['children'].every(item => item.is_show == 1) || this.proData[i]['children'].some(item => item.is_show === 1)){
            this.proData[i]['is_show']=1
          }

        }
      }
    },
   findFirstIndex(arr, obj) {
      for (let i = 0; i < arr.length; i++) {
        if (arr[i].product_id === obj.id) {
          return i;
        }
      }
      return -1; // 如果没有找到相同的 id 返回 -1
    },
    // 判断选中没有
    isCkecked() {
      let checked = this.proData.filter((item) => item.checked);
      if (checked.length) {
        this.isShowCheck = false;
      } else {
        this.isShowCheck = true;
      }
    },
    //全选
    handleCheckAllChange(val) {
      this.isIndeterminate = !this.isIndeterminate;
      this.proData.forEach((item) => {
        this.$set(item, 'checked', val);
        item.children.forEach((itm) => {
          this.$set(itm, 'checked', val);
        });
      });
      this.isCkecked();
    },
    //单选
    handleCheckOneChange(val, row) {
      let totalCount = this.proData.length;
      let someStatusCount = 0;
      this.$set(row, 'checked', val);
      if(row.children&&row.children.length){
        row.children.forEach((itm) => {
          this.$set(itm, 'checked', val);
        });
      } 
      this.proData.forEach((item) => {
        if (item.checked === val) {
          someStatusCount++;
        }  
      });
      this.checkAll = totalCount === someStatusCount ? val : !val;
      this.isIndeterminate = someStatusCount > 0 && someStatusCount < totalCount;
      this.isCkecked();
    },
    //详情
    getInfo() {
      this.loading = true;
      seckillActivityDetailApi(this.$route.params.id)
        .then((res) => {
          this.active_id = res.data.seckill_active_id
          let info = res.data;
          this.ruleForm = {
            all_pay_count: info.all_pay_count,
            end_day: info.end_day,
            name: info.name,
            once_pay_count: info.once_pay_count,
            start_day: info.start_day,
            seckill_time_ids: info.seckill_time_ids,
            product_category_ids: info.product_category_ids || [],
            timeVal: [info.start_day, info.end_day],
            status: info.status,
            atmosphere_pic: info.atmosphere_pic,
            border_pic: info.border_pic
          };
          this.tableFrom.seckill_active_id = res.data.seckill_active_id
          this.getJoinedList();
          if(!this.isEdit){
            this.getProductList(res.data.seckill_active_id)
          } 
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
        });
    },
    // 获取活动相关的商品列表
    getProductList(){
      this.listLoading = true;
      getSelectProductList({seckill_active_id: id}) .then((res) => {
        this.proData = res.data.list
        this.getAttrValue(this.proData, true);
        this.listLoading = false;
      })
      .catch((res) => {
        this.listLoading = false;
      });
    },
    // 获取活动之前参与的商品
    getJoinedList(num){
      this.tableFrom.page = num || this.tableFrom.page
      this.selectedLoading = true;
      seckillProductLstApi(this.tableFrom) .then((res) => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.getAttrValue(this.tableData.data, false);
        this.selectedLoading = false;
      })
      .catch((res) => {
        this.selectedLoading = false;
      });
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getJoinedList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getJoinedList('')
    },
    back() {
      this.$router.push({ path: `/marketing/seckill/store_seckill/list` });
    },
    //行删除
    handleDelete(index, row) {
      this.$modalSure('删除该秒杀商品吗').then(() => {
        let i = this.proData.findIndex((item) => item == row);
        this.proData.splice(i, 1);
      });
    },
    setActivity(data,type) {
      this.proData.forEach((item) => {
        item.children.forEach((item1) => {
          if (item.checked && item1.checked) {
            if(type == 'limit'){ //限量
              if(data.limit_type == 0){
                this.$set(item1, 'stock', item1.stock+data.activity_stock);
              }else if(data.limit_type == 1){
                this.$set(item1, 'stock', item1.stock-data.activity_stock > 0 ? item1.stock-data.activity_stock : 0);
              }else if(data.limit_type == 2){
                this.$set(item1, 'stock', data.activity_stock);
              } 
            }else if(type == 'price'){ //秒杀价
              if(data.type == 0){
                this.$set(item1, 'price', item1.price+data.price);
              }else if(data.type == 1){
                this.$set(item1, 'price', item1.price-data.price > 0 ? item1.price-data.price : 0.01);
              }else if(data.type == 2){
                this.$set(item1, 'price', (item1.price * data.price) / 100);
              }else if(data.type == 3){
                this.$set(item1, 'price', data.price);
              }
            }else{
              if(type == '0'){
                this.$set(item1, 'is_show', 0);
              }else if(type == '1'){
                this.$set(item, 'is_show', 1);
                this.$set(item1, 'is_show', 1);
              }
              if(item['children'].every(itm => itm.is_show == 0)){
                this.$set(item, 'is_show', 0);
              }else if(item['children'].every(itm => itm.is_show == 1) || item['children'].some(itm => itm.is_show === 1)){
                this.$set(item, 'is_show', 1);
              }
            }
          }
        });
      });
    },
    // 列表
    getSeckillIntervalList() {
      seckillIntervalListApi({active_id: this.ruleForm.seckill_active_id}).then((res) => {
        this.spikeTimeList = res.data;
      });
    },
    addGoods() {
      this.$refs.goodsList.dialogVisible = true;
      this.$refs.goodsList.getList('');
      this.$refs.goodsList.setCheckedProduct(this.proData);
    },
    //选择完商品确定方法
    selectList(proData) { 
      this.getAttrValue(proData, true);
      this.isCkecked();
    }, 
    // 选中商品
    getAttrValue(row, isEdit) {
      const _this = this;
      row.map((item) => {
        _this.$set(item, 'sort', item.sort ? item.sort : 0);
        _this.$set(item, 'is_show', 1);
        _this.$set(item, 'checked', true);
        _this.$set(item, 'is_show', 1);
        item.attrValue.map((i) => {
          _this.$set(i, 'checked', true);
          _this.$set(i, 'store_name', i.sku || '默认');
          _this.$set(i, 'sku', i.sku || '默认');
          _this.$set(i, 'is_show',  1);
          _this.$set(i, 'stock', i.stock ? i.stock : 0);
          _this.$set(i, 'old_stock', i.old_stock ? i.old_stock : 0);
          _this.$set(i, 'price', i.price ? i.price : 0);
          _this.$set(i, 'id', i.product_id ? i.product_id : 0);
          _this.$set(i, 'product_id', i.value_id ? i.value_id : 0); 
        });
        _this.$set(item, 'children', item.attrValue);
      });
      if(isEdit){
        _this.proData = row;
        _this.isCkecked();
      }else{
        _this.tableData.data = row;
      }
     
    },
    batchDel() {
      this.$modalSure(`批量删除商品吗？`).then(() => {
        this.proData = this.proData.filter((item) => !item.checked);
      });
    },
    //设置活动价
    setPrice(value) {
      this.keyNum = Math.random();
       if(value == 'limit' || value == 'price'){
        this.$refs.activityModal.showModal(value);
      }else{
        if(value == 'open'){
          this.$modalSure('确定批量开启秒杀商品').then(() => {
            this.setActivity(null, '1');
          })
        }else{
          this.$modalSure('确定批量关闭秒杀商品').then(() => {
            this.setActivity(null, '0');
          })
        }
      }  
    },
    // 具体日期
    onchangeTime(e) {
      this.ruleForm.timeVal = e;
      this.ruleForm.start_day = e ? e[0] : '';
      this.ruleForm.end_day = e ? e[1] : '';
    },
    filterIsShowOne(arr) {
      return arr.map(item => {
        if (item.attrValue && item.attrValue.length > 0) {
          const filteredChildren = item.attrValue.filter(child => child.is_show == 1);
          return { ...item, attrValue: filteredChildren };
        } else {
          return item;
        }
      });
    },
    submitForm: Debounce(function (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          let product_list = []
          if (this.proData.length > 0) {
            let total = 0;
            let price = 0;
            this.proData.map((item) => {
              item.children.map((i) => {
                total += i.stock;
                price += i.price;
              });
            });
            let arr = this.proData.filter(item => item.is_show === 1);
            product_list = this.filterIsShowOne(arr)
            if (!total && total !== 0 && this.ruleForm.product_list.length>0) return this.$message.warning('商品限量不能为空');
            if (!price && this.ruleForm.product_list.length>0) return this.$message.warning('商品秒杀价格不能为空');
            if (total < this.proData.length && this.ruleForm.product_list.length>0) return this.$message.warning('商品限量总和不能小于0');
          }
          let parmas = {
            active_id: this.active_id,
            product_list: product_list
          }
          addProductApi(parmas)
            .then((res) => {
              this.$message.success(res.message);
              // this.getJoinedList();
              // this.activeName = "third";
              this.$router.push({ path: `${this.roterPre}/marketing/seckill/store_seckill/list` });
            })
            .catch((res) => {
              this.$message.error(res.message);
            }); 
          } else {
            return false;
          }
        });
     }),
  },
};
</script>

<style lang="scss" scoped>
.dropdown {
  padding: 0 10px;
  border: 1px solid var(--prev-color-primary);
  height: 32px;
  display: flex;
  align-items: center;
  border-radius: 4px;
  &.disabled{
    border-color: #e6ebf5;
    cursor: not-allowed;
    .el-dropdown-link {
      color: #C0C4CC;
      cursor: not-allowed;
    }
  }
}
.add_title {
  position: relative;
}
::v-deep .disabled .el-tag, .disabled .upLoad{
  cursor: not-allowed;
}
.box-body {
  ::v-deep.el-card__body {
    padding-top: 0px;
  }
}
.el-table__row--level-1 .el-checkbox{
  margin-left: 10px;
}
.row_title {
  max-width: 150px !important;
}
.input_width {
  width: 90px;
}
.desc {
  color: #999;
  font-size: 12px;
  line-height: 16px;
  margin: 0;
}
.mt10 {
  margin-top: 10px;
}
.mr14 {
  margin-right: 14px;
}
.pictrue {
  width: 58px;
  height: 58px;
  margin-right: 10px;
  position: relative;
  img {
    width: 100%;
    height: 100%;
  }
  .del {
    position: absolute;
    top: 0;
    right: 0;
  }
}
::v-deep .el-input-number .el-input-number__decrease, 
::v-deep .el-input-number .el-input-number__increase{
  display: block;
}
</style>

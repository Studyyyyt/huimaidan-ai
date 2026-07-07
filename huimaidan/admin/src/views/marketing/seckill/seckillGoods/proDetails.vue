<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1100px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div v-loading="loading">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">{{ productData.store_name }}</div>
              <div>
                <span class="mr20">商品ID：{{ productData.product_id }}</span>
                <!-- <span class="mr20">商品类型：{{productData.type == 0 ? '普通商品' : productData.type == 1 ? '虚拟商品' : '卡密商品' }}</span>
                <span class="mr20">商品状态：{{ productData.product_id }}</span> -->
              </div>
            </div>
            <div>
              <el-button
                v-if="isEdit"
                size="small"
                @click="cancelEdit"
                >取消</el-button
              >
              <el-button
                v-if="!isEdit && (productData.status == 0 || productData.status == 1)"
                type="primary"
                size="small"
                @click="handleEdit"
                >编辑</el-button
              >
              <el-button
                v-if="isEdit"
                type="success"
                size="small"
                @click="saveInfo"
                >完成</el-button
              >
              <el-dropdown v-if="productData.status == 0" @command="handleCommand" class="ml10">
                <el-button icon="el-icon-more" size="small"></el-button>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="password">审核</el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">类型</div>
              <div>{{productData.type == 0 ? '普通商品' : productData.type == 1 ? '虚拟商品' : '卡密商品' }}</div>
            </li>
            <li class="item">
              <div class="title">状态</div>
              <div>{{ productData.status == 1 ? '上架显示' : '下架' }}</div>
            </li>
            <li class="item">
              <div class="title">销量</div>
              <div>{{ productData.sales }}</div>
            </li>
            <li class="item">
              <div class="title">库存</div>
              <div>{{ productData.stock }}</div>
            </li>
            <li class="item">
              <div class="title">创建时间</div>
              <div>{{ productData.create_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="基本信息" name="basic">
            <div class="section">
              <ul class="list">
                <li class="item item100">
                  <div class="item-title">封面图：</div>
                  <img :src="productData.image" style="width:40px;height:40px;margin-right:12px;"/>
                </li>
                <li class="item item100">
                  <div class="item-title">轮播图：</div>
                  <img v-for="(pic,idx) in productData.slider_image" :key="idx" :src="pic" style="width:40px;height:40px;margin-right:12px;"/>
                </li>
              </ul>
              <li class="item item100">
                <div class="item-title">商品简介：</div>
                  <div class="value">{{productData.store_info}}</div>
                </li>
              <ul class="list">
                <li class="item">
                  <div class="item-title">平台分类：</div>
                  <div class="value">{{productData.storeCategory&&productData.storeCategory.cate_name || '-'}}</div>
                </li>
                <li v-if="productData.merCateId&&productData.merCateId.length>0" class="item">
                  <div class="item-title">店铺分类：</div>
                  <div class="value">
                    <span v-for="(item,index) in productData.merCateId" :key="index">{{item.category&&item.category.cate_name}}&nbsp;&nbsp;&nbsp;&nbsp;</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">商品标签：</div>
                  <div v-if="(productData.mer_labels&&productData.mer_labels_data.length) || (productData.sys_labels_data&&productData.sys_labels_data.length)" class="value">
                    <template v-if="productData.mer_labels_data&&productData.mer_labels_data.length">
                      <span v-for="(item,index) in productData.mer_labels_data" :key="index" class="value-item"> {{item}} </span>
                    </template>
                    <template v-if="productData.sys_labels_data&&productData.sys_labels_data.length">
                      <span v-for="(item,index) in productData.sys_labels_data" :key="index" class="value-item"> {{item}} </span>
                    </template>
                  </div>
                  <div v-else class="value"><span>-</span></div>
                </li>
                <li class="item">
                  <div class="item-title">品牌选择：</div>
                  <div class="value">{{productData.brand&&productData.brand.brand_name || '其它'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">单位：</div>
                  <div class="value">{{productData.unit_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">关键字：</div>
                  <div class="value">{{productData.keyword || '-'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">配送方式：</div>
                  <template v-if="productData.type==0">
                    <div v-if="productData.delivery_way.length==2" class="value">快递/到店自提</div>
                    <div v-else-if="productData.delivery_way.length==1">{{productData.delivery_way[0]==1 ? "到店自提" : "快递"}}</div>
                  </template>
                  <template v-else>
                    <div v-if="productData.type == 1" class="value">虚拟发货</div>
                    <div v-else-if="productData.type == 2" class="value">卡密发货</div>
                  </template>
                </li>
              </ul>
              <ul v-if="productData.video_link" class="list">
                <li class="item item100">
                  <div class="item-title">主图视频：</div>
                  <video style="width:300px;height: 150px;border-radius: 10px;" :src="productData.video_link" controls="controls">
                    您的浏览器不支持 video 标签。
                  </video>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="活动与规格" name="goods">
            <div class="section">
              <div class="title mt14">参与活动信息：</div>
              <ul class="list">
                <li class="item">
                  <div class="item-title">活动名称：</div>
                  <div class="value">{{productData.seckillActive&&productData.seckillActive.name || '-'}}</div>
                </li>
                 <li v-if="mer_svip_status" class="item">
                  <div class="item-title">活动状态：</div>
                  <div class="value">{{productData.seckillActive&&productData.seckillActive.status_text || '-'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">规格：</div>
                  <div class="value">{{productData.spec_type == 1 ? "多规格" : "单规格"}}</div>
                </li>
                <li class="item">
                  <div class="item-title">活动日期：</div>
                  <div v-if="productData.seckillActive" class="value mt1-5">{{productData.seckillActive.start_day + ' - ' + productData.seckillActive.end_day}}</div>
                </li>
                 <li class="item">
                  <div class="item-title">审核状态：</div>
                  <div class="value">
                    {{productData.status | seckillProductStatus}}
                    <span v-if="productData.refusal">(未通过原因：{{productData.refusal}})</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">单次限购：</div>
                  <div v-if="productData.seckillActive&&productData.seckillActive.once_pay_count" class="value">{{productData.seckillActive.once_pay_count+'('+productData.unit_name+')'}}</div>
                  <div v-else class="value">不限购</div>
                </li>
                <li class="item">
                  <div class="item-title">活动场次：</div>
                  <div v-if="productData.seckillActive" class="value">
                    <div v-for="(item, i) in productData.seckillActive.seckill_time_text_arr" :key="i">{{ item }}</div>
                  </div>
                  <div v-else class="value">-</div>
                </li>
                <li class="item">
                  <div class="item-title">活动限购：</div>
                  <div v-if="productData.seckillActive&&productData.seckillActive.all_pay_count" class="value">{{productData.seckillActive.all_pay_count+'('+productData.unit_name+')'}}</div>
                  <div v-else class="value">不限购</div>
                </li>
              </ul>
            </div>
            <div class="section" style="margin-top: 50px;">
              <div class="title">规格列表：</div>
              <div class="list mt14">
                <template v-if="productData.spec_type === 0">
                  <el-table :data="OneattrValue" border class="tabNumWidth" size="mini">
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="demo-image__preview">
                          <el-image
                            style="width: 60px; height: 60px"
                            :src="scope.row.image"
                          />
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="120">
                      <template slot-scope="scope">
                        <span class="priceBox" v-text="scope.row[iii]" />
                      </template>
                    </el-table-column>
                  </el-table>
                </template>
                <template v-if="productData.spec_type === 1">
                  <el-table :data="ManyAttrValue" border class="tabNumWidth" :height="ManyAttrValue.length==1 ? '128' : ManyAttrValue.length>2&&ManyAttrValue.length<5 ? '300' : ManyAttrValue.length>=5 ? '400' : '200'" size="mini">
                      <template v-if="manyTabDate">
                        <el-table-column v-for="(item,iii) in manyTabDate" :key="iii" align="center" :label="manyTabTit[iii].title" min-width="100">
                          <template slot-scope="scope">
                            <span class="priceBox" v-text="scope.row[iii]" />
                          </template>
                        </el-table-column>
                      </template>
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="upLoadPicBox">
                          <div class="pictrue tabPic"><img :src="scope.row.image"></div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="100">
                      <template slot-scope="scope">
                        <span class="priceBox">{{ scope.row[iii] }}</span>
                      </template>
                    </el-table-column>
                  </el-table>
                </template>
              </div>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品详情" name="detail">
            <div class="section">
              <div class="contentPic" v-html="productData.content"/>
            </div>
          </el-tab-pane>
          <el-tab-pane label="营销信息" name="marketing">
            <div class="section">
              <ul v-if="!isEdit" class="list">
                <li class="item">
                  <div class="item-title">店铺推荐：</div>
                  <div class="value">{{productData.is_good ? '是' : '否'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">平台推荐：</div>
                  <div v-if="productData.is_benefit||productData.is_new||productData.is_best||productData.is_hot" class="value">
                    <span class="value-item" v-if="productData.is_benefit">促销单品</span>
                    <span class="value-item" v-if="productData.is_new">首发新品</span>
                    <span class="value-item" v-if="productData.is_best">精品推荐</span>
                    <span class="value-item" v-if="productData.is_hot">热门榜单</span>
                  </div>
                  <div v-else class="value-item">无</div>
                </li>
                <li v-if="productData.star" class="item">
                  <div class="item-title">平台推荐星级：</div>
                  <div class="value">
                    <el-rate disabled v-model="productData.star" :colors="colors"></el-rate>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">收藏人数：</div>
                  <div class="value">
                    <span> {{productData.care_count}}人</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">已售数量：</div>
                  <div class="value">
                    <span> {{productData.ficti}} (指手动添加数量)</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">实际销量 ：</div>
                  <div class="value">
                    <span> {{productData.sales-productData.ficti}} (指实际售出数量)</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">平台排序：</div>
                  <div class="value" style="margin-top:2px;">
                    <span> {{productData.rank}} </span>
                  </div>
                </li>
              </ul>
              <el-form
                v-else
                ref="formValidate"
                v-loading="fullscreenLoading"
                class="formValidate"
                size="small"
                :model="formValidate"
                label-width="110px"
                @submit.native.prevent>
                <el-form-item label="商品推荐：">
                  <el-checkbox-group v-model="checkboxGroup" size="small">
                    <el-checkbox v-for="(item, index) in recommend" :key="index" :label="item.value">{{ item.name }}</el-checkbox>
                  </el-checkbox-group>
                </el-form-item>
                <el-form-item label="平台推荐星级：">
                  <el-rate class="rate_star"
                    v-model="formValidate.star"
                    :colors="colors" style="margin-top: 4px;">
                  </el-rate>
                  <span style="margin-top: 4px; font-size: 12px;">备注：5星为最高推荐级别，1星为最低推荐级别，设置后会在商城商品列表、搜索商品列表中体现。</span>
                </el-form-item>
                <el-form-item label="排序：">
                  <el-input-number v-model="formValidate.rank"  label="排序" />
                </el-form-item>
              </el-form>
            </div>
          </el-tab-pane>
          <el-tab-pane label="其它信息" name="others">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">支持退款：</div>
                  <div class="value">{{productData.refund_switch ? '是' : '否'}}</div>
                </li>
               <li v-if="productData.guarantee" class="item item100">
                  <div class="item-title">保障服务：</div>
                  <div class="value" style="width: 250px;">
                    <span>{{productData.guarantee.template_name}}</span>
                    <div v-if="productData.guarantee.templateValue && productData.guarantee.templateValue.length>0" style="display: inline;">
                      【<span v-for="(item,i) in productData.guarantee.templateValue" :key="i" class="value-temp">{{item.value&&item.value.guarantee_name}}</span>】
                    </div>
                  </div>
                </li>
                <li v-if="productData.refusal" class="item">
                  <div class="item-title">审核拒绝原因：</div>
                  <div class="value">{{productData.refusal}}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <ul style="padding: 0;margin-top: 50px;">
                <li class="item item100">
                  <div class="item-title">店铺商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="merParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">平台商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="sysParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>

              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane v-if="productData.merchant" label="店铺信息" name="merchant">
            <div class="section">
              <ul class="list">
                <li class="item">
                  <div class="item-title">店铺名称：</div>
                  <div class="value">{{productData.merchant.mer_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">店铺类别：</div>
                  <div class="value">{{productData.merchant.is_trader == 1 ? '自营' : '非自营'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">店铺类型：</div>
                  <div class="value">{{productData.merchant.type_name}}</div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
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
import { seckillDetailApi, seckillProductUpdateApi } from "@/api/marketing";
import SettingMer from '@/libs/settingMer'
const proOptions = [{ name: '是否热卖', value: 'is_hot' }, { name: '促销单品', value: 'is_benefit' }, { name: '是否精品', value: 'is_best' }, { name: '是否新品', value: 'is_new' }]
const defaultObj = {
  image: '',
  slider_image: [],
  store_name: '',
  store_info: '',
  keyword: '',
  brand_id: '', // 品牌id
  cate_id: '', // 平台分类id
  mer_cate_id: [], // 店铺分类id
  unit_name: '',
  sort: 0,
  is_show: 0,
  is_benefit: 0,
  is_new: 0,
  is_good: 0,
  temp_id: '',
  params: [],
  attrValue: [{
    image: '',
    price: null,
    // svip_price: null,
    cost: null,
    ot_price: null,
    stock: null,
    old_stock: null,
    bar_code: null,
    bar_code_number: null,
    weight: null,
    volume: null
  }],
  attr: [],
  selectRule: '',
  extension_type: 0,
  content: '',
  spec_type: 0
}
const objTitle = {
  // svip_price: {
  //   title: '付费会员价'
  // },
  cost: {
    title: '成本价'
  },
  ot_price: {
    title: '售价'
  },
  old_stock: {
    title: '库存'
  },
  price: {
    title: '秒杀价'
  },
  stock: {
    title: '限量'
  },
  bar_code: {
    title: '规格编码'
  },
  bar_code_number: {
    title: '条形码'
  },
  weight: {
    title: '重量（KG）'
  },
  volume: {
    title: '体积(m³)'
  }
}
export default {
  components: {},
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      loading: true,
      fullscreenLoading: false,
      productId: '',
      direction: 'rtl',
      activeName: 'basic',
      isEdit: false,
      isAudit: false,
      recommend: proOptions,
      checkboxGroup: [],
      productData: {},
      formThead: Object.assign({}, objTitle),
      manyTabDate: {},
      manyTabTit: {},
      OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])], // 多规格
      svip_type: 0,
      mer_svip_status: 0,
      orderImg: require('@/assets/images/product_icon.png'),
      merParams: [],
      sysParams: [],
      formUrl: "",
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      // baseURL: 'http://localhost:8080',
      formData: [],
      timeVal: [],
      recordData: {
        data: [],
        total: 0
      },//商品操作记录
      recordForm: {
        type: '',
        date: "",
        page: 1,
        limit: 10
      },
      colors: ['#99A9BF', '#F7BA2A', '#FF9900'],
      formValidate: {}
    };
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, defaultObj.attrValue[0])
      // if(this.svip_type == 0 || this.mer_svip_status == 0)delete obj.svip_price
      delete obj.image
      return obj
    },
  },
  filters: {
  },
  methods: {
    handleEdit(){
      this.isEdit = true;
      this.activeName = "marketing";
    },
    cancelEdit() {
      this.isEdit = false;
    },
    saveInfo(){
      this.formValidate.is_hot = this.checkboxGroup.indexOf('is_hot') == -1 ? 0 : 1
      this.formValidate.is_benefit = this.checkboxGroup.indexOf('is_benefit') == -1 ? 0 : 1
      this.formValidate.is_best = this.checkboxGroup.indexOf('is_best') == -1 ? 0 : 1
      this.formValidate.is_new = this.checkboxGroup.indexOf('is_new') == -1 ? 0 : 1
      seckillProductUpdateApi(this.productId, this.formValidate)
        .then(async(res) => {
          this.fullscreenLoading = false
          this.$message.success(res.message)
          this.getInfo(this.productId,false)
          this.$emit('getList','')
          this.isEdit = false
        })
        .catch((res) => {
          this.fullscreenLoading = false
          this.$message.error(res.message)
        })
    },
    handleClose() {
      this.activeName = 'basic';
      this.$emit('closeDrawer');
    },
    //下拉审核
    handleCommand() {
      this.$emit('handleAudit',this.productId);
    },
    getInfo(id,isEdit) {
      this.isEdit = isEdit;
      this.activeName = isEdit ? "marketing" : "basic";
      this.loading = true;
      this.productId = id;
      seckillDetailApi(id).then(res => {
        this.loading = false;
        this.productData = res.data
        this.mer_svip_status = res.data.mer_svip_status
        this.svip_type = res.data.svip_price_type
        if (this.productData.spec_type === 0) {
          this.OneattrValue = res.data.attrValue
        } else {
          this.ManyAttrValue = res.data.attrValue
        }
        const tmp = {}
        const tmpTab = {}
        this.productData.attr.forEach((o, i) => {
          tmp['value' + i] = { title: o.value }
          tmpTab['value' + i] = ''
        })
        this.manyTabDate = tmpTab
        this.manyTabTit = tmp
        this.checkboxGroup = []
        this.formThead = Object.assign({}, this.formThead, tmp)
        if (this.productData.is_hot === 1) this.checkboxGroup.push('is_hot')
        if (this.productData.is_benefit === 1) this.checkboxGroup.push('is_benefit')
        if (this.productData.is_best === 1) this.checkboxGroup.push('is_best')
        if (this.productData.is_new === 1) this.checkboxGroup.push('is_new')
        this.formValidate = {
          star: res.data.star,
          rank: res.data.rank,
        }
        this.sysParams = []
        this.merParams = []
        if(res.data.params&&res.data.params.length>0){
          for(var i=0;i<res.data.params.length;i++){
            if(res.data.params[i]['mer_id'] == 0){
              this.sysParams.push(res.data.params[i])
            }else{
              this.merParams.push(res.data.params[i])
            }
          }
        }
        this.loading = false
      }).catch(res => {
        this.$message.error(res.message)
        this.loading = false
      })
    },
    tabClick(tab) {

    },
  },
};
</script>
<style lang="scss" scoped>
.head {
  padding: 20px 35px;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    .iconfont {
      color: var(--prev-color-primary);
      &.sale-after {
        color: #90add5;
      }
    }
    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;
      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        font-weight: bold;
        color: #282828;
      }
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 20%;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
    }
  }
}
.tabNumWidth{
  overflow-y: auto;
  &:before{
    display: none;
  }
}
::v-deep .el-table .cell{
  text-overflow: auto;
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  .title {
    font-size: 14px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    // align-items: center;
    &:nth-child(3n + 1) {
      padding-right: 20px;
    }
    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }
    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
    .item-title{
      width: 100px;
      text-align: right;
    }
  }
  .item100{
    padding-left: 0;
    flex: 0 0 calc(100% / 1);
    padding-left: 0!important;
  }
  .contentPic{
    width: 500px;
    margin: 0 auto;
    max-height: 600px;
    overflow-y: auto;
  }

  .value {
    .value-item {
      &::after{
        content: "/";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    .value-temp{
       &::after{
        content: "、";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.contentPic ::v-deep img{
  max-width: 100%;
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
::v-deep .ones th{
  background: #F0F5FF;
}

</style>

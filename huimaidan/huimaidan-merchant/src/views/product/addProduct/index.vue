<template>
  <div class="divBox">
    <el-card class="box-card">
      <div class="clearfix">
        <el-tabs v-if="headTab.length > 0" v-model="currentTab">
          <el-tab-pane
            v-for="(item, index) in headTab"
            :key="index"
            :name="item.name"
            :label="item.title"
          />
        </el-tabs>
      </div>

      <el-form
        ref="formValidate"
        :key="currentTab"
        v-loading="fullscreenLoading"
        class="formValidate mt20"
        :rules="ruleValidate"
        :model="formValidate"
        label-width="130px"
        @submit.native.prevent
      >
        <!-- 商品信息 -->
        <productInfo
          v-if="currentTab === '1'"
          :formValidate="formValidate"
          :OneattrValue="OneattrValue"
          :attrValue="attrValue"
          :is_timed="is_timed"
          :timeVal="timeVal"
          :videoLink="videoLink"
          :timeVal2="timeVal2"
          :props="props"
          @changeTimed="switchTimed"
          @productCon="productCon"
          @getSpecsLst="getSpecsLst"
          @getSpecsList="getSpecsList"
          @generateHeader="generateHeader"
        />
        <!--  规格设置  -->
        <productSpecs
          v-if="currentTab === '2' && formValidate.type !== 4"
          :formValidate="formValidate"
          :attrs="attrs"
          :oneFormBatch="oneFormBatch"
          :OneattrValue="OneattrValue"
          :ManyAttrValue="ManyAttrValue"
          :changeAttrValue="changeAttrValue"
          :attrValue="attrValue"
          :formThead="formThead"
          :formDynamic="formDynamic"
          :product_id="String(product_id)"
          :cdkeyLibraryList="cdkeyLibraryList"
          @handleBlur="handleBlur"
          @handleFocus="handleFocus"
          @generateAttr="generateAttr"
          @handleAddRole="handleAddRole"
          @handleRemoveRole="handleRemoveRole"
          @attrChangeValue="attrChangeValue"
          @batchDel="batchDel"
          @getSelectedLiarbry="getSelectedLiarbry"
          @delAttrTable="delAttrTable"
          @delManyImg="delManyImg"
          @attrDetailChangeValue="attrDetailChangeValue"
          @setAttrs="setAttrs"
          @addVirtual="addVirtual"
          @seeVirtual="seeVirtual"
        />

        <!-- 预约商品的规格设置 -->
        <reservationSpecs
          v-if="currentTab === '2' && formValidate.type === 4"
          ref="reservationSpecs"
          :formValidate="formValidate"
          :attrs="attrs"
          :OneattrValue="OneattrValue"
          :ManyAttrValue="ManyAttrValue"
          :changeAttrValue="changeAttrValue"
          :attrValue="attrValue"
          :formThead="formThead"
          :oneFormBatch="oneFormBatch"
          :formDynamic="formDynamic"
          :product_id="product_id"
          :cdkeyLibraryList="cdkeyLibraryList"
          @change="handleSpecChange"
          @generateAttr="generateAttr"
          @handleAddRole="handleAddRole"
          @handleRemoveRole="handleRemoveRole"
          @delAttrTable="delAttrTable"
          @delManyImg="delManyImg"
          @attrChangeValue="attrChangeValue"
          @batchDel="batchDel"
          @attrDetailChangeValue="attrDetailChangeValue"
          @getSelectedLiarbry="getSelectedLiarbry"
          @setAttrs="setAttrs"
          @handleBlur="handleBlur"
          @handleFocus="handleFocus"
        >
        </reservationSpecs>

        <!-- 预约设置 -->
        <reservationSetting
          v-if="currentTab === '7'"
          ref="reservationSetting"
          :roterPre="roterPre"
          :formValidate="formValidate"
          :formList="formList"
          :formUrl="formUrl"
          @getFormList="getFormList"
          @getFormInfo="getFormInfo"
        >
        </reservationSetting>

        <!-- 商品详情 -->
        <productDetail
          v-if="currentTab === '3'"
          :formValidate="formValidate"
          @getEditorContent="getEditorContent"
        />

        <!-- 营销设置 -->
        <productMarket
          v-if="currentTab === '4'"
          :formValidate="formValidate"
          :good-list="goodList"
          :OneattrValue="OneattrValue"
          :open_svip="open_svip"
          :manyTabDate="manyTabDate"
          :deduction_set="deduction_set"
          :extensionStatus="extensionStatus"
          :deductionStatus="deductionStatus"
          :deduction_ratio_rate="deduction_ratio_rate"
          :extension_one_rate="extension_one_rate"
          :extension_two_rate="extension_two_rate"
          :manyTabTit="manyTabTit"
          :ManyAttrValue="ManyAttrValue"
          :svip_rate="svip_rate"
          :base-url="baseURL"
          :specValue="specValue"
          :formThead="formThead"
          @openRecommend="openRecommend"
          @updateDeduction="updateDeduction"
        />
        <!-- 参数设置-->
        <product-param
          v-if="currentTab === '5'"
          :formValidate="formValidate"
          :paramList = "formValidate.params"
          :customSpecs="customSpecs"
          :sysSpecsSelect="sysSpecsSelect"
          :merSpecsSelect="merSpecsSelect"
          @getSpecsList="getSpecsList"
        />

        <!-- 其他设置 -->
        <productOther
          v-if="currentTab === '6' && formValidate.type !== 4"
          :formValidate="formValidate"
          :deliveryList="deliveryList"
          :shippingList="shippingList"
          :guaranteeList="guaranteeList"
          :formList="formList"
          :formUrl="formUrl"
          :roterPre="roterPre"
          @addTem="addTem"
          @getFormList="getFormList"
          @getFormInfo="getFormInfo"
          @addServiceTem="addServiceTem"
        />

        <!-- 预约商品的其他设置 -->
        <reservationOther
          v-if="currentTab === '6' && formValidate.type === 4"
          ref="reservationOther"
          :formValidate="formValidate"
          :deliveryList="deliveryList"
          :shippingList="shippingList"
          :guaranteeList="guaranteeList"
          :formList="formList"
          :formUrl="formUrl"
          :roterPre="roterPre"
          @addTem="addTem"
          @getFormList="getFormList"
          @getFormInfo="getFormInfo"
          @addServiceTem="addServiceTem"
        ></reservationOther>
      </el-form>
    </el-card>

    <div class="footer" :style="containerStyle">
      <el-button
        v-show="currentTab > 1"
        class="submission"
        size="small"
        @click="handleSubmitUp"
        >上一步
      </el-button>
      <el-button
        v-show="currentTab != 6"
        class="submission"
        size="small"
        @click="handleSubmitNest('formValidate')"
        >下一步
      </el-button>
      <el-button
        v-show="currentTab == '6' || $route.query.id"
        :loading="loading"
        type="primary"
        class="submission"
        size="small"
        @click="handleSubmit('formValidate')"
        >提交
      </el-button>
      <el-button
        :loading="loading"
        class="submission"
        size="small"
        @click="handlePreview('formValidate')"
        >预览
      </el-button>
    </div>

    <!--属性选择弹窗-->
    <el-dialog
      v-if="attrShow"
      :visible.sync="attrShow"
      title="请选择商品规格"
      width="320px"
    >
      <attr-list
        :attrs="attrsList"
        @activeData="activeAttr"
        @close="labelAttr"
        @subAttrs="subAttrs"
        v-if="attrShow"
      ></attr-list>
    </el-dialog>
    <!--添加服务保障模板-->
    <guarantee-service ref="serviceGuarantee" @get-list="getGuaranteeList" />
    <!--预览商品-->
    <div v-if="previewVisible">
      <div class="bg" @click.stop="previewVisible = false" />
      <preview-box
        v-if="previewVisible"
        ref="previewBox"
        :preview-key="previewKey"
      />
    </div>
    <!-- 生成淘宝京东表单-->
    <tao-bao ref="taoBao" @info-data="infoData($event, 'taobao')" />
    <!--添加链接-->
    <add-carMy
      ref="addCarMy"
      :virtualList="virtualList"
      @changeVirtual="changeVirtual"
      @fixdBtn="fixdBtn"
      @closeCarMy="closeCarMy"
    ></add-carMy>
    <!--添加卡密-->
    <cdkey-library
      ref="cdkeyLibrary"
      :cdkeyLibraryInfo="cdkeyLibraryInfo"
      :selectedLibrary="selectedLibrary"
      @handlerSubSuccess="handlerChangeCdkeyIdSubSuccess"
    ></cdkey-library>
    <!--选择店铺推荐商品-->
    <el-dialog
      :visible.sync="recommendVisible"
      title="推荐商品列表"
      width="900px"
    >
      <goods-list
        ref="goodslist"
        v-if="recommendVisible"
        :ischeckbox="true"
        :isGood="true"
        :product_id="product_id"
        :selectedArr="goodList"
        @getProductId="getRecommend"
        @close="closeRecommend"
      ></goods-list>
    </el-dialog>
    <templatesFrom
      ref="templateForm"
      @getList="getShippingList"
    ></templatesFrom>
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
import vuedraggable from "vuedraggable";
import Sortable from "sortablejs";
import productInfo from "./components/productInfo.vue";
import productSpecs from "./components/productSpecs.vue";
import productDetail from "./components/productDetail.vue";
import productOther from "./components/productOther.vue";
import productMarket from "./components/productMarket.vue";
import reservationSetting from "./components/reservationSetting.vue";
import reservationSpecs from "./components/reservationSpecs.vue";
import reservationOther from "./components/reservationOther.vue";
import templatesFrom from "@/components/templatesFrom";
import { mateName, arraysEqual } from "@/utils";
import {
  GoodsTableHead,
  VirtualTableHead,
  VirtualTableHead2,
  reservationTableHeard
} from "./TableHeadList.js";
import {
  shippingListApi,
  templateLsitApi,
  productCreateApi,
  productLstDetail,
  productUpdateApi,
  productConfigApi,
  productGetTempKeysApi,
  guaranteeListApi,
  productPreviewApi,
  specsSelectedApi,
  productSpecsDetailApi,
  associatedFormList,
  productUnrelatedListApi,
  unitCreatApi,
  productReservationCreateApi,
  productReservationEditApi,
  productReservationInfoApi,
  attrCreatApi
} from "@/api/product";
import { roterPre } from "@/settings";
import guaranteeService from "@/components/serviceGuarantee/index";
import previewBox from "@/components/previewBox/index";
import productParam from "./components/productParam.vue";
import attrList from "@/components/attrList";
import goodsList from "@/components/goodsList";
import SettingMer from "@/libs/settingMer";
import { getToken } from "@/utils/auth";
import taoBao from "./taoBao";
import copyRecord from "./copyRecord";
import addCarMy from "./addCarMy";
import cdkeyLibrary from "./cdkeyLibrary";
import container from "@/mixins/container";

const defaultObj = {
  image: "",
  slider_image: [],
  customize_time_period: [""],
  store_name: "",
  store_info: "",
  keyword: "",
  brand_id: "", // 品牌id
  cate_id: "", // 平台分类id
  mer_cate_id: [], // 商户分类id
  param_temp_id: "",
  unit_name: "",
  sort: 0,
  once_max_count: 0,
  is_good: 0,
  is_show: 1,
  auto_on_time: "",
  auto_off_time: "",
  temp_id: "",
  video_link: "",
  guarantee_template_id: "",
  delivery_way: [],
  mer_labels: [],
  delivery_free: 0,
  pay_limit: 0,
  once_min_count: 0,
  svip_price_type: 0,
  refund_switch: 1,
  params: [],
  custom_temp_id: [],
  header: [],
  attrValue: [
    {
      image: "",
      price: null,
      cost: null,
      ot_price: null,
      settlement_price: null,
      svip_price: null,
      select: false,
      stock: null,
      cdkey: {},
      library_name: "",
      library_id: "",
      bar_code: "",
      bar_code_number: "",
      weight: null,
      volume: null,
      reservation: []
    }
  ],
  specValue: [
    {
      price: null,
      ot_price: null
    }
  ],
  attr: [],
  extension_type: 0,
  integral_rate: -1,
  content: "",
  spec_type: 0,
  give_coupon_ids: [],
  is_gift_bag: 0,
  couponData: [],
  activity_label_ids: [],
  extend: [], // 自定义留言
  type: 0,
  product_type: 0,
  is_show: 1,

  // 预约设置数据
  time_period: [],
  reservation_time_interval: 60,
  reservation_times: [],
  reservation_time_type: 1,
  reservation_type: 3,
  reservation_start_time: "",
  reservation_end_time: "",
  show_num_type: 1,
  sale_time_type: 1,
  sale_time_start_day: "",
  sale_time_end_day: "",
  sale_time_week: [1, 2, 3, 4, 5, 6, 7],
  show_reservation_days: 10,
  is_advance: 0,
  advance_time: 0,
  is_cancel_reservation: 0,
  cancel_reservation_time: 0,
  reservation_form_type: 1,
  mer_form_id: ""
};
const objTitle = {
  price: {
    title: "售价"
  },
  cost: {
    title: "成本价"
  },
  ot_price: {
    title: "划线价"
  },
  svip_price: {
    title: "付费会员价"
  },
  stock: {
    title: "库存"
  },
  bar_code: {
    title: "规格编码"
  },
  bar_code_number: {
    title: "条形码"
  },
  weight: {
    title: "重量（KG）"
  },
  volume: {
    title: "体积(m³)"
  }
};
// 定义验证规则数组，每个规则包含字段名、提示信息和验证函数
const validationRules = [
  {
    field: "store_name",
    message: "基本信息-商品名称不能为空",
    validator: value => !value.trim()
  },
  // {
  //   field: 'unit_name',
  //   message: '基本信息-单位不能为空',
  //   validator: value => !value
  // },
  {
    field: "cate_id",
    message: "基本信息-平台商品分类不能为空",
    validator: value => !value
  },
  {
    field: "image",
    message: "基本信息-商品封面图不能为空",
    validator: value => !value
  },
  {
    field: "slider_image",
    message: "基本信息-商品轮播图不能为空",
    validator: value => value.length === 0
  }
];
export default {
  name: "ProductProductAdd",
  components: {
    WangEditor,
    guaranteeService,
    previewBox,
    attrList,
    goodsList,
    taoBao,
    copyRecord,
    addCarMy,
    cdkeyLibrary,
    draggable: vuedraggable,
    templatesFrom,
    productInfo,
    productSpecs,
    productDetail,
    productMarket,
    productParam,
    productOther,
    reservationSetting,
    reservationOther,
    reservationSpecs
  },
  mixins: [container],
  data() {
    const url =
      SettingMer.https + "/upload/image/0/file?ueditor=1&token=" + getToken();
    return {
      roterPre: roterPre,
      baseURL: SettingMer.httpUrl || "http://localhost:8080",
      formUrl: "",
      tabs: [],
      fullscreenLoading: false,
      props: { emitPath: false },
      active: 0,
      deduction_set: -1,
      // OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      // ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])], // 多规格
      OneattrValue: [JSON.parse(JSON.stringify(defaultObj.attrValue[0]))], // 单规格
      ManyAttrValue: [JSON.parse(JSON.stringify(defaultObj.attrValue[0]))], // 多规格
      ruleList: [],
      createBnt: true,
      showIput: false,
      merCateList: [], // 商户分类筛选
      categoryList: [], // 平台分类筛选
      shippingList: [], // 运费模板
      guaranteeList: [], // 服务保障模板
      BrandList: [], // 品牌
      deliveryList: [],
      labelList: [], // 商品标签
      formData: [], // 表单数据
      formThead: Object.assign({}, objTitle),
      // formValidate: Object.assign({}, defaultObj),
      formValidate: JSON.parse(JSON.stringify(defaultObj)),
      picValidate: true,
      formDynamics: {
        template_name: "",
        template_value: []
      },
      manyTabTit: {},
      manyTabDate: {},

      // 规格数据
      formDynamic: {
        attrsName: "",
        attrsVal: ""
      },
      isBtn: false,
      images: [],
      currentTab: "1",
      isChoice: "",
      upload: {
        videoIng: false // 是否显示进度条；
      },
      progress: 10, // 进度条默认0
      videoLink: "",

      loading: false,
      ruleValidate: {
        give_coupon_ids: [
          {
            required: true,
            message: "请选择优惠券",
            trigger: "change",
            type: "array"
          }
        ],
        store_name: [
          { required: true, message: "请输入商品名称", trigger: "blur" }
        ],
        cate_id: [
          { required: true, message: "请选择平台分类", trigger: "change" }
        ],
        keyword: [
          { required: true, message: "请输入商品关键字", trigger: "blur" }
        ],
        unit_name: [{ required: true, message: "请输入单位", trigger: "blur" }],
        store_info: [
          { required: true, message: "请输入商品简介", trigger: "blur" }
        ],
        temp_id: [
          { required: true, message: "请选择运费模板", trigger: "change" }
        ],
        once_max_count: [
          { required: true, message: "请输入限购数量", trigger: "change" }
        ],
        image: [{ required: true, message: "请上传商品图", trigger: "change" }],
        slider_image: [
          {
            required: true,
            message: "请上传商品轮播图",
            type: "array",
            trigger: "change"
          }
        ],
        spec_type: [
          { required: true, message: "请选择商品规格", trigger: "change" }
        ],
        delivery_way: [
          { required: true, message: "请选择送货方式", trigger: "change" }
        ]
      },
      attrInfo: {},
      keyNum: 0,
      extensionStatus: 0,
      deductionStatus: 0,
      previewVisible: false,
      previewKey: "",
      deliveryType: [],

      customBtn: 0, // 自定义留言开关
      // 自定义留言下拉选择
      CustomList: [
        {
          value: "text",
          label: "文本框"
        },
        {
          value: "number",
          label: "数字"
        },
        {
          value: "email",
          label: "邮件"
        },
        {
          value: "date",
          label: "日期"
        },
        {
          value: "time",
          label: "时间"
        },
        {
          value: "idCard",
          label: "身份证"
        },
        {
          value: "mobile",
          label: "手机号"
        },
        {
          value: "image",
          label: "图片"
        }
      ],
      customess: {
        content: []
      }, // 自定义留言内容

      headTab: [],
      type: 0,
      modals: false,
      attrVal: {
        price: null,
        // cost: null,
        ot_price: null,
        stock: null,
        bar_code: null,
        bar_code_number: null,
        weight: null,
        volume: null
      },
      specVal: {
        price: null,
        ot_price: null
      },
      open_svip: false,
      svip_rate: 0,
      extension_one_rate: "",
      extension_two_rate: "",
      deduction_ratio_rate: "",
      customSpecs: [],
      merSpecsSelect: [],
      sysSpecsSelect: [],
      attrs: [],
      attrsList: [],
      activeAtter: [],
      attrShow: false,
      createProduct: false,
      generateArr: [],
      createCount: this.$route.params.id ? 0 : -10,
      virtualList: [],
      formList: [],
      carMyShow: false, //是否开启卡密弹窗
      tabIndex: 0,
      tabName: "",
      oneFormBatch: [
        {
          image: "",
          price: "",
          cost: "",
          ot_price: "",
          svip_price: "",
          stock: "",
          cdkey: {},
          code: "",
          weight: "",
          volume: ""
        }
      ],
      headerCarMy: {
        title: "卡密设置",
        slot: "fictitious",
        align: "center",
        width: 95
      },
      product_id: "",
      goodList: [],
      unitList: [],
      recommendVisible: false,
      timeVal: "",
      timeVal2: "",
      is_timed: 0,
      cdkeyId: null, //卡密库id
      cdkeyLibraryInfo: null, //卡密库对象
      selectedLibrary: [], //已选择的卡密库
      cdkeyLibraryList: [], //可选的卡密库
      columnsInstalM: [],
      canSel: true, // 规格图片添加判断
      changeAttrValue: "", //修改的规格值
      tableKey: 0,
      rakeBack: [
        {
          title: "一级返佣(元)",
          slot: "extension_one",
          align: "center",
          width: 95
        },
        {
          title: "二级返佣(元)",
          slot: "extension_two",
          align: "center",
          width: 95
        }
      ],
      manyVipPrice: "",
      manyBrokerage: "",
      manyBrokerageTwo: ""
    };
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, this.attrVal);
      return obj;
    },
    specValue() {
      const obj = Object.assign({}, this.specVal);
      return obj;
    }
  },
  watch: {
    "formValidate.attr": {
      handler: function(val) {
        if (this.formValidate.spec_type === 1) this.watCh(val);
      },
      immediate: false,
      deep: true
    },
    "$route.query.id": {
      handler: function(nVal, oVal) {
        if (nVal !== oVal && nVal) {
          this.initData();
        }
      },
      immediate: false,
      deep: true
    },
    "$route.query.productType": {
      handler: function(nVal, oVal) {
        if (nVal !== oVal && nVal) {
          this.getHeaderTab();
        }
      },
      immediate: false,
      deep: true
    }
  },
  created() {
    this.tempRoute = Object.assign({}, this.$route);
    if (this.$route.query.id && this.formValidate.spec_type === 1) {
      this.$watch("formValidate.attr", this.watCh);
    }
  },
  mounted() {
    if (this.$route.query.productType) {
      this.formValidate.type = Number(this.$route.query.productType);
      // 若选中的商品类型为卡密商品（ID 为 3），调用获取卡密库列表的方法
      if (this.formValidate.type === 3) {
        this.getCdkeyLibraryList();
      }

      // 获取商品配置信息
      this.productCon();

      // 根据商品类型显示规格信息
      this.showSpecsByType();

      // 修正拼写错误，将 arrs 改为 attrs
      this.generateHeader(this.attrs);

      // 触发 generateHeader 事件并传递规格数据
      this.$emit("generateHeader", this.attrs);
    }

    this.initData();
    this.getHeaderTab();
  },

  destroyed() {
    window.removeEventListener("popstate", this.goBack, false);
  },
  methods: {
    handleSpecChange({ isSingle, data }) {
      if (isSingle) {
        this.OneattrValue = data;
      } else {
        this.ManyAttrValue = data;
      }
    },
    /**
     * @description: 商品信息tab=1的相关方法
     */

    // 获取商品配置信息
    productCon() {
      productConfigApi()
        .then(res => {
          this.extensionStatus = Number(res.data.extension_status);
          this.deductionStatus = res.data.integral_status;
          this.deliveryType = res.data.delivery_way;
          this.open_svip = res.data.mer_svip_status == 1 && res.data.svip_switch_status == 1;
          this.svip_rate = Number(res.data.svip_store_rate);
          this.extension_one_rate = res.data.extension_one_rate + "";
          this.extension_two_rate = res.data.extension_two_rate + "";
          this.deduction_ratio_rate = res.data.integral_rate;
          const deliveryAll = {
              1: '到店自提',
              2: this.formValidate.type == 0 ? "快递配送" : this.formValidate.type == 1 ? "虚拟发货"
              : "卡密发货",
              3: '同城配送'
          };
          this.deliveryList = []
          this.deliveryType.forEach(item => {
            // 判断是否为卡密发货或虚拟发货
            let delivery = deliveryAll[item]
            if (this.formValidate.type != 0 && item == 2) {
              this.deliveryList.push({ value: item, name: delivery })
            } else if (this.formValidate.type == 0) {
              this.deliveryList.push({ value: item, name: delivery })
            }
          })
          if (!this.$route.params.id) {
             this.formValidate.delivery_way = this.deliveryType;
          }
        } )
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 动态tab头部数据
    getHeaderTab() {
      this.headTab = [
        { title: "商品信息", name: "1" },
        { title: "规格设置", name: "2" },
        { title: "商品详情", name: "3" },
        ...(this.formValidate.type === 4
          ? [{ title: "预约设置", name: "7" }]
          : []),
        { title: "营销设置", name: "4" },
        { title: "商品参数", name: "5" },
        { title: "其他设置", name: "6" }
      ];
    },

    // 根据商品类型判断是否显示重量体积
    showSpecsByType() {
      if (this.formValidate.type == 2 || this.formValidate.type == 3) {
        delete this.attrValue.weight;
        delete this.attrValue.volume;
      } else {
        this.attrValue.weight = "";
        this.attrValue.volume = "";
      }
    },

    // 根据商品平台分类获取参数模板
    getSpecsLst(info, isData) {
      let cate_id = info ? info.cate_id : this.formValidate.cate_id;
      specsSelectedApi({ cate_id: cate_id })
        .then(res => {
          this.merSpecsSelect = res.data.mer || [];
          this.sysSpecsSelect = res.data.sys || [];
          if (this.$route.query.type == 1 && isData) {
            this.infoData(info, "taobao");
          }
          if (this.$route.query.type != 1 && isData) {
            this.infoData(info);
          }
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 获取参数模板详情数据
    getSpecsList() {
      let merParams = [...this.formValidate.custom_temp_id],
        sysParams = [...[this.formValidate.param_temp_id]];
      let params = [...merParams, ...sysParams];
      if (params.length <= 0) {
        this.formValidate.merParams = [];
        this.formValidate.sysParams = [];
      } else {
        productSpecsDetailApi({
          template_ids: params.toString()
        })
          .then(res => {
            let arr = [];
            this.formValidate.params.forEach((item, i) => {
              if (!item.parameter_id) arr.push(item);
            });
            this.formValidate.params = [...arr, ...res.data];
            // 更新组件
            this.$forceUpdate()
          })
          .catch(res => {
            this.$message.error(res.message);
          });
      }
    },

    // 根据不同商品类型动态生成商品规格表头
    generateHeader(data) {
      let array = [];
      data.forEach(item => {
        if (item.detail.length === 0) {
          // return this.$message.error(`请添加${item.value}的规格值`);
        } else {
          array.push({
            title: item.value,
            key: item.value,
            minWidth: 140,
            fixed: "left"
          });
        }
      });
      let specificationsColumns = array;
      let arr;
      if (this.formValidate.type == 2) {
        arr = [...specificationsColumns, ...VirtualTableHead];
        this.$set(this.formValidate, "header", arr);
        // 找到slot 等于 fictitious 将title改为规格名称
        this.formValidate.header.map(item => {
          if (item.slot === "fictitious") {
            item.title = "云盘设置";
          }
        });
      } else if (this.formValidate.type == 3) {
        //卡密商品
        arr = [...specificationsColumns, ...VirtualTableHead2];
      } else if (this.formValidate.type == 4) {
        arr = [...specificationsColumns, ...reservationTableHeard];
      } else {
        arr = [...specificationsColumns, ...GoodsTableHead];
      }
      this.$set(this.formValidate, "header", arr);
      this.tableKey += 1;
      this.columnsInstalM = arr;
    },

    /**
     * @description: 商品规格tab==2的相关方法
     */

    //  添加新规格
    handleAddRole() {
      let data = {
        value: this.formDynamic.attrsName,
        add_pic: 0,
        detail: []
      };
      this.attrs.push(data);
    },

    // 子传父修改数据
    setAttrs(val) {
      this.attrs = val;
      this.generateAttr(this.attrs);
    },

    // 规格名称改变
    attrChangeValue(i, val) {
      this.generateHeader(this.attrs);
      this.generateAttr(this.attrs);
    },

    // 删除规格
    handleRemoveRole(index) {
      this.attrs.splice(index, 1);
      if (!this.attrs.length) {
        this.formValidate.header = [];
        this.ManyAttrValue = [];
      } else {
        this.generateAttr(this.attrs);
      }
    },

    // 删除表格中 对应属性
    delAttrTable(val) {
      for (let i = 0; i < this.ManyAttrValue.length; i++) {
        let item = this.ManyAttrValue[i];
        if (item.attr_arr && item.attr_arr.includes(val)) {
          this.ManyAttrValue.splice(i, 1);
          i--;
        }
      }
    },

    // 删除规格图片
    delManyImg(val, index, indexn) {
      const newAttrs = [...this.attrs];
      newAttrs[index].detail = [...newAttrs[index].detail];
      newAttrs[index].detail[indexn] = {
        ...newAttrs[index].detail[indexn],
        pic: ""
      };

      this.attrs = newAttrs;
      this.ManyAttrValue.forEach(item => {
        if (item.attr_arr && item.attr_arr.includes(val.value)) {
          item.image = "";
        }
      });
    },

    //添加云盘链接
    addVirtual(type, index, name) {
      this.tabIndex = index;
      this.tabName = name;
      if (type == 0) {
        this.$refs.addCarMy.carMyShow = true;
        this.virtualListClear();
        this.$refs.addCarMy.fixedCar = {
          is_type: 0,
          key: "",
          stock: 0
        };
      } else {
        this.getSelectedLiarbry();
        this.cdkeyLibraryInfo = {};
        this.$refs.cdkeyLibrary.cdkeyShow = true;
      }
    },

    // 查看云盘链接
    seeVirtual(type, data, name, index) {
      this.tabName = name;
      this.tabIndex = index;
      if (type == 0) {
        this.virtualListClear();
        this.$refs.addCarMy.fixedCar = {
          is_type: 0,
          key: "",
          stock: 0
        };
        if (
          data.cdkey &&
          data.cdkey.list &&
          data.cdkey.list.length &&
          data.cdkey.is_type == 1
        ) {
          this.$refs.addCarMy.fixedCar.is_type = 1;
          this.virtualList = data.cdkey.list;
        } else if (data.cdkey && data.cdkey.key) {
          this.$refs.addCarMy.fixedCar.is_type = 0;
          this.$refs.addCarMy.fixedCar.key = data.cdkey.key;
          this.$refs.addCarMy.fixedCar.stock = data.stock;
        }
        this.$refs.addCarMy.carMyShow = true;
      } else {
        this.cdkeyLibraryInfo = {
          id: data.library_id,
          name: data.library_name
        };

        this.getSelectedLiarbry(data);
        this.$refs.cdkeyLibrary.cdkeyShow = true;
      }
    },

    //提交云盘链接
    fixdBtn(e) {
      if (!this[this.tabName][this.tabIndex]["cdkey"]) {
        this.$set(this[this.tabName][this.tabIndex], "cdkey", {});
      }
      if (e.is_type == 0) {
        this.$set(this[this.tabName][this.tabIndex]["cdkey"], "key", e.key);
        this.$set(this[this.tabName][this.tabIndex], "stock", Number(e.stock));
        this[this.tabName][this.tabIndex]["cdkey"].list = [];
      } else {
        this.$set(this[this.tabName][this.tabIndex]["cdkey"], "list", e.list);
        this.$set(this[this.tabName][this.tabIndex], "stock", e.list.length);
        this[this.tabName][this.tabIndex]["cdkey"].key = "";
      }
      this.$set(
        this[this.tabName][this.tabIndex]["cdkey"],
        "is_type",
        e.is_type
      );
      this.$refs.addCarMy.carMyShow = false;
    },

    // 关闭云盘弹窗
    closeCarMy() {
      this.$refs.addCarMy.carMyShow = false;
    },

    // 清除属性
    batchDel() {
      this.oneFormBatch = [
        {
          image: "",
          price: "",
          cost: "",
          ot_price: "",
          stock: "",
          bar_code: "",
          weight: "",
          volume: "",
          virtual_list: []
        }
      ];
    },

    //卡密列表
    getCdkeyLibraryList() {
      productUnrelatedListApi().then(res => {
        this.cdkeyLibraryList = res.data.data;
      });
    },

    //添加倒入卡密的值
    changeVirtual(e) {
      this.virtualList = this.virtualList.concat(e);
    },

    // 取出来已选择的卡密库
    getSelectedLiarbry(data, array) {
      this.selectedLibrary = [];
      array.forEach((item, index) => {
        if (item.library_id) this.selectedLibrary.push(item.library_id);
      });
    },

    //选择卡密库回调
    handlerChangeCdkeyIdSubSuccess(row) {
      if (!row) {
        this.$set(this[this.tabName][this.tabIndex], "cdkeyLibrary", {});
        this.$set(this[this.tabName][this.tabIndex], "library_name", "");
        this.$set(this[this.tabName][this.tabIndex], "library_id", 0);
        this.$set(this[this.tabName][this.tabIndex], "stock", 0);
      } else {
        this.$set(this[this.tabName][this.tabIndex], "library_id", row.id);
        this.$set(
          this[this.tabName][this.tabIndex]["cdkeyLibrary"],
          "name",
          row.name
        );
        this.$set(
          this[this.tabName][this.tabIndex],
          "stock",
          row.total_num - row.used_num
        );
      }
    },

    //提交属性值；
    subAttrs(e) {
      let selectData = [];
      this.attrsList.forEach((el, index) => {
        let obj = [];
        el.details.forEach(label => {
          if (label.select) {
            obj.push(label.name);
          }
        });
        if (obj.length) {
          selectData.push(obj);
        }
      });
      let newData = [];
      if (selectData.length) {
        newData = this.doCombination(selectData);
      }
      this.attrShow = false;
      this.activeAtter = selectData;
      this.oneFormBatch[0].attr = newData.length ? newData.join(";") : "全部";
      let manyAttr = this.ManyAttrValue;
      manyAttr.forEach(j => {
        this.$set(j, "select", false);
        if (newData.length) {
          newData.forEach(item => {
            if (j.sku && j.sku.split("").length == item.split("").length) {
              if (j.sku == item) {
                this.$set(j, "select", true);
              }
            } else {
              if (j.sku && j.sku == item) {
                this.$set(j, "select", true);
              }
            }
          });
        } else {
          this.$set(j, "select", true);
        }
      });
      this.$nextTick(function() {
        this.$set(this, "ManyAttrValue", manyAttr);
      });
    },

    watCh(val) {
      const tmp = {};
      const tmpTab = {};
      this.formValidate.attr.forEach((o, i) => {
        tmp["value" + i] = { title: o.value };
        tmpTab["value" + i] = o.detail;
      });
      // this.ManyAttrValue = this.attrFormat(val)
      this.manyTabTit = tmp;
      this.manyTabDate = tmpTab;
      this.formThead = Object.assign({}, this.formThead, tmp);
    },

    //清空卡密
    virtualListClear() {
      this.virtualList = [
        {
          is_type: 0,
          key: "",
          stock: ""
        }
      ];
    },

    /**
     * @description: 商品详情tab==3的相关方法
     */

    // 商品详情
    getEditorContent(data) {
      this.formValidate.content = data;
    },

    /**
     * @description: 营销设置tab==4的相关方法
     */

    // 选择店铺推荐商品
    openRecommend() {
      this.recommendVisible = true;
    },

    /**
     * @description: 其他设置tab==6的相关方法
     */

    // 运费模板
    addTem() {
      this.$refs.templateForm.dialogVisible = true;
      this.$refs.templateForm.resetData();
    },

    // 系统表单下拉数据
    getFormList() {
      associatedFormList()
        .then(res => {
          this.formList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 关联的表单信息
    getFormInfo() {
      if (!this.formValidate.mer_form_id) {
        return;
      } else {
        let time = new Date().getTime() * 1000;
        let formUrl = `${
          this.baseURL
        }/pages/admin/system_form/index?inner_frame=1&time=${time}&form_id=${
          this.formValidate.mer_form_id
        }`;
        this.formUrl = formUrl;
      }
    },

    // 添加服务保障模板
    addServiceTem() {
      this.$refs.serviceGuarantee.add();
    },

    /**
     * 页面点击操作的相关方法
     */

    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.warning(error);
      }
    },

    // 返回上一页
    goBack() {
      sessionStorage.clear();
      window.history.back();
    },

    // 点击上一步
    handleSubmitUp() {
      if (this.formValidate.type === 4) {
        if (this.currentTab === "7") {
          this.currentTab = "3";
        } else if (this.currentTab === "4") {
          this.currentTab = "7";
        } else {
          this.currentTab = (Number(this.currentTab) - 1).toString();
        }
      } else {
        this.currentTab = (Number(this.currentTab) - 1).toString();
      }
    },

    // 点击下一步
    handleSubmitNest(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          if (this.formValidate.type == 4) {
            if (this.currentTab == 7) {
              this.currentTab = "4";
            } else if (this.currentTab == 3) {
              this.currentTab = "7";
            } else {
              this.currentTab = (Number(this.currentTab) + 1).toString();
            }
          } else {
            this.currentTab = (Number(this.currentTab) + 1).toString();
          }
        }
      });
    },
    switchTimed(val) {
      this.is_timed = val;
    },
    // 点击提交按钮
    handleSubmit(name) {
      // 检查 reservationSetting 引用是否存在，避免出现未定义错误
      this.$store.dispatch("settings/setEdit", false);
      let ids = [];
      this.goodList.forEach((item, index) => {
        ids.push(item.product_id);
      });

      this.formValidate.good_ids = ids;
      this.formValidate.auto_off_time = this.is_timed
        ? this.formValidate.auto_off_time
        : "";
      // 遍历验证规则数组进行验证
      for (const rule of validationRules) {
        const { field, message, validator } = rule;
        if (validator(this.formValidate[field])) {
          return this.$message.warning(message);
        }
      }
      this.$refs[name].validate(valid => {
        if (valid) {
          if (this.formValidate.spec_type == 1) {
            if (this.ManyAttrValue.length < 2)
              return this.$message.warning("商品规格-规格数量最少1个");
            // 删除第一项
            let newData = JSON.parse(JSON.stringify(this.ManyAttrValue));

            newData.shift();
            this.formValidate.attrValue = newData;
          } else {
            this.formValidate.attrValue = this.OneattrValue;
            this.formValidate.attr = [];
          }

          for (const attrValueItem of this.formValidate.attrValue) {
            if (!attrValueItem.image) {
              if (attrValueItem.pic) {
                attrValueItem.image = attrValueItem.pic;
              } else {
                return this.$message.warning("请补充规格设置-规格列表-商品规格图片");
              }
            }
          }

          // 预约商品的数据和验证逻辑
          if (this.formValidate.type == 4) {
            if (
              this.formValidate.reservation_time_type == 1 &&
              !this.formValidate.reservation_start_time
            ) {
              return this.$message.warning("请选择预约时间段");
            }
            if (
              !this.formValidate.time_period &&
              this.formValidate.time_period.length
            ) {
              return this.$message.warning("请选择预约时间段");
            }
            // 移除time_period中的stock字段
            this.formValidate.time_period = this.formValidate.time_period.map(
              ({ stock, ...rest }) => rest
            );
            // 检查每个属性的预约设置
            const hasEmptyReservation = this.formValidate.attrValue.some(
              attr => {
                if (
                  !attr &&
                  !attr.reservation &&
                  attr.reservation.length == 0
                ) {
                  this.$message.warning("请设置预约数量");
                  return true;
                }

                // 移除reservation中的is_show字段
                attr.reservation = attr.reservation.map(
                  ({
                    is_show,
                    start,
                    end,
                    stock,
                    end_time,
                    start_time,
                    ...rest
                  }) => ({
                    start_time: start || start_time,
                    end_time: end || end_time,
                    stock: stock
                  })
                );
                return false;
              }
            );
            if (hasEmptyReservation) return;
          }

          this.fullscreenLoading = true;
          this.loading = true;
          let disCreate = this.$route.query.id && !this.$route.query.type;
          disCreate ? this.productUpdate() : this.productCreate();
        }
      });
    },

    // 点击预览按钮
    handlePreview(name) {
      if (this.formValidate.spec_type === 1) {
        let newData = JSON.parse(JSON.stringify(this.ManyAttrValue));
        newData.shift();
        this.formValidate.attrValue = newData;
      } else {
        this.formValidate.attrValue = this.OneattrValue;
        this.formValidate.attr = [];
      }
      productPreviewApi(this.formValidate)
        .then(async res => {
          this.previewVisible = true;
          this.previewKey = res.data.preview_key;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 添加商品
    productCreate() {
      const api =
        this.formValidate.type === 4
          ? productReservationCreateApi
          : productCreateApi;
      api({
        ...this.formValidate,
        activity_label_ids: this.formValidate.activity_label_ids.join(",")
      })
        .then(async res => {
          this.fullscreenLoading = false;
          this.$message.success(res.message);
          this.$router.push({ path: this.roterPre + "/product/list" });
          this.loading = false;
        })
        .catch(res => {
          this.fullscreenLoading = false;
          this.loading = false;
          this.ManyAttrValue = [
            ...this.oneFormBatch,
            ...this.formValidate.attrValue
          ];
          this.$message.error(res.message);
        });
    },

    // 编辑商品
    productUpdate() {
      const api =
        this.formValidate.type === 4
          ? productReservationEditApi
          : productUpdateApi;
      api(this.$route.query.id, {
        ...this.formValidate,
        activity_label_ids: this.formValidate.activity_label_ids.join(",")
      })
        .then(async res => {
          this.fullscreenLoading = false;
          this.$message.success(res.message);
          this.$router.push({ path: this.roterPre + "/product/list" });
          this.formValidate.slider_image = [];
          this.loading = false;
        })
        .catch(res => {
          this.fullscreenLoading = false;
          this.loading = false;
          this.ManyAttrValue = [
            ...this.oneFormBatch,
            ...this.formValidate.attrValue
          ];
          this.$message.error(res.message);
        });
    },

    // 商品编辑-获取商品的信息

    initData() {
      this.getShippingList();
      this.getGuaranteeList();
      this.productGetRule();
      this.getFormList();
      this.$store.dispatch("settings/setEdit", true);
      this.formValidate.slider_image = [];
      if (this.$route.query.id || this.$route.query.type == "copy") {
        this.product_id = this.$route.query.id;
        this.setTagsViewTitle();
        this.getInfo();
      } else {
        this.productCon();
        if (this.deduction_set == -1) this.formValidate.integral_rate = -1;
      }
      if (this.$route.query.type == 1) {
        this.type = this.$route.query.type;
        this.$refs.taoBao.modals = true;
      } else {
        this.type = 0;
      }
    },

    setTagsViewTitle() {
      const title = "编辑商品";
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}-${this.$route.query.id}`
      });
      this.$store.dispatch("tagsView/updateVisitedView", route);
    },

    // 获取服务保障模板
    getGuaranteeList() {
      guaranteeListApi().then(res => {
        this.guaranteeList = res.data;
      });
    },

    // 获取商品属性模板；
    productGetRule() {
      templateLsitApi().then(res => {
        this.ruleList = res.data;
      });
    },
    // 运费模板；
    getShippingList() {
      shippingListApi().then(res => {
        this.shippingList = res.data;
      });
    },

    doCombination(arr) {
      var count = arr.length - 1; //数组长度(从0开始)
      var tmp = [];
      var totalArr = []; // 总数组
      return doCombinationCallback(arr, 0); //从第一个开始
      //js 没有静态数据，为了避免和外部数据混淆，需要使用闭包的形式
      function doCombinationCallback(arr, curr_index) {
        for (let val of arr[curr_index]) {
          tmp[curr_index] = val; //以curr_index为索引，加入数组
          //当前循环下标小于数组总长度，则需要继续调用方法
          if (curr_index < count) {
            doCombinationCallback(arr, curr_index + 1); //继续调用
          } else {
            totalArr.push(tmp.join(",")); //(直接给push进去，push进去的不是值，而是值的地址)
          }
          //js  对象都是 地址引用(引用关系)，每次都需要重新初始化，否则 totalArr的数据都会是最后一次的 tmp 数据；
          let oldTmp = tmp;
          tmp = [];
          for (let index of oldTmp) {
            tmp.push(index);
          }
        }
        return totalArr;
      }
    },
    // 修改积分方式
    updateDeduction(e) {
      this.formValidate.integral_rate = e
    },
    getRecommend(selected) {
      this.goodList =
        selected && selected.length <= 18 ? selected : selected.slice(0, 18);
      this.recommendVisible = false;
    },
    closeRecommend() {
      this.recommendVisible = false;
    },
    // 删除店铺推荐商品
    deleteRecommend(index) {
      this.goodList.splice(index, 1);
    },

    delSpecs(index) {
      this.formValidate.params.splice(index, 1);
    },

    attrFormat(arr) {
      let data = [],
        that = this;
      let res = [];
      return format(arr);
      function format(arr) {
        if (arr.length > 1) {
          arr.forEach((v, i) => {
            if (i === 0) data = arr[i]["detail"];
            const tmp = [];
            data.forEach(function(vv) {
              arr[i + 1] &&
                arr[i + 1]["detail"] &&
                arr[i + 1]["detail"].forEach(g => {
                  const rep2 =
                    (i !== 0 ? "" : arr[i]["value"] + "_$_") +
                    vv +
                    "-$-" +
                    arr[i + 1]["value"] +
                    "_$_" +
                    g;
                  tmp.push(rep2);
                  if (i === arr.length - 2) {
                    const rep4 = {
                      image: "",
                      price: 0,
                      cost: 0,
                      ot_price: 0,
                      select: true,
                      sku: "",
                      stock: 0,
                      cdkey: {},
                      cdkeyLibrary: {},
                      library_name: "",
                      library_id: "",
                      bar_code: "",
                      weight: 0,
                      volume: 0,
                      extension_one: 0,
                      extension_two: 0
                    };
                    rep2.split("-$-").forEach((h, k) => {
                      const rep3 = h.split("_$_");
                      if (!rep4["detail"]) rep4["detail"] = {};
                      rep4["detail"][rep3[0]] = rep3.length > 1 ? rep3[1] : "";
                    });
                    // if(rep4.detail !== 'undefined' && rep4.detail !== null){
                    Object.values(rep4.detail).forEach((v, i) => {
                      rep4["value" + i] = v;
                    });
                    // }
                    res.push(rep4);
                  }
                });
            });
            data = tmp.length ? tmp : [];
          });
        } else {
          const dataArr = [];
          arr.forEach((v, k) => {
            v["detail"].forEach((vv, kk) => {
              dataArr[kk] = v["value"] + "_" + vv;
              res[kk] = {
                image: "",
                price: 0,
                cost: 0,
                ot_price: 0,
                select: true,
                sku: "",
                stock: 0,
                cdkey: {},
                cdkeyLibrary: {},
                library_name: "",
                library_id: "",
                bar_code: "",
                weight: 0,
                volume: 0,
                extension_one: 0,
                extension_two: 0,
                detail: { [v["value"]]: vv }
              };
              Object.values(res[kk].detail).forEach((v, i) => {
                res[kk]["value" + i] = v;
              });
            });
          });
          data.push(dataArr.join("$&"));
        }
        if (that.generateArr.length > 0) {
          that.generateArr.forEach((v, i) => {
            res[i]["image"] = v.image || v.pic;
            res[i]["price"] = v.price;
            res[i]["cost"] = v.cost;
            res[i]["ot_price"] = v.ot_price;
            res[i]["sku"] = v.sku;
            res[i]["stock"] = v.stock;
            res[i]["unique"] = v.unique;
            res[i]["bar_code"] = v.bar_code;
            res[i]["volume"] = v.volume;
            res[i]["weight"] = v.weight;
            res[i]["extension_one"] = v.extension_one;
            res[i]["extension_two"] = v.extension_two;
            res[i]["cdkey"] = (v.cdkey && v.cdkey.length && v.cdkey[0]) || null;
            res[i]["cdkeyLibrary"] = v.cdkeyLibrary || {};
            res[i]["library_name"] = v.cdkeyLibrary && v.cdkeyLibrary.name;
            res[i]["library_id"] = v.library_id || "";
            res[i]["svip_price"] = v.svip_price || "";
          });
        }
        return res;
      }
    },

    handleFocus(val) {
      this.changeAttrValue = val;
    },
    handleBlur() {
      this.changeAttrValue = "";
    },

    //选中属性
    activeAttr(e) {
      this.attrsList = e;
    },
    //关闭属性弹窗
    labelAttr() {
      this.attrShow = false;
    },

    // 立即生成
    generateAttr(data, val) {
      // 判断该段Js执行时间
      this.generateHeader(data);
      const combinations = this.generateCombinations(data);

      const hasDefaultSelect = this.ManyAttrValue.some(item => item.is_default_select == 1);

      let rows = combinations.map(combination => {
        // 新生成的行默认都不选中；若原列表中无默认选中，则仅第一行在后续被设为默认
        let is_default_select = 0;
        const row = {
          attr_arr: combination,
          detail: {},
          cdkey: {},
          title: "",
          key: "",
          price: 0,
          image: "",
          ot_price: 0,
          cost: 0,
          stock: 0,
          is_show: 1,
          is_default_select,
          unique: "",
          weight: "",
          extension_one: 0,
          extension_two: 0,
          svip_price: 0
        };
        // 判断商品类型是卡密/优惠券
        let virtualType = this.formValidate.type;
        if (virtualType == 3) {
          //卡密
          this.$set(row, "virtual_list", []);
        } else if (virtualType == 2) {
          //云盘
          this.$set(row, "cdkey", {});
          this.$set(row, "coupon_name", "");
        } else if (virtualType == 4) {
        }
        for (let i = 0; i < combination.length; i++) {
          const value = combination[i];
          this.$set(row, data[i].value, value);
          this.$set(row, "title", data[i].value);
          this.$set(row, "key", data[i].value);
          this.$set(row.detail, data[i].value, value);
          // 如果manyFormValidate中存在该属性值，则赋值
          for (let k = 0; k < this.ManyAttrValue.length; k++) {
            const manyItem = this.ManyAttrValue[k];
            // 对比两个数组是否完全相等
            if (
              k > 0 &&
              manyItem.attr_arr &&
              arraysEqual(manyItem.attr_arr, combination)
            ) {
              Object.assign(row, {
                price: manyItem.price,
                cost: manyItem.cost,
                ot_price: manyItem.ot_price,
                stock: manyItem.stock,
                reservation: manyItem.reservation || [],
                image: manyItem.image,
                unique: manyItem.unique || "",
                weight: manyItem.weight || "",
                is_show: manyItem.is_show || 1,
                is_default_select: manyItem.is_default_select || 0,
                volume: manyItem.volume || 0,
                is_virtual: manyItem.is_virtual,
                extension_one: manyItem.extension_one,
                extension_two: manyItem.extension_two,
                svip_price: manyItem.svip_price
              });
              if (virtualType == 1) {
                row.virtual_list = manyItem.virtual_list;
              }
            } else if (
              k > 0 &&
              manyItem.attr_arr.length &&
              data[i].add_pic &&
              combination.includes(val)
            ) {
              // data[i].detail中的value是规格值 存在与 manyItem.attr_arr 中的某一项
              data[i].detail.map((e, ii) => {
                combination.includes(e.value) &&
                  this.$set(row, "image", e.image);
              });
            }
          }
        }
        return row;
      });
      // 若规格列表中不存在已选中的默认规格，将第一个规格设为默认选中，确保有且只有一个默认
      if (!hasDefaultSelect && rows.length > 0) {
        rows[0].is_default_select = 1;
      }
      this.$nextTick(() => {
        // rows数组第一项 新增默认数据 oneFormBatch
        this.ManyAttrValue = [...this.oneFormBatch, ...rows];
      });
    },
    // 规格值改变
    attrDetailChangeValue(val, i) {
      if (this.ManyAttrValue.length) {
        let key = this.attrs[i].value;
        this.ManyAttrValue.map((item, i) => {
          if (i > 0) {
            if (
              Object.keys(item.detail).includes(key) &&
              item.detail[key] === this.changeAttrValue
            ) {
              item.detail[key] = val;
              let index = item.attr_arr.findIndex(
                item => item === this.changeAttrValue
              );
              item.attr_arr[index] = val;
            }
          }
        });
        this.changeAttrValue = val;
      } else {
        this.generateAttr(this.attrs, 1);
      }
    },

    // 生成规格组合
    generateCombinations(arr, prefix = []) {
      if (arr.length === 0) {
        return [prefix];
      }
      const [first, ...rest] = arr;
      return first.detail.flatMap(detail =>
        this.generateCombinations(rest, [...prefix, detail.value])
      );
    },

    // 获取接口详情
    getInfo() {
      this.fullscreenLoading = true;
      let parmas = {};
      if (this.$route.query.type == "copy") parmas.is_copy = 1;
      let api =
        this.$route.query.productType == 4
          ? productReservationInfoApi
          : productLstDetail;
      api(this.$route.query.id, parmas)
        .then(async res => {
          this.generateArr = [];
          let info = res.data;
          this.getSpecsLst(info, true);
          this.productCon();
          if (info.type == 3) this.getCdkeyLibraryList();
          if (info.mer_form_id) {
            let time = new Date().getTime() * 1000;
            let formUrl = `${
              this.baseURL
            }/pages/admin/system_form/index?inner_frame=1&time=${time}&form_id=${
              info.mer_form_id
            }`;
            this.formUrl = formUrl;
          }
        })
        .catch(res => {
          this.fullscreenLoading = false;
          this.$message.error(res.message);
        });
    },

    // 给商品表单赋值
    infoData(data, type) {
      if (type == "taobao") {
        this.formValidate.type = Number(this.$route.query.productType);
        if (this.formValidate.type == 4) {
          if (data.attr.length > 0) {
            data.attr = [data.attr[0]];
          }
        }
      }
      this.deduction_set = data.integral_rate == -1 ? -1 : 1;
      this.goodList = data.goodList || [];
      this.attrs = data.attr || [];
      data.attrValue.forEach(val => {
        this.$set(val, "select", true);
        if (data.type == 3) {
          this.$set(
            val,
            "library_id",
            val.library_id == 0 ? "" : val.library_id
          );
        }
      });
      this.formValidate = {
        ...data,
        activity_label_ids: Array.isArray(data.activity_label_ids) ? data.activity_label_ids.map(Number) : []
      };
      if (data.type != 4) {
        this.formValidate.delivery_way =
          data.delivery_way && data.delivery_way.length
            ? data.delivery_way.map(String)
            : this.deliveryType;
      }

      this.formValidate.temp_id = mateName(
        this.shippingList,
        "shipping_template_id",
        data.temp_id
      );
      this.formValidate.mer_form_id = mateName(
        this.formList,
        "form_id",
        data.mer_form_id
      );

      this.is_timed = data.auto_off_time ? 1 : 0;
      this.formValidate.spec_type = Number(data.spec_type);
      this.formValidate.params = data.params || [];
      this.formValidate.couponData = data.coupon || [];
      this.formValidate.mer_labels =
        data.mer_labels && data.mer_labels.length
          ? data.mer_labels.map(Number)
          : [];
      this.formValidate.guarantee_template_id = data.guarantee_template_id
        ? data.guarantee_template_id
        : "";

      if (type == "taobao") {
        let obj = {
          reservation_time_type: 1,
          reservation_type: 3,
          reservation_start_time: "",
          reservation_end_time: "",
          show_num_type: 1,
          sale_time_type: 1,
          sale_time_start_day: "",
          sale_time_end_day: "",
          sale_time_week: [1, 2, 3, 4, 5, 6, 7],
          show_reservation_days: 10,
          is_advance: 0,
          advance_time: 0,
          is_cancel_reservation: 0,
          cancel_reservation_time: 0,
          reservation_form_type: 1
        };
        for (let key in obj) {
          this.formValidate[key] = obj[key];
        }
      }

      if (this.formValidate.type == 4) {
        this.formValidate.reservation_time_type = Number(
          data.reservation_time_type
        );

        this.formValidate.show_num_type = Number(data.show_num_type);
        if (data.attrValue.length > 0) {
          data.attrValue.forEach(item => {
            if (item.reservation && item.reservation.length > 0) {
              item.reservation.forEach(el => {
                el.start = el.start_time;
                el.end = el.end_time;
              });
            }
          });
        }
      }
      this.formValidate.type = Number(this.$route.query.productType);

      if (data.spec_type == 0) {
        this.ManyAttrValue = [];
        (data.attrValue[0].list = []), (this.OneattrValue = data.attrValue);
      } else {
        if (this.formValidate.extend.length != 0) {
          this.customBtn = 1;
        }

        this.generateHeader(this.attrs);

        this.ManyAttrValue = [...this.oneFormBatch, ...data.attrValue];
        if (type == "taobao" && this.formValidate.type == 4) {
          this.ManyAttrValue = [];
          this.generateAttr(this.attrs);
        }
      }
      if (
        this.formValidate.custom_temp_id.length > 0 ||
        this.formValidate.param_temp_id
      ) {
        this.getSpecsList();
      }

      if (type == "taobao") {
        this.formValidate.delivery_free = 0;
        this.formValidate.is_gift_bag = 0;
        this.formValidate.extension_type = 0;
      }

      this.fullscreenLoading = false;
      setTimeout(e => {
        // this.checkAllGroup(data.extension_type);
      }, 1000);
    },

    checkAllGroup(data) {
      let endLength = this.attrs.length + 3;
      if (this.formValidate.spec_type === 0) {
        if (data == 0) {
          this.columnsInstall = this.columns2
            .slice(0, endLength)
            .concat(this.member);
        } else if (data == 1) {
          this.columnsInstall = this.columns2
            .slice(0, endLength)
            .concat(this.rakeBack);
        } else {
          this.columnsInstall = this.columns2.slice(0, endLength);
        }
      } else {
        if (data == 0) {
          this.columnsInstal2 = this.columnsInstalM
            .slice(0, endLength)
            .concat(this.member);
        } else if (data == 1) {
          this.columnsInstal2 = this.columnsInstalM
            .slice(0, endLength)
            .concat(this.rakeBack);
        } else {
          this.columnsInstal2 = this.columnsInstalM.slice(0, endLength);
        }
      }
    }
  }
};
</script>

<style scoped lang="scss">
.footer {
  position: fixed;
  bottom: 0;
  right: 0;
  width: var(--container-width);
  height: 60px;
  background: #fff;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 90;
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

.divBox {
  margin-bottom: 46px;
}
</style>

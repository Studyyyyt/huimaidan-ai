<template>
  <div class="divBox">
    <el-card class="box-card">
      <!-- 步骤条，显示当前创建助力活动的步骤 -->
      <div slot="header" class="clearfix">
        <el-steps :active="currentTab" align-center finish-status="success">
          <el-step title="选择助力商品" />
          <el-step title="填写基础信息" />
          <el-step title="修改商品详情" />
        </el-steps>
      </div>
      <!-- 表单，用于输入助力活动相关信息 -->
      <el-form
        ref="formValidate"
        v-loading="fullscreenLoading"
        class="formValidate mt20"
        :rules="ruleValidate"
        :model="formValidate"
        label-width="120px"
        @submit.native.prevent
      >
        <!-- 选择助力商品步骤 -->
        <div v-show="currentTab === 0" style="overflow: hidden;">
          <el-row :gutter="24">
            <el-col v-bind="grid2">
              <el-form-item label="选择商品：" prop="image">
                <div class="upLoadPicBox" @click="add()">
                  <div v-if="formValidate.image" class="pictrue">
                    <img :src="formValidate.image" />
                  </div>
                  <div v-else class="upLoad">
                    <i class="el-icon-camera cameraIconfont" />
                  </div>
                </div>
              </el-form-item>
            </el-col>
          </el-row>
        </div>
        <!-- 填写基础信息步骤 -->
        <div v-show="currentTab === 1">
          <!-- 商品图片相关 -->
          <el-row :gutter="24">
            <el-col v-bind="grid2">
              <el-form-item label="商品主图：" prop="image">
                <div class="upLoadPicBox" @click="modalPicTap('1')">
                  <div v-if="formValidate.image" class="pictrue">
                    <img :src="formValidate.image" />
                  </div>
                  <div v-else class="upLoad">
                    <i class="el-icon-camera cameraIconfont" />
                  </div>
                </div>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="商品轮播图：" prop="slider_image">
                <div class="acea-row">
                  <div
                    v-for="(item, index) in formValidate.slider_image"
                    :key="index"
                    class="pictrue"
                    draggable="false"
                    @dragstart="handleDragStart($event, item)"
                    @dragover.prevent="handleDragOver($event, item)"
                    @dragenter="handleDragEnter($event, item)"
                    @dragend="handleDragEnd($event, item)"
                  >
                    <img :src="item" />
                    <i
                      class="el-icon-error btndel"
                      @click="handleRemove(index)"
                    />
                  </div>
                  <div
                    v-if="formValidate.slider_image.length < 10"
                    class="upLoadPicBox"
                    @click="modalPicTap('2')"
                  >
                    <div class="upLoad">
                      <i class="el-icon-camera cameraIconfont" />
                    </div>
                  </div>
                </div>
              </el-form-item>
            </el-col>
            <!-- 商品信息-->
            <el-col class="sp100">
              <el-form-item label="商品名称：" prop="store_name">
                <el-input
                  v-model="formValidate.store_name"
                  size="small"
                  placeholder="请输入商品名称"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="24">
            <el-col class="sp100">
              <el-form-item label="助力活动简介：">
                <el-input
                  v-model="formValidate.store_info"
                  size="small"
                  type="textarea"
                  :rows="3"
                  placeholder="请输入助力活动简介"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <!-- 活动时间、助力人数等信息 -->
          <el-row :gutter="24">
            <el-col v-bind="grid2">
              <el-form-item label="活动时间：" required>
                <el-date-picker
                  v-model="assist_date"
                  type="datetimerange"
                  size="small"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  align="right"
                  @change="onchangeTime"
                />
              </el-form-item>
            </el-col>
            <el-col v-bind="grid2">
              <el-form-item label="助力人数：" prop="assist_count">
                <el-input-number
                  v-model.number="formValidate.assist_count"
                  size="small"
                  :min="1"
                  placeholder="请输入人数"
                />
              </el-form-item>
            </el-col>
            <el-col v-bind="grid2">
              <el-form-item label="用户助力次数：" prop="assist_user_count">
                <el-input-number
                  v-model.number="formValidate.assist_user_count"
                  size="small"
                  :min="1"
                  placeholder="请输入次数"
                />
              </el-form-item>
            </el-col>
            <el-col v-bind="grid2">
              <el-form-item label="单位：" prop="unit_name">
                <el-input
                  v-model="formValidate.unit_name"
                  size="small"
                  placeholder="请输入单位"
                  style="width: 130px;"
                />
              </el-form-item>
            </el-col>
            <el-col v-bind="grid2">
              <el-form-item label="排序：">
                <el-input-number
                  v-model="formValidate.sort"
                  size="small"
                  placeholder="请输入排序序号"
                />
              </el-form-item>
            </el-col>
            <el-col v-bind="grid2">
              <el-form-item label="限购：">
                <el-input-number
                  v-model="formValidate.pay_count"
                  size="small"
                  :min="1"
                  placeholder="请输入限购数"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <!-- 活动状态、送货方式等信息 -->
          <el-row :gutter="24">
            <el-col v-bind="grid2">
              <el-form-item label="活动状态：">
                <el-radio-group v-model="formValidate.is_show">
                  <el-radio :label="0" class="radio">关闭</el-radio>
                  <el-radio :label="1">开启</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :span="24">
              <el-form-item label="送货方式：" prop="delivery_way">
                <div class="acea-row">
                  <el-checkbox-group v-model="formValidate.delivery_way">
                    <el-checkbox
                      v-for="item in deliveryList"
                      :key="item.value"
                      :label="item.value"
                    >
                      {{ item.name }}
                    </el-checkbox>
                  </el-checkbox-group>
                </div>
              </el-form-item>
            </el-col>
            <el-col
              v-if="
                (formValidate.delivery_way.length == 2 ||
                  (formValidate.delivery_way.length == 1 &&
                    formValidate.delivery_way[0] == 2)) &&
                  formValidate.type == 0
              "
              :span="24"
            >
              <el-form-item label="是否包邮：">
                <el-radio-group v-model="formValidate.delivery_free">
                  <el-radio :label="0" class="radio">否</el-radio>
                  <el-radio :label="1">是</el-radio>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col
              v-if="
                formValidate.delivery_free == 0 &&
                  (formValidate.delivery_way.length == 2 ||
                    (formValidate.delivery_way.length == 1 &&
                      formValidate.delivery_way[0] == 2)) &&
                  formValidate.type == 0
              "
              :span="24"
            >
              <el-form-item label="运费模板：" prop="temp_id">
                <div class="acea-row">
                  <el-select
                    v-model="formValidate.temp_id"
                    size="small"
                    placeholder="请选择"
                    class="selWidthd mr20"
                  >
                    <el-option
                      v-for="item in shippingList"
                      :key="item.shipping_template_id"
                      :label="item.name"
                      :value="item.shipping_template_id"
                    />
                  </el-select>
                  <el-button class="mr15" size="small" @click="addTem"
                    >添加运费模板</el-button
                  >
                </div>
              </el-form-item>
            </el-col>
            <el-col v-if="labelList.length" :span="24">
              <el-form-item label="商品标签：">
                <el-select
                  v-model="formValidate.mer_labels"
                  size="small"
                  multiple
                  placeholder="请选择"
                  class="selWidthd"
                >
                  <el-option
                    v-for="item in labelList"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          <!--系统表单-->
          <el-row
            :gutter="24"
            v-if="
              formData.length > 0 &&
                currentTab === 1 &&
                formValidate.mer_form_id
            "
          >
            <el-col>
              <el-form-item label="关联系统表单：">
                <!-- <el-select
                size="small"
                class="selWidthd"
                disabled
                v-model="formValidate.mer_form_id"
                >
                  <el-option
                    v-for="items in formList"
                    :key="items.form_id"
                    :value="items.form_id"
                    :label="items.name"
                  >{{ items.name }}
                  </el-option>
                </el-select> -->
                <span class="explanation"
                  >如需改活动商品系统表单，请前往对应普通商品>编辑>修改系统表单即可实现</span
                >
              </el-form-item>
            </el-col>
            <el-col :span="16">
              <el-form-item>
                <div style="width: 350px;">
                  <iframe
                    id="iframe"
                    class="iframe-box"
                    :src="formUrl"
                    frameborder="0"
                    ref="iframe"
                    style="min-height: 300px;"
                  ></iframe>
                </div>
                <!-- <el-table
                  border
                  class="specsList"
                  :data="formData"
                  size="small"
                >
                  <el-table-column prop="label" label="表单标题" min-width="100" />
                  <el-table-column prop="type" label="表单类型" min-width="100">
                    <template slot-scope="{row}">
                      <span>{{row.type | formTypeFilter}}</span>
                    </template>
                  </el-table-column>
                  <el-table-column min-width="100" label="是否必填">
                    <template slot-scope="{row}">
                      <span>{{row.val ? '必填'  : '不必填'}}</span>
                    </template>
                  </el-table-column>
                </el-table> -->
              </el-form-item>
            </el-col>
          </el-row>
          <!-- 单规格表格-->
          <el-row :gutter="24">
            <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
              <el-form-item
                v-if="formValidate.spec_type === 0"
                label="规格列表："
              >
                <el-table
                  :data="OneattrValue"
                  border
                  class="tabNumWidth"
                  size="small"
                >
                  <el-table-column align="center" label="图片" min-width="80">
                    <template slot-scope="scope">
                      <div class="upLoadPicBox">
                        <div v-if="formValidate.image" class="pictrue tabPic">
                          <img :src="scope.row.image" />
                        </div>
                        <div v-else class="upLoad tabPic">
                          <i class="el-icon-camera cameraIconfont" />
                        </div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="划线价" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row["price"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="助力价" min-width="80">
                    <template slot-scope="scope">
                      <el-input
                        v-model="scope.row['assist_price']"
                        type="number"
                        size="small"
                        class="priceBox"
                        :min="0"
                        :max="scope.row['price']"
                        @blur="limitPrice(scope.row)"
                      />
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="成本价" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row["cost"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="库存" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row["old_stock"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="限量" min-width="80">
                    <template slot-scope="scope">
                      <el-input
                        v-model="scope.row['stock']"
                        type="number"
                        size="small"
                        class="priceBox"
                        :max="scope.row['old_stock']"
                        :min="0"
                      />
                    </template>
                  </el-table-column>
                  <el-table-column
                    align="center"
                    label="规格编码"
                    min-width="80"
                  >
                    <template slot-scope="scope">
                      <span>{{ scope.row["bar_code"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="条形码" min-width="80">
                    <template slot-scope="scope">
                      <span>{{ scope.row["bar_code_number"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column
                    align="center"
                    label="重量（KG）"
                    min-width="80"
                  >
                    <template slot-scope="scope">
                      <span>{{ scope.row["weight"] }}</span>
                    </template>
                  </el-table-column>
                  <el-table-column
                    align="center"
                    label="体积（m³）"
                    min-width="80"
                  >
                    <template slot-scope="scope">
                      <span>{{ scope.row["volume"] }}</span>
                    </template>
                  </el-table-column>
                </el-table>
              </el-form-item>
            </el-col>
          </el-row>
          <!-- 多规格表格-->
          <el-row :gutter="24">
            <el-form-item
              v-if="formValidate.spec_type === 1"
              label="规格列表："
            >
              <el-table
                ref="multipleTable"
                :data="ManyAttrValue"
                tooltip-effect="dark"
                size="small"
                highlight-current-row
                :row-key="
                  row => {
                    return row.id;
                  }
                "
              >
                <el-table-column label="选择" width="65">
                  <template slot-scope="scope">
                    <el-radio
                      v-model="radio"
                      :label="scope.$index"
                      @change.native="getCurrentRow(scope.row)"
                    />
                  </template>
                </el-table-column>
                <template v-if="manyTabDate">
                  <el-table-column
                    v-for="(item, iii) in manyTabDate"
                    :key="iii"
                    align="center"
                    :label="manyTabTit[iii].title"
                    min-width="80"
                  >
                    <template slot-scope="scope">
                      <span class="priceBox" v-text="scope.row[iii]" />
                    </template>
                  </el-table-column>
                </template>
                <el-table-column align="center" label="图片" min-width="80">
                  <template slot-scope="scope">
                    <div class="upLoadPicBox">
                      <div v-if="scope.row.image" class="pictrue tabPic">
                        <img :src="scope.row.image" />
                      </div>
                      <div v-else class="upLoad tabPic">
                        <i class="el-icon-camera cameraIconfont" />
                      </div>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="划线价" min-width="80">
                  <template slot-scope="scope">
                    <span>{{ scope.row["price"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="助力价" min-width="80">
                  <template slot-scope="scope">
                    <el-input
                      v-model="scope.row['assist_price']"
                      type="number"
                      size="small"
                      class="priceBox"
                      :min="0"
                      :max="scope.row['price']"
                    />
                  </template>
                </el-table-column>
                <el-table-column align="center" label="成本价" min-width="80">
                  <template slot-scope="scope">
                    <span>{{ scope.row["cost"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="库存" min-width="80">
                  <template slot-scope="scope">
                    <span>{{ scope.row["old_stock"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="限量" min-width="80">
                  <template slot-scope="scope">
                    <el-input
                      v-model="scope.row['stock']"
                      type="number"
                      size="small"
                      class="priceBox"
                      :min="0"
                      :max="scope.row['old_stock']"
                    />
                  </template>
                </el-table-column>
                <el-table-column align="center" label="规格编码" min-width="80">
                  <template slot-scope="scope">
                    <span>{{ scope.row["bar_code"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="条形码" min-width="80">
                  <template slot-scope="scope">
                    <span>{{ scope.row["bar_code_number"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  align="center"
                  label="重量（KG）"
                  min-width="80"
                >
                  <template slot-scope="scope">
                    <span>{{ scope.row["weight"] }}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  align="center"
                  label="体积（m³）"
                  min-width="80"
                >
                  <template slot-scope="scope">
                    <span>{{ scope.row["volume"] }}</span>
                  </template>
                </el-table-column>
              </el-table>
            </el-form-item>
          </el-row>
        </div>
        <!-- 商品详情-->
        <el-row v-show="currentTab === 2">
          <el-col :span="24">
            <el-form-item label="商品详情：">
              <WangEditor
                :content="formValidate.content"
                @editorContent="getEditorContent"
                style="width: 100%;"
              ></WangEditor>
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item style="margin-top:30px;">
          <el-button
            v-show="currentTab > 0"
            type="primary"
            class="submission"
            size="small"
            @click="handleSubmitUp"
            >上一步</el-button
          >
          <el-button
            v-show="currentTab == 0"
            type="primary"
            class="submission"
            size="small"
            @click="handleSubmitNest1('formValidate')"
            >下一步</el-button
          >
          <el-button
            v-show="currentTab == 1"
            type="primary"
            class="submission"
            size="small"
            @click="handleSubmitNest2('formValidate')"
            >下一步</el-button
          >
          <el-button
            v-show="currentTab === 2"
            :loading="loading"
            type="primary"
            class="submission"
            size="small"
            @click="handleSubmit('formValidate')"
            >提交</el-button
          >
          <el-button
            v-show="currentTab === 2"
            :loading="loading"
            type="primary"
            class="submission"
            size="small"
            @click="handlePreview('formValidate')"
            >预览</el-button
          >
        </el-form-item>
      </el-form>
    </el-card>
    <!--选择商品-->
    <goods-list ref="goodsList" @getProduct="getProduct" />
    <!--预览商品-->
    <div v-if="previewVisible">
      <div class="bg" @click.stop="previewVisible = false" />
      <preview-box
        v-if="previewVisible"
        ref="previewBox"
        :preview-key="previewKey"
      />
    </div>
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
import goodsList from "../../seckill/seckillGoods/goodsList";
import previewBox from "@/components/previewBox/index";
import templatesFrom from "@/components/templatesFrom";
import { mateName } from "@/utils";
import {
  shippingListApi,
  templateLsitApi,
  assistCreateProApi,
  assistProDetailApi,
  productDetailApi,
  categorySelectApi,
  categoryListApi,
  assistUpdateProApi,
  assistPreviewApi,
  productConfigApi,
  getProductLabelApi,
  associatedFormList,
  associatedFormInfo
} from "@/api/product";
import { roterPre } from "@/settings";
import SettingMer from "@/libs/settingMer";
import { getToken } from "@/utils/auth";

// 默认表单数据对象
const defaultObj = {
  product_id: "",
  image: "",
  slider_image: [],
  store_name: "",
  store_info: "",
  start_day: "",
  end_day: "",
  start_time: "",
  end_time: "",
  is_open_recommend: 1,
  is_open_state: 1,
  is_show: 1,
  keyword: "",
  brand_id: "", // 品牌id
  //   cate_id: '', // 平台分类id
  //   mer_cate_id: [], // 商户分类id
  pay_count: 1,
  unit_name: "",
  assist_user_count: 1,
  assist_count: 2,
  integral: 0,
  is_good: 0,
  temp_id: "",
  assist_date: "",
  delivery_way: [],
  mer_labels: [],
  delivery_free: 0,
  attrValue: [
    {
      image: "",
      price: null,
      assist_price: null,
      cost: null,
      ot_price: null,
      old_stock: null,
      stock: null,
      bar_code: null,
      bar_code_number: null,
      weight: null,
      volume: null
    }
  ],
  attr: [],
  extension_type: 0,
  content: "",
  spec_type: 0,
  is_gift_bag: 0
};

// 商品推荐选项
const proOptions = [
  {
    name: "店铺推荐",
    value: "is_good"
  }
];
export default {
  name: "AssistProductAdd",
  components: {
    WangEditor,
    goodsList,
    previewBox,
    templatesFrom
  },
  data() {
    const url =
      SettingMer.https + "/upload/image/0/file?ueditor=1&token=" + getToken();
    return {
      radio: "",
      multipleTable: [],
      dialogVisible: false,
      product_id: "",
      // multipleSelection: [],
      optionsCate: {
        value: "store_category_id",
        label: "cate_name",
        children: "children",
        emitPath: false
      },
      roterPre,
      selectRule: "",
      checkboxGroup: [],
      recommend: proOptions,
      tabs: [],
      fullscreenLoading: false,
      props: {
        emitPath: false
      },
      propsMer: {
        emitPath: false,
        multiple: true
      },
      active: 0,
      OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])], // 多规格
      ruleList: [],
      merCateList: [], // 商户分类筛选数据
      categoryList: [], // 平台分类筛选数据
      shippingList: [], // 发货时间数据
      deliveryList: [],
      labelList: [], // 商品标签数据
      BrandList: [], // 品牌数据
      formValidate: Object.assign({}, defaultObj),
      timeVal: "",
      assist_date: "",
      maxStock: "",
      addNum: 0,
      singleSpecification: {},
      multipleSpecifications: [],
      formDynamics: {
        template_name: "",
        template_value: []
      },
      manyTabTit: {},
      manyTabDate: {},
      grid2: {
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24
      },
      // 规格数据
      formDynamic: {
        attrsName: "",
        attrsVal: ""
      },
      isBtn: false,
      manyFormValidate: [],
      images: [],
      currentTab: 0,
      isChoice: "",
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24
      },
      loading: false,
      ruleValidate: {
        store_name: [
          { required: true, message: "请输入商品名称", trigger: "blur" }
        ],
        assist_date: [
          { required: true, message: "请选择助力活动时间", trigger: "blur" }
        ],
        // mer_cate_id: [{ required: true, message: '请选择商户分类', trigger: 'change', type: 'array', min: '1' }],
        // cate_id: [{ required: true, message: '请选择平台分类', trigger: 'change' }],
        keyword: [
          { required: true, message: "请输入商品关键字", trigger: "blur" }
        ],
        unit_name: [{ required: true, message: "请输入单位", trigger: "blur" }],
        assist_user_count: [
          {
            required: true,
            message: "请输入助力人数",
            trigger: "blur",
            type: "number"
          }
        ],
        assist_count: [
          { required: true, message: "请输入助力次数", trigger: "blur" }
        ],
        store_info: [
          { required: true, message: "请输入秒杀活动简介", trigger: "blur" }
        ],
        temp_id: [
          { required: true, message: "请选择运费模板", trigger: "change" }
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
        delivery_way: [
          { required: true, message: "请选择送货方式", trigger: "change" }
        ]
      },
      attrInfo: {},
      keyNum: 0,
      extensionStatus: 0,
      previewVisible: false,
      previewKey: "",
      deliveryType: [],
      formData: [],
      formUrl: "",
      // baseURL: 'http://localhost:8080',
      baseURL: SettingMer.httpUrl || "http://localhost:8080",
      formList: []
    };
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, defaultObj.attrValue[0]);
      delete obj.image;
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
    }
  },
  created() {
    this.tempRoute = Object.assign({}, this.$route);
  },
  mounted() {
    this.formValidate.slider_image = [];
    if (this.$route.params.id) {
      this.setTagsViewTitle();
      this.getInfo(this.$route.params.id);
      this.currentTab = 1;
    } else {
      this.formValidate.attr.map(item => {
        this.$set(item, "inputVisible", false);
      });
      this.productCon();
    }
    this.getCategorySelect();
    this.getCategoryList();
    this.getShippingList();
    this.getLabelLst();
    this.$store.dispatch("settings/setEdit", true);
  },
  methods: {
    // 获取商品标签列表
    getLabelLst() {
      getProductLabelApi()
        .then(res => {
          this.labelList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取商品配置信息
    productCon() {
      productConfigApi()
        .then(res => {
          this.deliveryType = res.data.delivery_way.map(String);
          const name = this.formValidate.type == 0 ? "快递配送" : "虚拟发货";
          if (this.deliveryType.length == 2) {
            if (this.formValidate.type == 2) {
              //卡密商品
              this.deliveryList = [{ value: "2", name: name }];
            } else {
              this.deliveryList = [
                { value: "1", name: "到店自提" },
                { value: "2", name: name }
              ];
            }
          } else {
            if (
              this.deliveryType.length == 1 &&
              this.deliveryType[0] == "1" &&
              this.formValidate.type != 2
            ) {
              this.deliveryList = [{ value: "1", name: "到店自提" }];
            } else {
              this.deliveryList = [{ value: "2", name: name }];
              this.formValidate.delivery_way = ["2"];
            }
          }
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取选中的表格行数据
    getCurrentRow(row) {
      row.checked = true;
      this.multipleTable = row;
      this.formValidate.attrValue = [row];
    },
    // 限制助力价范围
    limitPrice(row) {
      if (row.assist_price - row.price > 0) {
        row.assist_price = row.price;
      } else if (row.assist_price < 0) {
        row.assist_price = 0;
      }
    },
    // 打开商品选择弹窗
    add() {
      this.$refs.goodsList.dialogVisible = true;
    },
    // 获取选择的商品信息
    getProduct(data) {
      this.formValidate.image = data.src;
      this.product_id = data.id;
    },
    // 处理活动时间选择
    onchangeTime(e) {
      this.assist_date = e;

      this.formValidate.start_time = e
        ? this.moment(e[0]).format("YYYY-MM-DD HH:mm:ss")
        : "";
      this.formValidate.end_time = e
        ? this.moment(e[1]).format("YYYY-MM-DD HH:mm:ss")
        : "";
    },
    // 设置标签页标题
    setTagsViewTitle() {
      const title = "编辑商品";
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}-${this.$route.params.id}`
      });
      this.$store.dispatch("tagsView/updateVisitedView", route);
    },
    // 处理推荐选项改变
    onChangeGroup() {
      this.checkboxGroup.includes("is_good")
        ? (this.formValidate.is_good = 1)
        : (this.formValidate.is_good = 0);
    },
    // 处理多规格数据变化
    watCh(val) {
      const tmp = {};
      const tmpTab = {};
      this.formValidate.attr.forEach((o, i) => {
        tmp["value" + i] = {
          title: o.value
        };
        tmpTab["value" + i] = "";
      });
      this.ManyAttrValue.forEach((val, index) => {
        const key = Object.values(val.detail)
          .sort()
          .join("/");
        if (this.attrInfo[key]) this.ManyAttrValue[index] = this.attrInfo[key];
      });
      this.attrInfo = {};
      this.ManyAttrValue.forEach(val => {
        this.attrInfo[
          Object.values(val.detail)
            .sort()
            .join("/")
        ] = val;
      });
      this.manyTabTit = tmp;
      this.manyTabDate = tmpTab;
    },
    // 打开运费模板添加弹窗
    addTem() {
      this.$refs.templateForm.dialogVisible = true;
      this.$refs.templateForm.resetData();
    },
    // 获取商户分类列表
    getCategorySelect() {
      categorySelectApi()
        .then(res => {
          this.merCateList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 平台分类；
    getCategoryList() {
      categoryListApi()
        .then(res => {
          this.categoryList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
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
    // 删除表格中的属性
    delAttrTable(index) {
      this.ManyAttrValue.splice(index, 1);
    },
    // 获取商品详情编辑器内容
    getEditorContent(data) {
      this.formValidate.content = data;
    },
    // 获取商品信息
    getInfo(id) {
      this.fullscreenLoading = true;
      this.$route.params.id
        ? assistProDetailApi(id)
            .then(async res => {
              const info = res.data;
              this.formValidate = {
                type: info.type,
                product_id: info.product_id,
                image: info.product.image,
                slider_image: info.product.slider_image,
                store_name: info.store_name,
                store_info: info.store_info,
                assist_count: info.assist_count ? info.assist_count : 2,
                assist_user_count: info.assist_user_count
                  ? info.assist_user_count
                  : 1,
                start_time: info.start_time ? info.start_time + ":00" : "",
                end_time: info.end_time ? info.end_time + ":00" : "",
                brand_id: info.product.brand_id, // 品牌id
                pay_count: info.pay_count ? info.pay_count : 1,
                unit_name: info.product.unit_name,
                is_good: info.product.is_good,
                temp_id: mateName(
                  this.shippingList,
                  "shipping_template_id",
                  info.product.temp_id
                ),
                is_show: info.is_show,
                mer_form_id: info.product.mer_form_id,
                attr: info.product.attr,
                sort: info.product.sort,
                extension_type: info.extension_type,
                content: info.product.content.content,
                spec_type: info.product.spec_type,
                is_gift_bag: info.product.is_gift_bag,
                delivery_way:
                  info.product.delivery_way && info.product.delivery_way.length
                    ? info.product.delivery_way.map(String)
                    : this.deliveryType,
                delivery_free: info.product.delivery_free
                  ? info.product.delivery_free
                  : 0,
                mer_labels:
                  info.mer_labels && info.mer_labels.length
                    ? info.mer_labels.map(Number)
                    : []
              };
              if (this.formValidate.spec_type === 0) {
                this.OneattrValue = info.product.attrValue;
                // this.OneattrValue.forEach((val, i) => {
                //   this.$set(this.OneattrValue[i], 'assist_price', val.assistSku && val.assistSku.assist_price ? val.assistSku.assist_price : 0)
                // });
                this.$set(
                  this.OneattrValue[0],
                  "assist_price",
                  this.OneattrValue[0].assistSku &&
                    this.OneattrValue[0].assistSku.assist_price
                    ? this.OneattrValue[0].assistSku.assist_price
                    : 0
                );
                this.$set(
                  this.OneattrValue[0],
                  "stock",
                  this.OneattrValue[0].assistSku
                    ? this.OneattrValue[0].assistSku.stock
                    : this.OneattrValue[0].old_stock
                );
              } else {
                this.ManyAttrValue = info.product.attrValue;
                this.ManyAttrValue.forEach((val, i) => {
                  this.attrInfo[
                    Object.values(val.detail)
                      .sort()
                      .join("/")
                  ] = val;
                  this.$set(
                    this.ManyAttrValue[i],
                    "assist_price",
                    this.ManyAttrValue[i].assistSku &&
                      this.ManyAttrValue[i].assistSku.assist_price
                      ? this.ManyAttrValue[i].assistSku.assist_price
                      : 0
                  );
                  this.$set(
                    this.ManyAttrValue[i],
                    "stock",
                    this.ManyAttrValue[i].assistSku
                      ? this.ManyAttrValue[i].assistSku.stock
                      : this.ManyAttrValue[i].old_stock
                  );
                  if (this.ManyAttrValue[i].assistSku) {
                    this.$nextTick(() => {
                      this.radio = i;
                      this.$refs.multipleTable.setCurrentRow(
                        this.ManyAttrValue[i]
                      );
                      this.formValidate.attrValue = [this.ManyAttrValue[i]];
                    });
                  }
                });
              }
              if (this.formValidate.is_good === 1) {
                this.checkboxGroup.push("is_good");
              }
              if (info.product.mer_form_id) {
                this.getFormList();
                this.getFormInfo();
              }
              this.fullscreenLoading = false;
              this.assist_date = [info.start_time, info.end_time];
              this.$store.dispatch("settings/setEdit", true);
              this.productCon();
            })
            .catch(res => {
              this.fullscreenLoading = false;
              this.$message.error(res.message);
            })
        : productDetailApi(id)
            .then(async res => {
              const info = res.data;
              this.formValidate = {
                type: info.type,
                product_id: info.product_id,
                image: info.image,
                slider_image: info.slider_image,
                store_name: info.store_name,
                store_info: info.store_info,
                assist_count: info.assist_count ? info.assist_count : 2,
                assist_user_count: info.assist_user_count
                  ? info.assist_user_count
                  : 1,
                start_time: info.start_time ? info.start_time + ":00" : "",
                end_time: info.end_time ? info.end_time + ":00" : "",
                brand_id: info.brand_id, // 品牌id
                pay_count: info.pay_count ? info.pay_count : 1,
                unit_name: info.unit_name,
                is_good: info.is_good,
                temp_id: mateName(
                  this.shippingList,
                  "shipping_template_id",
                  info.temp_id
                ),
                mer_form_id: info.mer_form_id,
                is_show: info.is_show,
                attr: info.attr,
                sort: info.sort ? info.sort : 0,
                extension_type: info.extension_type,
                content: info.content,
                spec_type: info.spec_type,
                is_gift_bag: info.is_gift_bag,
                delivery_way:
                  info.delivery_way && info.delivery_way.length
                    ? info.delivery_way.map(String)
                    : this.deliveryType,
                delivery_free: info.delivery_free ? info.delivery_free : 0,
                mer_labels:
                  info.mer_labels && info.mer_labels.length
                    ? info.mer_labels.map(Number)
                    : []
              };
              this.assist_date = "";
              if (this.formValidate.spec_type === 0) {
                this.OneattrValue = info.attrValue;
                this.OneattrValue.assist_price = this.OneattrValue.assist_price
                  ? this.OneattrValue.assist_price
                  : 0;
              } else {
                this.ManyAttrValue = info.attrValue;
                this.ManyAttrValue.forEach((val, i) => {
                  this.attrInfo[
                    Object.values(val.detail)
                      .sort()
                      .join("/")
                  ] = val;
                  this.$set(this.ManyAttrValue[i], "assist_price", 0);
                });
              }
              if (this.formValidate.is_good === 1) {
                this.checkboxGroup.push("is_good");
              }
              if (info.mer_form_id) {
                this.getFormList();
                this.getFormInfo();
              }
              this.fullscreenLoading = false;
              this.productCon();
            })
            .catch(res => {
              this.fullscreenLoading = false;
              this.$message.error(res.message);
            });
    },
    // 获取系统表单下拉数据
    getFormList() {
      associatedFormList()
        .then(res => {
          this.formList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取关联的表单信息
    getFormInfo() {
      associatedFormInfo(this.formValidate.mer_form_id)
        .then(res => {
          this.formData = res.data;
          let time = new Date().getTime() * 1000;
          let formUrl = `${
            this.baseURL
          }/pages/admin/system_form/index?inner_frame=1&time=${time}&form_id=${
            this.formValidate.mer_form_id
          }`;
          this.formUrl = formUrl;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 删除商品轮播图中的某一项
    handleRemove(i) {
      this.formValidate.slider_image.splice(i, 1);
    },
    // 点击商品图触发上传操作
    modalPicTap(tit, num, i) {
      const _this = this;
      const attr = [];
      this.$modalUpload(function(img) {
        if (tit === "1" && !num) {
          _this.formValidate.image = img[0];
          _this.OneattrValue[0].image = img[0];
        }
        if (tit === "2" && !num) {
          img.map(item => {
            attr.push(item.attachment_src);
            _this.formValidate.slider_image.push(item);
            if (_this.formValidate.slider_image.length > 10) {
              _this.formValidate.slider_image.length = 10;
            }
          });
        }
        if (tit === "1" && num === "dan") {
          _this.OneattrValue[0].image = img[0];
        }
        if (tit === "1" && num === "duo") {
          _this.ManyAttrValue[i].image = img[0];
        }
      }, tit);
    },
    // 点击上一步按钮
    handleSubmitUp() {
      if (this.currentTab-- < 0) this.currentTab = 0;
    },
    // 点击第一步的下一步按钮
    handleSubmitNest1(name) {
      if (!this.formValidate.image) {
        this.$message.warning("请选择商品！");
        return;
      } else {
        this.currentTab++;
        if (!this.$route.params.id) this.getInfo(this.product_id);
      }
    },
    // 点击第二步的下一步按钮
    handleSubmitNest2(name) {
      if (this.formValidate.spec_type === 0) {
        this.formValidate.attrValue = this.OneattrValue;
      }
      this.$refs[name].validate(valid => {
        if (valid) {
          if (
            !this.formValidate.store_name ||
            // !this.formValidate.store_info ||
            !this.formValidate.image ||
            !this.formValidate.slider_image
          ) {
            this.$message.warning("请填写完整商品信息！");
            return;
          }
          if (
            !this.formValidate.attrValue ||
            this.formValidate.attrValue.length === 0
          ) {
            this.$message.warning("请选择商品规格！");
            return;
          }
          this.currentTab++;
        }
      });
    },
    // 预览商品
    handlePreview(name) {
      assistPreviewApi(this.formValidate)
        .then(async res => {
          this.previewVisible = true;
          this.previewKey = res.data.preview_key;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          this.$store.dispatch("settings/setEdit", false);
          this.fullscreenLoading = true;
          this.loading = true;
          this.$route.params.id
            ? assistUpdateProApi(this.$route.params.id, this.formValidate)
                .then(async res => {
                  this.fullscreenLoading = false;
                  this.$message.success(res.message);
                  this.$router.push({
                    path: this.roterPre + "/marketing/assist/list"
                  });
                  this.$refs[name].resetFields();
                  this.formValidate.slider_image = [];
                  this.loading = false;
                })
                .catch(res => {
                  this.fullscreenLoading = false;
                  this.loading = false;
                  this.$message.error(res.message);
                })
            : assistCreateProApi(this.formValidate)
                .then(async res => {
                  this.fullscreenLoading = false;
                  this.$message.success(res.message);
                  this.$router.push({
                    path: this.roterPre + "/marketing/assist/list"
                  });
                  this.$refs[name].resetFields();
                  this.formValidate.slider_image = [];
                  this.loading = false;
                })
                .catch(res => {
                  this.fullscreenLoading = false;
                  this.loading = false;
                  this.$message.error(res.message);
                });
        } else {
          if (
            !this.formValidate.store_name ||
            !this.formValidate.store_info ||
            !this.formValidate.image ||
            !this.formValidate.slider_image
          ) {
            this.$message.warning("请填写完整商品信息！");
          }
        }
      });
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.warning(error);
      }
    },
    // 处理图片拖动开始事件
    handleDragStart(e, item) {
      this.dragging = item;
    },
    // 处理图片拖动结束事件
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    // 处理图片拖动经过事件
    handleDragOver(e) {
      e.dataTransfer.dropEffect = "move";
    },
    // 处理图片拖动进入事件
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = "move";
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.slider_image];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.slider_image = newItems;
    }
  }
};
</script>

<style lang="scss" scoped>
.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.1);
}
.priceBox ::v-deep input {
  padding-right: 0;
}
.sp100 {
  width: 100%;
}
.proCoupon ::v-deep .el-form-item__content {
  margin-top: 5px;
}
.tabPic {
  width: 40px !important;
  height: 40px !important;
  img {
    width: 100%;
    height: 100%;
  }
}
.noLeft ::v-deep .el-form-item__content {
  margin-left: 0 !important;
}
.tabNumWidth ::v-deep .el-input-number--medium {
  width: 121px !important;
}
.tabNumWidth ::v-deep .el-input-number__increase {
  width: 20px !important;
  font-size: 12px !important;
}
.tabNumWidth ::v-deep .el-input-number__decrease {
  width: 20px !important;
  font-size: 12px !important;
}
.tabNumWidth ::v-deep .el-input-number--medium .el-input__inner {
  padding-left: 25px !important;
  padding-right: 25px !important;
}
.tabNumWidth ::v-deep thead {
  line-height: normal !important;
}
.tabNumWidth ::v-deep .el-table .cell {
  line-height: normal !important;
}
.selWidthd {
  width: 350px;
}
.button-new-tag {
  height: 28px;
  line-height: 26px;
  padding-top: 0;
  padding-bottom: 0;
}
.input-new-tag {
  width: 90px;
  margin-left: 10px;
  vertical-align: bottom;
}
.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
  position: relative;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
}
.btndel {
  position: absolute;
  z-index: 1;
  width: 20px !important;
  height: 20px !important;
  left: 46px;
  top: -4px;
}
.labeltop ::v-deep .el-form-item__label {
  float: none !important;
  display: inline-block !important;
  margin-left: 120px !important;
  width: auto !important;
}
</style>

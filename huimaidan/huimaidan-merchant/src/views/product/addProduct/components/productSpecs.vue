<!-- 商品规格 -->
<template>
  <div>
    <el-row>
      <el-col :span="24">
        <el-form-item label="规格类型：" props="spec_type">
          <el-radio-group v-model="formValidate.spec_type">
            <el-radio :label="0" class="radio">单规格</el-radio>
            <el-radio :label="1">多规格</el-radio>
          </el-radio-group>
          <el-dropdown
            v-if="formValidate.spec_type == 1 && ruleList.length > 0"
            class="ml20"
            @command="confirm"
            trigger="hover"
          >
            <span class="el-dropdown-link">
              选择规格模板<i class="el-icon-arrow-down el-icon--right"></i
            ></span>
            <el-dropdown-menu slot="dropdown">
              <el-scrollbar style="max-height: 300px;overflow-y:scroll;">
                <el-dropdown-item
                  v-for="(item, index) in ruleList"
                  :key="index"
                  :command="item.attr_template_id"
                >
                  {{ item.template_name }}
                </el-dropdown-item>
              </el-scrollbar>
            </el-dropdown-menu>
          </el-dropdown>
        </el-form-item>
      </el-col>
      <!-- 规格设置 -->
      <el-col :span="24" v-if="formValidate.spec_type === 1" class="noForm">
        <el-form-item label="商品规格：" required>
          <div class="specifications">
            <draggable
              group="specifications"
              :disabled="attrs.length < 2"
              :list="attrs"
              handle=".move-icon"
              @end="onMoveSpec"
              animation="300"
            >
              <div
                class="specifications-item active"
                v-for="(item, index) in attrs"
                :key="index"
                @click="changeCurrentIndex(index)"
              >
                <div class="move-icon">
                  <span class="iconfont icondrag2"></span>
                </div>
                <i
                  class="del el-icon-error"
                  @click="handleRemoveRole(index, item.value)"
                ></i>
                <div class="specifications-item-box">
                  <div class="lineBox"></div>
                  <div class="specifications-item-name mb18">
                    <auto-width-input
                      size="small"
                      v-model="item.value"
                      placeholder="规格名称"
                      @change="attrChangeValue(index, item.value)"
                      @focus="handleFocus(item.value)"
                      maxlength="30"
                      show-word-limit
                    ></auto-width-input>
                    <el-checkbox
                      class="ml20"
                      v-model="item.add_pic"
                      :disabled="!item.add_pic && !canSel"
                      :true-label="1"
                      :false-label="0"
                      @change="e => addPic(e, index)"
                      >添加规格图</el-checkbox
                    >
                    <el-tooltip
                      class="item"
                      effect="dark"
                      content="添加规格图片, 仅支持打开一个(建议尺寸:800*800)"
                      placement="right"
                    >
                      <i class="el-icon-info"></i>
                    </el-tooltip>
                  </div>
                  <div class="rulesBox ml30">
                    <draggable
                      class="item"
                      :list="item.detail"
                      :disabled="item.detail.length < 2"
                      handle=".drag"
                      @end="onMoveSpec"
                    >
                      <div
                        v-for="(det, indexn) in item.detail"
                        :key="indexn"
                        class="mr10 spec drag"
                      >
                        <i
                          class="el-icon-error"
                          @click="handleRemove2(item.detail, indexn, det.value)"
                        ></i>
                        <auto-width-input
                          style="min-width:120px;"
                          size="small"
                          v-model="det.value"
                          placeholder="规格值"
                          @change="attrDetailChangeValue(det.value, index)"
                          @focus="handleFocus(det.value)"
                          maxlength="30"
                          @blur="handleBlur()"
                        >
                          <template slot="prefix">
                            <span class="iconfont icondrag2"></span>
                          </template>
                        </auto-width-input>
                        <div class="img-popover" v-if="item.add_pic">
                          <div class="popper-arrow"></div>
                          <div
                            class="popper"
                            @click="handleSelImg(det, index, indexn)"
                          >
                            <img class="img" v-if="det.pic" :src="det.pic" />
                            <i v-else class="el-icon-plus"></i>
                          </div>
                          <i
                            v-if="det.pic"
                            class="img-del el-icon-error"
                            @click="handleRemoveImg(det, index, indexn)"
                          ></i>
                        </div>
                      </div>
                      <div style="min-width: 210px;" :class="{ 'is-empty-sku': item.detail.length === 0 }">
                        <el-popover
                          :ref="'popoverRef_' + index"
                          placement=""
                          trigger="click"
                          @after-enter="handleShowPop(index)"
                        >
                          <auto-width-input
                            :ref="'inputRef_' + index"
                            size="small"
                            placeholder="请输入规格值"
                            v-model="formDynamic.attrsVal"
                            @enter="
                              createAttr(formDynamic.attrsVal, index)
                            "
                            @blur="createAttr(formDynamic.attrsVal, index)"
                            maxlength="30"
                            show-word-limit
                          >
                          </auto-width-input>
                          <div class="addfont" slot="reference">添加规格值</div>
                        </el-popover>
                      </div>
                    </draggable>
                  </div>
                </div>
              </div>
            </draggable>
            <el-button
              v-if="attrs.length < 4"
              size="small"
              type="text"
              @click="handleAddRole()"
              >添加新规格</el-button
            >
            <el-button
              v-if="attrs.length >= 1"
              size="small"
              type="text"
              @click="handleSaveAsTemplate()"
              >另存为模板</el-button
            >
          </div>
        </el-form-item>
      </el-col>
      <!-- 批量设置-->
      <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
        <!-- 单规格表格-->
        <el-form-item v-if="formValidate.spec_type === 0">
          <el-table :data="OneattrValue" border class="tabNumWidth" size="mini">
            <el-table-column align="center" label="图片" min-width="80">
              <template slot-scope="scope">
                <div
                  class="upLoadPicBox specPictrue"
                  @click="modalPicTap('1', 'dan')"
                >
                  <div v-if="formValidate.image" class="pictrue tabPic">
                    <img :src="scope.row.image" />
                  </div>
                  <div v-else class="upLoad tabPic">
                    <i class="el-icon-camera cameraIconfont" />
                  </div>
                </div>
              </template>
            </el-table-column>
            <el-table-column
              v-for="(item, iii) in attrValue"
              :key="iii"
              :label="formThead[iii] && formThead[iii].title"
              align="center"
              min-width="110"
            >
              <template slot-scope="scope">
                <div v-if="formValidate.svip_price_type != 0 && formThead[iii]">
                  <el-input
                    v-if="formThead[iii].title === '规格编码'"
                    v-model="scope.row[iii]"
                    type="text"
                    class="priceBox"
                  />
                  <el-input
                    v-if="formThead[iii].title === '条形码'"
                    v-model="scope.row[iii]"
                    type="text"
                    class="priceBox"
                  />
                  <el-input-number
                    v-if="
                      formThead[iii].title !== '付费会员价' &&
                        formThead[iii].title !== '规格编码' &&
                        formThead[iii].title !== '条形码' &&
                        formThead[iii].title !== '库存'
                    "
                    v-model="scope.row[iii]"
                    :min="0"
                    size="small"
                    @blur="memberPrice(formThead[iii], scope.row)"
                    class="priceBox"
                    controls-position="right"
                  />
                  <el-input
                    v-if="
                      formThead[iii].title === '库存' &&
                        (formValidate.type == 2 || formValidate.type == 3)
                    "
                    v-model="scope.row[iii]"
                    type="text"
                    size="small"
                    class="priceBox"
                    disabled
                  />
                  <el-input
                    v-else-if="
                      formThead[iii].title === '库存' &&
                        (formValidate.type !== 2 && formValidate.type !== 3)
                    "
                    v-model="scope.row[iii]"
                    type="text"
                    size="small"
                    class="priceBox"
                  />
                </div>
                <div v-else>
                  <el-input
                    v-if="formThead[iii].title === '规格编码'"
                    v-model="scope.row[iii]"
                    type="text"
                    size="small"
                    class="priceBox"
                  />
                  <el-input
                    v-else-if="formThead[iii].title === '条形码'"
                    v-model="scope.row[iii]"
                    type="text"
                    size="small"
                    class="priceBox"
                  />
                  <el-input-number
                    v-else
                    v-model="scope.row[iii]"
                    :min="0"
                    size="small"
                    :disabled="
                      formThead[iii].title === '库存' &&
                        (formValidate.type == 2 || formValidate.type == 3)
                    "
                    class="priceBox"
                    controls-position="right"
                  />
                </div>
              </template>
            </el-table-column>
            <template v-if="formValidate.type == 2">
              <el-table-column align="center" label="云盘设置" min-width="120">
                <template slot-scope="scope">
                  <el-button
                    v-if="
                      scope.row.cdkey &&
                        !scope.row.cdkey.list &&
                        !scope.row.stock
                    "
                    size="small"
                    @click="addVirtual(0, 0, 'OneattrValue')"
                    >添加链接</el-button
                  >
                  <span
                    v-else
                    class="seeCatMy"
                    @click="seeVirtual(0, OneattrValue[0], 'OneattrValue', 0)"
                    >已设置</span
                  >
                </template>
              </el-table-column>
            </template>
            <template v-if="formValidate.type == 3">
              <el-table-column align="center" label="卡密设置" min-width="140">
                <template slot-scope="scope">
                  <el-select
                    placeholder="请选择卡密库"
                    clearable
                    size="small"
                    v-model="scope.row.library_id"
                    @change="handleChange($event, scope.$index, 'OneattrValue')"
                  >
                    <el-option
                      :value="item.id"
                      v-for="(item, index) in cdkeyLibraryList"
                      :key="index"
                      :label="item.name"
                      :disabled="
                        !item.checkout &&
                          (item.product_id != product_id ||
                            (item.product_id == product_id &&
                              $route.query.type == 'copy'))
                      "
                    ></el-option>
                  </el-select>
                </template>
              </el-table-column>
            </template>
          </el-table>
        </el-form-item>
        <!-- 多规格表格-->
        <el-form-item
          v-if="formValidate.spec_type == 1"
          class="labeltop"
          label="规格列表："
        >
          <el-table
            :data="ManyAttrValue"
            style="width: 100%"
            :cell-class-name="tableCellClassName"
            :span-method="objectSpanMethod"
            border
            :key="tableKey"
            size="small"
          >
            <el-table-column
              v-for="(item, index) in formValidate.header"
              :key="index"
              :label="item.title"
              :min-width="item.minWidth || '100'"
              :fixed="item.fixed"
            >
              <template slot-scope="scope">
                <!-- 批量设置 -->
                <template v-if="scope.$index == 0">
                  <template v-if="item.key">
                    <div
                      v-if="
                        attrs.length &&
                          attrs[scope.column.index] &&
                          ManyAttrValue.length
                      "
                    >
                      <el-select
                        v-model="oneFormBatch[0][item.title]"
                        :placeholder="`请选择${item.title}`"
                        size="small"
                        clearable
                      >
                        <el-option
                          v-for="val in attrs[scope.column.index].detail"
                          :key="val.value"
                          :label="val.value"
                          :value="val.value"
                        >
                        </el-option>
                      </el-select>
                    </div>
                  </template>
                  <template v-else-if="item.slot === 'image'">
                    <div
                      class="upLoadPicBox specPictrue"
                      @click.stop="modalPicTap('1', 'pi', scope.$index)"
                    >
                      <div class="upLoad tabPic" v-if="oneFormBatch[0].image">
                        <img v-lazy="oneFormBatch[0].image" />
                        <i
                          class="el-icon-error btndel btnclose"
                          @click.stop="oneFormBatch[0].image = ''"
                        />
                      </div>
                      <div class="upLoad tabPic" v-else>
                        <i class="el-icon-camera cameraIconfont"></i>
                      </div>
                    </div>
                  </template>
                  <template v-else-if="item.slot === 'price'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].price"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'cost'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].cost"
                      :min="0"
                      :max="9999999999"
                      clearable
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'ot_price'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].ot_price"
                      :min="0"
                      :max="9999999999"
                      clearable
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'stock'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].stock"
                      :disabled="
                        formValidate.type == 3 || formValidate.type == 2
                      "
                      :min="0"
                      :max="9999999999"
                      clearable
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'fictitious'">
                    --
                  </template>
                  <template v-else-if="item.slot === 'bar_code'">
                    <el-input v-model="oneFormBatch[0].bar_code"></el-input>
                  </template>
                  <template v-else-if="item.slot === 'bar_code_number'">
                    <el-input
                      v-model="oneFormBatch[0].bar_code_number"
                    ></el-input>
                  </template>
                  <template v-else-if="item.slot === 'weight'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].weight"
                      :min="0"
                      :max="9999999999"
                      clearable
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'volume'">
                    <el-input-number
                      :controls="false"
                      v-model="oneFormBatch[0].volume"
                      :min="0"
                      :max="9999999999"
                      clearable
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'selected_spec'">
                    --
                  </template>
                  <template v-else-if="item.slot === 'action'">
                    <el-button type="text" size="mini" @click="batchAdd"
                      >批量修改</el-button
                    >
                    <el-button type="text" size="mini" @click="batchDel"
                      >清空</el-button
                    >
                  </template>
                </template>
                <template v-else>
                  <template v-if="item.key">
                    <div>
                      <span>{{ scope.row.detail[item.key] }}</span>
                    </div>
                  </template>
                  <template v-if="item.slot === 'image'">
                    <div
                      class="upLoadPicBox specPictrue"
                      @click="modalPicTap('1', 'duo', scope.$index)"
                    >
                      <div
                        class="upLoad tabPic"
                        v-if="scope.row.image || scope.row.pic"
                      >
                        <img :src="scope.row.image || scope.row.pic" />
                      </div>
                      <div class="upLoad tabPic" v-else>
                        <i
                          class="el-icon-camera cameraIconfont"
                          style="font-size: 24px"
                        ></i>
                      </div>
                    </div>
                  </template>
                  <template v-if="item.slot === 'price'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].price"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'cost'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].cost"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'ot_price'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].ot_price"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'stock'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].stock"
                      :disabled="
                        formValidate.type == 3 || formValidate.type == 2
                      "
                      :min="0"
                      :max="9999999999"
                      :precision="0"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'bar_code'">
                    <el-input
                      v-model="ManyAttrValue[scope.$index].bar_code"
                    ></el-input>
                  </template>
                  <template v-else-if="item.slot === 'bar_code_number'">
                    <el-input
                      v-model="ManyAttrValue[scope.$index].bar_code_number"
                    ></el-input>
                  </template>
                  <template v-else-if="item.slot === 'weight'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].weight"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template v-else-if="item.slot === 'volume'">
                    <el-input-number
                      :controls="false"
                      v-model="ManyAttrValue[scope.$index].volume"
                      :min="0"
                      :max="9999999999"
                      class="priceBox"
                    ></el-input-number>
                  </template>
                  <template
                    v-else-if="
                      item.slot === 'fictitious' && formValidate.type == 2
                    "
                  >
                    <el-button
                      v-if="
                        !scope.row.cdkey ||
                          (scope.row.cdkey &&
                            !scope.row.cdkey.list &&
                            !scope.row.stock)
                      "
                      size="small"
                      @click="addVirtual(0, scope.$index, 'ManyAttrValue')"
                      >添加链接</el-button
                    >
                    <span
                      v-else
                      class="seeCatMy"
                      @click="
                        seeVirtual(
                          0,
                          ManyAttrValue[scope.$index],
                          'ManyAttrValue',
                          scope.$index
                        )
                      "
                      >已设置</span
                    >
                  </template>
                  <template
                    v-else-if="
                      item.slot === 'fictitious' && formValidate.type == 3
                    "
                    slot-scope="scope"
                  >
                    <el-select
                      clearable
                      placeholder="请选择卡密库"
                      size="small"
                      v-model="scope.row.library_id"
                      @change="
                        handleChange($event, scope.$index, 'ManyAttrValue')
                      "
                      @visible-change="
                        getSelectedLiarbry(
                          ManyAttrValue[scope.$index],
                          ManyAttrValue
                        )
                      "
                    >
                      <el-option
                        :value="item.id"
                        v-for="(item, index) in cdkeyLibraryList"
                        :key="index"
                        :label="item.name"
                        :disabled="
                          (!item.checkout &&
                            (item.product_id != product_id ||
                              (item.product_id == product_id &&
                                $route.query.type == 'copy'))) ||
                            (selectedLibrary.length > 0 &&
                              selectedLibrary.indexOf(item.id) != -1)
                        "
                      ></el-option>
                    </el-select>
                  </template>
                  <template v-else-if="item.slot === 'selected_spec'">
                    <el-switch
                      v-model="ManyAttrValue[scope.$index].is_default_select"
                      :active-value="1"
                      :inactive-value="0"
                      @change="e => changeDefaultSelect(e, scope.$index)"
                    />
                  </template>
                  <template v-else-if="item.slot === 'action'">
                    <el-switch
                      class="defineSwitch"
                      v-model="ManyAttrValue[scope.$index].is_show"
                      active-text="显示"
                      inactive-text="隐藏"
                      :active-value="1"
                      :inactive-value="0"
                    />
                  </template>
                </template>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import vuedraggable from "vuedraggable";
import Sortable from "sortablejs";

import { templateLsitApi, attrCreatApi } from "@/api/product";

export default {
  props: {
    formValidate: {
      type: Object,
      default: () => ({
        spec_type: 0 // 默认值
      })
    },
    ManyAttrValue: {
      type: Array,
      default: () => []
    },
    changeAttrValue: {
      type: String,
      default: () => ""
    },
    attrValue: {
      type: Object,
      default: () => {}
    },
    formThead: {
      type: Object,
      default: () => {}
    },
    oneFormBatch: {
      type: Array,
      default: () => []
    },
    OneattrValue: {
      type: Array,
      default: () => []
    },
    formDynamic: {
      type: Object,
      default: () => ({})
    },
    product_id: {
      type: String,
      default: ""
    },
    attrs: {
      type: Array,
      default: () => []
    },
    cdkeyLibraryList: {
      type: Array,
      default: () => []
    },
    selectedLibrary: {
      type: Array,
      default: () => []
    }
  },
  components: {
    draggable: vuedraggable
  },
  data() {
    return {
      ruleList: [],
      timeCheckAll: [],
      reservationTime: [],
      timeCheckAllGroup: [], //自动划分当前选中的元素
      timeCheckAllClone: [], //自动划分克隆全部选中的元素
      timeDataClone: [], //自定义划分时的库存（为了切换时段划分时，可以复原之前选中的数据）
      canSel: true, // 规格图片添加判断
      tableKey: 0,
      selectRule: ""
    };
  },

  mounted() {
    this.productGetRule();
    this.showSpecsByType();
  },
  methods: {
    /**根据商品类型判断是否显示重量体积 */
    showSpecsByType() {
      if (this.formValidate.type == 2 || this.formValidate.type == 3) {
        delete this.attrValue.weight;
        delete this.attrValue.volume;
      } else {
        this.attrValue.weight = "";
        this.attrValue.volume = "";
      }
    },
    // 规格名称改变
    attrChangeValue(i, val) {
      this.$emit("attrChangeValue", i, val);
    },
    handleChange(event, index, name) {
      let result = this.cdkeyLibraryList.find(item => item.id === event);
      this.$set(
        this[name][index],
        "stock",
        event ? Number(result.total_num - result.used_num) : 0
      );
      if (name == "ManyAttrValue")
        this.getSelectedLiarbry(this[name][index], this.ManyAttrValue);
    },
    attrDetailChangeValue(val, i) {
      this.$emit("attrDetailChangeValue", val, i);
    },

    //添加云盘链接
    addVirtual(type, index, name) {
      this.$emit("addVirtual", type, index, name);
    },
    handleFocus(val) {
      this.$emit("handleFocus", val);
    },
    handleBlur() {
      this.$emit("handleBlur");
    },
    // 规格图片添加开关
    addPic(e, i) {
      if (e) {
        this.attrs.map((item, ii) => {
          if (ii !== i) {
            this.$set(item, "add_pic", 0);
          }
        });
        this.canSel = false;
      } else {
        this.canSel = true;
      }
    },
    handleShowPop(index) {
      this.$refs["inputRef_" + index][0].focus();
    },

    // 删除规格
    handleRemoveRole(index) {
      this.$emit("handleRemoveRole", index);
    },

    // 删除属性
    handleRemove2(item, index, val) {
      item.splice(index, 1);
      this.$emit("delAttrTable", val);
    },

    handleSelImg(item, index, indexn) {
      let that = this;
      this.$modalUpload(function(img) {
        item.pic = img[0];
        that.changeSpecImg([item.value], img[0], index, indexn);
      });
    },
    changeSpecImg(arr, img, index, indexn) {
      // 判断是否存在规格图
      let isHas = false;
      for (let i = 1; i < this.ManyAttrValue.length; i++) {
        let item = this.ManyAttrValue[i];
        if (item.image && this.isSubset(item.attr_arr, arr)) {
          isHas = true;
          break;
        }
      }
      if (isHas) {
        this.$confirm("可以同步修改下方该规格图片，确定要替换吗？", "提示", {
          confirmButtonText: "替换",
          cancelButtonText: "暂不",
          type: "warning"
        })
          .then(() => {
            for (let val of this.ManyAttrValue) {
              if (this.isSubset(val.attr_arr, arr)) {
                this.$set(val, "image", img);
              }
            }

            this.$emit("setAttrs", this.attrs);
          })
          .catch(() => {});
      } else {
        for (let val of this.ManyAttrValue) {
          if (this.isSubset(val.attr_arr, arr)) {
            this.$set(val, "image", img);
          }
        }
        this.$emit("setAttrs", this.attrs);
      }
    },

    isSubset(arr1, arr2) {
      // 将数组转换为 Set，以便进行高效的包含检查
      const set1 = new Set(arr1);
      const set2 = new Set(arr2);
      // 检查 set2 中的每个元素是否都在 set1 中
      for (let elem of set2) {
        if (!set1.has(elem)) {
          return false;
        }
      }
      return true;
    },
    // 切换默认选中规格
    changeDefaultSelect(e, index) {
      // 一个开启 其他关闭
      this.ManyAttrValue.map((item, i) => {
        if (i !== index) {
          item.is_default_select = 0;
        }
      });
      if (e) this.ManyAttrValue[index].is_show = 1;
    },

    // 添加属性
    createAttr(num, idx) {
      if (num) {
        // 判断是否存在同样熟悉
        var isExist = this.attrs[idx].detail.some(item => item.value === num);
        if (isExist) {
          this.$message.error("规格值已存在");
          return;
        }
        this.attrs[idx].detail.push({ value: num, image: "" });
        this.formValidate.attr = this.attrs;
        if (this.ManyAttrValue.length) {
          this.addOneAttr(this.attrs[idx].value, num);
        } else {
          this.$emit("generateAttr", this.attrs);
        }
        this.$refs["popoverRef_" + idx][0].doClose(); //关闭的
        this.clearAttr();
        setTimeout(() => {
          if (this.$refs["popoverRef_" + idx]) {
            //重点是以下两句
            this.$refs["popoverRef_" + idx][0].doShow(); //打开的
            //重点是以上两句
          }
        }, 20);
      } else {
        this.$refs["popoverRef_" + idx][0].doClose(); //关闭的
      }
      // 监听多规格值变化，在新增时候默认选中规格要自动默认第一个数据
      let exists = this.ManyAttrValue.some(item => item.is_default_select == 0);
      if (exists) {
        this.ManyAttrValue[1].is_default_select = 1;
      }
    },
    // 新增一条属性
    addOneAttr(val, val2) {
      this.$emit("generateAttr", this.attrs, val2);
    },
    handleRemoveImg(val, index, indexn) {
      this.$emit("delManyImg", val, index, indexn);
    },

    clearAttr() {
      this.formDynamic.attrsName = "";
      this.formDynamic.attrsVal = "";
    },

    // 清空批量规格信息
    batchDel() {
      this.$emit("batchDel");
    },
    // 生成列表 行 列 数据
    tableCellClassName({ row, column, rowIndex, columnIndex }) {
      //注意这里是解构
      //利用单元格的 className 的回调方法，给行列索引赋值
      row.index = rowIndex || "";
      column.index = columnIndex;
    },
    handleSaveAsTemplate() {
      this.$prompt("", "请输入模板名称", {
        confirmButtonText: "确定",
        cancelButtonText: "取消"
      })
        .then(({ value }) => {
          let template_value = this.attrs.map(item => {
            return {
              value: item.value,
              detail: item.detail.map(e => e.value)
            };
          });
          let formDynamic = {
            template_name: value,
            template_value: template_value
          };
          attrCreatApi(formDynamic, 0)
            .then(res => {
              this.$message.success(res.message);
              this.productGetRule();
            })
            .catch(res => {
              this.$message.error(res.message);
            });
        })
        .catch(() => {});
    },
    // 选择规格
    onChangeSpec(num) {
      if (num === 1) this.productGetRule();
    },
    changeCurrentIndex(i) {
      this.currentIndex = i;
    },
    // 获取商品属性模板；
    productGetRule() {
      templateLsitApi().then(res => {
        this.ruleList = res.data;
      });
    },
    // 新增规格
    handleAddRole() {
      this.$emit("handleAddRole");
    },

    // 批量添加
    batchAdd() {
      let arr = [];
      for (let val of this.attrs) {
        if (this.oneFormBatch[0][val.value]) {
          arr.push(this.oneFormBatch[0][val.value]);
        }
      }
      for (let val of this.ManyAttrValue) {
        if (arr.length) {
          if (this.isSubset(val.attr_arr, arr)) {
            if (this.oneFormBatch[0].image) {
              this.$set(val, "image", this.oneFormBatch[0].image);
            }
            if (
              this.oneFormBatch[0].price != undefined &&
              this.oneFormBatch[0].price != ""
            ) {
              this.$set(val, "price", this.oneFormBatch[0].price);
            }
            if (
              this.oneFormBatch[0].cost != undefined &&
              this.oneFormBatch[0].cost != ""
            ) {
              this.$set(val, "cost", this.oneFormBatch[0].cost);
            }
            if (
              this.oneFormBatch[0].ot_price != undefined &&
              this.oneFormBatch[0].ot_price != ""
            ) {
              this.$set(val, "ot_price", this.oneFormBatch[0].ot_price);
            }
            if (
              this.oneFormBatch[0].stock != undefined &&
              this.oneFormBatch[0].stock != ""
            ) {
              this.$set(val, "stock", this.oneFormBatch[0].stock);
            }
            if (
              this.oneFormBatch[0].bar_code != undefined &&
              this.oneFormBatch[0].bar_code != ""
            ) {
              this.$set(val, "bar_code", this.oneFormBatch[0].bar_code);
            }
            if (
              this.oneFormBatch[0].bar_code_number != undefined &&
              this.oneFormBatch[0].bar_code_number != ""
            ) {
              this.$set(
                val,
                "bar_code_number",
                this.oneFormBatch[0].bar_code_number
              );
            }
            if (
              this.oneFormBatch[0].weight != undefined &&
              this.oneFormBatch[0].weight != ""
            ) {
              this.$set(val, "weight", this.oneFormBatch[0].weight);
            }
            if (
              this.oneFormBatch[0].volume != undefined &&
              this.oneFormBatch[0].volume != ""
            ) {
              this.$set(val, "volume", this.oneFormBatch[0].volume);
            }
          }
        } else {
          if (this.oneFormBatch[0].image) {
            this.$set(val, "image", this.oneFormBatch[0].image);
          }
          if (
            this.oneFormBatch[0].price != undefined &&
            this.oneFormBatch[0].price != ""
          ) {
            this.$set(val, "price", this.oneFormBatch[0].price);
          }
          if (
            this.oneFormBatch[0].cost != undefined &&
            this.oneFormBatch[0].cost != ""
          ) {
            this.$set(val, "cost", this.oneFormBatch[0].cost);
          }
          if (
            this.oneFormBatch[0].ot_price != undefined &&
            this.oneFormBatch[0].ot_price != ""
          ) {
            this.$set(val, "ot_price", this.oneFormBatch[0].ot_price);
          }
          if (
            this.oneFormBatch[0].stock != undefined &&
            this.oneFormBatch[0].stock != ""
          ) {
            this.$set(val, "stock", this.oneFormBatch[0].stock);
          }
          if (
            this.oneFormBatch[0].weight != undefined &&
            this.oneFormBatch[0].weight != ""
          ) {
            this.$set(val, "weight", this.oneFormBatch[0].weight);
          }
          if (
            this.oneFormBatch[0].volume != undefined &&
            this.oneFormBatch[0].volume != ""
          ) {
            this.$set(val, "volume", this.oneFormBatch[0].volume);
          }
          if (
            this.oneFormBatch[0].bar_code != undefined &&
            this.oneFormBatch[0].bar_code != ""
          ) {
            this.$set(val, "bar_code", this.oneFormBatch[0].bar_code);
          }
          if (
            this.oneFormBatch[0].bar_code_number != undefined &&
            this.oneFormBatch[0].bar_code_number != ""
          ) {
            this.$set(
              val,
              "bar_code_number",
              this.oneFormBatch[0].bar_code_number
            );
          }
        }
      }
    },

    // 合并单元格
    objectSpanMethod({ row, column, rowIndex, columnIndex }) {
      if (columnIndex === 0 && rowIndex > 0) {
        let lable = column.label;
        //这里判断第几列需要合并
        const tagFamily = this.ManyAttrValue[rowIndex].detail[lable];
        const index = this.ManyAttrValue.findIndex((item, index) => {
          if (index > 0) return item.detail[lable] == tagFamily;
        });
        if (rowIndex == index) {
          let len = 1;
          for (let i = index + 1; i < this.ManyAttrValue.length; i++) {
            if (this.ManyAttrValue[i].detail[lable] !== tagFamily) {
              break;
            }
            len++;
          }
          return {
            rowspan: len,
            colspan: 1
          };
        } else {
          return {
            rowspan: 0,
            colspan: 0
          };
        }
      }
    },
    // 点击商品图
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
        if (tit === "1" && num === "pi") {
          _this.oneFormBatch[0].image = img[0];
        }
      }, tit);
    },
    // 规格拖拽排序后
    onMoveSpec() {
      this.$emit("generateAttr", this.attrs);
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
    seeVirtual(type, data, name, index) {
      this.$emit("seeVirtual", type, data, name, index);
    },

    getSelectedLiarbry(data, array) {
      this.$emit("getSelectedLiarbry", data, array);
    },

    changeCurrentIndex(i) {
      this.currentIndex = i;
    },
    // 选择属性确认
    confirm(name) {
      this.selectRule = name;
      this.createBnt = true;
      if (!this.selectRule) {
        return this.$message.warning("请选择属性");
      }
      this.ruleList.forEach(item => {
        if (item.attr_template_id === this.selectRule) {
          item.template_value.forEach((value, index) => {
            value.add_pic = 0;
          });
          this.canSel = true;
          this.$emit("setAttrs", [...item.template_value]);
          this.formValidate.attr = item.template_value;
        }
      });
      // this.$emit('generateAttr', this.attrs)
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
    }
  }
};
</script>
<style scoped lang="scss">
.drag {
  cursor: move;
}

.form-tip {
  font-size: 12px;
  color: #999999;
}

.add-time {
  color: var(--prev-color-primary);
  cursor: pointer;
}

.reservation-times-box {
  margin-top: 10px;
  padding: 10px 20px;
  width: 100%;
  background-color: #fafafa;
  border-radius: 10px;

  ::v-deep .el-checkbox__label {
    font-size: 13px;
  }
}

.acea-row {
  display: flex;
  flex-wrap: wrap;
  margin-top: 14px;
}

.flex-1 {
  flex: 1;
}

.customize-time {
  display: flex;
  flex-wrap: wrap;
  .relative {
    position: relative;
    &:hover .el-icon-error {
      visibility: visible;
    }
  }
  .el-icon-error {
    visibility: hidden;
    cursor: pointer;
    font-size: 15px;
    color: #999999;
    position: absolute;
    top: -5px;
    right: 6px;
  }
}

// 多规格设置
.specifications {
  .specifications-item:hover {
    background-color: var(--prev-color-primary-light-9);
  }

  .specifications-item:hover .del {
    display: block;
  }

  .specifications-item:last-child {
    margin-bottom: 14px;
  }

  .specifications-item {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20px 15px;
    transition: all 0.1s;
    background-color: #fafafa;
    margin-bottom: 10px;
    border-radius: 4px;

    .del {
      display: none;
      position: absolute;
      right: 15px;
      top: 15px;
      font-size: 22px;
      color: var(--prev-color-primary);
      cursor: pointer;
      z-index: 9;
    }

    .specifications-item-box {
      position: relative;

      .lineBox {
        position: absolute;
        left: 13px;
        top: 30px;
        width: 30px;
        height: 45px;
        border-bottom-left-radius: 6px;
        border-left: 1px solid #dcdfe6;
        border-bottom: 1px solid #dcdfe6;
      }

      .specifications-item-name {
        .el-icon-info {
          color: var(--prev-color-primary);
          font-size: 12px;
          margin-left: 5px;
        }
      }
    }
  }
}

.spec {
  display: block;
  margin: 5px 0;
  position: relative;

  .img-popover {
    cursor: pointer;
    width: 76px;
    height: 76px;
    padding: 6px;
    margin-top: 12px;
    background-color: #fff;
    position: relative;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover .img-del {
      display: block;
    }

    .img-del {
      display: none;
      position: absolute;
      right: 3px;
      top: 3px;
      font-size: 16px;
      color: var(--prev-color-primary);
      cursor: pointer;
      z-index: 9;
    }

    .popper {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 4px;
    }

    .popper-arrow,
    .popper-arrow:after {
      position: absolute;
      display: block;
      width: 0;
      height: 0;
      border-color: transparent;
      border-style: solid;
    }

    .popper-arrow {
      top: -13px;
      border-top-width: 0;
      border-bottom-color: #dcdfe6;
      border-width: 6px;
      filter: drop-shadow(0 2px 12px rgba(0, 0, 0, 0.03));

      &::after {
        top: -5px;
        margin-left: -6px;
        border-top-width: 0;
        border-bottom-color: #fff;
        content: " ";
        border-width: 6px;
      }
    }
  }

  .el-icon-error {
    position: absolute;
    display: none;
    right: -3px;
    top: -3px;
    z-index: 9;
    color: var(--prev-color-primary);
  }
}

.priceBox {
  width: 100%;
}

.tabPic {
  width: 40px !important;
  height: 40px !important;

  img {
    width: 100%;
    height: 100%;
  }
}

.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 15px;
  display: inline-block;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }

  .btndel {
    position: absolute;
    z-index: 1;
    width: 20px !important;
    height: 20px !important;
    left: 46px;
    top: -4px;
  }
}
.spec:hover {
  .el-icon-error {
    display: block;
    z-index: 999;
    cursor: pointer;
  }
}
.move-icon {
  width: 30px;
  cursor: move;
  margin-right: 10px;
}
.move-icon .icondrag2 {
  font-size: 26px;
  color: #bbb;
}

.btndel {
  position: absolute;
  z-index: 1;
  width: 20px !important;
  height: 20px !important;
  left: 46px;
  top: -4px;

  &.btnclose {
    left: auto;
    right: 0;
    top: 0;
  }
}

.addfont {
  display: inline-block;
  font-size: 12px;
  font-weight: 400;
  color: var(--prev-color-primary);
  margin-left: 14px;
  cursor: pointer;
}

.upLoadPicBox {
  position: relative;

  &.specPictrue {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .upLoad {
    -webkit-box-orient: vertical;
    -moz-box-orient: vertical;
    -o-box-orient: vertical;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    line-height: 20px;
  }

  span {
    font-size: 10px;
  }
}

.rulesBox {
  display: flex;
  flex-wrap: wrap;
  align-items: center;

  .item {
    display: flex;
    flex-wrap: wrap;
  }

  .addfont {
    margin-top: 5px;
    margin-left: 0px;
    width: 100px;
  }

  ::v-deep .el-popover {
    border: none;
    box-shadow: none;
    padding: 0;
    margin-top: 9px;
  }
}

.is-empty-sku {
  margin-left: 20px;
}
</style>

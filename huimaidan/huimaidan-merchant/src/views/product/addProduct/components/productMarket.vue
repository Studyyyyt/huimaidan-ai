<!-- 商品营销设置 -->
<template>
  <el-row>
    <el-col :span="24">
      <el-form-item label="商品推荐：">
        <el-checkbox
          v-model="formValidate.is_good"
          :true-label="1"
          :false-label="0"
          label="店铺推荐"
        />
        <div class="form-tip">
          设置后，该商品会在店铺中其他商品详情店铺推荐备选列表中展示
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="活动标签：">
        <label-list :list="activityLabelList" class="mr14" v-if="activityLabelList.length" />
        <el-button
          type="text"
          style="font-size: 12px;"
          @click="openActivityLabel"
          >设置活动标签</el-button
          >
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="关联推荐：">
        <div class="acea-row row-middle">
          <div class="acea-row">
            <div
              v-for="(item, index) in goodList"
              :key="`good-${index}`"
              class="pictrue"
            >
              <img :src="item.image" />
              <i class="el-icon-error btndel" @click="deleteRecommend(index)" />
            </div>
            <div class="uploadCont" v-if="goodList.length < 18">
              <div class="upLoadPicBox" @click="openRecommend">
                <div class="upLoad">
                  <i class="el-icon-camera cameraIconfont" />
                </div>
              </div>
            </div>
          </div>
          <el-popover
            placement="bottom"
            title=""
            min-width="200"
            trigger="hover"
          >
            <img
              :src="`${baseUrl}/static/images/store-recommend.png`"
              style="height:270px;"
              alt=""
            />
            <el-button
              type="text"
              slot="reference"
              style="font-size: 12px;"
              class="ml14"
              >查看示例</el-button
            >
          </el-popover>
        </div>
        <div class="form-tip">
          设置后，该商品详情页【店铺推荐】会展示选中的商品
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item v-if="deductionStatus > 0" label="积分抵扣：">
        <el-radio-group
          v-model="deduction"
          @change="changeIntergral(deduction)"
        >
          <el-radio :label="1" class="radio">单独设置</el-radio>
          <el-radio :label="-1">默认设置</el-radio>
        </el-radio-group>
        <div v-if="deduction == 1">
          <el-input-number
            v-model="formValidate.integral_rate"
            :min="0"
            size="small"
            controls-position="right"
            placeholder="请输入抵扣比例"
          />
          %
        </div>
        <span
          v-if="deduction == -1"
          class="form-tip"
          style="color: #F56464;"
          >（店铺统一设置，抵扣比例{{ deduction_ratio_rate }}%）</span
        >
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="商品赠券：" class="proCoupon">
        <div class="acea-row">
          <el-tag
            v-for="(tag, index) in formValidate.couponData"
            :key="index"
            class="mr10"
            closable
            :disable-transitions="false"
            @close="handleCloseCoupon(tag)"
            >{{ tag.title }}
          </el-tag>
          <el-button class="mr15" size="mini" @click="addCoupon"
            >选择优惠券</el-button
          >
          <div class="form-tip">设置购买该商品可默认赠送的优惠券</div>
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="开启礼包：">
        <el-radio-group
          v-model="formValidate.is_gift_bag"
          :disabled="$route.params.id ? true : false"
        >
          <el-radio :label="0" class="radio">否</el-radio>
          <el-radio :label="1">是</el-radio>
        </el-radio-group>
        <div class="form-tip">1. 选择开启礼包后，不可修改</div>
        <div class="form-tip">
          2.
          用户购买该分销礼包商品后，可自动成为分销员（即已成为分销员的用户在移动端看不到该分销礼包商品）
        </div>
        <div class="form-tip">
          3.
          该商品设置为分销礼包后会展示在平台后台的【分销】-【分销礼包】（即不会展示在平台后台-【商品列表】）
        </div>
      </el-form-item>
    </el-col>

    <el-col :span="24">
      <el-form-item
        v-if="extensionStatus > 0"
        label="佣金设置："
        props="extension_type"
      >
        <el-radio-group
          v-model="formValidate.extension_type"
          @change="onChangetype(formValidate.extension_type)"
        >
          <el-radio :label="1" class="radio">单独设置</el-radio>
          <el-radio :label="0">默认设置</el-radio>
        </el-radio-group>
        <span
          v-if="formValidate.extension_type == 0"
          class="form-tip"
          style="color: #F56464;"
          >（平台设置，一级佣金{{ extension_one_rate }}%，二级佣金{{
            extension_two_rate
          }}%）</span
        >
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item
        v-if="open_svip"
        label="付费会员价："
        props="svip_price_type"
      >
        <el-radio-group
          v-model="formValidate.svip_price_type"
          @change="onChangeSpecs(formValidate.svip_price_type)"
        >
          <el-radio :label="0" class="radio">不设置</el-radio>
          <el-radio :label="2">自定义设置</el-radio>
          <el-radio :label="1" class="radio">默认设置</el-radio>
        </el-radio-group>
        <span class="form-tip" style="color: #F56464;"
          >（店铺统一设置，{{ svip_rate * 10 }}折）</span
        >
      </el-form-item>
    </el-col>
    <el-col
      v-if="formValidate.spec_type === 1"
      :xl="24"
      :lg="24"
      :md="24"
      :sm="24"
      :xs="24"
    >
      <el-form-item
        v-if="
          (open_svip && formValidate.svip_price_type == 2) ||
            (extensionStatus > 0 && formValidate.extension_type == 1)
        "
        label="批量设置："
      >
        <div class="acea-row">
          <div
            v-if="open_svip && formValidate.svip_price_type == 2"
            class="mr15 acea-row row-middle"
          >
            <span>会员价：</span>
            <el-input-number
              v-model="manyVipPrice"
              :min="0"
              size="small"
              style="width:150px;"
              controls-position="right"
            />
          </div>
          <template
            v-if="extensionStatus > 0 && formValidate.extension_type == 1"
          >
            <div class="mr15">
              <span>一级返佣：</span>
              <el-input-number
                v-model="manyBrokerage"
                :min="0"
                size="small"
                class="input-number-with-text"
                controls-position="right"
              />
            </div>
            <div class="mr15">
              <span>二级返佣：</span>
              <el-input-number
                v-model="manyBrokerageTwo"
                :min="0"
                size="small"
                class="input-number-with-text"
                controls-position="right"
              />
            </div>
          </template>
          <div>
            <el-button type="primary" size="small" @click="batchSet"
              >批量设置</el-button
            >
          </div>
        </div>
      </el-form-item>
    </el-col>
    <el-col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
      <!-- 单规格表格-->
      <el-form-item v-if="formValidate.spec_type === 0">
        <el-table :data="OneattrValue" border class="tabNumWidth" size="mini">
          <el-table-column align="center" label="图片" min-width="80">
            <template slot-scope="scope">
              <div class="upLoadPicBoxspecPictrue">
                <div class="pictrue tabPic" v-if="scope.row.image">
                  <img :src="scope.row.image" />
                </div>
                <div v-else>
                  --
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column
            v-for="(item, iii) in specValue"
            :key="iii"
            :label="formThead[iii] && formThead[iii].title"
            align="center"
            min-width="110"
          >
            <template slot-scope="scope">
              <div>{{ scope.row[iii] }}</div>
            </template>
          </el-table-column>
          <template v-if="formValidate.svip_price_type != 0">
            <el-table-column align="center" label="付费会员价" min-width="120">
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.svip_price"
                  :min="0"
                  size="small"
                  :disabled="formValidate.svip_price_type == 1"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
          </template>
          <template v-if="formValidate.extension_type === 1">
            <el-table-column
              align="center"
              label="一级返佣(元)"
              min-width="120"
            >
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.extension_one"
                  :min="0"
                  size="small"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
            <el-table-column
              align="center"
              label="二级返佣(元)"
              min-width="120"
            >
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.extension_two"
                  :min="0"
                  size="small"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
          </template>
        </el-table>
      </el-form-item>
      <!-- 多规格表格-->
      <el-form-item
        v-if="formValidate.spec_type === 1"
        class="labeltop"
        label="规格列表："
      >
        <el-table
          ref="specsTable"
          :data="ManyAttrValue.slice(1)"
          border
          class="tabNumWidth"
          size="small"
          key="2"
        >
          <template v-if="manyTabDate">
            <el-table-column
              v-for="(item, iii) in manyTabDate"
              :key="iii"
              align="center"
              :label="manyTabTit[iii].title"
              min-width="80"
            >
              <template slot-scope="scope">
                <div>
                  <span>{{ scope.row.detail[manyTabTit[iii].title] }}</span>
                </div>
              </template>
            </el-table-column>
          </template>
          <el-table-column align="center" label="图片" min-width="80">
            <template slot-scope="scope">
              <div class="upLoadPicBox specPictrue">
                <div
                  class="pictrue tabPic"
                  v-if="scope.row.image || scope.row.pic"
                >
                  <img :src="scope.row.image || scope.row.pic" />
                </div>
                <div v-else>
                  --
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column
            v-for="(item, iii) in specValue"
            :key="iii"
            :label="formThead[iii].title"
            align="center"
            min-width="110"
          >
            <template slot-scope="scope">
              <div>{{ scope.row[iii] }}</div>
            </template>
          </el-table-column>
          <template v-if="formValidate.svip_price_type != 0">
            <el-table-column align="center" label="付费会员价" min-width="120">
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.svip_price"
                  :min="0"
                  size="small"
                  :disabled="formValidate.svip_price_type == 1"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
          </template>
          <template v-if="formValidate.extension_type === 1">
            <el-table-column
              key="1"
              align="center"
              label="一级返佣(元)"
              min-width="120"
            >
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.extension_one"
                  :min="0"
                  size="small"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
            <el-table-column
              key="2"
              align="center"
              label="二级返佣(元)"
              min-width="120"
            >
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row.extension_two"
                  :min="0"
                  size="small"
                  class="priceBox"
                  controls-position="right"
                />
              </template>
            </el-table-column>
          </template>
        </el-table>
      </el-form-item>
    </el-col>


    <!-- 选择活动标签弹窗 -->
    <el-dialog
      title="选择活动标签"
      :visible.sync="showActivityLabel"
      width="540px"
      :before-close="storeLabelClose"
    >
      <activity-label ref="storeLabelList"
        :selectedLabelIds="formValidate.activity_label_ids"
        @change="handleActivityLabelChange"
        @close="storeLabelClose"  />
    </el-dialog>
  </el-row>
</template>

<script>
import activityLabel from "@/components/activityLabel/index.vue";
import { labelOptionsApi } from "@/api/product";
import LabelList from "@/components/activityLabel/label-list.vue";

export default {
  name: "MarketingSettings",
  components: {
    activityLabel,
    LabelList
  },
  props: {
    formValidate: {
      type: Object,
      default: () => ({})
    },
    OneattrValue: {
      type: Array,
      default: () => []
    },
    ManyAttrValue: {
      type: Array,
      default: () => []
    },
    goodList: {
      type: Array,
      default: () => []
    },
    manyTabDate: {
      type: Object,
      default: () => ({})
    },
    specValue: {
      type: Object,
      default: () => ({})
    },
    open_svip: {
      type: Boolean,
      default: false
    },
    price: {
      type: Number,
      default: 0
    },
    svip_rate: {
      type: Number,
      default: 0
    },
    manyTabTit: {
      type: Object,
      default: () => ({})
    },

    formThead: {
      type: Object,
      default: () => ({})
    },

    extensionStatus: {
      type: Number,
      default: 0
    },
    deductionStatus: {
      type: Number,
      default: 0
    },
    deduction_set: {
      type: Number,
      default: -1
    },
    extension_one_rate: {
      type: String,
      default: ""
    },
    extension_two_rate: {
      type: String,
      default: ""
    },
    deduction_ratio_rate: {
      type: Number,
      default: 0
    },

    baseUrl: {
      type: String,
      required: ""
    }
  },
  data() {
    return {
      manyBrokerageTwo: 0,
      manyBrokerage: 0,
      keyNum: 0,
      manyVipPrice: "",
      deduction: this.deduction_set,

      showActivityLabel: false,

      activityLabelIdMap: {}
    };
  },
  created() {
    this.getActivityLabelList();
  },
  computed: {
    activityLabelList() {
      const list = [];
      this.formValidate.activity_label_ids.forEach(id => {
        if (this.activityLabelIdMap[id]) {
          list.push(this.activityLabelIdMap[id]);
        }
      });

      return list;
    }
  },
  methods: {
    getActivityLabelList() {
      labelOptionsApi()
        .then(res => {
          this.activityLabelIdMap = res.data.reduce((acc, item) => {
            if (item.children && item.children.length) {
              item.children.forEach(child => {
                acc[child.id] = child;
              });
            }

            return acc;
          }, {});
        });
    },
    handleActivityLabelChange(labelList) {
      this.storeLabelClose();
      this.formValidate.activity_label_ids = labelList.map(item => item.id);
    },
    storeLabelClose(){
      this.showActivityLabel = false;
    },
    openActivityLabel() {
      this.showActivityLabel = true;
    },
    // 选择店铺推荐商品
    openRecommend() {
      this.$emit("openRecommend");
    },
    // 删除店铺推荐商品
    deleteRecommend(index) {
      this.goodList.splice(index, 1);
    },
    addCoupon() {
      const _this = this;
      _this.formValidate.give_coupon_ids = [];
      _this.formValidate.couponData = [];
      this.$modalCoupon(
        this.formValidate.couponData,
        "wu",
        _this.formValidate.give_coupon_ids,
        (this.keyNum += 1),
        function(row) {
          _this.$set(_this.formValidate, "couponData", row);
          row.map(item => {
            _this.formValidate.give_coupon_ids.push(item.coupon_id);
          });
          _this.$forceUpdate();
        }
      );
    },
    handleCloseCoupon(tag) {
      this.formValidate.couponData.splice(
        this.formValidate.couponData.indexOf(tag),
        1
      );
      this.formValidate.give_coupon_ids = [];
      this.formValidate.couponData.map(item => {
        this.formValidate.give_coupon_ids.push(item.coupon_id);
      });
      this.$forceUpdate();
    },
    //设置会员价
    onChangeSpecs(item) {
      if (item == 1 || item == 2 && this.open_svip) {
        this.OneattrValue[0]['svip_price'] = this.OneattrValue[0]['price']
          ? this.accMul(this.OneattrValue[0]['price'], this.svip_rate)
          : 0
        let price = 0
        for (const val of this.ManyAttrValue) {
          price = val.price ? this.accMul(val.price, this.svip_rate) : 0
          this.$set(val, 'svip_price', price)
        }
      }
    },
    // 乘法
    accMul(arg1, arg2) {
      var max = 0
      var s1 = arg1.toString()
      var s2 = arg2.toString()
      try {
        max += s1.split('.')[1].length
      } catch (e) {}
      try {
        max += s2.split('.')[1].length
      } catch (e) {}
      return (
        (Number(s1.replace('.', '')) * Number(s2.replace('.', ''))) /
        Math.pow(10, max)
      )
    },
    // 批量设置
    batchSet() {
      for (let val of this.ManyAttrValue) {
        this.manyBrokerage != undefined &&
          this.$set(val, "extension_one", this.manyBrokerage);
        this.manyBrokerageTwo != undefined &&
          this.$set(val, "extension_two", this.manyBrokerageTwo);
        if (this.manyVipPrice != undefined) {
          this.$set(val, "svip_price", this.manyVipPrice);
        } else {
          this.$set(
            val,
            "svip_price",
            (val.price * (this.manyVipDiscount / 100)).toFixed(2)
          );
        }
      }
    },

    // 切换积分抵扣
    changeIntergral(e) {
      this.$emit('updateDeduction', e);   
    }
  }
};
</script>

<style scoped lang="scss">
.form-tip {
  font-size: 12px;
  color: #999999;
}
.pictrueBox {
  display: inline-block;
}
.pictrueTab {
  width: 40px !important;
  height: 40px !important;
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

.uploadCont {
  width: 60px;
  height: 60px;
  margin-right: 10px;
}
.upLoadPicBox {
  width: 60px;
  height: 60px;
  line-height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 2px;
  cursor: pointer;
}
.upLoad {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.02);
}
.cameraIconfont {
  font-size: 24px;
  color: #898989;
}
.ml14 {
  margin-left: 14px;
}
.proCoupon {
  margin-bottom: 20px;
}
.desc {
  color: #999;
}
.tabNumWidth ::v-deep .el-input-number--medium {
  width: 100px;
}
.tabNumWidth ::v-deep .el-input-number__increase {
  width: 20px !important;
  font-size: 12px !important;
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
.priceBox ::v-deep .el-input__inner {
  padding-right: 15px;
}
.el-input-number.is-controls-right .el-input__inner {
  padding-right: 15px !important;
}
.tabNumWidth ::v-deep .priceBox .el-input-number__decrease,
.tabNumWidth ::v-deep .priceBox .el-input-number__increase {
  display: none;
}
.tabNumWidth ::v-deep .priceBox.el-input-number--small {
  width: 90px !important;
}
.tabNumWidth ::v-deep .el-select {
  width: 120px;
}
.tabNumWidth ::v-deep thead {
  line-height: normal !important;
}
.tabNumWidth ::v-deep .cell {
  line-height: normal !important;
  text-overflow: clip !important;
}
.input-number-with-text {
  position: relative;
}
.input-number-with-text ::v-deep .el-input__inner {
  padding-right: 30px;
}
.input-number-with-text::after {
  content: "元";
  display: inline-block;
  color: #ccc;
  font-size: 13px;
  position: absolute;
  right: 10px;
  top: 2px;
}
</style>

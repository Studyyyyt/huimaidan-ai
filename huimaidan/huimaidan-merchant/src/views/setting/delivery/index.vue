<template>
  <div class="div-box">
    <el-card class="box-card">
      <!-- S 基本信息 -->
      <div class="title-box">
        <span class="title-text">基础信息</span>
      </div>
      <BasicForm v-if="isGetData" ref="basicForm" :basic-form="basicForm" />
      <!-- E 基本信息 -->

      <!-- S 配送费信息 -->
      <div class="title-box">
        <span class="title-text">配送费信息</span>
      </div>
      <!-- S 配送价格 -->
      <div v-if="isGetData">
        <el-form
          ref="costForm"
          :model="costForm"
          :rules="costRules"
          label-width="140px"
        >
          <div class="delivery-price-box">
            <div class="price-title-label">配送价格：</div>
            <div class="price-contents-box">
              <!-- 起送价 -->
              <el-form-item
                label="起送价"
                prop="min_delivery_amount"
                class="price-content-label"
              >
                <el-input
                  v-model="costForm.min_delivery_amount"
                  size="small"
                  placeholder="请输入金额"
                  class="price-input"
                >
                  <template slot="append"
                    >元</template
                  >
                </el-input>
                <span class="remarks price-remarks"
                  >订单中的商品在优惠前的总金额（不包含运费）低于起送价时，买家将不能下单</span
                >
              </el-form-item>
              <!-- 基础运费 -->
              <el-form-item
                label="基础运费"
                prop="base_shipping_fee"
                class="price-content-label"
              >
                <el-input
                  v-model="costForm.base_shipping_fee"
                  size="small"
                  placeholder="请输入金额"
                  class="price-input"
                >
                  <template slot="append"
                    >元</template
                  >
                </el-input>
                <span class="remarks price-remarks"
                  >商家未设置叠加溢价规则时，则所有订单统一运费标准</span
                >
              </el-form-item>
              <!-- 包邮金额 -->
              <el-form-item
                label="包邮金额"
                prop="free_shipping_amount"
                class="price-content-label"
              >
                <el-input
                  v-model="costForm.free_shipping_amount"
                  size="small"
                  placeholder="请输入金额"
                  class="price-input"
                >
                  <template slot="append"
                    >元</template
                  >
                </el-input>
                <span class="remarks price-remarks"
                  >订单实付满多少元，商家进行包邮</span
                >
              </el-form-item>
              <!-- S 叠加溢价部分 -->
              <el-form-item label="叠加溢价" class="price-content-label">
                <el-checkbox v-model="isOpenPremium" @change="changePremium"
                  >开启叠加溢价</el-checkbox
                >
                <el-tooltip class="item" effect="dark" placement="right">
                  <i class="iconfont-h5 icon-icon_question" />
                  <div slot="content">
                    多个叠加溢价规则互相叠加。例如:距离溢价设置5-8公里内，
                    <br />每增加2公里运费增加1元，3-5千克内，每增加1千克运费增加1元。
                    <br />则7公里4千克的订单，计算运费时基于基础运费之外，再额外增加溢价1+1=2元。
                  </div>
                </el-tooltip>
                <!-- S 距离阶梯溢价 -->
                <div v-if="isOpenPremium" class="premium-box">
                  <div class="mb-22">
                    距离阶梯价
                    <el-tooltip class="item" effect="dark" placement="right">
                      <i class="iconfont-h5 icon-icon_question" />
                      <div slot="content">
                        最多设置7个层级。增加距离不足阶梯公里数的，向上按足量计算。
                      </div>
                    </el-tooltip>
                  </div>
                  <el-input
                    v-model="
                      costForm.distance_premium_config.level_first.lt_distance
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >公里</template
                    >
                  </el-input>
                  <span>内，按基础运费计算</span>
                  <br />
                  <!-- 无阶梯层级时显示添加距离层级 -->
                  <div
                    v-show="
                      costForm.distance_premium_config.level_stairs.length == 0
                    "
                    class="mb-22"
                  >
                    <el-button type="text" @click="addDistanceLadder"
                      >添加距离层级</el-button
                    >
                  </div>
                  <!-- S 距离层级内容 -->
                  <div
                    v-for="(item, index) in costForm.distance_premium_config
                      .level_stairs"
                    :key="index"
                    class="ladder mb-22"
                  >
                    <InputNumberRange
                      :start-value.sync="item.start_distance"
                      :end-value.sync="item.end_distance"
                      right-text="公里"
                      class="width-220"
                      @update:startValue="updateStartValue($event, index, 0)"
                      @update:endValue="updateEndValue($event, index, 0)"
                    />
                    <span>内，每增加</span>
                    <el-input
                      v-model="
                        costForm.distance_premium_config.level_stairs[index]
                          .add_distance
                      "
                      size="small"
                      placeholder="请输入"
                      class="price-input"
                    >
                      <template slot="append"
                        >公里</template
                      >
                    </el-input>
                    <span>运费增加</span>
                    <el-input
                      v-model="
                        costForm.distance_premium_config.level_stairs[index]
                          .add_amount
                      "
                      size="small"
                      placeholder="请输入"
                      class="price-input"
                    >
                      <template slot="append"
                        >元</template
                      >
                    </el-input>
                    <i
                      class="iconfont-h5 icon-icon_ban"
                      @click="showDelDialog(index, 0)"
                    />
                    <div
                      v-show="
                        index ==
                          costForm.distance_premium_config.level_stairs.length -
                            1
                      "
                      class="add-btn"
                    >
                      <el-button type="text" @click="addDistanceLadder"
                        >+新增</el-button
                      >
                    </div>
                  </div>
                  <!-- E 距离层级内容 -->
                  <el-input
                    v-model="
                      costForm.distance_premium_config.level_last.gt_distance
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >公里</template
                    >
                  </el-input>
                  <span>外，每增加</span>
                  <el-input
                    v-model="
                      costForm.distance_premium_config.level_last.add_distance
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >公里</template
                    >
                  </el-input>
                  <span>运费增加</span>
                  <el-input
                    v-model="
                      costForm.distance_premium_config.level_last.add_amount
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >元</template
                    >
                  </el-input>
                </div>
                <!-- E 距离阶梯溢价 -->

                <!-- S 重量阶梯溢价 -->
                <div v-if="isOpenPremium" class="premium-box">
                  <div class="mb-22">
                    重量阶梯价
                    <el-tooltip class="item" effect="dark" placement="right">
                      <i class="iconfont-h5 icon-icon_question" />
                      <div slot="content">
                        最多设置7个层级。增加距离不足阶梯千克数的，向上按足量计算。
                      </div>
                    </el-tooltip>
                  </div>
                  <el-input
                    v-model="
                      costForm.weight_premium_config.level_first.lt_weight
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >千克</template
                    >
                  </el-input>
                  <span>内，按基础运费计算</span>
                  <br />
                  <!-- 无阶梯层级时显示添加距离层级 -->
                  <div
                    v-show="
                      costForm.weight_premium_config.level_stairs.length == 0
                    "
                    class="mb-22"
                  >
                    <el-button type="text" @click="addWeightLadder"
                      >添加重量层级</el-button
                    >
                  </div>
                  <!-- S 重量层级内容 -->
                  <div
                    v-for="(item, index) in costForm.weight_premium_config
                      .level_stairs"
                    :key="index"
                    class="ladder mb-22"
                  >
                    <InputNumberRange
                      :start-value.sync="item.start_weight"
                      :end-value.sync="item.end_weight"
                      right-text="千克"
                      class="width-220"
                      @update:startValue="updateStartValue($event, index, 1)"
                      @update:endValue="updateEndValue($event, index, 1)"
                    />
                    <span>内，每增加</span>
                    <el-input
                      v-model="
                        costForm.weight_premium_config.level_stairs[index]
                          .add_weight
                      "
                      size="small"
                      placeholder="请输入"
                      class="price-input"
                    >
                      <template slot="append"
                        >千克</template
                      >
                    </el-input>
                    <span>运费增加</span>
                    <el-input
                      v-model="
                        costForm.weight_premium_config.level_stairs[index]
                          .add_amount
                      "
                      size="small"
                      placeholder="请输入"
                      class="price-input"
                    >
                      <template slot="append"
                        >元</template
                      >
                    </el-input>
                    <i
                      class="iconfont-h5 icon-icon_ban"
                      @click="showDelDialog(index, 1)"
                    />
                    <div
                      v-show="
                        index ==
                          costForm.weight_premium_config.level_stairs.length - 1
                      "
                      class="add-btn"
                    >
                      <el-button type="text" @click="addWeightLadder"
                        >+新增</el-button
                      >
                    </div>
                  </div>
                  <!-- E 重量层级内容 -->
                  <el-input
                    v-model="
                      costForm.weight_premium_config.level_last.gt_weight
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >千克</template
                    >
                  </el-input>
                  <span>外，每增加</span>
                  <el-input
                    v-model="
                      costForm.weight_premium_config.level_last.add_weight
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >千克</template
                    >
                  </el-input>
                  <span>运费增加</span>
                  <el-input
                    v-model="
                      costForm.weight_premium_config.level_last.add_amount
                    "
                    size="small"
                    placeholder="请输入"
                    class="price-input mb-22"
                  >
                    <template slot="append"
                      >元</template
                    >
                  </el-input>
                </div>
                <!-- E 重量阶梯溢价 -->
              </el-form-item>
              <!-- E 叠加溢价部分 -->
            </div>
          </div>
        </el-form>
      </div>
      <!-- E 配送价格 -->
      <!-- 配送时间 -->
      <deliveryTime v-if="isGetData" ref="timeRef" :cost-form="costForm" :basic-form="basicForm" />
      <!-- E 配送费信息 -->
    </el-card>
    <el-card class="submit-card">
      <div>
        <el-button type="primary" @click="submit">提交</el-button>
      </div>
    </el-card>
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
import { deliverySetApi, deliveryUpdateApi } from "@/api/setting";

import BasicForm from "./basicForm.vue";
import DeliveryTime from "./deliveryTime.vue";

import InputNumberRange from "@/views/setting/delivery/components/inputNumberRange.vue";
import { checkInputNumber } from "@/utils/validate.js";

export default {
  name: "DeliverySettings",
  components: {
    BasicForm,
    DeliveryTime,
    InputNumberRange
  },
  data() {
    return {
      basicForm: {
        mer_delivery_type: [0, 1, 2],
      },
      costForm: {
        range_type: 1, // 配送范围类型
        fence: [], // 电子围栏区域
        min_delivery_amount: "",
        base_shipping_fee: "",
        free_shipping_amount: "",
        is_premium_stack_enabled: 0,
        delivery_time_type: 1,
        selectable_days: 7,
        commission_rate: 0,
        distance_premium_config: {
          level_first: {
            lt_distance: ""
          },
          level_stairs: [
            {
              start_distance: null,
              end_distance: null,
              add_distance: "",
              add_amount: ""
            }
          ],
          level_last: {
            gt_distance: "",
            add_distance: "",
            add_amount: ""
          }
        },
        weight_premium_config: {
          level_first: {
            lt_weight: ""
          },
          level_stairs: [
            {
              start_weight: null,
              end_weight: null,
              add_weight: "",
              add_amount: ""
            }
          ],
          level_last: {
            gt_weight: "",
            add_weight: "",
            add_amount: ""
          }
        }
      },
      isGetData: false, // 是否完成请求，获取数据
      isOpenPremium: true, // 是否开启叠加溢价
      costRules: {
        min_delivery_amount: [
          { required: true, message: `请输入起送价`, trigger: "blur" },
          { validator: checkInputNumber, trigger: "blur" }
        ],
        base_shipping_fee: [
          { required: true, message: `请输入基础运费`, trigger: "blur" },
          { validator: checkInputNumber, trigger: "blur" }
        ],
        free_shipping_amount: [
          { required: true, message: `请输入包邮规则`, trigger: "blur" },
          { validator: checkInputNumber, trigger: "blur" }
        ]
      }
    };
  },
  computed: {},
  created() {
    this.getForm();
  },
  methods: {
    // 获取配送设置数据
    getForm() {
      deliverySetApi()
        .then(res => {
          this.basicForm = res.data.basicSettings
          if (!Array.isArray(this.basicForm.mer_delivery_type)) {
            this.basicForm.mer_delivery_type = [];
          } else {
            this.basicForm.mer_delivery_type = this.basicForm.mer_delivery_type.map(Number);
          }
          // this.costForm = res.data.deliverySettings;
          if (!(res.data.deliverySettings instanceof Array)) {
            this.costForm = res.data.deliverySettings;
          }
          if (res.data.deliverySettings.is_premium_stack_enabled) {
            this.isOpenPremium = true;
          } else {
            this.isOpenPremium = false;
          }
          this.isGetData = true;
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    // 更新配送设置数据
    updateForm(id) {
      const data = { ...this.basicForm, ...this.costForm };
      deliveryUpdateApi(id, data)
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    },
    // 更新范围输入框组件的起始值
    updateStartValue(value, index, type) {
      if (type == 0) {
        this.costForm.distance_premium_config.level_stairs[
          index
        ].start_distance = value;
      } else {
        this.costForm.weight_premium_config.level_stairs[
          index
        ].start_distance = value;
      }
    },
    // 更新范围输入框组件的结束值
    updateEndValue(value, index, type) {
      if (type == 0) {
        this.costForm.distance_premium_config.level_stairs[
          index
        ].start_weight = value;
      } else {
        this.costForm.weight_premium_config.level_stairs[
          index
        ].end_weight = value;
      }
    },
    // 改变是否开启叠加溢价字段的值
    changePremium() {
      if (this.costForm.is_premium_stack_enabled) {
        this.costForm.is_premium_stack_enabled = 0;
      } else {
        this.costForm.is_premium_stack_enabled = 1;
      }
    },
    // 添加距离阶梯
    addDistanceLadder() {
      if (this.costForm.distance_premium_config.level_stairs.length <= 7) {
        this.costForm.distance_premium_config.level_stairs.push({
          start_distance: null,
          end_distance: null,
          add_distance: "",
          add_amount: ""
        });
      } else {
        this.$message({
          message: "阶梯层数最多为7",
          type: "warning"
        });
      }
    },
    // 添加重量阶梯
    addWeightLadder() {
      if (this.costForm.weight_premium_config.level_stairs.length <= 7) {
        this.costForm.weight_premium_config.level_stairs.push({
          start_weight: null,
          end_weight: null,
          add_weight: "",
          add_amount: ""
        });
      } else {
        this.$message({
          message: "阶梯层数最多为7",
          type: "warning"
        });
      }
    },
    // 展示删除距离阶梯的窗口
    showDelDialog(index, type) {
      this.$confirm("此操作将删除该层级, 是否继续?", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning"
      })
        .then(() => {
          if (type == 0) {
            this.delLadder(index, type);
          } else if (type == 1) {
            this.delLadder(index, type);
          }
          this.$message({
            type: "success",
            message: "删除成功!"
          });
        })
        .catch(() => {
          this.$message({
            type: "info",
            message: "已取消删除"
          });
        });
    },
    // 删除阶梯层级
    delLadder(index, type) {
      if (type == 0) {
        this.costForm.distance_premium_config.level_stairs.splice(index, 1); // 删除距离层级
      } else if (type == 1) {
        this.costForm.weight_premium_config.level_stairs.splice(index, 1); // 删除重量层级
      }
    },
    // 提交配送设置
    submit() {
      const id = this.costForm.delivery_config_id
        ? this.costForm.delivery_config_id
        : 0
      let costValid = true
      // 配送费用表单验证
      this.$refs.costForm.validate((valid, errData) => {
        if (valid) {
          // const id = this.costForm.delivery_config_id
          //   ? this.costForm.delivery_config_id
          //   : 0
          // this.updateForm(id)
          costValid = true
        } else {
          for (var item in errData) {
            this.$message({
              type: 'error',
              message: errData[item][0].message
            })
            costValid = false
            break
          }
        }
      })
      if (!costValid) return
      // 基础信息组件表单验证
      const basicValid = this.$refs.basicForm.submit()
      if (!basicValid) return
      // 配送时间组件表单验证
      const timeValid = this.$refs.timeRef.submit()
      if (!timeValid) return
      this.updateForm(id)
    }
  }
};
</script>

<style scoped lang="scss">
@import "@/styles/form.scss";
.div-box {
  margin-bottom: 80px;
}
.title-box {
  height: 18px;
  display: flex;
  align-items: center;
  margin-bottom: 22px;
  .title-text {
    display: flex;
    align-items: center;
  }
  .title-text::before {
    display: block;
    content: "";
    width: 2px;
    height: 14px;
    background-color: #4073fa;
    margin-right: 5px;
  }
}
.remarks {
  color: #a4a4a4;
  font-size: 12px;
}
.delivery-price-box {
  display: flex;
  .price-title-label {
    width: 140px;
    text-align: end;
    padding-right: 12px;
    flex-shrink: 0;
  }
  .price-contents-box {
    width: 1000px;
    background-color: #f5f7fa;
    padding: 20px;
    flex-shrink: 0;
    .price-content-label {
      .price-remarks {
        margin-left: 10px;
      }
      ::v-deep .el-form-item__label {
        width: 80px !important;
      }
      ::v-deep .el-form-item__content {
        margin-left: 80px !important;
      }
      .price-input {
        width: 150px;
      }

      .premium-box {
        background-color: #ffffff;
        padding: 20px;
        margin-bottom: 10px;
        border-radius: 5px;
        span {
          margin: 0 10px;
        }
        .ladder {
          display: flex;
          align-items: center;
          .icon-icon_ban {
            margin: 0 20px;
          }
          .icon-icon_ban:hover {
            color: #4073fa;
            cursor: pointer;
          }
          .add-btn {
            height: 20px;
            line-height: 20px;
            padding: 0 20px;
            border-left: 2px solid #eeeeee;
            ::v-deep .el-button {
              padding: 0;
            }
          }
        }
      }
      .icon-icon_question {
        color: #b6babe;
        font-size: 13px;
      }
    }
  }
}
.submit-card {
  display: flex;
  position: fixed;
  z-index: 2;
  width: 100%;
  bottom: 0;
  right: 0;
  justify-content: center;
  align-items: center;
}
.mb-22 {
  margin-bottom: 22px;
}
.width-220 {
  width: 220px;
}
</style>

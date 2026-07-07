<template>
  <div class="divBox">
    <el-card class="box-card">
      <el-row class="acea-row row-middle row-center">
        <el-col :span="24">
          <el-form
            class="form"
            ref="formValidate"
            :rules="ruleValidate"
            :model="formValidate"
            label-width="100px"
            @submit.native.prevent
          >
            <el-row v-show="current === 0">
              <el-col :span="24">
                <el-form-item label="套餐名称：" prop="title">
                  <el-input placeholder="请输入套餐名称" size="small" v-model="formValidate.title"/>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item
                  label="套餐时间："
                  :prop="formValidate.is_time == 1 ? 'time' : ''"
                >
                  <el-radio-group
                    element-id="is_time"
                    v-model="formValidate.is_time"
                  >
                    <el-radio :label="0">不限时</el-radio>
                    <el-radio :label="1" class="radio">限时</el-radio>
                  </el-radio-group>
                  <div
                    class="acea-row row-middle"
                    v-if="formValidate.is_time == 1"
                    style="display: inline-block; margin-left: 15px;"
                  >
                    <el-date-picker
                      v-model="timeVal"
                       type="datetimerange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        align="right"
                        @change="onchangeTime"
                      />
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="套餐类型：" prop="type" label-for="type">
                  <el-radio-group element-id="type" v-model="formValidate.type">
                    <el-radio :label="0" class="radio">固定套餐</el-radio>
                    <el-radio :label="1">搭配套餐</el-radio>
                  </el-radio-group>
                  <div class="ml100 grey">
                    {{
                      formValidate.type == 0
                        ? "套餐内所有商品打包销售，消费者需成套购买整个套餐"
                        : "套餐内主商品必选，搭配商品任意选择1件及以上即可购买套餐"
                    }}
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24" v-if="formValidate.type == 1">
                <el-form-item
                  label="套餐主商品："
                  prop="products"
                  label-for="products"
                >
                  <el-table
                    :data="specsMainData"
                    size="small"
                    class="table"
                  >
                   <el-table-column prop="store_name"
                      label="商品名称"
                      min-width="260">
                    <template slot-scope="scope">
                      <div class="product-data">
                        <img class="image" :src="scope.row.image" />
                        <div>{{ scope.row.store_name }}</div>
                      </div>
                    </template>
                    </el-table-column>
                    <el-table-column label="参与规格" min-width="150">
                    <template slot-scope="scope">
                      <div v-for="(item, index) in scope.row.attr" :key="index">
                        {{ item.sku || '默认' }} | {{ item.active_price || item.price }}
                      </div>
                    </template>
                    </el-table-column>
                    <el-table-column label="操作" min-width="100">
                      <template slot-scope="scope">
                        <el-button type="text" size="small" @click="editGoods(scope.row, scope.$index, 'Main')">设置规格</el-button>
                        <el-button type="text" size="small" @click="deleteGoods(scope.$index, 'Main')">删除</el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                  <el-button
                    v-if="specsMainData.length < 1"
                    type="primary"
                    size="small"
                    class="mt20"
                    @click="addGoods('Main')"
                    >添加商品</el-button>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item
                  :label="
                    formValidate.type == 1 ? '套餐搭配商品：' : '套餐商品：'
                  "
                  prop="products"
                  label-for="products"
                >
                  <el-table
                    :data="specsData"
                    size="small"
                    class="table"
                  >
                    <el-table-column
                      prop="store_name"
                      label="商品名称"
                      min-width="260"
                    >
                    <template slot-scope="scope">
                      <div class="product-data">
                        <img class="image" :src="scope.row.image" />
                        <div>{{ scope.row.store_name }}</div>
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column label="参与规格" min-width="150">
                      <template slot-scope="scope">
                        <div v-for="(item, index) in scope.row.attr" :key="index">
                          {{ item.sku || '默认' }} | {{ item.active_price || item.price }}
                        </div>
                    </template>
                    </el-table-column>
                  <el-table-column label="操作" min-width="100">
                      <template slot-scope="scope">
                        <el-button type="text" size="small" @click="editGoods(scope.row, scope.$index, 'Other')">设置规格</el-button>

                        <el-button type="text" size="small" @click="deleteGoods(scope.$index, 'Other')">删除</el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                  <el-button
                    v-if="specsData.length < 50"
                    type="primary"
                    size="small"
                    class="mr15 mt20"
                    @click="addGoods('Other')"
                    >添加商品</el-button>
                </el-form-item>
              </el-col>
              <el-col>
                <el-form-item
                  label="套餐数量："
                  :prop="formValidate.is_limit == 1 ? 'limit_num' : ''"
                  label-for="limit_num"
                >
                  <el-radio-group
                    element-id="is_limit"
                    v-model="formValidate.is_limit"
                  >
                    <el-radio :label="0">不限量</el-radio>
                    <el-radio :label="1" class="radio">限量</el-radio>
                  </el-radio-group>
                  <el-input-number
                    v-if="formValidate.is_limit == 1"
                    placeholder="请输入限量数量"
                    v-model="formValidate.limit_num"
                    :min="0"
                    :max="99999"
                    :precision="0"
                    style="margin-left: 15px;"
                  ></el-input-number>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="排序：" label-for="sort">
                  <el-input-number
                    placeholder="请输入排序序号"
                    v-model="formValidate.sort"
                    :min="0"
                    :max="999999"
                    :precision="0"
                    size="small"
                  ></el-input-number>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item
                  label="套餐包邮："
                  prop="free_shipping"
                  label-for="status"
                >
                  <el-switch
                    v-model="formValidate.free_shipping"
                    :active-value="1"
                    :inactive-value="0"
                    active-text="是"
                    inactive-text="否"
                  />
                  <div class="ml100 grey">
                    不包邮时，将按照商品的运费模板进行计算
                  </div>
                </el-form-item>
              </el-col>
              <el-col :span="24">
                <el-form-item label="上架状态：" prop="is_show">
                  <el-switch
                    v-model="formValidate.is_show"
                    :active-value="1"
                    :inactive-value="0"
                    :width="55"
                    active-text="上架"
                    inactive-text="下架"
                  />
                </el-form-item>
              </el-col>
            </el-row>
            <el-form-item>
              <el-button
                type="primary"
                :loading="submitOpen"
                @click="next('formValidate')"
              >
                <div v-if="!submitOpen">提交</div>
                <div v-else>提交中</div>
              </el-button>
            </el-form-item>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
    <el-dialog
      :visible.sync="modals"
      title="商品列表"
      class="paymentFooter"
      width="900px"
    >
      <goods-list
        ref="goodslist"
        v-if="modals"
        :ischeckbox="true"
        :isDiscount="true"
        @getProductId="getProductId"
        @close="closeModals"
      ></goods-list>
    </el-dialog>
    <el-dialog
      :visible.sync="ggModel"
      title="规格设置"
      class="paymentFooter"
      width="800px"
    >
      <div class="df mt15">
        <el-form inline>
          <el-form-item label="优惠价：">
            <el-input-number
              v-model="rate_price"
              :min="0"
            ></el-input-number>
            <el-button size="small" type="primary" @click="changeRatePrice">批量添加</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-table
        ref="multipleSelection"
        :data="manyFormValidate"
        :row-key="(row) => { return row.unique }"
        @selection-change="selectOne"
        height="420"
        size="small"
      >
        <el-table-column type="selection" :reserve-selection="true" width="80" />
        <el-table-column label="图片" width="100">
           <template slot-scope="scope">
              <div class="demo-image__preview">
              <el-image :src="scope.row.image" :preview-src-list="[scope.row.image]" />
            </div>
            </template>
        </el-table-column>
        <el-table-column label="规格" prop="sku" width="200">
           <template slot-scope="scope">
              <span>{{scope.row.sku || '默认'}}</span>
          </template>
        </el-table-column>
        <el-table-column label="售价" prop="price" width="100"/>
        <el-table-column label="会员价" prop="is_svip_price" width="100"/>
        <el-table-column align="center" label="优惠价" min-width="100">
          <template slot-scope="scope">
            <el-input-number v-model="scope.row['active_price']" :controls="false" :precision="2" style="width:100px" :min="0" :max="Number(scope.row['price'])" @blur="limitPrice(scope.row)" />
          </template>
        </el-table-column>
      </el-table>
      <el-button class="mt10" type="primary" @click="getAttr" style="width: 100%;">
        <div>提交</div>
      </el-button>
    </el-dialog>
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

import goodsList from "@/components/goodsList/index";
import { roterPre } from '@/settings'
import { discountsSave, discountsGetDetails, discountsUpdate } from "@/api/marketing"; //详情 创建 编辑
import { discountsFrom } from "./formRule/discountsFrom";
import { productAttrsApi } from "@/api/marketing";
import { formatDate } from "@/utils/validate";

export default {
  name: "lotteryCreate",
  components: { goodsList },
  data() {
    return {
      roterPre: roterPre,
      ggModel: false,
      modals: false,
      loading: false,
      timeVal: '',
      manyFormValidate: [],
      multipleSelection: [],
      submitOpen: false,
      spinShow: false,
      addGoodsModel: false,
      isChoice: "单选",
      current: 0,
      modalPic: false,
      modal_loading: false,
      images: [],
      goodsAddType: "",
      specsMainData: [],
      specsData: [],
      formValidate: {
        title: "", //套餐名称
        type: 0, //套餐类型
        is_time: 0, //是否限时
        is_limit: 0, //限量1/不限量0
        limit_num: 0, //限量
        link_ids: [], //参与用户标签
        time: [], //套餐时间
        sort: 0, //排序
        free_shipping: 1, //是否包邮
        status: 1,
        products: [],
      },
      ruleValidate: discountsFrom,
      currentid: "",
      picTit: "",
      tableIndex: 0,
      copy: 0,
      editIndex: null,
      id: "",
      rate_price: 0,
    };
  },
  computed: {

  },
  mounted() {
    if (this.$route.params.id) {
      this.setTagsViewTitle()
      this.id = parseInt(this.$route.params.id);
      this.current = 0;
      this.copy = this.$route.params.copy || 0;
      this.getInfo();
    }
  },
  methods: {
     setTagsViewTitle() {
      const title = '编辑套餐'
      const route = Object.assign({}, this.tempRoute, {
        title: `${title}-${this.$route.params.id}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    //勾选规格
    selectOne(data) {
      this.multipleSelection = []
      data.forEach((v,i) =>{
        if(v.product_id == this.product_id){
          this.multipleSelection.push(v)
        }
      })
    },
    //提交选中规格
    getAttr() {
      if (!this.multipleSelection.length)
        return this.$message.warning("请先选择规格");
      let type;
      this.goodsAddType === "Main"
        ? (type = this.specsMainData)
        : (type = this.specsData);
      this.$set(type[this.tabIndex], "attr", this.multipleSelection);
      let items = [];
       for (let i = 0; i < this.multipleSelection.length; i++) {
         if(this.multipleSelection[i]['active_price'] == '' || !this.multipleSelection[i]['active_price']){
            return this.$message.warning("请填写优惠价格");
         }else{
           items.push({active_price: this.multipleSelection[i]['active_price'],unique: this.multipleSelection[i]['unique']})
         }
        }
      this.$set(type[this.tabIndex],"items", items);
      this.ggModel = false;
    },
    limitPrice(row) {
      if (row.active_price - row.price > 0)row.active_price = row.price
    },
    //修改优惠价格
    changeRatePrice() {
      if (!this.multipleSelection.length) {
        return this.$message.warning("请先选中规格");
      }
      this.multipleSelection.forEach(item => {
        this.$set(item, "active_price", this.rate_price);
      });
    },
    // 规格列表
    generate(id, type) {
      this.product_id = id
      productAttrsApi(id)
        .then((res) => {
          this.ggModel = true;
          let info = JSON.parse(JSON.stringify(res.data));
          let data =
            this.goodsAddType === "Main"
              ? this.specsMainData[this.tabIndex].attr || []
              : this.specsData[this.tabIndex].attr || [];

          const selectProductMap = data.reduce((acc, item) => {
            acc[item.unique] = item;
            return acc;
          }, {});

          const selectProduct = [];

          for (const item of info) {
            const selectItem = selectProductMap[item.unique];
            if (selectItem) {
              item.active_price = selectItem.productSku && selectItem.productSku.active_price || selectItem.active_price;
              selectProduct.push(item);
            }
          }

          this.manyFormValidate = info;

          this.$nextTick(() => {
            selectProduct.forEach((row) => {
              this.$refs.multipleSelection.toggleRowSelection(row, true)
            })
            this.multipleSelection = selectProduct
          })
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 选择的商品
    getProductId(productList) {
      if (
        this.goodsAddType === "Main" &&
        (productList.length > 1 || this.specsMainData.length > 0)
      ) {
        this.$message.warning("最多添加一个商品");
        return;
      }
      let pData = JSON.parse(JSON.stringify(productList));
      for (let i = 0; i < pData.length; i++) {
        pData[i].attr = [];
      }
      this.$nextTick((e) => {
        if (this.goodsAddType === "Main") {
          let arr = this.concat_arr(this.specsData, pData);
          if (arr.length) {
            return this.$message.warning("已存在搭配商品");
          }
          this.specsMainData = this.specsMainData.concat(pData);
        } else {
          let arr = this.concat_arr(this.specsData, pData);
          let mainArr = this.concat_arr(this.specsMainData, pData);
          if (arr.length || mainArr.length) {
            return this.$message.warning("已存在相同主商品/搭配商品");
          }
          this.specsData = this.specsData.concat(pData);
        }
        this.modals = false;
      });
    },
    closeModals(){
      this.modals = false;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.formValidate.time[0] = e ? this.moment(e[0]).format('YYYY-MM-DD HH:mm:ss') : ''
      this.formValidate.time[1] = e ? this.moment(e[1]).format('YYYY-MM-DD HH:mm:ss') : ''
    },
    // 详情
    getInfo() {
      discountsGetDetails(this.id).then((res) => {
        this.formValidate = res.data;
        this.timeVal =  [
          new Date(this.formValidate.time[0]),
          new Date(this.formValidate.time[1])
        ];

        res.data.discountsProduct.forEach(product => {
          const attr = [];
          const items = [];
          const attrValue = product.product && product.product.attrValue || [];
          attrValue.forEach(attrItem => {
            if (attrItem.productSku) {
              attr.push({
                ...attrItem,
                active_price: attrItem.productSku.active_price
              });
              items.push({
                active_price: attrItem.productSku.active_price,
                unique: attrItem.unique
              });
            }
          });

          if (product.type === 1) {
            this.specsMainData.push({
              ...product,
              attr,
              items
            });
          } else {
            this.specsData.push({
              ...product,
              attr,
              items
            });
          }
        });
      });
    },
    // 下一步
    next(name) {
      if (this.formValidate.type === 1 && !this.specsMainData.length) {
        return this.$message.warning("请选择主商品");
      } else if (this.formValidate.type === 0 && !this.specsData.length) {
        return this.$message.warning("请选择套餐商品");
      }
      this.specsMainData.forEach((v) => {
        v.type = 1;
      });
      this.specsData.forEach((v) => {
        v.type = 0;
      });
      let productData;
      if (this.formValidate.type == 1) {
        productData = this.specsMainData.concat(this.specsData);
      } else {
        productData = this.specsData;
      }
      if (productData.length < 2) {
        return this.$message.warning("套餐内商品不能少于2个");
      }
      for (let i = 0; i < productData.length; i++) {
        productData[i].temp_id = 0
        const element = productData[i];
        if (!element.items) {
          return this.$message.warning(`请选择 ${element.store_name} 的规格`);
        }
      }
      this.formValidate.products = productData;
      this.formValidate.temp_id = 0;
      if (this.submitOpen) return false;
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.submitOpen = true;
          if (this.id && !this.copy) {
            discountsUpdate(this.id,this.formValidate)
              .then(async (res) => {
                this.$message.success(res.message);
                setTimeout(() => {
                  this.submitOpen = false;
                  this.$router.push({
                    path: `${roterPre}/marketing/discounts/list`,
                  });
                }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$message.error(res.message);
              });
          } else {
            discountsSave(this.formValidate)
              .then(async (res) => {
                this.$message.success(res.message);
                setTimeout(() => {
                  this.submitOpen = false;
                  this.$router.push({
                    path: `${roterPre}/marketing/discounts/list`,
                  });
                }, 500);
              })
              .catch((res) => {
                this.submitOpen = false;
                this.$message.error(res.message);
              });
          }
        } else {
          return false;
        }
      });
    },
    concat_arr(arr1, arr2) {
      let result = [];
      for (var i = 0; i < arr1.length; i++) {
        var obj = arr1[i];
        var num = obj.product_id;
        var flag = false;
        for (var j = 0; j < arr2.length; j++) {
          var aj = arr2[j];
          var n = aj.product_id;
          if (n == num) {
            flag = true;
            break;
          }
        }
        if (flag) {
          result.push(obj);
        }
      }
      return result;
    },
    // 点击商品图
    modalPicTap(tit, num, i) {
     const _this = this;
     this.$modalUpload(function(img) {
        if(tit == "1" && !num) {
          _this.formValidate.image = img[0];
        }
      }, tit);
    },
    handleRemove() {
      this.formValidate.image = "";
    },
    // 表单验证
    validate(prop, status, error) {
      if (status === false) {
        this.$message.error(error);
        return false;
      } else {
        return true;
      }
    },
    //新增商品
    addGoods(type) {
      this.goodsAddType = type;
      this.modals = true;
    },
    //编辑设置规格
    editGoods(row, index, type) {
      this.goodsAddType = type;
      this.tabIndex = index;
      this.rate_price = 0;
      this.generate(row.product_id, 0);
    },
    //删除商品
    deleteGoods(index, type) {
      if (type === "Main") {
        this.specsMainData.splice(index, 1);
      } else {
        this.specsData.splice(index, 1);
      }
    },
    //时间格式转换
    formatDate(time) {
      if (time) {
        let date = new Date(time * 1000);
        return formatDate(date, "yyyy-MM-dd hh:mm");
      } else {
        return "";
      }
    },
  },
};
</script>

<style scoped lang="scss">
.labelInput{
	border: 1px solid #dcdee2;
	width: 50%;
	padding: 0 5px;
	border-radius: 5px;
	min-height: 30px;
	cursor: pointer;
	.span{
		color: #c5c8ce;
	}
	.iconxiayi{
		font-size: 12px
	}
}
.custom-label {
  display: inline-flex;
  line-height: 1.5;
}
.grey {
  color: #999;
}
.maxW ::v-deep .ivu-select-dropdown {
  max-width: 600px;
}
.ivu-table-wrapper {
  border-left: 1px solid #dcdee2;
  border-top: 1px solid #dcdee2;
}
.tabBox_img {
  width: 50px;
  height: 50px;
}
.tabBox_img img {
  width: 100%;
  height: 100%;
}
.form {
  .picBox {
    display: inline-block;
    cursor: pointer;
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
      z-index: 9;
      width: 20px;
      height: 20px;
      left: 46px;
      top: -4px;
    }
  }
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
    cursor: pointer;
    display: flex;
  }
}
.table{
  line-height: 20px;
}
.product-data {
  display: flex;
  align-items: center;
  .image {
    width: 50px !important;
    height: 50px !important;
    margin-right: 10px;
  }
}
.df {
  display: flex;
  align-items: center;
}
.m10 {
  margin: 10px;
}
</style>

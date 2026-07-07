<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="手动退款"
      :visible.sync="dialogVisible"
      width="850px"
    >
      <div class="container" v-loading="loading">
        <div>
          <el-table
            ref="multipleSelection"
            :data="productList"
            tooltip-effect="dark"
            size="small"
            border

            :row-key="(row) => { return row.order_product_id }"
            @selection-change="handleSelectionChange"
            :header-cell-style="{background:'#F0F5FF'}"
          >
            <el-table-column align="center" type="selection" :reserve-selection="true" min-width="50"/>
            <el-table-column align="center" label="商品信息" min-width="200">
              <template slot-scope="scope">
                <div class="acea-row" style="align-items: center;">
                  <div class="demo-image__preview">
                    <el-image :src="scope.row.cart_info.productAttr.image" :preview-src-list="[scope.row.cart_info.productAttr.image]" />
                  </div>
                  <span style="width: 150px;">{{scope.row.cart_info.product.store_name}}</span>
                </div>
              </template>
            </el-table-column>
            <el-table-column align="center" label="规格" min-width="80">
              <template slot-scope="scope">
                <span>{{scope.row.cart_info.productAttr.sku || '默认'}}</span>
              </template>
            </el-table-column>
            <el-table-column align="center" label="商品实付金额" min-width="80">
              <template slot-scope="scope">
                <span>{{scope.row.product_price}}</span>
              </template>
            </el-table-column>
            <el-table-column v-if="order_type!=2" key="1" align="center" label="退还运费" min-width="80">
              <template slot-scope="scope">
                <span v-if="isRowSelected(scope.row)">{{ scope.row.postage_price}}</span>
                <span v-else>0.00</span>
              </template>
            </el-table-column>
            <el-table-column align="center" prop="product_num" label="总数" min-width="80" />
            <el-table-column label="退款数量" align="center" min-width="120">
              <template slot-scope="scope">
                <el-input-number
                  v-model="scope.row['refund_num']"
                  type="number"
                  :min="1"
                  :max="scope.row.max_num"
                  style="width: 100px;"
                  controls-position="right"
                  @change="limitNum(scope.row)"
                />
              </template>
            </el-table-column>
          </el-table>
        </div>
        <el-form label-width="100px" size="small" style="margin-top: 20px;">
          <el-form-item label="退款金额：" required>
            <el-input-number v-model="refund_price" :min="0" :max="maxRefundPrice" :precision="2" placeholder="请输入退款金额" class="selWidth" size="small" controls-position="right" />
            <span v-if="order_type == 2 && isDisplayPostage" style="font-size: 12px; color: #999;">包含退还运费：{{ postagePrice }}元</span>
          </el-form-item>
          <el-form-item  label="退款原因" required>
            <el-select v-model="refund_message" placeholder="请选择" class="selWidth">
              <el-option
                v-for="item in reasonList"
                :key="item"
                :label="item"
                :value="item"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="备注：">
            <el-input v-model="mer_mark" class="selWidth" size="small" />
          </el-form-item>
        </el-form>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false" size="small">取消</el-button>
        <el-button type="primary" @click="submitForm" :loading="btnloading" size="small">确定</el-button>
      </span>
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
import { getRundProductApi, getRundMessageApi, rundCreateApi, computeRefundPrice  } from '@/api/order'

export default {
  name: 'OrderCancellate',
  data() {
    return {
      dialogVisible: false,
      loading: true,
      btnloading: false,
      code: '',
      order_id: "",
      order_type: "",
      ruleValidate: {
        refund_price: [{ required: true, message: '请输入退款金额', trigger: 'blur' }],
        refund_message: [{ required: true, message: '请选择退款原因', trigger: 'change' }],
      },
      refund_price: "",
      refund_message: "",
      mer_mark: "",
      reasonList: [],
      multipleSelection: [],
      ids: [],
      productList: [],
      maxRefundPrice: 0,
      isDisplayPostage: false,
      postagePrice: 0
    }
  },
  methods: {
    submitForm() {
      if(this.refund_price == undefined){
        return this.$message.warning('请输入退款金额!')
      }
      if(!this.refund_message){
        return this.$message.warning('请选择退款原因!')
      }
      if (!this.multipleSelection.length) {
        return this.$message.warning('请选择商品!')
      }
      const refundData = {}
      this.multipleSelection.map((item) => {
        refundData[item.order_product_id] = item.refund_num
      })
      let data = {
        order_id: this.order_id,
        refund: refundData,
        refund_message: this.refund_message,
        refund_price: this.refund_price,
        mer_mark: this.mer_mark
      }
      this.btnloading = true
      rundCreateApi(data).then(res => {
        this.btnloading = false
        this.$message.success(res.message)
        this.dialogVisible = false
        this.$emit('refundSuccess')
      }).catch(({ message }) => {
        this.btnloading = false
        this.$message.error(message)
      })
    },
    // 计算退款金额
    getRefundPrice(){
      const refundData = {}
      this.multipleSelection.map((item) => {
        refundData[item.order_product_id] = item.refund_num
      })
      computeRefundPrice({ order_id: this.order_id, refund: refundData}).then(res => {
        this.refund_price = res.data.totalRefundPrice
        this.isDisplayPostage = res.data.isDisplayPostage
      }).catch(({ message }) => {
        this.refund_price = 0
        // this.$message.error(message)
      })
    },
    //订单详情
    getOrderDetails(id,type) {
      this.order_type = type;
      this.order_id = id
      this.loading = true
      this.dialogVisible = true
      getRundProductApi(id).then(res => {
        this.getRefundMessage()
        this.refund_message = res.data.refund_message
        this.mer_mark = res.data.mer_mark
        this.productList = res.data.product
        // this.multipleSelection = res.data.product
        // this.$nextTick(() => {
        //   res.data.product.forEach((row) => {
        //     this.$refs.multipleSelection.toggleRowSelection(row, true)
        //   })
        // })
        this.productList.forEach((item, index)=>{
          item.max_num = item.refund_num
        })
        this.postagePrice = res.data.postage_price;
        this.maxRefundPrice = Number(res.data.postage_price) + Number(res.data.total_refund_price)
        // this.refund_price = this.maxRefundPrice
        this.refund_price = 0
        this.loading = false
      }).catch(({ message }) => {
        this.dialogVisible = false
        this.loading = false
        this.$message.error(message)
      })
    },
    //退款原因
    getRefundMessage(){
      getRundMessageApi().then(res => {
        this.reasonList = res.data
        this.refund_message = res.data[0]
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 选择商品
    handleSelectionChange(val) {
      this.multipleSelection = val
      this.getRefundPrice();
    },
    // 判断某一行是否被选中
    isRowSelected(row) {
      return this.multipleSelection.some(selectedRow => selectedRow.product_id === row.product_id);
    },
    limitNum(row){
      if(row.refund_num > row.max_num){
        row.refund_num = row.max_num
      }else if(row.refund_num < 1){
        row.refund_num = 1
      }
      this.getRefundPrice()
    }
  }
}
</script>

<style scoped lang="scss">
.title{
  margin-bottom: 16px;
  color: #17233d;
  font-weight: 500;
  font-size: 14px;
}
</style>

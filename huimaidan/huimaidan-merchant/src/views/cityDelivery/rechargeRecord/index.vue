<template>
  <div class="divBox">
    <div class="selCard">
      <el-form size="small" inline label-width="85px" @submit.native.prevent>
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            clearable
            @change="onchangeTime"
          />
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <div class="mb20">
        <span>充值余额： {{delivery_balance}}</span>
        <el-button size="small" type="primary" class="ml10" @click="toRecharge">去充值</el-button>
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="序号" min-width="50">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="pay_price" label="充值金额" min-width="100" />
        <el-table-column prop="create_time" label="充值时间" min-width="100" />
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
    <!--充值-->
     <el-dialog title="配送费充值" :visible.sync="dialogVisible" width="700px">
      <div class="description" v-loading="spinShow" >
        <el-form size="small" label-width="130px">
          <el-form-item label="当前剩余金额：">
            <div class="description-term">{{delivery_balance}}</div>
          </el-form-item>
          <el-form-item label="选择充值金额：">
            <el-select
              v-model="price"
              placeholder="请选择"
              class="filter-item selWidth mr20"
              @change="getQRCode"
            >
              <el-option v-for="item in amountList" :key="item.value" :label="item.label" :value="item.value" />
            </el-select>
          </el-form-item>
          <el-form-item label="付款方式：">
            <div class="align-center">
              <div>
                <span style="color: #25b864;">微信支付</span>(支付码过期时间：{{endtime}})
              </div>
              <div class="erweima">
                <vue-qr class="bicode" :text="qrCode" :size="310" />
              </div>
            </div>
          </el-form-item>
        </el-form>
      </div>
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
import { rechargeLst, rechargeInfoApi } from '@/api/order'
// 二维码组件
import VueQr from 'vue-qr'
import timeOptions from '@/utils/timeOptions';
export default {
  components: { VueQr },
  data() {
    return {
      pickerOptions: timeOptions,
      dialogVisible: false,
      tableData: {
        data: [],
        total: 0
      },
      delivery_balance: '',
      listLoading: true,
      loading: true,
      tableFrom: {
        keyword: '',
        date: '',
        station_id: '',
        page: 1,
        limit: 20
      },
      timeVal: [],
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' }
        ]
      },
      amountList: [
        {label: '10.00元', value:10},
        {label: '50.00元', value:50},
        {label: '100.00元', value:100},
        {label: '200.00元', value:200},
        {label: '500.00元', value:500},
        {label: '1000.00元', value:1000},
      ],
      qrCode: "",
      endtime: "",
      price: 10,
      spinShow: false,

    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab
      this.timeVal = []
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.getList(1)
    },

    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      rechargeLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.delivery_balance = res.data.delivery_balance
          this.listLoading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.listLoading = false
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
    // 去充值
    toRecharge() {
      this.dialogVisible = true;
      this.getQRCode();
    },
    getQRCode() {
      this.spinShow = true
      rechargeInfoApi({price: this.price})
        .then(res => {
          this.$set(this, 'endtime', res.data.endtime)
          this.$set(this, 'qrCode', res.data.config)
          setTimeout(() => {
            this.spinShow = false
          }, 300)
        })
        .catch(res => {
          this.spinShow = false
          this.$message.error(res.message)
        })
    }
  }
}
</script>

<style lang="scss" scoped>
  .erweima{
    margin-top: 20px;
     img{
      width: 160px;
      height: 160px;

    }
  }

</style>

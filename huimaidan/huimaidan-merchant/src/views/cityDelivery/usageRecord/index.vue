<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="90px" inline>
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            clearable
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="订单号：" prop="order_sn">
          <el-input v-model="tableFrom.order_sn" placeholder="请输入订单号" class="selWidth" clearable @keyup.enter.native="getList(1)" />
        </el-form-item>
        <el-form-item label="发货点名称：" prop="station_id">
          <el-select
            v-model="tableFrom.station_id"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in storeList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="订单状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in statusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="配送单号：" prop="keyword">
          <el-input v-model="tableFrom.keyword" placeholder="请输入配送单号" class="selWidth" clearable @keyup.enter.native="getList(1)" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="序号" min-width="50">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="station.station_name" label="发货点名称" min-width="100" />
        <el-table-column prop="order_sn" label="配送订单号" min-width="80" />
        <el-table-column prop="storeOrder.order_sn" label="订单号" min-width="60" />
        <el-table-column label="配送起点" min-width="100">
         <template slot-scope="scope">
            <div>{{scope.row.station && scope.row.station.station_address}}</div>
          </template>
        </el-table-column>
        <el-table-column label="配送终点" min-width="100">
         <template slot-scope="scope">
            <div> {{scope.row.to_address}}</div>
          </template>
        </el-table-column>
        <el-table-column label="配送方式" min-width="60">
          <template slot-scope="scope">
            <div> {{ scope.row.station_type == 0 ? '商家配送' : scope.row.station_type == 1 ? '达达配送' : 'UU配送' }}</div>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="60">
          <template slot-scope="scope">
            <div> {{scope.row.status | runErrandStatus}}</div>
            <span
              v-if="scope.row.status == -1 && scope.row.reason"
              style="display: block; font-size: 12px; color: red"
            >原因: {{ scope.row.reason }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="distance" label="配送距离" min-width="100" />
        <el-table-column prop="fee" label="配送费用" min-width="100" />
       <el-table-column prop="create_time" label="消费时间" min-width="100" />
       <el-table-column prop="mark" label="备注" min-width="100" />
       <el-table-column label="操作" min-width="80" fixed="right">
          <template slot-scope="scope">
            <el-button v-if="scope.row.status != -1" type="text" size="small" @click="toCancle(scope.row.delivery_order_id)">取消</el-button>
            <!--<el-button type="text" size="small" @click="onDetails(scope.row.delivery_order_id)">详情</el-button>-->
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
import { deliveryOrderLst, getStoreLst, deliveryOrderCancle } from '@/api/order'
import timeOptions from '@/utils/timeOptions';
export default {
  components: {

  },
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      loading: true,
      tableFrom: {
        keyword: '',
        order_sn: '',
        date: '',
        station_id: '',
        page: 1,
        limit: 20
      },
      timeVal: [],
      storeList: [],
      statusList: [
        {label: '已取消', value: '-1'},
        {label: '待接单', value: '0'},
        {label: '待取货', value: '2'},
        {label: '配送中', value: '3'},
        {label: '已完成', value: '4'},
        {label: '物品返回中', value: '9'},
        {label: '物品返回完成', value: '10'},
        {label: '骑士到店', value: '100'}
      ]
    }
  },
  mounted() {
    this.getList(1)
    this.getStoreList()
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.getList(1)
    },
    // 获取发货点列表
    getStoreList() {
      getStoreLst(this.tableFrom)
        .then(res => {
          this.storeList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      deliveryOrderLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
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
    // 取消
    toCancle(id) {
      this.$modalForm(deliveryOrderCancle(id)).then(() => this.getList(''))
    },
    // 详情
    onDetails(id) {

    }
  }
}
</script>

<style lang="scss" scoped>

</style>

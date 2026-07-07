<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="85px">
        <el-form-item label="创建时间：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            clearable
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="分账时间：">
          <el-date-picker
            v-model="timeVal2"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            placeholder="自定义时间"
            style="width: 280px;"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            clearable
            :picker-options="pickerOptions"
            @change="onchangeTime2"
          />
        </el-form-item>
        <el-form-item label="分账状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            class="selWidth"
            size="small"
            clearable
            @change="getList(1)"
          >
            <el-option v-for="item in applyStatus" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="账单类型：" prop="type">
          <el-select
            v-model="tableFrom.type"
            placeholder="请选择"
            class="selWidth"
            size="small"
            clearable
            @change="getList(1)"
          >
            <el-option label="订单支付" value="order" />
            <el-option label="尾款支付" value="presell" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="exports">列表导出</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="profitsharing_id" label="分账ID" min-width="50" />
        <el-table-column prop="order.order_sn" label="订单编号" min-width="60" />
        <el-table-column label="分账金额" min-width="100">
          <template slot-scope="scope">
            <div>订单金额： {{ scope.row.profitsharing_price }}</div>
            <div v-if="scope.row.profitsharing_refund > 0" style="color:#82e493;">退款金额： {{ scope.row.profitsharing_refund }}</div>
            <div>分账给商户金额： {{ scope.row.profitsharing_mer_price }}</div>
          </template>
        </el-table-column>
        <el-table-column label="账单类型" min-width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.type == 'order' ? '订单支付' : '尾款支付' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" min-width="100">
          <template slot-scope="scope">
            <div v-if="scope.row.status == 0">未分账</div>
            <div v-else-if="scope.row.status == 1">已分账<br>分账时间： {{ scope.row.profitsharing_time }}</div>
            <div v-else-if="scope.row.status == -1">已退款</div>
            <div v-else-if="scope.row.status == -2">分账失败<br> <span style="color: red; font-size: 12px;"> 失败原因： {{ scope.row.error_msg }}</span></div>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="100" />
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
    <!--导出订单列表-->
    <file-list ref="exportList" />
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
import { applymentLstApi, ledgerManageExportApi } from '@/api/system';
import createWorkBook from '@/utils/newToExcel.js';
import fileList from "@/components/exportFile/fileList";
import timeOptions from '@/utils/timeOptions';
export default {
  components: { fileList },
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      listLoading: true,
      tableFrom: {
        type: '',
        keyword: '',
        status: '',
        date: '',
        profit_date: '',
        page: 1,
        limit: 20
      },
      timeVal: [],
      timeVal2: [],
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
      selectionList: [],
      ids: '',
      LogLoading: false,
      applyStatus: [
        { value: 0, label: '待分账' },
        { value: 1, label: '已分账' },
        { value: -1, label: '已退款' },
        { value: -2, label: '分账失败' }
      ],
      orderDatalist: null
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.timeVal2 = []
      this.tableFrom.date = ""
      this.tableFrom.profit_date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab
      this.timeVal = []
      this.getList(1)
    },
    selectChange2(tab) {
      this.tableFrom.profit_date = tab
      this.timeVal2 = []
      this.getList(1)
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
      this.getList(1)
    },
    onchangeTime2(e) {
      this.timeVal2 = e
      this.tableFrom.profit_date = e ? this.timeVal2.join('-') : ''
      this.getList(1)
    },
    async exports(value) {
      let excelData = JSON.parse(JSON.stringify(this.tableFrom)), data = []
      excelData.page = 1
      let pageCount = 1
      let lebData = {};
      for (let i = 0; i < pageCount; i++) {
        lebData = await this.downData(excelData)
        pageCount = Math.ceil(lebData.count/excelData.limit)
        if (lebData.export.length) {
          data = data.concat(lebData.export)
          excelData.page++
        }
      }
      createWorkBook(lebData.header, lebData.title, data, lebData.foot,lebData.filename);
      return
    },
    /**资金流水 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        ledgerManageExportApi(excelData).then((res) => {
          return resolve(res.data)
        })
      })
    },
    // 导出
    exportRecord() {
      ledgerManageExportApi( this.tableFrom)
        .then((res) => {
          const h = this.$createElement;
          this.$msgbox({
            title: '提示',
            message: h('p', null, [
              h('span', null, '文件正在生成中，请稍后点击"'),
              h('span', { style: 'color: teal' }, '导出记录'),
              h('span', null, '"查看~ '),
            ]),
            confirmButtonText: '我知道了',
          }).then(action => {

          });
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    // 导出列表
    getExportFileList() {
      this.$refs.exportList.exportFileList();
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      applymentLstApi(this.tableFrom)
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
    }
  }
}
</script>

<style lang="scss" scoped>
table .el-image{
  display: inline-block;
}
.el-icon-arrow-down {
  font-size: 12px;
}
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
}
.mt20 {
  margin-top: 20px;
}
.demo-image__preview{
  position: relative;
}
.maxw180{
  display: inline-block;
  max-width: 180px;
}

</style>

<template>
  <div class="divBox">
    <div class="selCard mb14">
      <el-form :model="tableFrom" ref="searchForm" size="small" inline label-width="90px">
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
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="审核状态：" prop="status">
          <el-select
            v-model="tableFrom.status"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="全部" value="" />
            <el-option label="待审核" value="0" />
            <el-option label="已审核" value="1" />
            <el-option label="审核失败" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item label="到账状态：" prop="financial_status">
          <el-select
            v-model="tableFrom.financial_status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="(getList(1))"
          >
            <el-option
              v-for="item in arrivalStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <br>
        <el-form-item label="收款方式：" prop="financial_type">
          <el-select
            v-model="tableFrom.financial_type"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="全部" value="" />
            <el-option label="银行卡" value="1" />
            <el-option label="支付宝" value="3" />
            <el-option label="微信" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="管理员搜索：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入管理员ID/姓名"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <cards-data v-if="cardLists.length>0" :card-lists="cardLists" />
    <el-card class="box-card">
      <div class="mb20">
        <el-button size="small" type="primary" @click="applyTransfer">
        申请转账
      </el-button>
      <el-button size="small" type="primary" @click="exports">导出列表</el-button>
      </div>
      <el-table
        v-loading="listLoading"
        tooltip-effect="dark"
        :data="tableData.data"
        class="table"
        size="small"
      >
        <el-table-column label="序号" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="申请时间" min-width="170" />
        <el-table-column prop="extract_money" label="转账金额（元）" min-width="120" />
        <el-table-column prop="mer_admin_id" label="管理员姓名" min-width="90" />
        <el-table-column label="收款方式" min-width="100">
          <template slot-scope="scope">
            <span v-if="scope.row.financial_type">{{ scope.row.financial_type == 1 ? '银行' : scope.row.financial_type == 2 ? '微信' : '支付宝' }}</span>
            <span v-else>--</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.status == 0 ? '待审核' : scope.row.status == 1 ? '审核通过' : '审核未通过' }}</span>
            <span v-if="scope.row.status === -1" style="font-size: 12px;">
              <br />
              原因：{{ scope.row.refusal }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="到账状态" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.financial_status == 1 ? '已到账' : '未到账' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="mer_money" label="余额（元）" min-width="120"/>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="transferDetail(scope.row.financial_id)">转账信息</el-button>
            <el-button type="text" size="small" @click="transferMark(scope.row.financial_id)">备注</el-button>
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
    <!--转账信息-->
    <el-dialog v-if="dialogVisible" title="转账信息:" :visible.sync="dialogVisible" width="540px">
      <div class="box-container">
        <div class="acea-row">
          <div class="list sp100"><label class="name">商户余额：</label>{{ transferData.mer_money }}</div>
          <div class="list sp100"><label class="name">商户收款方式：</label>{{ transferData.financial_type == 1 ? '银行卡' : transferData.financial_type == 2 ? '微信' : '支付宝' }}</div>
          <div v-if="transferData.financial_type == 1" class="list sp100"><label class="name">开户银行：</label>{{ transferData.financial_account.bank }}</div>
          <div v-if="transferData.financial_type == 1" class="list sp100"><label class="name">银行账号：</label>{{ transferData.financial_account.bank_code }}</div>
          <div v-if="transferData.financial_type == 1" class="list sp100"><label class="name">开户户名：</label>{{ transferData.financial_account.name }}</div>
          <div v-if="transferData.financial_type != 1" class="list sp100"><label class="name">真实姓名：</label>{{ transferData.financial_account.name }}</div>
          <div v-if="transferData.financial_type == 2" class="list sp100"><label class="name">微信号：</label>{{ transferData.financial_account.wechat }}</div>
          <div v-if="transferData.financial_type == 2" class="list sp100 image"><label class="name">微信收款二维码：</label><img style="max-width: 150px; height: 80px;" :src="transferData.financial_account.wechat_code"/></div>
          <div v-if="transferData.financial_type == 3" class="list sp100"><label class="name">支付宝账号：</label>{{ transferData.financial_account.alipay }}</div>
          <div v-if="transferData.financial_type == 3" class="list sp100 image"><label class="name">支付宝收款二维码：</label><img style="max-width: 150px; height: 80px;" :src="transferData.financial_account.alipay_code"/></div>
          <div class="list sp100"><label class="name">本次申请转账金额：</label><span class="font-red">{{ transferData.extract_money }}</span></div>
          <div class="list sp100"><label class="name">审核状态：</label>{{ transferData.status == 0 ? '待审核' : transferData.status == 1 ? '已审核' : '审核失败' }}</div>
          <div v-if="transferData.status == 1" class="list sp100"><label class="name">审核时间：</label>{{ transferData.status_time }}</div>
          <div v-if="transferData.status == 1 && transferData.update_time" class="list sp100"><label class="name">转账凭证：</label>
            <div v-if="transferData.image.length > 0" class="acea-row">
              <div v-for="(item,index) in transferData.image" :key="index" class="pictrue">
                <img @click="getPicture(item)" :src="item"/>
              </div>
            </div>
          </div>
          <div v-if="transferData.status == 1 && transferData.update_time" class="list sp100"><label class="name">转账时间：</label>{{ transferData.update_time }}</div>
          <div v-if="transferData.status == -1" class="list sp100"><label class="name">审核未通过原因：</label>{{ transferData.refusal }}</div>
        </div>
      </div>
    </el-dialog>
    <!--查看二维码-->
    <el-dialog v-if="pictureVisible" :visible.sync="pictureVisible" width="700px">
      <img :src="pictureUrl" class="pictures"/>
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
import {transferLstApi, applyTransferApi, transferDetailApi, transferMarkApi, transferRecordsExportApi } from '@/api/accounts'
import cardsData from "@/components/cards/index";
import createWorkBook from '@/utils/newToExcel.js';
import timeOptions from '@/utils/timeOptions';
export default {
  components: { cardsData },
  name: 'transferAccount',
  data() {
    return {
      pickerOptions: timeOptions,
      tableData: {
        data: [],
        total: 0
      },
      arrivalStatusList: [
        { label: "已到账", value: 1 },
        { label: "未到账", value: 0 }
      ],
      listLoading: true,
      tableFrom: {
        date: '',
        page: 1,
        limit: 20,
        keyword: '',
        financial_type: '',
        status: '',
        financial_status: ''
      },
      timeVal: [],
      selectionList: [],
      loading: false,
      dialogVisible: false,
      pictureVisible: false,
      pictureUrl: '',
      transferData: {},
      cardLists: [],
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    /**重置 */
    searchReset(){
      this.timeVal = []
      this.tableFrom.date = ""
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 转账信息
    transferDetail(id) {
      transferDetailApi(id).then(res => {
        this.dialogVisible = true;
        this.transferData = res.data
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 获取支付二维码
    getPicture(url) {
      this.pictureVisible = true;
      this.pictureUrl = url;
    },
    // 备注
    transferMark(id) {
      this.$modalForm(transferMarkApi(id)).then(() => this.getList(1))
    },
    // 申请转账
    applyTransfer() {
      this.$modalForm(applyTransferApi()).then(() => this.getList(1))
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.tableFrom.date = e ? this.timeVal.join('-') : ''
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
    /**转账记录 */
    downData(excelData) {
      return new Promise((resolve, reject) => {
        transferRecordsExportApi(excelData).then((res) => {
          return resolve(res.data)
        })
      })
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num ? num : this.tableFrom.page;
      transferLstApi(this.tableFrom)
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

    handleClose() {
      this.dialogLogistics = false
    },
  }
}
</script>

<style lang="scss" scoped>
::v-deep .el-dialog__title{
  font-weight: bold;
}
.el-icon-arrow-down {
  font-size: 12px;
}
.font-red{
  color: #ff4949;
}
.tabBox_tit {
  width: 60%;
  font-size: 12px !important;
  margin: 0 2px 0 10px;
  letter-spacing: 1px;
  padding: 5px 0;
  box-sizing: border-box;
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
.box-container {
  overflow: hidden;
  padding: 0 10px;
}
.box-container .list.image {
  display: flex;
  align-items: center;
}

.box-container .list {
  float: left;
  line-height: 30px;
}
.box-container .sp100 {
  width: 100%;
  font-size: 13px;
}
.acea-row{
  margin-bottom: 20px;
}
</style>

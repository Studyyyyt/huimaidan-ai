<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFromIssue" ref="searchForm" size="small" label-width="85px"  :inline="true">
        <el-form-item label="使用状态：" prop="status">
          <el-select v-model="tableFromIssue.status" placeholder="请选择状态" @change="getIssueList" clearable class="selWidth">
            <el-option label="全部" value="" />
            <el-option label="已使用" value="1" />
            <el-option label="未使用" value="0" />
            <el-option label="已过期" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="领取人：" prop="username">
          <el-input v-model="tableFromIssue.username" @keyup.enter.native="getIssueList" placeholder="请输入领取人" clearable class="selWidth" />
        </el-form-item>
        <el-form-item label="优惠劵：" prop="coupon_id">
          <el-input v-model="tableFromIssue.coupon_id" @keyup.enter.native="getIssueList" placeholder="请输入优惠劵ID" clearable class="selWidth" />
        </el-form-item>
        <el-form-item label="获取方式：" prop="type">
          <el-select v-model="tableFromIssue.type" placeholder="请选择状态" @change="getIssueList" clearable class="selWidth">
            <el-option label="全部" value="" />
            <el-option label="自己领取" value="receive" />
            <el-option label="后台发送" value="send" />
            <el-option label="新人" value="new" />
            <el-option label="买赠送" value="buy" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getIssueList">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="Loading"
        :data="issueData.data"
        size="small"
      >
        <el-table-column
          prop="coupon_id"
          label="ID"
          min-width="80"
        />
        <el-table-column
          prop="coupon_title"
          label="优惠券名称"
          min-width="150"
        />
        <el-table-column
          label="领取人"
          min-width="200"
        >
          <template slot-scope="scope">
            <span v-if="scope.row.user">{{scope.row.user.nickname | filterEmpty}}</span>
            <span v-else>未知</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="coupon_price"
          label="面值"
          min-width="100"
        />
        <el-table-column
          prop="use_min_price"
          label="最低消费额"
          min-width="120"
        />
        <el-table-column
          prop="start_time"
          label="开始使用时间"
          min-width="150"
        />
        <el-table-column
          prop="end_time"
          label="结束使用时间"
          min-width="150"
        />
        <el-table-column
          label="获取方式"
          min-width="150"
        >
          <template slot-scope="scope">
            <span>{{scope.row.type | failFilter}}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="状态"
          min-width="100"
        >
          <template slot-scope="scope">
            <span>{{scope.row.status | statusFilter}}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          background
          :page-size="tableFromIssue.limit"
          :current-page="tableFromIssue.page"
          layout="total, prev, pager, next, jumper"
          :total="issueData.total"
          @size-change="handleSizeChangeIssue"
          @current-change="pageChangeIssue"
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
import { issueApi } from '@/api/marketing'
import { roterPre } from '@/settings'
export default {
  name: 'CouponUser',
  filters: {
    failFilter(status) {
      const statusMap = {
        'receive': '自己领取',
        'send': '后台发送',
        'give': '满赠',
        'new': '新人',
        'buy': '买赠送'
      }
      return statusMap[status]
    },
    statusFilter(status) {
      const statusMap = {
        0: '未使用',
        1: '已使用',
        2: '已过期'
      }
      return statusMap[status]
    }
  },
  data() {
    return {
      Loading: false,
      roterPre: roterPre,
      tableFromIssue: {
        page: 1,
        limit: 10,
        coupon_id: '',
        status: '',
        username: '',
        type: ''
      },
      issueData: {
        data: [],
        total: 0
      }
    }
  },
  mounted() {
    this.getIssueList()
  },
  methods: {
    /**
     * 重置搜索表单
     */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getIssueList(1)
    },
    // 获取优惠券发放列表
    getIssueList() {
      this.Loading = true
      issueApi(this.tableFromIssue).then(res => {
        this.issueData.data = res.data.list
        this.issueData.total = res.data.count
        this.Loading = false
      }).catch(res => {
        this.Loading = false
        this.$message.error(res.message)
      })
    },
    // 页码改变事件
    pageChangeIssue(page) {
      this.tableFromIssue.page = page
      this.getIssueList()
    },
    // 每页显示数量改变事件
    handleSizeChangeIssue(val) {
      this.tableFromIssue.limit = val
      this.getIssueList()
    }
  }
}
</script>
<style scoped lang="scss">
</style>

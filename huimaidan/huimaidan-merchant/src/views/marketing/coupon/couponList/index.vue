<template>
  <div class="divBox">
    <!-- 搜索表单区域 -->
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="95px" :inline="true">
        <!-- 优惠券状态筛选 -->
        <el-form-item label="优惠券状态：" prop="status">
          <el-select v-model="tableFrom.status" placeholder="请选择" class="selWidth" clearable @change="getList(1)">
            <el-option label="未开启" :value="0" />
            <el-option label="开启" :value="1" />
          </el-select>
        </el-form-item>
        <!-- 优惠券类型筛选 -->
        <el-form-item label="优惠券类型：" prop="type">
          <el-select v-model="tableFrom.type" placeholder="请选择" class="selWidth" clearable @change="getList(1)">
            <el-option label="店铺券" :value="0" />
            <el-option label="商品券" :value="1" />
          </el-select>
        </el-form-item>
        <!-- 获取方式筛选 -->
        <el-form-item label="获取方式：" prop="send_type">
          <el-select v-model="tableFrom.send_type" placeholder="请选择" class="selWidth" clearable @change="getList(1)">
            <el-option label="手动获取" :value="0" />
            <el-option label="新人" :value="2" />
            <el-option label="买赠" :value="3" />
            <el-option label="后台发放" :value="6" />
          </el-select>
        </el-form-item>
        <!-- 优惠券名称筛选 -->
        <el-form-item label="优惠券名称：" prop="coupon_name">
          <el-input v-model="tableFrom.coupon_name" placeholder="请输入优惠券名称" class="selWidth" clearable @keyup.enter.native="getList(1)" />
        </el-form-item>
        <!-- 搜索和重置按钮 -->
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <!-- 添加优惠券按钮 -->
      <div class="mb20">
        <router-link :to=" { path:`${roterPre}` + '/marketing/coupon/creatCoupon' } ">
          <el-button size="small" type="primary">添加优惠劵</el-button>
        </router-link>
      </div>
      <!-- 优惠券列表表格 -->
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column
          prop="coupon_id"
          label="ID"
          min-width="50"
        />
        <el-table-column
          prop="title"
          label="优惠劵名称"
          min-width="120"
        />
        <el-table-column
          label="优惠劵类型"
          min-width="90"
        >
          <template slot-scope="{ row }">
            <span>{{ row.type | couponTypeFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="150"
          label="领取日期"
        >
          <template slot-scope="{ row }">
            <div v-if="row.start_time">
              {{ row.start_time }} <br>- {{ row.end_time }}
            </div>
            <span v-else>不限时</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="150"
          label="使用时间"
        >
          <template slot-scope="{ row }">
            <div v-if="row.use_start_time && row.use_end_time">
              {{ row.use_start_time }} <br>- {{ row.use_end_time }}
            </div>
            <span v-else>{{ row.coupon_time }}天</span>
          </template>
        </el-table-column>
        <el-table-column
          min-width="120"
          label="发布数量"
        >
          <template slot-scope="{ row }">
            <span v-if="row.is_limited ===0 ">不限量</span>
            <div v-else>
              <span class="fa">发布：{{ row.total_count }}</span>
              <span class="sheng">剩余：{{ row.remain_count }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          min-width="120"
          label="使用数量"
        >
          <template slot-scope="{ row }">
            <div>
              <span>已领取/发放总数：{{ row.send_num }}</span>
              <span class="sheng">已使用总数：{{ row.used_num }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          label="状态"
          min-width="100"
        >
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="显示"
              inactive-text="隐藏"
              :width="55"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="details(scope.row.coupon_id)">详情</el-button>
            <el-button type="text" size="small" @click="receive(scope.row.coupon_id)">领取/发放记录</el-button>
            <el-button type="text" size="small" @click="onEdit(scope.row.coupon_id)">编辑</el-button>
            <router-link :to=" { path:`${roterPre}` + '/marketing/coupon/creatCoupon/' + scope.row.coupon_id } ">
              <el-button type="text" size="small" class="ml14 mr14">复制</el-button>
            </router-link>
            <el-button type="text" size="small" @click="handleDelete(scope.row.coupon_id, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页组件 -->
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
    <!-- 优惠券详情抽屉 -->
    <el-drawer v-if="detailDialog" title="优惠券详情" :visible.sync="detailDialog" size="800px">
      <div>
        <el-tabs type="border-card" v-model="activeName">
          <el-tab-pane label="基本信息" name="basic">
            <div class="section">
              <div class="title">优惠券信息</div>
              <ul class="list">
                <li class="item">
                  <div>优惠券名称：</div>
                  <div class="value">{{couponDetail.title}}</div>
                </li>
                <li class="item">
                  <div>优惠券类型：</div>
                  <div class="value">{{couponDetail.type | couponTypeFilter}}</div>
                </li>
                <li class="item">
                  <div>优惠券面值：</div>
                  <div class="value">{{couponDetail.coupon_price}}</div>
                </li>
                <li class="item">
                  <div>使用门槛：</div>
                  <div class="value">
                    {{ couponDetail.use_min_price == '0.00' ? "无门槛" : "最低消费"+couponDetail.use_min_price }}
                  </div>
                </li>
                <li class="item">
                  <div>使用有效期：</div>
                  <div class="value" v-if="couponDetail.coupon_time && couponDetail.coupon_type == 0">{{ couponDetail.coupon_time }}天</div>
                  <div class="value" v-else-if="couponDetail.coupon_type == 1">
                    {{ couponDetail.use_start_time + " - " + couponDetail.use_end_time }}
                  </div>
                </li>
                <li class="item">
                  <div>领取时间：</div>
                  <div class="value" v-if="couponDetail.is_timeout == 1">
                    {{ couponDetail.start_time }} - {{ couponDetail.end_time }}
                  </div>
                  <div class="value" v-else>不限时</div>
                </li>
                <li class="item">
                  <div>获取方式：</div>
                  <div class="value" v-if="couponDetail.send_type == 0">手动领取</div>
                  <div class="value" v-else-if="couponDetail.send_type == 1">消费满赠券</div>
                  <div class="value" v-else-if="couponDetail.send_type == 2">新人券</div>
                  <div class="value" v-else-if="couponDetail.send_type == 3">买赠券</div>
                  <div class="value" v-else-if="couponDetail.send_type == 4">首单赠送券</div>
                  <div class="value" v-else-if="couponDetail.send_type == 5">会员赠送券</div>
                </li>
                <li class="item">
                  <div>是否限量：</div>
                  <div class="value theme">{{couponDetail.is_limited | filterClose}}</div>
                </li>
                <li class="item">
                  <div>已发布总数：</div>
                  <div class="value">{{ couponDetail.is_limited == 0 ? "不限量" : couponDetail.total_count }}</div>
                </li>
                <li class="item">
                  <div>剩余总数：</div>
                  <div class="value">{{ couponDetail.is_limited == 0 ? "不限量" : couponDetail.remain_count }}</div>
                </li>
               <li class="item">
                  <div>创建时间：</div>
                  <div class="value">{{couponDetail.create_time}}</div>
                </li>
                <li class="item">
                  <div>状态：</div>
                  <div class="value">{{couponDetail.status ? "开启" : "关闭"}}</div>
                </li>
                <li class="item">
                  <div>排序：</div>
                  <div class="value">{{couponDetail.sort}}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">优惠券情况</div>
              <ul class="list">
                <li class="item">
                  <div>已领取/发放总数：</div>
                  <div class="value">
                    {{couponDetail.send_num}}
                    <el-button
                      size="small"
                      type="text"
                      class="ml20"
                      @click="receive(couponDetail.coupon_id)"
                      >已领取/发放记录</el-button>
                  </div>
                </li>
                <li class="item">
                  <div>已使用总数：</div>
                  <div class="value">
                    {{couponDetail.used_num}}
                    <el-button
                      size="small"
                      type="text"
                      class="ml20"
                      @click="usedRecord(couponDetail.coupon_id)"
                      >已领取/使用记录</el-button>
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane v-if="type == 1" label="商品信息" name="product">
            <el-table v-loading="listLoading" :data="relateData.data">
              <el-table-column prop="product.product_id" label="ID" min-width="50" />
              <el-table-column label="商品图" min-width="80">
                <template slot-scope="scope">
                  <div v-if="scope.row.product.image" class="demo-image__preview">
                    <img
                      style="width: 36px; height: 36px"
                      :src="scope.row.product.image"
                    >
                  </div>
                  <div v-else class="demo-image__preview">
                    <img
                      style="width: 36px; height: 36px"
                      src="../../../../assets/images/f.png"
                    >
                  </div>
                </template>
              </el-table-column>
              <el-table-column prop="product.store_name" label="商品名称" min-width="150" />
              <el-table-column prop="product.stock" label="库存" min-width="50" />
              <el-table-column prop="product.price" label="商品售价" min-width="50" />
              <el-table-column prop="product.sales" label="销售数量" min-width="50" />
            </el-table>
            <div class="block mb20">
              <el-pagination
                :page-size="tableFromRelate.limit"
                :current-page="tableFromRelate.page"
                layout="prev, pager, next, jumper"
                :total="relateData.total"
                @size-change="handleSizeChangeRelate"
                @current-change="pageChangeRelate"
              />
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
     <!-- 领取/使用记录弹窗 -->
    <el-dialog
      :title="title"
      :visible.sync="dialogVisible"
      width="660px"
      :before-close="handleClose"
      class="modalbox"
    >
      <el-table
        v-loading="Loading"
        :data="issueData.data"
        style="width: 100%"
        size="small"
        highlight-current-row
      >
        <el-table-column
          prop="user.nickname"
          label="用户名"
          min-width="150"
        />
        <el-table-column label="用户头像" min-width="80">
          <template slot-scope="scope">
            <div v-if="scope.row.user&&scope.row.user.avatar" class="demo-image__preview">
              <img
                style="width: 36px; height: 36px"
                :src="scope.row.user.avatar"
              >
            </div>
            <div v-else class="demo-image__preview">
              <img
                style="width: 36px; height: 36px"
                src="../../../../assets/images/f.png"
              >
            </div>
          </template>
        </el-table-column>
        <el-table-column
          v-if="receiveType == 0"
          key="1"
          label="领取方式"
          min-width="120"
        >
          <template slot-scope="scope">
            <span v-if="scope.row.type == 'receive'">自己领取</span>
            <span v-else-if="scope.row.type == 'send'">后台发送</span>
            <span v-else-if="scope.row.type == 'give'">满赠券</span>
            <span v-else-if="scope.row.type == 'new'">新人券</span>
            <span v-else-if="scope.row.type == 'buy'">买赠送</span>
          </template>
        </el-table-column>
        <el-table-column
          :label="receiveTime"
          min-width="120"
        >
          <template slot-scope="scope">
            <span v-if="receiveType === 0">{{ scope.row.create_time }}</span>
            <span v-else>{{ scope.row.use_time }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          :page-size="tableFromIssue.limit"
          :current-page="tableFromIssue.page"
          layout=" prev, pager, next, jumper"
          :total="issueData.total"
          @size-change="handleSizeChangeIssue"
          @current-change="pageChangeIssue"
        />
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
import { couponIssueListApi, couponIssueStatusApi, issueApi, couponDeleteApi, couponDetailApi, couponRelateProLst, couponUpdateApi } from '@/api/marketing'
import { roterPre } from '@/settings'
export default {
  name: 'CouponList',
  data() {
    return {
      Loading: false,
      dialogVisible: false,
      detailDialog: false,
      roterPre: roterPre,
      listLoading: true,
      title: '领取/发放记录',
      receiveTime: '领取时间',
      activeName: "basic",
      receiveType: 0,
      id: '',
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        status: '',
        coupon_name: '',
        type: '',
        send_type: ''
      },
      tableFromIssue: {
        page: 1,
        limit: 10,
        coupon_id: 0
      },
      issueData: {
        data: [],
        total: 0
      },
      relateData: {
        data: [],
        total: 0
      },
      tableFromRelate: {
        page: 1,
        limit: 5
      },
      couponDetail: {},
      type: 0
    }
  },
  mounted() {
    this.getList(1)
  },
  methods: {
    // 重置搜索表单
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 删除优惠券
    handleDelete(id, idx) {
      this.$modalSure().then(() => {
        couponDeleteApi(id).then(({ message }) => {
          this.$message.success(message)
          this.getList()
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    // 关闭对话框
    handleClose() {
      this.dialogVisible = false
    },
    // 查看优惠券详情
    details(id) {
      this.detailDialog = true
      this.type = 0
      couponDetailApi(id).then(res => {
        this.couponDetail = res.data
        this.type = res.data.type
        this.id = id
        if (res.data.type == 1) this.getRelateList(id)
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 编辑优惠券
    onEdit(id) {
      this.$modalForm(couponUpdateApi(id)).then(() => this.getList(''))
    },
    // 查看领取/发放记录
    receive(id) {
      this.dialogVisible = true
      this.title = '领取/发放记录'
      this.receiveTime = '领取时间'
      this.receiveType = 0
      this.tableFromIssue.coupon_id = id
      this.getIssueList('')
    },
    // 使用记录
    usedRecord(id) {
      this.dialogVisible = true
      this.title = '使用记录'
      this.receiveTime = '使用时间'
      this.receiveType = 1
      this.tableFromIssue.coupon_id = id
      this.getIssueList(1)
    },
    // 获取使用记录列表
    getIssueList(status) {
      this.Loading = true
      this.tableFromIssue.status = status
      issueApi(this.tableFromIssue).then(res => {
        this.issueData.data = res.data.list
        this.issueData.total = res.data.count
        this.Loading = false
      }).catch(res => {
        this.Loading = false
        this.$message.error(res.message)
      })
    },
    pageChangeIssue(page) {
      this.tableFromIssue.page = page
      let status = this.receiveType == 1 ? 1 : ''
      this.getIssueList(status)
    },
    handleSizeChangeIssue(val) {
      this.tableFromIssue.limit = val
      let status = this.receiveType == 1 ? 1 : ''
      this.getIssueList(status)
    },
    // 获取关联商品列表
    getRelateList(id) {
      this.Loading = true
      this.relateData.data = []
      couponRelateProLst(id, this.tableFromRelate).then(res => {
        this.relateData.data = res.data.list
        this.relateData.total = res.data.count
        this.Loading = false
      }).catch(res => {
        this.Loading = false
        this.$message.error(res.message)
      })
    },
    pageChangeRelate(page) {
      this.tableFromRelate.page = page
      this.getRelateList(this.id)
    },
    handleSizeChangeRelate(val) {
      this.tableFromRelate.limit = val
      this.getRelateList(this.id)
    },
    // 获取优惠券列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      couponIssueListApi(this.tableFrom).then(res => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
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
    // 修改状态
    onchangeIsShow(row) {
      couponIssueStatusApi(row.coupon_id, row.status).then(({ message }) => {
        this.$message.success(message)
        this.getList('')
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    }
  }
}
</script>

<style scoped lang="scss">
  .modalbox ::v-deep .el-dialog{
    min-width: 550px;
  }
  .fa{
    color: #0a6aa1;
    display: block;
  }
  .sheng{
    color: #ff0000;
    display: block;
  }
  .section {
  padding: 30px 10px;
  border-bottom: 1px dashed #eeeeee;
  &:first-child{
    padding-top: 15px;
  }
  &:last-child{
    border: none;
  }
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
    font-weight: bold;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    align-items: center;
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }

  }
  .theme{
    color: var(--prev-color-primary);
  }
}
.el-tabs--border-card{
  box-shadow: none;
  border: none;
}
</style>

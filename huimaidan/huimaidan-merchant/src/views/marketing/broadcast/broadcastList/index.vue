<template>
  <div class="divBox">
    <!-- 搜索筛选卡片 -->
    <div class="selCard">
      <el-form :model="tableForm" ref="searchForm" size="small" label-width="65px" inline>
        <!-- 状态筛选 -->
        <el-form-item label="状态：" prop="status_tag">
          <el-select v-model="tableForm.status_tag" placeholder="请选择状态" @change="getList(1)" clearable class="selWidth">
            <el-option label="全部" value="" />
            <el-option label="未审核" value="0" />
            <el-option label="已审核" value="1" />
            <el-option label="审核失败" value="-1" />
          </el-select>
        </el-form-item>
        <!-- 关键字搜索 -->
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableForm.keyword"
            placeholder="请输入直播商品名称/ID"
            class="selWidth"
            @keyup.enter.native="getList(1)"
           />
        </el-form-item>
        <!-- 搜索和重置按钮 -->
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <!-- 卡片内容，包含表格和分页 -->
    <el-card class="mt14">
      <!-- 操作按钮 -->
      <div class="mb20">
        <router-link :to=" { path:`${roterPre}` + '/marketing/broadcast/addProduct' } ">
         <el-button size="small" type="primary">添加直播商品</el-button>
        </router-link>
        <el-button size="small" type="success" @click="batchAdd">批量添加直播商品</el-button>
      </div>
      <!-- 商品列表表格 -->
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        highlight-current-row
      >
        <el-table-column label="序号" min-width="60">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableForm.page - 1) * tableForm.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="goods_id" label="商品ID" min-width="60" />
        <el-table-column prop="name" label="商品名称" min-width="150" />
        <el-table-column label="商品图" min-width="80">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image :src="scope.row.cover_img" :preview-src-list="[scope.row.cover_img]" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="原价" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.product ? scope.row.product.price : '' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="直播价" min-width="150" />
        <el-table-column label="库存" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.product && scope.row.product.stock }}</span>
          </template>
        </el-table-column>
        <el-table-column v-if="tableForm.status_tag !== 1" key="3" label="审核状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.status | liveReviewStatusFilter }}</span>
            <span v-if="scope.row.status === -1" style="display: block; font-size: 12px;">原因 {{ scope.row.error_msg }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否上架" min-width="100">
          <template v-if="scope.row.status === 2" slot-scope="scope">
            <el-switch
              v-model="scope.row.is_mer_show"
              :active-value="1"
              :inactive-value="0"
              active-text="上架"
              inactive-text="下架"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="160" />
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              @click="onProDetails(scope.row.broadcast_goods_id)"
            >详情</el-button>
            <el-button
              type="text"
              size="small"
              @click="handleUpdate(scope.row.broadcast_goods_id)"
            >编辑</el-button>
            <el-button
              type="text"
              size="small"
              @click="handleDelete(scope.row.broadcast_goods_id, scope.$index)"
            >删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页组件 -->
      <div class="block">
        <el-pagination
          background
          :page-size="tableForm.limit"
          :current-page="tableForm.page"
          layout="total, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
    </el-card>
    <!-- 商品详情组件 -->
    <details-from ref="ProDetail" @getList="getList" />
    <!-- 批量添加商品组件 -->
    <batch-add ref="batchAdd" @get-list="getList" />
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
import { broadcastProListApi, changeProDisplayApi, updateBroadcastApi, broadcastProDeleteApi } from '@/api/marketing'
import { roterPre } from '@/settings'
import detailsFrom from './proDetail'
import batchAdd from '../batchAdd/index'
export default {
  name: 'BroadcastProList',
  components: { detailsFrom, batchAdd },
  data() {
    return {
      roterPre: roterPre,
      listLoading: true,
      dialogVisible: false,
      tableData: {
        data: [],
        total: 0
      },
      tableForm: {
        page: 1,
        limit: 20,
        status_tag: this.$route.query.status || '',
        keyword: '',
        broadcast_goods_id: this.$route.query.id || ''
      },
      broadcast_goods_id: this.$route.query.id || '',
    }
  },
  watch: {
    // 监听 broadcast_goods_id 变化，重新获取列表
    broadcast_goods_id: {
      handler(newValue) {
        this.getList()
      },
      immediate: false
    }
  },
  mounted() {
    this.getList()
  },
  methods: {
    // 重置搜索表单
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 批量添加直播商品
    batchAdd() {
      this.$refs.batchAdd.dialogVisible = true
      this.$refs.batchAdd.getList([])
    },
    // 查看商品详情
    onProDetails(id) {
      this.broadcast_goods_id = id
      this.$refs.ProDetail.dialogVisible = true
      this.$refs.ProDetail.getData(id)
    },
    // 编辑商品信息
    handleUpdate(id) {
      this.$modalForm(updateBroadcastApi(id)).then(() => this.getList())
    },
    // 删除商品
    handleDelete(id, idx) {
      this.$modalSureDelete().then(
        () => {
          broadcastProDeleteApi(id)
            .then(({ message }) => {
              this.$message.success(message)
              this.tableData.data.splice(idx, 1)
              this.getList()
            })
            .catch(({ message }) => {
              this.$message.error(message)
            })
        }
      )
    },
    // 获取商品列表
    getList(num) {
      this.listLoading = true
      this.tableForm.page = num || this.tableForm.page
      broadcastProListApi(this.tableForm)
        .then((res) => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch((res) => {
          this.listLoading = false
          this.$message.error(res.message)
        })
    },
    // 处理页码变化
    pageChange(page) {
      this.tableForm.page = page
      this.getList()
    },
    // 处理分页大小变化
    handleSizeChange(val) {
      this.tableForm.limit = val
      this.getList()
    },
    // 修改状态
    onchangeIsShow(row) {
      changeProDisplayApi(row.broadcast_goods_id, { is_show: row.is_mer_show })
        .then(({ message }) => {
          this.$message.success(message)
          this.getList()
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    }
  }
}
</script>

<style scoped lang="scss">
.modalbox ::v-deep .el-dialog {
  min-width: 550px;
}
.seachTiele {
  line-height: 35px;
}
.fa {
  color: #0a6aa1;
  display: block;
}
.sheng {
  color: #ff0000;
  display: block;
}
</style>

<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableForm" ref="searchForm" size="small" label-width="80px" inline>
        <el-form-item label="状态：" prop="status_tag">
          <el-select
            v-model="tableForm.status_tag"
            clearable
            placeholder="请选择"
            class="selWidth"
            @change="getList(1)"
          >
            <el-option label="全部" value="" />
            <el-option label="待审核" value="0" />
            <el-option label="审核已通过" value="1" />
            <el-option label="审核未通过" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item label="显示状态：" prop="show_type">
          <el-select v-model="tableForm.show_type" placeholder="请选择" clearable class="selWidth" @change="getList(1)">
            <el-option v-for="item in studioShowStatusList" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="直播状态：" prop="live_status">
          <el-select v-model="tableForm.live_status" placeholder="请选择" clearable class="selWidth" @change="getList(1)">
            <el-option v-for="item in studioStatusList" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input v-model="tableForm.keyword" @keyup.enter.native="getList(1)" placeholder="请输入直播间名称/ID/主播昵称/微信号" class="selWidth" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <div class="mb20">
        <router-link :to=" { path:`${roterPre}` + '/marketing/studio/creatStudio' } ">
          <el-button size="small" type="primary">创建直播间</el-button>
        </router-link>
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small" highlight-current-row>
        <el-table-column label="序号" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableForm.page - 1) * tableForm.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="直播间名称" min-width="120" />
        <el-table-column prop="broadcast_room_id" label="直播间ID" min-width="90" />
        <el-table-column prop="anchor_name" label="主播昵称" min-width="90" />
        <el-table-column prop="anchor_wechat" label="主播微信号" min-width="100" />
        <el-table-column prop="phone" label="主播手机号" min-width="150" />
        <el-table-column prop="start_time" min-width="150" label="直播开始时间" />
        <el-table-column prop="end_time" min-width="150" label="直播计划结束时间" />
        <el-table-column label="直播状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.live_status | broadcastStatusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" min-width="150" label="创建时间" />
        <el-table-column v-if="tableForm.status_tag !== -1" key="5" label="显示状态" min-width="100">
          <template slot-scope="scope">
            <el-switch v-model="scope.row.is_mer_show" :active-value="1" :inactive-value="0" :width="55" active-text="显示" inactive-text="隐藏" @click.native="onchangeIsShow(scope.row)" />
          </template>
        </el-table-column>
        <el-table-column key="15" label="开启收录" min-width="100">
          <template slot-scope="scope">
            <el-switch v-if="scope.row.status == 2" v-model="scope.row.is_feeds_public" :width="55" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" @click.native="onchangeIsFeeds(scope.row)" />
            <el-switch v-else v-model="scope.row.is_feeds_public" disabled :active-value="1" :width="55" :inactive-value="0" active-text="开启" inactive-text="关闭" />
          </template>
        </el-table-column>
        <el-table-column key="16" label="禁言" min-width="100">
          <template slot-scope="scope">
            <el-switch v-if="scope.row.status == 2" v-model="scope.row.close_comment" :width="55" :active-value="0" :inactive-value="1" active-text="开启" inactive-text="关闭" @click.native="onchangeIsCommen(scope.row)" />
            <el-switch v-else v-model="scope.row.close_comment" disabled :width="55" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" />
          </template>
        </el-table-column>
        <el-table-column key="17" label="客服开关" min-width="100">
          <template slot-scope="scope">
            <el-switch v-if="scope.row.status == 2" v-model="scope.row.close_kf" :width="55" :active-value="0" :inactive-value="1" active-text="开启" inactive-text="关闭" @click.native="onchangeIsKf(scope.row)" />
            <el-switch v-else v-model="scope.row.close_kf" disabled :width="55" :active-value="0" :inactive-value="1" active-text="开启" inactive-text="关闭" />
          </template>
        </el-table-column>
        <el-table-column label="商城显示" min-width="90">
          <template slot-scope="scope">
            <span v-if="scope.row.is_mer_show == 1 && scope.row.is_show == 1">显示</span>
            <span v-else-if="scope.row.is_mer_show == 0 && scope.row.is_show == 1">商户关闭</span>
            <span v-else-if="scope.row.is_mer_show == 1 && scope.row.is_show == 0">平台关闭</span>
            <span v-else>关闭</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.status | liveReviewStatusFilter }}</span>
            <span v-if="scope.row.status === -1" style="display: block; font-size: 12px;">原因 {{ scope.row.error_msg }}</span>
          </template>
        </el-table-column>
        <el-table-column label="直播商品" min-width="100">
          <template slot-scope="scope">
            <el-button v-if="scope.row.status === 2" key="4" type="text" size="small" @click="handleImport(scope.row)">导入</el-button>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="180" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onStudioDetails(scope.row.broadcast_room_id,'detail')">详情</el-button>
            <el-button type="text" size="small" @click="onEditAssistant(scope.row.broadcast_room_id)">编辑助手</el-button>
            <el-button v-if="scope.row.status === 2 && scope.row.live_status === 102" type="text" size="small" @click="onStudioDetails(scope.row.broadcast_room_id,'edit')">编辑</el-button>
            <el-button v-if="scope.row.live_status === 102 && (scope.row.status === -1 || scope.row.status === 0)" type="text" size="small" @click="onEdit(scope.row.broadcast_room_id)">编辑</el-button>
            <el-button type="text" size="small" @click="handleDelete(scope.row, scope.$index)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination background :page-size="tableForm.limit" :current-page="tableForm.page" layout="total, prev, pager, next, jumper" :total="tableData.total" @size-change="handleSizeChange" @current-change="pageChange" />
      </div>
    </el-card>
    <!--详情-->
    <details-from ref="studioDetail" />
    <!--导入直播商品-->
    <import-goods ref="uploadGoods" />
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
import { broadcastListApi, changeStudioRoomDisplayApi, broadcastDeleteApi, studioEdit, studioEditAssistant,
  openCollectionApi, openCommontApi, studioPushMessageApi, studioCloseKfApi } from '@/api/marketing'
import { roterPre } from '@/settings'
import detailsFrom from './studioDetail'
import importGoods from '@/components/importGoods/index'
export default {
  name: 'StudioList', components: { detailsFrom, importGoods },
  data() {
    return {
      Loading: false,
      roterPre: roterPre,
      dialogVisible: false,
      importVisible: false,
      listLoading: true,
      studioShowStatusList: [{ label: "显示", value: 3 },{ label: "商户关闭", value: 2 },{ label: "平台关闭", value: 1 },{ label: "关闭", value: 0 }],
      studioStatusList: [{ label: "正在进行", value: '101' },{ label: "已结束", value: '103' },{ label: "未开始", value: '102' },{ label: "已过期", value: '107' }],
      tableData: { data: [], total: 0 },
      tableForm: {
        page: 1,
        limit: 20,
        status_tag: this.$route.query.status ? this.$route.query.status : "",
        keyword: '',
        show_type: "",
        status: "",
        broadcast_room_id: this.$route.query.id ? this.$route.query.id : "",
      },
      broadcast_room_id: this.$route.query.id ? this.$route.query.id : "",
      liveRoomStatus: '',
    }
  },
  watch: {
    broadcast_room_id(newName, oldName) {
      this.getList("");
    }
  },
  mounted() {
    this.getList('')
  },
  methods: {
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 回
    // 详情
    onStudioDetails(id, type) {
      this.broadcast_room_id = id
      this.$refs.studioDetail.dialogVisible = true
      this.$refs.studioDetail.getData(id, type)
    },
    // 导入
    handleImport(row) {
      this.$refs.uploadGoods.importVisible = true
      this.$refs.uploadGoods.broadcast_id = row.broadcast_room_id
      this.$refs.uploadGoods.broadcast_room = row.name
      this.$refs.uploadGoods.image = ''
      localStorage.setItem('liveRoom_id', row.broadcast_room_id)
    },
    // 编辑助手
    onEditAssistant(id) {
      this.$modalForm(studioEditAssistant(id)).then(() => this.getList(''))
    },
    // 删除
    handleDelete(item, idx) {
      (item.status == 2 && item.live_status == 101) ?
        this.$confirm('该直播间正在进行直播，删除后不可恢复，您确认删除吗？', '提示', {
          confirmButtonText: '删除',
          cancelButtonText: '不删除',
          type: 'warning'
        }).then(() => {
          broadcastDeleteApi(item.broadcast_room_id).then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1)
          }).catch(({ message }) => {
            this.$message.error(message);
          });
        }).catch(action => {
          this.$message({
            type: 'info',
            message: '已取消'
          })
        }):
        this.$modalSureDelete().then(() => {
          broadcastDeleteApi(item.broadcast_room_id).then(({ message }) => {
            this.$message.success(message);
            this.tableData.data.splice(idx, 1)
            this.getList()
          }).catch(({ message }) => {
            this.$message.error(message);
          });
        }
        );
    },
    // 推送消息
    onPushMessage(id) {
      this.$confirm('给长期订阅用户推送消息？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        studioPushMessageApi(id).then(({ message }) => {
          this.$message.success(message);
        }).catch(({ message }) => {
          this.$message.error(message);
        });
      }).catch(action => {
        this.$message({
          type: 'info',
          message: '已取消'
        })
      })
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(studioEdit(id)).then(() => this.getList(''))
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableData.page = num ? num : this.tableData.page
      broadcastListApi(this.tableForm).then((res) => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.listLoading = false
      }).catch((res) => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableForm.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableForm.limit = val
      this.getList('')
    },
    // 修改状态
    onchangeIsShow(row) {
      changeStudioRoomDisplayApi(row.broadcast_room_id, {
        is_show: row.is_mer_show
      }).then(({ message }) => {
        this.$message.success(message)
        this.getList('')
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 开启收录
    onchangeIsFeeds(row) {
      openCollectionApi(row.broadcast_room_id, row.is_feeds_public).then(({ message }) => {
        this.$message.success(message)
        this.getList('')
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 禁言
    onchangeIsCommen(row) {
      openCommontApi(row.broadcast_room_id, row.close_comment).then(({ message }) => {
        this.$message.success(message)
        this.getList('')
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
    // 客服
    onchangeIsKf(row) {
      studioCloseKfApi(row.broadcast_room_id, row.close_kf).then(({ message }) => {
        this.$message.success(message)
        this.getList('')
      }).catch(({ message }) => {
        this.$message.error(message)
      })
    },
}
}
</script>

<style lang="scss" scoped>
@import '@/styles/form.scss';
.modalbox ::v-deep .el-dialog {
  min-width: 550px;
}

</style>

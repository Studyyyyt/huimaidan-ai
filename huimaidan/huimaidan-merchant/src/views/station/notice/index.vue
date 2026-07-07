<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="85px" inline>
        <el-form-item label="时间选择：">
          <el-date-picker v-model="timeVal" value-format="yyyy/MM/dd" format="yyyy/MM/dd" type="daterange" placement="bottom-end" placeholder="自定义时间" style="width: 280px;" :picker-options="pickerOptions" @change="onchangeTime" start-placeholder="开始时间" end-placeholder="结束时间" />
        </el-form-item>
        <el-form-item label="消息名称：" prop="keyword">
          <el-input v-model="tableFrom.keyword" @keyup.enter.native="getLists(1)" placeholder="请输入消息名称" clearable class="selWidth" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
      >
        <el-table-column label="序号" min-width="50">
          <template slot-scope="scope">
            <span>{{ scope.$index+(tableFrom.page - 1) * tableFrom.limit + 1 }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="消息名称"
          min-width="150"
        >
          <template slot-scope="scope">
            <div @click="getInfo(scope.row)">
              <span class="circle notice-title" :class="scope.row.is_read === 0 ? 'red' : 'gray'"></span>
              <span class="notice-title" :class="scope.row.is_read === 0 ? 'unread' : 'read'">{{ scope.row.notice_title }}</span>
            </div>
            </template>
        </el-table-column>
        <el-table-column
          label="日期"
          min-width="180"
        >
          <template slot-scope="scope">
            <span :class="scope.row.is_read === 0 ? 'unread' : 'read'">{{ scope.row.create_time }}</span>
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="操作" width="100">
          <template slot-scope="scope">
            <a v-db-click @click="getInfo(scope.row)" class="text-blue">查看</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="delNotice(scope.row.notice_log_id)" class="text-blue">删除</a>
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
    <el-drawer
      :with-header="false"
      :visible.sync="showInfo"
      size="1000px"
      direction="rtl"
      :before-close="handleClose"
    >
      <div class="info-title p-15 relative">
        {{ infoData.notice_title }}
        <i @click="handleClose" class="el-icon-close close-btn"></i>
      </div>
      <div class="info-content p-15" v-html="infoData.notice_content"></div>
    </el-drawer>
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
import { stationNewsList, stationNoticeRead, delNoticeApi, noticeDetailApi } from '@/api/system'
import { roterPre } from '@/settings'
import timeOptions from '@/utils/timeOptions';
export default {
  name: 'SystemLog',
  data() {
    return {
      pickerOptions: timeOptions,
      props: {
        emitPath: false
      },
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      dialogVisible: false,
      categoryList: [],
      merCateList: [],
      merSelect: [],
      fullscreenLoading: false,
      roterPre: roterPre,
      timeVal: [],
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
        keyword: '',
      },
      // 要展开的行，数值的元素是row的key值
      expands: [],
      idx: 0,
      showInfo: false,
      infoData: {}
    }
  },
  mounted() {
    this.getList('');
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
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.getLists(1);
    },
    // 列表
    getList(num) {
      let that = this
      that.listLoading = true
      that.tableFrom.page = num ? num : that.tableFrom.page;
      stationNewsList(that.tableFrom).then(res => {
        that.tableData.data = res.data.list
        that.tableData.total = res.data.count
        that.listLoading = false
        if(that.$route.params.id){
          that.handleRead({notice_log_id: that.$route.params.id,is_read: 0})
          that.tableData.data.forEach((item,index)  => {
            if (item.notice_log_id == that.$route.params.id) {
              that.idx = index
              that.expands.push(that.tableData.data[that.idx].notice_log_id);
            }
          })
        }
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
     // 列表
    getLists(num) {
      let that = this
      that.listLoading = true
      that.tableFrom.page = num ? num : that.tableFrom.page;
      stationNewsList(that.tableFrom).then(res => {
        that.tableData.data = res.data.list
        that.tableData.total = res.data.count
        that.listLoading = false
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getLists('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getLists(1)

    },
    handleRead(data){
      if(data.is_read === 0){
        stationNoticeRead(data.notice_log_id).then(res => {
          this.listLoading = false
          data.is_read = 1
          if(this.$route.params.id && this.tableFrom.page === 1){
            this.tableData.data[this.idx].is_read = 1
          }
        }).catch(res => {
          this.listLoading = false
          this.$message.error(res.message)
        })
      }
    },
    getInfo(data){
      noticeDetailApi(data.notice_log_id).then(res => {
        this.infoData = res.data;
        this.showInfo = true;
        if(data.is_read === 0){
          this.handleRead(data)
        }
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 删除
    delNotice(id){
      this.$modalSure("删除该公告吗").then(() => {
        delNoticeApi(id).then(({ message }) => {
            this.$message.success(message);
            this.getList();
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    handleClose(){
      this.showInfo = false
    }
  }
}
</script>

<style scoped lang="scss">
::v-deep .el-table__expanded-cell,::v-deep .el-table__expanded-cell:hover{
  background-color: #f4f5f9!important;
}
.read{
  color: #999;
}
.circle{
  display: inline-block;
  height: 8px;
  width: 8px;
  border-radius: 100%;
  box-shadow: 0 0 0 1px #fff;
}
.red{
  background: #ed4014;
}
.gray{
  background: #c5c8ce;
}
::v-deep .el-table th:nth-child(2){
  padding-left: 15px;
}
.text-blue{
  color: var(--prev-color-primary);
}
.p-15{
  padding: 15px;
}
.info-title{
  font-size: 16px;
  font-weight: 500;
  border-bottom: 1px solid #f2f2f2;
}
.relative{
  position: relative;
}
.info-content{
  word-break: break-all;
  white-space: pre-wrap;
  font-size: 16px;
  line-height: 24px;
  ::v-deep img{
    max-width: 100%;
  }
}

.close-btn{
  cursor: pointer;
  font-size: 20px;
  position: absolute;
  top: 17px;
  right: 14px;
}

.notice-title {
  cursor: pointer;
}
</style>

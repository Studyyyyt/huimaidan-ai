<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        inline
        size="small"
        label-width="85px"
      >
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
         <el-form-item label="开启状态：">
          <el-select
            v-model="tableFrom.status"
            placeholder="请选择"
            size="small"
            @change="getList(1)"
            class="selWidth">
            <el-option label="开启" value="1"></el-option>
            <el-option label="关闭" value="0"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="消息名称：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            @keyup.enter.native="getList(1)"
            placeholder="请输入消息名称搜索"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="createNews"
        >发布公告</el-button
      >
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="notice_title" show-overflow-tooltip label="公告名称" min-width="150" />
        <el-table-column label="店铺范围" min-width="120">
          <template slot-scope="scope">
            <span v-show="scope.row.type === 1">指定店铺</span>
            <span v-show="scope.row.type === 2">指定自营属性</span>
            <span v-show="scope.row.type === 3">指定分类</span>
            <span v-show="scope.row.type === 4">全部</span>
          </template>
        </el-table-column>
        <el-table-column label="开启状态" min-width="140">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              @change="onchangeStatus(scope.row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="发送日期" min-width="180" />
        <el-table-column fixed="right" label="操作" width="200">
          <template slot-scope="scope">
            <a v-db-click @click="getInfo(scope.row.notice_id)" class="text-blue">详情</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="edit(scope.row.notice_id)" class="text-blue">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="deleteNotice(scope.row.notice_id)" class="text-blue">删除</a>
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
      size="600px"
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
import { stationNewsList, noticeSwitchStatusApi, noticeDetail } from "@/api/system";
import { fromList } from "@/libs/constants.js";
import timeOptions from "@/utils/timeOptions";
import { roterPre } from "@/settings";
import { noticeDeleteApi } from "@/api/system";

export default {
  name: "SystemLog",
  data() {
    return {
      props: {
        emitPath: false
      },
      pickerOptions: timeOptions,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      fromList: fromList,
      tableFrom: {
        page: 1,
        limit: 20,
        store_name: "",
        keyword: "",
        date: "",
        status: ""
      },
      timeVal: "",
      roterPre: roterPre,
      infoData: {},
      showInfo: false,
    };
  },
  mounted() {
    this.getList("");
  },
  _methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.getList(1);
    },
    // 选择时间
    selectChange(tab) {
      this.tableFrom.date = tab;
      this.tableFrom.page = 1;
      this.timeVal = [];
      this.getList(1);
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      stationNewsList(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    },
    edit(id) {
      this.$router.push({ path: this.roterPre + '/station/notice/detail/' + id });
    },
    deleteNotice(id) {
      this.$modalSure().then(() => {
        noticeDeleteApi(id).then(res => {
          this.$message.success(res.message);
          this.getList();
        }).catch(res => {
          this.$message.error(res.message);
        });
      });
    },
    createNews() {
      this.$router.push({ path: this.roterPre + '/station/notice/detail' });
    },
    // 修改是否显示
    onchangeStatus(row) {
      noticeSwitchStatusApi(row.notice_id, row.status)
        .then(async (res) => {
          this.$message.success(res.message);
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    getInfo(id) {
      noticeDetail(id).then(res => {
        this.infoData = res.data;
        this.showInfo = true;
      }).catch(res => {
        this.$message.error(res.message);
      });
    },
    handleClose() {
      this.showInfo = false;
    }
  },
  get methods() {
    return this._methods;
  },
  set methods(value) {
    this._methods = value;
  },
};
</script>

<style scoped lang="scss">
::v-deep .el-input--medium .el-input__inner {
  line-height: 32px;
  height: 32px;
}
.demo-table-expand .el-form-item {
  width: 100%;
}
.dialog-footer {
  display: flex;
  justify-content: flex-end;
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
</style>

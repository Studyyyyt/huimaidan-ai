<template>
  <el-dialog v-if="dialogVisible" title="选择用户" :visible.sync="dialogVisible" width="900px">
    <div>
      <div class="container">
        <el-form size="small" inline>
          <el-form-item>
            <el-input
              v-model="tableFrom.search"
              placeholder="请输入用户昵称/ID/手机号"
              class="selWidth"
              clearable
              @keyup.enter.native="getList"
            >
            </el-input>
          </el-form-item>
          <el-form-item>
              <el-button type="primary" size="small" @click="getList">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small" style="height: 401px;">
        <el-table-column width="55">
          <template slot-scope="scope">
            <el-radio
              v-model="templateRadio"
              :label="scope.row.uid"
              @change.native="getTemplateRow(scope.row)"
            >&nbsp</el-radio>
          </template>
        </el-table-column>
        <el-table-column prop="uid" label="ID" min-width="80" />
        <el-table-column label="用户头像" min-width="100">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                style="width: 36px; height: 36px"
                :src="scope.row.avatar"
                :preview-src-list="[scope.row.avatar]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="nickname" label="用户昵称" min-width="120" />
        <el-table-column prop="phone" label="用户手机号" min-width="120" />
        <el-table-column label="用户类型" min-width="100">
          <template slot-scope="{row}">
            <span>{{ row.user_type === 'routine' ? '小程序' : row.user_type === 'wechat' ? '公众号' : row.user_type === 'app' ? 'App' : row.user_type === 'pc' ? 'PC' : 'H5' }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="is_svip" label="付费会员" min-width="100">
          <template slot-scope="{row}">
            <span>{{row.is_svip > 0 ? "是" : "否"}}</span>
          </template>
        </el-table-column>
        <el-table-column prop="now_money" label="余额" min-width="80" />
        <el-table-column prop="integral" label="积分" min-width="80" />
      </el-table>
      <div class="mt30 acea-row row-between">
        <el-pagination
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          layout="prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogVisible = false;" size="small">关闭</el-button>
          <el-button type="primary" @click="handleSubmit" size="small">确定</el-button>
        </div>
      </div>
    </div>
  </el-dialog>
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
import { searchMembers } from '@/api/order'
export default {
  name: 'OrderUserList',
  props:{

  },
  data() {
    return {
      dialogVisible: false,
      templateRadio: 0,
      merCateList: [],
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 6,
        search: '',
      },
      multipleSelection: {},
      checked: []
    }
  },
  mounted() {
    window.addEventListener('unload', (e) => this.unloadHandler(e))
  },
  methods: {
    getTemplateRow(row) {
      this.multipleSelection = row
    },
    handleSubmit() {
      this.dialogVisible = false
      this.$emit('getUserDetail', this.multipleSelection)
    },
    // 列表
    getList() {
      this.listLoading = true
      searchMembers(this.tableFrom)
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
    // 获取传过来的搜索条件
    getSearchData(keyword) {
      if(keyword)this.tableFrom.search = keyword
      this.getList()
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList()
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList()
    }
  }
}
</script>

<style scoped lang="scss">

</style>

<template>
  <div class="header-notice right-menu-item">
    <el-dropdown trigger="click" :hide-on-click="false" placement="top" @visible-change="getTodoList">
      <span class="el-dropdown-link">
        <el-badge v-if="count > 0" is-dot class="item" :value="count">
          <i class="el-icon-message-solid"></i>
        </el-badge>
        <span v-else class="item">
          <i class="el-icon-message-solid"></i>
        </span>
      </span>
      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item class="clearfix">
          <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-card v-if="dealtList.length > 0" class="box-card">
              <div slot="header" class="clearfix">
                <span>系统通知</span>
              </div>
              <router-link
                v-for="(item, i ) in dealtList" :key="i" class="text item_content"
                :to="{path: item.path}"
              >
                <div class="title">{{ item.title }}</div>
                <div class="message">{{ item.message }}</div>
              </router-link>
              <div v-if="list.length>3 && list.length!=dealtList.length" class="moreBtn" @click="dealtList=list">展开全部<span class="el-icon-arrow-down"></span></div>
            </el-card>
            <el-card v-else class="box-card">
              <div slot="header" class="clearfix">
                <span>系统通知</span>
              </div>
              <div class="tab-empty">
                <img src="@/assets/images/no-message.png" class="empty-img" alt="">
                <div class="empty-text">暂无系统通知</div>
              </div>
            </el-card>

          </el-tabs>
        </el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>
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
import { stationNewsList, needDealtList } from '@/api/system'
import { roterPre } from "@/settings";
export default {
  name: 'headerNotice',
  data() {
    return {
      activeName: 'second',
      messageList: [],
      dealtList: [],
      list: [],
      count: 0,
      tabPosition: 'right',
      roterPre: roterPre,
      type: 'message',
      listMore: false,
    }
  },
  computed: {

  },
  watch: {

  },
  mounted() {
    // this.getList();
    // this.getTodoList()
  },
  methods: {
    handleClick(tab, event) {

    },
    // 列表
    getList() {
      stationNewsList({is_read: 0}).then(res => {
        this.messageList = res.data.list
        this.count = res.data.count;
      }).catch(res => {
      })
    },
     // 待办列表
    getTodoList(){
      needDealtList().then(res => {
        this.list = res.data
        this.dealtList = res.data.length>3 ? res.data.slice(0,3) : res.data
      }).catch(res => {
      })
    },
    HandleDelete(i){
      this.messageList.splice(i,1)
    },
    getData(){

    }
  }
}
</script>

<style lang="scss" scoped>
.header-notice{
  position: relative;
  cursor: pointer;
}
.el-dropdown-menu__item{
  background-color: #ffffff;
  padding: 0;
  border-radius: 6px;
}
.item_content {
  display: inline-block;
  white-space: nowrap;
  width: 100%;
  overflow: hidden;
  text-overflow:ellipsis;
  margin-top: 10px;
  line-height: 20px;
  font-size: 13px;
}
.item_content .title{
  color: #333333;
  font-weight: bold;
}
.item_content .message{
  color: #666666
}
.moreBtn{
  color: #666666;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  cursor: pointer;
}
::v-deep .el-card__body{
  padding: 0 24px 10px;
}
.clearfix:before,
.clearfix:after {
  display: table;
  content: "";
}
.clearfix:after {
  clear: both
}
.box-card {
  width: 240px;
}
::v-deep .el-tabs__header{
  margin: 0;
}
::v-deep .el-card__header{
  padding: 10px 24px 0;
  font-weight: bold;
}
.tab-empty {
  text-align: center;
  margin-top: 15px;
}
.empty-text {
  color: #999999;
  font-size: 12px;
}
.empty-img {
  display: inline-block;
  width: 160px;
  height: 123px;
}
</style>

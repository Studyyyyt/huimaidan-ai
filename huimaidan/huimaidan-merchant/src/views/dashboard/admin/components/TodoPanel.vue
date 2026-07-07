<template>
  <div>
    <el-row :gutter="14">
      <!-- 待办事项 -->
      <el-col
        :xs="{span: 24}"
        :sm="{span: 24}"
        :md="{span: 12}"
        :lg="{span: 12}"
        :xl="{span: 12}"
      >
        <el-row class="todo-count">
          <el-col>
            <el-row :span="24">
              <div class="title">待办事项</div>
            </el-row>
            <el-row :span="24" class="panel-count">
              <el-col v-for="(item, index) in todoList" :key="index" class="item" :span="8">
                <el-dropdown placement="bottom">
                  <router-link :to="{path: item.path}" class="item-link">
                    <div class="icon" :class="'icon'+index">
                      <span class="iconfont" :class="item.icon"></span>
                    </div>
                    <div class="desc">
                      <div class="text" :title="item.title">{{item.title}}</div>
                      <div class="count">{{item.count}}</div>
                    </div>
                  </router-link>
                  <el-dropdown-menu v-show="item.children.length>0" :class="item.children.length>0 ? 'dropdown': 'nodrop'">
                    <el-dropdown-item v-for="(itm, idx) in item.children" :key="idx">
                      <router-link :to="{path: itm.path}">
                        {{itm.message}}
                      </router-link>
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </el-dropdown>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </el-col>
      <!--商户销量金额排行-->
      <el-col
        :xs="{span: 24}"
        :sm="{span: 24}"
        :md="{span: 12}"
        :lg="{span: 12}"
        :xl="{span: 12}"
      >
      <el-row class="sales-amount">
        <el-col>
          <el-row class="panel-title">
            <el-col :span="12">
              <span>商品销售金额排行TOP10</span>
            </el-col>
            <el-col :span="12" class="align-right">
              <el-radio-group v-model="rankingTime" size="mini" @change="getSalesRank(rankingTime)">
                <el-radio-button v-for="item in timeList" :key="item.value" :label="item.value">{{ item.label }}</el-radio-button>
              </el-radio-group>
            </el-col>
          </el-row>
          <div class="grid-title-count">
            <el-row class="grid-title">
              <el-col :span="2">排名</el-col>
              <el-col :span="18">商品名称</el-col>
              <el-col :span="4">销售金额</el-col>
            </el-row>
          </div>
          <div class="grid-list-content">
            <el-row v-for="(list, index) in salesRankingList" :key="index" class="grid-count">
              <el-col :span="2" class="grid-list">
                <span :class="'gray'+index" class="navy-blue">{{ index+1 }}</span>
              </el-col>
              <el-col class="grid-list" :span="18">
                <img :src="list.product && list.product.image" alt>
                <span>{{ list.product && list.product.store_name }}</span>
              </el-col>
              <el-col class="grid-list" :span="4">{{ list.number }}</el-col>
            </el-row>
          </div>
        </el-col>
      </el-row>
      </el-col>
    </el-row>
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
import {
  toDoDataApi, getSalesRankApi
} from '@/api/dashboard'
export default {
  name: 'ToDoPanel',
  components: {

  },
  data() {
    return {
      todoList: [],
      salesRankingList: [],
      rankingTime: 'year',
      timeList: [
        { value: 'lately7', label: '近7天' },
        { value: 'lately30', label: '近30天' },
        { value: 'month', label: '本月' },
        { value: 'year', label: '本年' }
      ],
    }
  },
  activated() {

  },
  mounted() {
    this.getTodoData()
    this.getSalesRank(this.rankingTime)
  },
  methods: {
    getTodoData() {
      toDoDataApi()
        .then(res => {
          this.todoList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    getSalesRank(date) {
      getSalesRankApi({date: date})
        .then(res => {
          this.salesRankingList = res.data.list
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
  }
}
</script>
<style lang="scss" scoped>
  .todo-count{
    background: #ffffff;
    border-radius: 4px;
    margin-top: 14px;
    .title{
      padding: 25px 30px;
      color: #000000;
      font-size: 16px;
      font-weight: bold;
    }
  }
  .panel-count{
    padding: 0 47px 40px;
    .item{
      margin-top: 50px;
      &:nth-child(-n+3){
        margin-top: 0;
      }
      .item-link{
        display: flex;
        align-items: center;
      }
      .icon{
        width: 50px;
        height: 50px;
        margin-right: 16px;
        text-align: center;
        line-height: 50px;
        .iconfont{
          font-size: 28px;
        }
        &.icon0{
          background: #ECFBFB;
          .iconfont{
            color:#0FC6C2;
          }
        }
        &.icon1{
          background: #EFF7FF;
          .iconfont{
            color:#3491FA;
          }
        }
        &.icon2{
          background: #F9F5FE;
          .iconfont{
            color:#B27FEB;
          }
        }
        &.icon3{
          background: #F0F4FF;
          .iconfont{
            color:#4073FA;
          }
        }
        &.icon4{
          background: #FFFAED;
          .iconfont{
            color:#F7BA1E;
          }
        }
        &.icon5{
          background: #FFF5EB;
          .iconfont{
            color:#FF7D00;
          }
        }
        &.icon6{
          background: #F8FDED;
          .iconfont{
            color:#9FDB1D;
          }
        }
        &.icon7{
          background: #ECFBFB;
          .iconfont{
            color:#0FC6C2;
          }
        }
        &.icon8{
          background: #F9F5FE;
          .iconfont{
            color:#B27FEB;
          }
        }
        &.icon9{
          background: #F0F4FF;
          .iconfont{
            color:#4073FA;
          }
        }
        &.icon10{
          background: #FFF5EB;
          .iconfont{
            color:#FF7D00;
          }
        }
      }
      .desc{
        .text{
          color: #606266;
          font-size: 14px;
          text-overflow: ellipsis;
          max-width: 100px;
          overflow: hidden;
          white-space: nowrap;
        }
        .count{
          margin-top: 8px;
          color: #333333;
          font-size: 18px;
          font-weight: bold;
        }
      }
    }
  }
  .dropdown{
    border: none;
    // width: 130px;
    padding: 10px 5px;
    box-shadow: 0px 0px 14px 0px rgba(0,53,132,0.16);
    border-radius: 6px;

    .el-dropdown-menu__item{
      font-size: 13px;
      color: #626266;
      line-height: 26px;
      background: #ffffff;
      margin-top: 10px;
      &:hover{
        color: #4073FA;
      }
    }
  }
  .dropdown ::v-deep .popper__arrow{
    box-shadow: none;
    filter: none;
  }
  .nodrop{
    padding: 0;
    box-shadow: none;
    opacity: 0;
  }
  .sales-amount{
    background: #ffffff;
    border-radius: 4px;
    margin-top: 14px;
  }
.panel-title {
  padding: 25px 35px 10px;
  color: #000;
  overflow: hidden;
  font-weight: bold;
  font-size: 16px;
}
::v-deep .el-radio-button__orig-radio:checked+.el-radio-button__inner{
  color: #fff;
  background-color: #6394F9;
  border-color: #6394F9;
  -webkit-box-shadow: -1px 0 0 0 #6394F9;
  box-shadow: -1px 0 0 0 #6394F9;
}
.select-icon-date {
  opacity: 0.6;
}
.grid-title {
  font-weight: bold;
  padding: 10px 0;
  margin: 0 35px;
  font-size: 12px;
  border-bottom: 1px solid #EBEEF5;
}
.bg-blue {
  background-color: #eff4fe;
}
.bg-green {
  background-color: #effbf6;
}
.bg-gray-dark {
  background-color: #eff1f4;
}
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb{
  -webkit-box-shadow: inset 0 0 6px transparent;;
}
::-webkit-scrollbar {
  width: 4px!important; /*对垂直流动条有效*/
}
.grid-list-content {
  padding: 0 35px;
  height: 261px;
  overflow-x: hidden;
  overflow-y: scroll;
  .el-row {
    border-bottom: 1px solid #EBEEF5;
    &:last-child {
      margin-bottom: 0;
      border-bottom: none;
    }
  }
  .grid-list {
    padding: 20px 13px;
    color: #000000;
    &:first-child {
      padding-left: 0;
      span {
        display: block;
        width: 18px;
        line-height: 18px;
        text-align: center;
        color: #fff;
        border-radius: 100%;
        -webkit-border-radius: 100%;
        font-size: 12px;
        &.navy-blue {
          background: #D0D0D0;
        }
        &.gray0 {
          background: #EBCA80;
        }
        &.gray1 {
          background: #ABB4C7;
        }
        &.gray2 {
          background: #CCB3A0;
        }
      }
    }
    &:nth-child(2) {
      position: relative;
      padding-left: 50px;
      span {
        display: inline-block;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 13px;
      }
      img {
        width: 30px;
        height: 30px;
        position: absolute;
        left: 0;
        top: 15px;
        border-radius: 2px;
      }
    }
    &:last-child {
      font-size: 13px;
    }
  }
}
.align-right{
  text-align: right;
}
</style>

<template>
  <div class="divBox">
    <el-row class="">
      <div class="right-wrapper">
        <el-card>
          <el-form
            size="small"
            inline
            label-width="85px"
            @submit.native.prevent
          >
            <el-form-item label="模板名称：">
              <el-input
                v-model="diyFrom.name"
                @keyup.enter.native="refreshList"
                placeholder="请输入模板名称"
                clearable
                class="selWidth"
              />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" size="small" @click="refreshList"
                >搜索</el-button
              >
              <!-- <el-button size="small" @click="searchReset()">重置</el-button> -->
            </el-form-item>
          </el-form>
        </el-card>
        <el-card class="mt14" style="min-height: 74vh;">
          <div class="acea-row relative">
            <div style="width: 350px;height:550px;margin-right: 20px;position: relative;" v-if="isDiy">
              <iframe id="iframe" class="iframe-box" :src="imgUrl" frameborder="0" ref="iframe"></iframe>
              <div class="tips-box">
                <span class="small-tips">若页面未加载出，请前往系统配置页面填写网站域名</span>
                <el-button type="text" @click="gotoSystemConfig">点击前往</el-button>
              </div>
            </div>
            <div class="table">
              <div class="acea-row row-between-wrapper">
                <el-row type="flex">
                  <div>
                    <div class="acea-row row-between-wrapper">
                      <div class="button acea-row row-middle">
                        <el-button type="primary" size="small" @click="add" style="font-size: 12px;"><i class="el-icon-plus" style="margin-right: 4px;"/>添加</el-button>
                      </div>
                      <div class="small-tips">&nbsp;&nbsp;注：初次进入该页面，可直接添加商城首页模板，也可先复制默认模板，再编辑默认模板保存后设为首页。</div>
                    </div>
                  </div>
                </el-row>
              </div>
              <el-table
                class="tables"
                :data="list"
                ref="table"
                highlight-current-row
                size="small"
                v-loading="loading"
              >
                <el-table-column prop="id" label="页面ID" min-width="50" />
                <el-table-column prop="name" label="模板名称" min-width="100" />
                <el-table-column prop="add_time" label="添加时间" min-width="120" />
                <el-table-column prop="update_time" label="更新时间" min-width="120" />
                <el-table-column label="操作" min-width="180" fixed="right">
                  <template slot-scope="scope">
                    <el-button type="text" size="small" @click="edit(scope.row)">编辑</el-button>
                    <el-button type="text" size="small" @click="del(scope.row.id, scope.$index)">删除</el-button>
                    <el-button type="text" size="small" v-if="scope.row.status != 1" @click="setStatus(scope.row, scope.$index)">设为首页</el-button>
                    <el-button type="text" size="small" v-if="scope.row.is_diy" class="copy-data" @click="preview(scope.row)">预览</el-button>
                    <div style="display: inline-block" v-if="!scope.row.is_diy">
                      <el-button type="text" size="small" @click="recovery(scope.row, scope.$index)">恢复初始设置</el-button>
                    </div>
                    <el-button type="text" size="small" @click="onDiyCopy(scope.row)">复制</el-button>
                  </template>
                </el-table-column>
              </el-table>
              <div class="block">
                <el-pagination
                  background
                  :page-size="diyFrom.limit"
                  :current-page="diyFrom.page"
                  layout="total, prev, pager, next, jumper"
                  :total="total"
                  @size-change="handleSizeChange"
                  @current-change="pageChange"
                />
              </div>
            </div>
          </div>
        </el-card>
      </div>
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
import SettingMer from '@/libs/settingMer'
import { roterPre } from '@/settings'
import { diyList, diyDel, setStatus, recovery, diyCopy } from "@/api/diy";
import { mapState } from "vuex";
import shopStreet from './shopStreet'
import users from './users'
export default {
  name: "devise_list",
  computed: {
    ...mapState('layout', [
      'menuCollapse'
    ])
  },
  components: {
    shopStreet,
    users
  },
  data() {
    return {
      grid: {
        sm: 10,
        md: 12,
        lg: 19,
      },
      loading: false,
      theme3: "light",
      roterPre: roterPre,
      list: [],
      imgUrl:'',
      modal: false,
      BaseURL: SettingMer.httpUrl || 'http://localhost:8080',
      cardShow: 0,
      loadingExist: false,
      isDiy: 1,
      qrcodeImg: '',
      diyFrom: {
        page: 1,
        limit: 10,
        name: "",
      },
      total: 0,
    };
  },
  created() {
    this.getList();
  },
  mounted: function() {
    this.$store.commit("settings/STOREDIY", 0);
  },
  methods: {
    gotoSystemConfig() {
      this.$router.push({
        path: `${roterPre}/systemForm/Basics/system_tabs`,
      });
    },
    preview(row){
      let time = new Date().getTime() * 1000
      let imgUrl = `${this.BaseURL}/pages/index/index?inner_frame=1&diyId=${row.id}&time=${time}`;
      this.imgUrl = imgUrl;
    },
    // 获取列表
    getList() {
      let storage = window.localStorage;
      this.imgUrl = storage.getItem('imgUrl');
      let that = this
      this.loading = true;
      diyList(this.diyFrom).then((res) => {
        this.loading = false;
        let data = res.data;
        this.list = data.list;
        this.total = data.count;
        let time = new Date().getTime() * 1000
        let imgUrl = `${that.BaseURL}/pages/index/index?inner_frame=1&time=${time}`;
        storage.setItem('imgUrl',imgUrl)
        that.imgUrl = imgUrl;
      }).catch(({ message }) => {
        this.loading = false;
        this.$message.error(message)
      });
    },
    refreshList() {
      this.diyFrom.page = 1;
      this.getList();
    },
    pageChange(status) {
      this.diyFrom.page = status;
      this.getList();
    },
    handleSizeChange(val) {
      this.diyFrom.limit = val
      this.getList()
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: `${roterPre}/setting/diy/index`,
        query: { id: row.id, name: row.template_name || "moren", types: 1 },
      });
    },
    // 添加
    add() {
      this.$router.push({
        path: `${roterPre}/setting/diy/index`,
        query: { id: 0, name: "首页", types: 1 },
      });
    },
    // 删除
    del(id,idx) {
      this.$modalSure('删除模板吗').then(() => {
        diyDel(id).then(({ message }) => {
          this.$message.success(message)
          this.getList()
        }).catch(({ message }) => {
          this.$message.error(message)
        })
      })
    },
    // 使用模板
    async setStatus(row) {
      let that = this
      that.$modalSure("把该模板设为首页").then(() => {
        setStatus(row.id).then((res) => {
            that.$message.success(res.message);
            that.getList();
          }).catch((res) => {
            that.$message.error(res.message);
          });
        })
    },
    recovery(row) {
      recovery(row.id).then((res) => {
        this.$message.success(res.message);
        this.getList();
      });
    },
    onDiyCopy(row) {
      diyCopy(row.id).then(() => {
        this.getList()
      }).catch(res => {
        this.$message.error(res.message);
      })
    }
  },
};
</script>

<style scoped lang="scss">
  /* 用来设置当前页面element全局table 选中某行时的背景色*/
  .el-table__body tr.current-row>td{
    background-color: #69A8EA !important;
  }
  ::v-deep .spike-bd .spike-distance .bg-red{
    width: 45px;
  }
  .product_tabs{
    padding: 15px 32px 0;
    background: #fff;
    text-align: right;
  }
  .el-menu-item{
    height: 47px;
  }
  .el-menu-item.is-active::after{
    content: "";
    display: block;
    width: 2px;
    position: absolute;
    top: 0;
    bottom: 0;
    right: 0;
    background: var(--prev-color-primary)!important;
  }
  .tables{
    margin-top: 17px;
  }
  .ivu-mt{
    // background-color: #fff;
    // padding-bottom: 50px;
  }
  .bnt{
    width: 80px!important;
  }
  .iframe-box{
    width: 350px;
    height: 100%;
    border-radius: 10px;
    border: 1px solid #eee;
  }
  .mask{
    position: absolute;
    left:0;
    width: 100%;
    top:0;
    height: 100%;
    background-color: rgba(0,0,0,0);
  }
  .table{
    width: calc(100% - 390px);
  }
  .right-wrapper{
    // width: calc(100% - 100px);
  }
  ::v-deep.right-wrapper  {
    .el-form-item.el-form-item--small {
      margin-bottom: 0;
    }

    .el-pagination {
      margin-top: 14px;
    }
  }
  .code{
    position: relative;
  }
  .QRpic {
    width: 160px;
    height: 160px;

    img {
      width: 100%;
      height: 100%;
    }
  }
  .left-wrapper {
    width: 100px;
    background: #fff;
    border-right: 1px solid #dcdee2;
  }
  .picCon{
    width: 280px;
    height: 510px;
    background: #FFFFFF;
    border: 1px solid #EEEEEE;
    border-radius: 25px;
    .pictrue{
      width: 250px;
      height: 417px;
      border: 1px solid #EEEEEE;
      opacity: 1;
      border-radius: 10px;
      margin: 30px auto 0 auto;
      img{
        width: 100%;
        height: 100%;
        border-radius: 10px;
      }
    }
    .circle{
      width: 36px;
      height: 36px;
      background: #FFFFFF;
      border: 1px solid #EEEEEE;
      border-radius: 50%;
      margin: 13px auto 0 auto;
    }
  }

  .small-tips {
    color:#F56464;
    font-size: 13px;
  }

  .tips-box {
    margin-top: 10px;
    text-align: center;
    .small-tips {
      font-size: 14px;
      color: red;
    }
  }
</style>

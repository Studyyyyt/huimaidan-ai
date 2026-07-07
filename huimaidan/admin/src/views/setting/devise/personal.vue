<template>
  <div class="divBox">
    <el-row class="ivu-mt box-wrapper acea-row">
      <div class="right-wrapper">
        <div>
          <div
            class="product_tabs"
            :style="'padding-right:'+(menuCollapse?105:20)+'px'"
          >
          </div>
        </div>
        <users ref="users" @parentFun="getChildData"></users>
      </div>
    </el-row>

    <div class="footer-btn">
      <el-button type="primary" size="small" @click="submit" :loading="loadingExist">保存</el-button>
      <el-button size="small" @click="reast">重置</el-button>
    </div>
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

import { mapState } from "vuex";
import users from './users'
export default {
  name: "personal",
  computed: {
    ...mapState('layout', [
      'menuCollapse'
    ])
  },
  components: {
    users
  },
  data() {
    return {
      loadingExist: false,
    };
  },
  methods: {
    getChildData(e){
      this.loadingExist = e
    },
    submit(){
      this.$refs.users.onSubmit()
    },
    reast(){
      this.$refs.users.getInfo();
    },
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
    margin-top: 20px;
  }
  .ivu-mt{
    background-color: #fff;
    padding-bottom: 50px;
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
    width: calc(100% - 100px);
    height: 74vh;
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
  .footer-btn{
    height: 72px;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>

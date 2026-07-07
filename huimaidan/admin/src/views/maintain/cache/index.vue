<template>
  <div class="divBox">
    <div class="box-card">
      <el-card>
        <div class="acea-row row-between">
          <span class="header-title">{{ $route.meta.title }}</span>
          <span class="clear_tit">
            <i class="el-icon-info" style="color: #ED4014" />
            <span>清除数据请谨慎，清除就无法恢复哦！</span>
          </span>
        </div>
      </el-card>
      <el-card class="mt15">
        <el-row :gutter="24">
          <el-col
            v-bind="grid"
            class="mb20"
            v-for="(item, index) in tabList"
            :key="index"
          >
            <div class="clear_box">
              <span class="clear_box_sp1" v-text="item.title"></span>
              <span class="clear_box_sp2" v-text="item.tlt"></span>
              <el-button
                size="small"
                :type="item.typeName"
                v-text="item.typeName === 'primary' ? '立即更换' : '立即清理'"
                @click="onChange(item)"
              ></el-button>
            </div>
          </el-col>
        </el-row>
      </el-card>
      <!-- 更换域名-->
      <el-dialog
        :visible.sync="modals"
        class="tableBox"
        title="更换域名"
        width="540px"
        :close-on-click-modal="false"
      >
        <div class="acea-row row-column">
          <span
            >替换规则：将数据库中被换域名（旧域名），修改为替换域名（新域名）。</span
          >
          <span class="mb15"
            >注意事项：此功能仅限于数据量较少时使用，当数据量过大，可能会操作失败无法替换</span
          >
          <el-input
            class="mb15"
            v-model="value5"
            type="text"
            placeholder="请输入原始域名..."
          />
          <el-input
            v-model="value6"
            type="text"
            placeholder="请输入更换后的域名..."
          />
        </div>
        <span slot="footer" class="dialog-footer">
          <el-button size="small" @click="modals = false">取 消</el-button>
          <el-button size="small" type="primary" @click="changeYU"
            >确 定</el-button
          >
        </span>
      </el-dialog>
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
import { clearCacheApi, replaceSiteUrlApi } from "@/api/maintain";
export default {
  name: "DataCache",
  data() {
    return {
      value5: "",
      value6: "",
      modals: false,
      grid: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24
      },
      tabList: [
        {
          title: "更换域名",
          tlt: "替换所有本地上传的图片域名",
          typeName: "primary",
          type: "11"
        },
        {
          title: "清除全部缓存",
          tlt: "清除所有商城数据，谨慎操作",
          typeName: "error",
          type: "1"
        },
        {
          title: "清理店铺缓存",
          tlt: "清除所有店铺数据，谨慎操作",
          typeName: "error",
          type: "2"
        },
        {
          title: "清理配置缓存",
          tlt: "清除配置缓存,谨慎操作",
          typeName: "error",
          type: "3"
        }
      ]
    };
  },
  mounted() {},
  methods: {
    // 清除数据
    onChange(item) {
      if (item.type === "11") {
        this.modals = true;
      } else {
        this.clearCache(item.type);
      }
    },
    // 更换域名
    changeYU() {
      replaceSiteUrlApi({ origin: this.value5, replace: this.value6 })
        .then(res => {
          this.modals = false;
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 清除缓存
    clearCache(type) {
      return new Promise((resolve, reject) => {
        this.$confirm(`确定清除吗？清除后无法恢复请谨慎操作！`, "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        })
          .then(() => {
            clearCacheApi({ type: type })
              .then(({ message }) => {
                this.$message.success(message);
              })
              .catch(({ message }) => {
                this.$message.error(message);
              });
          })
          .catch(action => {
            this.$message({
              type: "info",
              message: "已取消"
            });
          });
      });
    }
  }
};
</script>

<style scoped lang="scss">
.card_container {
  margin-top: 150px;
  text-align: center;
}
.header-title {
  font-size: 20px;
}
.clear_tit {
  align-items: center;
  span {
    font-size: 14px;
    color: #ed4014;
  }
}
.clear_box {
  border: 1px solid #dadfe6;
  border-radius: 3px;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 10px;
  box-sizing: border-box;
  .clear_box_sp1 {
    font-size: 16px;
    color: #000000;
    display: block;
  }
  .clear_box_sp2 {
    font-size: 14px;
    color: #808695;
    display: block;
    margin: 12px 0;
  }
}
.clear_box ::v-deep .ivu-btn-error {
  color: #fff;
  background-color: #ed4014;
  border-color: #ed4014;
}
.product_tabs ::v-deep .ivu-page-header-title {
  margin-bottom: 0 !important;
}
</style>

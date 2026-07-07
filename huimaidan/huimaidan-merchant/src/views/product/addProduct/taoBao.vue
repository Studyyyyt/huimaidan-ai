<template>
  <div class="Box">
    <el-dialog
      v-if="modals"
      :visible.sync="modals"
      width="900px"
      title="商品采集"
      custom-class="dialog-scustom"
    >
      <div>
        <div class="acea-row row-middle">
          <div>平台一号通采集次数剩余：</div>
          <span class="warning mr14">{{ count }} 次</span>
          <router-link
            class="link"
            :to="{ path: roterPre + '/setting/sms/sms_pay/index?type=copy' }"
            target="_blank"
          >
            购买采集次数
          </router-link>
        </div>
        <div class="acea-row row-middle mt20">
          <div class="mr14">
            自有一号通采集次数剩余次数，需登录一号通后进行查看！
          </div>
          <router-link
            class="link"
            :to="{ path: roterPre + '/setting/sms/sms_account/index' }"
            target="_blank"
          >
            点击登录
          </router-link>
        </div>
        <div class="acea-row row-middle mt20">
          <div class="mr14">系统默认先消耗旧版一号通采集次数！</div>
          <span class="link" @click="openRecords">
            查看商品采集记录
          </span>
        </div>
        <!-- <div>复制淘宝、天猫、京东、苏宁、1688；</div>
        生成的商品默认是没有上架的，请手动上架商品！
        <span style="color: #F56464;">商品复制次数剩余：{{ count }}次</span>
         <router-link :to="{path: roterPre + '/setting/sms/sms_pay/index?type=copy'}" target="_blank">
            <el-button size="small" type="text">增加采集次数</el-button>
          </router-link>
          <el-button size="small" type="primary" style="margin-left: 15px;" @click="openRecords">查看商品复制记录</el-button> -->
      </div>
      <el-form
        ref="formValidate"
        class="formValidate"
        label-width="90px"
        label-position="right"
        @submit.native.prevent
      >
        <el-form-item label="链接地址：" required>
          <el-input
            v-model="soure_link"
            search
            placeholder="请输入商品详情链接地址"
            class="numPut"
          />
          <!-- <el-button :loading="loading" size="small" type="primary" @click="add">确定</el-button> -->
          <div class="desc">
            系统支持采集淘宝、天猫、京东、苏宁、1688的商品信息
          </div>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button size="small" @click="modals = false">取消</el-button>
        <el-button type="primary" size="small" :loading="loading" @click="add"
          >确定</el-button
        >
      </div>
    </el-dialog>
    <!--商品复制记录-->
    <copy-record ref="copyRecord" />
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
import { crawlFromApi, productCopyCountApi } from "@/api/product";
import copyRecord from "./copyRecord";
import { roterPre } from "@/settings";
export default {
  name: "CopyTaoBao",
  components: { copyRecord },
  data() {
    return {
      roterPre: roterPre,
      modals: false,
      loading: false,
      count: 0,
      soure_link: "",
      artFrom: {
        type: "taobao",
        url: ""
      }
    };
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {
    this.getCopyCount();
  },
  methods: {
    // 获取剩余复制次数
    getCopyCount() {
      productCopyCountApi().then(res => {
        this.count = res.data.count;
      });
    },
    // 查看复制记录
    openRecords() {
      this.$refs.copyRecord.getRecord();
    },
    // 生成表单
    add() {
      if (this.soure_link) {
        var reg = /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?/;
        if (!reg.test(this.soure_link)) {
          return this.$message.warning("请输入以http开头的地址！");
        }
        this.artFrom.url = this.soure_link;
        this.loading = true;
        crawlFromApi(this.artFrom)
          .then(res => {
            const info = res.data;
            this.modals = false;
            this.$emit("info-data", info);
          })
          .catch(res => {
            this.$message.error(res.message);
            this.loading = false;
          });
      } else {
        this.$message.warning("请输入链接地址！");
      }
    }
  }
};
</script>

<style scoped lang="scss">
.formValidate {
  margin-top: 40px;
}
.dialog-scustom {
  height: 600px;
}
.warning {
  color: #ed4014;
}
.link {
  color: var(--prev-color-primary);
  cursor: pointer;
  // font-size: 13px;
}
.desc {
  color: #999;
}
</style>

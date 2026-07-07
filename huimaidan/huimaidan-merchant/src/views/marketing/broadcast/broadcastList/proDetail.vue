<template>
  <div class="divBox">
    <!-- 使用 v-if 控制对话框的渲染 -->
    <el-dialog title="商品信息" :visible.sync="dialogVisible" width="650px" v-if="dialogVisible">
      <!-- 使用 v-loading 显示加载状态 -->
      <div v-loading="loading">
        <div class="box-container">
          <div class="list sp">
            <label class="name">商品名称：</label>
            <span class="info">{{ FormData.name }}</span>
          </div>
          <div class="list sp">
            <label class="name">直播价：</label>
            <span class="info">{{ FormData.price }}</span>
          </div>
          <div class="list sp">
            <label class="name">库存：</label>
            <span v-if="FormData.product" class="info">{{ FormData.product.stock }}</span>
          </div>
          <div class="list sp100 image">
            <label class="name">商品图：</label>
            <img
              v-if="FormData.product"
              style="max-width: 150px; height: 80px;"
              :src=" FormData.cover_img"
            />
          </div>
          <div class="list sp">
            <label class="name">审核结果：</label>
            <span class="info">{{ FormData.status | liveReviewStatusFilter }}</span>
          </div>
          <div class="list sp100">
            <label class="name">备注：</label>
            <span class="info">
              <el-input v-model="FormData.mark" type="textarea" :rows="1" />
            </span>
          </div>
          <!-- 提交按钮 -->
          <div class="list sp100 mt20">
            <el-button
              type="button"
              class="el-button el-button--primary el-button--medium"
              style="width: 100%;"
              :disabled="FormData.mark == ''"
              @click="handleRemarks()"
            >提交</el-button>
          </div>
        </div>
      </div>
    </el-dialog>
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
import { broadcastProDetailApi, broadcastProRemarksApi } from "@/api/marketing";

export default {
  name: "BroadcastProDetail",
  data() {
    return {
      dialogVisible: false,
      option: {
        form: {
          labelWidth: "150px",
        },
      },
      FormData: {
        product: { stock: "", image: "" },
      },
      loading: false,
    };
  },
  mounted() {},
  methods: {
    /**
     * 获取商品详情数据
     * @param {number} id - 商品ID
     */
     async getData(id) {
      try {
        this.loading = true;
        const res = await broadcastProDetailApi(id);
        this.FormData = res.data;
      } catch (res) {
        this.handleError(res);
      } finally {
        this.loading = false;
      }
    },
    /**
     * 提交备注信息
     */
     async handleRemarks() {
      try {
        this.loading = true;
        const res = await broadcastProRemarksApi(
          this.FormData.broadcast_goods_id,
          this.FormData.mark
        );
        this.$message.success(res.message);
        this.dialogVisible = false;
        this.$emit("getList");
      } catch (res) {
        this.$message.error(res.message);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.box-container {
  overflow: hidden;
}
.box-container .list {
  float: left;
  line-height: 40px;
}
.box-container .sp {
  width: 50%;
}
.box-container .sp3 {
  width: 33.3333%;
}
.box-container .sp100 {
  width: 100%;
}
.box-container .list .name {
  display: inline-block;
  width: 100px;
  text-align: right;
  color: #606266;
  font-size: 13px;
}
.box-container .list .blue {
  color: var(--prev-color-primary);
}
.box-container .list.image {
  margin-bottom: 40px;
}
.box-container .list.image img {
  position: relative;
  top: 40px;
}
.el-textarea {
  width: 400px;
}
</style>

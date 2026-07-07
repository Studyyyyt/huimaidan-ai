<template>
  <div>
    <el-dialog
      title="添加商品类型"
      :visible.sync="isShow"
      width="590px"
      :before-close="handleClose"
    >
      <div>
        <el-form label-width="auto">
          <el-form-item label="商品类型：" required>
            <div
              v-for="(item, index) in virtual"
              :key="index"
              class="virtual"
              :class="{
                virtual_boder: addType == item.id,
                virtual_boder2: addType !== item.id,
                disabled: $route.params.id && addType !== item.id
              }"
              @click="virtualbtn(item.id, 2)"
            >
              <div class="virtual_top">{{ item.tit }}</div>
              <div class="virtual_bottom">({{ item.tit2 }})</div>
              <div v-if="addType == item.id" class="virtual_san" />
              <div v-if="addType == item.id" class="virtual_dui">✓</div>
            </div>
          </el-form-item>
        </el-form>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="handleClose" size="small">取 消</el-button>
        <el-button type="primary" @click="confirmSelection" size="small"
          >确 定</el-button
        >
      </span>
    </el-dialog>
  </div>
</template>
<script>
import { roterPre } from "@/settings";
export default {
  data() {
    return {
      isShow: false,
      addType: 0,
      type: 1,
      virtual: [
        { tit: "普通商品", id: 0, tit2: "物流发货" },
        { tit: "虚拟商品", id: 1, tit2: "虚拟发货" },
        { tit: "云盘商品", id: 2, tit2: "统一链接自动发货" },
        { tit: "卡密商品", id: 3, tit2: "不同卡密自动发货" },
        { tit: "预约商品", id: 4, tit2: "上门/到店服务" }
      ]
    };
  },
  methods: {
    open(type) {
      if (type) {
        this.type = type;
      } else {
        this.type = 0;
      }
      this.isShow = true;
    },
    handleClose() {
      this.isShow = false;
    },
    virtualbtn(id, type) {
      this.addType = id;
    },
    confirmSelection() {
      if (this.type == 1) {
        this.$router.push({
          path: `${roterPre}/product/list/addProduct`,
          query: { productType: this.addType, type: this.type }
        });
      } else {
        this.$router.push({
          path: `${roterPre}/product/list/addProduct`,
          query: { productType: this.addType }
        });
      }

      this.isShow = false;
      this.addType = 0;
      this.type = 0;
    }
  }
};
</script>
<style scoped lang="scss">
.virtual_boder {
  border: 1px solid var(--prev-color-primary);
}

.virtual_boder2 {
  border: 1px solid #e7e7e7;
}

.virtual_san {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 0;
  height: 0;
  border-bottom: 26px solid var(--prev-color-primary);
  border-left: 26px solid transparent;
}

.virtual_dui {
  position: absolute;
  bottom: -2px;
  right: 2px;
  color: #ffffff;
  font-family: system-ui;
}

.virtual {
  width: 120px;
  height: 60px;
  background: #ffffff;
  border-radius: 3px;
  display: inline-block;
  text-align: center;
  padding-top: 10px;
  position: relative;
  cursor: pointer;
  margin-bottom: 20px;
  line-height: 23px;

  .virtual_top {
    font-size: 14px;
    font-weight: 600;
    color: rgba(0, 0, 0, 0.85);
  }

  .virtual_bottom {
    font-size: 12px;
    font-weight: 400;
    color: #999999;
  }

  &.disabled {
    .virtual_top,
    .virtual_bottom {
      color: #ccc;
    }
  }
}

.virtual:nth-child(1n) {
  margin-right: 20px;
}
</style>

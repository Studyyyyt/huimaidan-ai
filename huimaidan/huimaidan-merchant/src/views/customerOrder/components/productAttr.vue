<template>
  <div>
    <el-dialog
      :visible.sync="dialogVisible"
      title="商品规格"
      scrollable
      width="600px"
    >
      <div class="productAttr">
        <div>
          <div class="header">
            <div class="pictrue">
              <img :src="attr.productSelect.image || storeInfo.image" />
            </div>
            <div class="text line2">
              <div class="name line2">{{ attr.productSelect.store_name }}</div>
              <div>
                <div class="info">库存 {{ attr.productSelect.stock }}</div>
                <div class="price acea-row row-middle mt14">
                  <div class="money">
                    ¥<span class="num">{{ attr.productSelect.price }}</span>
                  </div>
                  <div
                    v-if="attr.productSelect.svip_price > 0"
                    class="svip acea-row row-middle"
                  >
                    <span>¥{{ attr.productSelect.svip_price }}</span>
                    <img src="@/assets/images/svip_price.png" alt="" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="attr">
            <div
              class="list"
              v-for="(item, indexw) in attr.productAttr"
              :key="indexw"
            >
              <div class="title">{{ item.attr_name }}</div>
              <div class="listn acea-row">
                <div
                  class="item acea-row row-center-wrapper"
                  :class="item.index === itemn.attr ? 'on' : ''"
                  v-for="(itemn, indexn) in item.attr_value"
                  @click="tapAttr(indexw, indexn)"
                  :key="indexn"
                >
                  {{ itemn.attr }}
                </div>
              </div>
            </div>
            <div class="list">
              <div class="title">数量</div>
              <div class="cartBnt acea-row row-middle">
                <div class="cart-reduce">
                  <span class="iconfont iconjian" @click="CartNumDes"></span>
                </div>
                <el-input
                  type="number"
                  class="cart-input"
                  v-model="attr.productSelect.cart_num"
                  :max="attr.productSelect.stock"
                  :min="1"
                  @change="getCartNum"
                />
                <div class="cart-increase">
                  <span class="iconfont iconjia" @click="CartNumAdd"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button
          type="primary"
          :class="{ disabled: attr.productSelect.stock <= 0 }"
          :disabled="attr.productSelect.stock <= 0"
          class="bnt acea-row row-center-wrapper"
          @click="goCat"
        >
          确定
        </button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: "productAttr",
  props: {
    attr: {
      type: Object,
      default: () => {}
    },
    storeInfo: {
      type: Object,
      default: () => {}
    },
    isCart: {
      type: Number,
      value: 0
    }
  },
  data() {
    return {
      dialogVisible: false
    };
  },
  created() {},
  methods: {
    goCat() {
      this.$emit("goCat", this.isCart);
    },
    tapAttr(indexw, indexn) {
      let that = this;
      that.$emit("attrVal", {
        indexw: indexw,
        indexn: indexn
      });
      this.$set(
        this.attr.productAttr[indexw],
        "index",
        this.attr.productAttr[indexw].attr_values[indexn]
      );
      let value = that.getCheckedValue().join(",");
      that.$emit("ChangeAttr", value);
    },
    //获取被选中属性；
    getCheckedValue() {
      let productAttr = this.attr.productAttr;
      let value = [];
      for (let i = 0; i < productAttr.length; i++) {
        for (let j = 0; j < productAttr[i].attr_values.length; j++) {
          if (productAttr[i].index === productAttr[i].attr_values[j]) {
            value.push(productAttr[i].attr_values[j]);
          }
        }
      }
      return value;
    },
    CartNumDes() {
      this.$emit("ChangeCartNum", false);
    },
    CartNumAdd() {
      this.$emit("ChangeCartNum", true);
    },
    getCartNum(data) {
      // this.$set(this.attr.productSelect, "cart_num", data.cart_num);
      this.$forceUpdate();
    }
  }
};
</script>

<style lang="scss" scoped>
::-webkit-scrollbar-thumb {
  -webkit-box-shadow: inset 0 0 6px #eee;
}
::-webkit-scrollbar {
  width: 4px;
  /*对垂直流动条有效*/
}
.productAttr {
  padding: 0 10px;
  .header {
    display: flex;
    flex-wrap: nowrap;
    width: 100%;
  }
  .pictrue {
    width: 116px;
    height: 116px;
    margin-right: 20px;
    img {
      width: 100%;
      height: 100%;
      border-radius: 10px;
    }
  }
  .text {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .bnt {
    width: 100%;
    height: 36px;
    background: var(--prev-color-primary);
    font-size: 12px;
    color: #fff;
    border-radius: 4px;
    margin: 20px auto 0;
    cursor: pointer;
    border: none;
    &.disabled {
      color: #ccc;
      background: #f5f5f5;
      cursor: not-allowed;
    }
  }
  .attr {
    height: 300px;
    overflow-x: hidden;
    overflow-y: scroll;
  }
  .list {
    .title {
      color: #303133;
      font-size: 13px;
      margin-top: 24px;
    }
    .listn {
      max-height: 125px;
      overflow-y: scroll;
      .item {
        min-width: 70px;
        line-height: 32px;
        background: #f5f5f5;
        border-radius: 6px;
        color: #606266;
        font-size: 13px;
        margin-top: 10px;
        margin-right: 14px;
        cursor: pointer;
        padding: 0 20px;
        &.on {
          background: var(--prev-color-primary);
          color: #fff;
        }
      }
    }
  }
  .name {
    color: #000;
    font-size: 18px;
    font-weight: 400;
  }
  .info {
    font-size: 13px;
    color: #909399;
  }
  .money {
    font-size: 18px;
    color: #ff7700;
    font-weight: 500;
    .num {
      font-size: 22px;
    }
  }
  .svip {
    color: #303133;
    margin-left: 10px;
    img {
      width: 33px;
      height: 14px;
      margin-left: 4px;
    }
  }
}
.cartBnt {
  margin-top: 14px;
}
.cart-reduce,
.cart-increase {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: #f5f5f5;
  border-radius: 100%;
  cursor: pointer;
  &.cart-reduce {
    border-right: none;
  }
  &.cart-increase {
    border-left: none;
  }
  .iconfont {
    color: #303133;
    font-size: 13px;
  }
  &:hover {
    background: var(--prev-color-primary);
    .iconfont {
      color: #fff;
    }
  }
  &.on {
    .iconfont {
      color: #ddd;
    }
  }
}
.cart-input {
  max-width: 50px;
  width: 30px;
  height: 26px;
  margin: 0 22px;
  ::v-deep .el-input__inner {
    border: none;
    font-size: 16px;
    color: #303133;
  }
}
</style>

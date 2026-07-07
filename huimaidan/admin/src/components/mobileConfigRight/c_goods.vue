<template>
  <div class="goods-box" v-if="configData.list">
    <div class="acea-row">
      <div class="title">选择商品</div>
      <div class="wrapper">
        <draggable class="dragArea list-group" :list="configData.list" group="peoples">
          <template v-if="configData.list.length">
            <div class="item" v-for="(goods, index) in configData.list" :key="index">
              <img :src="goods.image" alt="" />
              <span class="iconfont-diy icondel_1" @click.stop="bindDelete(index)"></span>
            </div>
          </template>
          <div class="add-item item" @click="modals = true"><span class="iconfont-diy iconaddto"></span></div>
        </draggable>
      </div>
    </div>

    <el-dialog :visible.sync="modals" title="商品列表" class="paymentFooter" width="900">
      <goods-list ref="goodslist" :ischeckbox="true" :isdiy="true" isType :multipleSelectionAll="configData.list" @getProductId="getProductId"
        v-if="modals" @close="modals = false"></goods-list>
    </el-dialog>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import goodsList from '@/components/goodsList';
export default {
  name: 'c_goods',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  components: {
    goodsList,
    draggable: vuedraggable,
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.configData = this.configObj[this.configNme];
      },
      immediate: true,
      deep: true,
    },
  },
  data() {
    return {
      modals: false,
      goodsList: [],
      tempGoods: {},
      configData: {},
    };
  },
  created() {
    this.configData = this.configObj[this.configNme];
  },
  methods: {
    //对象数组去重；
    unique(arr) {
      const res = new Map();
      return arr.filter((arr) => !res.has(arr.product_id) && res.set(arr.product_id, 1));
    },
    getProductId(data) {
      this.modals = false;
      let list = this.configData.list.concat(data);
      this.configData.list = this.unique(list);
    },
    cancel() {
      this.modals = false;
    },
    bindDelete(index) {
      this.configData.list.splice(index, 1);
    },
  },
};
</script>

<style scoped lang="scss">
.goods-box {
  padding: 0 15px;

  .wrapper,
  .list-group {
    display: flex;
    flex-wrap: wrap;
    width: 272px;
  }

  .title {
    color: #999999;
    font-size: 12px;
    width: 75px;
    margin-right: 16px;
    margin-top: 20px;
  }

  .add-item {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 1px solid #eee;

    .iconfont-diy {
      font-size: 24px;
      color: #d8d8d8;
    }
  }

  .item {
    position: relative;
    width: 64px;
    height: 64px;
    margin-bottom: 20px;
    margin-right: 12px;

    &:nth-of-type(3n) {
      margin-right: 0;
    }

    img {
      width: 100%;
      height: 100%;
    }

    .icondel_1 {
      position: absolute;
      right: -5px;
      top: -12px;
      color: #999999;
      font-size: 22px;
      cursor: pointer;
    }
  }
}
</style>

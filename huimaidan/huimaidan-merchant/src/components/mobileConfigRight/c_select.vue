<template>
  <div class="slider-box">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-select :filterable="!!configData.filterable" v-model="configData.activeValue" @change="sliderChange" :multiple="configData.multiple">
          <el-option v-for="(item, index) in configData.list" :value="item.activeValue" :key="index"
            :label="item.title"></el-option>
        </el-select>
      </el-col>
    </div>
  </div>
</template>

<script>
import { labelListApi, storeCategoryListApi } from '@/api/product';

export default {
  name: 'c_select',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
    number: {
      type: null,
    },
  },
  data() {
    return {
      defaults: {},
      configData: {},
      timeStamp: '',
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
      const dataSource = this.configData.dataSource;
      if (dataSource === "goodsLabel") {
        this.getGoodsLabel();
      } else if (dataSource === "storeCate") {
        this.merCategoryListApi();
      }
    });
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true,
    },
    number(nVal) {
      this.timeStamp = nVal;
    },
  },
  methods: {
    async merCategoryListApi() {
      try {
        const res = await storeCategoryListApi();
        this.configData.list = res.data.list.map(el => {
          const { merchant_category_id: id, category_name: name } = el;
          return { activeValue: id, title: name };
        });
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async getGoodsLabel() {
      try {
        const res = await labelListApi();
        this.configData.list = res.data.list.map(el => {
          const { product_label_id: id, label_name: name } = el;
          return {
            activeValue: id,
            title: name
          };
        });
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    sliderChange(e) {
      let storage = window.localStorage;
      this.configData.activeValue = e ? e : storage.getItem(this.timeStamp);
      this.$emit('getConfig', { name: 'select', values: e });
    },
  },
};
</script>

<style scoped lang="scss">
.slider-box {
  padding: 0 15px;

  .label {
    color: #999999;
    font-size: 12px;
  }
}

.c_row-item {
  margin-bottom: 20px;
}
</style>

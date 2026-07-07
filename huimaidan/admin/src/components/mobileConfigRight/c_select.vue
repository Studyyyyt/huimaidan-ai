<template>
  <div class="slider-box" :class="{ 'small': small }">
    <div class="c_row-item">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="small ? 19 : 18">
        <el-select :filterable="!!configData.filterable" v-model="configData.activeValue" @change="sliderChange" :multiple="configData.multiple" :size="small ? 'small' : ''" style="width: 100%;">
          <el-option v-for="(item, index) in configData.list" :value="item.activeValue" :key="index"
            :label="item.title"></el-option>
        </el-select>
      </el-col>
    </div>
  </div>
</template>

<script>
import { labelListApi, merCategoryListApi, getstoreTypeApi } from '@/api/product';
import { merchantGroupLst } from '@/api/merchant';

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
    small: {
      type: Boolean,
      default: false,
    }
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
      } else if (dataSource === "storeType") {
        this.getStoreType();
      } else if (dataSource === "storeGroup") {
        this.getStoreGroup();
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
    async getStoreType() {
      try {
        const res = await getstoreTypeApi();
        this.configData.list = res.data.map(el => {
          const { value: id, label: name } = el;
          return { activeValue: id, title: name };
        });
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async getStoreGroup() {
      try {
        const res = await merchantGroupLst();
        this.configData.list = res.data.map(el => {
          const { region_id: id, name } = el;
          return { activeValue: id, title: name };
        });
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async merCategoryListApi() {
      try {
        const res = await merCategoryListApi();
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

  &.small {
    padding: 0;
  }

  .label {
    color: #999999;
    font-size: 12px;
  }
}

.c_row-item {
  margin-bottom: 20px;
}
</style>

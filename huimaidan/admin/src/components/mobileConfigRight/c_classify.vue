<template>
  <div class="slider-box">
    <div class="c_row-item" v-if="configData.title">
      <el-col class="label" :span="4">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <el-cascader
          @change="sliderChange"
          placeholder="请选择分类"
          size="mini"
          v-model="configData.classVal"
          :options="treeSelect"
          :props="cascaderProps"
          filterable
          clearable
        >
        </el-cascader>
      </el-col>
    </div>
  </div>
</template>

<script>
import { getCategory } from '@/api/diy';
export default {
  name: 'c_classify',
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
      cascaderProps: { multiple: true, checkStrictly: true, emitPath: false },
      treeSelect: [],
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.cascaderProps.checkStrictly = !!this.configData.checkStrictly
      this.configData = this.configObj[this.configNme];
      this.goodsCategory();
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
  },
  methods: {
    sliderChange() {
      this.$emit('getConfig', { name: 'classlfy' });
    },
    goodsCategory() {
      getCategory()
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
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
    width: 75px;
    margin-right: 16px;
  }
}
.c_row-item {
  margin-bottom: 20px;
}
::v-deep.el-cascader__search-input {
  margin-left: 8px;
}
::v-deep.el-cascader {
  width: 100%;
}
</style>

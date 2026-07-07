<template>
  <div class="slider-box">
    <div class="c_row-item" :class="{ 'show-unit': !!configData.unit }">
      <el-col class="label" :span="4" v-if="configData.title">
        {{ configData.title }}
      </el-col>
      <el-col :span="18">
        <div style="display: flex;">
          <div class="slider-box-inner">
            <el-slider v-model="configData.val" controls-position="right" :min="configData.min" :max="configData.max || 100"></el-slider>
          </div>
          <div class="input-number-box">
            <el-input-number size="small" v-model="configData.val" controls-position="right" :min="configData.min" :max="configData.max || 100" style="width: 100%;"></el-input-number>
          </div>
        </div>
      </el-col>
    </div>
  </div>
</template>

<script>
export default {
  name: 'c_slider',
  props: {
    configObj: {
      type: Object,
    },
    configNme: {
      type: String,
    },
  },
  data() {
    return {
      defaults: {},
      sliderWidth: 0,
      configData: {},
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
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
    sliderChange(e) {
      this.$emit('getConfig', e);
    },
  },
};
</script>

<style scoped lang="scss">
.c_row-item {
  margin: 0 15px 20px 15px;
  position: relative;
  .label {
    color: #999;
    font-size: 12px;
  }

  &.show-unit::before {
    position: absolute;
    content: "px";
    right: 8px;
    z-index: 1;
    font-size: 12px;
    color: #606266;
  }
}

.slider-box-inner {
  flex: 1;
  margin-right: 20px
}

.input-number-box {
  width: 86px;
}
</style>

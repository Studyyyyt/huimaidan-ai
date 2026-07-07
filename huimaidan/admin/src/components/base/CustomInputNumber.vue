<template>
  <div @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave" class="custom-input-number">
    <el-input-number v-bind="$attrs" v-on="$listeners" :controls="controls" controls-position="right" class="custom-input-number-input" :style="inputStyle" />
  </div>
</template>

<script>
export default {
  name: 'CustomInputNumber',
  props: {
    width: {
      type: String,
      default: '80px',
    }
  },
  data() {
    return {
      controls: false,
    }
  },
  computed: {
    inputStyle() {
      return {
        width: this.width,
      }
    }
  },
  methods: {
    handleMouseEnter() {
      this.controls = true;
    },
    handleMouseLeave() {
      this.controls = false;
    },
  },
};
</script>

<style lang="scss" scoped>
.custom-input-number {
  width: fit-content;

  ::v-deep {
    .el-input__inner {
      text-align: left;
    }

    .el-input-number__decrease,
    .el-input-number__increase {
      width: 23px;
    }
  }
}
</style>

<template>
  <div class="input-number-range">
    <div class="flex">
      <el-input
        ref="inputFromRef"
        :value="startValue"
        :placeholder="startPlaceholder"
        @input="handleInputFrom"
      />
      <div class="center">
        <span class="delimiter">-</span>
      </div>
      <el-input
        ref="inputToRef"
        :value="endValue"
        :placeholder="endPlaceholder"
        @input="handleInputTo"
      />
      <div class="right-box">{{ rightText }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'InputNumberRange',
  props: {
    startValue: {
      type: [Number, String],
      default: null
    },
    endValue: {
      type: [Number, String],
      default: null
    },
    startPlaceholder: {
      type: String,
      default: '请输入'
    },
    endPlaceholder: {
      type: String,
      default: '请输入'
    },
    rightText: {
      type: String,
      default: '文本'
    }
  },
  data() {
    return {}
  },
  computed: {},
  watch: {},
  methods: {
    handleInputFrom(value) {
      this.$emit('update:startValue', value)
    },

    handleInputTo(value) {
      this.$emit('update:endValue', value)
    }
  }
}
</script>

<style lang="scss" scoped>
// 取消element原有的input框样式
::v-deep .el-input__inner {
  border: 0px;
  margin: 0;
  padding: 0 15px;
  background-color: transparent;
}
.input-number-range {
  background-color: #fff;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
}
.delimiter {
  color: #303133;
}
.flex {
  display: flex;
  height: 32px;
  align-items: center;
  .center {
    color: #c0c4cc;
  }
}
.is-disabled {
  background-color: #f5f7fa;
  border-color: #e4e7ed;
  color: #c0c4cc;
  cursor: not-allowed;
}
.right-box {
  height: 32px;
  padding: 0 20px;
  flex-shrink: 0;
  background-color: #f5f7fa;
  // border: 1px solid #F5F7FA;
  border-left: 1px solid #dcdfe6;
  border-radius: 0 4px 4px 0;
  line-height: 32px;
}
</style>

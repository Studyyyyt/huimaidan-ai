<template>
  <div class="input-auto-wrapper" :class="{ 'has-prefix': hasPrefix, 'has-word-limit': hasWordLimit }">
    <p class="input-content" :class="size">{{ value || '&nbsp;' }}</p>
    <el-input v-bind="$attrs" v-on="$listeners" ref="inputRef" @keyup.enter.native="handleEnter" @blur="handleBlur"
      :value="value" class="input-comp" :size="size">
      <slot name="prefix" slot="prefix" />
      <slot name="suffix" slot="suffix" />
    </el-input>
  </div>
</template>

<!-- 该组件是对 el-input 的封装，主要用于解决 el-input 宽度自适应的问题。 -->
<!-- 组件已对 el-inpit props、slots 进行了透传，可以直接使用 el-input 的 props、slots。 -->
<!-- 当前仅适配了 size="small" 的大小样式，如果需要适配其他大小，请自行补充下方样式。 -->

<script>
export default {
  name: 'autoWidthInput',
  props: {
    value: String,
    size: {
      type: String,
      default: "small"
    },
  },
  data() {
    return {
      inputStyle: {
        width: 'auto',
        minWidth: '80px'
      }
    }
  },
  computed: {
    hasPrefix() {
      return !!this.$scopedSlots.prefix;
    },
    hasWordLimit() {
      return 'show-word-limit' in this.$attrs;
    },
    hasSuffix() {
      return !!this.$scopedSlots.suffix;
    }
  },
  methods: {
    focus() {
      this.$refs.inputRef.focus();
    },
    handleEnter(event) {
      this.$emit('enter', event);
    },
    handleBlur(event) {
      this.$emit('blur', event);
    }
  }
}
</script>

<style scoped lang="scss">
.input-auto-wrapper {
  display: inline-block;
  position: relative;
  padding-inline: 15px;

  &.has-prefix {
    padding-left: 30px;
  }

  &.has-suffix {
    padding-right: 30px;
  }

  &.has-word-limit {
    padding-right: 48px;
  }

  .input-comp {
    position: absolute;
    inset: 0;
  }

  .input-content {
    white-space: nowrap;
    min-width: var(--el-input-min-width, 120px);
    visibility: hidden;
    border-left: 1px solid transparent;
    border-right: 1px solid transparent;

    &.small {
      height: 32px;
      line-height: 32px;
      font-size: 12px;
    }

    // 自行补充其他大小样式的样式
    // &.large {

    // }
  }
}
</style>

<template>
  <div class="list">
    <div class="label" :class="{ on: label.selected }" v-for="(label, j) in list" :key="j"
      @click="handleClick(label, j)">
      <div class="label-item" :style="{
        backgroundColor: label.bg_color,
        color: label.color,
        border: label.border_color ? '1px solid ' + label.border_color : 'none',
      }" v-if="!label.icon">
        {{ label.label_name }}
      </div>
      <img :src="label.icon" class="img-tag" v-else />
      <div class="sanjiao" v-show="label.selected">
        <span class="iconfont iconwancheng"></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "label-list",
  props: {
    list: Array,
  },
  methods: {
    handleClick(label, index) {
      this.$emit("select", {
        label,
        index
      });
    }
  }
}
</script>

<style scoped lang="scss">
.list {
  display: inline-flex;
  flex-wrap: wrap;
  line-height: 18px;

  .label {
    position: relative;
    border: 1px solid #ffffff;
    padding: 2px;
    border-radius: 4px;
    margin: 0 8px 10px 0;
    cursor: pointer;
    display: flex;
    align-items: center;

    &.on {
      border: 1px solid #2d8cf0;
    }
  }

  .label-item {
    padding: 3px 8px;
    background: #eeeeee;
    color: #333333;
    border-radius: 2px;
    cursor: pointer;
    font-size: 12px;
  }

  .img-tag {
    height: 22px;
    border-radius: 2px;
  }

  .sanjiao {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 20px;
    height: 20px;
    background: #2d8cf0;
    clip-path: polygon(100% 100%, 100% 0, 0 100%);
    color: #fff;
    text-align: right;

    .iconfont {
      font-size: 10px;
    }
  }
}
</style>

<template>
  <div class="c_tabs">
    <el-tabs v-model="configData.tabVal">
      <el-tab-pane
        v-for="(item, index) in configData.tabList"
        :key="index"
        :label="item.name"
        :name="index + ''"
      ></el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
export default {
  name: "c_tabs",
  props: {
    configObj: {
      type: Object
    },
    configNme: {
      type: String
    }
  },
  data() {
    return {
      formData: {
        type: 0
      },
      defaults: {},
      configData: {}
    };
  },
  watch: {
    configObj: {
      handler(nVal, oVal) {
        this.defaults = nVal;
        this.configData = nVal[this.configNme];
      },
      deep: true
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.defaults = this.configObj;
      this.configData = this.configObj[this.configNme];
    });
  }
};
</script>

<style scoped lang="scss">
.c_tabs {
  ::v-deep .el-tabs--top .el-tabs__item.is-top:nth-child(2) {
    padding-left: 20px !important;
  }

  ::v-deep.el-tabs__header {
    margin: 0;
  }
}
</style>

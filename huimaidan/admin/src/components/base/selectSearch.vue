<template>
 <el-form-item :label="searchName">
    <el-input placeholder="请输入内容" v-model="keywords" class="input-with-select selWidth" clearable @keyup.enter.native="changeSearch(1)">
        <el-select v-model="prependSelect" slot="prepend" placeholder="请选择">
          <el-option v-for="item in searchSelectList" :key="item.value" :label="item.label" :value="item.value">
            {{item.label}}
          </el-option>
        </el-select>
    </el-input>
</el-form-item>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
export default {
  name: 'SelectSearch',
  props: {
    searchSelectList: {
      type: Array,
      default: function () {
        return [
          {label: "昵称", value: "nickname"},
          {label: "用户ID", value: "uid"},
          {label: "手机号", value: "phone"},
        ];
      },
    },
    select: {
      type: String,
      default: function () {
        return 'nickname';
      },
    },
    searchName: {
      type: String,
      default: function () {
        return '用户搜索：';
      },
    },
  },
  data() {
    return {
      keywords: '',
      prependSelect: this.select,
      searchForm: {
        nickname: "",
        uid: "",
        phone: ""
      }
    };
  },

  methods: {
    changeSearch(){
      this.searchForm = {nickname: "",uid: "",phone: "",real_name: ""}
      this.searchForm[this.prependSelect] = this.keywords;
      this.$emit('search', this.searchForm);
    },
    resetParmas() {
      this.keywords = ""
      this.prependSelect = this.select
      this.changeSearch()
    }
  },
};
</script>

<style scoped>
::v-deep .el-input-group__prepend .el-input{
  min-width: 100px;
}
</style>

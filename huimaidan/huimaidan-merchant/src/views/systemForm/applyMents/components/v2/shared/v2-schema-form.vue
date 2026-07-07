<template>
  <div>
    <template v-for="item of config">
      <V2Upload :form.sync="form" v-bind="item" :path="path" v-if="item.type === 'upload'" />

      <!-- 验证有效期 -->
      <template v-else-if="item.type === 'validate-period'">
        <!-- 有效期开始日期 -->
        <el-form-item :required="item.required" :label="`${item.begin.label}：`" :prop="path + '.' + item.begin.field">
          <el-date-picker v-model="form[item.begin.field]" type="date" value-format="yyyy-MM-dd" format="yyyy-MM-dd"
            :placeholder="getPlaceholder(item.begin)" :picker-options="generateBeginDateOptions()" class="selWidth" />
        </el-form-item>

        <!-- 是否长期有效 -->
        <el-form-item :required="item.required" :label="`${item.longterm.label}：`"
          :prop="path + '.' + item.longterm.field">
          <el-radio-group v-model="form[item.longterm.field]" @change="value => handleLongtermChange(value, item)">
            <el-radio :label="true">是</el-radio>
            <el-radio :label="false">否</el-radio>
          </el-radio-group>
        </el-form-item>

        <!-- 有效期结束日期 -->
        <el-form-item :required="item.required" :label="`${item.end.label}：`" :prop="path + '.' + item.end.field"
          v-if="!form[item.longterm.field]">
          <el-date-picker v-model="form[item.end.field]" type="date" value-format="yyyy-MM-dd" format="yyyy-MM-dd"
            :placeholder="getPlaceholder(item.end)" :picker-options="generateEndDateOptions()" class="selWidth" />
        </el-form-item>
      </template>

      <el-form-item :required="item.required" :label="`${item.label}：`" :prop="path + '.' + item.field" v-else>
        <!-- 文本输入框 -->
        <el-input v-model="form[item.field]" :minlength="item.min" :maxlength="item.max"
          :placeholder="getPlaceholder(item)" class="selWidth" v-if="item.type === 'text'" />


        <!-- 日期选择器 -->
        <el-date-picker v-model="form[item.field]" type="date" value-format="yyyy-MM-dd" format="yyyy-MM-dd"
          :placeholder="getPlaceholder(item)" :picker-options="item.pickerOptions" class="selWidth"
          v-else-if="item.type === 'date'" />

        <!-- 下拉选择器 -->
        <el-select v-model="form[item.field]" :placeholder="getPlaceholder(item)" class="selWidth"
          v-else-if="item.type === 'select'">
          <el-option v-for="item in item.options" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>

        <!-- 复选框 -->
        <el-checkbox-group v-model="form[item.field]" v-else-if="item.type === 'checkbox'">
          <el-checkbox v-for="option in item.options" :key="option.value" :label="option.value">{{ option.label
          }}</el-checkbox>
        </el-checkbox-group>

        <!-- 单选框 -->
        <el-radio-group v-model="form[item.field]" v-else-if="item.type === 'radio'">
          <el-radio v-for="option in item.options" :key="option.value" :label="option.value">{{ option.label
            }}</el-radio>
        </el-radio-group>

        <!-- 省市编码选择器 -->
        <el-cascader v-model="form[item.field]" :options="provinceCodeList" clearable :show-all-levels="false"
          size="small" v-else-if="item.type === 'address'" :props="cascaderProps" />

        <template v-if="item.tips && item.tips.length">
          <div class="tips" v-for="tip in item.tips" :key="tip" v-html="tip" />
        </template>
      </el-form-item>
    </template>
  </div>
</template>

<script>
import V2Upload from "./v2-upload.vue";
import { generateBeginDateOptions, generateEndDateOptions } from "../v2.utils";

export default {
  name: "V2SchemaForm",
  props: {
    form: Object,
    config: Array,
    path: String
  },
  data() {
    return {
      provinceCodeList: [],
      cascaderProps: {
        emitPath: false
      }
    };
  },
  components: {
    V2Upload,
  },
  created() {
    import('@/utils/address.js').then(res => {
      this.provinceCodeList = res.default
    })
  },
  methods: {
    generateBeginDateOptions,
    generateEndDateOptions,
    getPlaceholder(item) {
      if (item.placeholder) return item.placeholder;
      const prefix = item.type === "text" ? "请输入" : "请选择";
      return prefix + item.label;
    },
    handleLongtermChange(value, item) {
      const periodEnd = value ? "长期" : "";
      this.form[item.end.field] = periodEnd;
    }
  }
}
</script>

<style scoped lang="scss">
.tips {
  font-size: 12px;

  :deep(a) {
    color: var(--prev-color-primary) !important;
  }
}
</style>

<template>
  <el-row>
    <el-col :span="24">
      <el-form-item label="平台商品参数：">
        <el-select
          v-model="formValidate.param_temp_id"
          placeholder="请选择平台商品参数"
          @change="getSpecsList"
          size="small"
          clearable
          class="pageWidth"
        >
          <el-option
            v-for="item in sysSpecsSelect"
            :key="item.value"
            :label="item.label"
            :value="item.value.toString()"
          />
        </el-select>
      </el-form-item>
    </el-col>

    <el-col :span="24">
      <el-form-item label="自定义参数模板：">
        <el-select
          v-model="formValidate.custom_temp_id"
          multiple
          placeholder="添加自定义参数模板"
          @change="getSpecsList"
          clearable
          size="small"
          class="pageWidth"
        >
          <el-option
            v-for="item in merSpecsSelect"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
        <el-button type="defalut" size="small" @click="addSpecs"
          >添加参数</el-button
        >
      </el-form-item>
    </el-col>
    <el-col :span="16">
      <el-form-item>
        <el-table
          border
          size="small"
          class="specsList"
          ref="tableParameter"
          :data="paramData"
          row-key="parameter_value_id"
        >
          <el-table-column align="center" label="参数名称" min-width="120">
            <template slot-scope="scope">
              <el-input
                v-model="scope.row.name"
                :disabled="scope.row.mer_id == 0"
                size="small"
                placeholder="请输入参数名称"
                class="priceBox"
              />
            </template>
          </el-table-column>
          <el-table-column align="center" label="参数值" min-width="120">
            <template slot-scope="scope">
              <div class="arrbox">
                <el-tag
                  @close="handleClose(scope.$index, index)"
                  :name="item"
                  :closable="true"
                  v-for="(item, index) in scope.row.values"
                  :key="index"
                  size="small"
                  class="tags-item"
                >
                  {{ item.value }}
                </el-tag>
                <input
                  size="small"
                  class="arrbox_ip width100"
                  v-model="scope.row.single"
                  placeholder="请输入选项，回车确认"
                  @blur="addSpecList(scope.row, scope.$index)"
                  @keyup.enter="addSpecList(scope.row, scope.$index)"
                />
              </div>
            </template>
          </el-table-column>
          <el-table-column label="操作" min-width="60">
            <template slot-scope="scope">
              <el-button
                v-if="scope.row.mer_id != 0"
                type="text"
                class="submission"
                size="small"
                @click.native.prevent="delSpecs(scope.$index)"
                >删除</el-button
              >
            </template>
          </el-table-column>
        </el-table>
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
import Sortable from "sortablejs";
export default {
  name: "ProductParams",
  props: {
    formValidate: {
      type: Object,
      defalut: {}
    },
    customSpecs: {
      type: Array,
      defalut: []
    },
    sysSpecsSelect: {
      type: Array,
      defalut: []
    },
    merSpecsSelect: {
      type: Array,
      defalut: []
    },
    paramList: {
      type: Array,
      defalut: []
    }
  },
  data() {
    return {
      sortable: null, // 拖拽排序
      customSpecsData: [], // 自定义参数模板
      paramData: [] // table数据
    };
  },
  watch: {
    customSpecs(newVal) {
      this.customSpecsData = newVal;
    },
    paramList: {
      handler(nVal) {
        this.paramData = nVal
      },
      deep: true,
      immediate: true
    }
  },
  mounted() {
    this.setSort();
  },
  methods: {
    delSpecs(index) {
      this.formValidate.params.splice(index, 1);
    },

    addSpecs() {
      this.formValidate.params.push({
        name: "",
        values: [],
        sort: 0,
        single: ""
      });
    },

    getSpecsList() {
      this.$emit("getSpecsList");
    },

    //  设置表格行的拖拽排序功能
    setSort() {
      // ref一定跟table上面的ref一致
      const el = this.$refs.tableParameter.$el.querySelectorAll(
        ".el-table__body-wrapper > table > tbody"
      )[0];
      this.sortable = Sortable.create(el, {
        ghostClass: "sortable-ghost",
        setData: function(dataTransfer) {
          dataTransfer.setData("Text", "");
        },
        // 监听拖拽事件结束时触发
        onEnd: evt => {
          this.elChangeExForArray(
            evt.oldIndex,
            evt.newIndex,
            this.formValidate.params
          );
        }
      });
    },

    // 拖拽排序
    elChangeExForArray(index1, index2, array) {
      const temp = array[index1];
      array[index1] = array[index2];
      array[index2] = temp;
      return array;
    },

    // 删除参数
    handleClose(index, idx) {
      this.formValidate.params[index]["values"].splice(idx, 1);
    },
    addSpecList(item, index) {
      if (!item.single) {
        return;
      }
      let count = this.formValidate.params[index].values.indexOf(item);
      if (count === -1) {
        this.formValidate.params[index].values.push({ value: item.single });
      }
      item.single = "";
    }
  }
};
</script>

<style scoped lang="scss">
/* 保留原有样式 */
.pageWidth {
  width: 300px;
}
.specsList {
  width: 100%;
}
.arrbox {
  display: flex;
  flex-wrap: wrap;
}

.tags-item {
  margin: 2px 6px 2px 0;
}

.arrbox_ip {
  flex: 1;
  min-width: 100px;
}
.submission {
  color: var(--prev-color-primary);
}
</style>

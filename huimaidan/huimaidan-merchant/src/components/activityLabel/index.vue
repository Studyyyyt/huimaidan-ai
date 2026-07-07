<template>
  <div class="label-wrapper">
    <div v-if="!renderLabelList.length" class="nonefont">暂无标签</div>
    <div class="label-box-wrapper" v-else>
      <div class="label-box" v-for="(item, index) in renderLabelList" :key="index">
        <div class="title">{{ item.name }}</div>
        <label-list :list="item.children" @select="selectLabel" />
      </div>
    </div>
    <div class="footer">
      <el-button size="small" class="btns" ghost @click="cancel">取消</el-button>
      <el-button size="small" type="primary" class="btns" @click="subBtn">确定</el-button>
    </div>
  </div>
</template>

<script>
import { labelOptionsApi } from '@/api/product';
import LabelList from './label-list.vue';

export default {
  name: 'activityLabel',
  components: {
    LabelList
  },
  props: {
    selectedLabelIds: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      labelList: [],

      renderLabelList: []
    };
  },
  created() {
    this.getStoreLabel();
  },
  computed: {
    updateLabelParams() {
      return [this.labelList, this.selectedLabelIds];
    }
  },
  watch: {
    updateLabelParams() {
      this.resetLabelList();
    }
  },
  methods: {
    resetLabelList() {
      const labelList = JSON.parse(JSON.stringify(this.labelList));
      const idSet = new Set(this.selectedLabelIds);

      for (const category of labelList) {
        if (category.children && category.children.length) {
          for (const label of category.children) {
            label.selected = idSet.has(label.id);
          }
        }
      }

      this.renderLabelList = labelList;
    },
    // 用户标签
    getStoreLabel() {
      labelOptionsApi()
        .then((res) => {
          this.labelList = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    selectLabel({ label }) {
      label.selected = !label.selected;
    },
    // 确定
    subBtn() {
      const labelList = [];
      for (const category of this.renderLabelList) {
        if (category.children && category.children.length) {
          for (const label of category.children) {
            if (label.selected) {
              labelList.push(label);
            }
          }
        }
      }
      this.$emit('change', labelList);
    },
    cancel() {
      this.$emit('close');
    },
  },
};
</script>

<style lang="scss" scoped>
.label-wrapper {


  .footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 40px;

    button {
      margin-left: 10px;
    }
  }
}

.label-box {
  margin-bottom: 10px;
}

.btn {
  width: 60px;
  height: 24px;
}

.title {
  font-size: 13px;
  margin-bottom: 8px;
}

.nonefont {
  text-align: center;
  padding-top: 20px;
}

.label-box-wrapper {
  max-height: 50vh;
  overflow-y: auto;
}
</style>

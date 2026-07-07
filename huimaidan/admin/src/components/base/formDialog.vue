<template>
  <el-dialog :title="title" :visible.sync="panelVisible" :width="width" :close-on-click-modal="false" append-to-body class="form-dialog">
    <form-create :rule="mixinRule" :option="option" :value.sync="value" v-model="fApi" v-if="rule"></form-create>

    <span slot="footer" class="dialog-footer">
      <el-button size="small" @click="handleClosePanel">取消</el-button>
      <el-button size="small" type="primary" :loading="submitLoading" @click="handleConfirm">确定</el-button>
    </span>
  </el-dialog>
</template>

<script>
import formCreate from "@form-create/element-ui";
import { getUploadProps } from "@/utils/form-create";
import FormDialogTip from './formDialogTip.vue';

formCreate.component(FormDialogTip.name, FormDialogTip);

const getInitData = () => {
  return {
    title: "", // 标题
    fApi: null, // form-create 实例
    value: {}, // 表单数据
    rule: null, // 表单规则
    action: null, // 确认按钮点击事件
  };
};

const baseFormOptions = () => {
  // form-create 配置
  return {
    form: {
      size: "small",
      labelWidth: "7em"
    },
    submitBtn: {
      show: false
    }
  };
}

export default {
  name: 'FormDialog',
  props: {
    width: {
      type: String,
      default: '500px'
    },
  },
  components: {
    formCreate: formCreate.$form()
  },
  data() {
    return {
      ...getInitData(),
      panelVisible: false, // 面板是否显示
      submitLoading: false // 提交按钮 loading
    };
  },
  computed: {
    // form-create 配置
    option() {
      const baseOptions = baseFormOptions();

      if (!this.rule) return baseOptions;

      let maxFormLabelCharCount = 0;

      for (const rule of this.rule) {
        if (rule.title && rule.title.length > maxFormLabelCharCount) {
          maxFormLabelCharCount = rule.title.length;
        }
      }

      // 文字长度、必选标志位等综合计算 label 宽度
      baseOptions.form.labelWidth = `${maxFormLabelCharCount + 2}em`;

      return baseOptions;
    },
    // 表单规则
    mixinRule() {
      if (!this.rule) return [];
      return this.rule.map(rule => {
        if (rule.type === "upload") {
          return {
            ...rule,
            props: {
              ...getUploadProps(),
              ...rule.props,
            }
          }
        }

        return rule;
      });
    }
  },
  watch: {
    value: {
      handler(newVal, oldVal) {
        this.$emit("update", [newVal, oldVal]);
      },
      deep: true
    }
  },
  methods: {
    // 提交表单
    async handleConfirm() {
      if (this.submitLoading) return;
      this.submitLoading = true;
      // 先验证表单是否合法
      try {
        await this.fApi.validate();
      } catch {
        // 如果表单不合法，则不进行提交
        this.submitLoading = false;
        return;
      }
      // 执行传入的回调
      const isSuccess = await this.action(this.value);
      this.submitLoading = false;

      if (isSuccess) {
        // 关闭面板
        this.handleClosePanel();
      }
    },
    // 关闭面板
    handleClosePanel() {
      this.panelVisible = false;
      setTimeout(() => {
        Object.assign(this, getInitData());
      }, 200);
    },
    open({ rule, title, action }) {
      this.title = title;
      this.rule = rule;
      this.action = action;
      this.panelVisible = true;
    }
  }
}
</script>
<style lang="scss">
.form-dialog .has-tips + .el-form-item__error {
  position: static;
}
</style>
<style scoped lang="scss">

::v-deep ._fc-upload {
  display: flex;
  flex-wrap: wrap;
  row-gap: 4px;
}
</style>

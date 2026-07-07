<template>
  <el-row>
    <el-col :span="24">
      <el-form-item label="商品详情：">
        <WangEditor
          :content="formValidate.content"
          @editorContent="getEditorContent"
          style="width: 1000px;"
        />
      </el-form-item>
    </el-col>
  </el-row>
</template>

<script>
import WangEditor from '@/components/wangEditor/index.vue';
export default {
  name: 'ProductDetail',
  components: {
    WangEditor
  },
  props: {
    formValidate: {
      type: Object,
      default: () => ({})
    }
  },
  methods: {
    /**
     * 处理富文本编辑器内容变化
     * @param {string} content 编辑器内容
     */
    getEditorContent(content) {
      this.$emit('getEditorContent', content)
    }
  }
}
</script>
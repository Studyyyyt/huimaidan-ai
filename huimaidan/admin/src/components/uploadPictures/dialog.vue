<template>
  <div>
    <el-dialog :visible.sync="modalPic" width="960px" :title="title" :mask-closable="false">
      <uploadPictures :isMore="numLimit + ''" @getPic="getPic" :gridBtn="gridBtn" :gridPic="gridPic"
        v-if="modalPic">
      </uploadPictures>
    </el-dialog>
    <slot :open="open">
      <el-button size="small" type="primary" @click="open">上传图片</el-button>
    </slot>
  </div>
</template>

<script>
import uploadPictures from '@/components/uploadPictures';

export default {
  name: 'UploadPicturesDialog',
  props: {
    title: {
      type: String,
      default: '上传图片'
    },
    numLimit: {
      type: Number,
      default: 1
    }
  },
  components: {
    uploadPictures
  },
  data() {
    return {
      modalPic: false,
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
    }
  },
  methods: {
    open() {
      this.modalPic = true;
    },
    getPic(pic) {
      if (pic.att_dir.length > this.numLimit) {
        this.$message.warning(`最多只能选${this.numLimit}张图片`);
        return;
      }
      this.$emit('select', pic.att_dir);
      this.modalPic = false;
    }
  }
}
</script>

<style scoped></style>

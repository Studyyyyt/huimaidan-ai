<template>
  <el-form-item :required="required" :label="`${label}：`" :prop="propsStr">
    <div class="acea-row">
      <div v-if="fileList.length" class="upLoadPicBox">
        <div v-for="(item, index) in fileList" :key="index" class="pictrue relative">
          <el-image :src="item.dir" style="width: 100%;height: 100%" :initial-index="index" :preview-src-list="previewList"
            v-if="!isVideo" />
          <video v-else :src="item.dir" style="width: 100%;height: 100%" />
          <i class="el-icon-error btndel" @click="handleRemoveFile(index)" />
        </div>
      </div>
      <el-upload v-if="allowUpload" class="upload-demo mr10 mb5" :action="fileUrl" :on-success="handleSuccess"
        :headers="myHeaders" :show-file-list="false" :multiple="isMultiple" :limit="max" ref="upload"
        :accept="acceptType">
        <div class="upLoadPicBox">
          <div class="upLoad">
            <i class="el-icon-camera cameraIconfont" />
          </div>
        </div>
      </el-upload>
    </div>
    <template v-if="tips && tips.length">
      <div class="tips" v-for="tip in tips" :key="tip" v-html="tip" />
    </template>
  </el-form-item>
</template>

<script>
import SettingMer from '@/libs/settingMer';
import { getToken } from '@/utils/auth';

export default {
  name: "V2Upload",
  props: {
    form: Object,
    path: {
      type: String,
      default: ""
    },
    field: String,
    label: String,
    required: {
      type: Boolean,
      default: false
    },
    accept: {
      type: String,
      default: "image"
    },
    max: {
      type: Number,
      default: 1
    },
    tips: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      myHeaders: { 'X-Token': getToken() },
    };
  },
  computed: {
    propsStr() {
      if (this.path) {
        return this.path + '.' + this.field;
      }
      return this.field;
    },
    isVideo() {
      return this.accept === "video";
    },
    acceptType() {
      return this.isVideo ? "video/*" : "image/*";
    },
    isMultiple() {
      return this.max > 1;
    },
    fileUrl() {
      const path = this.isVideo ? `/applyments/upload/video/file` : `/applyments/upload/file`;
      return SettingMer.https + path;
    },
    value() {
      return this.getValue(this.field);
    },
    fileList() {
      if (!this.value) return [];
      if (!this.isMultiple) return [this.value];
      return this.value
    },
    previewList() {
      return this.fileList.map(item => item.dir);
    },
    allowUpload() {
      return this.fileList.length < this.max;
    }
  },
  methods: {
    getValue(targetField) {
      const fieldList = targetField.split(".");
      let value = this.form, currentField;
      while (currentField = fieldList.shift()) {
        value = value[currentField];
      }

      return value;
    },
    setValue(targetField, value) {
      let targetObj = this.form;
      const fieldList = targetField.split(".");
      while (fieldList.length > 1) {
        targetObj = targetObj[fieldList.shift()];
      }
      targetObj[fieldList[0]] = value;
    },
    handleSuccess(response) {

      if (response.status === 200) {
        this.$message.success('上传成功');
        const uploadRes = response.data[0];
        if (this.isMultiple) {
          this.setValue(this.field, [
            ...this.value,
            uploadRes
          ]);
        } else {
          this.setValue(this.field, uploadRes);
        }
      } else {
        this.$message.error(response.message)
      }
    },
    handleRemoveFile(index) {
      if (this.isMultiple) {
        this.value.splice(index, 1);
      } else {
        this.setValue(this.field, null);
      }
    }
  }
}
</script>

<style scoped lang="scss">
.relative {
  position: relative;
}

.upLoadPicBox {
  display: inline-flex;
}

.btndel {
  position: absolute;
  z-index: 1;
  right: -4px;
  top: -4px;
}

.tips {
  font-size: 12px;

  :deep(a) {
    color: var(--prev-color-primary) !important;
  }
}

::v-deep.el-form-item.is-error .upLoad {
  border: 1px solid #ff4949;
}
</style>

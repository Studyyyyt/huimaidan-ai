<template>
  <div class="divBox">
    <el-card style="margin-top: 15px;">
      <div class="auth acea-row row-between-wrapper">
        <div class="acea-row row-middle">
          <span class="iconfont iconbanquan iconIos blue"></span>
          <div class="acea-row row-middle">
            <span class="update">修改版权信息:</span>
            <el-input style="width: 460px" v-model="copyrightText" />
          </div>
        </div>
        <el-button type="primary" size="small" @click="saveCopyRight">保存</el-button>
      </div>
      <div class="authorized" @click="modalPicTap()">
        <div>
          <span class="update">上传授权图片:</span>
        </div>
        <div class="uploadPictrue" v-if="authorizedPicture">
          <img v-lazy="authorizedPicture" />
        </div>
        <div class="upload" v-else>
          <div class="iconfont">+</div>
        </div>
      </div>
      <span class="prompt">建议尺寸：宽290px*高100px</span>
    </el-card>
  </div>
</template>
<script>
import { getAuthApi, saveCrmebCopyRight } from '@/api/maintain'
export default {
  name: 'Index',
  data() {
    return {
      copyrightText: '',
      authorizedPicture: '',
    }
  },
  mounted() {
    this.getAuthData()
  },
  methods: {
    getAuthData() {
      getAuthApi().then(res => {
        const data = res.data || {}
        if (data.status == -1) {
          this.copyrightText = ''
          this.authorizedPicture = ''
        } else {
          this.copyrightText = data.copyright_context || ''
          this.authorizedPicture = data.copyright_image || ''
        }
      })
    },
    // 保存版权信息
    saveCopyRight() {
      saveCrmebCopyRight({
        copyright_context: this.copyrightText,
        copyright_image: this.authorizedPicture,
      }).then((res) => {
        return this.$message.success(res.message)
      }).catch(({ message }) => {
        this.$message.error(message);
      });
    },
    // 选择图片
    modalPicTap() {
      const _this = this;
      this.$modalUpload(function (img) {
        _this.authorizedPicture = img[0];
      });
    },
  }
}
</script>

<style scoped lang="scss">
  .auth {
    padding: 9px 16px 9px 10px;
  }
  .auth .iconIos {
    font-size: 40px;
    margin-right: 10px;
    color: #001529;
  }
  .update {
    white-space: nowrap;
    margin-right: 12px;
  }
  .prompt {
    margin-left: 152px;
    font-size: 12px;
    font-weight: 400;
    color: #999999;
  }
  .authorized {
    display: flex;
    margin-left: 60px;
    margin-bottom: 14px;
    .upload {
      width: 60px;
      height: 60px;
      background: rgba(0, 0, 0, 0.02);
      border-radius: 4px;
      border: 1px solid #DDDDDD;
      text-align: center;
      line-height: 60px;
    }
  }
  .uploadPictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;
  }
  .uploadPictrue img {
    width: 100%;
    height: 100%;
  }
</style>

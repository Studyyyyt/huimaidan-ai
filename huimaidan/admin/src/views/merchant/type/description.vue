<template>
  <div class="divBox">
    <el-card class="box-card">
      <el-tabs v-model="activeName">
        <el-tab-pane label="店铺类型说明" name="type"></el-tab-pane>
        <el-tab-pane label="店铺分类说明" name="category"></el-tab-pane>
      </el-tabs>
      <el-row v-loading="fullscreenLoading" class="formValidate mt20">
        <el-col :span="24">
          <el-row>
            <h3 class="title mb20">{{ currentTitle }}</h3>
            <WangEditor
              ref="editor"
              :editorHeight="530"
              :content="formValidate.agree"
              @editorContent="getEditorContent"
              style="width: 1000px;margin: 0 auto;"
              ></WangEditor>
          </el-row>
        </el-col>
        <el-col style="margin-top:30px; text-align: center;">
          <el-button type="primary" class="submission" size="small" @click="previewProtol">预览</el-button>
          <el-button type="primary" class="submission" size="small" @click="handleSubmit('formValidate')">提交</el-button>
        </el-col>
      </el-row>
    </el-card>
    <div class="Box">
      <el-dialog
        v-if="modals"
        :visible.sync="modals"
        title=""
        height="30%"
        custom-class="dialog-scustom"
        class="addDia"
      >
        <div class="agreement">
          <h3>{{ currentTitle }}</h3>
          <div class="content">
            <div v-html="formValidate.agree"></div>
          </div>
        </div>
      </el-dialog>
    </div>
  </div>
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
import WangEditor from '@/components/wangEditor/index.vue';
import {
  getStoreTypeApi,
  updateStoreTypeApi
} from '@/api/merchant';

const TITLE = {
  type: "店铺类型说明",
  category: "店铺分类说明"
};

export default {
  name: 'storeTypeDesc',
  components: { WangEditor },
  data() {
    return {
      activeName: "type",
      modals: false,
      props: {
        emitPath: false
      },
      formValidate: {
        agree: '',
      },
      content: '',
      fullscreenLoading: false,
    }
  },
  computed: {
    dataKey() {
      return this.activeName === "type" ? "sys_merchant_type" : "sys_merchant_category";
    },
    currentTitle() {
      return TITLE[this.activeName];
    }
  },
  watch: {
    activeName: {
      handler() {
        this.getInfo()
          .then(() => {
            if (!this.$refs.editor) return;
            this.$refs.editor.editor.txt.html(this.formValidate.agree);
          });
      },
      immediate: true
    }
  },
  mounted() {
  },
  methods: {
    getInfo() {
      this.fullscreenLoading = true
      return getStoreTypeApi(this.dataKey).then(res => {
        const info = res.data
        this.formValidate = {
          agree: info[this.dataKey],
        }
        this.fullscreenLoading = false
      }).catch(res => {
        this.$message.error(res.message)
        this.fullscreenLoading = false
      })
    },
    // 说明详情
    getEditorContent(data) {
      this.formValidate.agree = data;
    },
    // 提交
    handleSubmit(name) {
      if(this.formValidate.agree === '' || !this.formValidate.agree){
        this.$message.warning("请输入协议信息！");
        return
      }else{
        updateStoreTypeApi(this.dataKey,this.formValidate).then(async res => {
          this.fullscreenLoading = false
          this.$message.success(res.message)
        }).catch(res => {
          this.fullscreenLoading = false
          this.$message.error(res.message)
        })

      }

    },
    previewProtol(){
      this.modals = true;
    }
  }
}
</script>

<style scoped lang="scss">
.dialog-scustom,.addDia{
  min-width: 400px;
  height: 900px;
  .el-dialog{
    width: 400px;
  }
  h3{
    color: #333;
    font-size: 16px;
    text-align: center;
    font-weight: bold;
    margin: 0;
  }
}
.title{
  font-weight: bold;
  font-size: 18px;
  text-align: center;
}
.agreement{
  width: 350px;
  margin: 0 auto;
  box-shadow: 1px 5px 5px 2px rgba(0,0,0,.2);
  padding: 26px;
  border-radius: 15px;
  .content{
    height: 600px;
    overflow-y:scroll;
  }
  p{
    text-align: justify;
  }
}
.agreement .content ::v-deep p{
  font-size: 13px;
  line-height: 22px;
}
.agreement ::v-deep img{
  max-width: 100%;
}
/*css主要部分的样式*/
/*定义滚动条宽高及背景，宽高分别对应横竖滚动条的尺寸*/
::-webkit-scrollbar {
  width: 10px; /*对垂直流动条有效*/
  height: 10px; /*对水平流动条有效*/
}

/*定义滚动条的轨道颜色、内阴影及圆角*/
::-webkit-scrollbar-track{
  /*-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);*/
  background-color: transparent;
  border-radius: 3px;
}

</style>




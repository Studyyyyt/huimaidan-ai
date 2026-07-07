<template>
  <div class="divBox">
    <el-card class="box-card">
      <div>
        <el-tabs
          tab-position="left"
          v-model="editableTabsValue"
          @tab-click="tabStatus"
        >
          <el-tab-pane
            :label="item.label"
            v-for="(item, index) in tabList"
            :key="index"
            :name="item.key"
          >
            <div class="content">
              <div class="phoneBox">
                <div class="fontBox" v-html="formValidate.agree"></div>
              </div>
              <div class="ueditor">
                <div class="font">
                  <span class="gang"></span> {{ item.label }}
                </div>
                <WangEditor
                  v-if="showEdit"
                  :content="formValidate.agree"
                  @editorContent="getEditorContent"
                  style="width: 100%;"
                ></WangEditor>
              </div>
            </div>
          </el-tab-pane>
        </el-tabs>
        <div class="btn">
          <el-button class="button" type="primary" @click="submenus">提交</el-button>
        </div>
      </div>
    </el-card>
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
import WangEditor from "@/components/wangEditor/index.vue";
import { keylstApi, getAgreeApi, postAgreeApi } from "@/api/setting";
export default {
  name: "agreements",
  components: { WangEditor },
  data() {
    return {
      tabList: [
        { title: "用户协议", id: 1 },
        { title: "隐私政策", id: 2 },
        { title: "入驻协议", id: 3 },
        { title: "关于我们", id: 4 },
        { title: "资质证照", id: 5 },
        { title: "平台规则", id: 6 },
        { title: "注销声明", id: 7 },
        { title: "注销重要提示", id: 8 },
      ],
      formValidate: {
        agree: "",
      },
      editableTabsValue: "",
      showEdit: true
    };
  },
  created() {
    keylstApi().then((res) => {
      this.tabList = res.data;
      this.editableTabsValue = res.data[0].key;
      this.getInfo();
    });
  },
  methods: {
    getInfo() {
      let parmas = this.editableTabsValue
      getAgreeApi(parmas).then((res) => {
        this.showEdit = true
        if(res.data[this.editableTabsValue]) {
          let content = res.data[parmas]
          this.formValidate.agree = content;
        }
      });
    },
    getEditorContent(data) {
      this.formValidate.agree = data;
    },
    submenus() {
      if (this.formValidate.agree === "" || !this.formValidate.agree) {
        this.$message.warning("请输入协议信息！");
        return;
      } else {
        postAgreeApi(this.editableTabsValue, this.formValidate)
          .then(async (res) => {
            this.$message.success(res.message);
          })
          .catch((res) => {
            this.$message.error(res.message);
          });
      }
    },
    tabStatus() {
      this.formValidate.agree = ""
      this.showEdit = false
      this.getInfo();
    },
  },
};
</script>
<style scoped lang="scss">
.box-card ::v-deep .el-card__body {
  padding-left: 0px;
}
::v-deep .el-tabs__item.is-active {
  color: var(--prev-color-primary);
  background-color: #ccc;
  background: #437efd1e;
}
::v-deep .el-tabs--left .el-tabs__header.is-left {
  float: left;
  margin-bottom: 0;
  margin-right: 20px;
  height: 700px;
  font-size: 13px;
}
.gang {
  border: 2px solid var(--prev-color-primary);
  margin-right: 10px;
}
.btn {
  // border-top: 1px solid #ccc;
  // margin-top: 20px;
  width: 100%;
  position: fixed;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,.8);
  .button {
    display: block;
    margin: 17px auto 0px;
  }
}
.content {
  display: flex;
  justify-content: flex-start;
  flex-wrap: wrap;
  ::v-deep img {
    max-width: 100%;
  }
  .phoneBox {
    width: 302px;
    height: 543px;
    background-image: url("../../../assets/images/phoneBox.png");
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    overflow: hidden;
    margin-right: 20px;
    .fontBox {
      margin: 0 auto;
      margin-top: 45px;
      width: 255px;
      height: 450px;
      background: #ffffff;
      border: 1px solid #e2e2e2;
      padding: 10px;
      overflow: hidden;
      overflow-y: auto;
    }
  }
  .ueditor {
    flex: 1;
    .font {
      font-size: 20px;
      font-weight: 600;
      color: #303133;
      margin-bottom: 20px;
    }
  }
}
</style>

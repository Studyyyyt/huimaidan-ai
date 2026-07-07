<template>
  <div class="divBox">
    <el-card>
      <el-page-header @back="back" :content="$route.params.id ? '编辑公告' : '发布公告'">
      </el-page-header>
    </el-card>
    <el-card class="box-card mt14">
      <el-form
        ref="formValidate"
        class="formValidate mt20"
        size="small"
        :rules="ruleValidate"
        :model="formValidate"
        label-width="100px"
        @submit.native.prevent
      >
        <el-form-item label="消息名称：" prop="notice_title">
          <el-input
            type="text"
            maxlength="20"
            show-word-limit
            v-model="formValidate.notice_title"
            placeholder="请输入消息名称"
            class="w-440"
          />
        </el-form-item>
        <el-form-item label="选择店铺：">
          <el-radio-group v-model="formValidate.type">
            <el-radio :label="4">全部</el-radio>
            <el-radio :label="1">店铺名称</el-radio>
            <el-radio :label="2">店铺类别</el-radio>
            <el-radio :label="3">店铺分类</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="formValidate.type === 1" label="店铺名称：" key="mer_id">
          <el-select
            v-model="formValidate.mer_id"
            multiple
            clearable
            filterable
            placeholder="请选择"
            class="w-440"
          >
            <el-option
              v-for="item in merSelect"
              :key="item.mer_id"
              :label="item.mer_name"
              :value="item.mer_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item v-if="formValidate.type === 2" label="店铺类别：" key="is_trader">
          <el-select
            v-model="formValidate.is_trader"
            clearable
            placeholder="请选择"
            class="w-440"
          >
            <el-option label="自营" :value="1" />
            <el-option label="非自营" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item v-if="formValidate.type === 3" label="店铺分类：" key="category_id">
          <el-select
            v-model="formValidate.category_id"
            multiple
            placeholder="请选择"
            class="w-440"
            clearable
          >
            <el-option
              v-for="item in merCateList"
              :key="item.merchant_category_id"
              :label="item.category_name"
              :value="item.merchant_category_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="公告内容：" prop="notice_content">
          <WangEditor
            :content="formValidate.notice_content"
            @editorContent="getEditorContent"
            style="width: 800px;"
          ></WangEditor>
        </el-form-item>
      </el-form>
      <div style="height: 140px;"></div>
    </el-card>
    <el-card class="fixed-card">
      <el-button  size="small" type="primary" @click="submitForm('formValidate')"
        >保存</el-button
      >
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
import {  createNotice, noticeDetail, noticeUpdateApi } from "@/api/system";
import { merCategoryListApi, merSelectApi } from "@/api/product";
import WangEditor from '@/components/wangEditor/index.vue';
import { roterPre } from '@/settings'
export default {
  name: 'EditArticle',
  components: { WangEditor },
  data() {
    return {
      activeName: "first",
      roterPre: roterPre,
      sleOptions: {
        title: '',
        article_category_id: ''
      },
      defaultProps: {
        children: 'children',
        label: 'title'
      },
      list: [],
      grid: {
        xl: 12,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24
      },
      formValidate: {
        type: 4,
        notice_title: "",
        notice_content: "",
        is_trader: "",
        mer_id: [],
        category_id: [],
      },
      ruleValidate: {
        notice_title: [
          { required: true, message: "请输入消息名称", trigger: "blur" }
        ],
        notice_content: [
          { required: true, message: "请输入消息内容", trigger: "blur" }
        ]
      },
      tempRoute: {},
      merCateList: [],
      merSelect: []
    }
  },
  mounted() {
    const task1 = this.getCategorySelect();
    this.getMerSelect();
    if (this.$route.params.id) {
      const task2 = this.getDetails();
      Promise.all([task1, task2]).then(() => {
        const categoryLabelSet = new Set(this.formValidate.category);
        const category_id = this.merCateList.reduce((acc, item) => {
          if (categoryLabelSet.has(item.category_name)) {
            acc.push(item.merchant_category_id);
          }
          return acc;
        }, []);
        this.formValidate.category_id = category_id;
      });
    }
  },
  methods: {
    // 返回
    back() {
      this.$router.push({ path: `${roterPre}/station/notice` })
    },
    // 店铺分类；
    getCategorySelect() {
      return merCategoryListApi()
        .then(res => {
          this.merCateList = res.data.list;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 店铺列表；
    getMerSelect() {
      merSelectApi()
        .then(res => {
          this.merSelect = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 提交数据
    submitForm(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          let funApi = this.$route.params.id ? noticeUpdateApi(this.$route.params.id, this.formValidate) : createNotice(this.formValidate)
          funApi.then(async res => {
            this.$message.success(res.message);
            setTimeout(() => {
              this.back();
            }, 500)
          })
          .catch(res => {
            this.$message.error(res.message);
          });
        } else {
          return false
        }
      })
    },
    getDetails() {
      return noticeDetail(this.$route.params.id).then(res => {
        this.formValidate = res.data;
      }).catch(res => {
        this.$message.error(res.message);
      })
    },
    // 文章详情
    getEditorContent(data) {
      this.formValidate.notice_content = data;
    },
  }
}
</script>

<style scoped lang="scss">
::v-deep .el-pagination__jump{
  margin-left: 0;
}
::v-deep .el-tree-node__content{
  height: 34px;
  font-weight: normal;
}
.footer{
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  height: 66px;
  box-shadow: 0px 4px 10px 0px rgba(0,0,0,0.15);
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 2;
}
.w-440{
  width: 440px;
}
.fixed-card {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
  text-align: center;
}
</style>

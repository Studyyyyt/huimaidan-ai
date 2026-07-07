<template>
  <div class="divBox">
    <el-card class="box-card">
      <!-- 当 FormData 存在时，渲染表单创建组件 -->
      <form-create
        v-if="FormData"
        ref="fc"
        v-loading="loading"
        :option="option"
        :rule="FormData.rule"
        class="formBox"
        handle-icon="false"
        :cmovies="movies"
        @submit="onSubmit"
      />
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
import formCreate from '@form-create/element-ui'
import { createBroadcastProApi } from '@/api/marketing'
import request from '@/api/request'
import { roterPre } from '@/settings'

export default {
  name: 'CreateProduct',
  data() {
    return {
      option: {
        form: {
          labelWidth: '150px',
          size: "small",
        },
        global: {
          upload: {
            props: {
              onSuccess(rep, file) {
                if (rep.status === 200) {
                  file.url = rep.data.src
                }
              }
            }
          }
        }
      },
      FormData: null,
      loading: false,
      movies: 1,
      titles: ""
    }
  },
  components: {
    formCreate: formCreate.$form()
  },
  mounted() {
    // 调用获取表单数据的方法
    this.getFormData();
  },
  watch:{
    // 路由变化时，重新获取表单数据
    '$route.path': {
      handler: function() {
        this.getFormData();
      },
      immediate: false,
      deep: true
    },
  },
  methods: {
    setTagsViewTitle() {
      this.deepTraversal(this.menuList, "children");
      const route = Object.assign({}, this.tempRoute, { title: this.titles });
      this.$store.dispatch("tagsView/updateVisitedView", route);
    },
    deepTraversal(arr, child) {
      const that = this;
      function traversal(a) {
        a.forEach(o => {
          if (
            o.path.indexOf("Basics") !== -1 &&
            o.path === that.$route.path
          ) {
            that.titles = o.title;
            return;
          }
          if (o[child] && o[child].length) {
            traversal(o[child]);
          }
        });
      }
      traversal(arr);
    },
    getFormData() {
      this.loading = true;
      sessionStorage.setItem("singleChoice", 1);
      createBroadcastProApi()
        .then(async (res) => {
          this.FormData = res.data;
          this.loading = false;
        })
        .catch((res) => {
          this.$message.error(res.message);
          this.loading = false;
        });
    },
    onSubmit(formData) {
      request[this.FormData.method.toLowerCase()](
        this.FormData.api,
        formData
      )
        .then((res) => {
          this.$message.success(res.message || "提交成功");
          this.$router.push({ path: `${roterPre}/marketing/broadcast/list` });
        })
        .catch((err) => {
          this.$message.error(err.message || "提交失败");
        });
    },
  },
};
</script>
<style scoped lang="scss">


</style>

<template>
  <div class="divBox">
    <el-card class="box-card">
      <div class="container">
        <div class="demo-input-suffix acea-row">
          <el-form label-width="120px" size="small" :rules="ruleValidate" :model="formValidate" ref="formValidate">
            <el-form-item label="参数模板名称：" prop="template_name">
              <el-input v-model="formValidate.template_name" placeholder="请输入参数模板名称" :maxlength="30" class="pageWidth" clearable/>
            </el-form-item>
            <el-form-item label="平台分类：" prop="cate_ids">
              <el-cascader v-model="formValidate.cate_ids" class="pageWidth" filterable :options="cateList" :props="{ multiple: true, emitPath: false }" clearable />
            </el-form-item>
            <el-form-item label="排序：">
              <el-input-number :min="0" v-model="formValidate.sort" controls-position="right" label="排序" class="pageWidth" />
            </el-form-item>
            <el-form-item label="">
              <el-table
                :data="data"
                border
                size="small"
              >
                <el-table-column align="center" prop="name" label="参数名称" min-width="150">
                  <template slot-scope="scope">
                    <el-input v-model="scope.row.name" size="small" class="priceBox"/>
                  </template>
                </el-table-column>
                <el-table-column label="参数值" min-width="300">
                  <template slot-scope="scope">
                    <!-- <el-input v-model="scope.row.value" size="small" class="priceBox"/> -->
                    <div class="arrbox">
                      <el-tag
                        @close="handleClose(scope.$index,index)"
                        :name="item"
                        :closable="true"
                        v-for="(item, index) in scope.row.values"
                        :key="index"
                        size="small"
                        class="tags-item"
                      >
                        {{ item.value }}
                      </el-tag>
                      <input
                        size="small"
                        class="arrbox_ip width100"
                        v-model="scope.row.single"
                        :maxlength="15"
                        placeholder="请输入选项，回车确认"
                        @blur="addSpecList(scope.row,scope.$index)"
                        @keyup.enter="addSpecList(scope.row,scope.$index)"
                      />
                    </div>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="排序" min-width="200">
                  <template slot-scope="scope">
                    <el-input-number v-model="scope.row.sort" :min="0" size="small" class="priceBox" controls-position="right"/>
                  </template>
                </el-table-column>
                <el-table-column label="操作" min-width="100">
                  <template slot-scope="scope">
                    <el-button type="text" size="small" class="submission" @click="delSpecs(scope.row,scope.$index)">删除</el-button>
                  </template>
                </el-table-column>
              </el-table>
            </el-form-item>
            <el-form-item>
              <el-button size="small" @click="onAdd">添加参数</el-button>
            </el-form-item>
          </el-form>
        </div>
      </div>
    </el-card>
    <div class="footer">
      <el-button size="small" type="primary" @click="handleSubmit('formValidate')">保存</el-button>
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
import {
  productSpecs, specsUpdate, productSpecsInfo, categoryListApi
} from "@/api/product";
export default {
  name: "specsCreate",
  data() {
    return {
      listLoading: true,
      ruleValidate: {
        template_name: [
          { required: true, message: "请输入参数模板名称", trigger: "blur" },
        ],
        cate_ids: [
          { required: true, message: "请选择平台分类", trigger: "change" },
        ]
      },
      data: [],
      cateList: [],
      formValidate: {
        cate_ids: [],
        sort: 0,
        delete_params: []
      },
     ids: []
    };
  },
  created() {
    this.onAdd();
    if(this.$route.params.id)this.getInfo();
    this.getCategorySelect();
  },
  mounted() {

  },
  methods: {
    // 添加
    onAdd() {
      let obj = { name: "", values: [], sort: 0,  parameter_id: 0,single: "" };
      this.data.push(obj);
    },
    // 编辑
    onEdit(id) {
      this.$modalForm(levelUpdateApi(id)).then(() => this.getList(''));
    },
    // 输入后回车
    addSpecList(item,index) {
      if (!item.single) {
        return
      }
      let count = this.data[index].values.indexOf(item)
      if (count === -1) {
       this.data[index].values.push({value:item.single})
      }
      item.single = ''
    },
    handleClose(index, idx) {
      this.data[index]['values'].splice(idx, 1)
    },
    getInfo() {
      productSpecsInfo(this.$route.params.id).then((res) => {
        let cate_ids = []
        if(this.$route.params.id){
          if(this.$route.query.type != 'copy'){
            res.data.cateId.forEach((item,index)=>{
              cate_ids.push(item.category.store_category_id)
            })
          }
        }
        this.formValidate = {
          template_name: res.data.template_name,
          sort: res.data.sort,
          cate_ids: cate_ids
        }
        this.data = res.data.parameter
      });
    },
     // 平台分类；
    getCategorySelect() {
      categoryListApi().then(res => {
        this.cateList = res.data
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    // 删除
    delSpecs(item,idx) {
      this.ids.push(item.parameter_id)
      this.formValidate.delete_params = this.ids
      this.data.splice(idx, 1);
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.formValidate.params = this.data;
          for (let i = 0; i < this.formValidate.params.length; i++) {
            let data = this.formValidate.params[i];
            if (!data.name.trim()) {
              return this.$message.error("请输入参数名称");
            }
            if (!data.values.length) {
              return this.$message.error("请输入参数值");
            }
          }
          this.$route.params.id && this.$route.query.type != 'copy' ?
          specsUpdate(this.$route.params.id, this.formValidate)
            .then((res) => {
              this.$message.success(res.message);
              this.$router.push({ path: "/admin/product/specs" });
            })
            .catch((err) => {
              this.$message.error(err.message);
            }) :
          productSpecs(this.formValidate)
            .then((res) => {
              this.$message.success(res.message);
              this.$router.push({ path: "/admin/product/specs" });
            })
            .catch((err) => {
              this.$message.error(err.message);
            });
        } else {
          // this.$message.error("请输入参数模板名称");
        }
      });
    },
  },
};
</script>

<style scoped lang="scss">
.box-card {
  min-height: 74vh;
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
.tags-item {
  margin: 2px 6px 2px 0;
}
</style>

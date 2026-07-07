<template>
  <div class="divBox">
    <el-card class="box-card">
      <div slot="header">
        <div class="container">
          <div class="demo-input-suffix acea-row">
            <el-form label-width="120px" :rules="ruleValidate" :model="formValidate" ref="formValidate">
              <el-form-item label="参数模板名称：" prop="template_name">
                <el-input v-model="formValidate.template_name" :maxlength="30" size="small" placeholder="请输入参数模板名称" class="pageWidth" />
              </el-form-item>
              <el-form-item label="排序：">
                <el-input-number :min="0" size="small" v-model="formValidate.sort" label="排序" class="pageWidth" controls-position="right"/>
              </el-form-item>
              <el-form-item label="">
                <el-table
                  :data="data"
                  border
                  size="small"
                >
                  <el-table-column align="center" prop="name" label="参数名称" min-width="150">
                    <template slot-scope="scope">
                    <el-input v-model="scope.row.name" size="small" placeholder="请输入参数名称" class="priceBox"/>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="参数值" min-width="300">
                    <template slot-scope="scope">
                      <!-- <el-input v-model="scope.row.value" size="small" placeholder="请输入参数值" class="priceBox"/> -->
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
                          placeholder="请输入选项，回车确认"
                          :maxlength="15"
                          @blur="addSpecList(scope.row,scope.$index)"
                          @keyup.enter="addSpecList(scope.row,scope.$index)"
                        />
                      </div>
                    </template>
                  </el-table-column>
                  <el-table-column align="center" label="排序" min-width="200">
                    <template slot-scope="scope">
                      <el-input-number v-model="scope.row.sort" size="small" :min="0" class="priceBox" controls-position="right"/>
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" min-width="100">
                    <template slot-scope="scope">
                      <el-button type="text" class="submission" size="small" @click="delSpecs(scope.row,scope.$index)">删除</el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </el-form-item>
              <el-form-item>
                <el-button size="small" type="primary" @click="onAdd">添加参数</el-button>
              </el-form-item>
            </el-form>
          </div>
        </div>

      </div>
    </el-card>
    <el-card>
      <el-form label-width="120px">
        <el-form-item>
          <el-button size="small" type="primary" @click="handleSubmit('formValidate')">保存</el-button>
        </el-form-item>
      </el-form>

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
import { productSpecs, specsUpdate, productSpecsInfo } from "@/api/product";
import { roterPre } from '@/settings'
export default {
  name: "specsCreate",
  data() {
    return {
      listLoading: true,
      ruleValidate: {
        template_name: [
          { required: true, message: "请输入参数模板名称", trigger: "blur" },
        ],
      },
      data: [],
      single: "",
      cateList: [],
      formValidate: {
        sort: 0,
        delete_params: []
      },
      ids: []
    };
  },
  created() {
    this.onAdd();
    if(this.$route.params.id)this.getInfo();
  },
  mounted() {

  },
  methods: {
    // 添加
    onAdd() {
      let obj = { name: "", values: [], sort: 0,  parameter_id: 0, single: "" };
      this.data.push(obj);
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
    // 编辑
    onEdit(id) {
      this.$modalForm(levelUpdateApi(id)).then(() => this.getList(''));
    },
    getInfo() {
      productSpecsInfo(this.$route.params.id).then((res) => {
        this.formValidate = res.data
        this.data = res.data.parameter
      });
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
          this.$route.params.id ?
          specsUpdate(this.$route.params.id, this.formValidate)
            .then((res) => {
              this.$message.success(res.message);
              this.$router.push({ path: `${roterPre}/product/specs` });
            })
            .catch((err) => {
              this.$message.error(err.message);
            }) :
          productSpecs(this.formValidate)
            .then((res) => {
              this.$message.success(res.message);
              this.$router.push({ path: `${roterPre}/product/specs` });
            })
            .catch((err) => {
              this.$message.error(err.message);
            });
        } else {
          this.$message.error("请输入参数模板名称");
        }
      });
    },
  },
};
</script>

<style scoped lang="scss">
.tags-item {
  margin: 2px 6px 2px 0;
}
</style>

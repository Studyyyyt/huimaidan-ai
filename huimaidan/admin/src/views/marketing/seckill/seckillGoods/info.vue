<template>
  <el-dialog
    v-if="dialogVisible"
    title="商品审核"
    :visible.sync="dialogVisible"
    width="670px"
    :before-close="handleClose"
    class="projectInfo"
  >
    <el-form ref="ruleForm" :model="ruleForm" :rules="rules" label-width="80px" class="demo-ruleForm">
      <el-form-item label="审核状态" prop="status">
        <el-radio-group v-model="ruleForm.status">
          <el-radio :label="1">通过</el-radio>
          <el-radio :label="-1">拒绝</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-if="ruleForm.status===-1" label="原因" prop="refusal">
        <el-input v-model="ruleForm.refusal" type="textarea" placeholder="请输入原因" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="onSubmit">提交</el-button>
      </el-form-item>
    </el-form>
  </el-dialog>
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
import { seckillProductDetailApi, seckillProductStatusApi } from '@/api/product'
const defaultObj = {
  image: '',
  slider_image: [],
  store_name: '',
  store_info: '',
  keyword: '',
  brand_id: '', // 品牌id
  cate_id: '', // 平台分类id
  mer_cate_id: [], // 店铺分类id
  unit_name: '',
  sort: 0,
  is_show: 0,
  is_benefit: 0,
  is_new: 0,
  is_good: 0,
  temp_id: '',
  attrValue: [{
    image: '',
    price: null,
    cost: null,
    ot_price: null,
    stock: null,
    bar_code: null,
    bar_code_number: null,
    weight: null,
    volume: null
  }],
  attr: [],
  selectRule: '',
  extension_type: 0,
  content: '',
  spec_type: 0
}
const objTitle = {
  price: {
    title: '售价'
  },
  cost: {
    title: '成本价'
  },
  ot_price: {
    title: '划线价'
  },
  stock: {
    title: '库存'
  },
  bar_code: {
    title: '规格编码'
  },
  bar_code_number: {
    title: '条形码'
  },
  weight: {
    title: '重量（KG）'
  },
  volume: {
    title: '体积(m³)'
  }
}
const proOptions = [{ name: '热门榜单', value: 'is_hot' }, { name: '促销单品', value: 'is_benefit' }, { name: '精品推荐', value: 'is_best' }, { name: '首发新品', value: 'is_new' }]
export default {
  name: 'Info',
  props: {
    isShow: {
      type: Boolean,
      default: true
    },
    ids: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      rules: {
        status: [
          { required: true, message: '请选择审核状态', trigger: 'change' }
        ],
        refusal: [
          { required: true, message: '请填写拒绝原因', trigger: 'blur' }
        ]
      },
      proId: 0,
      ruleForm: {
        refusal: '',
        status: 1,
        id: ''
      },
      formThead: Object.assign({}, objTitle),
      manyTabDate: {},
      manyTabTit: {},
      loading: false,
      dialogVisible: false,
      projectData: {},
      recommend: proOptions,
      OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])] // 多规格
    }
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, defaultObj.attrValue[0])
      delete obj.image
      return obj
    },
  },
  methods: {
    onSubmit() {
      this.ruleForm.id = this.ids
      seckillProductStatusApi(this.ruleForm).then(res => {
        this.$message.success(res.message)
        this.dialogVisible = false
        this.$emit('subSuccess')
      }).catch(res => {
        this.listLoading = false
        this.$message.error(res.message)
      })
    },
    handleClose() {
      this.dialogVisible = false
    },
    // getInfo(id) {
    //   this.proId = id
    //   this.loading = true
    //   seckillProductDetailApi(id).then(res => {
    //     this.projectData = res.data
    //     if (this.projectData.spec_type === 0) {
    //       this.OneattrValue = res.data.attrValue
    //     } else {
    //       this.ManyAttrValue = res.data.attrValue
    //     }
    //     const tmp = {}
    //     const tmpTab = {}
    //     this.projectData.attr.forEach((o, i) => {
    //       tmp['value' + i] = { title: o.value }
    //       tmpTab['value' + i] = ''
    //     })
    //     this.manyTabDate = tmpTab
    //     this.formThead = Object.assign({}, this.formThead, tmp)
    //     this.loading = false
    //   }).catch(res => {
    //     this.$message.error(res.message)
    //     this.loading = false
    //   })
    // }
  }
}
</script>

<style scoped lang="scss">
  .projectInfo ::v-deep .el-tabs__content{
    padding-left: 10px !important;
  }
  .projectInfo ::v-deep .el-dialog__body{
    padding-top: 0 !important;
  }
  .tabPic{
    width: 40px !important;
    height: 40px !important;
    img{
      width: 100%;
      height: 100%;
    }
  }
  .sp {
    display: block;
    width: 33%;
    margin-bottom: 20px;
  }

  .sp100 {
    width: 100%;
    margin-bottom: 15px;
    display: inline-block;
  }
  .third{
    width: 100%;
    display: flex;
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;
    position: relative;
    cursor: pointer;
    display: inline-block;
    img {
      width: 100%;
      height: 100%;
    }
  }
  .demo-image__preview{
    display: inline-block;
  }
  .contentPic{
    text-align: center;
  }
</style>

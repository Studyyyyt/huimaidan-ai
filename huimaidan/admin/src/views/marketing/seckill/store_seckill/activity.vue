<template>
  <el-dialog
    :before-close="onClose"
    title="批量设置"
    :visible.sync="showStatus"
    width="620px"
  >
    <el-form ref="form" size="small" :model="form" label-width="80px">
      <el-form-item v-if="type == 'limit'" label="限量：">
        <el-radio-group v-model="form.limit_type">
          <el-radio label="0">增加</el-radio>
          <el-radio label="1">减少</el-radio>
          <el-radio label="2">固定数量</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-if="type == 'limit'">
        <el-input-number
          v-model="form.activity_stock"
          :step="1"
          step-strictly
          type="number"
          controls-position="right"
          :min="1"
          :max="99999"
          class="pageWidth"
        >
        </el-input-number>
      </el-form-item>
      <el-form-item v-if="type == 'price'" label="秒杀价：">
        <el-radio-group v-model="form.type">
          <el-radio label="0">增加</el-radio>
          <el-radio label="1">减少</el-radio>
          <el-radio label="2">折扣(%)</el-radio>
          <el-radio label="3">固定价格</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item v-if="type == 'price'">
        <el-input-number
          v-model="form.price"
          type="number"
          :precision="2"
          :min="0"
          :max="99999"
          controls-position="right"
          :controls="false"
          class="pageWidth"
        >
        </el-input-number> &nbsp;{{form.type==2?'%':'元'}}
        <p style="color:#F56464;font-size: 13px;">注：批量设置价格为敏感操作，请谨慎操作！</p>
      </el-form-item>
      <el-form-item label="是否开启：">
        <el-switch v-model="form.status" :width="55" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭" />
      </el-form-item>
    </el-form>
     <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="onClose">取 消</el-button>
        <el-button size="small" type="primary" @click="confirmSet">确 定</el-button>
      </span>
  </el-dialog>
</template>

<script>
export default {
  name: '',
  data() {
    return {
      showStatus: false,
      type: "limit",
      form: {
        type: '0',
        limit_type: "0",
        status: 1,
        price: '',
        discount: '',
        activity_stock: '',
        status: 1
      },
    };
  },
  methods: {
    showModal(value) {
      this.type = value;
      this.showStatus = true;
    },
    onClose() {
      let that = this
      that.showStatus = false;
      setTimeout(() => {
        that.form = {
          limit_type: "0",
          type: '0',
          status: 1,
          price: '',
          discount: '',
          activity_stock: '',
        }
      },500)
      
    },
    confirmSet() {
      this.$emit('onChange', this.form, this.type);
      this.onClose();
    },
  },
};
</script>

<style lang="scss" scoped>

</style>

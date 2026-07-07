<template>
  <el-dialog
    v-if="dialogVisible"
    title="添加会员"
    :visible.sync="dialogVisible"
    width="540px"
  >
     <el-form class="form" ref="ruleForm" :model="userForm" :rules="rules" label-width="100px" size="small">
        <el-form-item label="手机号：" prop="phone">
          <el-input v-model="userForm.phone" placeholder="请输入手机号" class="width100" /> 
        </el-form-item>
        <el-form-item label="用户昵称：" prop="nickname">
          <el-input v-model="userForm.nickname" placeholder="请输入用户昵称" class="width100" /> 
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false" size="small">取消</el-button>
        <el-button type="primary" @click="handleSubmit('ruleForm')" size="small">确定</el-button>
      </span>  
  </el-dialog>
</template>
<script>
import { addMembersApi } from '@/api/order';
export default {
  name: "AddMember",
  components: {
   
  },
  data() {
    return { 
      dialogVisible: false,
      userForm: {
        phone: "",
        nickname: ""
      },
      rules: {
        phone: [
          { required: true, message: '请输入手机号', trigger: 'blur' },
          { pattern: /^1[3456789]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
        ],
        nickname: [
          { required: true, message: '请输入用户昵称', trigger: 'blur' }
        ],
      },
    }
  },
  created() {},
  mounted() {},
  methods: {
    onClose(){
      this.dialogVisible = false
    },
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          addMembersApi(this.userForm)
            .then((res) => {
              this.$message.success('添加成功');
              this.dialogVisible = false;
              this.$emit('getUserDetail',res.data)
            })
            .catch((err) => {
              this.$message.error(err.message);
            });
        }
      })
    }
  },
};
</script>
<style lang="scss" scoped>
  
</style>

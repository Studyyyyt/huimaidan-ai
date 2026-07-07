<template>
  <div>
    <el-dialog
      v-if="dialogVisible"
      title="查询会员"
      :visible.sync="dialogVisible"
      width="500px"
    >
      <div class="member">
        <img class="avatar" src="@/assets/images/f.png" alt="">
        <div class="input">
          <el-input size="large" v-model="keyword" placeholder="请输入用户手机号/ID/昵称查询" class="width100" /> 
        </div>
        <div class="mt24 acea-row row-between">
          <div class="btn btn-default" @click="add">添加会员</div>
          <div class="btn btn-primary" @click="search">查询会员</div>
        </div>
      </div>
    </el-dialog>
    <addMember ref="addMember" @getUserDetail="getUserDetail" />
    <!--选择用户-->
    <userList 
      ref="userList" 
      @getUserDetail="getUserDetail"/>
  </div>
</template>
<script>
import addMember from "./addMember";
import userList from "./userList";
import { searchMembers } from '@/api/order';
export default {
  name: "SearchMember",
  components: { addMember, userList },
  data() {
    return { 
      dialogVisible: false,
      keyword: "",
      userInfo: {}
    }
  },
  created() {},
  mounted() {},
  methods: {
    onClose(){
      this.dialogVisible = false
    },
    add() {
      this.$refs.addMember.dialogVisible = true
    },
    search() {
      if(!this.keyword){
        return this.$message.error("请输入查询条件")
      }else{
        searchMembers({search:this.keyword})
        .then((res) => {
          if(res.data.list.length > 1){
            this.$refs.userList.getSearchData(this.keyword)
            this.$refs.userList.dialogVisible = true;
          }else if(res.data.list.length == 1){
            let info = res.data.list[0]
            this.getUserDetail(info)
          }else{
            this.$confirm('未查询到相关用户', '查询结果', {
              confirmButtonText: '返回',
              type: 'warning',
              showCancelButton: false
            }).then(() => {}).catch(() => {});
          }
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
      }
    },
    resetData() {
      this.$refs.userList.templateRadio = 0;
    },
    getUserDetail(info) {
      this.$emit('getUserDetail',info)
      this.dialogVisible = false
    }
  },
};
</script>
<style lang="scss" scoped>
  .member {
    padding: 0 50px 24px;
    text-align: center;
    .avatar {
      width: 80px;
      height: 80px;
      border-radius: 100%;
    }
    .input {
      margin-top: 24px;
      ::v-deep .el-input__inner {
        text-align: center;
      }
    }
    .btn {
      width: 176px;
      height: 46px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      font-weight: 500;
      cursor: pointer;
    }
    .btn-default {
      color: #606266;
      background: #F5F5F5;
    }
    .btn-primary {
      color: #fff;
      background: var(--prev-color-primary);
    }
  }
</style>

<template>
  <div>
    <el-dialog
      title="选择服务人员"
      :visible.sync="dialogTableVisible"
      :before-close="handleClose"
    >
      <div class="mb10">
        人员搜索：
        <el-input
          v-model="where.name"
          placeholder="请输入人员姓名搜索"
          style="width: 200px"
          size="small"
          class="mr10"
          @change="getSelectStaff"
        />
        手机号：
        <el-input
          v-model="where.phone"
          placeholder="请输入手机号码搜索"
          style="width: 200px"
          size="small"
          class="mr10"
          @change="getSelectStaff"
        />
        <el-button type="primary" size="small" @click="getSelectStaff"
          >搜索</el-button
        >
        <el-button size="small" @click="reset">重置</el-button>
      </div>
      <el-table :data="tableData" height="400px">

        <template v-if="deliveryType == 'reservation'">
          <el-table-column width="55">
            <template slot-scope="scope">
              <span
                v-if="staffs_id == scope.row.staffs_id"
                class="iconfont iconzidongxuanze"
                @click="iconChange(scope.row.staffs_id)"
              />
              <span
                v-else
                class="iconfont-h5 icon-ic_unselect"
                @click="iconChange(scope.row.staffs_id)"
              />
            </template>
          </el-table-column>
          <el-table-column property="staffs_id" label="id" width="150">
          </el-table-column>
        </template>
        <template v-else>
          <el-table-column width="55">
            <template slot-scope="scope">
              <span
                v-if="staffs_id == scope.row.service_id"
                class="iconfont iconzidongxuanze"
                @click="iconChange(scope.row.service_id)"
              />
              <span
                v-else
                class="iconfont-h5 icon-ic_unselect"
                @click="iconChange(scope.row.service_id)"
              />
            </template>
          </el-table-column>
          <el-table-column property="service_id" label="id" width="150">
          </el-table-column>
        </template>
        <el-table-column
          property="name"
          label="服务人员"
          width="100"
        ></el-table-column>
        <el-table-column property="phone" label="手机号码"></el-table-column>
        <el-table-column property="address" label="特别提示">
          <template slot-scope="scope">
            <span>{{ scope.row.remark || "--" }}</span>
          </template>
        </el-table-column>
      </el-table>
      <div class="mt10" slot="footer">
        <el-button @click="handleClose()" size="small">取消</el-button>
        <el-button type="primary" size="small" @click="submitOk()"
          >确定</el-button
        >
      </div>
    </el-dialog>
  </div>
</template>
<script>
import { staffListApi } from "@/api/system";
import { deliveryPersonLst } from '@/api/systemForm';
export default {
  data() {
    return {
      tableData: [],
      staffs_id: "", // 服务人员id
      where: {
        name: "",
        phone: "",
        status: 1
      },
      type: "",
      deliveryType: "",
      dialogTableVisible: false
    };
  },
  methods: {
    // 选择服务人员
    getSelectStaff() {
      staffListApi(this.where).then(res => {
        this.tableData = res.data.list;
      });
    },
    handleClose() {
      this.dialogTableVisible = false;
      this.staffs_id = "";
      this.where = {
        name: "",
        phone: "",
        status: 1
      };
    },
    reset() {
      this.where = {
        name: "",
        phone: "",
        status: 1
      };
      this.getSelectStaff();
    },
    iconChange(id) {
      this.staffs_id = id;
    },
    submitOk() {
      if (!this.staffs_id) return this.$message.error("请选择服务人员");
      this.deliveryType == "reservation" ?
      this.$emit("selectStaff", this.staffs_id, this.type) 
      : this.$emit("deliveryDispatch", this.staffs_id, this.type)
      this.handleClose();
    },
    openBox(type, id, deliveryType) {
      this.type = type;
      this.deliveryType = deliveryType;
      this.deliveryType == "reservation" ? 
      this.getSelectStaff() : this.getDeliveryLst();
      this.dialogTableVisible = true;
    },
    // 获取配送员信息
    getDeliveryLst() {
      deliveryPersonLst(this.where).then(res => {
        this.tableData = res.data.list;
      });
    }
  }
};
</script>
<style scoped lang="scss">
.iconzidongxuanze {
  cursor: pointer;
  font-size: 16px;
  margin-left: 10px;
  cursor: pointer;
  color: var(--prev-color-primary);
}
.iconfont-h5 {
  cursor: pointer;
  font-size: 16px;
  margin-left: 10px;
  cursor: pointer;
  color: #cccccc;
}
</style>

<template>
  <div class="divBox">
    <div class="selCard">
      <div class="container">
        <el-form
          :model="tableFrom"
          ref="searchForm"
          size="small"
          label-width="auto"
          inline
        >
          <el-form-item label="预约人员：">
            <el-input
              v-model="tableFrom.reservation_keyword"
              clearable
              placeholder="请输入预约人员姓名电话搜索"
              class="selWidth"
              @keydown="searchList"
            />
          </el-form-item>
          <el-form-item label="服务人员：">
            <el-select
              v-model="tableFrom.staff_id"
              placeholder="请选择服务人员"
              class="selWidth"
              clearable
              @change="searchList"
              style="width: 200px;"
            >
              <el-option
                v-for="item in staffList"
                :key="item.staffs_id"
                :label="item.name"
                :value="item.staffs_id"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="服务方式：">
            <el-select
              v-model="tableFrom.service_type"
              placeholder="请选择服务方式"
              class="selWidth"
              clearable
              style="width: 200px;"
              @change="searchList"
            >
              <el-option
                v-for="item in serviceList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              ></el-option>
            </el-select>
          </el-form-item>
          <select-search
            ref="selectSearch"
            :select="select"
            :searchSelectList="searchSelectList"
            @search="getSelectList"
          />
          <el-form-item>
            <el-button type="primary" size="small" @click="searchList"
              >搜索</el-button
            >
            <el-button size="small" @click="reset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <el-card class="mt14 board-card">
      <!-- 预约服务展示面板 -->
      <fullCalendar
        ref="fullCalendar"
        :tableFrom="tableFrom"
        @onOrderDetails="onOrderDetails"
      />
      <!--详情-->
      <order-detail
        ref="orderDetail"
        :orderId="orderId"
        :drawer="drawer"
        @closeDrawer="closeDrawer"
        @changeDrawer="changeDrawer"
        @reSend="reSend"
        @reDriving="reDriving"
        @onOrderRefund="onOrderRefund"
        @getlist="searchList"
      ></order-detail>
    </el-card>
    <!--退款-->
    <order-refund ref="orderRefund" @refundSuccess="refundSuccess" />
  </div>
</template>
<script>
import orderDetail from "../../order/orderDetails.vue";
import orderRefund from "../../order/orderRefund.vue";
import { staffListApi } from "@/api/system";
import { orderDetailApi, orderReDriving } from "@/api/order";
import selectSearch from "@/components/base/selectSearch";
import fullCalendar from "./fullCalendar";
export default {
  name: "",
  components: { selectSearch, fullCalendar, orderDetail, orderRefund },
  props: {},
  data() {
    return {
      tableFrom: {
        reservation_keyword: "",
        service_type: "",
        staff_id: "",
        uid: "",
        phone: "",
        nickname: ""
      },
      orderId: "",
      drawer: false,
      select: "",
      staffList: [],
      serviceList: [
        {
          id: "0",
          name: "上门服务"
        },
        {
          id: 1,
          name: "到店服务"
        }
      ],
      searchSelectList: [
        { label: "全部", value: "" },
        { label: "昵称", value: "nickname" },
        { label: "用户ID", value: "uid" },
        { label: "手机号", value: "phone" }
      ]
    };
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {
    this.getSelectStaff();
  },
  methods: {
    // 选择服务人员
    getSelectStaff() {
      staffListApi(this.where).then(res => {
        this.staffList = res.data.list;
      });
    },
    onOrderDetails(id) {
      this.orderId = id;
      this.$refs.orderDetail.getInfo(id);
      this.drawer = true;
    },
    reset() {
      this.$refs.selectSearch.resetParmas();
      for (let key in this.tableFrom) {
        this.tableFrom[key] = "";
      }
      this.$refs.fullCalendar.getList();
    },
    closeDrawer() {
      this.drawer = false;
    },
    changeDrawer(v) {
      this.drawer = v;
    },

    // 复打
    reDriving() {
      orderReDriving(this.orderId)
        .then(res => {
          // if (res.data.label) this.printImg(res.data.label);
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 修改发货信息
    reSend(id) {
      this.isBatch = false;
      this.sendVisible = true;
      this.orderId = id;
      this.isResend = true;
      delete this.shipment.order_id;
      orderDetailApi(id)
        .then(res => {
          const data = res.data;
          this.shipment.delivery_type =
            !this.isDump && !mer_dump_switch && data.delivery_type == 4
              ? 1
              : Number(data.delivery_type);
          this.noLogistics = data.delivery_type;
          this.loading = false;
          this.original = {
            delivery_name: data.delivery_name,
            delivery_id: data.delivery_id
          };
          this.loading = false;
        })
        .catch(({ message }) => {
          this.loading = false;
          this.$message.error(message);
        });
    },
    // 订单退款
    onOrderRefund(id) {
      this.$refs.orderRefund.getOrderDetails(id);
    },
    // 退款回调
    refundSuccess() {
      setTimeout(() => {
        this.drawer = false;
        this.searchList();
      }, 500);
    },
    getSelectList(val){
      this.tableFrom.nickname = val.nickname;
        this.tableFrom.uid = val.uid;
        this.tableFrom.phone = val.phone;
        this.$refs.fullCalendar.getList();
    },
    searchList(val) {
      this.$refs.selectSearch.changeSearch();
    }
   
  }
};
</script>
<style scoped lang="scss"></style>

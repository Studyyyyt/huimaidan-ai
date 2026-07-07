<template>
  <!-- 支付订单 -->
  <div class="order-bgc">
    <div class="putSupplier" v-for="(item, index) in orderData" :key="index">
      <div class="header acea-row row-between-wrapper">
          <div class="left acea-row row-middle">
            <div class="picture" :id="'qrCodeUrl' + index"></div>
            <div class="info">
              <div><span class="name">收货人：</span>{{ item.real_name }}</div>
              <div><span class="name">收货地址：</span>{{ item.user_address }}</div>
              <div>
                <span class="name">手机号：</span><span>{{ item.user_phone }}</span>
              </div>
            </div>
          </div>
          <div class="info">
            <div><span class="name">订单编号：</span>{{ item.order_sn }}</div>
            <div><span class="name">支付时间：</span>{{ item.pay_time }}</div>
            <div><span class="name">支付方式：</span>{{ item.pay_type | orderPayType }}</div>
          </div>
        </div>
        <div class="mt20">
          <el-table border size="small" :data="item.orderProduct" :disabled-hover="true">
            <el-table-column label="商品编号" align="center">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.$index+1 }} </span>
              </template>
            </el-table-column>
            <el-table-column label="商品名称">
              <template slot-scope="scope">
                <span class="nickname line2">{{ scope.row.cart_info&&scope.row.cart_info.product.store_name }} </span>
              </template>
            </el-table-column>
            <el-table-column label="商品规格">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.row.cart_info.productAttr.sku }} </span>
              </template>
            </el-table-column>
            <el-table-column label="规格编码">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.row.cart_info&&scope.row.cart_info.productAttr.bar_code }} </span>
              </template>
            </el-table-column>
            <el-table-column label="条形码">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.row.cart_info&&scope.row.cart_info.productAttr.bar_code_number }}</span>
              </template>
            </el-table-column>
            <el-table-column label="数量">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.row.product_num }} </span>
              </template>
            </el-table-column>
            <el-table-column label="金额">
              <template slot-scope="scope">
                <span class="nickname">{{ scope.row.total_price }}</span>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="bottom acea-row row-between-wrapper">
          <div class="acea-row row-middle">
            <div class="item"><span class="name">运费：</span>{{ item.pay_postage }}</div>
            <div class="item"><span class="name">平台优惠：</span>{{ item.platform_coupon_price }}</div>
            <div class="item"><span class="name">商户优惠：</span>{{ item.coupon_price }}</div>
            <div class="item"><span class="name">付费会员优惠：</span>{{ item.svip_discount }}</div>
            <div class="item"><span class="name">积分抵扣：</span>{{ item.integral_price }}</div>
          </div>
          <div>
            <div class="pricePay">合计应付：{{ (Number(item.total_price)+Number(item.pay_postage)).toFixed(2) }}</div>
            <div class="pricePay">合计优惠：-{{ (parseFloat(item.platform_coupon_price)+parseFloat(item.coupon_price)+parseFloat(item.integral_price)+parseFloat(item.svip_discount)).toFixed(2) }}</div>
            <div class="pricePay">实付金额：{{ item.pay_price }}</div>
          </div> 
        </div>
        <div class="bottom acea-row">
          <div class="name">
            用户备注：<span class="con">{{ item.mark || '-' }}</span>
          </div>
        </div>
        <div class="bottom acea-row row-center-wrapper">
          <div>
            店铺名称：<span class="con">{{ item.merchant.mer_name || '-' }}</span>
          </div>
          <div class="ml20">
            地址：<span class="con">{{ item.merchant.mer_address || '-' }}</span>
          </div>
          <div class="ml20">
            联系方式：<span class="con">{{ item.merchant.mer_phone || '-' }}</span>
          </div>
        </div>
      <!-- <div class="perpage" v-for="(itm, idx) in item.orderProduct" :key="idx">
        
      </div> -->
    </div>
    <!--  注意：后续要是加内容使页面撑大，记得查看下打印是否在同一张,是否会多余一张空白纸  -->
  </div>
</template>
<script>
import { distributionOrder } from '@/api/order';
import QRCode from 'qrcodejs2';
import SettingMer from '@/libs/settingMer'
export default {
  data() {
    return {
      orderData: [],
      BaseURL: SettingMer.httpUrl || 'http://localhost:8080',
      newArrayData: [],
    };
  },
  created() {
    this.getDistribution();
  },
  mounted() {},
  methods: {
    // 生成二维码
    creatQrCode() {
      let qrcode;
      // let url = window.location.origin + '/pages/goods/order_details/index?order_id=' + this.$route.query.id;
      this.orderData.forEach((item, index) => {
        let obj = document.getElementById('qrCodeUrl' + index);
        qrcode = new QRCode(obj, { 
          text: this.BaseURL+'/pages/store/home/index?id=' +item.mer_id, // 需要转换为二维码的内容
          width: 90,
          height: 90,
          colorDark: '#000000',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H,
        });
      });
    },
    getDistribution() {
      let printIds = this.$route.query.id;
      distributionOrder({ids: printIds})
        .then(res => {
          this.orderData = res.data;
          setTimeout(() => {
            this.creatQrCode();
          }, 200);
        })
        .catch((err) => {
          this.$message.error(err.message);
        });
    },
  },
};
</script>
<style lang="scss" scoped>
.perpage {
  page-break-after: always !important;
  margin-bottom: 50px;
}
.order-bgc {
  width: 100%;
  background-color: #fff;
  height: max-content;
  min-height: 100vh;
}
::v-deep .el-table th {
  background-color: #fff !important;
}
::v-deep .el-table-header thead tr th:nth-of-type(1) {
  padding-left: 0 !important;
}
::v-deep .el-table-header thead tr th {
  border-top: 1px solid #333;
}
::v-deep .el-table td:nth-of-type(1) {
  padding-left: 0 !important;
}
::v-deep .el-table-header table {
  //border-top:0!important;
}
::v-deep .el-table-border th,
::v-deep .el-table-border td {
  border-right: 1px solid #333 !important;
}
::v-deep .el-table-border th:nth-of-type(1),
::v-deep .el-table-border td:nth-of-type(1) {
  border-left: 1px solid #333 !important;
}
::v-deep .el-table td.el-table__cell,
::v-deep .el-table th.el-table__cell.is-leaf {
  border-color: #333 !important;
}
::v-deep .el-table th,
::v-deep .el-table td {
  border-bottom: 1px solid #333 !important;
  border-right: 1px solid #333 !important;
}
::v-deep .el-table-wrapper-with-border {
  border-color: #333 !important;
  border: unset;
  
}
::v-deep .el-table-border:after {
  background-color: #333;
  width: 0 !important;
  height: 0 !important;
}
::v-deep .el-table--border {
  border: 1px solid #333 !important;
  border-bottom: none !important;
  // border-right: none !important;
}
::v-deep .el-table:before {
  background-color: #333;
  width: 0 !important;
  height: 0 !important;
}
::v-deep .el-table .cell,
::v-deep .el-table th.el-table__cell > .cell {
  // height: 47px !important;
  display: flex;
  align-items: center;
  justify-content: center;
}
::v-deep .el-table {
  color: #000;
}
.pricePay {
  font-weight: bold;
}
.bottom {
  color: rgba(0, 0, 0, 0.85);
  font-size: 12px;
  font-weight: 400;
  margin-top: 20px;
  &:last-child {
    padding-top: 20px;
    border-top: 1px solid #eee;
  }
  .item {
    margin-right: 30px;
  }
  .name {
    font-weight: 600;
  }
  .con {
    width: 740px;
    font-weight: unset;
  }
}
.putSupplier {
  width: 794px;
  background-color: #fff;
  margin: 0 auto;
  padding-top: 20px;
  &:first-child{
    padding-top: 10px;
  }
  .header {
    .info {
      font-size: 12px;
      color: rgba(0, 0, 0, 0.85);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 80px;
      .name {
        font-weight: 600;
      }
    }
    .left {
      width: 500px;
      .picture {
        width: 90px;
        height: 90px;
        margin-right: 20px;
        img {
          width: 100%;
          height: 100%;
        }
      }
      .info {
        flex: 1;
      }
    }
  }
}
.delivery {
  display: flex;
  justify-content: center;
  width: 794px;
  padding-top: 14px;
  border-top: 1px solid #dddddd;
  margin: 11px auto;
  font-size: 10px;
  color: #333333;

  div + div {
    margin-left: 30px;
  }
}
</style>

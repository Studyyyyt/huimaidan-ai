<template>
  <div>
    <div class="selCard">
      <el-form size="small" label-width="85px" :inline="true">
        <el-form-item label="时间选择：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item>
          <el-radio-group v-model="timeType" size="small" @change="dateChange">
            <el-radio-button :label="item.val" v-for="(item,i) in fromList" :key="i">{{ item.text }}</el-radio-button>
          </el-radio-group>
        </el-form-item>
      </el-form>
    </div>
    <div class="top-grid-box mt-14">
      <div class="mb-14" v-for="(item, index) in baseInfo" :key="index">
        <el-card shadow="never">
          <div class="card-count-title">{{ item.title }}</div>
          <count-to
          :start-val="0"
          :end-val="item.count"
          :duration="2000"
          class="card-count regular"
          />
          <div class="card-count-statistic">环比增长：
            <span :class="item.statistic > 0 ? 'text-red' : 'text-green'">{{ item.statistic }}%</span>
            <i :class="Number(item.statistic)>=0?'el-icon-caret-top':'el-icon-caret-bottom'" />
          </div>
        </el-card>
      </div>
    </div>
    <el-card shadow="never">
      <echarts-from :styles="chartStyle" :option-data="optionData" />
    </el-card>
    <el-row :gutter="14" class="mt-14">
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="pieStyle" :option-data="pieOption" />
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="pieStyle" :option-data="typeOption" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import timeOptions from "@/utils/timeOptions";
import echartsFrom from '@/components/echarts/index';
import CountTo from "vue-count-to";
import { orderTopApi, orderLineChartApi, orderTypePieApi } from "@/api/order";
export default {
  name: "orderStatistic",
  components: {
    echartsFrom,
    CountTo
  },
  data(){
    return {
      timeVal: [],
      timeType: "lately7",
      fromList: [
        { text: '最近7天', val: 'lately7' },
        { text: '最近30天', val: 'lately30' },
        { text: '本月', val: 'month' },
        { text: '本年', val: 'year' }
      ],
      pickerOptions: timeOptions,
      baseInfo:[
        { title: "订单实付金额", count: 12800.00, statistic: "120%"},
        { title: "用券金额", count: 12800.00, statistic: "120%"},
        { title: "退款金额", count: 12800.00, statistic: "120%"},
        { title: "支付订单数", count: 4700, statistic: "120%"},
        { title: "退款订单数", count: 4700, statistic: "120%"},
      ],
      // 图表样式
      chartStyle: { width: '100%', height: '380px' },
      // 与示例图片相似的混合柱线图配置
      optionData: {
        color: ['#3B82F6', '#34D399', '#F59E0B', '#A78BFA'],
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'cross' }
        },
        legend: {
          data: ['订单金额', '退款金额', '订单数量', '退款数量'],
          top: 10
        },
        grid: { left: 60, right: 60, top: 50, bottom: 40 },
        xAxis: {
          type: 'category',
          boundaryGap: true,
          axisTick: { alignWithLabel: true },
          data: []
        },
        yAxis: [
          {
            type: 'value',
            name: '金额',
            position: 'left',
            axisLine: { show: false },
            splitLine: { show: true },
            axisLabel: { formatter: '{value}' },
            nameTextStyle: {
              align: 'right',
              padding: [0, 7, 0, 0]
            }
          },
          {
            type: 'value',
            name: '数量',
            position: 'right',
            axisLine: { show: false },
            splitLine: { show: false },
            axisLabel: { formatter: '{value}' },
            nameTextStyle: {
              align: 'left',
              padding: [0, 0, 0, 7]
            }
          }
        ],
        series: [
          {
            name: '订单金额',
            type: 'bar',
            yAxisIndex: 0,
            barWidth: 18,
            itemStyle: { borderRadius: [4, 4, 0, 0] },
            data: []
          },
          {
            name: '退款金额',
            type: 'bar',
            yAxisIndex: 0,
            barWidth: 18,
            itemStyle: { borderRadius: [4, 4, 0, 0] },
            data: []
          },
          {
            name: '订单数量',
            type: 'line',
            yAxisIndex: 1,
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            data: []
          },
          {
            name: '退款数量',
            type: 'line',
            yAxisIndex: 1,
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            data: []
          }
        ]
      },
      // 添加饼图样式与配置
      pieStyle: { height: '360px' },
      pieOption: {
        title: { text: '订单类型分析', left: 20, top: 10, textStyle: { fontSize: 18, fontWeight: 600 } },
        color: ['#3B82F6', '#34D399', '#60A5FA', '#F59E0B', '#FB923C', '#EF4444', '#A78BFA', '#F472B6', '#C084FC'],
        tooltip: {
          trigger: 'item',
          formatter: '{b}: {c} ({d}%)'
        },
        legend: {
          orient: 'vertical',
          left: 20,
          top: 50,
          icon: 'circle',
          itemWidth: 8,
          itemHeight: 8
        },
        series: [
          {
            name: '订单类型分析',
            type: 'pie',
            // radius: '65%',
            radius: ['40%', '70%'],
            center: ['60%', '55%'],
            avoidLabelOverlap: false,
            label: { show: true, position: 'outside', formatter: '{b}' },
            labelLine: { show: true, length: 14, length2: 10, smooth: true },
            data: [
              { value: 33, name: '普通订单' },
              { value: 8,  name: '秒杀订单' },
              { value: 12, name: '砍价订单' },
              { value: 10, name: '拼团订单' },
              { value: 5,  name: '积分订单' },
              { value: 10, name: '预售订单' },
              { value: 5,  name: '套餐订单' },
              { value: 8,  name: '新人订单' },
              { value: 9,  name: '抽奖订单' }
          ] }
        ]
      },
      typeOption:{
        title: { text: '发货方式统计', left: 20, top: 10, textStyle: { fontSize: 18, fontWeight: 600 } },
        color: ['#3B82F6', '#34D399', '#60A5FA', '#EF4444', '#FB923C'],
        tooltip: {
          trigger: 'item',
          formatter: '{b}: {c} ({d}%)'
        },
        legend: {
          orient: 'vertical',
          left: 20,
          top: 50,
          icon: 'circle',
          itemWidth: 8,
          itemHeight: 8
        },
        series: [
          {
            name: '发货方式统计',
            type: 'pie',
            // radius: '65%',
            radius: ['40%', '70%'],
            center: ['60%', '55%'],
            avoidLabelOverlap: false,
            label: { show: true, position: 'outside', formatter: '{b}' },
            labelLine: { show: true, length: 14, length2: 10, smooth: true },
            data: [
              { value: 20, name: '快递订单' },
              { value: 20, name: '配送订单' },
              { value: 20, name: '核销订单' },
              { value: 20, name: '虚拟发货' },
              { value: 20, name: '自动发货' },
          ] }
        ]
      }

    }
  },
  mounted() {
    this.getCount(this.timeType);
    this.getStatisticalData(this.timeType);
    this.getOrderTypePie(this.timeType);
  },
  methods: {
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.timeType = '';
      let val = e ? this.timeVal.join("-") : "lately7";
      this.getCount(val);
      this.getStatisticalData(val);
      this.getOrderTypePie(val);
    },
    dateChange(val){
      this.timeVal = [];
      this.getCount(val);
      this.getStatisticalData(val);
      this.getOrderTypePie(val);
    },
    getCount(val){
      orderTopApi({date: val}).then(res=>{
        this.baseInfo  =res.data;
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    getStatisticalData(val){
      orderLineChartApi({date: val}).then(res=>{
        if(res.data.length){
          let xAxis = [];
          let payPrice = [];
          let order_num = [];
          let refund_num = [];
          let refund_price = [];
          res.data.map(item=>{
            xAxis.push(item.xaxis);
            payPrice.push(item.pay_price);
            order_num.push(item.order_num);
            refund_num.push(item.refund_num);
            refund_price.push(item.refund_price);
          });
          this.optionData.xAxis.data = xAxis;
          this.optionData.series[0].data = payPrice;
          this.optionData.series[2].data = order_num;
          this.optionData.series[3].data = refund_num;
          this.optionData.series[1].data = refund_price;
        }
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    getOrderTypePie(val){
      orderTypePieApi(1, {date: val}).then(res=>{
        this.typeOption.series[0].data = res.data;
      })
      orderTypePieApi(0, {date: val}).then(res=>{
        this.pieOption.series[0].data = res.data;
      })
    }
  }
};
</script>
<style lang="scss" scoped>
.top-grid-box{
  display: grid;
  column-gap: 14px;
}
/* 小屏：≥576px -> 2列 */
@media (min-width: 576px) {
  .top-grid-box {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* 中屏：≥768px -> 3列 */
@media (min-width: 768px) {
  .top-grid-box {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* 大屏：≥992px -> 5列 */
@media (min-width: 992px) {
  .top-grid-box {
    grid-template-columns: repeat(5, 1fr);
  }
}

/* 超大屏：≥1200px -> 维持 5 列 */
@media (min-width: 1200px) {
  .top-grid-box {
    grid-template-columns: repeat(5, 1fr);
  }
}
.card-count-title{
  font-size: 16px;
  font-weight: 500;
  color: rgb(51, 51, 51);
  line-height: 22px;
}
.card-count{
  display: inline-block;
  font-size: 34px;
  font-weight: 600;
  line-height: 34px;
  margin-top: 16px;
}
.card-count-statistic{
  font-weight: 400;
  font-size: 14px;
  color: #606266;
  line-height: 20px;
  margin-top: 16px;
}
.text-red, .el-icon-caret-top{
  color: #F64F54;
  font-weight: 400;
}
.text-green, .el-icon-caret-bottom{
  color: #19BE6B;
}

</style>

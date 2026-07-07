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
    <el-row :gutter="14" class="mt-14">
      <el-col :xs="24" :sm="12" :md="8" :lg="4" :xl="4" class="mb-14" v-for="(item, index) in baseInfo" :key="index">
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
            <i :class="Number(item.statistic) > 0?'el-icon-caret-top':'el-icon-caret-bottom'" />
          </div>
        </el-card>
      </el-col>
    </el-row>
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
import CountTo from "vue-count-to";
import echartsFrom from '@/components/echarts/index';
import { productTopApi, productLineChartApi, productPieChartApi } from "@/api/product.js";
export default {
  name: "productStatistic",
  components: {
    echartsFrom,
    CountTo
  },
  data() {
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
      tableFrom: {
        page: 1,
        limit: 20,
        date: "",
      },
      baseInfo:[],
      chartStyle: {
        height: '260px'
      },
      optionData: {
        color: ['#5B8FF9', '#5AD8A6', '#F6BD16', '#A78BFA'],
        tooltip: {
          trigger: 'axis',
          axisPointer: { type: 'line' }
        },
        legend: {
          top: 10,
          left: 'center',
          icon: 'circle',
          itemWidth: 8,
          itemHeight: 8,
          data: ['商品浏览量', '收藏量', '下单量', '支付量']
        },
        grid: { top: 50, left: 50, right: 30, bottom: 40 },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          axisLine: { lineStyle: { color: '#E5E8EF' } },
          axisLabel: { color: '#666' },
          data: []
        },
        yAxis: {
          type: 'value',
          name: '数量',
          min: 0,
          axisLine: { show: false },
          splitLine: { lineStyle: { color: '#F0F0F0' } },
          axisLabel: { color: '#666' },
          nameTextStyle: {
            align: 'right',
            padding: [0, 7, 0, 0]
          }
        },
        series: [
          { name: '商品浏览量', type: 'line', smooth: true, symbol: 'none', lineStyle: { width: 2 }, data: [] },
          { name: '收藏量', type: 'line', smooth: true, symbol: 'none', lineStyle: { width: 2 }, data: [] },
          { name: '下单量', type: 'line', smooth: true, symbol: 'none', lineStyle: { width: 2 }, data: [] },
          { name: '支付量', type: 'line', smooth: true, symbol: 'none', lineStyle: { width: 2 }, data: [] }
        ]
      },
      // 添加饼图样式与配置
      pieStyle: { height: '360px' },
      pieOption: {
        title: { text: '商品分类', left: 20, top: 10, textStyle: { fontSize: 18, fontWeight: 600 } },
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
            name: '商品分类',
            type: 'pie',
            // radius: '65%',
            radius: ['40%', '70%'],
            center: ['60%', '55%'],
            avoidLabelOverlap: false,
            label: { show: true, position: 'outside', formatter: '{b}' },
            labelLine: { show: true, length: 14, length2: 10, smooth: true },
            data: []
          }
        ]
      },
      typeOption:{
        title: { text: '商品类型', left: 20, top: 10, textStyle: { fontSize: 18, fontWeight: 600 } },
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
            name: '商品类型',
            type: 'pie',
            // radius: '65%',
            radius: ['40%', '70%'],
            center: ['60%', '55%'],
            avoidLabelOverlap: false,
            label: { show: true, position: 'outside', formatter: '{b}' },
            labelLine: { show: true, length: 14, length2: 10, smooth: true },
            data: []
          }
        ]
      }
    };
  },
  mounted() {
    this.getCount(this.timeType);
    this.getStatisticalData(this.timeType);
    this.getProductTypePie(this.timeType);
  },
  methods: {
    onchangeTime(e) {
      this.timeVal = e;
      this.timeType = '';
      let val = e ? this.timeVal.join("-") : "lately7";
      this.getCount(val);
      this.getStatisticalData(val);
      this.getProductTypePie(val);
    },
    dateChange(val){
      this.timeVal = [];
      this.getCount(val);
      this.getStatisticalData(val);
      this.getProductTypePie(val);
    },
    getCount(val){
      productTopApi({date: val}).then(res=>{
        this.baseInfo = res.data;
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    getStatisticalData(val) {
      productLineChartApi({date: val}).then(res=>{
        if(res.data.length){
          let xAxis = [] ,paidNum = [], visitNum = [], relation = [], totalNum = []; //下单
          res.data.map(item=>{
            xAxis.push(item.xaxis);
            paidNum.push(item.paid_num);
            visitNum.push(item.visit);
            relation.push(item.relation);
            totalNum.push(item.total_num);
          }); //浏览 收藏 下单 支付
          this.optionData.xAxis.data = xAxis;
          this.optionData.series[0].data = visitNum;
          this.optionData.series[1].data = relation;
          this.optionData.series[2].data = totalNum;
          this.optionData.series[3].data = paidNum;
        }
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    async getProductTypePie(val) {
      const [productCatePieData, productTypePieData] = await Promise.all([
        productPieChartApi(0,{date: val}),
        productPieChartApi(1,{date: val})
      ]);
      this.typeOption.series[0].data = productCatePieData.data;
      this.pieOption.series[0].data = productTypePieData.data;
    },
  }
};
</script>
<style>
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

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
          :duration="3000"
          class="card-count regular"
          />
          <div class="card-count-statistic">环比增长：
            <span :class="item.statistic > 0 ? 'text-red' : 'text-green'">{{ item.statistic }}%</span>
            <i :class="Number(item.statistic) > 0?'el-icon-caret-top':'el-icon-caret-bottom'" />
          </div>
        </el-card>
      </el-col>
    </el-row>
    <el-row :gutter="14">
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="chartStyle" :option-data="newUserData" />
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="chartStyle" :option-data="activeUserData" />
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="chartStyle" :option-data="userData" />
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="12" :xl="12" class="mb-14">
        <el-card shadow="never">
          <echarts-from :styles="chartStyle" :option-data="newVipUserData" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import timeOptions from "@/utils/timeOptions";
import echartsFrom from '@/components/echarts/index';
import CountTo from "vue-count-to";
import { userLineChartApi, userTopApi, userPieChartApi } from "@/api/user";
export default {
  name: "orderStatistic",
  components: { echartsFrom, CountTo },
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
      // 图表样式
      chartStyle: { width: '100%', height: '380px' },
      // 新增用户数量折线图（与示例图片相似）
      newUserData: {
        title: { text: '新增用户数量', left: 20, top: 0, textStyle: { fontSize: 18, fontWeight: 600 } },
        color: ['#3B82F6'],
        tooltip: { trigger: 'axis', axisPointer: { type: 'line' } },
        grid: { left: 60, right: 40, top: 70, bottom: 40 },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          axisTick: { alignWithLabel: true },
          data: []
        },
        yAxis: {
          type: 'value',
          name: '数量',
          axisLine: { show: false },
          splitLine: { show: true }
        },
        series: [
          {
            name: '新增用户数量',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 3 },
            areaStyle: { opacity: 0 },
            data: []
          }
        ]
      },
      // 活跃用户数量折线图（与示例图片相似）
      activeUserData: {
        title: { text: '活跃用户数量', left: 20, top: 0, textStyle: { fontSize: 18, fontWeight: 600 } },
        color: ['#3B82F6'],
        tooltip: { trigger: 'axis', axisPointer: { type: 'line' } },
        grid: { left: 60, right: 40, top: 70, bottom: 40 },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          axisTick: { alignWithLabel: true },
          data: ['12.11','12.12','12.13','12.14','12.15','12.16','12.17']
        },
        yAxis: {
          type: 'value',
          name: '数量',
          axisLine: { show: false },
          splitLine: { show: true }
        },
        series: [
          {
            name: '活跃用户数量',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 3 },
            areaStyle: { opacity: 0 },
            data: [3900, 4200, 5200, 8300, 6800, 4300, 7500]
          }
        ]
      },
      // 新增会员用户数量折线图（与示例图片相似）
      newVipUserData: {
        title: { text: '新增付费会员数量', left: 20, top: 0, textStyle: { fontSize: 18, fontWeight: 600 } },
        color: ['#3B82F6'],
        tooltip: { trigger: 'axis', axisPointer: { type: 'line' } },
        grid: { left: 60, right: 40, top: 70, bottom: 40 },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          axisTick: { alignWithLabel: true },
          data: ['12.11','12.12','12.13','12.14','12.15','12.16','12.17']
        },
        yAxis: {
          type: 'value',
          name: '数量',
          axisLine: { show: false },
          splitLine: { show: true }
        },
        series: [
          {
            name: '新增付费会员数量',
            type: 'line',
            smooth: true,
            symbol: 'circle',
            symbolSize: 6,
            lineStyle: { width: 3 },
            areaStyle: { opacity: 0 },
            data: [3900, 4200, 5200, 8300, 6800, 4300, 7500]
          }
        ]
      },
      // 成交用户数量（堆叠柱状图）
      userData: {
        title: { text: '成交用户数量', left: 20, top: 0, textStyle: { fontSize: 18, fontWeight: 600 } },
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { data: ['老用户','新用户'], top: 20 },
        grid: { left: 60, right: 40, top: 70, bottom: 40 },
        xAxis: { type: 'category', data: ['12.11','12.12','12.13','12.14','12.15','12.16','12.17'] },
        yAxis: { type: 'value', name: '数量', axisLine: { show: false }, splitLine: { show: true } },
        series: [
          {
            name: '老用户',
            type: 'bar',
            stack: 'users',
            barMaxWidth: 24,
            itemStyle: { color: '#3B82F6', borderRadius: [4, 4, 0, 0] },
            data: [3200, 2100, 2800, 1500, 6000, 3000, 4200]
          },
          {
            name: '新用户',
            type: 'bar',
            stack: 'users',
            barMaxWidth: 24,
            itemStyle: { color: '#34D399', borderRadius: [4, 4, 0, 0] },
            data: [2600, 2400, 800, 2200, 2800, 900, 1400]
          }
        ]
      }
    }
  },
  mounted() {
    this.getCount(this.timeType);
    this.getUserLineData(this.timeType);
    this.getDealUserData(this.timeType);
  },
  methods: {
    onchangeTime(e) {
      this.timeVal = e;
      this.timeType = '';
      let val = e ? this.timeVal.join("-") : "lately7";
      this.getCount(val);
      this.getUserLineData(val);
      this.getDealUserData(val);
    },
    dateChange(val){
      this.timeVal = [];
      this.getCount(val);
      this.getUserLineData(val);
      this.getDealUserData(val);
    },
    getCount(val){
      userTopApi({date: val}).then(res=>{
        this.baseInfo = res.data;
      }).catch(err=>{
        this.$Message.error(err.msg);
      })
    },
    async getUserLineData(val) {
      const [newUserLineData, activeUserData, newVipUserData] = await Promise.all([
        userLineChartApi({date: val, type: 0}),
        userLineChartApi({date: val, type: 1}),
        userLineChartApi({date: val, type: 2}),
      ]);
      let newxAxis = [], newData = [],activexAxis = [],activeData = [], vipxAxis = [],vipData = [];
      newUserLineData.data.map(item=>{
        newxAxis.push(item.xaxis);
        newData.push(item.count);
      })
      activeUserData.data.map(item=>{
        activexAxis.push(item.xaxis);
        activeData.push(item.count);
      })
      newVipUserData.data.map(item=>{
        vipxAxis.push(item.xaxis);
        vipData.push(item.count);
      })
      this.newUserData.xAxis.data = newxAxis;
      this.newUserData.series[0].data = newData;
      this.activeUserData.xAxis.data = activexAxis;
      this.activeUserData.series[0].data = activeData;
      this.newVipUserData.xAxis.data = vipxAxis;
      this.newVipUserData.series[0].data = vipData;
    },
    getDealUserData(val){
      userPieChartApi({date: val}).then(res=>{
        let xAxis = [], oldVal = [], newVal = [];
        res.data.map(item=>{
          xAxis.push(item.xaxis);
          oldVal.push(item.old);
          newVal.push(item.new);
        })
        this.userData.xAxis.data = xAxis;
        this.userData.series[0].data = oldVal;
        this.userData.series[1].data = newVal;
      }).catch(err=>{
        this.$Message.error(err.message);
      })
    },
  }
}
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

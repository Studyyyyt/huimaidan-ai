<template>
  <div ref="echartsWrapper">
    <div :id="echarts" :style="styles" />
  </div>
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
// import echarts from 'echarts'
import { debounce } from '@/utils'
import * as echarts from 'echarts';
export default {
  name: 'Index',
  props: {
    styles: {
      type: Object,
      default: null
    },
    optionData: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      myChart: null
    }
  },
  computed: {
    echarts() {
      return 'echarts' + window.crypto.getRandomValues(new Uint32Array(2))[0]
    }
  },
  watch: {
    optionData: {
      handler(newVal, oldVal) {
        this.handleSetVisitChart()
      },
      deep: true // 对象内部属性的监听，关键。
    }
  },
  mounted: function() {
    const vm = this
    vm.$nextTick(() => {
      vm.handleSetVisitChart()
      window.addEventListener('resize', this.wsFunc)
    })

    this.handleContainerSize = debounce(this.handleContainerSize, 200);
    const observer = new ResizeObserver(entries => {
      this.handleContainerSize();
    });
    observer.observe(this.$refs.echartsWrapper);
    this.observer = observer;
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.wsFunc)
    if (!this.myChart) {
      return
    }
    this.myChart.dispose()
    this.myChart = null;
    this.observer.disconnect();
    this.observer = null;
  },
  methods: {
    handleContainerSize() {
      this.myChart && this.myChart.resize();
    },
    wsFunc() {
      // this.myChart.resize()
    },
    handleSetVisitChart() {
      var chartDom = document.getElementById(this.echarts);
      var myChart = echarts.init(chartDom);
      this.myChart = myChart;
      var option;
      // this.myChart = echarts.init(document.getElementById(this.echarts))
      // let option = null
      option = this.optionData
      // 基于准备好的dom，初始化echarts实例
      // this.myChart.setOption(option, true)
      option && myChart.setOption(option);
    }
  }
}
</script>

<style scoped>

</style>

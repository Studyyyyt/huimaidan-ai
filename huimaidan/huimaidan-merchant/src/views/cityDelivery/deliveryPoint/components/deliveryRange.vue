<template>
  <div>
      <el-form
        ref="costForm"
        :model="costForm"
        :label-width="labelWidth + 'px'"
        @submit.native.prevent
      >
        <el-form-item :label="label + ':'">
          <el-radio-group
            v-model="costForm.range_type"
          >
            <el-radio :label="1">按服务半径</el-radio>
            <el-radio :label="2">按行政区域</el-radio>
            <el-radio :label="3">电子围栏</el-radio>
          </el-radio-group>
          <br>
          <span class="remarks">{{ remarks }}</span>
        </el-form-item>
        <!-- 服务半径地图 -->
        <el-form-item
          v-if="costForm.range_type==1"
        >
          <div
            class="map-box"
          >
            <el-input
              v-model="costForm.radius"
              class="selWidth distance-input">
              <template slot="append">公里</template>
            </el-input>
            <div class="map-contents-box" v-if="merData.tx_map_key">
              <Maps
                :map-key="merData.tx_map_key"
                :location="location"
                :address="merData.mer_address"
                :radius="radiusNum"
              />
            </div>
          </div>
        </el-form-item>
        <!-- 行政区域筛选 -->
        <el-form-item
          v-if="costForm.range_type==2">
            <LazyCascader
              v-model="costForm.region"
              size="small"
              :props="addressProps"
              placeholder="请选择行政区域(多选)"
              collapse-tags
              clearable
              :filterable="false"
            />
            <br>
            <span class="remarks">如果选用第三方配送，请不要选择默认配送地址以外的城市区域。</span>
        </el-form-item>
        <!-- 电子围栏地图 -->
        <el-form-item
          v-if="costForm.range_type==3">
          <div
            class="map-box">
            <!-- 区域形状选择 -->
            <MapFence :default-fence="costForm.fence" @update="handleFenceUpdate" :map-key="merData.tx_map_key" v-if="merData.tx_map_key" :address="merData.mer_address" :location="location" />
          </div>
        </el-form-item>
      </el-form>
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

import {
  cityListV2
} from "@/api/freight";

import { getBaseInfo } from "@/api/user.js";
import Maps from "@/components/map/map.vue";
import MapFence from "./mapFence.vue";
import LazyCascader from "@/components/lazyCascader/index.vue";

const cacheAddress = {}

export default {
  name: "DeliveryRange",
  components: {
    Maps,
    MapFence,
    LazyCascader
  },
  props: {
    lat: Number,
    lng: Number,
    labelWidth: {
      type: Number,
      default: 140,
    },
    label: {
      type: String,
      default: "配送范围"
    },
    remarks: {
      type: String,
      default: "收货地址在配送范围之外的买家将不可下单。"
    },
    costForm: {
      type: Object,
      default: null
    }
  },
  computed: {
    location() {
      if (this.lat && this.lng) {
        return {
          lat: this.lat,
          lng: this.lng
        };
      } else if (this.merData.lat) {
        return {
          lat: this.merData.lat,
          lng: this.merData.long
        };
      }
      return {
        lat: 34.34127,
        lng: 108.93984
      }
    },
    radiusNum() {
      if (this.costForm.radius !== '') {
        return Number(this.costForm.radius)
      } else {
        return 0
      }
    }
  },
  created() {
    this.getMerInfo()
  },
  data() {
    return {
      merData: {}, // 店铺数据
      addressProps: { // 行政区域级联选择框组件配置选项
        children: 'children',
        label: 'name',
        value: 'id',
        multiple: true,
        lazy: true,
        lazyLoad: this.lazyLoad,
        checkStrictly: true
      },
    }
  },
  methods: {
    // 获取商户数据
    async getMerInfo() {
      try {
        const res = await getBaseInfo()
        this.merData = res.data
        if (!this.merData.tx_map_key) {
          this.$message.error("请联系平台配置腾讯地图KEY")
        }
      } catch (error) {
        this.$message.error(error.message)
      }
    },
    // 行政区域级联选择框组件加载动态数据
    lazyLoad(node, resolve) {
      if (cacheAddress[node]) {
        cacheAddress[node]().then(res => {
          resolve([...res.data])
        })
      } else {
        const p = cityListV2(node)
        cacheAddress[node] = () => p
        p.then(res => {
          res.data.forEach(item => {
            item.leaf = item.level === 3
          })
          cacheAddress[node] = () => new Promise((resolve1) => {
            setTimeout(() => resolve1(res), 300)
          })
          resolve(res.data)
        }).catch(res => {
          this.$message.error(res.message)
        })
      }
    },
    handleFenceUpdate(fence) {
      this.costForm.fence = fence;
    }
  }
}
</script>

<style scoped lang="scss">
  @import '@/styles/form.scss';
  .remarks {
    color: #A4A4A4;
    font-size: 12px;
  }
  .map-box {
    width: 1000px;
    background-color: #F5F7FA;
    padding: 20px;
    .distance-input {
      margin-bottom: 20px;;
    }
    .distance-input-fence {
      width: 150px;
      margin-left: 10px;

    }
    .distance-input-fence-disabled {
      ::v-deep .el-input__inner {
        border-color: #dfe4ed;
        // border-color: var(--prev-border-color-hover);
      }
    }
    .map-contents-box {
      background-color: #FFFFFF;
      border-radius: 5px;
      padding: 20px;
      position: relative;
      z-index: 1;
    }
  }
  .mb-22 {
    margin-bottom: 22px;
  }
</style>

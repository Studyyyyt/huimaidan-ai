<template>
  <div>
    <div :id="mapId" style="width:100%;height:450px;"></div>
  </div>
</template>

<script>
import { loadMapLib } from "@/components/map/index";

export default {
  name: "Map",
  props: {
    location: Object,
    mapKey: {
      type: String
    },
    address: {
      type: String
    },
    // 地图覆盖物半径
    radius: {
      type: Number,
      default: 0
    }
  },
  computed: {
    // 计算圆形覆盖物半径
    reRadius() {
      return this.radius * 1000;
    },
    // 计算圆形覆盖物半径变化后的地图缩放等级
    reZoom() {
      if (parseInt(Math.log2(this.reRadius / 5))) {
        return (22 - parseInt(Math.log2(this.reRadius / 5)))
      } else {
        return 15
      }
    }
  },
  data() {
    const prefix = "id_" + this.getRandomId();
    return {
      prefix,
      mapId: prefix + "_map",
    };
  },
  created() {
    const libLoadTask = this.loadMapLib();
    const loadCenterTask = this.getMapDefaultCenter();
    this.loadTask = [
      loadCenterTask,
      libLoadTask
    ];
  },
  mounted() {
    this.initMap();
  },
  watch: {
    // 监听覆盖物半径变化
    radius() {
      this.updateMapCircle();
    },
    location() {
      this.updateMapCenter();
    },
    address() {
      this.updateMapCenter(this.address);
    }
  },
  methods: {
    getRandomId() {
      // 生成随机 ID
      return Math.random().toString(36).substring(2, 10);
    },
    async initMap() {
      const [mapDefaultCenter] = await Promise.all(this.loadTask);
      const TMap = window.TMap;
      const options = {
        zoom: this.reZoom,
        showControl: false
      };

      const markerList = [];
      const circleList = [];

      if (mapDefaultCenter) {
        const { lng, lat } = mapDefaultCenter;
        options.center = new TMap.LatLng(lat, lng);

        markerList.push({
          position: options.center
        });

        this.reRadius && circleList.push({
          radius: this.reRadius,
          center: options.center
        });
      }

      const map = new TMap.Map(document.getElementById(this.mapId), options);

      map.on("click", e => {
        this.getLocationAddress(e.latLng)
          .then(address => {
            this.$emit('updateLocation', { location: e.latLng, address })
          });
      });

      this.marker = new TMap.MultiMarker({
        map,
        geometries: markerList
      });

      this.circle = new TMap.MultiCircle({
        map,
        geometries: circleList
      });

      this.map = map;
    },
    async getMapDefaultCenter() {
      if (this.location) return this.location;
      const res = await this.getLocationByAddress();
      if (res.status === 0) return res.result.location;
      return null;
    },
    loadMapLib() {
      // 加载地图库
      return loadMapLib(this.mapKey);
    },
    getLocationByAddress() {
      // 获取地址的经纬度
      return this.$jsonp("https://apis.map.qq.com/ws/geocoder/v1?", {
        address: `${this.address}`,
        key: this.mapKey,
        output: "jsonp"
      });
    },
    getLocationAddress(location) {
      return this.$jsonp("https://apis.map.qq.com/ws/geocoder/v1?", {
        location: `${location.lat},${location.lng}`,
        key: this.mapKey,
        output: "jsonp"
      }).then((res) => {
        return res.result.formatted_addresses.standard_address;
      });
    },
    updateMapCircle() {
      const [geometire] = this.circle.getGeometries();
      this.reRadius && this.circle.setGeometries([
        {
          radius: this.reRadius,
          center: geometire.center
        }
      ]);
    },
    async updateMapCenter(address) {
      // 更新地图中心点
      let location = this.location;
      if (address) {
        const resp = await this.getLocationByAddress();
        if (resp.status === 0) {
          location = resp.result.location;
          this.$emit('updateLocation', { location })
        } else {
          const errorMsg = resp.status === 348 ? "请补充详细地址" : resp.message;
          this.$message.error(errorMsg);
          return;
        }
      }

      if (!location) return;

      const { lat, lng } = location;

      const center = new TMap.LatLng(lat, lng);

      this.marker.setGeometries([
        {
          position: center
        }
      ]);

      this.reRadius && this.circle.setGeometries([
        {
          radius: this.reRadius,
          center
        }
      ]);

      if (address) {
        this.map.setCenter(center);
      }

    }
  }
};
</script>

<style scoped></style>

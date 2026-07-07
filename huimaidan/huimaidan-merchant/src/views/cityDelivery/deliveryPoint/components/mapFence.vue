<template>
  <div class="map-contents-box" :class="{ 'full-screen': isFullscreen }">
    <el-row>
      <!-- 地图本体部分 -->
      <el-col :span="isFullscreen ? 20 : 16">
        <div class="map-next-box" @mouseleave="handleMouseLeave">

          <!-- 地图本体 -->
          <div :id="mapId" class="map-next" />

          <!-- 地图工具栏 -->
          <div class="map-tool-box">
            <div class="address-search-box">
              <el-autocomplete size="small" v-model="addressSearchContent" :fetch-suggestions="handleAddressSearch"
                placeholder="请输入要搜索的地址" @select="handleAddressSearchSelect" popper-class="address-search-popper"
                class="address-search-input" @search="handleAddressInputConfirm" @keydown="handleKeyDown">
                <el-button slot="append" icon="el-icon-search" @click="handleAddressInputConfirm"></el-button>
              </el-autocomplete>
            </div>

            <div class="overlay-tool-box">
              <el-radio-group v-model="editorMode" size="small" fill="#4073fa" text-color="#fff">
                <el-radio-button label="add">添加</el-radio-button>
                <el-radio-button label="edit">编辑</el-radio-button>
              </el-radio-group>
              <div v-if="editorMode === 'add'" class="tool-box-add" @click="handleMapToolClick">
                <div v-for="tool of toolList" :key="tool.id" :id="tool.id" class="toolItem" :title="tool.title"
                  :data-tool-type="tool.type" :class="[tool.type, activeOverlayId === tool.id ? 'active' : '']" />
              </div>
            </div>
          </div>

          <!-- 全屏按钮 -->
          <div class="fullscreen-btn" @click.prevent="handleToggleFullscreen">
            <i class="iconfont-h5" :class="isFullscreen ? 'icon-ic_reduce' : 'icon-ic_enlarge2'"></i>
          </div>

          <!-- geomertry 鼠标 hover 提示 -->
          <div class="geometry-tooltip" :style="geometryTooltipStyle" v-if="geometryTooltipData.visible">{{
            geometryTooltipData.label }}</div>


          <p class="map-info">
            添加区域：点击右侧添加配送区域按钮或者鼠标左键点击及移动即可绘制图形。<br />
            结束区域绘制：多边形结束绘制需要双击鼠标左键，圆形、矩形、椭圆单击即可结束绘制。<br />
            编辑区域：点击编辑按钮，然后点击需要编辑的区域即可修改区域边界，按 Delete 键删除区域。<br />
            其他：椭圆图形绘制时先绘制宽度再绘制高度，点击地图右上角按钮可以切换到全屏。
          </p>
        </div>
      </el-col>
      <!-- 区域颜色部分 -->
      <el-col :span="isFullscreen ? 4 : 8">
        <div class="right-box">
          <div class="color-box">
            <el-scrollbar>
              <div class="color-item-box" v-for="(item, index) in fence" :key="index">
                <el-color-picker class="color-item" v-model="fence[index].color" size="mini"
                  @change="handleColorChange(item)" popper-class="color-popper-class" :popper-append-to-body="false">
                </el-color-picker>

                <el-input class="color-input" maxlength="10" show-word-limit v-model="fence[index].title" @focus="handleFenceTitleFocus(item)">
                </el-input>

                <el-button v-if="fence.length > 1" type="text" @click="handleDelFence(index)">
                  删除
                </el-button>
              </div>
            </el-scrollbar>
          </div>
          <el-button type="primary" plain class="add-btn" @click="addRegion">
            添加配送区域
          </el-button>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { loadMapLib } from "@/components/map/index";


const colorList = [
  "#4073fa",
  "#0fc6c2",
  "#f56464",
  "#ff7d00",
  "#3491fa",
  "#9fdb1d",
  "#f7ba1e",
  "#b27feb"
];


export default {
  name: "MapFence",
  props: {
    address: {
      type: String,
      default: "咸阳"
    },
    location: Object,
    mapKey: {
      type: String,
      default: ""
    },
    defaultFence: {
      type: Array,
      default: () => []
    }
  },
  data() {
    const prefix = "id_" + this.getRandomId();

    const polygonToolId = prefix + "_polygon";
    const circleToolId = prefix + "_circle";
    const rectangleToolId = prefix + "_rectangle";
    const ellipseToolId = prefix + "_ellipse";
    return {
      prefix,
      mapId: prefix + "_map",

      map: null,
      editor: null,
      polygonToolId,
      circleToolId,
      rectangleToolId,
      ellipseToolId,

      toolList: [
        {
          id: polygonToolId,
          type: "polygon",
          title: "多边形"
        },
        {
          id: circleToolId,
          type: "circle",
          title: "圆形"
        },
        {
          id: rectangleToolId,
          type: "rectangle",
          title: "矩形"
        },
        {
          id: ellipseToolId,
          type: "ellipse",
          title: "椭圆"
        }
      ],

      fence: this.defaultFence ? JSON.parse(JSON.stringify(this.defaultFence)) : [],

      activeOverlayId: prefix + "_polygon",
      editorMode: "add", // add: 添加, edit: 编辑

      defaultColor: "#3777FF",

      isFullscreen: false,

      geometryTooltipData: {
        visible: false,
        label: "",
        x: 0,
        y: 0
      },

      addressSearchContent: "",
    };
  },
  computed: {
    geometryTooltipStyle() {
      return {
        top: this.geometryTooltipData.y + "px",
        left: this.geometryTooltipData.x + "px"
      }
    },
    currentToolType() {
      if (this.activeOverlayId === this.polygonToolId) {
        return "polygon";
      } else if (this.activeOverlayId === this.circleToolId) {
        return "circle";
      } else if (this.activeOverlayId === this.rectangleToolId) {
        return "rectangle";
      } else if (this.activeOverlayId === this.ellipseToolId) {
        return "ellipse";
      }
    }
  },
  watch: {
    location(v) {
      if (v && this.map) {
        this.setMapCenter(v.lat, v.lng);
      }
    },
    fence: {
      handler(v) {
        this.$emit("update", v);
      },
      deep: true
    },
    editorMode(mode) {
      if (!this.editor) return;
      const { DRAW, INTERACT } = TMap.tools.constants.EDITOR_ACTION;
      this.editor.setActionMode(mode === "add" ? DRAW : INTERACT);
      this.editor.setActiveOverlay(this.activeOverlayId);
    }
  },
  created() {
    const loadCenterTask = this.getMapDefaultCenter();
    this.libLoadTask = [loadCenterTask, this.loadMapLib()];
  },
  mounted() {
    this.initMap();
    window.addEventListener("keyup", this.handleKeyup);
  },
  beforeDestroy() {
    window.removeEventListener("keyup", this.handleKeyup);

    // 移除地图的鼠标移动事件
    if (this.map) {
      this.map.off("mousemove", this.updateTooltipPosition);
    }

    // 移除 overlay 的 hover 事件
    if (this.editor) {
      this.editor.getOverlayList().forEach(overlayItem => {
        overlayItem.overlay.off("hover", this.updateTooltipContent);
      });
    }
  },
  methods: {
    async getMapDefaultCenter() {
      if (this.location) return this.location;
      const res = await this.handleQueryAddressPosition(this.address);
      if (res.status === 0) return res.result.location;
      return null;
    },
    handleKeyDown(e) {
      e.preventDefault();
    },
    handleFenceTitleFocus(fenceData) {
      if (!this.map) return;

      const location = fenceData.type === "polygon" ? fenceData.data.paths[0] : fenceData.data.center;

      
      this.map.panTo(new TMap.LatLng(location.lat, location.lng));
    },
    async handleAddressInputConfirm() {
      const query = this.addressSearchContent.trim();
      if (!query) return;
      try {
        const resp = await this.handleQueryAddressPosition(query);
        if (resp.status !== 0) throw new Error(resp.message);
        const { lng, lat } = resp.result.location;
        this.setMapCenter(lat, lng);
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    async handleAddressSearch(query, cb) {
      query = query.trim();
      if (!query || !this.mapKey) return cb([]);

      const options = {
        key: this.mapKey,
        keyword: query,
        output: "jsonp"
      };

      if (this.map) {
        const center = this.map.getCenter();
        options.location = `${center.lat},${center.lng}`;
      }

      try {
        const res = await this.$jsonp("https://apis.map.qq.com/ws/place/v1/suggestion", options);
        if (res.status !== 0) return cb([]);
        cb(res.data.map(item => {
          return {
            value: item.title + " " + item.address,
            rawData: item
          };
        }));
      } catch (error) {
        cb([]);
      }
    },
    setMapCenter(lat, lng) {
      const centerPoint = new TMap.LatLng(lat, lng);

      // 更新地图中心点
      this.map.setCenter(centerPoint);

      // 更新标注
      this.marker.setGeometries([
        {
          position: centerPoint
        }
      ]);
    },
    handleAddressSearchSelect(item) {
      // 选择地址时，更新地址搜索内容
      this.addressSearchContent = item.value;
      const { lng, lat } = item.rawData.location;

      this.setMapCenter(lat, lng);
    },
    handleMouseLeave() {
      // 鼠标离开地图时，隐藏 tooltip
      this.geometryTooltipData.visible = false;
    },
    handleKeyup(e) {
      // 全屏时按 Esc 键退出全屏
      if (e.key === "Escape") {
        this.isFullscreen = false;
      }
    },
    updateTooltipContent(event) {
      // 更新 tooltip 的内容
      let label = "";
      if (event.geometry) {
        const fenceData = this.fence.find(item => item.id === event.geometry.id);
        if (fenceData) {
          label = fenceData.title;
        }
      }
      this.geometryTooltipData.visible = !!event.geometry;
      this.geometryTooltipData.label = label;
    },
    updateTooltipPosition(event) {
      // 更新 tooltip 的坐标
      this.geometryTooltipData.x = event.point.x;
      this.geometryTooltipData.y = event.point.y;
    },
    generateColorByGeometryLength() {
      // 根据电子围栏数量生成颜色
      const length = this.fence.length;
      return colorList[length % colorList.length];
    },
    handleToggleFullscreen() {
      // 切换全屏
      this.isFullscreen = !this.isFullscreen;
    },
    handleColorChange(fenceData) {
      // 根据选择的颜色生成新样式
      if (!this.editor) return;
      const overlayItem = this.editor.getOverlayList().find(item => item.id.endsWith(fenceData.type));
      if (!overlayItem) return;
      const overlay = overlayItem.overlay;
      const prevStyles = overlay.getStyles();

      if (!prevStyles[fenceData.color]) {
        // 根据选择的新颜色不存在，则生成新的颜色配置
        // 并更新到 overlay 中
        const newStyle = this.generateStyle(fenceData.type, fenceData.color);
        if (!newStyle) return;

        prevStyles[fenceData.color] = newStyle;
        overlay.setStyles(prevStyles);
      }

      // 将 fenceData 转换为地图需要的 geometry 数据
      // 并更新 overlay 中对应的 geometry 
      const nextGeometry = this.convertToGeometry(fenceData);
      overlay.updateGeometries([nextGeometry]);
    },
    parseGeometry(geometry, type) {
      // 将地图几何体数据转换为标准电子围栏数据
      if (type === "polygon") {
        return {
          id: geometry.id,
          type: "polygon",
          data: {
            paths: geometry.paths
          }
        };
      } else if (type === "circle") {
        return {
          id: geometry.id,
          type: "circle",
          data: {
            radius: geometry.radius,
            center: geometry.center
          }
        };
      } else if (type === "rectangle") {
        return {
          id: geometry.id,
          type: "rectangle",
          data: {
            width: geometry.width,
            height: geometry.height,
            center: geometry.center
          }
        };
      } else if (type === "ellipse") {
        return {
          id: geometry.id,
          type: "ellipse",
          data: {
            center: geometry.center,
            majorRadius: geometry.majorRadius,
            minorRadius: geometry.minorRadius
          }
        };
      }
    },
    getRandomId() {
      // 生成随机 ID
      return Math.random().toString(36).substring(2, 10);
    },
    loadMapLib() {
      // 加载地图库
      return loadMapLib(this.mapKey);
    },
    handleQueryAddressPosition(address) {
      // 获取地址的经纬度
      return this.$jsonp("https://apis.map.qq.com/ws/geocoder/v1", {
        address: `${address}`,
        key: this.mapKey,
        output: "jsonp"
      });
    },
    handleMapToolClick(e) {
      // 点击地图工具栏事件
      // 更新 editor 选中的 overlay 类型
      const toolType = e.target.dataset.toolType;
      if (!toolType) return;
      this.activeOverlayId = e.target.id;
      this.editor.setActiveOverlay(this.activeOverlayId);
    },
    hexToRgba(hex, alpha = 0.16) {
      // 将十六进制颜色转换为 rgba 颜色，支持设置透明度

      // 去掉开头的 #
      hex = hex.replace(/^#/, '');

      // 处理三位简写情况 #abc -> #aabbcc
      if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
      }

      const r = parseInt(hex.slice(0, 2), 16);
      const g = parseInt(hex.slice(2, 4), 16);
      const b = parseInt(hex.slice(4, 6), 16);

      return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    },
    generateStyle(overlayType, color) {
      // 根据 overlay 类型和颜色生成对应的颜色实例
      // 传入的颜色设置为边框颜色，填充颜色使用传入颜色的 16% 透明度

      let factory;
      if (overlayType === "polygon") {
        factory = TMap.PolygonStyle;
      } else if (overlayType === "circle") {
        factory = TMap.CircleStyle;
      } else if (overlayType === "rectangle") {
        factory = TMap.RectangleStyle;
      } else if (overlayType === "ellipse") {
        factory = TMap.EllipseStyle;
      }
      if (!factory) return;
      return new factory({
        color: this.hexToRgba(color),
        borderColor: color
      });
    },
    convertToGeometry(fenceData) {
      // 将标准电子围栏数据转换为地图需要的 geometry 数据
      const { type } = fenceData;
      const geometry = {
        id: fenceData.id,
        styleId: fenceData.color,
      };

      if (type === "polygon") {
        geometry.paths = fenceData.data.paths.map(path => new TMap.LatLng(path.lat, path.lng));
      } else if (type === "circle") {
        geometry.center = new TMap.LatLng(fenceData.data.center.lat, fenceData.data.center.lng);
        geometry.radius = Number(fenceData.data.radius);
      } else if (type === "rectangle") {
        geometry.center = new TMap.LatLng(fenceData.data.center.lat, fenceData.data.center.lng);
        geometry.width = fenceData.data.width;
        geometry.height = fenceData.data.height;
      } else if (type === "ellipse") {
        geometry.center = new TMap.LatLng(fenceData.data.center.lat, fenceData.data.center.lng);
        geometry.majorRadius = Number(fenceData.data.majorRadius);
        geometry.minorRadius = Number(fenceData.data.minorRadius);
      }

      return geometry;
    },
    generateOverlay(map) {
      // 生成 overlay 配置

      const TMap = window.TMap;
      const overlayConfig = [
        {
          type: "polygon", // overlay 类型
          id: this.polygonToolId, // overlay 的 id
          factory: TMap.MultiPolygon // overlay 的工厂函数
        },
        {
          type: "circle",
          id: this.circleToolId,
          factory: TMap.MultiCircle
        },
        {
          type: "rectangle",
          id: this.rectangleToolId,
          factory: TMap.MultiRectangle
        },
        {
          type: "ellipse",
          id: this.ellipseToolId,
          factory: TMap.MultiEllipse
        }
      ];

      // 将电子围栏数据按 overlay类型分组
      const fenceGroupByType = this.fence.reduce((acc, item) => {
        acc[item.type] = acc[item.type] || [];
        acc[item.type].push(item);
        return acc;
      }, {});

      // 根据 overlay 类型生成所有的对应的颜色实例对象
      // 例如 { "#000000": 颜色实例 } 
      const getStyle = type => {
        const fenceList = fenceGroupByType[type];
        let styles = {};
        colorList.forEach(color => {
          styles[color] = this.generateStyle(type, color);
        });
        if (fenceList && fenceList.length) {
          fenceGroupByType[type].reduce((acc, item) => {
            acc[item.color] = acc[item.color] || this.generateStyle(type, item.color);
            return acc;
          }, styles);
        }

        return {
          highlight: this.generateStyle(type, "#ffff00"), // 高亮样式
          default: this.generateStyle(type, colorList[0]), // 默认样式
          ...styles, // fenceData 中对应的颜色实例对象
        };
      }

      // 根据 overlay 类型，将 fenceData 转为 geometry 数据
      const getGeometries = type => {
        const fenceList = fenceGroupByType[type];
        if (!fenceList || fenceList.length === 0) return [];
        const data = fenceList.map(item => this.convertToGeometry(item));
        return data;
      }

      // 生成 overlayList 配置
      return overlayConfig.map(item => {
        return {
          overlay: new item.factory({
            map,
            styles: getStyle(item.type),
            geometries: getGeometries(item.type)
          }),
          id: item.id,
          selectedStyleId: "highlight"
        }
      });
    },
    initEditor() {
      // 初始化编辑器
      const TMap = window.TMap;
      const map = this.map;
      const overlayList = this.generateOverlay(map);
      const editor = new TMap.tools.GeometryEditor({
        map,
        overlayList,
        actionMode: TMap.tools.constants.EDITOR_ACTION.DRAW, // 编辑器的工作模式
        activeOverlayId: this.activeOverlayId, // 激活图层
        selectable: true, // 开启选择
        snappable: true, // 开启吸附
        selectedStyleId: "highlight"
      });



      overlayList.forEach(overlayItem => {
        // 监听 overlay 的 hover 事件，更新 tooltip 的内容
        overlayItem.overlay.on("hover", this.updateTooltipContent);
      });

      editor.on("draw_complete", geometry => {
        // 绘制完成

        // 根据当前选取类型解析成标准的电子围栏数据
        const fenceData = this.parseGeometry(geometry, this.currentToolType);
        if (!fenceData) return;

        // 保存当前 fenceData
        this.saveRegion(fenceData);

        // 更新当前绘制的电子围栏数据的颜色
        const nextFenceData = this.fence[this.fence.length - 1];
        this.handleColorChange(nextFenceData);
      });

      editor.on("delete_complete", geometryList => {
        // map 控件中删除 geometry 时，同步删除 fenceData
        const idMap = geometryList.reduce((acc, item) => {
          acc[item.id] = 1;
          return acc;
        }, {});
        this.fence = this.fence.filter(item => !idMap[item.id]);
      });

      editor.on("adjust_complete", geometry => {
        // map 控件中调整 geometry 时，同步调整 fenceData
        const fenceData = this.fence.find(item => item.id === geometry.id);
        if (!fenceData) return;
        const nextFenceData = this.parseGeometry(geometry, fenceData.type);
        Object.assign(fenceData, nextFenceData);
      });

      this.editor = editor;
    },
    async initMap() {
      const [centerLocation] = await Promise.all(this.libLoadTask);
      const TMap = window.TMap;
      const options = {
        zoom: 15,
        showControl: false
      };
      if (centerLocation) {
        const { lng, lat } = centerLocation;
        options.center = new TMap.LatLng(lat, lng);
      }
      const map = new TMap.Map(document.getElementById(this.mapId), options);

      const markerList = [];
      if (options.center) {
        markerList.push(
          {
            position: options.center
          }
        );
      }

      // 创建标注，用来显示地图中心点
      this.marker = new TMap.MultiMarker({
        map,
        geometries: markerList
      });

      this.map = map;
      // 监听地图的鼠标移动事件，更新 tooltip 的坐标
      map.on("mousemove", this.updateTooltipPosition);
      this.initEditor();
    },
    addRegion() {
      // 添加默认电子围栏区域
      if (!this.editor) return;
      const overlayItem = this.editor.getActiveOverlay();
      if (!overlayItem) return;
      // 获取当前激活的 overlay
      const overlay = overlayItem.overlay;

      // 获取当前激活的 overlay 类型
      const currentOverlayType = overlay._layerType.toLowerCase();

      // 获取当前地图的中心点
      const center = overlay.getMap().getCenter();

      const POSITION_OFFSET = 0.01;

      // 默认电子围栏数据
      let defaultFenceData = {
        color: this.generateColorByGeometryLength(),
        type: currentOverlayType, // 当前激活的 overlay 类型
        data: {
          paths: [ // 多边形数据
            {
              lat: center.lat + POSITION_OFFSET, // 纬度 -> top
              lng: center.lng - POSITION_OFFSET, // 经度 -> right 
            },
            {
              lat: center.lat + POSITION_OFFSET,
              lng: center.lng + POSITION_OFFSET
            },
            {
              lat: center.lat - POSITION_OFFSET,
              lng: center.lng + POSITION_OFFSET
            },
            {
              lat: center.lat - POSITION_OFFSET,
              lng: center.lng - POSITION_OFFSET
            },
          ],

          center: { // 圆形数据、矩形数据、椭圆数据的中心点
            lat: center.lat,
            lng: center.lng
          },

          radius: 500, // 圆形半径数据

          width: 500, // 矩形宽度数据
          height: 500, // 矩形高度数据

          majorRadius: 300, // 椭圆长轴半径数据
          minorRadius: 400, // 椭圆短轴半径数据
        }
      };

      // 将默认电子围栏数据转换为地图需要的 geometry 数据，函数中会自动根据类型生成对应的 geometry 数据
      const geometryHalf = this.convertToGeometry(defaultFenceData);

      overlay.add([geometryHalf]);
      const geometries = overlay.getGeometries();
      const fenceData = this.parseGeometry(geometries[geometries.length - 1], currentOverlayType);
      this.saveRegion(fenceData);
    },
    saveRegion(fenceData) {
      // 保存电子围栏区域
      const baseFenceData = {
        title: `区域${this.fence.length + 1}`,
        color: fenceData.color || this.generateColorByGeometryLength(),
      };

      this.fence.push({
        ...baseFenceData,
        ...fenceData,
      });
    },
    handleDelFence(index) {
      // 删除电子围栏区域
      const fenceData = this.fence[index];
      this.fence.splice(index, 1);
      if (!this.editor) return;
      const overlayItem = this.editor.getOverlayList().find(item => item.id.endsWith(fenceData.type));
      const overlay = overlayItem.overlay;
      overlay.remove([fenceData.id]);
    }
  }
};
</script>
<style>
.address-search-popper,
.color-popper-class {
  z-index: 1001 !important;
}
</style>
<style scoped lang="scss">
.right-box {
  display: flex;
  flex-direction: column;
  height: 100%;
  margin-left: 10px;
  padding-left: 20px;
  border-left: 1px solid #EFEFFF;
  justify-content: space-between;

  .color-box {
    height: 513px;
    // overflow-y: auto;

    .color-item-box {
      display: flex;
      align-items: center;
      margin-bottom: 10px;

      .color-input {
        margin: 0 10px;
        width: 180px;
      }
    }
  }

  .add-btn {
    width: 100%;
  }
}

.map-next-box {
  position: relative;
}

.map-next {
  width: 100%;
  height: 450px;

  ::v-deep & > canvas + div {
    z-index: 1 !important;
  }
}

.map-contents-box {
  background-color: #FFFFFF;
  border-radius: 5px;
  padding: 20px;

  &.full-screen {
    position: fixed;
    inset: 0;
    z-index: 120;

    .color-box {
      height: 90vh;
    }

    .map-next {
      height: 84vh;
    }
  }
}

.map-tool-box {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 1;

  .address-search-box {
    position: relative;
    z-index: 1;

    .address-search-input {
      width: 320px;
    }
  }

  .overlay-tool-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
  }

  .tool-box-add {
    display: flex;

    .toolItem {
      width: 32px;
      height: 32px;
      margin: 1px;
      border-radius: 3px;
      background-size: 90%;
      background-position: center;
      background-repeat: no-repeat;
      box-shadow: 0 1px 2px 0 #e4e7ef;
      background-color: #ffffff;
      border: 1px solid #ffffff;

      &.active {
        border-color: #d5dff2;
        background-color: #d5dff2;
      }

      &:hover {
        border-color: #789cff;
      }
    }


    .polygon {
      background-image: url('https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/polygon.png');
    }

    .circle {
      background-image: url('https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/circle.png');
    }

    .rectangle {
      background-image: url('https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/rectangle.png');
    }

    .ellipse {
      background-image: url('https://mapapi.qq.com/web/lbs/javascriptGL/demo/img/ellipse.png');
    }
  }
}

.fullscreen-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 1;
  background-color: rgba($color: #000000, $alpha: .8);
  width: 30px;
  height: 30px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;

  .iconfont-h5 {
    font-size: 20px;
    color: #fff;
  }
}

.map-info {
  font-size: 12px;
  color: #A4A4A4;
  line-height: 24px;
  margin-top: 10px;
}

.geometry-tooltip {
  position: absolute;
  top: 0;
  left: 0;
  background-color: rgba(0, 0, 0, 0.8);
  padding: 10px;
  border-radius: 5px;
  color: #fff;
  line-height: 1.5;
  transform: translate3d(-113%, -50%, 0);

  &::before {
    content: "";
    position: absolute;
    right: -10px;
    top: 13px;
    z-index: 1002;
    border-top: 7px solid transparent;
    border-left: 10px solid rgba(0, 0, 0, 0.8);
    border-bottom: 7px solid transparent;
  }
}
</style>

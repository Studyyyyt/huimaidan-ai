<template>
  <el-card>
    <div class="title-box">
      <span class="title-text">基本信息</span>
    </div>
    <el-form size="small" :model="formValidate" ref="formValidate" :rules="rules" label-width="130px"
      class="delivery-point-form">
      <el-form-item label="提货类型：" prop="delivery_way">
        <el-checkbox-group v-model="formValidate.delivery_way" @change="changeWay">
          <el-checkbox v-for="(item, index) in takeProductList" :key="index" :label="item.value">{{ item.name
            }}</el-checkbox>
        </el-checkbox-group>
        <span class="trip">该提货点可以支撑的提货方式；不勾选同城配送，用户下单同城配送不可选中该自提点</span>
      </el-form-item>
      <el-form-item label="配送方式：" prop="type" v-if="formValidate.switch_city">
        <el-radio-group v-model="formValidate.type">
          <el-radio v-for="(item, index) in deliveryTypeList" :key="index" :label="item.value">{{ item.name
            }}</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="提货点名称：" prop="station_name">
        <el-input v-model="formValidate.station_name" placeholder="请输入发货点名称" class="pageWidth" />
      </el-form-item>
      <el-form-item v-if="deliveryType > 0" label="配送品类：">
        <el-select v-model="formValidate.business" placeholder="请选择" class="pageWidth" clearable>
          <el-option v-for="item in categoryList" :label="item.label" :value="item.key" :key="item.key" />
        </el-select>
        <div style="color: #999;font-size:12px;">该配送品类由第三方自动返回</div>
      </el-form-item>
      <el-form-item v-if="deliveryType > 0" label="所属城市：" prop="city_name">
        <el-select v-model="formValidate.city_name" placeholder="请选择" class="pageWidth" filterable clearable>
          <el-option v-for="item in cityList" :label="item.label" :value="item.key" :key="item.key" />
        </el-select>
      </el-form-item>
      <el-form-item label="提货点地址：" prop="station_address">
        <el-input v-model="formValidate.station_address" enter-button="查找位置" class="pageWidth" placeholder="请输入提货点地址">
          <el-button slot="append" @click="onSearchs">查找位置</el-button>
        </el-input>
        <div slot="content">请点击查找位置选择位置</div>
      </el-form-item>
      <el-form-item label="地图经纬度：">
        <el-input :value="location" placeholder="回显提货点地址的经纬度" disabled class="pageWidth" />
      </el-form-item>
      <el-form-item label="联系人姓名：" prop="contact_name">
        <el-input v-model="formValidate.contact_name" placeholder="请输入联系人电话" class="pageWidth" />
      </el-form-item>
      <el-form-item label="联系人电话：" prop="phone">
        <el-input v-model="formValidate.phone" placeholder="请输入联系人电话" class="pageWidth" />
      </el-form-item>
      <template v-if="deliveryType === DELIVERY_TYPE.DADA">
        <el-form-item label="联系人身份证：">
          <el-input v-model="formValidate.id_card" placeholder="请输入联系人身份证" class="pageWidth" />
        </el-form-item>
        <el-form-item label="门店关联方式：">
          <el-radio-group v-model="formValidate.bind_type">
            <el-radio :label="0">新增门店</el-radio>
            <el-radio :label="1">绑定门店</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item v-if="formValidate.bind_type == 1" label="关联门店ID：">
          <el-input v-model="formValidate.origin_shop_id" placeholder="请输入关联门店ID" class="pageWidth" />
        </el-form-item>
        <el-form-item v-if="formValidate.bind_type == 0" label="达达商家app账号：">
          <el-input v-model="formValidate.username" placeholder="请输入有效手机号" class="pageWidth" />
        </el-form-item>
        <el-form-item v-if="formValidate.bind_type == 0" label="达达商家app密码：">
          <el-input v-model="formValidate.password" placeholder="请输入数字和字母组合密码，长度在6-16位字符" class="pageWidth" />
        </el-form-item>
      </template>
      <el-form-item label="营业日期：" prop="business_date">
        <div v-for="item in week" :key="item.id" :class="{
          'check-btn': true,
          selected: formValidate.business_date && formValidate.business_date.includes(item.id)
        }" @click="toggleWeekday(item.id)">
          {{ item.name }}
        </div>
      </el-form-item>
      <el-form-item label="营业时间：" required>
        <el-time-picker is-range value-format="HH:mm" format="HH:mm" placement="bottom-end"
          :value="timeRange" range-separator="至" start-placeholder="开始时间" end-placeholder="结束时间" placeholder="选择时间范围"  @input="onchangeTime">
        </el-time-picker>
      </el-form-item>
      <el-form-item label="开启状态：">
        <el-switch v-model="formValidate.status" :active-value="1" :inactive-value="0" :width="55" active-text="开启"
          inactive-text="关闭" />
      </el-form-item>
    </el-form>
    <el-dialog v-if="modalMap && mapIframeUrl" v-model="modalMap" :visible.sync="modalMap" title="选择位置"
      close-on-click-modal class="mapBox" custom-class="dialog-scustom" :append-to-body='true'>
      <iframe id="mapPage" width="100%" height="500px" frameborder="0" :src="mapIframeUrl" />
    </el-dialog>


    <template v-if="formValidate.switch_city">
      <div class="title-box">
        <span class="title-text">配送范围</span>
      </div>

      <!-- 配送范围 -->
      <deliveryRange :cost-form="costForm" :lat="Number(formValidate.lat)" :lng="Number(formValidate.lng)" />
    </template>

    <div class="footer">
      <el-button size="small" @click="handleBack">取消</el-button>
      <el-button type="primary" size="small" @click="submitForm('formValidate')">提交</el-button>
    </div>
  </el-card>
</template>
<script>
import {
  getDeliveryCate,
  createStoreApi,
  deliveryStoreDetail,
  deliveryStoreUpdate,
  getCityLst,
  getConfigApi
} from '@/api/systemForm'
import { deliverySetApi } from '@/api/setting';
import deliveryRange from "./components/deliveryRange.vue";

const DELIVERY_TYPE = {
  MERCHANT: 0, // 商家配送
  DADA: 1, // 达达
  UU: 2, // UU
}

export default {
  name: 'deliveryPointForm',
  components: {
    deliveryRange
  },
  data() {
    return {
      DELIVERY_TYPE,
      costForm: {
        radius: 1, // 半径范围
        fence: [], // 电子围栏区域
        range_type: 1, // 配送范围类型
        region: [], // 行政区域范围
      },
      mapKey: "",
      modalMap: false,
      stationId: 0, // 配送门店 ID
      takeProductList: [
        { name: "同城配送", value: 1 },
        { name: "到店自提", value: 2 }
      ], // 提货类型

      week: [
        { id: 1, name: "周一" },
        { id: 2, name: "周二" },
        { id: 3, name: "周三" },
        { id: 4, name: "周四" },
        { id: 5, name: "周五" },
        { id: 6, name: "周六" },
        { id: 7, name: "周日" }
      ],
      deliveryTypeList: [
        {
          name: "商家配送",
          value: DELIVERY_TYPE.MERCHANT
        },
        {
          name: "UU",
          value: DELIVERY_TYPE.UU
        },
        { 
          name: "达达",
          value: DELIVERY_TYPE.DADA
        }
      ], // 支持的同城配送类型
      formValidate: {
        station_name: "",
        business: "",
        type: 0, // 0 -> 同城 1 -> 达达 2 -> UU
        station_address: "",
        delivery_way: [1, 2],
        delivery_type: 0,
        switch_city: 1,
        switch_take: 1,
        business_date: [1, 2, 3, 4, 5, 6, 7],
        business_time_start: "0:00",
        business_time_end: "23:00",
        bind_type: 0,
        city_name: "",
        lat: 0,
        lng: 0,
        id_card: "",
        phone: "",
        status: 0,
        contact_name: ""
      },
      categoryList: [],
      cityList: [],
      merDeliveryType: [], // 同城配送方式0：商家配送 1：达达 2：uu
      deliveryList: [
        { value: "2", name: "同城配送" },
        { value: "1", name: "到店自提" }
      ],
      rules: {
        station_name: [
          { required: true, message: '请输入提货点名称', trigger: 'blur' }
        ],
        business: [
          { required: true, message: '请选择配送品类', trigger: 'change' }
        ],
        contact_name: [
          { required: true, message: '请输入联系人姓名', trigger: 'blur' }
        ],
        phone: [
          { required: true, message: '请输入联系人电话', trigger: 'blur' },
          { pattern: /^1[3456789]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
        ],
        business_date: [
          { required: true, message: '请选择营业日期', trigger: 'change' }
        ],
        station_address: [
          { required: true, message: '请输入提货点地址', trigger: 'blur' }
        ],
        lat: [
          { required: true, message: '请选择经纬度', trigger: 'blur' }
        ],
        lng: [
          { required: true, message: '请选择经纬度', trigger: 'blur' }
        ],
        station_phone: [
          { required: true, message: '请输入提货点电话', trigger: 'blur' },
          { pattern: /^1[3456789]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
        ],
        delivery_way: [
          { required: true, message: '请勾选提货类型', trigger: 'change' }
        ]
      }
    }
  },
  computed: {
    location() {
      const { lat, lng } = this.formValidate;
      if (lat && lng) return lat + "," + lng;
      return "";
    },
    deliveryType() {
      if (this.formValidate.switch_city === 0) return -1;
      return this.formValidate.type;
    },
    timeRange() {
      const {
        business_time_start,
        business_time_end
      } = this.formValidate;
      return [
        business_time_start,
        business_time_end
      ];
    },
    mapIframeUrl() {
      return `https://apis.map.qq.com/tools/locpicker?type=1&key=${this.mapKey}&referer=myapp&search=1`
    }
  },
  watch: {
    deliveryType(deliveryType, oldDeliveryType) {
      if (deliveryType < 1) return;
      if (oldDeliveryType !== DELIVERY_TYPE.MERCHANT) {
        // 如果旧的配送方式不是商家配送，则清空配送品类和所属城市
        this.formValidate.business = "";
        this.formValidate.city_name = "";
      }
      this.getCategoryList();
      this.getCityList();
    }
  },
  async created() {
    this.getMapConfig();
    const merConfigTask = this.getDeliverySettings();

    if (this.$route.query.id) {
      this.stationId = this.$route.query.id;
      const stationInfotask = this.getStationInfo();
      const [_, stationInfo] = await Promise.all([merConfigTask, stationInfotask]);
      if (stationInfo) {
        this.initFormData(stationInfo);
      }
    }
  },
  mounted: function () {
    window.addEventListener(
      'message',
      function (event) {
        // 接收位置信息，用户选择确认位置点后选点组件会触发该事件，回传用户的位置信息
        var loc = event.data
        if (loc && loc.module === 'locationPicker') {
          // 防止其他应用也会向该页面post信息，需判断module是否为'locationPicker'
          window.parent.selectAdderss(loc)
        }
      },
      false
    )
    window.selectAdderss = this.selectAdderss
  },
  methods: {
    async getMapConfig() {
      const res = await getConfigApi();
      this.mapKey = res.data.tx_map_key;
    },
    forceUpdate(e) {
      this.$forceUpdate();
    },
    //选择配送方式
    changeWay(e) {
      this.formValidate.switch_city = e.includes(1) ? 1 : 0
      this.formValidate.switch_take = e.includes(2) ? 1 : 0
    },
    // 营业时间
    onchangeTime(e) {
      this.formValidate.business_time_start = e[0]
      this.formValidate.business_time_end = e[1]
    },
    // 查找位置
    onSearchs() {
      if (!this.mapKey) {
        return this.$message.error('平台未配置腾讯地图KEY');
      }

      this.modalMap = true;
    },
    // 选择经纬度
    selectAdderss(data) {
      this.formValidate.lat = data.latlng.lat
      this.formValidate.lng = data.latlng.lng
      if (data.poiname == '我的位置') {
        this.formValidate.station_address = data.poiaddress
      } else {
        this.formValidate.station_address = data.poiaddress + data.poiname
      }
      this.modalMap = false
    },
    toggleWeekday(id) {
      const index = this.formValidate.business_date.indexOf(id);
      if (index > -1) {
        this.formValidate.business_date.splice(index, 1);
      } else {
        this.formValidate.business_date.push(id);
      }
    },
    // 获取配送品类
    getCategoryList() {
      getDeliveryCate({
        type: this.deliveryType
      })
        .then(res => {
          this.categoryList = res.data
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    // 获取城市列表
    getCityList() {
      getCityLst({
        type: this.deliveryType
      })
        .then(res => {
          // 按城市名称去重
          const cityList = res.data;
          const uniqueCityList = [];
          const cityMap = new Map();
          cityList.forEach(city => {
            if (!cityMap.has(city.label)) {
              cityMap.set(city.label, 1);
              uniqueCityList.push(city);
            }
          });
          this.cityList = uniqueCityList;
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    initFormData(res) {
      let info = res.data
      let delivery_way = []
      if (info.switch_take == 1) delivery_way.push(2) // 到店自提
      if (info.switch_city == 1) delivery_way.push(1) // 同城配送

      const formatBusinessData = (list) => {
        if (!Array.isArray(list)) return [];
        return Array.from(new Set(list.map(Number)));
      }

      this.formValidate = {
        delivery_way, // 提货类型
        type: info.type,
        station_name: info.station_name,
        business: info.business && info.business.key,
        station_address: info.station_address,
        lat: info.lat,
        lng: info.lng,
        contact_name: info.contact_name,
        phone: info.phone,
        id_card: info.id_card,
        username: info.username,
        password: info.password,
        status: info.status,
        city_name: info.city_name,
        switch_take: info.switch_take,
        switch_city: info.switch_city,
        business_time_start: info.business_time_start,
        business_time_end: info.business_time_end,
        business_date: formatBusinessData(info.business_date),
        bind_type: info.bind_type,
        origin_shop_id: info.origin_shop_id
      }

      this.setDefaultDeilveryType();

      const {
        radius,
        fence,
        range_type,
        region
      } = info;

      this.costForm = {
        radius: radius || this.costForm,
        fence: fence || this.fence,
        range_type: range_type || this.range_type,
        region: region || this.region
      };
    },
    // 获取详情数据
    getStationInfo() {
      return deliveryStoreDetail(this.stationId)
        .then(res => {
          return res;
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    // 获取配送设置
    getDeliverySettings() {
      deliverySetApi()
        .then(res => {
          if (Array.isArray(res.data.basicSettings.mer_delivery_type)) {
            this.merDeliveryType = res.data.basicSettings.mer_delivery_type
          }
          if (!this.merDeliveryType.length) {
            return this.$message.error("请先在配送设置中配置配送方式");
          }
          const typeMap = this.merDeliveryType.reduce((acc, type) => {
            acc[type] = 1;
            return acc;
          }, {});
          this.deliveryTypeList = this.deliveryTypeList.filter(item => typeMap[item.value]);
          this.setDefaultDeilveryType();
        })
        .catch(err => {
          this.$message.error(err.message)
        })
    },
    setDefaultDeilveryType() {
      // 检查配送方式
      if (!this.deliveryTypeList.some(item => item.value === this.formValidate.type)) {
        this.formValidate.type = this.deliveryTypeList[0].value;
      }
    },
    submitForm(name) {
      this.$refs[name].validate(async (valid, errData) => {
        if (valid) {
          var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/
          if (this.formValidate.id_card && !reg.test(this.formValidate.id_card)) {
            return this.$message.warning('请输入正确的身份证号！')
          }

          const payload = {
            ...this.formValidate,
            ...this.costForm
          };

          const task = this.stationId ? deliveryStoreUpdate(this.stationId, payload)
            : createStoreApi(payload);

          try {
            const res = await task;
            this.$message.success(res.message);
            setTimeout(() => {
              this.handleBack();
            }, 500);
          } catch (error) {
            this.$message.error(error.message);
          }
        } else {
          for (const err of Object.values(errData)) {
            this.$message.error(err[0].message);
            break;
          }
        }
      })
    },
    handleBack() {
      this.$router.back();
    }
  }
}
</script>

<style scoped lang="scss">
.check-btn {
  display: inline-block;
  height: 32px;
  font-size: 12px;
  line-height: 32px;
  padding: 0px 10px;
  margin-right: 10px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  cursor: pointer;
  user-select: none;
}

.check-btn.selected {
  background-color: var(--prev-color-primary);
  color: white;
  border-color: var(--prev-color-primary);
}

.trip {
  font-size: 12px;
  color: #999;
}

::v-deep .add-point-dialog {
  margin-top: 6vh !important;

  .el-dialog__body {
    max-height: 760px;
  }
}

.footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  height: 60px;
  background: #fff;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 90;
}

.delivery-point-form {
  ::v-deep .el-form-item {
    margin-bottom: 22px;
  }
}

.title-box {
  height: 18px;
  display: flex;
  align-items: center;
  margin-bottom: 22px;

  .title-text {
    display: flex;
    align-items: center;
  }

  .title-text::before {
    display: block;
    content: "";
    width: 2px;
    height: 14px;
    background-color: #4073fa;
    margin-right: 5px;
  }
}
</style>

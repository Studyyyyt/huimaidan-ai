<template>
  <div>
    <el-dialog title="改约" :visible.sync="dialogTableVisible" width="50%">
      <div class="form-box">
        <div class="title">预约信息</div>
        <el-table
          :data="orderDetailList.orderProduct"
          size="small"
          class="mt20 mb20"
        >
          <el-table-column label="商品信息" min-width="300">
            <template slot-scope="scope">
              <div class="tab">
                <div class="demo-image__preview">
                  <el-image
                    :src="scope.row.cart_info.product.image"
                    :preview-src-list="[scope.row.cart_info.product.image]"
                  />
                </div>
                <div>
                  <div>{{ scope.row.cart_info.product.store_name }}</div>
                  <div class="line1 gary">
                    规格：{{
                      scope.row.cart_info.productAttr.sku
                        ? scope.row.cart_info.productAttr.sku
                        : "默认"
                    }}
                  </div>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="预约数量" min-width="90">
            <template slot-scope="scope">
              <div class="tab">
                <div class="line1">
                  {{ scope.row.product_num }}
                </div>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <el-form :model="form" ref="form" :rules="rules" label-width="auto">
          <el-form-item label="预约时间：" prop="reservation_date">
            <el-select
              v-model="form.reservation_date"
              size="small"
              placeholder="请选择"
              style="width:150px"
            >
              <el-option
                v-for="item in month"
                :key="item.date"
                :label="item.date"
                :value="item.date"
              ></el-option>
            </el-select>

            <TimePicker
              type="timerange"
              format="HH:mm"
              placeholder="选择时间"
              v-model="form.reservation_time_part"
              placement="bottom-end"
              style="width: 150px;"
              size="default"
              class="mr10"
              @change="reservationTimeChange"
            ></TimePicker>
          </el-form-item>
          <el-form-item label="服务方式：" prop="order_type">
            <el-radio-group v-model="form.order_type">
              <el-radio :label="0">上门服务</el-radio>
              <el-radio :label="1">到店服务</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="联系人：" prop="real_name">
            <el-input
              v-model="form.real_name"
              size="small"
              placeholder="请输入联系人"
              style="width:300px"
            ></el-input>
          </el-form-item>
          <el-form-item label="联系电话：" prop="user_phone">
            <el-input
              v-model="form.user_phone"
              size="small"
              type="number"
              placeholder="请输入联系电话"
              style="width:300px"
            ></el-input>
          </el-form-item>
          <el-form-item
            label="上门地址："
            prop="user_address"
            v-if="form.order_type == 0"
          >
            <el-input
              v-model="form.user_address"
              size="small"
              placeholder="请输入上门地址"
              style="width:300px"
            ></el-input>
          </el-form-item>

          <!-- 自定义表单 -->
          <div v-for="(val, index1) in form.order_extend" :key="index1">
            <div class="title mb20">{{ formInfoData.name }}</div>
            <el-form-item
              v-for="(item, index) in formInfoData.form_keys"
              :key="index"
            >
              <div slot="label" class="label">
                <span class="required" v-if="item.props.titleShow.val == 1"
                  >*</span
                >{{ item.label }}：
              </div>
              <el-date-picker
                v-if="item.type === 'dates'"
                v-model="val[item.label]"
                type="date"
                value-format="yyyy-MM-dd"
                format="yyyy-MM-dd"
                style="width:300px"
                size="small"
              ></el-date-picker>

              <TimePicker
                v-if="item.type === 'times'"
                type="time"
                format="HH:mm"
                :placeholder="item.props.tipConfig.value"
                v-model="val[item.label]"
                placement="bottom-start"
                style="width: 300px;"
                size="default"
                class="mr10"
              ></TimePicker>

              <TimePicker
                v-if="item.type === 'timeranges'"
                type="timerange"
                format="HH:mm"
                placeholder="选择时间"
                v-model="val[item.label]"
                style="width: 300px;"
                size="default"
                class="mr10"
              ></TimePicker>

              <el-date-picker
                v-if="item.type === 'dateranges'"
                v-model="val[item.label]"
                type="daterange"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                style="width:300px"
                size="small"
              ></el-date-picker>

              <LazyCascader
                v-if="item.type === 'citys'"
                v-model="val[item.label]"
                :nameVal="val[item.label]"
                placeholder="请选择"
                style="width: 300px"
                :props="props"
                clearable
                :filterable="false"
              />

              <el-checkbox-group
                v-model="checkList"
                v-if="item.type === 'checkboxs'"
              >
                <el-checkbox
                  v-for="(check, checkId) in item.props.wordsConfig.list"
                  :label="check.val"
                  :key="checkId"
                ></el-checkbox>
              </el-checkbox-group>

              <el-radio-group
                v-model="val[item.label]"
                v-if="item.type === 'radios'"
              >
                <el-radio
                  v-for="(radio, radioId) in item.props.wordsConfig.list"
                  :label="radio.val"
                  :key="radioId"
                  >{{ radio.val }}</el-radio
                >
              </el-radio-group>

              <el-select
                v-model="val[item.label]"
                placeholder="请选择"
                v-if="item.type === 'selects'"
                size="small"
                style="width:300px"
              >
                <el-option
                  v-for="option in item.props.wordsConfig.list"
                  :key="option.val"
                  :label="option.val"
                  :value="option.val"
                ></el-option>
              </el-select>

              <!-- 上传图片 -->
              <div class="acea-row" v-if="item.type === 'uploadPicture'">
                <div
                  v-for="(item, indexj) in val[item.label]"
                  :key="indexj"
                  class="pictrue"
                  draggable="false"
                >
                  <img :src="item" />
                  <i
                    class="el-icon-error btndel"
                    @click="handleRemove(val[item.label], indexj)"
                  />
                </div>
                <div class="uploadCont" title="750*750px">
                  <div
                    class="upLoadPicBox"
                    v-if="
                      val[item.label] &&
                        val[item.label].length <= item.props.numConfig.val
                    "
                    @click="modalPicTap(val[item.label])"
                  >
                    <div class="upLoad">
                      <i class="el-icon-camera cameraIconfont" />
                    </div>
                  </div>
                </div>
              </div>

              <el-input
                v-if="item.type === 'texts'"
                v-model="val[item.label]"
                size="small"
                placeholder="请输入"
                style="width:300px"
              ></el-input>
            </el-form-item>
          </div>
        </el-form>
      </div>
      <div slot="footer" class="dialog-footer">
        <el-button>取消</el-button>
        <el-button type="primary" @click="submitFn">确定</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import { TimePicker } from "view-design";
import "view-design/dist/styles/iview.css";
import { systemFormInfo } from "@/api/setting";
import { orderRescheduleApi } from "@/api/order";
import { cityListV2, cityList } from "@/api/freight";
import LazyCascader from "@/components/lazyCascader/index";
const cacheAddress = {};
export default {
  name: "DialogReschedule",
  components: { LazyCascader, TimePicker },
  props: {
    orderDetailList: {
      // 订单详情
      type: Object,
      default: () => ({})
    },
    month: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      formInfoData: {},
      checkList: ["选中且禁用", "复选框 A"],
      form: {
        reservation_date: "",
        reservation_time_part: [],
        part_start: "",
        part_end: "",
        order_type: 0,
        real_name: "",
        user_phone: "",
        user_address: "",
        order_extend: []
      },
      requiredList: [], // 必填项数据
      formProps: {},
      props: {
        children: "children",
        label: "name",
        value: "id",
        multiple: false,
        lazy: true,
        lazyLoad: this.lazyLoad,
        checkStrictly: false
      },
      citysData: [], // 所有省市区数据
      rules: {
        reservation_date: [
          { required: true, message: "请选择日期", trigger: "blur" }
        ],
        order_type: [
          { required: true, message: "请选择服务方式", trigger: "change" }
        ],
        real_name: [
          { required: true, message: "请输入联系人", trigger: "blur" }
        ],
        user_phone: [
          { required: true, message: "请输入联系电话", trigger: "blur" }
        ],
        user_address: [
          { required: true, message: "请输入上门地址", trigger: "blur" }
        ]
      },
      showPopover: false,
      dialogTableVisible: false,
      radio: null // 初始化 radio 变量
    };
  },
  watch: {
    orderDetailList: {
      handler(val) {
        this.setForm(val);
      },
      deep: true
    }
  },

  methods: {
    lazyLoad(node, resolve) {
      if (cacheAddress[node]) {
        cacheAddress[node]().then(res => {
          resolve([...res.data]);
        });
      } else {
        const p = cityListV2(node);
        cacheAddress[node] = () => p;
        p.then(res => {
          res.data.forEach(item => {
            item.leaf = item.snum === 0;
          });
          cacheAddress[node] = () =>
            new Promise(resolve1 => {
              setTimeout(() => resolve1(res), 300);
            });
          resolve(res.data);
        }).catch(res => {
          this.$message.error(res.message);
        });
      }
    },

    setForm(val) {
      this.form.reservation_date = val.orderProduct[0].reservation_date;
      let time = val.orderProduct[0].reservation_time_part.split("-");
      this.form.reservation_time_part = time.map(item => item.trim());
      this.form.part_start = this.form.reservation_time_part[0];
      this.form.part_end = this.form.reservation_time_part[1];
      this.form.order_type = val.order_type;
      this.form.real_name = val.real_name;
      this.form.user_phone = val.user_phone;
      this.form.user_address = val.user_address;
    },

    modalPicTap(val) {
      const _this = this;
      const attr = [];
      this.$modalUpload(function(img) {
        img.map(item => {
          val.push(item);
        });
      }, "2");
    },

    reservationTimeChange() {
      this.form.part_start = this.form.reservation_time_part[0];
      this.form.part_end = this.form.reservation_time_part[1];
    },

    handleRemove(val, i) {
      val.splice(i, 1);
    },

    getMonth() {
      if (this.orderDetailList.orderProduct[0].cart_info.product.mer_form_id) {
        this.getForm(
          this.orderDetailList.orderProduct[0].cart_info.product.mer_form_id
        );
      }
    },

    openBox() {
      // 获取整个省市区数据
      this.dialogTableVisible = true;
      this.getMonth();
      if (this.orderDetailList) {
        this.setForm(this.orderDetailList);
      }
    },

    // 递归根据name查找id数据
    findNamesByIds(ids) {
      let result = [];
      function traverse(node) {
        if (ids.includes(node.name)) {
          result.push(node.id);
        }
        if (node.children) {
          for (const child of node.children) {
            traverse(child);
          }
        }
      }
      for (const node of this.citysData) {
        traverse(node);
      }
      return result;
    },
    findNamesByNames(id) {
      let ids = id.map(str => parseInt(str));
      let result = [];
      function traverse(node) {
        if (ids.includes(node.id)) {
          result.push(node.label);
        }
        if (node.children) {
          for (const child of node.children) {
            traverse(child);
          }
        }
      }
      for (const node of tree) {
        traverse(node);
      }

      let str = result.join("/");
      return str;
    },

    submitFn() {
      if (this.form.reservation_time_part.length > 0) {
        this.form.part_start = this.form.reservation_time_part[0];
        this.form.part_end = this.form.reservation_time_part[1];
      }

      this.$refs.form.validate(valid => {
        // 系统表单必填项校验
        this.form.order_extend.map(item => {
          for (let key in item) {
            if (this.requiredList.includes(key)) {
              if (item[key].length == 0) {
                this.$message.error(`${key}不能为空`);
                return false;
              }
            }

            let checkboxs = ["checkboxs", "dateranges"];
            if (checkboxs.includes(this.formProps[key])) {
              if (item[key]) {
                item[key] = item[key].join(",");
              } else {
                item[key] = [];
              }
            } else if (this.formProps[key] == "timeranges") {
              if (item[key]) {
                item[key] = item[key].join("-");
              } else {
                item[key] = "";
              }
            } else if (this.formProps[key] == "citys") {
              // 省市区处理
              if (item[key]) {
                item[key] = this.findNamesByNames(item[key]);
              } else {
                item[key] = "";
              }
            }
          }
        });
      });
      orderRescheduleApi(this.orderDetailList.order_id, this.form)
        .then(res => {
          this.$message.success(res.message);
          this.dialogTableVisible = false;
          this.$emit("getInfo", this.orderDetailList.order_id);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    getForm(id) {
      systemFormInfo(id)
        .then(res => {
          for (let key in res.data.value) {
            if (res.data.value[key].name === "citys")
              if (JSON.parse(localStorage.getItem("citysData"))) {
                this.citysData = JSON.parse(localStorage.getItem("citysData"));
              } else {
                cityList().then(res => {
                  this.citysData = res.data;
                  localStorage.setItem("citysData", JSON.stringify(res.data));
                });
              }

            this.formProps[res.data.value[key].titleConfig.value] =
              res.data.value[key].name;

            if (res.data.value[key].titleShow.val == 1) {
              this.requiredList.push(res.data.value[key].titleConfig.value);
            }
          }
          res.data.form_keys.forEach(item => {
            for (let key in res.data.value) {
              if (item.key === res.data.value[key].key) {
                item.props = {
                  ...res.data.value[key]
                };
              }
            }
          });
          this.processingData();
          this.formInfoData = res.data;
        })
        .catch(error => {
          console.error("获取表单信息时发生错误", error);
        });
    },
    processingData() {
      let checkboxs = ["checkboxs", "dateranges"];
      this.orderDetailList.order_extend.forEach(extendItem => {
        for (let key in extendItem) {
          if (checkboxs.includes(this.formProps[key])) {
            if (extendItem[key]) {
              extendItem[key] = extendItem[key].split(",");
            } else {
              extendItem[key] = [];
            }
          } else if (this.formProps[key] == "timeranges") {
            if (extendItem[key]) {
              extendItem[key] = extendItem[key].split("-");
            } else {
              extendItem[key] = [];
            }
          } else if (this.formProps[key] == "citys") {
            // 省市区处理
            if (extendItem[key]) {
              extendItem[key] = this.findNamesByIds(extendItem[key].split("/"));
            } else {
              extendItem[key] = [];
            }
          }
        }
      });
      for (let key in this.form) {
        this.form[key] = this.orderDetailList[key];
        //   // 循环赋值
        this.form.reservation_date = this.orderDetailList.orderProduct[0].reservation_date;
        if (this.orderDetailList.orderProduct[0].reservation_time_part) {
          // 取字符串 - 前面的值
          const startPart = this.orderDetailList.orderProduct[0]
            .reservation_time_part;
          this.form.part_start = startPart.split("-")[0].trim();
          this.form.part_end = startPart.split("-")[1].trim();
          this.form.reservation_time_part = [
            this.form.part_start,
            this.form.part_end
          ];
        }
      }
    }
  }
};
</script>
<style scoped lang="scss">
.form-box {
  height: 500px;
  overflow-y: auto;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
  .label {
    .required {
      color: red;
      font-size: 13px;
      margin-right: 5px;
    }
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;
    position: relative;
    cursor: pointer;
    img {
      width: 100%;
      height: 100%;
    }
    .btndel {
      position: absolute;
      z-index: 1;
      width: 20px !important;
      height: 20px !important;
      left: 46px;
      top: -4px;
    }
  }
}
.title {
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary);
  font-size: 15px;
  line-height: 15px;
  color: #303133;
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
.plan-footer-one {
  position: relative;
  cursor: pointer;
  -webkit-appearance: none;
  background-color: #fff;
  background-image: none;
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  color: #c0c4cc;
  display: inline-block;
  font-size: inherit;
  min-height: 32px;
  line-height: 30px;
  outline: none;
  font-size: 13px;
  padding: 0 10px;

  -webkit-transition: border-color 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
  transition: border-color 0.2s cubic-bezier(0.645, 0.045, 0.355, 1);
  width: 100%;
}
.el-icon-arrow-down {
  font-weight: 400;
  position: absolute;
  right: 10px;
  top: 8px;
}
.flex-box {
  display: flex;
}
.item-box {
  padding: 5px 10px;
  font-size: 13px;
  cursor: pointer;
}
</style>

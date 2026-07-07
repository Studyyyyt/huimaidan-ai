<template>
  <div class="divBox">
    <el-card class="box-card dataBox">
      <el-tabs v-model="activeName">
        <el-tab-pane label="上门服务流程" name="1" />
        <el-tab-pane label="到店服务流程" name="2" />
      </el-tabs>

      <el-table
        :data="tableData1"
        style="width: 100%"
        v-if="activeName == 1"
        class="mt10"
      >
        <el-table-column prop="name" label="节点名称" width="180">
        </el-table-column>
        <el-table-column prop="status" label="状态" width="200">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.status"
              v-model="scope.row.status"
              disabled
              active-value="1"
              inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />

            <el-switch
              v-else
              v-model="form[scope.row.key]"
              active-value="1"
              inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />
          </template>
        </el-table-column>
        <el-table-column prop="info" label="说明">
          <template slot-scope="scope">
            <div v-html="formattedGreeting(scope.row.info)"></div>
          </template>
        </el-table-column>
        <el-table-column prop="address" label="操作" width="100">
          <template slot-scope="scope">
            <el-button
              type="text"
              v-if="scope.row.action"
              size="mini"
              @click="open(scope.row)"
              >{{ scope.row.action }}</el-button
            >
          </template>
        </el-table-column>
      </el-table>
      <el-table
        :data="tableData2"
        style="width: 100%"
        v-if="activeName == 2"
        class="mt10"
      >
        <el-table-column prop="name" label="节点名称" width="180">
        </el-table-column>
        <el-table-column prop="status" label="状态" width="180">
          <template slot-scope="scope">
            <el-switch
              v-if="scope.row.status"
              v-model="scope.row.status"
              disabled
              active-value="1"
              inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />

            <el-switch
              v-else
              v-model="form[scope.row.key]"
              active-value="1"
              inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
            />
          </template>
        </el-table-column>
        <el-table-column prop="info" label="说明">
          <template slot-scope="scope">
            <div v-html="formattedGreeting(scope.row.info)"></div>
          </template>
        </el-table-column>
      </el-table>

      <div style="height: 30px;"></div>

      <!-- 配送范围 -->
      <deliveryRange :cost-form="form" label="上门服务范围" remarks="收货地址在服务范围之外的买家将不可下单。" :labelWidth="100" />

    </el-card>


    <!-- 打卡范围 -->
    <el-dialog title="管理表单设置" :visible.sync="clockVisible" width="600px">
      <el-form ref="form" :model="form" :rules="rules" label-width="100px">
        <template v-if="type === 1">
          <el-form-item label="打卡限制：">
            <el-radio-group v-model="radioType" @change="typeChange">
              <el-radio :label="1">服务范围内打卡</el-radio>
              <el-radio :label="2">不限制打卡地址</el-radio>
            </el-radio-group>
            <div class="tip">未配置地图时，请选择不限制打卡地址</div>
          </el-form-item>
          <el-form-item
            label="打卡范围："
            v-if="radioType == 1"
            prop="checkin_radius"
          >
            <el-input
              v-model.number="form.checkin_radius"
              size="small"
              type="number"
              :min="20"
              style="width: 100%;"
            >
              <span slot="suffix" class="tip">米</span>
            </el-input>
          </el-form-item>
          <el-form-item label="打卡要求：">
            <el-radio-group v-model="form.checkin_take_photo">
              <el-radio label="1">需要拍照备注</el-radio>
              <el-radio label="0">跳过拍照备注环节</el-radio>
            </el-radio-group>
          </el-form-item>
        </template>
        <template v-if="type === 2">
          <el-form-item label="关联表单：" prop="trace_form_id">
            <div class="flex">
              <el-select
                size="small"
                class="pageWidth"
                clearable
                v-model="form.trace_form_id"
                @change="getFormInfo"
              >
                <el-option
                  v-for="items in formList"
                  :key="items.form_id"
                  :value="items.form_id"
                  :label="items.name"
                  >{{ items.name }}
                </el-option>
              </el-select>
              <router-link
                class="link"
                :to="{ path: this.roterPre + '/systemForm/form_create?id=0' }"
                target="_blank"
              >
                <el-button size="small" class="ml15 mr14"
                  >添加系统表单</el-button
                >
              </router-link>
              <el-button size="small" @click="getFormList" style="height: 32px;"
                >刷新</el-button
              >
            </div>
            <div class="tip">服务人员结束服务之前，必须提交此表单内容。</div>
          </el-form-item>
          <el-form-item v-if="form.trace_form_id">
            <div style="width: 350px;">
              <iframe
                id="iframe"
                class="iframe-box"
                :src="formUrl"
                frameborder="0"
                ref="iframe"
              ></iframe>
            </div>
          </el-form-item>
        </template>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="clockVisible = false" size="small">取消</el-button>
        <el-button type="primary" @click="submit" size="small">确定</el-button>
      </div>
    </el-dialog>

    <el-card class="submit-card">
      <div>
        <el-button type="primary" @click="saveChange">提交</el-button>
      </div>
    </el-card>
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
  associatedFormList,
  configTypeApi,
  getConfigDataApi
} from "@/api/product";
import SettingMer from "@/libs/settingMer";
import { roterPre } from "@/settings";
import deliveryRange from "@/views/cityDelivery/deliveryPoint/components/deliveryRange.vue";

export default {
  name: "LabelList",
  components: {
    deliveryRange
  },
  data() {
    return {
      activeName: "1",
      type: 1,
      clockVisible: false,
      form: {
        trace_form_id: "",
        checkin_radius: 0,
        enable_assigned: "1",
        checkin_take_photo: "1",
        enable_checkin: "1",
        enable_trace: "1",

        radius: 1, // 半径范围
        fence: [], // 电子围栏区域
        range_type: 1, // 配送范围类型
        region: [], // 行政区域范围
      },
      radioType: 1,
      rules: {
        checkin_radius: [
          { required: true, message: "请输入打卡范围", trigger: "change" }
        ],
        trace_form_id: [
          { required: true, message: "请选择关联表单", trigger: "change" }
        ]
      },
      roterPre: roterPre,
      formUrl: "",
      formList: [],
      baseURL: SettingMer.httpUrl || "http://localhost:8080",
      tableData1: [
        {
          name: "用户预约",
          status: "1",
          disable: true,
          info: "  用户预约为必要节点，不可关闭"
        },
        {
          name: "派单",
          key: "enable_assigned",
          info: `派单开启则由管理员指派一个服务人员，派单关闭则为服务人员抢单模式`
        },
        {
          name: "上门打卡",
          key: "enable_checkin",
          info: "上门打卡关闭之后，则无需开始服务直接进入下一环节",
          type: 1,
          action: "上门打卡范围"
        },
        {
          name: "服务过程留凭",
          key: "enable_trace",
          info:
            "服务过程留凭根据关联系统表单填写提交，服务过程选填可跳过，关闭则直接进入下一环节",
          action: "关联系统表单",
          type: 2
        },
        {
          name: "结束服务",
          status: "1",
          disable: true,
          info: "服务人员服务完成必须核销"
        }
      ],
      tableData2: [
        {
          name: "用户预约",
          status: "1",
          disable: true,
          info: "  用户预约为必要节点，不可关闭"
        },
        {
          name: "派单",
          key: "enable_tostore_assigned",
          info:
            "派单开启则由管理员指派一个服务人员，派单关闭则为服务人员均可核销"
        },
        {
          name: "结束服务",
          status: "1",
          disable: true,
          info: "服务人员服务完成必须核销"
        }
      ]
    };
  },
  mounted() {
    this.getFormList();
    this.getdata();
  },
  methods: {
    typeChange(val) {
      if (this.radioType == 2) {
        this.form.checkin_radius = 0;
      } else {
        this.form.checkin_radius = 500;
      }
    },

    open(row) {
      this.type = row.type;
      if (row.type == 1) {
        this.clockVisible = true;
      } else {
        this.getFormInfo();
        this.clockVisible = true;
      }
    },

    formattedGreeting(val) {
      if (this.activeName == 1) {
        return val.replace(
          "派单关闭则为服务人员抢单模式",
          `<span style="color: #FF7D00">派单关闭则为</span>服务人员<span style="color: #FF7D00">抢单模式</span>`
        );
      } else {
        return val.replace(
          "派单关闭则为服务人员均可核销",
          `<span style="color: #FF7D00">派单关闭则为</span>服务人员<span style="color: #FF7D00">均可核销</span>`
        );
      }
    },

    getdata() {
      getConfigDataApi().then(res => {
        this.form = res.data.reservation;
        Object.assign(this.form, {
          fence: this.form.fence || [],
          radius: this.form.radius || 1,
          range_type: this.form.range_type || 1,
          region: this.form.region || [],
        });
        this.form.checkin_take_photo = res.data.reservation.checkin_take_photo || "0";
        this.getFormInfo();
        if (this.form.checkin_radius && this.form.checkin_radius != 0) {
          this.radioType = 1;
        } else {
          this.radioType = 2;
        }
      });
    },

    submit() {
      this.$refs["form"].validate(valid => {
        if (valid) {
          this.clockVisible = false;
        }
      });
    },

    saveChange() {
      configTypeApi("reservation", this.form)
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 系统表单下拉数据
    getFormList() {
      associatedFormList()
        .then(res => {
          this.formList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 关联的表单信息
    getFormInfo() {
      if (!this.form.trace_form_id) {
        return;
      } else {
        let time = new Date().getTime() * 1000;
        let formUrl = `${
          this.baseURL
        }/pages/admin/system_form/index?inner_frame=1&time=${time}&form_id=${
          this.form.trace_form_id
        }`;
        this.formUrl = formUrl;
      }
    }
  }
};
</script>
<style scoped lang="scss">
::v-deep.el-table {
  font-size: 13px;
}

.tip {
  font-size: 12px;
  color: #999999;
}

.iframe-box {
  min-height: 300px;
}

.submit-card {
  display: flex;
  position: fixed;
  z-index: 2;
  width: 100%;
  bottom: 0;
  right: 0;
  justify-content: center;
  align-items: center;
}
</style>

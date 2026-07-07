<template>
  <div>
    <el-form-item label="服务模式：" required>
      <el-radio-group v-model="formValidate.reservation_type">
        <el-radio :label="1">到店服务</el-radio>
        <el-radio :label="2">上门服务</el-radio>
        <el-radio :label="3">上门+到店服务</el-radio>
      </el-radio-group>

      <div class="form-tip">用户购买此预约服务商品，可以选择的服务方式</div>
    </el-form-item>
    <el-form-item label="剩余可约数量：" required>
      <el-switch
        v-model="formValidate.show_num_type"
        size="small"
        :active-value="1"
        :inactive-value="0"
      />
      <div class="form-tip">关闭后，用户无法查看各时段的剩余预约数量</div>
    </el-form-item>
    <el-form-item label="可预约日期：" required>
      <el-radio-group v-model="formValidate.sale_time_type">
        <el-radio :label="1">每天</el-radio>
        <el-radio :label="2">自定义</el-radio>
      </el-radio-group>
      <div v-if="formValidate.sale_time_type === 2" class="flex mt10">
        <el-date-picker
          v-model="timeVal"
          type="daterange"
          size="small"
          style="width: 250px;"
          class="mr20"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="yyyy-MM-dd"
        />
        <div
          v-for="item in week"
          :key="item.id"
          :class="{
            'check-btn': true,
            selected: formValidate.sale_time_week.includes(item.id)
          }"
          @click="toggleWeekday(item.id)"
        >
          {{ item.name }}
        </div>
      </div>
      <div class="form-tip">
        设置预约服务的可预约日期；到达设置的可预约日期之后，该预约商品自动下架；
        <span v-if="formValidate.sale_time_type == 2">
          {{ weekRemarks(week) }}
        </span>
      </div>
    </el-form-item>

    <el-form-item label="预约日期范围：" required>
      <div>
        对用户展示
        <el-input-number
          v-model="formValidate.show_reservation_days"
          size="small"
          controls-position="right"
          class="ml10 mr10"
          :precision="0"
          :min="1"
          :max="10"
        ></el-input-number>
        天内的可预约日期
      </div>
      <div class="form-tip">
        用户端可以看到的可预约日期。示例：设置1天，则代表只可以预约当天
      </div>
    </el-form-item>

    <!-- 预约日期限量 -->
    <el-form-item label="提前预约：" required>
      <el-radio-group v-model="formValidate.is_advance" class="quota-settings">
        <div class="radio-item">
          <el-radio :label="0">无需提前预约</el-radio>
        </div>
        <div class="radio-item">
          <el-radio :label="1">
            用户要求提前
            <el-input-number
              v-model="formValidate.advance_time"
              :min="1"
              :max="999"
              :precision="0"
              controls-position="right"
              class="ml10 mr10"
              size="small"
            />
            小时进行预约
          </el-radio>
        </div>
        <div class="form-tip mt10">
          用户只能预约间隔时间后的时段。示例：当前10:00,设置2h，则用户只可预约12:00往后的时段
        </div>
      </el-radio-group>
    </el-form-item>

    <el-form-item label="取消订单：" required>
      <el-radio-group
        v-model="formValidate.is_cancel_reservation"
        class="quota-settings"
      >
        <div class="radio-item">
          <el-radio :label="0">不允许取消</el-radio>
        </div>
        <div class="radio-item">
          <el-radio :label="1">
            服务开始
            <el-input-number
              v-model="formValidate.cancel_reservation_time"
              :min="1"
              :max="999"
              :precision="0"
              controls-position="right"
              class="ml10 mr10"
              size="small"
            />
            小时之前，允许取消并自动退款
          </el-radio>
        </div>
        <div class="form-tip mt10">
          设置用户最晚可以取消预约的时间。示例：设置2h，用户预约12:00-14:00，则当天10:00之前允许用户取消预约
        </div>
      </el-radio-group>
    </el-form-item>
    <el-form-item label="表单信息：" required>
      <el-radio-group v-model="formValidate.reservation_form_type">
        <el-radio :label="1">每个预约提交一次</el-radio>
        <el-radio :label="2">每单提交一次 </el-radio>
      </el-radio-group>
      <div class="form-tip">
        一次购买2件预约商品，选择每个预约提交一次则需要填写两遍关联系统表单，选择每单提交一次则只需要填写一遍关联系统表单
      </div>
    </el-form-item>
    <el-form-item label="关联表单：">
      <div class="acea-row">
        <el-select
          size="small"
          class="pageWidth"
          clearable
          v-model="formValidate.mer_form_id"
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
          <el-button size="small" class="ml15 mr14">添加系统表单</el-button>
        </router-link>
        <el-button size="small" @click="getFormList">刷新</el-button>
      </div>
      <div class="form-tip">
        用户购买此服务时，必须填写关联表单中设置的字段内容才能够进行订单支付，例如：部分服务购买必须填写身份证号、作业环境等字段补充
      </div>
    </el-form-item>
    <el-form-item v-if="formValidate.mer_form_id">
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
  </div>
</template>

<script>
export default {
  props: {
    formValidate: { type: Object, default: () => {} }, // 表单验证方法
    formList: { type: Array, default: () => [] }, // 表单列表数据
    formUrl: { type: String, default: "" }, // 表单地址
    roterPre: { type: String, default: "" }
  },
  data() {
    return {
      // 表单数据
      serviceTypes: [`1`, `2`],
      timeVal: [],
      week: [
        { id: 1, name: "周一" },
        { id: 2, name: "周二" },
        { id: 3, name: "周三" },
        { id: 4, name: "周四" },
        { id: 5, name: "周五" },
        { id: 6, name: "周六" },
        { id: 7, name: "周日" }
      ],

      // 表单验证规则
      rules: {
        bookingPeriod: [
          { required: true, message: "请选择可预约时段", trigger: "change" }
        ]
      },
      // 模拟商品数据
      productOptions: [
        { value: "1", label: "商品1" },
        { value: "2", label: "商品2" },
        { value: "3", label: "商品3" }
      ]
    };
  },
  computed: {
    weekRemarks() {
      return (week) => {
        let unSelect = ''
        let selected = ''
        week.forEach(item => {
          if (this.formValidate.sale_time_week.includes(item.id)) {
            selected = selected + item.name + '、'
          } else {
            unSelect = unSelect + item.name + '、'
          }
        })
        let weekStr
        if (unSelect.length == 0) {
          weekStr = '周一到周日都选中，在可预约日期中的周一到周日可用'
        } else if (selected.length == 0) {
          weekStr = '周一到周日都未选中，在可预约日期中无可用日期'
        } else {
          const selectedStr = selected.slice(0, -1)
          const unSelectStr = unSelect.slice(0, -1)
          weekStr = unSelectStr + '未选中，在可预约日期中' + selectedStr + '可用'
        }
        return weekStr
      }
    }
  },
  watch: {
    timeVal(val) {
      this.formValidate.sale_time_start_day = val[0];
      this.formValidate.sale_time_end_day = val[1];
    }
    // formValidate(val){
    //     if(val.sale_time_type == 2){
    //         this.timeVal = [val.sale_time_start_day,val.sale_time_end_day]
    //     }
    // }
  },
  mounted() {
    if (this.formValidate.product_id == 0) return false;
    if (this.formValidate.sale_time_type == 2) {
      this.timeVal = [
        this.formValidate.sale_time_start_day,
        this.formValidate.sale_time_end_day
      ];
    }
  },
  methods: {
    // 处理表单提交
    handleSubmit() {
      ElMessage.success("设置保存成功");
    }
  },
  methods: {
    toggleWeekday(id) {
      const index = this.formValidate.sale_time_week.indexOf(id);
      if (index > -1) {
        this.formValidate.sale_time_week.splice(index, 1);
      } else {
        this.formValidate.sale_time_week.push(id);
      }
    },
    getFormList() {
      this.$emit("getFormList");
    },
    getFormInfo() {
      this.$emit("getFormInfo");
    }
  }
};
</script>

<style lang="scss" scoped>
.form-tip {
  font-size: 12px;
  color: #999999;
}

.quota-settings {
  display: flex;
  flex-direction: column;

  .radio-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    margin-top: 12px;

    .radio-content {
      display: flex;
      align-items: center;
    }
  }
}

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

.iframe-box {
  min-height: 300px;
}
</style>

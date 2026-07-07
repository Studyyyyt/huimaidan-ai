<template>
  <div style="margin-top: 20px;">
    <el-form
      ref="timeForm"
      :model="costForm"
      :rules="timeRules"
      label-width="140px"
    >
      <el-form-item label="配送员提成：" class="time-title-label" v-if="basicForm.mer_delivery_type.includes(0)">
        <div class="commission-rate-box">
          <el-input-number v-model="costForm.commission_rate" :min="0" :max="100" :step="0.01" :controls="false" :precision="2" />
        </div>
        <span class="remarks">
          支持两位小数，配送人员提成根据配送费用比例进行计算，包邮仍按照原配送费用给配送员进行结算。
        </span>
      </el-form-item>

      <!-- 配送时间类型 -->
      <el-form-item label="配送时间：" class="time-title-label">
        <el-radio-group v-model="costForm.delivery_time_type">
          <el-radio :label="1">买家可选定时送达</el-radio>
          <el-radio :label="2">统一尽快送达</el-radio>
        </el-radio-group>
        <br />
        <span class="remarks" v-show="costForm.delivery_time_type == 1">
          选中买家可选定时送达，买家下单选择同城配送时，需要选择送达时间，商家按约定时间送达。
        </span>
      </el-form-item>
      <!-- 尽快送达用户端文案 -->
      <el-form-item
        label="尽快送达用户端文案"
        class="time-item"
        prop="delivery_prompt"
        v-if="costForm.delivery_time_type == 2"
      >
        <el-input
          type="text"
          class="time-input"
          placeholder="请输入内容"
          v-model="costForm.delivery_prompt"
          maxlength="10"
          show-word-limit
        >
        </el-input>
      </el-form-item>
      <!-- 用户端可选定天数 -->
      <el-form-item
        label="用户端可选定天数"
        class="time-item"
        prop="selectable_days"
        v-if="costForm.delivery_time_type == 1"
      >
        <el-input
          type="text"
          class="time-input"
          v-model="costForm.selectable_days"
        >
        </el-input>
        <br />
        <span class="remarks"
          >用户可预约未来几天的时间送达，填写1代表用户只能预约今天送达。</span
        >
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

export default {
  name: "DeliveryTime",
  props: {
    basicForm: {
      type: Object,
      default: null
    },
    costForm: {
      type: Object,
      default: null
    }
  },
  computed: {},
  data() {
    return {
      timeRules: {
        delivery_prompt: [
          {
            required: true,
            message: `请输入尽快送达用户端文案`,
            trigger: "blur"
          }
        ],
        selectable_days: [
          {
            required: true,
            message: `请输入用户端可选定天数`,
            trigger: "blur"
          },
          { pattern: /^[0-9]*$/, message: "天数必须为数字" }
        ]
      }
    };
  },
  methods: {
    // 提交配送设置
    submit() {
      let isValid = true
      this.$refs.timeForm.validate((valid, errData) => {
        if (valid) {
          isValid = true
        } else {
          for (var item in errData) {
            this.$message({
              type: 'error',
              message: errData[item][0].message
            })
            isValid = false
            break
          }
        }
      })
      return isValid
    }
  }
};
</script>

<style scoped lang="scss">
@import "@/styles/form.scss";
.remarks {
  color: #a4a4a4;
  font-size: 12px;
}
.time-item {
  margin-left: 140px;
  .time-input {
    width: 500px;
  }
}
.mb-22 {
  margin-bottom: 22px;
}

.commission-rate-box {
  width: fit-content;
  position: relative;

  &::after {
    content: '%';
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
  }
}
</style>

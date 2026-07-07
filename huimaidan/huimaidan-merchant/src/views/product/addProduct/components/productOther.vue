<template>
  <el-row>
    <el-col>
      <el-form-item label="支持退款：">
        <el-switch
          v-model="formValidate.refund_switch"
          :active-value="1"
          :inactive-value="0"
          :width="55"
          active-text="开启"
          inactive-text="关闭"
        />
      </el-form-item>
    </el-col>
    <el-col v-if="deliveryList.length > 0" :span="24">
      <el-form-item label="送货方式：" prop="delivery_way">
        <div class="acea-row">
          <el-checkbox-group v-model="formValidate.delivery_way">
            <el-checkbox
              v-for="item in deliveryList"
              :key="item.value"
              :label="item.value"
            >
              {{ item.name }}
            </el-checkbox>
          </el-checkbox-group>
        </div>
      </el-form-item>
    </el-col>

    <el-col
      v-if="
      formValidate.delivery_way.includes('2') &&
      formValidate.type == 0
      "
      :span="24"
    >
      <el-form-item label="是否包邮：">
        <el-radio-group v-model="formValidate.delivery_free">
          <el-radio :label="0" class="radio">否</el-radio>
          <el-radio :label="1">是</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
    <el-col
      v-if="
        formValidate.delivery_free == 0 &&
        formValidate.delivery_way.includes('2') &&
        formValidate.type == 0
      "
      :span="24"
    >
      <el-form-item label="运费模板：" prop="temp_id" size="small">
        <div class="acea-row">
          <el-select
            v-model="formValidate.temp_id"
            size="small"
            placeholder="请选择"
            class="pageWidth"
          >
            <el-option
              v-for="item in shippingList"
              :key="item.shipping_template_id"
              :label="item.name"
              :value="item.shipping_template_id"
            />
          </el-select>
          <el-button class="ml15" size="small" @click="addTem"
            >添加运费模板</el-button
          >
        </div>
      </el-form-item>
    </el-col>
    <el-col v-if="formValidate.type != 3" :span="24">
      <el-col>
        <el-form-item label="最少购买件数：">
          <el-input-number
            v-model="formValidate.once_min_count"
            :min="0"
            size="small"
            controls-position="right"
            placeholder="请输入购买件数"
          />
          &nbsp;&nbsp;<span class="explanation">默认为0，则不限制购买件数</span>
        </el-form-item>
      </el-col>
    </el-col>
    <el-col v-if="formValidate.type != 3" :span="24">
      <el-form-item label="限购类型：">
        <el-radio-group v-model="formValidate.pay_limit">
          <el-radio :label="0" class="radio">不限购</el-radio>
          <el-radio :label="1">单次限购</el-radio>
          <el-radio :label="2">长期限购</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-col>
    <el-col v-if="formValidate.pay_limit != 0" :span="24">
      <el-col>
        <el-form-item label="限购数量：" prop="once_max_count">
          <el-input-number
            v-model="formValidate.once_max_count"
            :min="formValidate.once_min_count"
            size="small"
            controls-position="right"
            placeholder="请输入购买件数"
          />
          &nbsp;&nbsp;单次限购是限制每次下单最多购买的数量，长期限购是限制一个用户总共可以购买的数量
        </el-form-item>
      </el-col>
    </el-col>
    <el-col :span="24">
      <el-col v-bind="grid">
        <el-form-item label="排序：">
          <el-input-number
            v-model="formValidate.sort"
            controls-position="right"
            placeholder="请输入排序"
            size="small"
          />
        </el-form-item>
      </el-col>
    </el-col>
    <el-col :span="24">
      <el-form-item label="平台保障服务：" size="mini">
        <div class="acea-row">
          <el-select
            v-model="formValidate.guarantee_template_id"
            placeholder="请选择"
            clearable
            size="small"
            class="pageWidth"
          >
            <el-option
              v-for="item in guaranteeList"
              :key="item.guarantee_template_id"
              :label="item.template_name"
              :value="item.guarantee_template_id"
            />
          </el-select>
          <el-button class="ml15" size="small" @click="addServiceTem"
            >添加服务说明模板</el-button
          >
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="24">
      <el-form-item label="关联系统表单：" size="mini">
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
          <el-button class="ml15 mr14" size="small" @click="getFormList"
            >刷新</el-button
          >
          <router-link
            class="link"
            :to="{ path: roterPre + '/systemForm/form_create?id=0' }"
            target="_blank"
          >
            <el-button size="small">添加系统表单</el-button>
          </router-link>
        </div>
        <div class="explanation">
          注：添加系统表单后，商品不可加入购物车，添加系统表单，请前往：装修>系统表单
        </div>
      </el-form-item>
    </el-col>
    <el-col :span="16">
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
    </el-col>
  </el-row>
</template>

<script>
export default {
  name: "ProductOtherSettings",
  props: {
    formValidate: {
      type: Object,
      required: true
    },
    deliveryList: {
      type: Array,
      default: () => []
    },
    shippingList: {
      type: Array,
      default: () => []
    },
    guaranteeList: {
      type: Array,
      default: () => []
    },
    formList: {
      type: Array,
      default: () => []
    },
    formUrl: {
      type: String,
      default: ""
    },
    roterPre: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24
      }
    };
  },
  methods: {
    addTem() {
      this.$emit("addTem");
    },
    addServiceTem() {
      this.$emit("addServiceTem");
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

<style scoped lang="scss">
.explanation {
  color: #909399;
}
.iframe-box {
  min-height: 300px;
}
.pageWidth {
  width: 460px;
}
.ml15 {
  margin-left: 15px;
}
</style>

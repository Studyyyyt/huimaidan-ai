<!-- 区域设置 - 提成规则表格 -->
<template>
  <div class="commission-rules-wrapper">
    <p class="remarks">
      提示：关联店铺的订单提成需在 3 级代理间按 “层级比例” <span class="red">递减</span>分配，分 3 类订单情况：
    </p>
    <el-table :data="rulesIntro" size="small">
      <el-table-column prop="orderType" label="订单类型" width="200" />
      <el-table-column prop="calcLogic" label="提成计算逻辑">
        <template #default="{ row }">
          <p class="rule-item rule-tips">{{ row.calcLogicTips }}</p>
          <p class="rule-item" v-for="item of row.calcLogicList" :key="item">{{ item }}</p>
        </template>
      </el-table-column>
      <el-table-column prop="example" label="示例（假设：省代总提成比例 8%，市代提成比例 5%，区代提成 3%，平台抽成 10 万元" width="340">
        <template #default="{ row }">
          <p class="rule-item" v-for="item of row.exampleList" :key="item">{{ item }}</p>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script>

const rulesIntro = [
  {
    orderType: "1. 三级区域关联店铺的订单",
    calcLogicTips: "三级代理拿 “自身层级提成”，一二级代理拿 “上下级差价提成”",
    calcLogicList: [
      "省代提成 = 平台抽成 × (省代总比例 - 市代比例)",
      "市代提成 = 平台抽成 × (市代比例- 区代比例)",
      "区代提成 = 平台抽成 × 市代比例",
    ],
    exampleList: [
      "省代提成 = 10万 × (8% - 5%)=3000元",
      "市代提成 = 10万 × (5% - 3%)=2000元",
      "区代提成 = 10万 × 3%=3000元",
    ]
  },
  {
    orderType: "2. 二级区域关联店铺的订单",
    calcLogicTips: "二级代理拿 “自身层级提成”，一级代理拿 “上下级差价提成”",
    calcLogicList: [
      "省代提成 = 平台抽成 × (省代总比例 - 市代比例)",
      "市代提成 = 平台抽成 × 市代比例",
    ],
    exampleList: [
      "省代提成 = 10万 × (8% - 5%)=3000元",
      "市代提成 = 10万 × 5%=5000元",
    ]
  },
  {
    orderType: "3. 一级区域关联店铺的订单",
    calcLogicTips: "一级代理拿区域代理全级提成",
    calcLogicList: [
      "省代提成 = 平台抽成 × 省代总比例",
    ],
    exampleList: [
      "省代提成 = 10万 × 8%=8000元",
    ]
  }
];

export default {
  name: 'zoneCommissionTable',
  data() {
    return {
      rulesIntro
    }
  }
}
</script>

<style lang="scss" scoped>
.commission-rules-wrapper {
  background-color: #f8f8f8;
  border-radius: 6px;
  padding: 10px;
  max-width: 1000px;
}

.remarks {
  font-size: 12px;
  color: #909399;
  margin-bottom: 10px;

  .red {
    color: #ED4014;
  }
}

.rule-item {
  font-size: 13px;
  color: #303133;

  &.rule-tips {
    color: #909399;
  }
}
</style>
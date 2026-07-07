<template>
  <div class="app-container huimaidan-entry">
    <el-card shadow="never">
      <div slot="header" class="clearfix">
        <span>{{ pageTitle }}</span>
      </div>
      <el-alert
        title="惠买单商户端入口已接入"
        type="success"
        :closable="false"
        show-icon
      />
      <div class="entry-grid">
        <el-card v-for="item in entries" :key="item.key" shadow="hover" class="entry-card">
          <h3>{{ item.title }}</h3>
          <p>{{ item.desc }}</p>
          <el-tag size="small" type="info">{{ item.api }}</el-tag>
        </el-card>
      </div>
      <el-alert
        title="当前阶段先接通商户后台菜单和后端权限。完整表格、编辑、流水页面可在下一阶段按这些入口逐个补齐。"
        type="info"
        :closable="false"
      />
    </el-card>
  </div>
</template>

<script>
const entryMap = {
  discount: {
    title: '优惠规则',
    desc: '商户维护自己的惠买单折扣规则，包含消费折扣、启停状态和有效期。',
    api: '/merchant/huimaidan/discount'
  },
  pool: {
    title: '垫资池',
    desc: '查看垫资池余额、预警、扣减流水，适用于垫资池结算模式商户。',
    api: '/merchant/huimaidan/pool'
  },
  settlement: {
    title: '结算订单',
    desc: '查看惠买单订单、收款趋势、提现概览和提现记录。',
    api: '/merchant/huimaidan/settlement'
  },
  finance: {
    title: '财务概览',
    desc: '查看商户余额、销售额度和余额明细。',
    api: '/merchant/huimaidan/finance'
  }
}

export default {
  name: 'MerchantHuimaidanEntry',
  computed: {
    current() {
      return entryMap[this.$route.meta.section] || entryMap.discount
    },
    pageTitle() {
      return this.current.title
    },
    entries() {
      return Object.keys(entryMap).map(key => ({ key, ...entryMap[key] }))
    }
  }
}
</script>

<style scoped>
.huimaidan-entry .entry-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin: 18px 0;
}
.entry-card h3 {
  margin: 0 0 8px;
  font-size: 16px;
  color: #303133;
}
.entry-card p {
  min-height: 44px;
  color: #606266;
  line-height: 1.6;
}
</style>

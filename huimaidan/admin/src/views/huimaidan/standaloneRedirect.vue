<template>
  <div class="huimaidan-redirect">
    <p>正在打开惠买单管理页...</p>
  </div>
</template>

<script>
const tabMap = {
  guide: 'guide',
  import: 'merchant',
  discounts: 'discounts',
  merchantTags: 'merchant_tags',
  tags: 'tags',
  banners: 'banners',
  configs: 'configs',
  logs: 'logs'
}

export default {
  name: 'HuimaidanStandaloneRedirect',
  props: {
    tab: {
      type: String,
      default: ''
    }
  },
  mounted() {
    const tab = tabMap[this.tab] || tabMap[this.$route.params.tab] || this.$route.query.tab || 'merchant'
    window.location.href = `/huimaidan_ai_admin.html?tab=${encodeURIComponent(tab)}`
  }
}
</script>

<style scoped>
.huimaidan-redirect {
  padding: 24px;
  color: #606266;
}
</style>

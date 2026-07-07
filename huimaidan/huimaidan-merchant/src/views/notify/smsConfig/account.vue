<template>
    <div>
        <iframe :src="`https://api.crmeb.com/?app_referer=${app_referer}`" frameborder="0" height="100%" width="100%" id="iframe"></iframe>
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
import { getConfigApi } from '@/api/systemForm'
export default {
    name: 'smsAccount',
    components: {  },
    data () {
        return {
            app_referer: ""
        }
    },
    created () {
        this.getConfigData();
    },
    methods: {
        getConfigData() {
            getConfigApi().then(res => {
                this.app_referer = encodeURIComponent(btoa(res.data.app_referer));
            })
            .catch(res => {
                this.$message.error(res.message)
            })
        },
    }
}
</script>

<style lang="scss" scoped>
    #iframe{
        min-height: 80vh;
    }

</style>

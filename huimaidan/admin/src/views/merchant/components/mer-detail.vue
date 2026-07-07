<!-- 商户详情侧滑抽屉 -->
<template>
  <el-drawer :with-header="false" :size="1000" :visible.sync="visible" :before-close="handleClose">
    <div class="min100vh" v-loading="loading">
      <div v-if="orgDetail">
        <div class="head">
          <div class="full">
            <img class="order_icon" src="@/assets/images/u101.png" />
            <div class="text">
              <div class="title">
                {{ orgDetail.name }}
                <el-tag size="mini" :style="getOrgTypeStyle(orgDetail.type)" v-if="ORG_TYPE_META[orgDetail.type]" class="ml10">
                  {{ ORG_TYPE_META[orgDetail.type].label }}
                </el-tag>
              </div>
              <div class="acea-row">ID: {{ orgDetail.circle_id }}</div>
            </div>
          </div>
        </div>

        <el-tabs type="border-card" v-model="activeTab" class="zone-detail-tabs">
          <el-tab-pane v-for="tab in TABS" :key="tab.value" :label="tab.label" :name="tab.value"></el-tab-pane>
          <div class="section" v-show="activeTab === TAB_NAME.BASIC_INFO">
            <ul class="list column">
              <li class="item">
                <div>商户名称：</div>
                <div class="value">{{ orgDetail.name }}</div>
              </li>
              <li class="item">
                <div>{{ detailMeta.adminLabel }}：</div>
                <div class="value">{{ orgDetail.circleAgent ? orgDetail.circleAgent.name : '--' }}</div>
              </li>
              <li class="item">
                <div>开启状态：</div>
                <div class="value">{{ orgDetail.status ? '开启' : '关闭' }}</div>
              </li>
            </ul>
          </div>
          <div class="section" v-show="activeTab === TAB_NAME.RELATED_MERCHANT">
            <el-form :model="searchForm" label-width="65px" inline size="small"
              @submit.native.prevent="handleGetMerList">
              <el-form-item label="关键字：">
                <el-input v-model="searchForm.keyword" placeholder="请输入店铺名称、联系人搜索" @change="handleGetMerList"
                  style="width: 200px;" />
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="handleGetMerList">搜索</el-button>
                <el-button @click="handleResetMerSearchForm">重置</el-button>
              </el-form-item>
            </el-form>

            <el-table :data="merList" v-loading="merLoadOptions.loading">
              <el-table-column label="店铺名称" prop="mer_name" />
              <el-table-column label="所属商户">
                <template #default>
                  {{ orgDetail.name }}
                </template>
              </el-table-column>
              <el-table-column label="联系人" prop="real_name" />
              <el-table-column label="联系电话" prop="mer_phone" />
            </el-table>

            <el-pagination :current-page="merLoadOptions.page" :page-size="merLoadOptions.limit"
              :total="merLoadOptions.total" @current-change="handleCurrentChange" @size-change="handleSizeChange" />
          </div>
        </el-tabs>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { getBusinessZoneDetailApi, getZoneRelatedMerchantListApi } from '@/api/business-zone';
import { ORG_TYPE } from '@/domain/organization/org.enum';
import { ORG_TYPE_META } from '@/domain/organization/org.meta';
import { getOrgTypeStyle } from '@/domain/organization/org.utils';

const TAB_NAME = {
  BASIC_INFO: 'basicInfo',
  RELATED_MERCHANT: 'relatedMerchant',
}

const TABS = [
  {
    label: '基础信息',
    value: TAB_NAME.BASIC_INFO
  },
  {
    label: '关联店铺',
    value: TAB_NAME.RELATED_MERCHANT
  }
];

export default {
  name: 'orgDetail',
  props: {
    visible: Boolean,
    merId: {
      type: Number,
      default: null
    }
  },
  data() {
    return {
      ORG_TYPE_META,
      orgDetail: null,
      loading: false,

      TAB_NAME,
      TABS,
      activeTab: TAB_NAME.BASIC_INFO,

      searchForm: {
        keyword: "", // 关键字
        page: 1, // 页码
        limit: 10, // 每页条数
      },
      merList: [],
      merLoadOptions: {
        loading: false,
        total: 0,
      }
    }
  },
  computed: {
    isZone() {
      return this.orgDetail && this.orgDetail.type === ORG_TYPE.ZONE;
    },
    parentOrgName() {
      if (!this.orgDetail) return;
      if (!this.orgDetail.parent) {
        return '--';
      }
      let parentNameList = [];
      let parentOrg = this.orgDetail.parent;

      while (parentOrg) {
        parentNameList.push(parentOrg.name);
        parentOrg = parentOrg.parent;
      }

      return parentNameList.reverse().join(' / ');
    },
    detailMeta() {
      const zoneMeta = {
        adminLabel: `区域代理`
      };
      const merchantMeta = {
        adminLabel: `商户管理`
      };

      return this.isZone ? zoneMeta : merchantMeta;
    }
  },
  watch: {
    visible(visible) {
      if (visible) {
        this.merId && this.handleGetZoneDetail(this.merId);
        this.handleResetMerSearchForm();
      } else {
        setTimeout(() => {
          this.orgDetail = null;
          this.activeTab = TAB_NAME.BASIC_INFO;
        }, 300);
      }
    }
  },
  methods: {
    getOrgTypeStyle(orgType) {
      return getOrgTypeStyle(orgType);
    },
    handleSizeChange(size) {
      this.merLoadOptions.limit = size;
      this.handleGetMerList();
    },
    handleCurrentChange(page) {
      this.merLoadOptions.page = page;
      this.handleGetMerList();
    },
    handleResetMerSearchForm() {
      this.searchForm = {
        keyword: "",
        page: 1,
        limit: 10,
      };
      this.handleGetMerList();
    },
    async handleGetMerList() {
      if (this.merLoadOptions.loading) return;
      this.merLoadOptions.loading = true;
      try {
        const res = await getZoneRelatedMerchantListApi(this.merId, this.searchForm);
        this.merList = res.data.list;
        this.merLoadOptions.total = res.data.count;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.merLoadOptions.loading = false;
      }
    },
    async handleGetZoneDetail() {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await getBusinessZoneDetailApi(this.merId);
        this.orgDetail = res.data;
      } catch (error) {
        this.$message.error(error.message);
      } finally {
        this.loading = false;
      }
    },
    handleClose() {
      this.$emit('close');
    }
  }
}
</script>

<style scoped lang="scss">
.zone-detail-tabs {
  box-shadow: none;
}

.head {
  padding: 20px 15px;

  .full {
    display: flex;
    align-items: center;

    .order_icon {
      width: 60px;
      height: 60px;
      border-radius: 100%;
    }

    .iconfont {
      color: var(--prev-color-primary);

      &.sale-after {
        color: #90add5;
      }
    }

    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;

      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        color: rgba(0, 0, 0, 0.85);
      }
    }
  }
}

.section {
  // padding: 20px 0 8px;
  // border-bottom: 1px dashed #eeeeee;

  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }

  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;

    &.column {
      flex-direction: column;

      .item {
        padding: 0 !important;
        flex: none;
        width: 100%;
      }
    }
  }

  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    line-height: 18px;
    align-items: center;

    &:nth-child(3n + 1) {
      padding-right: 20px;
    }

    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }

    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
  }

  .label {
    width: 5em;
    text-align: right;
  }

  .value {
    flex: 1;

    .image {
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
</style>

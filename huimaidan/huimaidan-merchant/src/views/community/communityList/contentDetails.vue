<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1000px"
      direction="rtl"
      :before-close="handleClose"
    >
      <div v-loading="loading" class="pl20">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">
                内容详情
              </div>

              <div>
                <span class="mr20">内容ID：{{ info.community_id }}</span>
              </div>
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">审核状态</div>

              <div class="value1">
                <span v-if="info.status == 0" class=" col1">待审核</span>
                <span v-else-if="info.status == 1" class=" col2">审核通过</span>
                <span v-else class=" col3">审核未通过</span>
              </div>
            </li>
            <li class="item">
              <div class="title">发布时间</div>
              <div>
                {{ info.create_time }}
              </div>
            </li>

            <li class="item" v-if="info.status == '-1'">
              <div class="title">
                拒绝原因
              </div>
              <div>{{ info.refusal || "--" }}</div>
            </li>
          </ul>
        </div>

        <!-- tab -->
        <el-tabs type="border-card" v-model="activeName">
          <el-tab-pane label="发布内容" name="detail">
            <div class="section">
              <div class="title">内容信息</div>
              <ul class="list">
                <li class="item item50">
                  <div class="item-title">作者：</div>
                  <div class="flex value">
                    {{ info.author ? info.author.nickname : "--" }}
                  </div>
                </li>
                <li class="item item50">
                  <div class="item-title">作者ID：</div>
                  <div class=" value">
                    {{ info.author ? info.author.uid : "--" }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">
                    {{ info.is_type == 1 ? "内容图片：" : "视频封面：" }}
                  </div>
                  <div class="flex">
                    <div v-for="item in filterImgList(info)">
                      <el-image
                        style="width: 60px;height: 60px;margin-right: 10px"
                        :src="item"
                        :preview-src-list="[item]"
                      />
                    </div>
                  </div>
                </li>
                <li class="item item100" v-if="info.is_type == 2">
                  <div class="item-title">视频内容：</div>
                  <video
                    style="width:300px;height: 200px!important;border-radius: 10px;"
                    :src="info.video_link"
                    controls="controls"
                  >
                    您的浏览器不支持 video 标签。
                  </video>
                </li>
                <li class="item item100">
                  <div class="item-title">文章内容：</div>
                  <div class="value">
                    {{ info.content || "--" }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">参与话题：</div>
                  <div class="cate-name" v-if="info.category">
                    # {{ info.category.cate_name }}
                  </div>
                  <div v-else>--</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <div class="title">关联商品</div>
              <el-table
                ref="mainTable"
                :data="info.product"
                size="small"
                class="mt20"
                max-height="400"
              >
                <el-table-column label="ID" prop="product_id" width="55" />
                <el-table-column label="商品图" min-width="80">
                  <template slot-scope="scope">
                    <div class="demo-image__preview">
                      <el-image
                        style="width: 36px; height: 36px"
                        :src="scope.row.image"
                        :preview-src-list="[scope.row.image]"
                      />
                    </div>
                  </template>
                </el-table-column>
                <el-table-column label="商品信息" min-width="200">
                  <template slot-scope="scope">
                    <div class="row_title line2">
                      {{ scope.row.store_name }}
                    </div>
                  </template>
                </el-table-column>

                <el-table-column
                  prop="ot_price"
                  label="售价(元)"
                  min-width="100"
                />
                <el-table-column label="商品状态" min-width="100">
                  <template slot-scope="scope">
                    <span>{{ scope.row.is_show == 0 ? "下架" : "上架" }}</span>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>
          <el-tab-pane label="评论内容" name="goods">
            <el-table :data="table.list" style="width: 100%" class="mt20">
              <el-table-column prop="community_id" label="ID" width="180">
              </el-table-column>
              <el-table-column prop="name" label="用户名|ID" width="180">
                <template slot-scope="scope">
                  <div class="flex-center">
                    {{ scope.row.author.nickname }} | {{ scope.row.uid }}
                  </div>
                </template>
              </el-table-column>
              <el-table-column prop="content" label="评论内容">
              </el-table-column>
              <el-table-column prop="create_time" label="评论时间">
              </el-table-column>
            </el-table>
            <div class="block">
              <el-pagination
                :page-size="where.limit"
                :current-page="where.page"
                layout="prev, pager, next"
                :total="table.count"
                @size-change="handleSizeChangeLog"
                @current-change="pageChangeLog"
              />
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import { communityDetailApi, communityReplyApi } from "@/api/community";
export default {
  name: "",
  components: {},
  props: {},
  data() {
    return {
      drawer: false,
      loading: false,
      activeName: "detail",
      id: "",
      info: {},
      table: { count: 0, list: [] },
      where: {
        limit: 20,
        page: 1
      },
      orderImg: require("@/assets/images/icon_neirong.png")
    };
  },
  mounted() {},
  methods: {
    filterImgList(info) {
      if (info.is_type == "2") {
        return info.image.slice(0, 1);
      }
      return info.image;
    },
    openBox(id) {
      this.drawer = true;
      this.id = id;
      this.getInfo(this.id);
      this.getReply();
    },
    pageChangeLog(page) {
      this.where.page = page;
      this.getReply();
    },
    handleSizeChangeLog(val) {
      this.where.limit = val;
      this.getReply();
    },
    getReply() {
      communityReplyApi(this.id, this.where).then(res => {
        this.table = res.data;
      });
    },
    getInfo(id) {
      communityDetailApi(id).then(res => {
        this.info = res.data;
      });
    },
    handleClose() {
      this.drawer = false;
      this.activeName = "detail";
    }
  }
};
</script>
<style scoped lang="scss">
.head {
  padding: 20px 35px;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
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
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 200px;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);

      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
      .value1 {
        color: #f56022;
      }

      .value2 {
        color: #1bbe6b;
      }

      .value3 {
        color: #437ffd;
      }

      .value4 {
        color: #6a7b9d;
      }

      .value5 {
        color: #f5222d;
      }
    }
  }
}
.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .item-title {
    width: 80px;
    text-align: right;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 20px;
    font-size: 13px;
    color: #606266;
    // align-items: center;

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
    &.item100 {
      flex: 0 0 calc(100% / 1);
      padding-left: 0;
    }
    &.item50 {
      flex: 0 0 calc(50% / 1);
      padding-left: 0;
    }
  }
  .value {
    flex: 1;
    color: #333;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.flex-end {
  display: flex;
  justify-content: flex-end;
}

.col1 {
  color: #ff8d30;
}
.col2 {
  color: #377dff;
}
.col3 {
  color: #f95e45;
}
.cate-name {
  color: #377dff;
  padding: 0 10px;
  height: 28px;
  line-height: 28px;
  border: 1px solid #377dff;
  border-radius: 4px;
}
.block {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

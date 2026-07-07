<template>
  <div>
    <div class="main" v-show="showType == 0">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span>商品信息</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">顶部导航</div>
        <div class="form">
          <div class="form-item">
            <div class="form-label">菜单内容</div>
            <div class="form-value">
              <el-checkbox-group
                v-model="productCardInfo.navList"
                @change="navListChange"
              >
                <el-checkbox
                  :label="item.value"
                  v-for="(item, index) in navListBox"
                  :key="index"
                  :disabled="
                    productCardInfo.navList.length == 1 &&
                      productCardInfo.navList.includes(item.value)
                  "
                  >{{ item.label }}
                </el-checkbox>
              </el-checkbox-group>
            </div>
          </div>
          <div class="form-item">
            <div class="form-label">开启分享</div>
            <div class="form-value">
              <el-radio-group
                v-model="productCardInfo.openShare"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">商品主图</div>
        <div class="form">
          <div class="form-item">
            <div class="form-label">商品主图</div>
            <div class="form-value">
              <el-radio-group
                v-model="productCardInfo.pictureConfig"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="0">固定方图</el-radio>
                <el-radio :label="1">高度自适应</el-radio>
              </el-radio-group>
            </div>
          </div>
          <div class="form-item mt-20">
            <div class="form-label" style="margin-top: 7px;">高度设置px</div>
            <div class="form-value" style="margin-left: -14.8px">
              <el-input-number
                v-model="productCardInfo.swiperHeight"
                size="small"
                :min="750"
                class="w-270"
                controls-position="right"
              />
            </div>
          </div>
          <div class="form-item mt-20 acea-row items-center">
            <div class="form-label">轮播点</div>
            <div class="form-value ml-14">
              <el-radio-group
                v-model="productCardInfo.swiperDot"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">收藏/分享</div>
        <div class="form">
          <div class="form-item">
            <div class="form-label">是否显示</div>
            <div class="form-value">
              <el-checkbox-group v-model="productCardInfo.shareConfig">
                <draggable
                  class="dragArea list-group"
                  :list="productCardInfo.shareList"
                  group="peoples"
                  handle=".move-icon"
                >
                  <div
                    class="acea-row share-list w-270"
                    v-for="(item, index) in productCardInfo.shareList"
                    :key="index"
                  >
                    <div class="move-icon">
                      <span class="iconfont-diy icondrag"></span>
                    </div>
                    <el-checkbox
                      :label="item.value"
                      :disabled="
                        !productCardInfo.shareConfig.includes(item.value) &&
                          productCardInfo.shareConfig.length >= 2
                      "
                    >
                      {{ item.label }}</el-checkbox
                    >
                  </div>
                </draggable>
              </el-checkbox-group>
            </div>
          </div>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">商品信息</div>
        <div class="form">
          <div class="form-item">
            <div class="form-label">是否开启</div>
            <div class="form-value">
              <el-checkbox-group v-model="productCardInfo.isOpen">
                <el-checkbox
                  :label="item.value"
                  v-for="(item, index) in openShowList"
                  :key="index"
                  >{{ item.label }}
                </el-checkbox>
              </el-checkbox-group>
            </div>
          </div>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">svip会员</div>
        <div class="form">
          <div class="form-item mt-20 acea-row items-center">
            <div class="form-label">会员入口</div>
            <div class="form-value">
              <el-radio-group
                v-model="productCardInfo.showSvip"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="main" v-show="showType == 1">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span>优惠券</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">显示状态</div>
        <div class="form">
          <div class="form-item mt-20">
            <div class="form-label">是否显示</div>
            <div class="form-value ml-14">
              <el-radio-group
                v-model="productCardInfo.showCoupon"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
          <div class="form-item mt-20">
            <div class="form-value">
              <div style="color: #999;font-size: 12px; margin-left: 120px;">
                仅普通、预售商品有优惠券
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="main" v-show="showType == 2">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span>排行榜</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">显示状态</div>
        <div class="form">
          <div class="form-item mt-20">
            <div class="form-label">是否显示</div>
            <div class="form-value ml-14">
              <el-radio-group
                v-model="productCardInfo.showRank"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="main" v-show="showType == 3">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span>商品参数</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">显示状态</div>
        <div class="form">
          <div class="form-item mt-20">
            <div class="form-label">是否显示</div>
            <div class="form-value">
              <el-checkbox-group v-model="productCardInfo.showService">
                <draggable
                  class="dragArea list-group"
                  :list="productCardInfo.serviceList"
                  group="peoples"
                  handle=".move-icon"
                >
                  <div
                    class="acea-row share-list w-270"
                    v-for="(item, index) in productCardInfo.serviceList"
                    :key="index"
                  >
                    <div class="move-icon">
                      <span class="iconfont-diy icondrag"></span>
                    </div>
                    <el-checkbox :label="item.value">
                      {{ item.label }}</el-checkbox
                    >
                  </div>
                </draggable>
              </el-checkbox-group>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="main" v-if="[4, 5, 6, 8].includes(showType)">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span v-if="showType == 4">商品评价</span>
          <span v-else-if="showType == 5">优惠套餐</span>
          <span v-else-if="showType == 6">店铺信息</span>
          <span v-else-if="showType == 8">种草秀</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="title">显示状态</div>
        <div class="form">
          <div class="form-item mt-20">
            <div class="form-label">是否显示</div>
            <div class="form-value" v-if="showType == 4">
              <el-radio-group
                v-model="productCardInfo.showReply"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
            <div class="form-value" v-else-if="showType == 5">
              <el-radio-group
                v-model="productCardInfo.showMatch"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
            <div class="form-value" v-else-if="showType == 6">
              <el-radio-group
                v-model="productCardInfo.showStore"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
            <div class="form-value" v-else-if="showType == 8">
              <el-radio-group
                v-model="productCardInfo.showCommunity"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
          <div v-if="showType == 5" class="form-item flex-y-center mt-20">
            <div class="form-label">显示数量</div>
            <div class="form-value w-270 w-slider">
              <el-slider
                v-model="productCardInfo.communityNum"
                :min="1"
                :max="10"
                class="w-160 el-slider"
              ></el-slider>
              <el-input-number
                v-model="productCardInfo.communityNum"
                :min="1"
                :max="10"
                controls-position="right"
                size="small"
                class="w-88 slider-input"
              />
            </div>
          </div>
        </div>
      </div>
      <div v-if="showType == 6" class="main-content bg-fff">
        <div class="title">店铺推荐</div>
        <div class="form">
          <div class="form-item mt-20 acea-row items-center">
            <div class="form-label">是否显示</div>
            <div class="form-value">
              <el-radio-group
                v-model="productCardInfo.showRecommend"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
          <div v-if="showType == 6" class="form-item flex-y-center mt-20">
            <div class="form-label">显示数量</div>
            <div class="form-value w-270 w-slider">
              <el-slider
                v-model="productCardInfo.recommendNum"
                :min="1"
                :max="18"
                class="w-160 el-slider"
              ></el-slider>
              <el-input-number
                v-model="productCardInfo.recommendNum"
                :min="1"
                :max="18"
                controls-position="right"
                size="small"
                class="w-88 slider-input"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="main" v-show="showType == 7">
      <div class="main-header bg-fff">
        <div class="main-title">
          <span>底部菜单</span>
        </div>
      </div>
      <div class="main-content bg-fff">
        <div class="form">
          <div class="form-item mt-20">
            <div class="form-label">是否显示</div>
            <div class="form-value">
              <el-checkbox-group v-model="productCardInfo.menuList">
                <draggable
                  class="dragArea list-group"
                  :list="productCardInfo.footerList"
                  group="peoples"
                  handle=".move-icon"
                >
                  <div
                    class="acea-row share-list w-270"
                    v-for="(item, index) in productCardInfo.footerList"
                    :key="index"
                  >
                    <div class="move-icon">
                      <span class="iconfont-diy icondrag"></span>
                    </div>
                    <el-checkbox
                      :label="item.value"
                      :disabled="
                        (!productCardInfo.menuList.includes(item.value) &&
                          productCardInfo.menuList.length >= 3) ||
                          (productCardInfo.menuList.length == 1 &&
                            productCardInfo.menuList.includes(item.value))
                      "
                    >
                      {{ item.label }}</el-checkbox
                    >
                  </div>
                </draggable>
              </el-checkbox-group>
            </div>
          </div>
          <div class="form-item mt-20">
            <div class="form-label">购物车按钮</div>
            <div class="form-value">
              <el-radio-group
                v-model="productCardInfo.showCart"
                :true-value="1"
                :false-value="0"
              >
                <el-radio :label="1">显示</el-radio>
                <el-radio :label="0">隐藏</el-radio>
              </el-radio-group>
            </div>
          </div>
          <div class="form-item mt-20">
            <div class="form-value">
              <div style="color: #999;font-size: 12px; margin-left: 120px;">
                活动商品不可加入购物车，此按钮不生效
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import vuedraggable from "vuedraggable";
export default {
  props: {
    showType: {
      type: Number,
      default: 0
    },
    productCardInfo: {
      type: Object,
      default: () => {}
    }
  },
  components: {
    draggable: vuedraggable
  },
  data() {
    return {
      navListBox: [
        { label: "首页", value: 0 },
        { label: "搜索", value: 1 },
        { label: "购物车", value: 2 },
        { label: "我的收藏", value: 3 },
        { label: "个人中心", value: 4 }
      ],
      openShowList: [
        { label: "划线价", value: 0 },
        { label: "库存", value: 2 },
        { label: "已售", value: 1 }
      ]
    };
  },
  methods: {
    navListChange(e) {
      if (e.length == 0) {
        return this.$message.warning("该导航请至少选择一个");
      }
    }
  }
};
</script>

<style scoped lang="scss">
.bg-fff {
  background-color: #fff;
}
.share-list {
  height: 36px;
  background: #f9f9f9;
  border-radius: 3px;
  margin-bottom: 6px;
  line-height: 36px;
  .icondrag {
    font-size: 24px;
    margin-right: 18px;
    color: #ddd;
  }
}
.el-checkbox {
  margin-bottom: 15px;
}
.main {
  .main-header {
    font-size: 16px;
    padding: 20px 15px;
    margin: 6px 0 0;
    font-weight: 500;
    color: #333333;
    line-height: 16px;
    border-bottom: 1px solid #ebedf1;
  }
  .main-content {
    padding: 20px 15px;
    ~ .main-content {
      border-top: 6px solid #f0f2f5;
    }
    .title {
      font-size: 14px;
      font-weight: 400;
      color: #333333;
      line-height: 14px;
      margin-bottom: 20px;
    }

    .form {
      .form-item:last-child {
        margin-bottom: 0;
      }

      .form-item {
        display: flex;
        font-weight: 400;
        line-height: 12px;
        .form-label {
          color: #999999;
          line-height: 17px;
          margin-right: 46px;
          white-space: nowrap;
        }

        .form-value {
          color: #666666;
          .el-slider {
            display: inline-block;
          }
          .slider-input {
            float: right;
          }
          ::v-deep .ivu-radio-wrapper {
            margin-right: 43px;
          }

          ::v-deep .ivu-checkbox-wrapper {
            margin-bottom: 22px;
            width: 84px;
            font-size: 12px;
          }
        }
      }
    }
  }
}
.width270 {
  width: 270px;
}
</style>

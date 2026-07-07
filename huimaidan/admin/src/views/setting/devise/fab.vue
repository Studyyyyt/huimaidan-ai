<template>
  <div class="users">
    <div class="content">
      <div class="left">
        <div class="phone">
          <img src="@/assets/images/fab_bg.png" alt="" />
          <div class="mask"></div>
          <template v-if="suspended_window_diy.is_show">
            <div
              class="fab-wrapper style1"
              v-if="suspended_window_diy.index === 1"
              :style="{ top: shiftings + 'px' }"
              ref="fabWrapper1"
            >
              <div
                v-for="(item, index) in suspended_window_diy.button"
                :key="index"
                class="btn"
              >
                <img :src="item.img" alt="" />
              </div>
              <div class="btn">
                <img
                  v-if="suspended_window_diy.main_ago_image"
                  :src="suspended_window_diy.main_ago_image"
                  alt=""
                />
              </div>
            </div>
            <div
              class="fab-wrapper style2"
              v-if="suspended_window_diy.index === 2"
              :style="{ top: shiftings + 'px' }"
              ref="fabWrapper2"
            >
              <div
                v-for="(item, index) in suspended_window_diy.button"
                :key="index"
                class="btn"
              >
                <img :src="item.img" alt="" />
              </div>
              <span class="iconfont iconjinru"></span>
            </div>
            <div
              class="fab-wrapper style3"
              v-if="suspended_window_diy.index === 3"
              :style="{ top: shiftings + 'px' }"
              ref="fabWrapper3"
            >
              <div class="inner">
                <div>
                  <div
                    v-for="(item, index) in suspended_window_diy.button"
                    :key="index"
                    :class="`btn${suspended_window_diy.button.length}`"
                  >
                    <img v-if="item.img" :src="item.img" alt="" />
                  </div>
                </div>
                <div class="main-btn">
                  <img
                    v-if="suspended_window_diy.main_ago_image"
                    :src="suspended_window_diy.main_ago_image"
                    class="img"
                  />
                </div>
              </div>
            </div>
            <div
              class="fab-wrapper style4"
              v-if="suspended_window_diy.index === 4"
              :style="{ top: shiftings + 'px' }"
              ref="fabWrapper4"
            >
              <div class="inner">
                <div>
                  <div
                    v-for="(item, index) in suspended_window_diy.button"
                    :key="index"
                    class="btn"
                  >
                    <img v-if="item.img" :src="item.img" alt="" />
                  </div>
                </div>
                <div class="main-btn acea-row row-center-wrapper">
                  <span class="iconfont iconguanbi fs-18 text--w111-333"></span>
                </div>
              </div>
            </div>
          </template>
          
        </div>
      </div>
      <div class="right" :class="{'right0' : tagsViewList.length<=0}">
        <div class="main">
          <div class="main-header">
            <div class="main-title">
              <span>悬浮按钮</span>
            </div>
          </div>
          <div class="main-content">
            <div class="title">按钮设置</div>
            <div class="form">
              <div class="form-item acea-row mt-20">
                <div class="form-label">是否显示</div>
                <div class="form-value">
                  <el-radio-group v-model="suspended_window_diy.is_show">
                    <el-radio :label="1">显示</el-radio>
                    <el-radio :label="0">隐藏</el-radio>
                  </el-radio-group>
                </div>
              </div>
              <div class="form-item acea-row mt-20">
                <div class="form-label">选择风格</div>
                <div class="form-value">
                  <el-radio-group v-model="suspended_window_diy.index">
                    <el-radio :label="1">横排展开</el-radio>
                    <el-radio :label="2">竖列展开</el-radio>
                    <el-radio :label="3">扇形展开</el-radio>
                    <el-radio :label="4">弓状展开</el-radio>
                  </el-radio-group>
                </div>
              </div>
            </div>
          </div>
          <div class="main-content">
            <div class="title">位置设置</div>
            <div class="form">
              <div class="form-item acea-row mt-20" style="align-items: center">
                <div class="form-label">上下偏移</div>
                <div class="form-value">
                  <el-slider
                    v-model="suspended_window_diy.shifting"
                    show-input
                    @input="sliderInput($event)"
                  ></el-slider>
                </div>
              </div>
            </div>
          </div>
          <div class="main-content" v-if="suspended_window_diy.index !== 4">
            <div class="title">主按钮设置</div>
            <div class="form">
              <div
                class="form-item acea-row row-middle mt-20"
                v-if="suspended_window_diy.index != 3"
              >
                <div class="form-label">上传图片</div>
                <div class="form-value">
                  <div class="picTxt">
                    <div class="box" @click="modalPicTap('main_ago_image')">
                      <div
                        class="pictrue acea-row row-center-wrapper"
                        v-if="suspended_window_diy.main_ago_image"
                      >
                        <img
                          :src="suspended_window_diy.main_ago_image"
                          alt=""
                        />
                      </div>
                      <div class="upload-box" v-else>
                        <span class="el-icon-plus"></span>
                      </div>
                    </div>
                    <div class="tip">建议尺寸：70*70px</div>
                  </div>
                </div>
              </div>
              <div class="mt-20" v-if="suspended_window_diy.index == 3">
                <div class="tip" style="padding: 0 0 20px">
                  建议尺寸：70*70px
                </div>
                <div class="acea-row">
                  <div
                    class="form-item acea-row row-column mr20"
                    v-if="suspended_window_diy.index == 3"
                  >
                    <div class="form-value mb10">
                      <div class="picTxt">
                        <div class="box" @click="modalPicTap('main_ago_image')">
                          <div
                            class="pictrue acea-row row-center-wrapper"
                            v-if="suspended_window_diy.main_ago_image"
                          >
                            <img
                              :src="suspended_window_diy.main_ago_image"
                              alt=""
                            />
                          </div>
                          <div class="upload-box" v-else>
                            <span class="el-icon-plus"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-label flex-center" style="width: 64px">
                      展开前
                    </div>
                  </div>
                  <div
                    class="form-item acea-row row-column"
                    v-if="suspended_window_diy.index == 3"
                  >
                    <div class="form-value mb10">
                      <div class="picTxt">
                        <div
                          class="box"
                          @click="modalPicTap('main_after_image')"
                        >
                          <div
                            class="pictrue acea-row row-center-wrapper"
                            v-if="suspended_window_diy.main_after_image"
                          >
                            <img
                              :src="suspended_window_diy.main_after_image"
                              alt=""
                            />
                          </div>
                          <div class="upload-box" v-else>
                            <span class="el-icon-plus"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-label flex-center" style="width: 64px">
                      展开后
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="main-content">
            <div class="title">子按钮设置</div>
            <div class="form">
              <div class="tip">最多添加五个，建议尺寸70*70px</div>
              <draggable
                class="dragArea list-group"
                :list="suspended_window_diy.button"
                group="peoples"
                handle=".iconfont"
              >
                <div
                  class="box-item"
                  v-for="(item, index) in suspended_window_diy.button"
                  :key="index"
                >
                  <div class="left-tool">
                    <span class="iconfont iconxingzhuangjiehe"></span>
                  </div>
                  <div class="right-wrapper">
                    <div class="acea-row row-middle cell">
                      <div class="item-title">图片</div>
                      <div class="img-wrapper">
                        <div
                          class="img-item"
                          @click="modalPicTap('button', index)"
                        >
                          <div class="pictrue" v-if="item.img">
                            <img :src="item.img" alt="" />
                          </div>
                          <div class="empty-img" v-else>
                            <span class="iconfont iconjiahao"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="acea-row row-middle cell">
                      <div class="item-title">链接</div>
                      <div class="slider-box">
                        <div>
                          <el-input v-model="item.url" placeholder="请输入链接">
                            <el-button
                              slot="append"
                              icon="el-icon-arrow-right"
                              @click="getLink(index)"
                            ></el-button>
                          </el-input>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div
                    v-if="
                      suspended_window_diy.button.length > 3 ||
                        suspended_window_diy.index == 1 ||
                        suspended_window_diy.index == 2
                    "
                    class="del-box"
                    @click="deleteMenu(index)"
                  >
                    <span class="iconfont iconcha"></span>
                  </div>
                </div>
              </draggable>
              <el-button
                class="add-btn"
                @click="addMenu"
                v-if="suspended_window_diy.button.length < 5"
                >+ 添加</el-button
              >
            </div>
          </div>
        </div>
      </div>
    </div>
    <el-card :bordered="false" class="fixed-card">
      <div class="acea-row row-center-wrapper">
        <el-button class="bnt" type="primary" @click="onSubmit">保存</el-button>
      </div>
    </el-card>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import linkaddress from "@/components/linkaddress";
import { getSuspendedDiy, saveSuspendedDiy } from "@/api/diy.js";
import { mapState } from "vuex";
export default {
  components: {
    draggable,
    linkaddress
  },
  data() {
    return {
      sideBar1:
        window.localStorage.getItem("sidebarStyle") == "a" ? false : true,
      suspended_window_diy: {
        is_show: 1, // 是否显示
        index: 1, // 样式1
        shifting: 0, // 位置
        main_image: "", // 主按钮图
        button: [
          {
            img: "",
            url: ""
          }
        ],
        main_ago_image: require("@/assets/images/float_btn.png"),
        main_after_image: ""
      },
      modalPic: false,
      prop: "",
      index: 0,
      shiftings: 49,
      diyId: 0
    };
  },
  computed: {
    ...mapState({
      sidebar: state => state.app.sidebar
    }),
    isCollapse() {
      return !this.sidebar.opened;
    },
    tagsViewList() {
      return this.$store.state.menu.tagNavList;
    },
  },
  watch: {
    "suspended_window_diy.index"(val) {
      this.$nextTick(() => {
        this.sliderInput(this.suspended_window_diy.shifting);
      });
    }
  },
  created() {
    this.$store.commit("settings/STOREDIY", 1);
    this.getSuspendedDiy();
  },
  methods: {
    modalPicTap(prop, index) {
      const _this = this;
      _this.index = index;
      _this.prop = prop;
      this.$modalUpload(function(img) {
        if (Array.isArray(_this.suspended_window_diy[_this.prop])) {
          _this.suspended_window_diy[_this.prop][_this.index].img = img[0];
        } else {
          _this.suspended_window_diy[_this.prop] = img[0];
        }
        _this.$forceUpdate();
      }, 1);
    },
    getLink(index) {
      this.index = index;
      this.$refs.linkaddres.modals = true;
    },
    addMenu() {
      this.suspended_window_diy.button.push({
        img: "",
        url: ""
      });
    },
    deleteMenu(index) {
      this.$modalSure("是否确定删除该按钮").then(() => {
        this.suspended_window_diy.button.splice(index, 1);
      });
    },
    linkUrl(e) {
      this.suspended_window_diy.button[this.index].url = e;
    },
    onSubmit() {
      if (this.suspended_window_diy.index === 3) {
        if (
          !this.suspended_window_diy.main_ago_image ||
          !this.suspended_window_diy.main_after_image
        ) {
          return this.$message.warning("请选择主按钮图片");
        }
      }
      if (this.suspended_window_diy.index === 4) {
        this.suspended_window_diy.main_ago_image = "";
        this.suspended_window_diy.main_ago_image = "";
        this.suspended_window_diy.main_after_image = "";
      }
      if (
        this.suspended_window_diy.index === 1 ||
        this.suspended_window_diy.index === 2
      ) {
        if (!this.suspended_window_diy.main_ago_image) {
          return this.$message.warning("请选择主按钮图片");
        }
        this.suspended_window_diy.main_after_image = "";
      }
      if (
        this.suspended_window_diy.index === 3 ||
        this.suspended_window_diy.index === 4
      ) {
        if (this.suspended_window_diy.button.length < 3) {
          return this.$message.warning("子按钮需不少于3个");
        }
      }
      for (let i = 0; i < this.suspended_window_diy.button.length; i++) {
        if (
          !this.suspended_window_diy.button[i].img ||
          !this.suspended_window_diy.button[i].url
        ) {
          return this.$message.warning("子按钮的图片或者链接不能为空");
        }
      }
      console.log(this.suspended_window_diy);

      saveSuspendedDiy(this.diyId, { value: this.suspended_window_diy })
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    getSuspendedDiy() {
      getSuspendedDiy().then(res => {
        this.diyId = res.data.id || 0;
        this.suspended_window_diy = res.data.value || this.suspended_window_diy;
      });
    },
    // 上下偏移
    sliderInput(value) {
      let num = (value / 100) * 667;
      let offsetHeight = this.$refs[
        `fabWrapper${this.suspended_window_diy.index}`
      ].offsetHeight;
      if (num + offsetHeight > 667) {
        this.shiftings = 667 - offsetHeight;
      } else {
        this.shiftings = num;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.content {
  min-height: 600px;
  padding-right: 400px;
  .left {
    display: flex;
    justify-content: center;
    .phone {
      position: relative;
      // margin-top: 61px;
      width: 375px;
      height: 667px;
      overflow: hidden;
      img {
        width: 100%;
        height: 100%;
      }
      .mask {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        height: auto;
        background: #333333;
        opacity: 0.5;
        z-index: 9;
      }
    }
  }
  .right {
    width: 400px;
    position: fixed;
    top: 96px;
    right: 14px;
    bottom: 78px;
    overflow-y: auto;
    background-color: #fff;
    &.right0 {
      top: 60px;
    }
    ::v-deep .main-content {
      border-top: 6px solid #f0f2f5;
    }
  }
}
.main {
  .main-header {
    padding: 20px 15px;
    background: #ffffff;
    font-weight: 500;
    font-size: 16px;
    line-height: 16px;
    color: #333333;
  }
  .main-content {
    border-top: 6px solid #f0f2f5;
    padding: 20px 15px;
    background: #ffffff;
    .title {
      font-size: 14px;
      line-height: 14px;
      color: #333333;
    }
    .form {
      .tip {
        padding: 28px 0 20px;
        font-size: 12px;
        line-height: 12px;
        color: #bbbbbb;
      }
      .form-item:last-child {
        margin-bottom: 0;
      }
      .form-item {
        font-size: 12px;
        line-height: 12px;
        .form-label {
          width: 95px;
          color: #999999;
        }
        .form-value {
          flex: 1;
          color: #666666;
          ::v-deep .ivu-radio-group {
            margin: 0 -8px -20px 0;
          }
          ::v-deep .ivu-radio-wrapper {
            width: 83px;
            margin-bottom: 20px;
          }
          ::v-deep .ivu-radio {
            margin-right: 10px;
          }
          .picTxt {
            display: flex;
            align-items: center;
            .tip {
              margin-left: 16px;
              font-size: 12px;
              color: #bbbbbb;
            }
          }
          .pictrue {
            width: 64px;
            height: 64px;
            img {
              width: 64px;
              height: 64px;
            }
          }
          .upload-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border: 1px solid #eeeeee;
            border-radius: 4px;
            .ivu-icon {
              color: #bbbbbb;
            }
          }
        }
      }
    }
  }
}
.fab-wrapper {
  position: absolute;
  right: 10px;
  z-index: 10;
  padding: 5px;
  border-radius: 23px;
  background: rgba(255, 255, 255, 0.5);
  .btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #ffffff;
    & + .btn {
      margin-top: 8px;
    }
    img {
      display: block;
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }
  }
  &.style2 {
    right: 0;
    display: flex;
    align-items: center;
    border-radius: 37px 0px 0px 37px;
    .btn {
      margin: 0 8px 0 0;
    }
    .iconfont {
      width: 14px;
      height: 14px;
      margin: 0 5px 0 0;
      font-size: 14px;
      line-height: 14px;
      text-align: center;
    }
  }
}
.box-item {
  position: relative;
  display: flex;
  padding: 14px 20px 16px 0;
  border-radius: 3px;
  margin-bottom: 20px;
  background: #f9f9f9;
  .del-box {
    position: absolute;
    right: 0;
    top: 0;
    cursor: pointer;
    transform: translate(50%, -50%);
    .iconfont {
      font-size: 16px;
      color: #cccccc;
    }
  }
}
.left-tool {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  .iconfont {
    color: #dddddd;
    font-size: 18px;
    cursor: move;
  }
}
.acea-row.row-column, .acea-row.row-column-around {
  flex-direction: column;
}
.flex-center {
  align-items: center;
}
.right-wrapper {
  flex: 1;
  .cell + .cell {
    margin-top: 13px;
  }
  .item-title {
    color: #999999;
    font-size: 12px;
    width: 40px;
  }

  .img-wrapper {
    display: flex;
    .img-item {
      width: 64px;
      height: 64px;
      margin-right: 20px;
      .name {
        color: #bbbbbb;
        font-size: 12px;
        text-align: center;
        margin-top: 7px;
      }
      .pictrue {
        width: 100%;
        height: 100%;
        cursor: pointer;
        border: 1px solid #eeeeee;
        position: relative;
        border-radius: 3px;
      }
      img {
        display: block;
        width: 100%;
        height: 100%;
      }
      .empty-img {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        width: 100%;
        height: 100%;
        font-size: 12px;
        color: #bfbfbf;
        border: 1px solid #eeeeee;
        border-radius: 3px;
        .iconfont {
          font-size: 24px;
        }
      }
      .txt {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 22px;
        line-height: 22px;
        text-align: center;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        font-size: 12px;
        border-radius: 0 0 3px 3px;
      }
    }
  }
  .slider-box {
    flex: 1;
  }
}
.add-btn {
  width: 100%;
  height: 36px;
  border-radius: 3px;
  border-color: #eeeeee;
  color: #666666;
}
.fixed-card {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
}
.style3 {
  height: 149px;
  padding: 0;
  .inner {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
  }
  .main-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0px 0px 8px 0px rgba(0, 0, 0, 0.1);
    .img {
      width: 35px;
      height: 35px;
      border-radius: 50%;
    }
  }
  img {
    border-radius: 50%;
  }
  .btn3 {
    position: absolute;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin: 0;
    background: #ffffff;
    &:nth-child(1) {
      top: -49px;
      right: 27px;
    }
    &:nth-child(2) {
      top: 0;
      right: 57px;
    }
    &:nth-child(3) {
      top: 49px;
      right: 27px;
    }
  }
  .btn4 {
    position: absolute;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin: 0;
    background: #ffffff;
    &:nth-child(1) {
      top: -56px;
      right: 6px;
    }
    &:nth-child(2) {
      top: -27px;
      right: 51px;
    }
    &:nth-child(3) {
      top: 27px;
      right: 51px;
    }
    &:nth-child(4) {
      top: 56px;
      right: 6px;
    }
  }
  .btn5 {
    position: absolute;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    margin: 0;
    background: #ffffff;
    box-shadow: 0px 0px 8px 0px rgba(0, 0, 0, 0.1);
    &:nth-child(1) {
      top: -57px;
      right: 0;
    }
    &:nth-child(2) {
      top: -42px;
      right: 44px;
    }
    &:nth-child(3) {
      top: 0;
      right: 57px;
    }
    &:nth-child(4) {
      top: 42px;
      right: 44px;
    }
    &:nth-child(5) {
      top: 57px;
      right: 0;
    }
  }
}
.style4 {
  right: -62px;
  width: 175px;
  height: 175px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  box-shadow: 0px 0px 7px 0px rgba(0, 0, 0, 0.1);
  .inner {
    position: absolute;
    top: 50%;
    right: 70px;
    transform: translateY(-50%);
  }
  .main-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0px 0px 8px 0px rgba(0, 0, 0, 0.1);
  }
  .btn {
    position: absolute;
    width: 35px;
    height: 35px;
    background: #ffffff;
    box-shadow: 0px 0px 8px 0px rgba(0, 0, 0, 0.1);
    + .btn {
      margin: 0;
    }
    &:nth-child(1) {
      top: -57px;
      right: 0;
    }
    &:nth-child(2) {
      top: -42px;
      right: 44px;
    }
    &:nth-child(3) {
      top: 0;
      right: 57px;
    }
    &:nth-child(4) {
      top: 42px;
      right: 44px;
    }
    &:nth-child(5) {
      top: 57px;
      right: 0;
    }
  }
}
.el-radio {
  margin-bottom: 20px;
}
</style>

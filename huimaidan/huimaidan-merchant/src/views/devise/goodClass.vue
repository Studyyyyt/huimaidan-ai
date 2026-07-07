<template>
  <div>
    <div class="goodClass">
      <div class="flex-1">
        <el-carousel
          ref="carousel"
          :interval="4000"
          :autoplay="false"
          :initial-index="activeIndex"
          type="card"
          trigger="click"
          height="700px"
          @change="swiperChange"
        >
          <el-carousel-item v-for="(item, index) in list1" :key="index">
            <img
              class="cate-pic"
              :class="activeIndex == index ? 'blue-border' : ''"
              :src="item.image"
            />
          </el-carousel-item>
        </el-carousel>
      </div>
      <!-- <div class="w-400"></div>
      <div class="right">
        <div class="main">
          <div class="main-header bg-fff">
            <div class="main-title">
              <span>商品分类</span>
            </div>
          </div>
          <div class="main-content bg-fff">
            <div class="title">页面设置</div>
            <div class="form">
              <div class="form-item mt-20">
                <div class="form-label">分类等级</div>
                <div class="form-value ml-14">
                  <el-radio-group v-model="level" @change="levelChange">
                    <el-radio :label="2">二级分类</el-radio>
                    <el-radio :label="3">三级分类</el-radio>
                  </el-radio-group>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> -->
    </div>
    <el-card class="fixed-card">
      <div class="acea-row row-center-wrapper">
        <el-button class="bnt" type="primary" @click="onSubmit">保存</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { saveCategoryApi, getCategoryApi } from "@/api/diy";
import { mapState } from "vuex";
export default {
  name: "goodClass",
  data() {
    return {
      sideBar1:
        window.localStorage.getItem("sidebarStyle") == "a" ? false : true,
      list1: [
        {
          image: require("@/assets/images/cateClass/cate-1.png"),
          val: "1-2"
        },
        {
          image: require("@/assets/images/cateClass/cate-2.png"),
          val: "2-2"
        },
        {
          image: require("@/assets/images/cateClass/cate-3.png"),
          val: "3-2"
        },
        {
          image: require("@/assets/images/cateClass/cate-4.png"),
          val: "2-2-1"
        },
        {
          image: require("@/assets/images/cateClass/cate-5.png"),
          val: "3-2-1"
        },
        { image: require("@/assets/images/cateClass/cate-6.png"), val: "4-2" }
      ],
      level: 2,
      activeIndex: 0,
      activeStyle: "-1",
      cateId: 0
    };
  },
  computed: {
    ...mapState({
      sidebar: state => state.app.sidebar
    }),
    isCollapse() {
      return !this.sidebar.opened;
    }
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      getCategoryApi().then(res => {
        this.cateId = res.data.id;
        this.level = res.data.value.level;
        this.$refs.carousel.setActiveItem(res.data.value.index);
      });
    },
    levelChange(e) {
      this.$set(this, "activeIndex", 0);
    },
    swiperChange(e) {
      this.activeIndex = e;
    },
    onSubmit() {
      saveCategoryApi(this.cateId, { value: { index: this.activeIndex } })
        .then(res => {
          this.$message.success(res.message);
        })
        .catch(err => {
          this.$message.error(err.message);
        });
    }
  }
};
</script>
<style scoped lang="scss">
.cate-pic {
  width: 300px;
}
.goodClass {
  margin-bottom: 60px;
}
.el-carousel__item {
  display: flex;
  justify-content: center;
  align-items: center;
}

.right {
  width: 400px;
  position: fixed;
  right: 0;
  top: 80px;
  height: 100%;
  overflow-y: scroll;
  background: #fff;
  padding-bottom: 150px;
}

.main {
  .main-header {
    font-size: 16px;
    padding: 20px 15px;
    margin: 6px 0 0;
    font-weight: 500;
    color: #333333;
    line-height: 16px;
    background: #fff;
  }

  .main-content {
    background: #fff;
    padding: 20px 15px;
    margin-top: 6px;
    border-top: 6px solid #f0f2f5;

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
        font-size: 12px;
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

.fixed-card {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  box-shadow: 0 -1px 2px rgb(240, 240, 240);
}
.blue-border {
  border: 2px solid #1890ff;
}
</style>

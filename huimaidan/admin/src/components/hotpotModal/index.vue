<template>
  <div>
    <el-dialog title="编辑热区" :visible.sync="dialogVisible" @opened="openModal" fullscreen>
      <div class="hotpot-container">
        <div class="left-box">
          <div class="step-list">
            <div class="step-item">添加热区</div>
            <div class="step-item">调整热区大小及位置</div>
            <div class="step-item">设置热区链接</div>
            <div class="step-item">保存设置</div>
          </div>

          <div class="imgBox" @mouseup.left.stop="changeStop()" ref="imgBox">
            <div ref="container" id="img-box-container" class="container" :style="containerStyle">
              <img ref="backgroundImg" :src="imgs" ondragstart="return false;" oncontextmenu="return false;"
                onselect="document.selection.empty();" alt="img" @mousedown.left.stop="mouseDown($event)" v-if="isInited" />
              <!--draw hotpot-->
              <div v-show="caseShow" :style="{
                width: areaWidth + 'px',
                height: areaHeight + 'px',
                left: starX + 'px',
                top: starY + 'px',
              }" class="area" />
              <!--be hotpot-->
              <template v-if="isInited">
                <AreaBox v-for="(item, index) in areaData" :area-data-index="index" :key="'area' + index"
                :link="item.link" :title="item.title" :type="parseInt(item.type)" :area-init.sync="item"
                :parent-width="parentWidth" :parent-height="parentHeight" @delAreaBox="delAreaBox" @addURL="addURL" />
              </template>
              </div>
          </div>
        </div>
        <div class="right-box">
          <!-- 热区链接配置 -->
          <div class="form">
            <div class="header-box">
              <h2 class="mb20">图片热区</h2>
              <div class="alert-box">
                <i class="el-alert__icon el-icon-warning"></i>
                <span>双击设置热区</span>
              </div>
            </div>

            <div v-for="(item, index) in areaData" :key="index" class="form-row">
              <div class="form-item" style="min-width: 4em;">
                <span class="num">热区{{ item.number }}</span>
              </div>
              <div class="form-item label" style="flex: 1">
                <div>
                  <el-input icon="ios-arrow-forward" v-model="item.link" :style="linkInputStyle" placeholder="选择跳转链接">
                    <i class="el-icon-link" slot="suffix" @click="getLink(index)" />
                  </el-input>
                </div>
              </div>
              <i class="el-icon-delete" @click="delAreaBox(index)" />
            </div>
          </div>
          <div class="add-btn">
            <el-button class="btn" ghost @click="addArea"> <span class="iconfont iconjiahao"></span>添加热区</el-button>
          </div>
        </div>
      </div>
      <div slot="footer">
        <el-button class="mr20" type="primary" @click="saveAreaData"> 完成 </el-button>
      </div>
    </el-dialog>
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import AreaBox from './AreaBox';
import linkaddress from '@/components/linkaddress';

export default {
  name: 'OperationFloor',
  components: {
    AreaBox,
    linkaddress,
  },
  props: {
    /**
     * @description 图片数据对象
     * @type {ImgData}
     */
    imgs: {
      type: String, // 图片类型
      default: () => '', // 默认值为空字符串
    },
    /**
     * @description 是否为热门汤品
     * @type {boolean}
     */
    isHotPot: {
      type: Boolean, // 布尔类型
      default: () => false, // 默认值为false
    },
    /**
     * @description 图片区域数据对象
     * @type {AreaData[]}
     */
    imgAreaData: {
      type: Array, // 数组类型
      default: () => [], // 默认值为空数组
    },
    /**
     * @description 链接输入框样式对象
     * @type {LinkInputStyle}
     */
    linkInputStyle: {
      type: Object, // 对象类型
      default: () => ({
        // 默认值为一个包含width属性的对象
        // width: '300px',
      }),
    },
  },
  data() {
    return {
      /**
       * @description 对话框是否可见
       * @type {boolean}
       */
      dialogVisible: false,
      /**
       * @description 开始的x坐标
       * @type {number}
       */
      starX: 0,
      /**
       * @description 开始的y坐标
       * @type {number}
       */
      starY: 0,
      /**
       * @description 区域宽度
       * @type {number}
       */
      areaWidth: 0,
      /**
       * @description 区域高度
       * @type {number}
       */
      areaHeight: 0,
      /**
       * @description 当前显示的图片索引
       * @type {boolean}
       */
      caseShow: false,
      /**
       * @description 当前图片的宽度
       * @type {null}
       */
      nowImgWidth: null,
      /**
       * @description 区域数据
       * @type {Array}
       */
      areaData: [],
      /**
       * @description 当前显示的图片编号
       * @type {number}
       */
      imgNum: 1,
      /**
       * @description 父元素宽度
       * @type {number}
       */
      parentWidth: 0,
      /**
       * @description 父元素高度
       * @type {number}
       */
      parentHeight: 0,
      /**
       * @description 默认宽度
       * @type {number}
       */
      defaultWidth: 750,
      /**
       * @description 当前显示的图片索引
       * @type {number}
       */
      itemIndex: 0,

      isInited: false
    };
  },
  computed: {
    containerStyle() {
      return {
        "--container-width": this.parentWidth + "px",
        "--container-height": this.parentHeight + "px",
      }
    }
  },
  watch: {
    imgAreaData(val) {
      this.areaData = [...val];
    },
    dialogVisible(val) {
      if (!val) {
        this.isInited = false;
      }
    }
  },
  mounted() {
    this.areaData = [...this.imgAreaData];
  },
  methods: {
    getImgSize() {
      return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
          resolve({
            width: img.width,
            height: img.height,
          });
        };
        img.src = this.imgs;
      });
    },
    async updateContainerSize() {
      const imgBox = this.$refs.imgBox;
      const boxWidth = imgBox.clientWidth;
      const boxHeight = imgBox.clientHeight;
      const boxRatio = boxWidth / boxHeight;

      const { width, height } = await this.getImgSize();

      let containerWidth = 0, containerHeight = 0;
      const ratio = width / height;

      // 限制图片大小，使图片必须完整显示在容器内

      // 如果图片的宽高比大于容器宽高比，则以容器宽度为基准，高度自适应
      if (ratio > boxRatio) {
        containerWidth = boxWidth;
        containerHeight = boxWidth / ratio;
      } else {
        // 如果图片的宽高比小于容器宽高比，则以容器高度为基准，宽度自适应
        containerHeight = boxHeight;
        containerWidth = boxHeight * ratio;
      }
      this.parentWidth = containerWidth;
      this.parentHeight = containerHeight;
      setTimeout(() => {
        this.isInited = true;
      });
    },
    openModal() {
        this.updateContainerSize();
    },
    closeModal() {
      this.$confirm('未保存内容，是否在离开前放弃保存？', '提示信息', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          this.dialogVisible = false;
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: '已取消',
          });
        });
    },
    // 绘画热区开始
    mouseDown(e) {
      e.preventDefault();
      this.caseShow = true;
      // 记录滑动的初始值
      this.starX = e.layerX - 5;
      this.starY = e.layerY - 5;
      // 鼠标滑动的过程
      if (!document.onmousemove) {
        let maxWidth = this.parentWidth - e.layerX;
        document.onmousemove = (ev) => {
          if (ev.layerX - this.starX < maxWidth) {
            this.areaWidth = ev.layerX - this.starX - 5;
          } else {
            this.areaWidth = maxWidth;
          }
          this.areaHeight = ev.layerY - this.starY - 5;
        };
      }
    },
    // 绘画热区结束
    changeStop() {
      document.onmousemove = null;
      this.imgNum = this.areaData.length + 1;
      if (this.caseShow && this.areaWidth > 10 && this.areaHeight > 10) {
        const data = {
          number: this.imgNum,
          starX: this.starX,
          starY: this.starY,
          areaWidth: this.areaWidth,
          areaHeight: this.areaHeight,
          nowImgWidth: this.parentWidth,
          nowImgHeight: this.parentHeight,
          link: '',
        };
        this.areaData.push(data);
      }
      // 初始化绘图
      this.caseShow = false;
      this.starX = 0;
      this.starY = 0;
      this.areaWidth = 0;
      this.areaHeight = 0;
    },
    // 删除指定热区
    delAreaBox(index) {
      /* 删除某个热区 */
      this.areaData.splice(index, 1);
      this.$emit('delAreaData', this.areaData);
      /* 删除后 每个热区按顺序重新编号 */
      if (this.areaData) {
        const arr = this.areaData.filter((i) => i.number > index);
        if (!arr) return;
        arr.forEach((i) => i.number--);
        if (this.areaData[this.areaData.length - 1]) {
          this.imgNum = this.areaData[this.areaData.length - 1].number + 1;
        } else {
          this.imgNum = 1;
        }
      }
    },
    // 添加网址
    addURL(index, url) {
      let obj = {
        ...this.areaData[index],
        link: url,
      };
      this.$set(this.areaData, index, obj);
    },
    // 保存热区信息
    saveAreaData() {
      if ((this.areaData && !this.areaData.length) || !this.checkData(this.areaData)) {
        this.$message.error('热区是否配置链接、是否至少添加一个热区?');
        return;
      }
      this.$emit('saveAreaData', this.areaData);
      this.dialogVisible = false;
      this.$message.success('编辑成功!');
    },
    /**
     * 检查列表中每个元素是否都有 link 属性
     * @param {Array} list - 待检查的列表
     * @returns {Boolean} - 是否所有元素都有 link 属性
     */
    checkData(list) {
      let isCheck = true;
      list.some((val) => {
        if (!val.link) {
          isCheck = false;
        }
      });
      return isCheck;
    },
    /**
     * @description 获取链接地址并打开添加链接的模态框
     * @param {number} index - 当前项的索引值
     */
    getLink(index) {
      // 设置当前项的索引值
      this.itemIndex = index;
      // 打开添加链接的模态框
      this.$refs.linkaddres.modals = true;
    },
    /**
     * @description 处理链接地址的输入事件
     * @param {string} e - 链接地址
     */
    linkUrl(e) {
      // 将链接地址存储到对应的数据项中
      this.areaData[this.itemIndex].link = e;
    },
    addArea() {
      this.imgNum = this.areaData.length + 1;
      const lastArea = this.areaData[this.areaData.length - 1];

      const areaWidth = 100;
      const areaHeight = 100;
      let starX = 10;
      let starY = 10;
      if (lastArea) {
        starX = lastArea.starX + lastArea.areaWidth + 10;
        starY = lastArea.starY;
        if (starX + areaWidth > this.parentWidth) {
          starX = 10;
          starY += areaHeight + 10;
        }
      }
      this.areaData.push({
        number: this.imgNum,
        starX,
        starY,
        areaWidth,
        areaHeight,
        nowImgWidth: this.parentWidth,
        nowImgHeight: this.parentHeight,
        link: '',
      });
    },
  },
};
</script>

<style scoped lang="scss">
::v-deep .el-dialog__body {
  height: calc(100% - 115px);
  max-height: initial;
  overflow: auto;
}

.hotpot-container {
  height: 100%;
  display: flex;
}

.left-box {
  flex: 1;
  display: flex;
  flex-flow: column nowrap;

  .imgBox::-webkit-scrollbar {
    display: none;
    /* Chrome Safari */
  }

  .imgBox {
    flex: 1;
    display: flex;
    justify-content: center;
    // width: 65%;
    overflow-y: scroll;

    .container {
      position: relative;
      border: 1px solid #f5f5f5;
      width: var(--container-width);
      height: var(--container-height);
    }

    img {
      cursor: crosshair;
      display: block;
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .area {
      position: absolute;
      width: 200px;
      height: 200px;
      left: 200px;
      top: 300px;
      background: rgba(#2980b9, 0.3);
      border: 1px dashed #34495e;
    }
  }
}

.right-box {
  width: 400px;
  padding-inline: 15px;
  overflow-y: auto;
}

.form {
  .header-box {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 1;
  }
  h2 {
    color: #333;
    top: 0;
  }

  .alert-box {
    position: sticky;
    height: 36px;
    background: rgba(24, 144, 255, 0.1);
    border-radius: 3px 3px 3px 3px;
    border: 1px solid rgba(24, 144, 255, 0.3);
    padding-left: 11px;
    display: flex;
    align-items: center;
    font-size: 12px;
    color: #666666;
    line-height: 12px;
    gap: 6px;

    .el-alert__icon {
      color: #1890ff;
    }
  }

  .form-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
  }
}

.step-list {
  display: flex;
  justify-content: center;
  font-size: 12px;
  color: #333333;
  line-height: 17px;
  gap: 24px;
  margin: 38px 0 58px;
  counter-reset: my-counter;

  .step-item {
    display: inline-flex;
    align-items: center;
    gap: 12px;

    &::before {
      counter-increment: my-counter;
      content: counter(my-counter);
      border-radius: 50%;
      width: 24px;
      height: 24px;
      background: rgba(45, 140, 240, 0.1);
      font-weight: 500;
      color: #2D8CF0;
      display: inline-flex;
      justify-content: center;
      align-items: center;
    }
  }
}

.add-btn {
  position: sticky;
  bottom: 0;
  margin-top: 20px;

  .btn {
    width: 100%;
    height: 36px;
    border-color: #eeeeee;
    color: #666666;

    .iconfont {
      font-size: 11px;
      margin-right: 5px;
    }
  }
}
</style>

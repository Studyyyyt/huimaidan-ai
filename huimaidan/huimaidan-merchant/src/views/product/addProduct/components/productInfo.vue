<template>
  <div>
    <el-row :gutter="24">
      <!-- 商品信息 -->
      <el-col :span="24">
        <el-form-item label="商品类型：">
          <el-select
            v-model="formValidate.type"
            placeholder="请选择商品类型"
            disabled
            class="pageWidth mr10"
            size="small"
          >
            <el-option
              v-for="item in virtual"
              :key="item.tit"
              :label="item.tit"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商品名称：" prop="store_name">
          <el-input
            v-model="formValidate.store_name"
            placeholder="请输入商品名称"
            size="small"
            class="pageWidth"
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="平台商品分类：" prop="cate_id">
          <el-cascader
            v-model="formValidate.cate_id"
            class="pageWidth"
            size="small"
            :options="categoryList"
            :props="props"
            @change="getSpecsLst"
            filterable
            clearable
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商户商品分类：">
          <el-cascader
            v-model="formValidate.mer_cate_id"
            class="pageWidth"
            size="small"
            :options="merCateList"
            :props="{ multiple: true, checkStrictly: true, emitPath: false }"
            filterable
            clearable
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="单位：">
          <el-select
            v-model="formValidate.unit_name"
            placeholder="请选择"
            class="pageWidth mr10"
            clearable
            filterable=""
          >
            <el-option
              v-for="item in unitList"
              :key="item.label"
              :label="item.label"
              :value="item.label"
            />
          </el-select>
          <el-button type="primary" size="small" @click="addUnit">
            添加单位
          </el-button>
        </el-form-item>
      </el-col>

      <el-col v-bind="grid2">
        <el-form-item label="品牌：">
          <el-select
            v-model="formValidate.brand_id"
            filterable
            size="small"
            placeholder="请选择"
            class="pageWidth"
          >
            <el-option
              v-for="item in brandList"
              :key="item.brand_id"
              :label="item.brand_name"
              :value="item.brand_id"
            />
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商品封面图：" prop="image">
          <div class="upLoadPicBox" title="750*750px" @click="modalPicTap('1')">
            <div v-if="formValidate.image" class="pictrue">
              <img :src="formValidate.image" />
            </div>
            <div v-else class="upLoad">
              <i class="el-icon-camera cameraIconfont" />
            </div>
          </div>
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商品轮播图：" prop="slider_image">
          <div class="acea-row">
            <div
              v-for="(item, index) in formValidate.slider_image"
              :key="index"
              class="pictrue"
              draggable="false"
              @dragstart="handleDragStart($event, item)"
              @dragover.prevent="handleDragOver($event, item)"
              @dragenter="handleDragEnter($event, item)"
              @dragend="handleDragEnd($event, item)"
            >
              <img :src="item" />
              <i class="el-icon-error btndel" @click="handleRemove(index)" />
            </div>
            <div
              v-if="formValidate.slider_image.length < 10"
              class="uploadCont"
              title="750*750px"
            >
              <div class="upLoadPicBox" @click="modalPicTap('2')">
                <div class="upLoad">
                  <i class="el-icon-camera cameraIconfont" />
                </div>
              </div>
            </div>
          </div>
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="主图视频：" prop="video_link">
          <el-input
            v-model="videoLink"
            size="small"
            class="pageWidth"
            placeholder="请输入MP4格式的视频链接"
          />
          <input
            ref="refid"
            type="file"
            style="display: none;"
            @change="zh_uploadFile_change"
          />
          <el-button
            type="primary"
            icon="ios-cloud-upload-outline"
            class="uploadVideo"
            size="small"
            @click="zh_uploadFile"
          >
            {{ videoLink ? "确认添加" : "上传视频" }}
          </el-button>
          <el-col :span="12">
            <el-progress
              v-if="upload.videoIng"
              :percentage="progress"
              :text-inside="true"
              :stroke-width="20"
              style="margin-top: 10px;"
            />
          </el-col>
          <el-col :span="24">
            <div v-if="formValidate.video_link" class="iview-video-style">
              <video
                style="width:100%;height: 100%!important;border-radius: 10px;"
                :src="formValidate.video_link"
                controls="controls"
              >
                您的浏览器不支持 video 标签。
              </video>
              <div class="mark">
                <i class="el-icon-delete iconv" @click="delVideo" />
              </div>
            </div>
          </el-col>
        </el-form-item>
      </el-col>
      <el-col v-if="labelList.length" :span="24">
        <el-form-item label="商品标签：">
          <el-select
            v-model="formValidate.mer_labels"
            size="small"
            multiple
            placeholder="请选择"
            class="pageWidth"
          >
            <el-option
              v-for="item in labelList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商品关键字：">
          <el-input
            v-model="formValidate.keyword"
            placeholder="请输入多个商品关键字，用空格隔开"
            size="small"
            class="pageWidth"
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="商品简介：">
          <el-input
            v-model="formValidate.store_info"
            type="textarea"
            :rows="3"
            placeholder="请输入商品简介"
            class="pageWidth"
            size="small"
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="上架时间：">
          <el-radio-group v-model="formValidate.is_show">
            <el-radio :label="1">立即上架</el-radio>
            <el-radio :label="2">定时上架</el-radio>
            <el-radio :label="0">放入仓库</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-col>
      <el-col :span="24" v-if="formValidate.is_show == 2">
        <el-form-item>
          <el-date-picker
            v-model="formValidate.auto_on_time"
            value-format="yyyy-MM-dd HH:mm:ss"
            format="yyyy-MM-dd HH:mm:ss"
            size="small"
            type="datetime"
            placement="bottom-end"
            placeholder="请选择上架时间"
          />
        </el-form-item>
      </el-col>
      <el-col :span="24">
        <el-form-item label="定时下架：">
          <el-switch
            v-model="time_out"
            :active-value="1"
            :inactive-value="0"
            :width="55"
            active-text="开启"
            inactive-text="关闭"
            @change="changeValue"
          />
        </el-form-item>
      </el-col>
      <el-col :span="24" v-if="time_out == 1">
        <el-form-item>
          <el-date-picker
            v-model="formValidate.auto_off_time"
            value-format="yyyy-MM-dd HH:mm:ss"
            format="yyyy-MM-dd HH:mm:ss"
            size="small"
            type="datetime"
            placement="bottom-end"
            placeholder="请选择下架时间"
            @change="onchangeTime2"
          />
        </el-form-item>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import {
  categorySelectApi,
  categoryListApi,
  categoryBrandListApi,
  getProductLabelApi,
  unitOptionsApi,
  unitCreatApi,
  productGetTempKeysApi
} from "@/api/product";

export default {
  name: "productInfo",
  props: {
    formValidate: {
      type: Object,
      default: () => {}
    },

    OneattrValue: {
      // 一级属性值
      type: Array,
      default: () => []
    },
    attrValue: {
      type: Object,
      default: () => {}
    },
    is_timed: {
      // 定时下架
      type: Number,
      default: 0
    },
    timeVal2: {
      // 定时下架时间
      type: String,
      default: ""
    },
    timeVal: {
      // 定时上架时间
      type: String,
      default: ""
    },
    videoLink: {
      // 视频链接
      type: String,
      default: ""
    },
    props: {
      // 平台分类
      type: Object,
      default: () => {
        return {};
      }
    }
  },
  data() {
    return {
      // 建立完全独立的中间状态
      time_out: this.is_timed,
      progress: 10, // 进度条默认0
      categoryList: [], // 平台分类筛选
      merSpecsSelect: [],
      upload: {
        videoIng: false // 是否显示进度条；
      },
      dragging: null, // 拖拽排序
      unitList: [],
      sysSpecsSelect: [],
      merCateList: [], // 商户分类筛选
      brandList: [], // 品牌筛选
      labelList: [], // 标签筛选
      virtual: [
        { tit: "普通商品", id: 0 },
        { tit: "虚拟商品", id: 1 },
        { tit: "云盘商品", id: 2 },
        { tit: "卡密商品", id: 3 },
        { tit: "预约商品", id: 4 }
      ],
      grid2: {
        xl: 10,
        lg: 12,
        md: 12,
        sm: 24,
        xs: 24
      }
    };
  },
  computed: {},
  mounted() {
    // 并发请求获取数据，提高性能
    Promise.all([
      this.getCategoryList(),
      this.getCategorySelect(),
      this.getBrandListApi(),
      this.getLabelList(),
      this.getUnitList()
    ]).catch(error => {
      // 统一处理错误
      this.$message.error(`数据加载失败: ${error.message}`);
    });
  },
  watch: {
    is_timed(newVal) {
      this.time_out = newVal;
    }
  },
  methods: {
    changeValue() {
      this.$emit("changeTimed", this.time_out);
    },
    // 添加单位
    addUnit() {
      this.$modalForm(unitCreatApi()).then(() => this.getUnitList());
    },
    // 单位下拉选项
    getUnitList() {
      unitOptionsApi()
        .then(res => {
          this.unitList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 删除视频；
    delVideo() {
      const that = this;
      that.$set(that.formValidate, "video_link", "");
    },
    onchangeTime2(e) {
      this.timeVal2 = e;
      this.formValidate.auto_off_time = e;
    },
    // 轮播图拖拽排序
    handleDragStart(e, item) {
      this.dragging = item;
    },
    handleDragEnd(e, item) {
      this.dragging = null;
    },
    handleDragOver(e) {
      e.dataTransfer.dropEffect = "move";
    },
    handleDragEnter(e, item) {
      e.dataTransfer.effectAllowed = "move";
      if (item === this.dragging) {
        return;
      }
      const newItems = [...this.formValidate.slider_image];
      const src = newItems.indexOf(this.dragging);
      const dst = newItems.indexOf(item);
      newItems.splice(dst, 0, ...newItems.splice(src, 1));
      this.formValidate.slider_image = newItems;
    },
    zh_uploadFile() {
      if (this.videoLink) {
        this.formValidate.video_link = this.videoLink;
      } else {
        this.$refs.refid.click();
      }
    },
    zh_uploadFile_change(evfile) {
      const that = this;
      that.progress = 10;
      const suffix = evfile.target.files[0].name.substr(
        evfile.target.files[0].name.lastIndexOf(".")
      );
      if (suffix !== ".mp4") {
        return that.$message.error("只能上传MP4文件");
      }
      productGetTempKeysApi().then(res => {
        that.$videoCloud
          .videoUpload({
            type: res.data.type,
            evfile: evfile,
            res: res,
            uploading(status, progress) {
              that.upload.videoIng = status;
            }
          })
          .then(res => {
            that.formValidate.video_link = res.url || res.data.src;
            that.$message.success("视频上传成功");
            that.progress = 100;
          })
          .catch(res => {
            that.upload.videoIng = false;
            that.$message.error((res.msg && res.msg.message) || res.message);
          });
      });
    },

    //获取平台商品分类列表
    getCategoryList() {
      categoryListApi()
        .then(res => {
          this.categoryList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 参数模板
    getSpecsLst() {
      this.$emit("getSpecsLst", this.formValidate, true);
    },
    // 商户分类；
    getCategorySelect() {
      categorySelectApi()
        .then(res => {
          this.merCateList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 品牌筛选；
    getBrandListApi() {
      categoryBrandListApi()
        .then(res => {
          this.brandList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    handleRemove(i) {
      this.formValidate.slider_image.splice(i, 1);
    },
    modalPicTap(tit, num, i) {
      const _this = this;
      const attr = [];
      this.$modalUpload(function(img) {
        if (tit === "1" && !num) {
          _this.formValidate.image = img[0];
          _this.OneattrValue[0].image = img[0];
        }
        if (tit === "2" && !num) {
          img.map(item => {
            attr.push(item.attachment_src);
            _this.formValidate.slider_image.push(item);
            if (_this.formValidate.slider_image.length > 10) {
              _this.formValidate.slider_image.length = 10;
            }
          });
        }
        if (tit === "1" && num === "dan") {
          _this.OneattrValue[0].image = img[0];
        }
        if (tit === "1" && num === "duo") {
          _this.ManyAttrValue[i].image = img[0];
        }
        if (tit === "1" && num === "pi") {
          _this.oneFormBatch[0].image = img[0];
        }
      }, tit);
    },
    // 获取标签项
    getLabelList() {
      getProductLabelApi()
        .then(res => {
          this.labelList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    }
  }
};
</script>
<style scoped lang="scss">
.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
  position: relative;
  cursor: pointer;
  img {
    width: 100%;
    height: 100%;
  }
  .btndel {
    position: absolute;
    z-index: 1;
    width: 20px !important;
    height: 20px !important;
    left: 46px;
    top: -4px;
  }
}
.spec:hover {
  .el-icon-error {
    display: block;
    z-index: 999;
    cursor: pointer;
  }
}
.iview-video-style {
  width: 40%;
  height: 180px;
  border-radius: 10px;
  background-color: #707070;
  margin-top: 10px;
  position: relative;
  overflow: hidden;
}
.iview-video-style .mark {
  position: absolute;
  width: 100%;
  height: 30px;
  top: 0;
  background-color: rgba(0, 0, 0, 0.5);
  text-align: center;
  color: #FFF;
}
</style>

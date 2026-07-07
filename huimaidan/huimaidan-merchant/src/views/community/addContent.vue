<template>
  <div class="divBox">
    <el-card class="box-card">
      <el-form
        :model="ruleForm"
        ref="ruleForm"
        label-width="100px"
        class="demo-ruleForm"
      >
        <el-form-item label="发布作者：" required>
          <div class="upLoadPicBox" title="750*750px" @click="openUser">
            <div v-if="userImg" class="pictrue">
              <img :src="userImg" />
            </div>
            <div v-else class="upLoad">
              <i class="el-icon-camera cameraIconfont" />
            </div>
          </div>
          <div class="tips">只能选择本店铺客服人员。</div>
        </el-form-item>
        <el-form-item label="内容类型：" required>
          <el-radio-group v-model="ruleForm.is_type" @change="typeChange">
            ">
            <el-radio :label="1">图文</el-radio>
            <el-radio :label="2">视频</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="内容图片：" required v-if="ruleForm.is_type == 1">
          <div class="acea-row">
            <div
              v-for="(item, index) in ruleForm.image"
              :key="index"
              class="pictrue"
              draggable="false"
            >
              <img :src="item" />
              <i class="el-icon-error btndel" @click="handleRemove(index)" />
            </div>
            <div
              v-if="ruleForm.image.length < 9"
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
          <div class="tips">请上传小于500KB的图片，最多可以上传9张图片</div>
        </el-form-item>
        <!-- 视频类型 -->
        <template v-if="ruleForm.is_type == 2">
          <el-form-item label="视频内容：" required>
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
              上传视频
            </el-button>
            <el-col :span="12">
              <el-progress
                v-if="videoIng"
                :percentage="progress"
                :text-inside="true"
                :stroke-width="20"
                style="margin-top: 10px;"
              />
            </el-col>
            <el-col :span="24">
              <div v-if="ruleForm.video_link" class="iview-video-style">
                <video
                  style="width:100%;height: 100%!important;border-radius: 10px;"
                  :src="ruleForm.video_link"
                  controls="controls"
                >
                  您的浏览器不支持 video 标签。
                </video>
                <div class="mark" />
                <i class="el-icon-delete iconv" @click="delVideo" />
              </div>
            </el-col>
          </el-form-item>
          <el-form-item label="视频封面图：" required>
            <div
              class="upLoadPicBox"
              title="750*750px"
              @click="modalPicTap('1')"
            >
              <div v-if="ruleForm.image[0]" class="pictrue">
                <img :src="ruleForm.image[0]" />
              </div>
              <div v-else class="upLoad">
                <i class="el-icon-camera cameraIconfont" />
              </div>
            </div>
            <div class="tips">请上传小于500KB的图片</div>
          </el-form-item>
        </template>
        <el-form-item label="内容描述：">
          <el-input
            style=" width: 500px;"
            v-model="ruleForm.content"
            type="textarea"
            :rows="8"
            placeholder="请输入内容描述"
          ></el-input>
        </el-form-item>
        <el-form-item label="参与话题：">
          <el-select
            v-model="ruleForm.topic_id"
            placeholder="请选择"
            size="small"
            style="width: 500px;"
          >
            <el-option
              v-for="item in cateList"
              :key="item.topic_id"
              :label="item.topic_name"
              :value="item.topic_id"
            >
            </el-option>
          </el-select>
          <el-col :span="24">
            <el-button
              type="primary"
              size="small"
              class="mt20 mb20"
              @click="addGoods"
              >添加商品</el-button
            ></el-col>
          <!-- 商品列表 -->
          <el-table
            ref="mainTable"
            :data="tableData"
            size="small"
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
                <div class="row_title line2">{{ scope.row.store_name }}</div>
              </template>
            </el-table-column>

            <el-table-column prop="ot_price" label="售价(元)" min-width="100" />
            <el-table-column label="商品状态" min-width="100">
              <template slot-scope="scope">
                <span>{{ scope.row.is_show == 0 ? "下架" : "上架" }}</span>
              </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
    </el-card>
    <div class="footer">
      <el-button
        type="primary"
        class="submission"
        size="small"
        @click="handleSubmit"
        >提交
      </el-button>
    </div>
    <!-- 选择发布作者 -->
    <selectUserDialog
      ref="selectUserDialog"
      @getUserData="getUserData"
    ></selectUserDialog>
    <!-- 添加商品 -->
    <goodsListDialog
      ref="goodsListDialog"
      @onSelectList="selectList"
    ></goodsListDialog>
  </div>
</template>
<script>
import selectUserDialog from "./components/selectUserDialog";
import goodsListDialog from "@/components/goodsListDialog";
import { roterPre } from "@/settings";
import {
  communityCateApi,
  communityCreateApi,
  communityUpdateApi,
  communityDetailApi
} from "@/api/community";
import { productGetTempKeysApi } from "@/api/product";
export default {
  name: "",
  components: { selectUserDialog, goodsListDialog },
  props: {},
  data() {
    return {
      id: "", // 内容id
      ruleForm: {
        image: [],
        topic_id: "",
        is_type: 1,
        content: "",
        video_link: "",
        spu_id: [],
        uid: ""
      },
      roterPre: roterPre,
      images: [],
      tableData: [],
      isShowCheck: false,
      userImg: "",
      videoLink: "",
      videoIng: false, // 是否显示进度条；
      cateList: [],
      progress: 10, // 进度条默认0
      props: {
        value: "category_id",
        label: "cate_name",
        children: "children",
        multiple: true
      }
    };
  },
  created() {
    this.$store.dispatch("settings/setEdit", true);
    if (this.$route.query.id) {
      this.id = this.$route.query.id;
      this.getInfo(this.id);
    }
  },
  mounted() {
    this.getCateList();
  },
  methods: {
    addGoods() {
      this.$refs.goodsListDialog.dialogVisible = true;
      this.$refs.goodsListDialog.getList("");
      this.$refs.goodsListDialog.setCheckedProduct(this.tableData);
    },

    getInfo(id) {
      communityDetailApi(id).then(res => {
        let data = res.data;
        this.userImg = data.author.avatar;
        for (let key in this.ruleForm) {
          this.ruleForm[key] = key === "topic_id" && data[key] === 0 ? "" : data[key];
        }

        if (data.product && data.product.length > 0) {
          this.tableData = data.product;
        }
      });
    },

    openUser() {
      this.$refs.selectUserDialog.openBox();
    },
    selectList(data) {
      this.tableData = data;
    },

    // 判断选中没有
    isCkecked() {
      let checked = this.proData.filter(item => item.checked);
      if (checked.length) {
        this.isShowCheck = false;
      } else {
        this.isShowCheck = true;
      }
    },
    getCateList() {
      communityCateApi().then(res => {
        this.cateList = res.data;
      });
    },

    typeChange(e) {
      this.ruleForm.image = [];
    },
    // 获取
    getUserData(data) {
      this.userImg = data.avatar;
      this.ruleForm.uid = data.uid;
    },
    handleSubmit() {
      this.$store.dispatch("settings/setEdit", false);
      if (this.tableData.length > 0) {
        this.ruleForm.spu_id = this.tableData.map(item => item.spu_id);
      }

      if (!this.ruleForm.uid) return this.$message.error("请选择作者");
      if (!this.ruleForm.uid) return this.$message.error("请选择作者");
      if (this.ruleForm.image.length == 0)
        return this.$message.error("请上传图片");
      if (this.id) {
        communityUpdateApi(this.id, this.ruleForm)
          .then(res => {
            this.$message.success(res.message);
            this.$router.push({ path: this.roterPre + "/community/list" });
            this.id = "";
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      } else {
        communityCreateApi(this.ruleForm)
          .then(res => {
            this.$message.success(res.message);
            this.$router.push({ path: this.roterPre + "/community/list" });
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      }
    },
    zh_uploadFile() {
      if (this.videoLink) {
        this.ruleForm.video_link = this.videoLink;
      } else {
        this.$refs.refid.click();
      }
    },
    handleRemove(i) {
      this.ruleForm.image.splice(i, 1);
    },

    // 删除视频；
    delVideo() {
      this.$set(that.ruleForm, "video_link", "");
    },
    modalPicTap(tit, num, i) {
      const _this = this;
      const attr = [];
      this.$modalUpload(function(img) {
        if (tit === "1" && !num) {
          _this.ruleForm.image = [img[0]]
        }
        if (tit === "2" && !num) {
          img.map(item => {
            attr.push(item.attachment_src);
            _this.ruleForm.image.push(item);
            if (_this.ruleForm.image.length > 9) {
              _this.ruleForm.image.length = 9;
            }
          });
        }
      }, tit);
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
              that.videoIng = status;
            }
          })
          .then(res => {
            that.ruleForm.video_link = res.url || res.data.src;
            that.$message.success("视频上传成功");
            that.progress = 100;
            that.videoIng = false;
          })
          .catch(res => {
            that.videoIng = false;
            that.$message.error((res.msg && res.msg.message) || res.message);
          });
      });
    }
  }
};
</script>
<style scoped lang="scss">
.tips {
  font-size: 13px;
  color: #909399;
}
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
.footer {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background: #fff;
  box-shadow: 0 -1px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99;
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
}
</style>

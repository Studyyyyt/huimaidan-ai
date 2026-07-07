<template>
  <div class="divBox">
    <div>
      <el-card :body-style="{ padding: '0 20px 20px' }">
        <div class="">
          <el-tabs v-model="currentTab" @tab-click="changeTab">
            <el-tab-pane
              :label="item.label"
              :name="item.value.toString()"
              v-for="(item, index) in headerList"
              :key="index"
            />
          </el-tabs>
        </div>
        <el-alert closable v-if="currentTab == 1">
          <template slot="title">
            <p>上传图片时会生成缩略图</p>
            <p>未设置按照系统默认生成，系统默认：大图800*800，中图300*300，小图150*150</p>
            <p>水印只在上传图片时生成，原图，大中小缩略图上都按照比例存在。</p>
            <p>若上传图片时未开启水印，则该图在开启水印之后依旧无水印效果。</p>
          </template>
        </el-alert>
        <el-alert closable v-else>
          <template slot="title">
            <p v-if="currentTab == 2">
              七牛云开通方法：<a href="https://doc.crmeb.com/mer/mer2/4541" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 3">
              阿里云oss开通方法：<a href="https://doc.crmeb.com/mer/mer2/4542" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 4">
              腾讯云cos开通方法：<a href="https://doc.crmeb.com/mer/mer2/4543" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 7">
              京东云cos开通方法：<a href="https://doc.crmeb.com/mer/mer2/8516" target="_blank">点击查看</a>
            </p>
             <p v-if="currentTab == 6">
              UC云开通方法：<a href="https://doc.crmeb.com/mer/mer2/4545" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 5">
              华为云cos开通方法：<a href="https://doc.crmeb.com/mer/mer2/4544" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 8">
              天翼云cos开通方法：<a href="https://doc.crmeb.com/mer/mer2/8517" target="_blank">点击查看</a>
            </p>
            <p>第一步： 添加【存储空间】（空间名称不能重复）</p>
            <p>第二步： 开启【使用状态】</p>
            <template v-if="currentTab == 2">
              <p>第三步（必选）： 选择云存储空间列表上的修改【空间域名操作】</p>
              <p>第四步（必选）： 选择云存储空间列表上的修改【CNAME配置】，打开后复制记录值到对应的平台解析</p>
            </template>
            <template v-else>
              <p>第三步（可选）： 选择云存储空间列表上的修改【空间域名操作】</p>
              <p>第四步（可选）： 选择云存储空间列表上的修改【CNAME配置】，打开后复制记录值到对应的平台解析</p>
            </template>
          </template>
        </el-alert>
      </el-card>
    </div>
    <div class="pt10" v-if="currentTab == 1">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <el-row>
          <el-form label-width="120px" @submit.native.prevent>
            <el-form-item label="存储方式：">
              <el-radio-group v-model="formValidate.upload_type">
                <el-radio
                :label="item.value.toString()"
                v-for="(item, index) in metholdList"
                :key="index"
                >
                {{item.label}}
                </el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否开启缩略图：">
              <el-switch
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              :width="55"
              v-model="formValidate.image_thumb_status"
              />
            </el-form-item>
          </el-form>
          <el-form ref="formValidate"
          :model="formValidate"
          :rules="ruleValidate"
          label-width="30px"
          @submit.native.prevent>
          <div class="abbreviation">
            <div v-if="formValidate.image_thumb_status==1" class="top mb20">
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviationBig.png" alt="" />
                  </div>
                  <div>缩略大图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      size="small"
                      type="number"
                      v-model="formValidate.thumb_big_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      class="topIput"
                      size="small"
                      type="number"
                      v-model="formValidate.thumb_big_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviation.png" alt="" />
                  </div>
                  <div>缩略中图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      size="small"
                      type="number"
                      v-model="formValidate.thumb_mid_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      type="number"
                      size="small"
                      class="topIput"
                      v-model="formValidate.thumb_mid_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviationSmall.png" alt="" />
                  </div>
                  <div>缩略小图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      size="small"
                      type="number"
                      v-model="formValidate.thumb_small_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      class="topIput"
                      size="small"
                      type="number"
                      v-model="formValidate.thumb_small_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
            </div>
            <el-divider v-if="formValidate.image_thumb_status==1" />
            <div class="content">
              <el-form-item label="是否开启水印："  label-width="120px">
                <el-switch
                  :active-value="1"
                  :inactive-value="0"
                  active-text="开启"
                  inactive-text="关闭"
                  :width="55"
                  v-model="formValidate.image_watermark_status"
                />
                <span style="color: #999;font-size: 12px;">云存储可实现开启水印，水印显示；关闭水印，不显示；本地存储上传图片，如果开启水印，再关闭，水印仍显示，需重新上传。</span>
              </el-form-item>
              <div v-if="formValidate.image_watermark_status == 1">
                <el-form-item label="水印类型：" label-width="130px">
                  <el-radio-group v-model="formValidate.watermark_type">
                    <el-radio :label="1">图片</el-radio>
                    <el-radio :label="2">文字</el-radio>
                  </el-radio-group>
                </el-form-item>
                <div v-if="formValidate.watermark_type == 1">
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印透明度：" prop="name" label-width="130px">
                      <el-input
                        class="topIput"
                        size="small"
                        type="number"
                        v-model="formValidate.watermark_opacity"
                        placeholder="请输入水印透明度,0-100,100表示不透明"
                        style="width: 250px"
                      >
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印倾斜度：" prop="mail" label-width="130px">
                      <el-input-number
                        class="topIput"
                        size="small"
                        type="number"
                        :min="0"
                        :max="360"
                        controls-position="right"
                        v-model="formValidate.watermark_rotate"
                        placeholder="请输入水印倾斜度"
                        style="width: 250px"
                      >
                      </el-input-number>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印图片：" prop="name" label-width="130px">
                      <div class="picBox">
                        <div class="pictrue" v-if="formValidate.watermark_image">
                          <img :src="formValidate.watermark_image" />
                        </div>
                        <div v-else class="upLoad acea-row row-center-wrapper" @click.stop="modalPicTap()">
                          <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                        </div>
                        <div v-if="formValidate.watermark_image" class="uploadpicBox_list_method">
                          <i
                            class="el-icon-delete"
                            @click.stop="formValidate.watermark_image=''"
                          />
                          <i class="el-icon-view" @click.stop="showImage=true" />
                        </div>
                      </div>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印位置：" prop="mail" label-width="130px">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印横坐标偏移量："  label-width="130px" prop="name">
                      <el-input
                        class="topIput"
                        size="small"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                        style="width: 250px"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印纵坐标偏移量：" label-width="130px" prop="mail">
                      <el-input
                        class="topIput"
                        size="small"
                        type="number"
                        v-model="formValidate.watermark_y"
                        placeholder="请输入水印纵坐标偏移量"
                        style="width: 250px"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                  </div>
                </div>
                <!-- 水印类型为文字 -->
                <div v-else>
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印文字：" label-width="130px" prop="name">
                      <el-input size="small" class="topIput" v-model="formValidate.watermark_text" placeholder="请输入水印文字" style="width: 250px">
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印文字大小：" label-width="130px">
                      <el-input
                        class="topIput"
                        size="small"
                        type="number"
                        v-model="formValidate.watermark_text_size"
                        placeholder="请输入水印文字大小"
                        style="width: 250px"
                      >
                      </el-input>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印字体颜色：" prop="name" label-width="130px">
                      <el-color-picker size="small" v-model="formValidate.watermark_text_color"></el-color-picker>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印位置：" prop="mail" label-width="130px">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="水印字体旋转角度：" label-width="130px">
                      <el-input-number
                        class="topIput"
                        size="small"
                        type="number"
                        :min="0"
                        :max="360"
                        controls-position="right"
                        v-model="formValidate.watermark_text_angle"
                        placeholder="请输入水印字体旋转角度"
                        style="width: 250px"
                      >
                      </el-input-number>
                    </el-form-item>
                    <el-form-item class="contentIput" label="水印横坐标偏移量：" label-width="130px">
                      <el-input
                        class="topIput"
                        size="small"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                        style="width: 250px"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                  </div>
                  <el-form-item class="contentIput" label="水印纵坐纵偏移量：" prop="mail" label-width="130px">
                    <el-input
                      size="small"
                      class="topIput"
                      type="number"
                      v-model="formValidate.watermark_y"
                      placeholder="请输入水印纵坐纵偏移量"
                      style="width: 250px"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
            </div>
            <el-form-item>
              <el-button size="small" type="primary" @click="handleSubmit('formValidate')">保存</el-button>
            </el-form-item>
          </div>
        </el-form>
        </el-row>
      </el-card>
    </div>
    <!-- 缩略图配置 -->
    <div class="pt10" v-else-if="currentTab == 10">我去恶趣味我去</div>
    <div class="pt10" v-else>
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <el-row class="mb20">
          <el-col :span="24">
            <el-button size="small" type="primary" @click="addStorageBtn">添加存储空间</el-button>
            <el-button size="small" type="success" @click="synchro" style="margin-left: 20px">同步存储空间</el-button>
            <el-button size="small" @click="addConfigBtn" style="float: right">修改配置信息</el-button>
          </el-col>
        </el-row>
        <el-table
          :data="tableData.data"
          ref="table"
          class="mt14"
          size="small"
          v-loading="loading"
        >
          <el-table-column label="储存空间名称" min-width="120">
            <template slot-scope="scope">
              <span>{{ scope.row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="区域" min-width="90">
            <template slot-scope="scope">
              <span>{{ scope.row.region }}</span>
            </template>
          </el-table-column>
          <el-table-column label="空间域名" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.domain }}</span>
            </template>
          </el-table-column>
          <el-table-column label="使用状态" min-width="90">
            <template slot-scope="scope">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row.status"
                :value="scope.row.status"
                @change="changeSwitch(scope.row)"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </template>
          </el-table-column>
          <el-table-column label="创建时间" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.create_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="更新时间" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.update_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="220">
            <template slot-scope="scope">
              <template v-if="scope.row.domain && scope.row.domain != scope.row.cname">
                <el-button type="text" size="small" @click="config(scope.row)">CNAME配置</el-button>
              </template>
              <el-button type="text" size="small" @click="edit(scope.row)">修改空间域名</el-button>
              <el-button type="text" size="small" @click="del(scope.row)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="block">
          <el-pagination
            background
            :page-size="tableFrom.limit"
            :current-page="tableFrom.page"
            layout="total, prev, pager, next, jumper"
            :total="tableData.total"
            @size-change="handleSizeChange"
            @current-change="pageChange"
          />
        </div>

      </el-card>
    </div>
    <el-dialog :visible.sync="configuModal" title="CNAME配置" width="470px">
      <div>
        <div class="confignv"><span class="configtit">主机记录：</span>{{ configData.domain }}</div>
        <div class="confignv"><span class="configtit">记录类型：</span>CNAME</div>
        <div class="confignv">
          <span class="configtit">记录值：</span>{{ configData.name }}
          <span class="copy copy-data" @click="insertCopy(configData.name,$event)">复制</span>
        </div>
      </div>
    </el-dialog>
    <!--查看图片-->
    <el-dialog :visible.sync="showImage" title="查看图片" width="470px">
      <img :src="formValidate.watermark_image" style="width: 100%;" alt="">
    </el-dialog>

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
import {
  storageConfigApi,
  storageTypeApi,
  getStorageKeyApi,
  storageDeleteForm,
  addConfigApi,
  addStorageApi,
  storageListApi,
  storageSynchApi,
  storageSwitchApi,
  storageStatusApi,
  editStorageApi,
  positionPostApi,
} from '@/api/setting';
import clipboard from '@/utils/clipboard'
export default {
  components: { },
  data() {
    return {
      modalPic: false,
      saveType: 0,
      isChoice: '单选',
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      positionId: 1,
      positiontlt: '',
      formValidate: {},
      boxs: [
        { content: '左上', id: 1 },
        { content: '上', id: 2 },
        { content: '右上', id: 3 },
        { content: '左中', id: 4 },
        { content: '中', id: 5 },
        { content: '右中', id: 6 },
        { content: '左下', id: 7 },
        { content: '下', id: 8 },
        { content: '右下', id: 9 },
      ],
      ruleValidate: {},
      configuModal: false,
      configData: '',
      headerList: [],
      metholdList: [],
      total: 0,
      tableFrom: {
        page: 1,
        limit: 15,
        type: '1',
      },
      tableData: {
        data: [],
        total: 0
      },
      currentTab: '1',
      loading: false,
      addData: {
        input: '',
        select: '',
        jurisdiction: '1',
        type: '1',
      },
      confData: {
        AccessKeyId: '',
        AccessKeySecret: '',
      },
      localStorage: false,
      showImage: false
    };
  },
  created() {
    storageTypeApi().then((res) => {
      this.headerList=[{ label: '储存配置', value: '1' },...res.data]
      this.metholdList=[{ label: '本地储存', value: '1' },...res.data]
      this.changeTab();
    }).catch((err) => {
      this.$message.error(err);
    });

  },
  methods: {
    insertCopy(text,event) {
      clipboard(text, event)
    },
    bindbox(item) {
      this.positionId = item.id;
      this.positiontlt = item.content;
      this.formValidate.watermark_position = item.id;

    },
    handleSubmit(name) {
      if (this.formValidate.image_watermark_status) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            this.postMessage(this.formValidate);
          } else {
            this.$message.error('Fail!');
          }
        });
      } else {
        this.postMessage(this.formValidate);
      }
    },
    //保存接口
    postMessage(data) {
      positionPostApi(data)
        .then((res) => {
          this.$message.success(res.message);
        })
        .catch((err) => {
          this.$message.error(err.message);
        });
    },
    // 选择图片
    modalPicTap() {
      let _this = this;
      _this.$modalUpload(function (img) {
        _this.formValidate.watermark_image = img[0];
      });
    },
    config(row) {
      this.configuModal = true;
      this.configData = row;
    },
    //同步储存空间
    synchro() {
      storageSynchApi(this.currentTab)
        .then((res) => {
          this.$message.success(res.message);
          this.getList();
        })
        .catch((err) => {
          this.$message.error(err.message);
        });
    },
    // 添加存储空间
    addStorageBtn() {
      this.$modalForm(addStorageApi(this.currentTab)).then(() => {
        this.getList();
      });
    },
    // 修改配置信息
    addConfigBtn() {
      this.$modalForm(getStorageKeyApi(this.currentTab)).then(() => {
        this.getList(1);
      });
    },
    //修改空间域名
    edit(row) {
      this.$modalForm(editStorageApi(row.id)).then(() => {
        this.getList();
      });
    },
    changeSwitch(row) {
      this.$confirm('您确认要切换使用状态吗?', '切换状态', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        storageStatusApi(row.id)
          .then((res) => {
            this.$message.success(res.message);
            this.getList();
          })
          .catch((err) => {
            this.$message.error(err.message);
            this.getList();
          });
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '已取消'
          })
          this.getList();
      })
    },
    getList(num) {
      this.loading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      storageListApi(this.currentTab,this.tableFrom).then((res) => {
        this.tableData.data = res.data.list
        this.tableData.total = res.data.count
        this.loading = false
      });
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList(1)
    },
    changeTab() {
      this.formValidate.upload_type = this.currentTab;
      this.tableFrom.page = 1;
      if (this.currentTab == 1) {
        this.getposition();
      } else {
        this.getList();
      }
    },
    getposition() {
       storageConfigApi().then((res) => {
        if (res.data.type == 1) {
          this.localStorage = true;
        }
        this.formValidate = res.data;
        this.formValidate.image_watermark_status = Number(res.data.image_watermark_status)
        this.formValidate.thumb_big_width = res.data.thumb_big_width || '800'
        this.formValidate.thumb_big_height = res.data.thumb_big_height || '800'
        this.formValidate.thumb_mid_width = res.data.thumb_mid_width || '300'
        this.formValidate.thumb_mid_height = res.data.thumb_mid_height || '300'
        this.formValidate.thumb_small_width = res.data.thumb_small_width || '150'
        this.formValidate.thumb_small_height = res.data.thumb_small_height || '150'
        this.formValidate.watermark_type = res.data.watermark_type || 1
        this.positionId = res.data.watermark_position;
        for (var i = 0; i < this.boxs.length; i++) {
          if (this.boxs[i].id == res.data.watermark_position) {
            this.bindbox(this.boxs[i]);
          }
        }
    });
    },
    addSwitch(e) {
      if (e) {
        this.localStorage = 1;
      }
      storageSwitchApi({ type: this.localStorage })
        .then((res) => {
          this.$message.success(res.message);
          this.getList();
        })
        .catch((err) => {
          this.$message.error(err.message);
        });
    },
    // 删除
    del(row) {
      this.$modalSure().then(() => {
        storageDeleteForm(row.id)
          .then(({
            message
          }) => {
            this.$message.success(message)
            this.getList('')
          })
          .catch(({
            message
          }) => {
            this.$message.error(message)
          })
      })
    },
  },
};
</script>
<style scoped lang="scss">
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.ivu-input-group > .ivu-input:last-child,
::v-deep .ivu-input-group-append {
  background: none;
  color: #999999;
}
::v-deep .ivu-input-group .ivu-input {
  border-right: 0px !important;
}
.content ::v-deep .ivu-form .ivu-form-item-label {
  width: 133px;
}
.topIput {
  width: 186px;
  background: #ffffff;
  border-right: 0px !important;
}
.abbreviation {
  .top {
    display: flex;
    justify-content: flex-start;
    .topBox {
      display: flex;
      .topRight {
        width: 254px;
        margin-left: 36px;
      }
      .topLeft {
        width: 94px;
        height: 94px;

        text-align: center;
        font-size: 13px;
        font-weight: 400;
        color: #000000;
        .img {
          height: 67px;
          background: #f7fbff;
          border-radius: 4px;
          margin-bottom: 9px;
          .imgs {
            width: 70px;
            height: 51px;
            display: inline-block;
            text-align: center;
            margin-top: 8px;
          }
        }
      }
    }
  }
  .content {
    ::v-deep .ivu-form-item-label {
      width: 120px;
    }
    .flex {
      display: flex;
      justify-content: flex-start;

      .contentIput {
        width: 400px;
      }
      .conents {
        display: flex;
        .title {
          width: 30px;
          margin-top: 70px;
          margin-left: 6px;
        }
        .positionBox {
          display: flex;
          flex-wrap: wrap;
          width: 101px;
          height: 99px;
          border-right: 1px solid #dddddd;
          .box {
            width: 33px;
            height: 33px;
            // border-radius: 4px 0px 0px 0px;
            border: 1px solid #dddddd;
            cursor: pointer;
          }
          .on {
            background: rgba(24, 144, 255, 0.1);
          }
        }
      }
    }
  }
}
</style>
<style scoped lang="scss">
.message ::v-deep .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.ivu-radio-wrapper {
  margin-right: 15px;
  font-size: 12px !important;
}
.message ::v-deep .ivu-tabs-tab {
  border-radius: 0 !important;
}
.table-box {
  padding: 20px;
}
.is-table {
  display: flex;
  /* justify-content: space-around; */
  justify-content: center;
}
.el-alert a{
  color: var(--prev-color-primary);
}
.btn {
  cursor: pointer;
  color: var(--prev-color-primary);
  font-size: 10px;
}
.is-switch-close {
  background-color: #504444;
}
.is-switch {
  background-color: #eb5252;
}
.notice-list {
  background-color: var(--prev-color-primary)5;
  margin: 0 15px;
}
.table {
  padding: 0 18px;
}
.confignv {
  margin: 10px 0px;
}
.configtit {
  display: inline-block;
  width: 80px;
  text-align: right;
}
.copy {
  padding: 3px 5px;
  border: 1px solid #cccccc;
  border-radius: 5px;
  color: #333;
  cursor: pointer;
  margin-left: 5px;
}
.copy:hover {
  border-color: var(--prev-color-primary);
  color: var(--prev-color-primary);
}
.picBox {
  display: inline-block;
  cursor: pointer;
  position: relative;
}
.picBox .uploadpicBox_list_method {
  position: absolute;
  top: 0;
  left: 0;
  font-size: 18px;
  font-weight: bold;
  color: #fff;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -ms-flex-pack: distribute;
  justify-content: space-around;
  background: rgba(0, 0, 0, 0.4);
  border-radius: 4px;
  opacity: 0;
  width: 100%;
  height: 100%;
  -webkit-transition: 0.3s;
  transition: 0.3s;
}
.picBox:hover {
 .uploadpicBox_list_method  {
    opacity: 1;
  }
}
.picBox .pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
}

.picBox .pictrue img {
  width: 100%;
  height: 100%;
}
.picBox .upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
}
h3 {
  margin: 5px 0 15px 0;
}
.table-box p {
  margin-bottom: 10px;
}
.save-type {
  font-size: 13px;
}
</style>

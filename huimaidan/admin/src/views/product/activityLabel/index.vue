<template>
  <div>
    <el-row class="ivu-mt box-wrapper">
      <el-col v-bind="grid1" class="left-wrapper">
        <div class="tree_tit" v-db-click @click="addSort">
          活动标签
          <el-tooltip class="item" effect="dark" content="添加标签分类" placement="top">
            <i class="el-icon-plus"></i>
          </el-tooltip>
        </div>
        <div class="tree" ref="treeContainer">
          <el-tree
            :data="labelCateList"
            node-key="id"
            default-expand-all
            highlight-current
            :expand-on-click-node="false"
            @node-click="bindMenuItem"
            :current-node-key="treeId"
          >
            <span class="custom-tree-node" slot-scope="{ data }">
              <div class="file-name line1">
                <el-tooltip class="item" effect="dark" :content="data.name" placement="top" :disabled="data.name.length <= maxVisibleCharCount">
                  <span class="line1">
                    {{ data.name }}
                  </span>
                </el-tooltip>
              </div>
              <span v-if="data.id && !isSystemLabelCate(data.id)" class="dropdown-menu" :class="{ active: data.id === labelFrom.label_cate }">
                <el-dropdown @command="(command) => clickMenu(data, command)">
                  <i class="el-icon-more el-icon--right"></i>
                  <template slot="dropdown">
                    <el-dropdown-menu>
                      <el-dropdown-item command="1">编辑分类</el-dropdown-item>
                      <el-dropdown-item v-if="data.id" command="2">删除分类</el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
              </span>
            </span>
          </el-tree>
        </div>
      </el-col>
      <el-col v-bind="grid2" ref="rightBox">
        <el-card :bordered="false" shadow="never" class="right-wrapper">
          <el-row>
            <el-col>
              <el-button type="primary" size="small" v-db-click @click="add">添加标签</el-button>
            </el-col>
          </el-row>
          <el-table
            :data="labelLists"
            size="small"
            ref="table"
            class="mt14 table-container"
            v-loading="loading"
            highlight-current-row
            no-userFrom-text="暂无数据"
            no-filtered-userFrom-text="暂无筛选结果"
          >
            <el-table-column label="ID" width="80">
              <template slot-scope="scope">
                <span>{{ scope.row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="标签名称" width="180">
              <template slot-scope="scope">
                <div
                  v-if="scope.row.style_type == 1"
                  class="words-tag"
                  :style="{
                    backgroundColor: scope.row.bg_color,
                    color: scope.row.color,
                    border: scope.row.border_color ? '1px solid ' + scope.row.border_color : 'none',
                  }"
                >
                  <span>{{ scope.row.label_name }}</span>
                </div>
                <img :src="scope.row.icon" class="tag-img" v-else />
              </template>
            </el-table-column>
            <el-table-column label="分类名称" min-width="140" show-overflow-tooltip>
              <template slot-scope="scope">
                <span v-if="scope.row.cate && scope.row.cate.name ">{{ scope.row.cate.name }}</span>
                <span v-else>--</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" min-width="140">
              <template #header>
                状态
                <el-tooltip class="item" effect="dark" content="标签关闭后失效，无法使用" placement="top">
                  <i class="el-icon-warning-outline"></i>
                </el-tooltip>
              </template>
              <template slot-scope="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.status"
                  :value="scope.row.status"
                  @change="onchangeStatus(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column label="移动端展示" min-width="140">
              <template slot-scope="scope">
                <el-switch
                  class="defineSwitch"
                  :active-value="1"
                  :inactive-value="0"
                  v-model="scope.row.is_show"
                  :value="scope.row.is_show"
                  @change="onchangeShow(scope.row)"
                  size="large"
                  active-text="开启"
                  inactive-text="关闭"
                >
                </el-switch>
              </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作" width="100">
              <template slot-scope="scope">
                <a v-db-click @click="edit(scope.row.id)" class="text-blue">修改</a>
                <template v-if="!isSystemLabelCate(scope.row.label_cate)">
                  <el-divider direction="vertical"></el-divider>
                  <a v-db-click @click="del(scope.row.id)" class="text-blue">删除</a>
                </template>
              </template>
            </el-table-column>
          </el-table>
          <div class="row-right mt-20">
            <el-pagination
              background
              :page-size="labelFrom.limit"
              :current-page="labelFrom.page"
              layout="total, prev, pager, next"
              :total="labelTotal"
              @current-change="pageChange"
            />
          </div>
        </el-card>
        <el-dialog :visible.sync="modals" closable :title="isEdit ? '编辑标签' : '添加标签'" width="560px" @close="cancel">
          <div>
            <el-form label-position="right" size="small" ref="form" :rules="rules" :model="form" label-width="100px">
              <el-form-item label="标签名称：" prop="label_name">
                <el-input v-model="form.label_name" class="w-420" :disabled="isSystemLabelCate(form.label_cate)"></el-input>
              </el-form-item>
              <el-form-item label="分类选择：" prop="label_cate">
                <el-select v-model="form.label_cate" clearable class="w-420" :disabled="isSystemLabelCate(form.label_cate)">
                  <el-option
                    v-for="item in labelFormCateList"
                    :disabled="isSystemLabelCate(item.id)"
                    :value="item.id"
                    :label="item.name"
                    :key="item.id"
                  ></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="移动端展示：" prop="is_show" class="switch-width-double">
                <el-switch v-model="form.is_show" :active-value="1" :inactive-value="0" active-text="开启" inactive-text="关闭"> </el-switch>
              </el-form-item>
              <el-form-item label="效果设置：" prop="style_type">
                <el-radio-group v-model="form.style_type" :true-value="1" :false-value="2" @change="handleStyleTypeChange">
                  <el-radio :label="1">自定义</el-radio>
                  <el-radio :label="0">图片</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item label="字体颜色：" v-if="form.style_type == 1" prop="color">
                <el-color-picker v-model="form.color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则为默认色</p>
              </el-form-item>
              <el-form-item label="背景颜色：" v-if="form.style_type == 1" prop="bg_color">
                <el-color-picker v-model="form.bg_color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则为默认色</p>
              </el-form-item>
              <el-form-item label="边框颜色：" v-if="form.style_type == 1" prop="border_color">
                <el-color-picker v-model="form.border_color" show-alpha></el-color-picker>
                <p class="tip">若未设置颜色，则无边框</p>
              </el-form-item>
              <el-form-item label="上传图标：" v-if="form.style_type == 0" prop="icon">
                <div v-if="form.icon" class="upload-list">
                  <div class="upload-item">
                    <img :src="form.icon" />
                    <div class="close" @click="form.icon = ''">
                      <i class="el-icon-close"></i>
                    </div>
                  </div>
                </div>
                <el-button
                  v-else
                  class="upload-select"
                  type="dashed"
                  icon="el-icon-camera"
                  @click="modalPicTap(1)"
                ></el-button>
                <p class="tip">建议尺寸：80px*30px，若未上传则为空白</p>
              </el-form-item>
              <el-form-item label="排序：" prop="sort">
                <el-input-number v-model="form.sort" :min="0" :max="999" class="selWidth"></el-input-number>
              </el-form-item>
              <el-form-item label="是否开启：" prop="status" class="switch-width-double">
                <el-switch v-model="form.status" :active-value="1" :inactive-value="0" size="large" active-text="开启" inactive-text="关闭"></el-switch>
              </el-form-item>
            </el-form>
          </div>
          <span slot="footer" class="dialog-footer">
            <el-button @click="cancel">取 消</el-button>
            <el-button type="primary" v-db-click @click="addWordsConfirm">确 定</el-button>
          </span>
        </el-dialog>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import {
  labelCateListApi,
  activityLabelListApi,
  activityLabelInfoApi,
  activityLabelSaveApi,
  activityLabelCateFormApi,
  activityLabelCateUpdateApi,
  activityLabelStatusApi,
  activityLabelDeleteApi,
  labelCateDeleteApi,
  activityLabelUpdateApi
} from '@/api/product';

const ALL_CATE_ID = 0;

export default {
  name: 'labelList',
  data() {
    return {
      SYSTEM_LABEL_CATE_ID_LIST: [ALL_CATE_ID, 1],
      maxVisibleCharCount: 0,
      treeId: ALL_CATE_ID,
      grid1: {
        xl: 3,
        lg: 4,
        md: 4,
        sm: 8,
        xs: 0,
      },
      grid2: {
        xl: 21,
        lg: 20,
        md: 20,
        sm: 16,
        xs: 24,
      },

      loading: false,
      labelFrom: {
        page: 1,
        limit: 12,
        label_cate: ALL_CATE_ID,
      },
      labelLists: [],
      labelTotal: 0,
      theme3: 'light',
      labelCateList: [],
      sortName: '',
      modals: false,
      isEdit: false,
      form: {
        id: 0,
        label_cate: '',
        label_name: '',
        style_type: 1, //样式类型 1自定义 2图片
        color: '#e93323',
        bg_color: '#fff',
        border_color: '#e93323',
        sort: 0,
        is_show: 1,
        icon: '',
        status: 1,
      },
      rules: {
        label_name: [
          { required: true, message: '请输入热词名称', trigger: 'blur' },
          { min: 2, max: 6, message: '长度在 2 到 6 个字符', trigger: 'blur' },
        ],
        label_cate: [{ required: true, message: '请选择分组' }],
        icon: [{ required: true, min: 1, message: '请上传图标', }],
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
    labelFormCateList() {
      return this.labelCateList.filter(item => !this.isSystemLabelCate(item.id));
    }
  },
  created() {
    this.getLabelLabelAll();
    this.getList();
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
    this.handleResize();
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    handleStyleTypeChange() {
      this.$refs.form.clearValidate();
    },
    handleResize() {
      this.$nextTick(() => {
        const treeWidth = this.$refs.treeContainer.clientWidth;
        const contentWidth = treeWidth - 44;// 44px是两边的边距
        const maxVisibleCharCount = Math.floor(contentWidth / 14);// 14px是每个字符的宽度
        this.maxVisibleCharCount = maxVisibleCharCount;
      });
    },
    isSystemLabelCate(cateId) {
      return this.SYSTEM_LABEL_CATE_ID_LIST.includes(cateId);
    },
    // 添加
    add() {
      const isSystemLabelCate = this.isSystemLabelCate(this.labelFrom.label_cate);
      this.form.label_cate = isSystemLabelCate ? null :  this.labelFrom.label_cate;
      this.modals = true;
      this.isEdit = false;
    },
    modalPicTap(type, num) {
      const _this = this;
      this.$modalUpload(function (img) {
        _this.form.icon = img[0];
      }, type);
    },
    // 分组列表
    getList() {
      this.loading = true;
      activityLabelListApi({
        ...this.labelFrom,
        label_cate: this.labelFrom.label_cate === ALL_CATE_ID ? "" : this.labelFrom.label_cate,
      })
        .then(async (res) => {
          let data = res.data;
          this.labelLists = data.list;
          this.labelTotal = data.cout;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page){
      this.labelFrom.page = page;
      this.getList();
    },
    // 修改
    edit(id) {
      activityLabelInfoApi(id).then((res) => {
        this.form = res.data;
        this.isEdit = true;
        this.modals = true;
      });
    },
    // 删除
    del(id) {
       this.$modalSure("删除该标签吗").then(() => {
        activityLabelDeleteApi(id).then(({ message }) => {
            this.$message.success(message);
            this.labelFrom.page = 1;
            this.getList();
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    // 修改是否显示
    onchangeStatus(row) {
      activityLabelStatusApi(row.id, {type: 'status', status: row.status})
        .then(async (res) => {
          this.$message.success(res.message);
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },
    onchangeShow(row) {
      activityLabelStatusApi(row.id, {type: 'is_show', status: row.is_show})
        .then(async (res) => {
          this.$message.success(res.message);
        })
        .catch((res) => {
          this.$message.error(res.message);
        });
    },

    // 标签分类
    getLabelLabelAll() {
      labelCateListApi({page:1, limit: 100}).then((res) => {
        const cateList = [];

        for (const cate of res.data.list) {
          if (this.isSystemLabelCate(cate.id)) {
            /** 系统分类，需要置顶 */
            cateList.unshift(cate);
          } else {
            cateList.push(cate);
          }
        }
        cateList.unshift({
          id: ALL_CATE_ID,
          name: "全部"
        });
        this.labelCateList = cateList;
      });
    },
    // 显示标签小菜单
    showMenu(item) {
      this.labelCateList.forEach((el) => {
        if (el.id == item.id) {
          el.status = item.status ? false : true;
        } else {
          el.status = false;
        }
      });
    },
    addWordsConfirm() {
      if (!this.form.label_cate) return this.$message.error('请选择分组');
      this.$refs.form.validate((valid) => {
        if (valid) {
          let funApi = this.form.id ? activityLabelUpdateApi(this.form.id, this.form) : activityLabelSaveApi(this.form);
            funApi.then((res) => {
              this.$message.success(res.message);
              this.modals = false;
              this.cancel();
              this.labelFrom.page = 1;
              this.getList();
            })
            .catch((res) => {
              this.$message.error(res.message);
            });
        }
      });
    },
    cancel() {
      this.form = {
        id: 0,
        label_cate: '',
        label_name: '',
        style_type: 1, //样式类型 1自定义 0图片
        color: '#e93323',
        bg_color: '#ffffff',
        border_color: '#e93323',
        sort: 0,
        is_show: 1,
        icon: '',
        status: 1,
      };
      this.modals = false;
    },
    //编辑标签
    labelEdit(item) {
      this.$modalForm(activityLabelCateUpdateApi(item.id)).then(() => {
        this.getLabelLabelAll(1);
        this.getList();
      });
    },
    // 添加分类
    addSort() {
      this.$modalForm(activityLabelCateFormApi()).then(() => this.getLabelLabelAll());
    },
    deleteSort(id) {
      this.$modalSure("删除该分组吗").then(() => {
        labelCateDeleteApi(id).then(({ message }) => {
            this.$message.success(message);
            this.getLabelLabelAll()
            this.labelFrom.label_cate = ALL_CATE_ID;
            this.treeId = ALL_CATE_ID;
            this.getList();
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    },
    clickMenu(data, name) {
      if (name == 1) {
        this.labelEdit(data);
      } else if (name == 2) {
        this.deleteSort(data.id);
      }
    },
    bindMenuItem(cate) {
      this.labelFrom.page = 1;
      this.labelCateList.forEach((el) => {
        el.status = false;
      });
      this.labelFrom.label_cate = cate.id;
      this.getList();
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tree-node {
  .dropdown-menu {
    flex-shrink: 0;
  }
  .dropdown-menu:not(.active) {
    display: none;
  }

  &:hover .dropdown-menu {
    display: initial;
  }
}

.tree_tit {
  font-size: 15px;
  color: #606266;
  font-weight: 500;
  padding: 23px 23px 23px 20px;
  display: flex;
  align-items: center;
  cursor: pointer;

  .el-icon-plus {
    font-size: 20px;
    margin-left: auto;
    color: var(--prev-color-primary);
  }
}
.tree {
  height: 717px;
  overflow-y: auto;

  ::v-deep .is-current .file-name {
    color: var(--prev-color-primary);
  }

  .file-name {
    display: flex;
    align-items: center;
    color: #303133;
    font-size: 14px;

    .icon {
      width: 15px;
      height: 13px;
      margin-right: 6px;
    }
  }

  .el-tree-node__children .el-tree-node {
    margin-right: 0;
  }

  ::v-deep .el-tree-node__content {
    width: 100%;
    height: 48px;
  }

  ::v-deep .custom-tree-node {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-right: 20px;
    font-size: 13px;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.6);
    line-height: 13px;
    overflow: hidden;
  }

  ::v-deep .is-current {
    background: #f1f9ff !important;
    color: var(--prev-color-primary) !important;
  }

  ::v-deep .is-current .custom-tree-node {
    color: var(--prev-color-primary) !important;
  }

  ::v-deep .el-tree--highlight-current .el-tree-node.is-current>.el-tree-node__content {
    background-color: var(--prev-color-primary-light-9) !important;
    border-right: 2px solid var(--prev-color-primary);
  }
}
.showOn {
  color: #2d8cf0;
  background: #f0faff;
  z-index: 2;
}
.text-blue{
  color: var(--prev-color-primary);
}

::v-deep .ivu-menu-vertical .ivu-menu-item-group-title {
  display: none;
}

::v-deep .ivu-menu-vertical.ivu-menu-light:after {
  display: none;
}

.left-wrapper,
.right-wrapper {
  min-height: 87.7vh;
}

.left-wrapper {
  // height: 920px;
  background: #fff;
  border-right: 1px solid #f2f2f2;
}
.w-420 {
  width: 420px;
}
.words-tag {
  background-color: #f4f4f4;
  display: inline-block;
  padding: 0 10px;
  font-size: 12px;
  color: #4f4f4f;
  border-radius: 4px;
  box-sizing: border-box;
  white-space: nowrap;
  height: 28px;
  line-height: 26px;
}
.tag-img {
  display: block;
  height: 28px;
  object-fit: cover;
  border-radius: 4px;
}
.menu-item {
  z-index: 50;
  position: relative;
  display: flex;
  justify-content: space-between;
  word-break: break-all;

  .icon-box {
    z-index: 3;
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
  }

  &:hover .icon-box {
    display: block;
  }

  .right-menu {
    z-index: 10;
    position: absolute;
    right: -106px;
    top: -11px;
    width: auto;
    min-width: 121px;
  }
}
.tip {
  color: #888;
  font-size: 12px;
  line-height: 16px;
}
.upload-select {
  width: 64px;
  height: 64px;
  font-size: 32px !important;
  background: #f5f5f5;
  color: #ccc;
  margin-bottom: 6px;
}
.upload-item {
  position: relative;
  display: inline-block;
  width: 64px;
  height: 64px;
  border-radius: 4px;
  margin: 0 15px 10px 0;
  img {
    width: 64px;
    height: 64px;
    border-radius: 4px;
    vertical-align: middle;
  }
  .close {
    cursor: pointer;
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    margin: -10px -10px 0 0;
    background-color: #aaa;
    color: #fff;
  }
}
.row-right{
  display: flex;
  justify-content: flex-end;
}

.table-container {
  height: 646px;
}
</style>

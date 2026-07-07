<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div class="head acea-row row-between-wrapper">版权信息</div>
      <el-table ref="table" size="small" :data="copyrightTableData" empty-text="暂无数据">
        <el-table-column label="文字版权信息" minWidth="180">
          <template slot-scope="scope"> {{ scope.row.copyrightContext }} </template>
        </el-table-column>
        <el-table-column label="底部版权图片" minWidth="180">
          <template slot-scope="scope">
            <div class="uploadPictrue" v-viewer v-show="scope.row.copyrightImage">
              <img v-lazy="scope.row.copyrightImage" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="180">
          <template slot-scope="scope">
            <el-button type="text" size="small" class="btn cup" @click="editCopyright()">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <el-card v-for="(value, key, index) in tableList" :key="index" :bordered="false" shadow="never" class="ivu-mt mt16">
      <div class="head acea-row row-between-wrapper">{{ key | headText }}</div>
      <el-table ref="table" size="small" :data="tableList[key]" empty-text="暂无数据">
        <el-table-column :label="key == 'permissions' ? '文件/目录' : '环境'" minWidth="180">
          <template slot-scope="scope">{{ scope.row.name }} </template>
        </el-table-column>
        <el-table-column label="要求" minWidth="180">
          <template slot-scope="scope">
            <span>{{ scope.row.require }} </span>
            <el-tooltip placement="top" v-if="key == 'process' && !scope.row.value">
              <div slot="content" v-html="trips[scope.$index].message"></div>
              <i class="el-icon-warning-outline"></i>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="180">
          <template slot-scope="scope">
            <span v-if="typeof scope.row.value === 'boolean'">
              <i v-if="scope.row.value === true" class="el-icon-check"></i>
              <i v-else class="el-icon-close"></i>
            </span>
            <span v-else>{{ scope.row.value }}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog :visible.sync="modalCopyright" title="版权信息" width="550px">
      <div class="auth">
        <div class="update">修改版权信息:</div>
        <el-input style="width: 460px" v-model="copyrightText" />
      </div>
      <div class="auth">
        <div class="update">上传版权图片:</div>
        <div>
          <div class="uploadPictrue" v-if="authorizedPicture" @click="modalPicTap('单选')">
            <img v-lazy="authorizedPicture" />
            <i class="el-icon-error" @click.stop="authorizedPicture = ''"></i>
          </div>
          <div class="upload" v-else @click="modalPicTap('单选')">
            <div class="iconfont">+</div>
          </div>
          <div class="tips-info">建议尺寸：宽290px*高100px</div>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="modalCopyright = false">取 消</el-button>
        <el-button type="primary" @click="saveCopyRight">保存</el-button>
      </span>
    </el-dialog>

  </div>
</template>
<script>
import { auth } from '@/api/system';
import { getAuthApi, saveCrmebCopyRight } from '@/api/maintain'
export default {
  name: 'system_auth',
  computed: {},
  data() {
    return {
      copyrightText: '',
      authorizedPicture: '',
      modalCopyright: false,
      copyrightTableData: [],
      tableList: [],
      trips: [
        {
          title: '温馨提示',
          message:
            '您的【长连接】未开启，没有开启会导致系统默认客服无法使用,后台订单通知无法收到。请尽快执行命令开启！！',
        },
        {
          title: '温馨提示',
          message:
            '您的【定时任务】未开启，没有开启会导致自动收货、未支付自动取消订单、订单自动好评、拼团到期退款等任务无法正常执行。请尽快执行命令开启！！',
        },
        {
          title: '温馨提示',
          message:
            '您的【消息队列】未开启，没有开启会导致异步任务无法执行。请尽快执行命令开启！！',
        },
      ],
    };
  },
  filters: {
    headText(z) {
      if (z === 'server') {
        return '服务器信息';
      } else if (z === 'environment') {
        return '系统环境要求';
      } else if (z === 'permissions') {
        return '权限状态';
      } else if (z === 'process') {
        return '启动进程';
      }
    },
  },
  components: {},
  mounted() {
    this.getInfo();
    this.getAuthData();
  },
  methods: {
    editCopyright() {
      this.modalCopyright = true;
    },
    // 保存版权信息
    saveCopyRight() {
      saveCrmebCopyRight({
        copyright_context: this.copyrightText,
        copyright_image: this.authorizedPicture,
      }).then((res) => {
        this.modalCopyright = false;
        return this.$message.success(res.message)
      }).catch(({ message }) => {
        this.$message.error(message);
      });
    },
    // 选择图片
    modalPicTap() {
      const _this = this;
      this.$modalUpload(function (img) {
        _this.authorizedPicture = img[0];
      });
    },
    getInfo() {
      auth()
        .then((res) => {
          this.tableList = res.data
        })
        .catch((err) => {
          this.$message.error(err.message);
        });
    },
    // 获取版权信息
    getAuthData() {
      getAuthApi().then(res => {
        const data = res.data || {}
        this.copyrightText = data.copyright_context || ''
        this.authorizedPicture = data.copyright_image || ''
        this.copyrightTableData = [
          {
            copyrightContext: this.copyrightText,
            copyrightImage: this.authorizedPicture,
          },
        ];
      })
    },
  },
  destroyed: {},
};
</script>
<style scoped lang="scss">
.auth {
  padding: 9px 16px 9px 10px;
  display: flex;

  .box {
    width: 50px;
  }

  .update {
    white-space: nowrap;
    margin-bottom: 12px;
  }

  .upload {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 4px;
    border: 1px solid #dddddd;
  }

  .tips-info {
    margin-top: 10px;
    color: #999;
    font-size: 12px;
  }
}

.uploadPictrue {
  width: 60px;
  height: 60px;
  border-radius: 4px;
  position: relative;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
    border-radius: 4px;
  }

  .el-icon-error {
    position: absolute;
    top: -3px;
    right: -3px;
    color: #999999;
  }
}

.head {
  font-weight: 400;
  font-size: 14px;
  color: #303133;
  margin-bottom: 20px;
}

.el-icon-check {
  color: #1890ff;
  font-size: 22px;
  font-weight: 600;
}

.el-icon-close {
  color: #f5222d;
  font-size: 22px;
  font-weight: 600;
}

.el-icon-warning-outline {
  font-size: 13px;
}
</style>

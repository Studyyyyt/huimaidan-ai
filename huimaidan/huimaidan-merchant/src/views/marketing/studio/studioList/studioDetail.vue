<template>
  <div class="divBox">
    <el-drawer v-if="dialogVisible" title="直播间信息" :visible.sync="dialogVisible" size="800px">
      <div v-loading="loading">
        <el-tabs type="border-card" v-model="activeName">
          <el-tab-pane label="直播间信息" name="basic">
            <div class="section">
              <div class="title">用户信息</div>
              <ul class="list">
                <li class="item">
                  <div>直播间名称：</div>
                  <div class="value">{{FormData.name}}</div>
                </li>
                <li class="item">
                  <div>主播微信号：</div>
                  <div class="value">{{FormData.anchor_wechat}}</div>
                </li>
                 <li class="item">
                  <div>直播间ID：</div>
                  <div class="value">{{FormData.broadcast_room_id}}</div>
                </li>
                <li class="item">
                  <div>主播昵称：</div>
                  <div class="value">{{FormData.anchor_name}}</div>
                </li>
                <li class="item">
                  <div>手机号：</div>
                  <div class="value">{{FormData.phone}}</div>
                </li>
                <li class="item">
                  <div>审核结果：</div>
                  <div class="value">{{FormData.status | liveReviewStatusFilter}}</div>
                </li>
                <li class="item">
                  <div>直播开始时间：</div>
                  <div class="value">{{FormData.start_time}}</div>
                </li>
                <li class="item">
                  <div>直播结束时间：</div>
                  <div class="value">{{FormData.end_time}}</div>
                </li>
                 <li class="item">
                  <div>直播间类型：</div>
                  <div class="value">{{FormData.type | broadcastType}}</div>
                </li>
                <li v-if="FormData.type == 1" class="item">
                  <div>显示类型：</div>
                  <div class="value">{{FormData.screen_type | broadcastDisplayType}}</div>
                </li>
                 <li v-if="FormData.type == 1 && FormData.push_url" class="item">
                  <div>推流地址：</div>
                  <div class="value">{{FormData.push_url}}</div>
                  <el-button class="copyBtn" type="primary" plain @click="handleClipboard(FormData.push_url,$event)">复制</el-button>
                </li>
                <li class="item">
                  <div>背景图：</div>
                  <div class="value"><img style="max-width: 150px; height: 80px;" :src="FormData.cover_img" /></div>
                </li>
                <li class="item">
                  <div>分享图：</div>
                  <div class="value"><img style="max-width: 150px; height: 80px;" :src="FormData.share_img" /></div>
                </li>
              </ul>
              <div class="item">
                <div class=""></div>
              </div>
             </div>
             <div class="section">
              <div class="title">操作信息</div>
              <ul class="list">
                <li class="item">
                  <div>是否开启点赞：</div>
                  <div class="value theme">{{FormData.close_like ? "✖" : "✔"}}</div>
                </li>
                <li class="item">
                  <div>是否开启货架：</div>
                  <div class="value theme">{{FormData.close_goods ? "✖" : "✔"}}</div>
                </li>
                <li class="item">
                  <div>是否开启评论：</div>
                  <div class="value theme">{{FormData.close_comment ? "✖" : "✔"}}</div>
                </li>
                <li class="item">
                  <div>是否开启直播回放：</div>
                  <div class="value theme">{{FormData.replay_status ? "✔" : "✖"}}</div>
                </li>
                <li class="item">
                  <div>是否开启分享：</div>
                  <div class="value theme">{{FormData.close_share ? "✖" : "✔"}}</div>
                </li>
                <li class="item">
                  <div>是否开启客服：</div>
                  <div class="value theme">{{FormData.close_kf ? "✖" : "✔"}}</div>
                </li>
                <li class="item">
                  <div>是否开启官方收录：</div>
                  <div class="value theme">{{FormData.is_feeds_public ? "✔" : "✖"}}</div>
                </li>
              </ul>
              <template v-if="isEdit">
                <div class="item">
                  <div>直播列表推荐：</div>
                  <div class="value">
                    <el-rate class="rate_star"
                      v-model="FormData.star"
                      :colors="colors">
                    </el-rate>
                  </div>
                </div>
                <div class="item">
                  <div>备注：</div>
                  <div class="value">
                    <el-input v-model="FormData.mark" type="textarea" :rows="1" />
                    <el-button size="small" type="primary" :disabled="FormData.mark == ''" @click="handleRemarks">提交</el-button>
                  </div>
                </div>
              </template>
            </div>
          </el-tab-pane>
          <el-tab-pane v-if="FormData.status === 2" label="商品信息" name="product">
            <div class="section">
              <div class="title mb20">已导入的直播商品：</div>
               <el-button v-if="type === 'edit'" size="small" type="primary" class="mr10" @click="handleImport(FormData)">导入直播商品</el-button>
               <selected-goods ref="selectedGoods" :type="type" :broadcast_room_id="FormData.broadcast_room_id" />
            </div>
          </el-tab-pane>
         </el-tabs>
      </div>
    </el-drawer>
    <!--导入直播商品-->
    <import-goods ref="uploadGoods" @getList="getList"/>
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
import { broadcastDetailApi, broadcastRemarksApi } from '@/api/marketing'
import selectedGoods from './selectedGoods'
import importGoods from '@/components/importGoods/index'
import clipboard from '@/utils/clipboard'
export default {
  name: 'StudioDetail',
  components: {
    selectedGoods,
    importGoods
  },
  data() {
    return {
      activeName: "basic",
      dialogVisible: false,
      option: {
        form: {
          labelWidth: '150px'
        }
      },
      FormData: {},
      loading: false,
      type: ''
    }
  },
  mounted() {

  },
  methods: {
    getData(id, type) {
      this.loading = true
      this.type = type
      broadcastDetailApi(id)
        .then((res) => {
          this.FormData = res.data
          this.loading = false
        })
        .catch((res) => {
          this.$message.error(res.message)
          this.loading = false
        })
    },
    // 导入
    handleImport(row) {
      this.$refs.uploadGoods.importVisible = true
      this.$refs.uploadGoods.broadcast_id = row.broadcast_room_id
      this.$refs.uploadGoods.broadcast_room = row.name
      this.$refs.uploadGoods.image = ''
      localStorage.setItem('liveRoom_id', row.broadcast_room_id)
    },
    handleRemarks(id) {
      broadcastRemarksApi(this.FormData.broadcast_room_id, this.FormData.mark)
        .then((res) => {
          this.loading = false
          this.$message.success(res.message)
          this.dialogVisible = false
          this.$emit('getList')
        })
        .catch((res) => {
          this.$message.error(res.message)
          this.loading = false
        })
    },
    getList() {
      this.$refs.selectedGoods.getList()
    },
    handleClipboard(text, event) {
      clipboard(text, event)
    }
  }
}
</script>

<style lang="scss" scoped>
.section {
  padding: 30px 10px;
  border-bottom: 1px dashed #eeeeee;
  &:first-child{
    padding-top: 15px;
  }
  &:last-child{
    border: none;
  }
  .title {
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
    font-size: 15px;
    line-height: 15px;
    color: #303133;
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
  }
  .item {
    flex: 0 0 calc(100% / 2);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    align-items: center;
  }
  .value {
    flex: 1;
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }

  }
  .theme{
    color: var(--prev-color-primary);
  }
}
.el-tabs--border-card{
  box-shadow: none;
  border: none;
}
</style>

<template>
  <div>
    <el-card class="card">
      <el-tabs v-model="activeName">
        <el-tab-pane label="基础信息" name="first"></el-tab-pane>
        <el-tab-pane label="登录注册" name="second"></el-tab-pane>
      </el-tabs>
      <el-form v-if="activeName == 'first'" :model="basicsForm" label-width="120px">
        <el-row :gutter="24">
          <el-col :span="24" class="mt10">
            <el-form-item label="用户默认头像：">
              <div
                style="display: inline-block;"
                @click="modalPicTap('1')"
              >
                <div v-if="authorizedPicture" class="uploadPictrue">
                  <img :src="authorizedPicture">
                </div>
                <div v-else class="uploadPictrue">
                  <i class="iconfont iconshangpinshuliang-jia" />
                </div>
              </div>
              <div class="tip">建议尺寸：120*120px</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10">
            <el-form-item label="用户信息设置：">
              <!-- 用户表格 -->
              <el-table
                :data="listOne"
                ref="table"
                class="goods"
                row-key="id"
                highlight-current-row
                :draggable="true"
              >
                <el-table-column
                  label="#"
                  min-width="50"
                >
                <template>
                  <i class="iconfont-diy icondrag"></i>
                </template>
                </el-table-column>
                <el-table-column
                  label="信息名称"
                  prop="title"
                  min-width="80"
                />
                <el-table-column
                  label="使用"
                  min-width="80"
                >
                  <template slot-scope="scope">
                    <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_used" />
                  </template>
                </el-table-column>
                <el-table-column
                  label="必填"
                  min-width="80"
                >
                  <template slot-scope="scope">
                    <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_require" :disabled="scope.row.is_used==0" />
                  </template>
                </el-table-column>
                <el-table-column
                  label="用户端显示"
                  min-width="80"
                >
                  <template slot-scope="scope">
                    <el-checkbox :true-label="1"  :false-label="0" v-model="scope.row.is_show" :disabled="scope.row.is_used==0" />
                  </template>
                </el-table-column>
                <el-table-column
                  label="信息格式"
                  min-width="150"
                >
                  <template slot-scope="scope">
                    <span>{{scope.row.type_name}}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  label="提示信息"
                  prop="msg"
                  min-width="150"
                />
                <el-table-column label="操作" min-width="60" fixed="right">
                  <template slot-scope="scope">
                    <el-button type="text" size="small" v-if="scope.row.is_default!=1" @click="delInfo(scope.row.id)">删除</el-button>
                  </template>
                </el-table-column>
              </el-table>
              <div class="tip goods">
                1.开启使用后，后台添加用户时可填写此信息；开启必填后，后台添加用户时此信息必须填写；开启用户端展示后，在商城用户个人信息中展示
                <br/>
                2.自定义添加日期和单选格式的字段，暂不支持用户列表搜索，如业务需要建议进一步开发；其它字段均支持用户列表搜索
              </div>
              <el-button type="default" size="small" class="addInfo" @click="addModel = true">新增信息</el-button>
              <div class="mt20">
                <el-button type="primary" size="small" @click="handleSubmit()">保存</el-button>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form v-else-if="activeName == 'second'" :model="loginForm" size="small" label-width="150px">
        <el-row :gutter="24">
          <el-col :span="24" class="mt10">
            <el-form-item label="" label-width="0">
              <div class="title">登录设置</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item label="强制手机号绑定：">
              <el-switch v-model="loginForm.is_phone_login" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
              <div class="tip">开启，商城登录时强制手机号登录绑定</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item label="获取头像昵称：">
              <el-switch v-model="loginForm.first_avatar_switch" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
              <div class="tip">开启，小程序首次登录弹出获取头像昵称弹窗；关闭，则不弹出</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item label="修改头像和昵称：">
              <el-switch v-model="loginForm.open_update_info" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
              <div class="tip">开启，用户可自主修改头像或昵称；关闭，则不能修改</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item label="手机号快速验证组件：">
              <el-switch v-model="loginForm.wechat_phone_switch" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
              <div class="tip">开启，使用微信手机号快速验证组件；关闭，则不使用</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10">
            <el-form-item label="" label-width="0">
              <div class="title">注册有礼</div>
            </el-form-item>
          </el-col>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item label="注册有礼启用：">
              <el-switch v-model="loginForm.newcomer_status" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
              <div class="tip">新用户注册后，会给用户赠送礼品</div>
            </el-form-item>
          </el-col>
          <template v-if="loginForm.newcomer_status==1">
            <el-col :span="24" class="mt10 ml30">
              <el-form-item label="注册有礼弹窗：">
                <div
                  class="upLoadPicBox"
                  style="display: inline-block;"
                >
                  <div v-if="loginForm.register_popup_pic" class="pictrue">
                    <img :src="loginForm.register_popup_pic">
                    <i class="el-icon-error btndel" @click.stop="loginForm.register_popup_pic = ''" />
                  </div>
                  <div v-else class="upLoad" @click="modalPicTap('2')">
                    <i class="iconfont iconshangpinshuliang-jia" />
                  </div>
                </div>
                <div class="acea-row">
                  <div class="tip">建议尺寸：270p*426px，</div>
                  <div class="tip">
                    如不上传此图，则默认使用系统样式
                    <el-popover
                      placement="right"
                      title=""
                      min-width="200"
                      trigger="hover"
                      >
                      <img :src="`${baseURL}/statics/system/register_pic.jpg`" style="height:270px;" alt="">
                      <el-button type="text" slot="reference">查看示例</el-button>
                  </el-popover>
                  </div>
                </div>
              </el-form-item>
            </el-col>
            <el-col :span="24" class="mt10 ml30">
              <el-form-item label="赠送余额：">
                <el-switch v-model="loginForm.register_money_status" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
                <div v-if="loginForm.register_money_status==1" class="mt10">
                  <el-input-number
                    :rules="{
                    required: true,
                    message: '请输入赠送金额',
                    trigger: 'change',
                  }"
                    v-model="loginForm.register_give_money"
                    type="number"
                    :precision="2"
                    :min="0"
                    :max="99999"
                    controls-position="right"
                    class="selWidth"
                  />
                  <div class="tip">新用户注册即奖励充值余额，大于或等于0（0为不赠送）</div>
                </div>
              </el-form-item>
              <el-form-item label="赠送积分：">
                <el-switch v-model="loginForm.register_integral_status" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
                <div v-if="loginForm.register_integral_status==1" class="mt10">
                  <el-input-number
                    :rules="{
                      required: true,
                      message: '请输入赠送积分',
                      trigger: 'change',
                    }"
                    v-model="loginForm.register_give_integral"
                    type="number"
                    :precision="2"
                    :min="0"
                    :max="99999"
                    controls-position="right"
                    class="selWidth"
                  />
                  <div class="tip">新用户注册即奖励积分，大于或等于0（0为不赠送）</div>
                </div>
              </el-form-item>
              <el-form-item label="赠送优惠券：">
                <el-switch v-model="loginForm.register_coupon_status" :active-value="1" :inactive-value="0" :width="55" active-text="开启" inactive-text="关闭" />
                <div class="tip">新用户注册后即赠送优惠券</div>
                <div v-if="loginForm.register_coupon_status" class="mt10">
                  <el-table
                    :data="couponData"
                    ref="table"
                    class="goods"
                    row-key="id"
                    highlight-current-row
                  >
                    <el-table-column
                      label="优惠券名称"
                      prop="title"
                      min-width="80"
                    />
                    <el-table-column
                      label="优惠劵类型"
                      min-width="100"
                    >
                      <template slot-scope="{ row }">
                        <span>{{ row.type | couponTypeFilter }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      prop="coupon_price"
                      label="优惠券面值"
                      min-width="90"
                    />
                    <el-table-column
                      label="最低消费额"
                      min-width="90"
                    >
                      <template slot-scope="scope">
                        <span>{{ scope.row.use_min_price===0?'不限制':scope.row.use_min_price }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column
                      label="有效期限"
                      min-width="150"
                    >
                      <template slot-scope="scope">
                        <span>{{ scope.row.coupon_type===1?scope.row.use_start_time+'-'+scope.row.use_end_time:scope.row.coupon_time+'天' }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column label="操作" min-width="60" fixed="right">
                      <template slot-scope="scope">
                        <el-button type="text" size="small" @click="delCoupon(scope.$index)">删除</el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
                <el-button v-if="loginForm.register_coupon_status" type="text" size="small" class="mt10" @click="addCoupon">+添加优惠券</el-button>
              </el-form-item>
            </el-col>
          </template>
          <el-col :span="24" class="mt10 ml30">
            <el-form-item>
              <el-button type="primary" size="small" @click="submitForm()">提交</el-button>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
    <!-- 新增信息 -->
    <el-dialog
      :visible.sync="addModel"
      title="新增信息"
      class-name="vertical-center-modal"
      scrollable
      width="630px"
      @close="cancelSubmit"
    >
      <el-form
        ref="formValidate"
        :model="formItem"
        :rules="ruleValidate"
        size="small"
        label-width="90px"
      >
        <el-row>
          <el-col>
            <el-form-item label="字段名：" prop="field">
              <el-input
                v-model="formItem.field"
                placeholder="以英文开头的字母、数字、下划线组合，用于代码中筛选信息名称，在后台前端不展示"
                class="width100"
              />
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="信息名称：" prop="title">
              <el-input
                v-model="formItem.title"
                placeholder="请输入信息名称"
                class="width100"
              />
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="信息格式 ：" prop="type">
              <el-select v-model="formItem.type" class="width100">
                <el-option
                  v-for="item in formatList"
                  :value="item.value"
                  :label="item.label"
                  :key="item.value"
                >
                  {{ item.label }}
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item
              label="单选项 ："
              prop="content"
              v-if="formItem.type === 'radio'"
            >
              <div class="arrbox">
                <el-tag
                  @close="handleClose"
                  :name="item"
                  :closable="true"
                  v-for="(item, index) in formItem.content"
                  :key="index"
                >
                  {{ item }}
                </el-tag>
                <input
                  size="small"
                  class="arrbox_ip width100"
                  v-model="single"
                  placeholder="请输入选项，回车确认"
                  @keyup.enter="addlabel"
                />
              </div>
            </el-form-item>
          </el-col>
          <el-col>
            <el-form-item label="提示文案：" prop="msg">
              <el-input
                v-model="formItem.msg"
                placeholder="请输入提示文案"
                class="width100"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="cancelSubmit">取 消</el-button>
        <el-button size="small" type="primary" @click="addSubmit">确 定</el-button>
      </span>
    </el-dialog>
     <!-- 选择优惠券 -->
    <el-dialog title="优惠券列表" :visible.sync="visibleCoupon" width="1000px">
      <coupon-List ref="couponList" :newGift="true" :checkedData="couponData" :checkedIds="[]" @sendSuccess="sendSuccess" @close="visibleCoupon=false" />
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
import { settingUser, addSetting, setUser, userSetDelApi, userlogInCouponApi, userlogInPost } from '@/api/user.js'
import { getSysConfig } from '@/api/system';
import SettingMer from '@/libs/settingMer'
import couponList from '../list/couponList'
export default {
  name: 'setupUser',
  components: {couponList},
  props: {},
  data() {
    const validatorSingle = (rule, value, callback)=>{
      if(value.length<2){
        callback(new Error('单选项最少输入2个'));
      }else{
        callback();
      }
    };
    const validatorActive = (rule, value, callback)=>{
      if(value===""||value == null || value<0){
        callback(new Error('活动价不能为空'));
      }else{
        callback();
      }
    };
    const validatorFiled = (rule, value, callback)=>{
      if(value===""||value == null||!value){
        callback(new Error('字段名不能为空'));
      } else if (!/^[a-z][a-z0-9_]*$/.test(value)) {
        callback(new Error('格式不正确!'))
      } else{
        callback();
      }
    };
    return {
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      paySwitch: 1,
      phoneSwitch: 1,
      indexCoupon: 0,
      val: '',
      formActive:{
        activeInput: 0
      },
      basicsForm: {},
      loginForm: {
        register_popup_pic: ""
      },
      selectArr: [],
      value: '',
      formItem: {
        title: '',
        type_name: '',
        msg: '',
        content: [],
      },
      single: '',
      activityShow: false,
      isChoice: '单选',
      modalPic: false,
      loading: false,
      addModel: false,
      authorizedPicture: '', // 图片
      isShow: false,
      formatList: [
        {
          value: 'input',
          label: '文本',
        },
        {
          value: 'int',
          label: '数字',
        },
        {
          value: 'date',
          label: '日期',
        },
        {
          value: 'radio',
          label: '单选项',
        },
        {
          value: 'id_card',
          label: '身份证',
        },
        {
          value: 'email',
          label: '邮件',
        },
        {
          value: 'phone',
          label: '手机号',
        },
        {
          value: 'address',
          label: '地址',
        },
      ],
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
      listOne: [],
      couponData: [],
      ruleValidate: {
        field: [
          {
            required: true,
            validator: validatorFiled,
            trigger: 'blur',
          },
        ],
        title: [
          {
            required: true,
            message: '信息名称不能为空',
            trigger: 'blur',
          },
        ],
        type: [
          {
            required: true,
            message: '信息格式不能为空',
            trigger: 'blur',
          },
        ],
        msg: [
          {
            required: true,
            message: '信息文案不能为空',
            trigger: 'blur',
          },
        ],
        content: [
          { required: true, validator:validatorSingle,type: 'array', trigger: 'blur' },
        ],
      },
      ruleActive:{
        activeInput: [
          {
            required: true,
            validator:validatorActive,
            trigger: 'blur'
          },
        ]
      },
      couponType: 0,
      vipCopon: [],
      activeName: "first",
      visibleCoupon: false,
    }
  },
  computed: {},
  created() {
    this.settingUser()
    this.getUserLogIn()

  },
  mounted() {

  },
  methods: {
    // 获取用户配置
    settingUser() {
      settingUser().then((res) => {
        this.authorizedPicture = res.data.avatar
        this.listOne = res.data.list
      })
    },
    // 获取用户登录注册信息
    getUserLogIn(){
      getSysConfig("user_register").then((res) => {
        let data = res.data.config_value
        this.loginForm = {
          is_phone_login: Number(data.is_phone_login) || 0,
          open_update_info: Number(data.open_update_info) || 0,
          first_avatar_switch: Number(data.first_avatar_switch) || 0,
          wechat_phone_switch: Number(data.wechat_phone_switch) || 0,
          newcomer_status: Number(data.newcomer_status) || 0,
          register_integral_status: Number(data.register_integral_status) || 0,
          register_give_integral: Number(data.register_give_integral) || 0,
          register_money_status: Number(data.register_money_status) || 0,
          register_give_money: data.register_give_money || 0,
          register_coupon_status: Number(data.register_coupon_status) || 0,
          register_popup_pic: data.register_popup_pic || "",
          register_give_coupon: data.register_give_coupon
        }
        userlogInCouponApi().then((res) => {
          this.couponData = res.data.list
        })
      })
    },
    cancel(name){
      this.activityShow = false
      this.$refs[name].resetFields();
    },
    addCoupon(){
      this.visibleCoupon = true;
      this.$nextTick(function(){
        this.$refs.couponList.getList('');
      })
    },
    sendSuccess(checkedData, couponIds){
      this.couponData = checkedData
      this.loginForm.register_give_coupon = couponIds
      this.visibleCoupon = false;
    },
    // 选择图片
    modalPicTap(num) {
      const _this = this;
        this.$modalUpload(function (img) {
          if(num == 1){
            _this.authorizedPicture = img[0];
          }else{
            _this.loginForm.register_popup_pic = img[0];
          }

      },'');
    },
    // 取消新增信息
    cancelSubmit() {
      this.formItem = {
        title: '',
        type_name: '',
        msg: '',
        content: [],
      }
      this.addModel = false
      this.$refs.formValidate.resetFields()
    },
    // 提交信息
    addSubmit() {
      this.$refs.formValidate.validate((valid) => {
        let obj = {
          ...this.formItem,
          is_required: 0,
          is_used: 0,
          is_show: 0,
        };
        switch (obj.type) {
          case 'input':
            obj.type_name = '文本';
            break;
          case 'int':
            obj.type_name = '数字';
            break;
          case 'date':
            obj.type_name = '日期';
            break;
          case 'radio':
            obj.type_name = '单选项';
            break;
          case 'id_card':
            obj.type_name = '身份证';
            break;
          case 'email':
            obj.type_name = '邮件';
            break;
          case 'phone':
            obj.type_name = '手机号';
            break;
          case 'address':
            obj.type_name = '地址';
            break;
        }
        let labelName = [];
        this.listOne.forEach(item=>{
          labelName.push(item.info)
        });
        if (valid) {
          if(labelName.indexOf(obj.title) == -1){
            // this.listOne.push(obj)
            addSetting(obj).then((res) => {
              this.$message.success(res.message)
              this.settingUser()
            }).catch((err) => {
              this.$message.error(err.message)
            })
            this.cancelSubmit();
          }else{
            this.$message.error('该信息已经添加过')
          }
        }
      })
    },
    // 信息删除
    delInfo(id) {
      this.$modalSure('确定删除该条数据').then(() => {
        userSetDelApi(id)
          .then(res => {
            this.$message.success(res.message)
            this.settingUser();
          })
          .catch(res => {
            this.$message.error(res.message)
          })
      })
    },
    //删除优惠券
    delCoupon(idx){
      this.couponData.splice(idx, 1)
      this.loginForm.register_give_coupon = this.couponData.map((item) => {
        return item.coupon_id;
      });
    },
    // 输入后回车
    addlabel() {
      if (!this.single) {
        return
      }
      let count = this.formItem.content.indexOf(this.single)
      if (count === -1) {
        this.formItem.content.push(this.single)
      }
      this.single = ''
    },
    // 表单提交
    handleSubmit() {
     let data = {
        avatar: this.authorizedPicture,
        user_extend_info: this.listOne
      }
      setUser(data).then((res) => {
        this.$message.success(res.message)
      })
    },
    handleClose(event, name) {
      const index = this.formItem.content.indexOf(name)
      this.formItem.content.splice(index, 1)
    },
    //提交登录注册信息
    submitForm(){
      userlogInPost(this.loginForm).then((res) => {
        this.$message.success(res.message)
      }).catch(res => {
        this.$message.error(res.message)
      })
    }

  },
}
</script>
<style scoped lang="scss">
.goods{
  .icondrag{
    color: #ccc;
  }
}
::v-deep .card .el-input__inner {
  text-align: left;
}
::v-deep .el-table__cell{
  line-height: 21px;
  font-size: 13px;
}
::v-deep .el-form-item__label {
  font-size: 12px;
  font-weight: 400;
  color: #333333;
}

.uploadPictrue {
  width: 60px;
  height: 60px;
  background: #F5F5F5;
  border-radius: 4px;
  border: 1px dashed #DDDDDD;
  text-align: center;
  line-height: 60px;
  img {
    width: 100%;
    height: 100%;
  }
}
.addInfo {
  width: 78px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid rgba(151, 151, 151, 0.36);
  text-align: center;
  line-height: 32rpx;
  font-size: 12px;
  font-weight: 400;
  color: rgba(0, 0, 0, 0.85);
  margin-top: 20px;
  cursor:pointer;
}
.footer {
  width: 100%;
  height: 65px;
  background: #FFFFFF;
  position: fixed;
  right: 0;
  bottom: 0;
  left: 200px;
  z-index: 10;
  .btn {
    margin-left: 40%;
  }
}
.title{
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary);
  font-size: 15px;
  line-height: 15px;
  color: #303133;
}
.ml30{
  margin-left: 20px;
}
.tip {
  color: #909399;
  font-size: 12px;
}
</style>

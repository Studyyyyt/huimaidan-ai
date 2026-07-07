<template>
  <div>
    <el-drawer
      :with-header="false"
      :visible.sync="drawer"
      size="1100px"
      :direction="direction"
      :before-close="handleClose"
    >
      <div v-loading="loading">
        <div class="head">
          <div class="full">
            <img class="order_icon" :src="orderImg" alt="" />
            <div class="text">
              <div class="title">{{ productData.store_name }}</div>
              <div>
                <span class="mr20">商品ID：{{ productData.product_id }}</span>
              </div>
            </div>
            <div>
              <el-button
                v-if="isEdit"
                size="small"
                @click="cancelEdit"
                >取消</el-button
              >
              <el-button
                v-if="!isEdit && (productData.status == 0 || productData.status == 1) && productData.is_del === 0"
                type="primary"
                size="small"
                @click="handleEdit"
                >编辑</el-button
              >
              <el-button
                v-if="isEdit"
                type="success"
                size="small"
                @click="handleSubmit('formValidate')"
                >完成</el-button
              >
            </div>
          </div>
          <ul class="list">
            <li class="item">
              <div class="title">类型</div>
              <div>{{ productData.type == 0 ? '普通商品' : productData.type == 1 ? '虚拟商品' : productData.type == 4 ? '预约商品' :
                '卡密商品' }}</div>
            </li>
            <li class="item">
              <div class="title">状态</div>
              <div>{{ productData.us_status | productStatusFilter }}</div>
            </li>
            <li class="item">
              <div class="title">销量</div>
              <div>{{ productData.sales }}</div>
            </li>
            <li class="item">
              <div class="title">库存</div>
              <div>{{ productData.stock }}</div>
            </li>
            <li class="item">
              <div class="title">创建时间</div>
              <div>{{ productData.create_time }}</div>
            </li>
          </ul>
        </div>
        <el-tabs type="border-card" v-model="activeName" @tab-click="tabClick">
          <el-tab-pane label="基本信息" name="basic">
            <div class="section">
              <div class="title">基本信息</div>
              <ul class="list">
                <li class="item item100">
                  <div class="item-title">封面图：</div>
                  <img :src="productData.image" style="width:40px;height:40px;margin-right:12px;"/>
                </li>
                <li class="item item100">
                  <div class="item-title">轮播图：</div>
                  <img v-for="(pic,idx) in productData.slider_image" :key="idx" :src="pic" style="width:40px;height:40px;margin-right:12px;"/>
                </li>
              </ul>
              <li class="item item100">
                <div class="item-title">商品简介：</div>
                  <div class="value">{{productData.store_info}}</div>
                </li>
              <ul class="list">
                <li class="item">
                  <div class="item-title">平台分类：</div>
                  <div class="value">{{productData.storeCategory&&productData.storeCategory.cate_name || '-'}}</div>
                </li>
                <li v-if="productData.merCateId&&productData.merCateId.length>0" class="item">
                  <div class="item-title">店铺分类：</div>
                  <div class="value">
                    <span v-for="(item,index) in productData.merCateId" :key="index">{{item.category&&item.category.cate_name}}&nbsp;&nbsp;&nbsp;&nbsp;</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">商品标签：</div>
                  <div v-if="(productData.mer_labels&&productData.mer_labels_data.length) || (productData.sys_labels_data&&productData.sys_labels_data.length)" class="value">
                    <template v-if="productData.mer_labels_data&&productData.mer_labels_data.length">
                      <span v-for="(item,index) in productData.mer_labels_data" :key="index" class="value-item"> {{item}} </span>
                    </template>
                    <template v-if="productData.sys_labels_data&&productData.sys_labels_data.length">
                      <span v-for="(item,index) in productData.sys_labels_data" :key="index" class="value-item"> {{item}} </span>
                    </template>
                  </div>
                  <div v-else class="value"><span>-</span></div>
                </li>
                <li class="item">
                  <div class="item-title">品牌选择：</div>
                  <div class="value">{{productData.brand&&productData.brand.brand_name || '其它'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">单位：</div>
                  <div class="value">{{productData.unit_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">关键字：</div>
                  <div class="value">{{productData.keyword || '-'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">配送方式：</div>
                  <template v-if="productData.type==0">
                    <div v-if="productData.delivery_way.length==2" class="value">快递/到店自提</div>
                    <div v-else-if="productData.delivery_way.length==1">{{productData.delivery_way[0]==1 ? "到店自提" : "快递"}}</div>
                  </template>
                  <template v-else>
                    <div v-if="productData.type == 1" class="value">虚拟发货</div>
                    <div v-else-if="productData.type == 2" class="value">卡密发货</div>
                  </template>
                </li>
              </ul>
              <ul v-if="productData.video_link" class="list">
                <li class="item item100">
                  <div class="item-title">主图视频：</div>
                  <video style="width:300px;height: 150px;border-radius: 10px;" :src="productData.video_link" controls="controls">
                    您的浏览器不支持 video 标签。
                  </video>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="规格与价格" name="goods">
            <div class="section">
              <div class="title">规格信息</div>
              <ul class="list">
                 <li class="item">
                  <div class="item-title" style="text-align: left;width: auto;">规格：</div>
                  <div class="value">{{productData.spec_type == 1 ? "多规格" : "单规格"}}</div>
                </li>
              </ul>
            </div>
            <div class="section" style="margin-top: 20px;">
              <div class="title" style="margin-bottom: 20px;">规格列表</div>
              <div class="list">
                <template v-if="productData.spec_type === 0&& productData.type  != 4">
                  <el-table :data="OneattrValue" border class="tabNumWidth" size="mini">
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="demo-image__preview">
                          <el-image
                            style="width: 60px; height: 60px"
                            :src="scope.row.image || scope.row.pic"
                          />
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="120">
                      <template slot-scope="scope">
                        <span class="priceBox" v-text="scope.row[iii]" />
                      </template>
                    </el-table-column>
                    <template v-if="productData.extension_type === 1">
                      <el-table-column align="center" label="一级返佣(元)" min-width="100">
                        <template slot-scope="scope">
                          <span class="priceBox" v-text="scope.row.extension_one" />
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="二级返佣(元)" min-width="100">
                        <template slot-scope="scope">
                          <span class="priceBox" v-text="scope.row.extension_two" />
                        </template>
                      </el-table-column>
                    </template>
                  </el-table>
                </template>
                <template v-if="productData.spec_type === 1&& productData.type !=4">
                  <el-table :data="ManyAttrValue" border class="tabNumWidth" size="mini">
                      <template v-if="manyTabDate">
                        <el-table-column v-for="(item,iii) in manyTabDate" :key="iii" align="center" :label="manyTabTit[iii].title" min-width="100">
                          <template slot-scope="scope">
                            <span class="priceBox" v-text="scope.row[iii]" />
                          </template>
                        </el-table-column>
                      </template>
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">
                        <div class="upLoadPicBox">
                          <div class="pictrue tabPic"><img :src="scope.row.image || scope.row.pic"></div>
                        </div>
                      </template>
                    </el-table-column>
                    <el-table-column v-for="(item,iii) in attrValue" :key="iii" :label="formThead[iii].title" align="center" min-width="100">
                      <template slot-scope="scope">
                        <span class="priceBox">{{ scope.row[iii] }}</span>
                      </template>
                    </el-table-column>
                    <template v-if="productData.extension_type === 1">
                      <el-table-column align="center" label="一级返佣(元)" min-width="80">
                        <template slot-scope="scope">
                          <span class="priceBox">{{ scope.row.extension_one }}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="二级返佣(元)" min-width="80">
                        <template slot-scope="scope">
                          <span class="priceBox">{{ scope.row.extension_two }}</span>
                        </template>
                      </el-table-column>
                    </template>
                  </el-table>
                </template>
                   <!-- 预约商品规格 -->
                   <template v-if="productData.type  == 4">
                  <el-table :data="productData.spec_type==1?ManyAttrValue:OneattrValue" border class="tabNumWidth ones" size="mini">
                    <template v-if="manyTabDate">
                      <el-table-column v-for="(item, iii) in manyTabDate" :key="iii" align="center"
                        :label="manyTabTit[iii].title" min-width="100">
                        <template slot-scope="scope">
                          {{ scope.row.detail[manyTabTit[iii].title] }}

                        </template>
                      </el-table-column>
                    </template>
                    <!-- 预约商品规格 -->
                    <el-table-column align="center" label="图片" min-width="80">
                      <template slot-scope="scope">

                        <div class="pictrue tabPic" v-if="scope.row.image || scope.row.pic"><img :src="scope.row.image || scope.row.pic"
                            style="width: 36px; height: 36px; border-radius: 4px;"></div>
                            <div v-else>--</div>
                      </template>
                    </el-table-column>

                    <el-table-column v-for="(item, iii) in reservationTableHeard" :key="iii" align="center"
                      :label="item.title" min-width="100">
                      <template slot-scope="scope">
                        <span class="priceBox" >{{ scope.row[item.slot] ||'--'}}</span>

                      </template>
                    </el-table-column>
                  </el-table>
                </template>
              </div>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品详情" name="detail">
            <div class="section">
              <div class="contentPic" v-html="productData.content"/>
            </div>
          </el-tab-pane>
          <el-tab-pane label="预约设置" name="reservation" v-if="productData.type == 4">
            <div class="section">
              <ul class="list">
                <li class="item item100">
                  <div class="item-title">服务模式：</div>
                  <div class="value">
                    <span v-if="productData.reservation_type == 1"
                      >到店服务</span
                    >
                    <span v-if="productData.reservation_type == 2"
                      >上门服务</span
                    >
                    <span v-if="productData.reservation_type == 3"
                      >上门+到店服务</span
                    >
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">剩余可约数量：</div>
                  <div class="value">
                    {{ productData.show_num_type ? "显示" : "隐藏" }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">可售日期：</div>
                  <div class="value" v-if="productData.sale_time_type == 1">
                    每天
                  </div>
                  <div class="value" v-else>
                    {{ productData.sale_time_start_day }}~{{
                      productData.sale_time_end_day
                    }}的
                    {{ getWeekName(productData.sale_time_week) }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">预约日期范围：</div>
                  <div class="value">
                    对用户展示{{
                      productData.show_reservation_days
                    }}天内的可预约日期
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">提前预约：</div>
                  <div class="value">
                    {{
                      productData.is_advance == 1
                        ? `用户需要提前${productData.advance_time}小时进行预约`
                        : "无需提前预约"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">取消预约：</div>
                  <div class="value">
                    {{
                      productData.is_cancel_reservation == 1
                        ? `服务开始${
                            productData.cancel_reservation_time
                          }小时之前，允许取消并自动退款`
                        : "不允许用户取消预约"
                    }}
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">表单信息：</div>
                  <div class="value">
                    {{
                      productData.reservation_form_type == 1
                        ? `每个预约提交一次`
                        : "每单提交一次"
                    }}
                  </div>
                </li>
                <li class="item item100" v-if="productData.mer_form_id">
                  <div class="item-title">系统表单：</div>
                  <div class="value">
                    <div style="width: 350px;">
                      <iframe
                        id="iframe"
                        class="iframe-box"
                        :src="formUrl"
                        frameborder="0"
                        ref="iframe"
                        style="min-height: 300px;"
                      ></iframe>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="营销信息" name="marketing">
            <div class="section">
              <div class="title">营销信息</div>
              <el-form v-if="isEdit" ref="formValidate" class="formValidate mt20" :rules="ruleValidate" :model="formValidate" size="small" label-width="100px" @submit.native.prevent>
                <el-form-item label="商品名称：" prop="store_name">
                  <el-input v-model="formValidate.store_name" size="small" placeholder="请输入商品名称" />
                </el-form-item>
                <el-form-item label="星级推荐：">
                  <el-rate class="rate_star" v-model="formValidate.star" :colors="colors" style="margin-top: 4px;"></el-rate>
                  <span style="margin-top: 4px; font-size: 12px;color: #999;">备注：5星为最高推荐级别，1星为最低推荐级别，设置后会在商城商品列表、搜索商品列表中体现。</span>
                </el-form-item>
                <el-form-item label="商品推荐：">
                  <el-checkbox-group v-model="checkboxGroup" size="small" @change="onChangeGroup">
                    <el-checkbox v-for="(item, index) in recommend" :key="index" :label="item.value">{{ item.name }}</el-checkbox>
                  </el-checkbox-group>
                  <div style="color: #999;font-size: 12px;">根据选中标签展示在商城对应的推荐列表</div>
                </el-form-item>
                <el-form-item label="大图推荐：">
                  <el-switch
                    :width="55"
                    v-model="formValidate.cate_hot"
                    :active-value="1"
                    :inactive-value="0"
                    active-text="开启"
                    inactive-text="关闭"
                  />
                  <div style="color: #999;font-size: 12px;">注：开启后，在列表点击商品会先进入该商品三级分类的大图商品列表页</div>
                </el-form-item>
                <el-form-item label="排序：">
                  <el-input-number v-model="formValidate.rank" size="small" placeholder="请输入排序序号" style="width: 200px;" />
                </el-form-item>
                <el-col :span="24">
                  <el-form-item label="商品详情：">
                    <!-- <ueditor-from v-model="formValidate.content" :content="formValidate.content" /> -->
                    <WangEditor
                    :content="formValidate.content"
                    @editorContent="getEditorContent"
                    style="width: 100%;"
                  ></WangEditor>
                  </el-form-item>
                </el-col>
                <!-- <el-form-item style="margin-top:30px;">
                  <el-button type="primary" class="submission" size="small" @click="handleSubmit('formValidate')">提交</el-button>
                </el-form-item> -->
              </el-form>
              <ul v-else class="list">
                <li class="item">
                  <div class="item-title">店铺推荐：</div>
                  <div class="value">{{productData.is_good ? '开启' : '关闭'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">平台推荐：</div>
                  <div v-if="productData.is_benefit||productData.is_new||productData.is_best||productData.is_hot" class="value">
                    <span class="value-item" v-if="productData.is_benefit">促销单品</span>
                    <span class="value-item" v-if="productData.is_new">首发新品</span>
                    <span class="value-item" v-if="productData.is_best">精品推荐</span>
                    <span class="value-item" v-if="productData.is_hot">热门榜单</span>
                  </div>
                  <div v-else class="value-item">无</div>
                </li>
                <li class="item">
                  <div class="item-title">分销礼包：</div>
                  <div class="value">{{productData.is_gift_bag ? '是' : '否'}}</div>
                </li>
                <li v-if="productData.star" class="item">
                  <div class="item-title">平台推荐星级：</div>
                  <div class="value">
                    <el-rate disabled v-model="productData.star" :colors="colors"></el-rate>
                  </div>
                </li>
                <li v-if="productData.merchant&&productData.integral_rate!=0" class="item">
                  <div class="item-title">积分抵扣：</div>
                  <div v-if="productData.integral_rate == -1&&productData.merchant" class="value">默认</div>
                  <div v-else-if="productData.integral_rate > 0" class="value">单独设置{{'('+productData.integral_rate+'%)'}}</div>
                  <span v-if="productData.integral_rate == -1" style="color: #F56464;">（店铺设置比例，{{productData.merchant.mer_integral_rate}}%）</span>
                </li>
                <li v-if="productData.integral_rate!=0" class="item">
                  <div class="item-title">积分抵扣金额：</div>
                  <div class="value">{{productData.integral_price_total}}元</div>
                </li>
                <li v-if="productData.coupon&&productData.coupon.length>0" class="item">
                  <div class="item-title">商品赠券：</div>
                  <div class="value">
                    <span v-for="(itm,idx) in productData.coupon" :key="idx" class="value-item"> {{itm.title}} </span>
                  </div>
                  <span>（购买此商品后自动赠送）</span>
                </li>
                <li class="item">
                  <div class="item-title">收藏人数：</div>
                  <div class="value">
                    <span> {{productData.care_count}}人</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">已售数量：</div>
                  <div class="value">
                    <span> {{productData.ficti}} (指手动添加数量)</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">实际销量：</div>
                  <div class="value">
                    <span> {{productData.sales-productData.ficti}} (指实际售出数量)</span>
                  </div>
                </li>
                <li class="item">
                  <div class="item-title">佣金设置：</div>
                  <div class="value">{{productData.extension_type==1?"单独设置":"默认"}}</div>
                  <span v-if="productData.extension_type == 0 && productData.merchant.mer_config" style="color: #F56464;">（一级佣金{{productData.merchant.mer_config.extension_one_rate}}%，二级佣金{{productData.merchant.mer_config.extension_two_rate}}%）</span>
                </li>
                 <li v-if="mer_svip_status" class="item">
                  <div class="item-title">付费会员价：</div>
                  <div class="value">{{productData.svip_price_type==0 ? '不设置' : productData.svip_price_type==1 ? '默认' : '自定义'}}</div>
                  <span v-if="productData.svip_price_type==1 && productData.merchant" style="color: #F56464;">（店铺设置，{{productData.merchant.mer_config.svip_store_rate*10}}折）</span>
                </li>
                <li class="item">
                  <div class="item-title">大图推荐：</div>
                  <div class="value">
                    <span> {{productData.cate_hot == 1 ? '开启' : '关闭'}}</span>
                  </div>
                </li>
                <li class="item" v-if="productData.activity_labels && productData.activity_labels.length" style="align-items: flex-start;">
                  <div class="item-title">活动标签：</div>
                  <div class="value" style="flex: 1;">
                    <label-list :list="productData.activity_labels" />
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="其它信息" name="others">
            <div class="section">
              <div class="title">其它信息</div>
              <ul class="list">
                <li class="item">
                  <div class="item-title">支持退款：</div>
                  <div class="value">{{productData.refund_switch ? '是' : '否'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">最少购买件数：</div>
                  <div class="value">{{productData.once_min_count == 0 ? '不限购' : productData.once_min_count}}</div>
                </li>
                <li v-if="productData.pay_limit!=0" class="item">
                  <div class="item-title">限购类型：</div>
                  <div class="value">{{productData.pay_limit==1?'单次限购':'长期限购'}}{{productData.once_min_count+'('+productData.unit_name+')'}}</div>
                </li>
               <li v-if="productData.guarantee" class="item item100">
                  <div class="item-title">保障服务：</div>
                  <div class="value" style="width: 250px;">
                    <span>{{productData.guarantee.template_name}}</span>
                    <div v-if="productData.guarantee.templateValue && productData.guarantee.templateValue.length>0" style="display: inline;">
                      【<span v-for="(item,i) in productData.guarantee.templateValue" :key="i" class="value-temp">{{item.value&&item.value.guarantee_name}}</span>】
                    </div>
                  </div>
                </li>
                <li v-if="productData.refusal" class="item">
                  <div class="item-title">审核拒绝原因：</div>
                  <div class="value">{{productData.refusal}}</div>
                </li>
              </ul>
            </div>
            <div class="section">
              <ul style="padding: 0;margin-top: 50px;">
                <li class="item item100">
                  <div class="item-title">店铺商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="merParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>
                <li class="item item100">
                  <div class="item-title">平台商品参数：</div>
                  <div class="value" style="width: 721px;">
                    <el-table
                      border
                      ref="tableParameter"
                      :data="sysParams"
                      row-key="parameter_value_id"
                      size="small"
                      class="ones"
                    >
                      <el-table-column
                        align="center"
                        label="参数名称"
                        width="360"
                      >
                        <template slot-scope="scope">
                          <span>{{scope.row.name}}</span>
                        </template>
                      </el-table-column>
                      <el-table-column align="center" label="参数值" width="360">
                        <template slot-scope="scope">
                          <span>{{scope.row.value}}</span>
                        </template>
                      </el-table-column>
                    </el-table>
                  </div>
                </li>
                <li class="item item100" v-if="productData.type !== 4">
                  <div class="item-title">关联系统表单：</div>
                  <div v-if="!productData.mer_form_id" class="value">关闭</div>
                  <div v-else-if="productData.mer_form_id && formData.length==0" class="value">表单已被删除</div>
                  <div v-else-if="formData.length>0 && productData.mer_form_id && activeName=='others'" class="value" style="width: 350px;">
                    <iframe class="iframe-box" :src="formUrl" frameborder="0" ref="iframe" style="min-height: 300px;"></iframe>
                  </div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="店铺信息" name="merchant" v-if="productData.merchant">
            <div class="section">
              <div class="title">店铺信息</div>
              <ul class="list">
                <li class="item">
                  <div class="item-title">店铺名称：</div>
                  <div class="value">{{productData.merchant.mer_name}}</div>
                </li>
                <li class="item">
                  <div class="item-title">店铺类别：</div>
                  <div class="value">{{productData.merchant.is_trader == 1 ? '自营' : '非自营'}}</div>
                </li>
                <li class="item">
                  <div class="item-title">店铺类型：</div>
                  <div class="value">{{productData.merchant.type_name}}</div>
                </li>
              </ul>
            </div>
          </el-tab-pane>
          <el-tab-pane label="商品操作记录" name="records">
            <div class="section">
              <el-form size="small" label-width="90px">
                <div class="acea-row">
                  <el-form-item label="操作端：">
                    <el-select
                      v-model="recordForm.type"
                      placeholder="请选择"
                      class="selWidth mr10"
                      clearable
                      filterable
                      @change="getRecordData(productId)"
                    >
                      <el-option label="平台端" value="1" />
                      <el-option label="店铺端" value="2" />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="操作时间：">
                    <el-date-picker
                      class="selWidth"
                      v-model="timeVal"
                      type="datetimerange"
                      placeholder="选择日期"
                      value-format="yyyy/MM/dd HH:mm:ss"
                      start-placeholder="开始时间"
                      end-placeholder="结束时间"
                      clearable
                      @change="onchangeTime"
                    >
                    </el-date-picker>
                  </el-form-item>
                </div>
              </el-form>
              <el-table
                border
                ref="productRecords"
                :data="recordData.data"
                row-key="operate_log_id"
                size="small"
                class="ones"
              >
                <el-table-column
                  align="center"
                  label="序号"
                  min-width="60"
                  prop="operate_log_id"
                />
                <el-table-column align="center" label="操作记录" min-width="120">
                  <template slot-scope="scope">
                    <span>{{scope.row.category_name}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作端" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.type}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作角色" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.operator_role_nickname}}</span>
                  </template>
                </el-table-column>
                <el-table-column align="center" label="操作人" min-width="100">
                  <template slot-scope="scope">
                    <span>{{scope.row.operator_nickname}}</span>
                  </template>
                </el-table-column>
                <el-table-column
                  align="center"
                  label="操作时间"
                  min-width="100"
                  prop="create_time"
                />
              </el-table>
              <div class="block">
                <el-pagination :page-size="recordForm.limit" :current-page="recordForm.page" layout="prev, pager, next, jumper" :total="recordData.total" @size-change="handleSizeChange" @current-change="pageChange" />
              </div>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-drawer>
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
import { productDetailApi,productReservationDetailApi, operateRecordList, productUpdateApi } from '@/api/product';
import { associatedFormInfo } from '@/api/marketing';
import WangEditor from '@/components/wangEditor/index.vue';
import SettingMer from '@/libs/settingMer'
import labelList from '@/components/activityLabel/label-list.vue';
const proOptions = [{
  name: '热门榜单',
  value: 'is_hot'
}, {
  name: '促销单品',
  value: 'is_benefit'
}, {
  name: '精品推荐',
  value: 'is_best'
}, {
  name: '首发新品',
  value: 'is_new'
}]
const defaultObj = {
  image: '',
  slider_image: [],
  store_name: '',
  store_info: '',
  keyword: '',
  brand_id: '', // 品牌id
  cate_id: '', // 平台分类id
  mer_cate_id: [], // 店铺分类id
  unit_name: '',
  sort: 0,
  is_show: 0,
  is_benefit: 0,
  is_new: 0,
  is_good: 0,
  temp_id: '',
  params: [],
  attrValue: [{
    image: '',
    price: null,
    svip_price: null,
    ot_price: null,
    stock: null,
    bar_code: null,
    bar_code_number: null,
    weight: null,
    volume: null
  }],
  attr: [],
  selectRule: '',
  extension_type: 0,
  content: '',
  spec_type: 0
}
const objTitle = {
  price: {
    title: '售价'
  },
  svip_price: {
    title: '付费会员价'
  },
  ot_price: {
    title: '划线价'
  },
  stock: {
    title: '库存'
  },
  bar_code: {
    title: '规格编码'
  },
  bar_code_number: {
    title: '条形码'
  },
  weight: {
    title: '重量（KG）'
  },
  volume: {
    title: '体积(m³)'
  }
}
export default {
  components: {
    WangEditor,
    labelList
  },
  props: {
    drawer: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isEdit: false,
      loading: true,
      productId: '',
      direction: 'rtl',
      activeName: 'basic',
      productData: {},
      formThead: Object.assign({}, objTitle),
      manyTabDate: {},
      reservationTableHeard: [

{
  title: '售价(元)',
  slot: 'price',
  align: 'center',
  minWidth: '120px',
},
{
  title: '划线价(元)',
  slot: 'ot_price',
  align: 'center',
  minWidth: '120px',
},

{
  title: '商品编码',
  slot: 'bar_code',
  align: 'center',
  minWidth: '120px',
},
{
  title: '每天可约总数',
  slot: 'stock',
  align: 'center',
  minWidth: '120px',
},

],
      manyTabTit: {},
      OneattrValue: [Object.assign({}, defaultObj.attrValue[0])], // 单规格
      ManyAttrValue: [Object.assign({}, defaultObj.attrValue[0])], // 多规格
      svip_type: 0,
      mer_svip_status: 0,
      orderImg: require('@/assets/images/product_icon.png'),
      merParams: [],
      sysParams: [],
      formUrl: "",
      baseURL: SettingMer.httpUrl || 'http://localhost:8080',
      // baseURL: 'http://localhost:8080',
      formData: [],
      timeVal: [],
      recordData: {
        data: [],
        total: 0
      },//商品操作记录
      recordForm: {
        type: '',
        date: "",
        page: 1,
        limit: 10
      },
      colors: ['#99A9BF', '#F7BA2A', '#FF9900'],
      fullscreenLoading: false,
      recommend: proOptions,
      checkboxGroup: [],
      ruleValidate: {
        store_name: [{
          required: true,
          message: "请输入商品名称",
          trigger: "blur"
        },]
      },
      week: [
        { id: 1, name: "周一" },
        { id: 2, name: "周二" },
        { id: 3, name: "周三" },
        { id: 4, name: "周四" },
        { id: 5, name: "周五" },
        { id: 6, name: "周六" },
        { id: 7, name: "周日" }
      ],
      formValidate: {
        is_hot: 0,
        is_best: 0,
        is_new: 0,
        is_benefit: 0,
        ficti: 0,
        is_ficti: "",
        content: '',
        store_name: '',
        rank: '',
        us_status: '',
        star: 0
      },
    };
  },
  computed: {
    attrValue() {
      const obj = Object.assign({}, defaultObj.attrValue[0])
      if(this.svip_type == 0 || this.mer_svip_status == 0)delete obj.svip_price
      delete obj.image
      return obj
    },
  },
  filters: {
  },
  methods: {
    handleEdit(){
      this.isEdit = true;
      this.activeName = "marketing";
    },
    cancelEdit() {
      this.isEdit = false;
    },
    handleClose() {
      this.activeName = 'basic';
      this.$emit('closeDrawer');
    },
    getEditorContent(data) {
      this.formValidate.content = data;
    },
    getWeekName(val) {
      if (!val || val.length == 0) return "";
      let str = "";
      this.week.map(item => {
        if (val.includes(item.id)) {
          str += item.name + "，";
        }
      });
      return str;
    },
    getInfo(id,isEdit) {
      this.loading = true;
      this.productId = id;
      this.isEdit = isEdit;
      // this.activeName = isEdit ? "marketing" : "basic";
      productDetailApi(id).then(res => {
        this.loading = false;
        this.productData = res.data;
        let info = res.data;
        this.formValidate = {
          is_hot: info.is_hot,
          is_best: info.is_best,
          is_new: info.is_new,
          is_benefit: info.is_benefit,
          ficti: info.ficti,
          content: info.content,
          store_name: info.store_name,
          rank: info.rank,
          star: info.star,
          cate_hot: info.cate_hot
        }
         this.checkboxGroup = []
        if(info.is_benefit === 1) this.checkboxGroup.push('is_benefit')
        if(info.is_hot === 1) this.checkboxGroup.push('is_hot')
        if(info.is_best === 1) this.checkboxGroup.push('is_best')
        if(info.is_new === 1) this.checkboxGroup.push('is_new')
        this.mer_svip_status = res.data.mer_svip_status
        this.svip_type = res.data.svip_price_type
        if (this.productData.spec_type === 0) {
          this.OneattrValue = res.data.attrValue
        } else {
          this.ManyAttrValue = res.data.attrValue
        }
        const tmp = {}
        const tmpTab = {}
        this.productData.attr.forEach((o, i) => {
          tmp['value' + i] = { title: o.value }
          tmpTab['value' + i] = ''
        })
        this.manyTabDate = tmpTab
        this.manyTabTit = tmp
        this.formThead = Object.assign({}, this.formThead, tmp)
        this.sysParams = []
        this.merParams = []
        if(res.data.params&&res.data.params.length>0){
          for(var i=0;i<res.data.params.length;i++){
            if(res.data.params[i]['mer_id'] == 0){
              this.sysParams.push(res.data.params[i])
            }else{
              this.merParams.push(res.data.params[i])
            }
          }
        }
        if(res.data.mer_form_id)this.getFormInfo(res.data.mer_form_id,res.data.mer_id)
        this.getRecordData(id)
        this.loading = false
      }).catch(res => {
        this.$message.error(res.message)
        this.loading = false
      })
    },
    onChangeGroup() {
      this.checkboxGroup.includes('is_benefit') ? this.formValidate.is_benefit = 1 : this.formValidate.is_benefit = 0 && this.checkboxGroup.remove('is_benefit')
      this.checkboxGroup.includes('is_best') ? this.formValidate.is_best = 1 : this.formValidate.is_best = 0 && this.checkboxGroup.remove('is_best')
      this.checkboxGroup.includes('is_new') ? this.formValidate.is_new = 1 : this.formValidate.is_new = 0 && this.checkboxGroup.remove('is_new')
      this.checkboxGroup.includes('is_hot') ? this.formValidate.is_hot = 1 : this.formValidate.is_hot = 0 && this.checkboxGroup.remove('is_hot')
    },
     // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if(valid) {
          productUpdateApi(this.productId, this.formValidate).then(async res => {
            this.fullscreenLoading = false
            this.$message.success(res.message)
            this.getInfo(this.productId,false)
            this.$emit('getList','')
          }).catch(res => {
            this.fullscreenLoading = false
            this.$message.error(res.message)
          })
        } else {
          return false
        }
      })
    },
    // 关联的表单信息
    getFormInfo(id,merId){
      associatedFormInfo(id,{mer_id:merId}).then((res) => {
        this.formData = res.data
        let time = new Date().getTime() * 1000
        let formUrl = `${this.baseURL}/pages/admin/system_form/index?inner_frame=1&form_id=${id}&time=${time}`;
        this.formUrl = formUrl;
      })
      .catch((res) => {
        this.$message.error(res.message)
      })
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e
      this.recordForm.date = e ? this.timeVal.join('-') : ''
      this.getRecordData(this.productData.product_id)
    },
    // 商品操作记录
    getRecordData(id){
      operateRecordList(id,this.recordForm)
        .then(res => {
          this.recordData.data = res.data.list
          this.recordData.total = res.data.count
        })
        .catch(res => {
          this.$message.error(res.message)
        })
    },
    pageChange(page) {
      this.recordForm.page = page
      this.getRecordData(this.productId)
    },
    handleSizeChange(val) {
      this.recordForm.limit = val
      this.getRecordData(this.productId)
    },
    tabClick(tab) {

    },
  },
};
</script>
<style lang="scss" scoped>
.head {
  padding: 20px 35px;
  .full {
    display: flex;
    align-items: center;
    .order_icon {
      width: 60px;
      height: 60px;
    }
    .iconfont {
      color: var(--prev-color-primary);
      &.sale-after {
        color: #90add5;
      }
    }
    .text {
      align-self: center;
      flex: 1;
      min-width: 0;
      padding-left: 12px;
      font-size: 13px;
      color: #606266;
      .title {
        margin-bottom: 10px;
        font-weight: 500;
        font-size: 16px;
        line-height: 16px;
        font-weight: bold;
        color: #282828;
        line-height: 20px;
      }
      .order-num {
        padding-top: 10px;
        white-space: nowrap;
      }
    }
  }
  .list {
    display: flex;
    margin-top: 20px;
    overflow: hidden;
    list-style: none;
    padding: 0;
    .item {
      flex: none;
      width: 20%;
      font-size: 14px;
      line-height: 14px;
      color: rgba(0, 0, 0, 0.85);
      .title {
        margin-bottom: 12px;
        font-size: 13px;
        line-height: 13px;
        color: #666666;
      }
    }
  }
}
.tabNumWidth{
  max-height: calc(100vh - 350px);
  overflow-y: auto;
  &:before{
    display: none;
  }
}
.el-tabs--border-card {
  box-shadow: none;
  border-bottom: none;
}
.section {
  .title {
    font-size: 15px;
    line-height: 15px;
    color: #303133;
    padding-left: 10px;
    border-left: 3px solid var(--prev-color-primary);
  }
  .list {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .item {
    flex: 0 0 calc(100% / 3);
    display: flex;
    margin-top: 16px;
    font-size: 13px;
    color: #606266;
    align-items: center;
    &:nth-child(3n + 1) {
      padding-right: 20px;
    }
    &:nth-child(3n + 2) {
      padding-right: 10px;
      padding-left: 10px;
    }
    &:nth-child(3n + 3) {
      padding-left: 20px;
    }
    .item-title{
      width: 100px;
      text-align: right;
    }
  }
  .item100{
    padding-left: 0;
    flex: 0 0 calc(100% / 1);
    padding-left: 0!important;
  }
  .contentPic{
    width: 500px;
    margin: 0 auto;
    max-height: 600px;
    overflow-y: auto;
    div{
      width: 500px!important;
    }
  }
  .value {
    .value-item {
      &::after{
        content: "/";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    .value-temp{
       &::after{
        content: "、";
        display: inline-block;
      }
      &:last-child{
        &::after{
          display: none;
        }
      }
    }
    image {
      display: inline-block;
      width: 40px;
      height: 40px;
      margin: 0 12px 12px 0;
      vertical-align: middle;
    }
  }
}
.contentPic ::v-deep img,.contentPic ::v-deep div,.contentPic ::v-deep p{
  max-width: 100%!important;
}
.tab {
  display: flex;
  align-items: center;
  .el-image {
    width: 36px;
    height: 36px;
    margin-right: 10px;
  }
}
::v-deep .el-drawer__body {
  overflow: auto;
}
::v-deep .ones th{
  background: #F0F5FF;
}

</style>

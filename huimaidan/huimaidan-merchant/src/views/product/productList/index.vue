<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        size="small"
        label-width="95px"
        :inline="true"
      >
        <el-form-item label="平台分类：" prop="cate_id">
          <el-cascader
            ref="myCascader"
            filterable
            v-model="tableFrom.cate_id"
            class="selWidth"
            :options="categoryList"
            :props="props"
            clearable
            @change="getList(1)"
          />
        </el-form-item>
        <el-form-item label="商品分类：" prop="mer_cate_id">
          <el-select
            v-model="tableFrom.mer_cate_id"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in merCateList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="品牌选择：" >
          <el-select
            v-model="tableFrom.brand_id"
            filterable
            clearable
            placeholder="请选择"
            @change="getList(1)"
            class="filter-item selWidth"
          >
            <el-option
              v-for="item in BrandList"
              :key="item.brand_id"
              :label="item.brand_name"
              :value="item.brand_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="是否为礼包：" prop="is_gift_bag">
          <el-select
            v-model="tableFrom.is_gift_bag"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option label="是" value="1" />
            <el-option label="否" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="会员价：" prop="svip_price_type">
          <el-select
            v-model="tableFrom.svip_price_type"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option label="未设置" value="0" />
            <el-option label="默认设置" value="1" />
            <el-option label="自定义设置" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="商品状态：" prop="us_status">
          <el-select
            v-model="tableFrom.us_status"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList()"
          >
            <el-option
              v-for="item in productStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="商品标签：" prop="mer_labels">
          <el-select
            v-model="tableFrom.mer_labels"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
          >
            <el-option
              v-for="item in labelList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="活动标签：" prop="activity_label_ids">
          <el-cascader
            v-model="tableFrom.activity_label_ids"
            :options="activityLabelCascader"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
            :props="activityLabelProps"
          >
          </el-cascader>
        </el-form-item>
        <el-form-item label="运费模板：" prop="temp_id">
          <el-select
            v-model="tableFrom.temp_id"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
          >
            <el-option
              v-for="item in tempList"
              :key="item.shipping_template_id"
              :label="item.name"
              :value="item.shipping_template_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="系统表单：" prop="form_id">
          <el-select
            v-model="tableFrom.form_id"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
          >
            <el-option
              v-for="item in formList"
              :key="item.form_id"
              :label="item.name"
              :value="item.form_id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="关键字：" prop="keyword">
          <el-input
            v-model="tableFrom.keyword"
            placeholder="请输入商品名称，关键字"
            class="selWidth"
            clearable
            @keyup.enter.native="getList(1)"
          />
        </el-form-item>
        <el-form-item label="商品类型：" prop="is_ficti">
          <el-select
            v-model="tableFrom.is_ficti"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in productTypeList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="大图推荐：" prop="cate_hot">
          <el-select
            v-model="tableFrom.cate_hot"
            placeholder="请选择"
            class="filter-item selWidth"
            clearable
            filterable
            @change="getList(1)"
          >
            <el-option label="开启" value="1" />
            <el-option label="关闭" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)"
            >搜索</el-button
          >
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14 dataBox">
      <el-tabs v-model="tableFrom.type" @tab-click="getProduct">
        <el-tab-pane
          v-for="(item, index) in headeNum"
          :key="index"
          :name="item.type.toString()"
          :label="item.name + '(' + item.count + ')'"
        />
      </el-tabs>
      <div class="mt5 mb14">
        <el-button size="small" type="primary" @click="addProduct"
          >添加商品</el-button
        >
        <el-button size="small" type="success" @click="onCopy"
          >商品采集</el-button
        >
        <template v-if="tableFrom.type != 5">
          <el-button
            size="small"
            :disabled="checkedIds.length == 0 && !allCheck"
            type="default"
            @click="openBatch"
            >批量设置</el-button
          >
          <el-button
            size="small"
            :disabled="checkedIds.length == 0 && !allCheck"
            type="default"
            @click="openBatchSetActivityLabel"
            >批量设置活动标签</el-button
          >
        </template>
        <el-button
          v-if="
            tableFrom.type == 1 || tableFrom.type == 3 || tableFrom.type == 4
          "
          size="small"
          :disabled="checkedIds.length == 0 && !allCheck"
          @click="batchOff"
          >批量下架</el-button
        >
        <el-button
          v-if="tableFrom.type == 2 || tableFrom.type == 4"
          size="small"
          :disabled="checkedIds.length == 0 && !allCheck"
          @click="batchShelf"
          >批量上架</el-button
        >
      </div>
      <el-alert
        v-if="checkedIds.length > 0 || allCheck"
        :title="
          allCheck
            ? `已选择  ${tableData.total}  项`
            : `已选择  ${checkedIds.length}  项`
        "
        type="info"
        show-icon
        class="mb10"
      />

      <el-table
        v-loading="listLoading"
        :data="tableData.data"
        size="small"
        :row-class-name="tableRowClassName"
        :row-key="
          row => {
            return row.product_id;
          }
        "
        @selection-change="handleSelectionChange"
        @rowclick.stop="closeEdit"
      >
        <el-table-column width="50">
          <template slot="header" slot-scope="scope">
            <el-popover
              placement="top-start"
              width="100"
              trigger="hover"
              class="tabPop"
            >
              <div>
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'dan' }"
                  @click="onHandle('dan', scope.$index)"
                  >选中本页</span
                >
                <span
                  class="spBlock onHand"
                  :class="{ check: chkName === 'duo' }"
                  @click="onHandle('duo')"
                  >选中全部</span
                >
              </div>
              <el-checkbox
                slot="reference"
                :value="
                  (chkName === 'dan' &&
                    checkedPage.indexOf(tableFrom.page) > -1) ||
                    chkName === 'duo'
                "
                @change="changeType"
              />
            </el-popover>
          </template>
          <template slot-scope="scope">
            <el-checkbox
              :disabled="scope.row.cancel_time"
              :value="
                !scope.row.cancel_time &&
                  (checkedIds.indexOf(scope.row.product_id) > -1 ||
                    (chkName === 'duo' &&
                      noChecked.indexOf(scope.row.product_id) === -1))
              "
              @change="v => changeOne(v, scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column type="expand">
          <template slot-scope="props">
            <el-form
              label-position="left"
              inline
              class="demo-table-expand demo-table-expand1"
              label-width="90px"
            >
              <el-form-item label="平台分类：">
                <span>{{
                  props.row.storeCategory
                    ? props.row.storeCategory.cate_name
                    : "-"
                }}</span>
              </el-form-item>
              <el-form-item label="商品分类：">
                <template v-if="props.row.merCateId.length">
                  <span
                    v-for="(item, index) in props.row.merCateId"
                    :key="index"
                    class="mr10"
                    >{{ item.category.cate_name }}</span
                  >
                </template>
                <span v-else>-</span>
              </el-form-item>
              <el-form-item label="品牌：">
                <span class="mr10">{{
                  props.row.brand ? props.row.brand.brand_name : "其他"
                }}</span>
              </el-form-item>
              <el-form-item label="划线价格：">
                <span>{{ props.row.ot_price | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="成本价：">
                <span>{{ props.row.cost | filterEmpty }}</span>
              </el-form-item>
              <el-form-item label="收藏：">
                <span>{{ props.row.care_count | filterEmpty }}</span>
              </el-form-item>
              <el-form-item
                v-if="tableFrom.type === '7'"
                key="1"
                label="未通过原因："
              >
                <span>{{ props.row.refusal }}</span>
              </el-form-item>
              <el-form-item
                v-if="props.row.activity_labels.length"
                label="活动标签："
                class="form-item-flex"
              >
                <label-list :list="props.row.activity_labels" />
              </el-form-item>
            </el-form>
          </template>
        </el-table-column>
        <el-table-column prop="product_id" label="ID" min-width="50" />
        <el-table-column label="商品图" min-width="70">
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                :src="scope.row.image"
                :preview-src-list="[scope.row.image]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="store_name" label="商品名称" min-width="230">
          <template slot-scope="scope">
            <el-tooltip :open-delay="300" placement="bottom">
                <div slot="content" class="max-w-300">{{ scope.row.store_name}}</div>
                <div class="line2">
                  <span class="tags_name" >{{ scope.row.spec_type==0 ? '[单规格]' : '[多规格]' }}</span>
                  {{ scope.row.store_name || '--' }}
                </div>
              </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="store_name" label="商品类型" min-width="230">
          <template slot-scope="scope">
            <div>{{ getTypes(scope.row.type) }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="商品售价" min-width="80" />
        <el-table-column prop="sales" label="销量" min-width="70" />
        <el-table-column prop="stock" label="库存" min-width="70" />
        <el-table-column prop="sort" align="center" label="排序" min-width="80">
          <template slot-scope="scope">
            <span v-if="scope.row.index === tabClickIndex">
              <el-input
                v-model.number="scope.row['sort']"
                type="number"
                maxlength="300"
                size="mini"
                autofocus
                @blur="inputBlur(scope)"
              />
            </span>
            <span v-else @dblclick.stop="tabClick(scope.row)">{{
              scope.row["sort"]
            }}</span>
          </template>
        </el-table-column>
        <el-table-column
          v-if="Number(tableFrom.type) < 5"
          key="1"
          prop="status"
          label="上/下架"
          min-width="90"
        >
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.is_show"
              :active-value="1"
              :inactive-value="0"
              :width="55"
              active-text="上架"
              inactive-text="下架"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="stock" label="商品状态" min-width="90">
          <template slot-scope="scope">
            <span>{{ scope.row.us_status | productStatusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          label="操作"
          :min-width="tableFrom.type == 5 ? '100' : '150'"
          fixed="right"
        >
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              class="mr10"
              @click="onDetails(scope.row)"
              >详情</el-button
            >
            <!-- <router-link v-if="tableFrom.type != 5" :to="{path: roterPre + '/product/list/addProduct/' + scope.row.product_id}"> -->
            <el-button
              v-if="tableFrom.type !== '5'"
              type="text"
              size="small"
              class="mr10"
              @click="editProduct(scope.row)"
              >编辑</el-button
            >
            <!-- </router-link> -->
            <el-button
              v-if="tableFrom.type !== '5'"
              type="text"
              size="small"
              class="mr10"
              @click="handlePreview(scope.row.product_id)"
              >预览</el-button
            >
            <el-dropdown>
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right" />
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item
                  v-if="
                    tableFrom.type !== '5' &&
                      is_audit == '1' &&
                      scope.row.type != 4
                  "
                  @click.native="onAuditFree(scope.row)"
                >
                  <span class="dropdown-text">免审编辑</span></el-dropdown-item
                >
                <el-dropdown-item v-if="tableFrom.type != 5">
                  <span class="dropdown-text" @click="copyProduct(scope.row)"
                    >复制商品</span
                  >
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="tableFrom.type !== '5'"
                  @click.native="onEditLabel(scope.row)"
                >
                  <span class="dropdown-text" @click="onEditLabel(scope.row)"
                    >商品标签</span
                  >
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="tableFrom.type !== '5'"
                  @click.native="onEditActivityLabel(scope.row)"
                >
                  <span class="dropdown-text" @click="onEditActivityLabel(scope.row)"
                    >活动标签</span
                  >
                </el-dropdown-item>
                <el-dropdown-item v-if="tableFrom.type != 5">
                  <span class="dropdown-text" @click="viewOn(scope.row)"
                    >查看评价</span
                  >
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="
                    tableFrom.type !== '1' &&
                      tableFrom.type !== '3' &&
                      tableFrom.type !== '4'
                  "
                  @click.native="
                    handleDelete(scope.row.product_id, scope.$index)
                  "
                >
                  <span class="dropdown-text">
                    {{ tableFrom.type === "5" ? "删除" : "加入回收站" }}</span
                  >
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="tableFrom.type === '5'"
                  @click.native="handleRestore(scope.row.product_id)"
                >
                  <span class="dropdown-text">恢复商品</span></el-dropdown-item
                >
              </el-dropdown-menu>
            </el-dropdown>
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
    <!-- 生成淘宝京东表单-->
    <tao-bao
      ref="taoBao"
      @getSuccess="getSuccess"
      :deliveryType="deliveryType"
      :deliveryList="deliveryList"
    />
    <!--预览商品-->
    <div v-if="previewVisible">
      <div class="bg" @click.stop="previewVisible = false" />
      <preview-box
        v-if="previewVisible"
        ref="previewBox"
        :goods-id="goodsId"
        :product-type="0"
        :preview-key="previewKey"
      />
    </div>
    <!--编辑标签-->
    <el-dialog
      v-if="dialogLabel"
      title="选择标签"
      :visible.sync="dialogLabel"
      width="470px"
      :before-close="handleClose"
    >
      <el-form
        ref="labelForm"
        :model="labelForm"
        @submit.native.prevent
        size="small"
      >
        <el-form-item>
          <el-select
            v-model="labelForm.mer_labels"
            clearable
            multiple
            placeholder="请选择"
            class="width100"
          >
            <el-option
              v-for="item in labelList"
              :key="item.id"
              :label="item.name"
              :value="item.id.toString()"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="dialogLabel = false">取消</el-button>
        <el-button size="small" type="primary" @click="submitForm('labelForm')"
          >提交</el-button
        >
      </span>
    </el-dialog>
    <!-- 免审核弹窗-->
    <edit-attr ref="editAttr" />
    <!--运费模板弹窗-->
    <el-dialog
      v-if="dialogFreight"
      title="选择运费模板"
      :visible.sync="dialogFreight"
      width="800px"
      :before-close="handleFreightClose"
    >
      <el-form
        ref="tempForm"
        :model="tempForm"
        :rules="tempRule"
        @submit.native.prevent
      >
        <el-form-item prop="temp_id">
          <el-select
            v-model="tempForm.temp_id"
            clearable
            placeholder="请选择"
            class="selWidth"
          >
            <el-option
              v-for="item in tempList"
              :key="item.shipping_template_id"
              :label="item.name"
              :value="item.shipping_template_id"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="submitTempForm('tempForm')"
          >提交</el-button
        >
      </span>
    </el-dialog>
    <!--批量设置佣金弹窗-->
    <el-dialog
      v-if="dialogCommision"
      title="设置佣金"
      :visible.sync="dialogCommision"
      width="600px"
    >
      <el-form
        ref="commisionForm"
        :model="commisionForm"
        :rules="commisionRule"
        @submit.native.prevent
      >
        <el-form-item label="一级佣金比例：" prop="extension_one">
          <el-input-number
            v-model="commisionForm.extension_one"
            :precision="2"
            :step="0.1"
            :min="0"
            :max="1"
            class="priceBox"
            controls-position="right"
          />
        </el-form-item>
        <el-form-item label="二级佣金比例：" prop="extension_two">
          <el-input-number
            v-model="commisionForm.extension_two"
            :precision="2"
            :step="0.1"
            :min="0"
            :max="1"
            class="priceBox"
            controls-position="right"
          />
        </el-form-item>
        <el-form-item>
          <span
            >备注：订单交易成功后给上级返佣的比例，例:0.5 =
            返订单金额的50%</span
          >
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="submitCommisionForm('commisionForm')"
          >提交</el-button
        >
      </span>
    </el-dialog>
    <!--批量设置付费会员价-->
    <el-dialog
      v-if="dialogSvip"
      title="批量设置付费会员价"
      :visible.sync="dialogSvip"
      width="700px"
    >
      <el-form
        ref="svipForm"
        :model="svipForm"
        @submit.native.prevent
        label-width="80px"
      >
        <el-form-item label="参与方式：">
          <el-radio-group v-model="svipForm.svip_price_type">
            <el-radio :label="0" class="radio">不设置会员价</el-radio>
            <el-radio :label="1" class="radio">默认设置会员价</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item>
          备注：默认设置会员价是指商户在
          <router-link
            :to="{ path: roterPre + '/systemForm/Basics/svip' }"
            class="member-link"
          >
            [设置-付费会员设置]
          </router-link>
          中设置的会员折扣价，选择后每个商品默认展示此处设置的会员折扣价。
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="submitSvipForm('svipForm')"
          >提交</el-button
        >
      </span>
    </el-dialog>
    <!--批量设置弹窗-->
    <el-dialog
      v-if="batchModal"
      title="批量设置"
      :visible.sync="batchModal"
      width="750px"
    >
      <div>
        <el-alert
          title="每次只能修改一项，如需修改多项，请多次操作。"
          type="warning"
          :closable="false"
        >
        </el-alert>
        <div class="batch-tab mt20">
          <el-tabs :tab-position="tabPosition" v-model="batchName">
            <el-tab-pane label="商品分类" name="cate">
              <el-form size="small" label-width="120px" :inline="true">
                <el-form-item label="平台商品分类：">
                  <el-cascader
                    v-model="batchData.cate_id"
                    :options="categoryList"
                    :props="props"
                    clearable
                    class="width100"
                  />
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="商品标签" name="label">
              <el-form size="small" label-width="120px" :inline="true">
                <el-form-item label="商品标签：">
                  <el-select
                    v-model="batchData.mer_labels"
                    clearable
                    multiple
                    placeholder="请选择"
                    class="width100"
                  >
                    <el-option
                      v-for="item in labelList"
                      :key="item.id"
                      :label="item.name"
                      :value="item.id"
                    />
                  </el-select>
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="配送方式" name="delivery_method">
              <el-form size="small" label-width="120px" :inline="true">
                <el-form-item label="配送方式：">
                  <el-checkbox-group v-model="batchData.delivery_way">
                    <el-checkbox
                      v-for="item in deliveryList"
                      :key="item.value"
                      :label="item.value"
                    >
                      {{ item.name }}
                    </el-checkbox>
                  </el-checkbox-group>
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="运费设置" name="postage">
              <el-form size="small" label-width="120px">
                <el-form-item label="运费设置：">
                  <el-radio-group v-model="batchData.delivery_free">
                    <el-radio :label="1">包邮</el-radio>
                    <el-radio :label="0" class="radio">运费模板</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item v-if="batchData.delivery_free == 0">
                  <el-select
                    v-model="batchData.temp_id"
                    clearable
                    placeholder="请选择"
                    class="width100"
                  >
                    <el-option
                      v-for="item in tempList"
                      :key="item.shipping_template_id"
                      :label="item.name"
                      :value="item.shipping_template_id"
                    />
                  </el-select>
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="佣金设置" name="commission">
              <el-form size="small" label-width="120px" :inline="false">
                <el-form-item label="一级佣金比例：">
                  <el-input-number
                    v-model="batchData.extension_one"
                    :precision="2"
                    :step="0.1"
                    :min="0"
                    :max="1"
                    class="width100"
                    controls-position="right"
                  />
                </el-form-item>
                <el-form-item label="二级佣金比例：">
                  <el-input-number
                    v-model="batchData.extension_two"
                    :precision="2"
                    :step="0.1"
                    :min="0"
                    :max="1"
                    class="width100"
                    controls-position="right"
                  />
                </el-form-item>
                <el-form-item>
                  <span style="color:#909399"
                    >备注：订单交易成功后给上级返佣的比例，例:0.5 =
                    返订单金额的50%</span
                  >
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane
              v-if="open_svip == 1"
              label="付费会员设置"
              name="member"
            >
              <el-form label-width="120px">
                <el-form-item label="参与方式：">
                  <el-radio-group v-model="batchData.svip_price_type">
                    <el-radio :label="0" class="radio">不设置会员价</el-radio>
                    <el-radio :label="1" class="radio">默认设置会员价</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item>
                  备注：默认设置会员价是指商户在
                  <router-link
                    :to="{ path: roterPre + '/systemForm/Basics/svip' }"
                    class="member-link"
                  >
                    [设置-付费会员设置]
                  </router-link>
                  中设置的会员折扣价，选择后每个商品默认展示此处设置的会员折扣价。
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="系统表单" name="sys_form">
              <el-form label-width="120px">
                <el-form-item label="系统表单：">
                  <el-select
                    size="small"
                    clearable
                    v-model="batchData.mer_form_id"
                    @change="getFormInfo"
                    class="width100"
                  >
                    <el-option
                      v-for="items in formList"
                      :key="items.form_id"
                      :value="items.form_id"
                      :label="items.name"
                      >{{ items.name }}
                    </el-option>
                  </el-select>
                </el-form-item>
                <el-form-item v-if="formData.length > 0">
                  <el-table
                    border
                    class="specsList"
                    :data="formData"
                    size="small"
                  >
                    <el-table-column
                      prop="label"
                      label="表单标题"
                      min-width="100"
                    />
                    <el-table-column
                      prop="type"
                      label="表单类型"
                      min-width="100"
                    >
                      <template slot-scope="{ row }">
                        <span>{{ row.type | formTypeFilter }}</span>
                      </template>
                    </el-table-column>
                    <el-table-column min-width="100" label="是否必填">
                      <template slot-scope="{ row }">
                        <span>{{ row.val ? "必填" : "不必填" }}</span>
                      </template>
                    </el-table-column>
                  </el-table>
                </el-form-item>
              </el-form>
            </el-tab-pane>
            <el-tab-pane label="商品价格设置" name="price">
              <el-form size="small" label-width="60px">
                <el-form-item label="单选：">
                  <el-radio-group
                    v-model="batchData.price_type"
                    @change="changePrice"
                  >
                    <el-radio label="add">增加</el-radio>
                    <el-radio label="sub">减少</el-radio>
                    <el-radio label="mul">折扣</el-radio>
                  </el-radio-group>
                </el-form-item>
                <el-form-item>
                  <el-input-number
                    v-model="batchData.price_number"
                    :precision="2"
                    :step="0.1"
                    :min="0"
                    class="width100"
                    controls-position="right"
                  />
                  <span style="color:#909399"
                    >({{
                      batchData.price_type == "add" ||
                      batchData.price_type == "sub"
                        ? "元"
                        : "%"
                    }})</span
                  >
                </el-form-item>
                <el-form-item>
                  <div style="color:#F56464">
                    注：批量设置价格为敏感操作，请谨慎操作！
                  </div>
                </el-form-item>
              </el-form>
            </el-tab-pane>
          </el-tabs>
        </div>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="submitBatchForm">提交</el-button>
      </span>
    </el-dialog>
    <!--商品详情-->
    <pro-detail
      ref="proDetail"
      :productId="product_id"
      :configData="configData"
      @closeDrawer="closeDrawer"
      @changeDrawer="changeDrawer"
      :drawer="drawer"
    ></pro-detail>
    <!-- 添加商品 -->
    <productTypeDialog ref="productTypeDialog"></productTypeDialog>

    <!-- 选择活动标签弹窗 -->
    <el-dialog
      title="选择活动标签"
      :visible.sync="showActivityLabel"
      width="540px"
      :before-close="storeLabelClose"
    >
      <activity-label ref="storeLabelList"
        :selectedLabelIds="selectedActivityLabelIds"
        @change="handleActivityLabelChange"
        @close="storeLabelClose"  />
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
  productLstApi,
  productDeleteApi,
  categorySelectApi,
  lstFilterApi,
  statusApi,
  categoryListApi,
  destoryApi,
  restoreApi,
  productSort,
  getProductLabelApi,
  updatetProductLabel,
  batchesOnOffApi,
  productConfigApi,
  batchesLabelsApi,
  shippingListApi,
  batchesTempApi,
  productBathExtApi,
  productBathSvipApi,
  associatedFormList,
  associatedFormInfo,
  categoryBrandListApi,
  batchSetProduct,
  setActivityLabelApi,
  labelOptionsApi
} from "@/api/product";
import { roterPre } from "@/settings";
import taoBao from "./taoBao";
import editAttr from "./editAttr";
import proDetail from "./proDetails.vue";
import productTypeDialog from "../addProduct/components/productTypeDialog.vue";
import previewBox from "@/components/previewBox/index";
import activityLabel from "@/components/activityLabel/index.vue"
import LabelList from "@/components/activityLabel/label-list.vue";
import { DELIVERY_TYPE } from "@/views/systemForm/setSystem/domain/delivery.enum";

const DELIVERY_TYPE_LIST = [
  {
    value: DELIVERY_TYPE.IN_STORE + '',
    name: '到店自提/到店服务'
  },
  {
    value: DELIVERY_TYPE.EXPRESS + '',
    name: '快递配送/上门服务'
  },
  {
    value: DELIVERY_TYPE.CITY_DELIVERY + '',
    name: '同城配送'
  }
];

export default {
  name: "ProductList",
  components: { taoBao, previewBox, editAttr, proDetail, productTypeDialog, activityLabel, LabelList },
  data() {
    return {
      activityLabelProps: {
        multiple: true,
        emitPath: false
      },
      props: {
        emitPath: false
      },
      roterPre: roterPre,
      drawer: false,
      headeNum: [],
      labelList: [],
      tempList: [],
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        mer_cate_id: "",
        cate_id: "",
        brand_id: "",
        keyword: "",
        temp_id: "",
        form_id: "",
        cate_hot: "",
        type: this.$route.query.type ? this.$route.query.type : "1",
        is_ficti: "",
        is_gift_bag: "",
        us_status: "",
        mer_labels: "",
        activity_label_ids: [],
        svip_price_type: "",
        product_id: this.$route.query.id ? this.$route.query.id : "",
        setActivityIds: []
      },
      categoryList: [], // 平台
      merCateList: [], // 商户分类筛选
      BrandList: [],
      modals: false,
      tabClickIndex: "",
      multipleSelection: [],
      productStatusList: [
        { label: "上架显示", value: 1 },
        { label: "下架", value: 0 },
        { label: "平台关闭", value: -1 }
      ],
      productTypeList: [
        { label: "普通商品", value: 0 },
        { label: "虚拟商品", value: 1 },
        { label: "卡密商品", value: 3 },
        { label: "云盘商品", value: 2 },
        { label: "预约商品", value: 4 }
      ],
      tempRule: {
        temp_id: [
          { required: true, message: "请选择运费模板", trigger: "change" }
        ]
      },
      commisionRule: {
        extension_one: [
          { required: true, message: "请输入一级佣金", trigger: "change" }
        ],
        extension_two: [
          { required: true, message: "请输入二级佣金", trigger: "change" }
        ]
      },
      commisionForm: { extension_one: 0, extension_two: 0 },
      svipForm: { svip_price_type: 0 },
      goodsId: "",
      previewKey: "",
      product_id: "",
      configData: {},
      previewVisible: false,
      dialogLabel: false,
      dialogFreight: false,
      dialogCommision: false,
      dialogSvip: false,
      is_audit: false,
      deliveryType: [],
      deliveryList: [],
      labelForm: {},
      tempForm: {},
      isBatch: false,
      open_svip: false,
      batchModal: false,
      tabPosition: "left",
      formList: [],
      formData: [], //表单数据
      batchName: "cate",
      batchData: {
        delivery_way: [],
        price_type: "add",
        delivery_free: 1,
        extension_one: 0,
        extension_two: 0,
        price_number: 0
      },
      chkName: "",
      checkedIds: [], // 订单当前页选中的数据
      noChecked: [], // 订单全选状态下当前页不选中的数据
      checkedPage: [],
      allCheck: false,
      showActivityLabel: false,
      isEditMultipleLabel: false,
      selectedActivityLabelIds: [],
      activityLabelOptions: []
    };
  },
  mounted() {
    this.getActivityLabelList();
    this.getLstFilterApi();
    this.getCategorySelect();
    this.getCategoryList();
    this.getBrandList();
    this.getFormList();
    this.getList(1);
    this.getLabelLst();
    this.getTempLst();
    this.productCon();
  },
  computed: {
    activityLabelCascader() {
      const hasChildrenCategory = this.activityLabelOptions.filter(item => item.children && item.children.length);
      return this.formatActivityLabelCascader(hasChildrenCategory);
    }
  },
  methods: {
    formatActivityLabelCascader(options) {
      return options.map(item => {
        const nextItem = {
          value: item.id,
          label: item.name || item.label_name,
        }

        if (item.children && item.children.length) {
          nextItem.children = this.formatActivityLabelCascader(item.children);
        }

        return nextItem;
      });
    },
    async getActivityLabelList() {
      try {
        const res = await labelOptionsApi();
        this.activityLabelOptions = res.data.map(item => {
          return {
            ...item,
            id: `cate_${item.id}`
          };
        });
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    /**重置 */
    searchReset() {
      this.$refs.searchForm.resetFields();
      this.$refs.myCascader.$refs.panel.checkesValue = [];
      this.$refs.myCascader.$refs.panel.clearCheckedNodes();
      this.$refs.myCascader.$refs.panel.activePath = [];
      this.$refs.myCascader.$refs.panel.syncActivePath();
      this.getList(1);
      this.getLstFilterApi();
    },
    // 把每一行的索引放进row
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },
    // 添加明细原因 row 当前行 column 当前列
    tabClick(row) {
      this.tabClickIndex = row.index;
    },
    // 添加商品
    addProduct() {
      this.$refs.productTypeDialog.open();
    },
    // 失去焦点初始化
    inputBlur(scope) {
      if (!scope.row.sort || scope.row.sort < 0) scope.row.sort = 0;
      productSort(scope.row.product_id, { sort: scope.row.sort })
        .then(res => {
          this.closeEdit();
        })
        .catch(res => {});
    },
    closeEdit() {
      this.tabClickIndex = null;
    },

    getTypes(type) {
      let str = "";
      this.productTypeList.map(item => {
        if (item.value == type) {
          str = item.label;
        }
      });
      return str;
    },

    /**
     * 编辑商品
     * @param {Object} data - 商品数据对象
     * @param {number} data.product_id - 商品 ID
     * @param {number} data.type - 商品类型
     */
    editProduct(data) {
      const { product_id, type } = data;
      if (!product_id) {
        console.warn("商品 ID 未提供，无法跳转编辑页面");
        return;
      }
      this.$router.push({
        path: `${this.roterPre}/product/list/addProduct`,
        query: {
          id: product_id,
          productType: type
        }
      });
    },

    copyProduct(row) {
      this.$router.push({
        path: `${roterPre}/product/list/addProduct`,
        query: { id: row.product_id, productType: row.type, type: "copy" }
      });
    },
    // 选择商品
    onHandle(name) {
      this.chkName = this.chkName === name ? "" : name;
      this.changeType(!(this.chkName === ""));
    },
    changeType(v) {
      if (v) {
        if (!this.chkName) {
          this.chkName = "dan";
        }
      } else {
        this.chkName = "";
        this.allCheck = false;
      }
      const index = this.checkedPage.indexOf(this.tableFrom.page);
      if (this.chkName === "dan") {
        this.checkedPage.push(this.tableFrom.page);
      } else if (index > -1) {
        this.checkedPage.splice(index, 1);
      }

      this.syncCheckedId();
    },
    syncCheckedId() {
      const ids = this.tableData.data.map(v => {
        return v.product_id;
      });

      if (this.chkName === "duo") {
        this.checkedIds = [];
        this.allCheck = true;
      } else if (this.chkName === "dan") {
        this.allCheck = false;
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index === -1) {
            this.checkedIds.push(id);
          }
        });
      } else {
        ids.forEach(id => {
          const index = this.checkedIds.indexOf(id);
          if (index > -1) {
            this.checkedIds.splice(index, 1);
          }
        });
      }
    },
    // 分开选择
    changeOne(v, row) {
      if (v) {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.product_id);
          if (index > -1) this.noChecked.splice(index, 1);
        } else {
          const index = this.checkedIds.indexOf(row.product_id);
          if (index === -1) this.checkedIds.push(row.product_id);
        }
      } else {
        if (this.chkName === "duo") {
          const index = this.noChecked.indexOf(row.product_id);
          if (index === -1) this.noChecked.push(row.product_id);
        } else {
          const index = this.checkedIds.indexOf(row.product_id);
          if (index > -1) this.checkedIds.splice(index, 1);
        }
      }
    },
    // 批量设置价格
    changePrice() {
      if (this.batchData.price_type == "div") {
        this.batchData.price_number = 1;
      } else {
        this.batchData.price_number = 0;
      }
    },
    // 批量设置--提交数据
    submitBatchForm() {
      let name = this.batchName;
      switch (name) {
        case "cate":
          if (!this.batchData.cate_id) {
            return this.$message.warning("请选择商品分类！");
          }
          break;
        case "delivery_method":
          this.batchData.reservation_type = this.batchData.delivery_way.filter(item => item !== DELIVERY_TYPE.CITY_DELIVERY + '');
          if (!this.batchData.delivery_way.length) {
            return this.$message.warning("请选择配送方式！");
          }
          break;
        case "postage":
          if (
            this.batchData.delivery_free != 0 &&
            this.batchData.delivery_free != 1
          ) {
            return this.$message.warning("请选择运费设置！");
          }
          if (this.batchData.delivery_free == 0 && !this.batchData.temp_id) {
            return this.$message.warning("请选择运费模板！");
          }
          break;
        case "commission":
          this.batchData.extension_one = this.batchData.extension_one || 0;
          this.batchData.extension_two = this.batchData.extension_two || 0;
          break;
        case "price":
          if (
            this.batchData.price_type == "div" &&
            this.batchData.price_number == 0
          ) {
            return this.$message.warning("除数不能为0！");
          }
          break;
      }
      let parmas = this.batchData;
      parmas.batch_type = this.batchName;
      if (this.allCheck) {
        parmas.batch_select_type = "all";
        parmas.where = this.tableFrom;
      } else {
        parmas.batch_select_type = "select";
        parmas.ids = this.checkedIds;
      }
      batchSetProduct(parmas)
        .then(res => {
          this.batchModal = false;
          this.$message.success(res.message);
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },

    // 查看评价
    viewOn(row) {
      this.$router.push({
        path: `${this.roterPre}/product/reviews`,
        query: {
          product_id: row.product_id
        }
      });
    },

    handleSelectionChange(val) {
      this.multipleSelection = val;
      const data = [];
      this.multipleSelection.map(item => {
        data.push(item.product_id);
      });
      this.product_ids = data;
    },
    productCon() {
      productConfigApi()
        .then(res => {
          this.configData = res.data;
          this.is_audit = res.data.is_audit;
          this.open_svip =
            res.data.mer_svip_status == 1 && res.data.svip_switch_status == 1;
          this.deliveryType = res.data.delivery_way.map(String);
          this.deliveryList = DELIVERY_TYPE_LIST.filter(item => this.deliveryType.includes(item.value));
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 系统表单下拉数据
    getFormList() {
      associatedFormList()
        .then(res => {
          this.formList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 关联的表单信息
    getFormInfo() {
      if (this.batchData.mer_form_id) {
        associatedFormInfo(this.batchData.mer_form_id)
          .then(res => {
            this.formData = res.data;
          })
          .catch(res => {
            this.$message.error(res.message);
          });
      } else {
        this.formData = [];
      }
    },
    getSuccess() {
      this.getLstFilterApi();
      this.getList(1);
    },
    handleClose() {
      this.dialogLabel = false;
    },
    handleFreightClose() {
      this.dialogFreight = false;
    },
    // 添加淘宝商品成功
    onClose() {
      this.modals = false;
    },
    // 复制淘宝
    onCopy() {
      this.$refs.productTypeDialog.open(1);
    },
    // 打开批量设置弹窗
    openBatch() {
      this.batchModal = true;
      this.batchData.cate_id = ''
    },
    // 打开批量设置活动标签弹窗
    openBatchSetActivityLabel() {
      this.selectedActivityLabelIds = [];
      this.isEditMultipleLabel = true;
      this.setActivityIds = [...this.checkedIds];
      this.showActivityLabel = true;
    },
    async handleActivityLabelChange(labelList){
      const ids = labelList.map(item => item.id);
      this.showActivityLabel = false;
      try {
        const res = await setActivityLabelApi(
          this.setActivityIds,
          ids,
          !this.isEditMultipleLabel
        );
        this.$message.success(res.message);
        this.getList("");
      } catch (error) {
        this.$message.error(error.message);
      }
    },
    storeLabelClose(){
      this.showActivityLabel = false;
    },
    // 获取标签项
    getLabelLst() {
      getProductLabelApi()
        .then(res => {
          this.labelList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 获取运费模板
    getTempLst() {
      shippingListApi()
        .then(res => {
          this.tempList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 免审编辑
    onAuditFree(row) {
      this.$refs.editAttr.getAttrDetail(row.product_id);
    },
    // 批量设置佣金
    batchCommision() {
      // if(this.multipleSelection.length === 0) return this.$message.warning('请先选择商品')
      this.dialogCommision = true;
    },
    // 批量设置会员价
    batchSvip() {
      if (this.multipleSelection.length === 0)
        return this.$message.warning("请先选择商品");
      this.dialogSvip = true;
    },
    submitCommisionForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          this.commisionForm.ids = this.product_ids;
          productBathExtApi(this.commisionForm).then(({ message }) => {
            this.$message.success(message);
            this.getList("");
            this.dialogCommision = false;
          });
        } else {
          return;
        }
      });
    },
    // 提交会员价
    submitSvipForm(name) {
      this.svipForm.ids = this.product_ids;
      productBathSvipApi(this.svipForm).then(({ message }) => {
        this.$message.success(message);
        this.getList("");
        this.dialogSvip = false;
      });
    },
    // 批量上架
    batchShelf() {
      if (this.checkedIds.length === 0 && !this.allCheck)
        return this.$message.warning("请先选择商品");
      let ids = [];
      if (this.allCheck) {
        this.tableData.data.map(item => {
          ids.push(item.product_id);
        });
      } else {
        ids = this.checkedIds;
      }
      let data = { status: 1, ids: ids };
      batchesOnOffApi(data)
        .then(res => {
          this.$message.success(res.message);
          this.getLstFilterApi();
          this.getList("");
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 批量下架
    batchOff() {
      if (this.checkedIds.length === 0 && !this.allCheck)
        return this.$message.warning("请先选择商品");
      let ids = [];
      if (this.allCheck) {
        this.tableData.data.map(item => {
          ids.push(item.product_id);
        });
      } else {
        ids = this.checkedIds;
      }
      let data = { status: 0, ids: ids };
      batchesOnOffApi(data)
        .then(res => {
          this.$message.success(res.message);
          this.getLstFilterApi();
          this.getList("");
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 批量设置标签
    batchLabel() {
      this.labelForm = {
        mer_labels: [],
        ids: this.product_ids
      };
      this.isBatch = true;
      this.dialogLabel = true;
    },
    // 批量设置运费模板
    batchFreight() {
      this.dialogFreight = true;
    },
    submitTempForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          this.tempForm.ids = this.product_ids;
          batchesTempApi(this.tempForm).then(({ message }) => {
            this.$message.success(message);
            this.getList("");
            this.dialogFreight = false;
          });
        } else {
          return;
        }
      });
    },
    handleRestore(id) {
      this.$modalSure("恢复商品").then(() => {
        restoreApi(id)
          .then(res => {
            this.$message.success(res.message);
            this.getLstFilterApi();
            this.getList("");
          })
          .catch(res => {
            this.$message.error(res.message);
          });
      });
    },
    // 预览
    handlePreview(id) {
      this.previewVisible = true;
      this.goodsId = id;
      this.previewKey = "";
    },
    // 查看详情
    onDetails(row) {
      this.product_id = row.product_id;
      this.drawer = true;
      this.$refs.proDetail.getInfo(row.product_id, row.type);
    },
    changeDrawer(v) {
      this.drawer = v;
    },
    closeDrawer() {
      this.drawer = false;
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
    // 平台分类；
    getCategoryList() {
      categoryListApi()
        .then(res => {
          this.categoryList = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 品牌筛选；
    getBrandList() {
      categoryBrandListApi()
        .then((res) => {
          this.BrandList = res.data
        })
        .catch((res) => {
          this.$message.error(res.message)
        })
    },
    // 顶部切换并初始化选择的商品
    getProduct() {
      this.getList();
      this.changeType();
    },
    // 列表表头；
    getLstFilterApi() {
      lstFilterApi({
        ...this.tableFrom,
        activity_label_ids: this.tableFrom.activity_label_ids.join(",")
      })
        .then(res => {
          this.headeNum = res.data;
        })
        .catch(res => {
          this.$message.error(res.message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num || this.tableFrom.page;
      productLstApi({
        ...this.tableFrom,
        activity_label_ids: this.tableFrom.activity_label_ids.join(",")
      })
        .then(res => {
          this.tableData.data = res.data.list.map(item => {
            return {
              ...item,
              activity_label_ids: Array.isArray(item.activity_label_ids) ? item.activity_label_ids : []
            };
          });
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
      this.getLstFilterApi();
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList("");
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure(
        this.tableFrom.type !== "5" ? "加入回收站" : "删除该商品"
      ).then(() => {
        this.tableFrom.type === "5"
          ? destoryApi(id)
              .then(({ message }) => {
                this.$message.success(message);
                this.getList("");
                this.getLstFilterApi();
              })
              .catch(({ message }) => {
                this.$message.error(message);
              })
          : productDeleteApi(id)
              .then(({ message }) => {
                this.$message.success(message);
                this.getList("");
                this.getLstFilterApi();
              })
              .catch(({ message }) => {
                this.$message.error(message);
              });
      });
    },
    // 编辑活动标签
    onEditActivityLabel(row) {
      this.isEditMultipleLabel = false;
      this.selectedActivityLabelIds = row.activity_label_ids.map(item => Number(item));
      this.setActivityIds = [row.product_id];
      this.showActivityLabel = true;
    },
    // 编辑标签
    onEditLabel(row) {
      this.dialogLabel = true;
      this.product_id = row.product_id;
      this.labelForm = {
        mer_labels: row.mer_labels
      };
    },
    submitForm(name) {
      this.$refs[name].validate(valid => {
        if (valid) {
          this.isBatch
            ? batchesLabelsApi(this.labelForm).then(({ message }) => {
                this.$message.success(message);
                this.getList("");
                this.dialogLabel = false;
                this.isBatch = false;
              })
            : updatetProductLabel(this.product_id, this.labelForm).then(
                ({ message }) => {
                  this.$message.success(message);
                  this.getList("");
                  this.dialogLabel = false;
                }
              );
        } else {
          return;
        }
      });
    },
    onchangeIsShow(row) {
      statusApi(row.product_id, row.is_show)
        .then(({ message }) => {
          this.$message.success(message);
          this.getList("");
          this.getLstFilterApi();
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    }
  }
};
</script>

<style scoped lang="scss">
.bg {
  z-index: 100;
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.1);
}
a {
  text-decoration: none;
  color: #333;
}
.check {
  color: #00a2d4;
}
.spBlock {
  display: block;
  cursor: pointer;
}
.tags_name {
  font-size: 10px;
  height: 16px;
  line-height: 16px;
  padding: 0 2px;
  margin-right: 2px;
  color: var(--prev-color-primary);
  &.name0 {
    color: var(--prev-color-primary);
  }
  &.name1 {
    color: #ff8a4d;
  }
}
.max-w-300{
  max-width: 300px;
}
.member-link {
  color: var(--prev-color-primary);
}
.goods_detail .goods_detail_wrapper {
  z-index: -10;
}
::v-deep table.el-input__inner {
  padding: 0;
}
.demo-table-expand {
  font-size: 0;
}
.demo-table-expand1 ::v-deep label {
  color: #99a9bf;
}
.demo-table-expand .el-form-item {
  margin-right: 0;
  margin-bottom: 0;
  width: 33.33%;
}
.width100 {
  width: 400px;
}
.seachTiele {
  line-height: 35px;
}
.el-icon-arrow-down {
  font-size: 12px;
}
::v-deep .batch-tab .el-tabs__item {
  padding-left: 0;
  height: 50px;
  line-height: 50px;
}
.batch-tab .el-tabs {
  display: flex;
  align-items: center;
}
::v-deep .batch-tab .el-tabs__content {
  min-width: 560px;
}
.dropdown-text {
  font-size: 12px;
  color: var(--prev-color-primary) !important;
}

::v-deep .form-item-flex {
  display: flex;

  .el-form-item__content {
    flex: 1;
  }
}

::v-deep .filter-item .el-tag.el-tag--info {
  color: #303133;
}
</style>

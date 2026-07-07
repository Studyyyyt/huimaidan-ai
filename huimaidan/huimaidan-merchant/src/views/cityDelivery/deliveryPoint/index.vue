<template>
  <div class="divBox">
    <div class="selCard">
      <el-form :model="tableFrom" ref="searchForm" size="small" label-width="90px" inline>
        <el-form-item label="提货点名称：" prop="station_name">
          <el-input
            v-model="tableFrom.station_name"
            @keyup.enter.native="getList(1)"
            placeholder="请输入提货点名称"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="联系人姓名：" prop="contact_name">
          <el-input
            v-model="tableFrom.contact_name"
            @keyup.enter.native="getList(1)"
            placeholder="请输入联系人姓名"
            class="selWidth"
            clearable
          />
        </el-form-item>
        <el-form-item label="联系人电话：" prop="phone">
          <el-input
          v-model="tableFrom.phone"
          @keyup.enter.native="getList(1)"
          placeholder="请输入联系人电话"
          class="selWidth"
          clearable
          />
        </el-form-item>
        <el-form-item label="提货地址：" prop="station_address">
          <el-input
          v-model="tableFrom.station_address"
          @keyup.enter.native="getList(1)"
          placeholder="请输入提货地址"
          class="selWidth"
          clearable
          />
        </el-form-item>
        <el-form-item label="提货类型：" prop="swtich_type">
          <el-select
            v-model="tableFrom.swtich_type"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option v-for="item in pickTypeList" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" size="small" @click="getList(1)">搜索</el-button>
          <el-button size="small" @click="searchReset()">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <el-card class="mt14">
      <el-button size="small" type="primary" class="mb20" @click="add">添加提货点</el-button>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column label="序号" prop="station_id" min-width="50" />
        <el-table-column prop="station_name" label="提货点名称" min-width="100" />
        <el-table-column prop="contact_name" label="联系人姓名" min-width="100" />
        <el-table-column prop="phone" label="联系电话" min-width="100" />
        <el-table-column prop="station_address" label="提货地址" min-width="100" />
         <el-table-column prop="type" label="营业日期" min-width="150" >
          <template slot-scope="scope">
            <span v-for="day in scope.row.business_date"
              class="day-tag" :class="{'weekend': day > 5}">
              {{ getDayName(day) }}
            </span>
          </template>
        </el-table-column>
         <el-table-column prop="type" label="营业时间" min-width="100" >
          <template slot-scope="scope">
            <span >
              {{scope.row.business_time_start}} - {{scope.row.business_time_end}}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="type" label="提货类型" min-width="120" >
          <template slot-scope="scope">
            <span v-if="scope.row.switch_city==1 && scope.row.switch_take==1">同城配送、到店自提</span>
            <span v-else>{{scope.row.switch_take==1 ? '到店自提' : scope.row.switch_city==1 ? '同城配送' : ''}}</span>
          </template>
        </el-table-column>
        <el-table-column label="开启状态" min-width="90">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              @click.native="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>

        <el-table-column label="操作" min-width="100" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small" @click="onEdit(scope.row.station_id)">编辑</el-button>
            <el-button type="text" size="small" @click="onDelete(scope.row.station_id,scope.$index)">删除</el-button>
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
    <!--添加门店-->
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
import { deliveryStoreLst, deliveryStoreDetail, deliveryStoreRemark, deliveryStoreStatus, getConfigApi, deliveryStoreDelete } from '@/api/systemForm'
import { roterPre } from "@/settings";
export default {
  data() {
    return {
      dialogVisible: false,
      storeDetail: {},
      tableData: {
        data: [],
        total: 0
      },
      mapKey: "",
      keyUrl: "",
      keys: "",
      listLoading: true,
      loading: true,
      tableFrom: {
        station_name: '',
        station_address: '',
        phone: '',
        contact_name: '',
        swtich_type: '',
        page: 1,
        limit: 20
      },
      pickTypeList: [
        { value: 1, label: '同城配送' },
        { value: 2, label: '到店自提' }
      ],
      deliveryPoint:[
        {value:1,label:"达达"},
        {value:2,label:"UU"}
      ],
      delivery_type: 1,
      title: "",
    }
  },
  mounted() {
    this.getMapKey()
    this.getList(1)
  },
  methods: {
    // 将数字转换为星期几
    getDayName(day) {
      const days = ['', '周一', '周二', '周三', '周四', '周五', '周六', '周日'];
      return days[day];
    },
    /**重置 */
    searchReset(){
      this.$refs.searchForm.resetFields()
      this.getList(1)
    },
    // 添加提货点
    add() {
      this.$router.push({
        path: roterPre + "/delivery/delivery_point/form"
      });
    },
    // 编辑提货点
    onEdit(id) {
      this.$router.push({
        path: roterPre + "/delivery/delivery_point/form?id=" + id
      });
    },
    // 获取KEY值
    getMapKey() {
      getConfigApi().then(res => {
        this.mapKey = res.data.tx_map_key
        const keys = res.data.tx_map_key
        this.keys = res.data.tx_map_key
        this.keyUrl = `https://apis.map.qq.com/tools/locpicker?type=1&key=${keys}&referer=myapp`
        this.delivery_type = res.data.delivery_type
      })
      .catch(res => {
        this.$message.error(res.message)
      })
    },
    // 列表
    getList(num) {
      this.listLoading = true
      this.tableFrom.page = num || this.tableFrom.page
      deliveryStoreLst(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list
          this.tableData.total = res.data.count
          this.listLoading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.listLoading = false
        })
    },
    pageChange(page) {
      this.tableFrom.page = page
      this.getList('')
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val
      this.getList('')
    },
    // 详情
    onDetails(id) {
      this.dialogVisible = true
      deliveryStoreDetail(id)
        .then(res => {
          this.storeDetail = res.data
          this.loading = false
        })
        .catch(res => {
          this.$message.error(res.message)
          this.loading = false
        })
    },
    // 备注
    onRemark(id) {
      this.$modalForm(deliveryStoreRemark(id)).then(() => this.getList(''))
    },
    // 删除
    onDelete(id,idx) {
      this.$modalSureDelete('确定删除该门店').then(
        () => {
          deliveryStoreDelete(id)
            .then(({ message }) => {
              this.$message.success(message)
              this.tableData.data.splice(idx, 1)
              this.getList()
            })
            .catch(({ message }) => {
              this.$message.error(message)
            })
        }
      )
    },
    // 是否开通
    onchangeIsShow(row) {
      deliveryStoreStatus(row.station_id, { status: row.status })
        .then(({ message }) => {
          this.$message.success(message)
          this.getList()
        })
        .catch(({ message }) => {
          this.$message.error(message)
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.description{
  &-term {
    display: table-cell;
    padding-bottom: 10px;
    line-height: 20px;
    width: 100%;
    font-size: 12px;
  }
}

.day-tag {
  display: inline-block;
  padding-right: 12px;
  position: relative;
}
.day-tag:not(:last-child):after {
  content: '、';
  position: absolute;
  right: -4px;
}
</style>

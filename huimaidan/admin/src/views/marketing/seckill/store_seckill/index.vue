<template>
  <div class="divBox">
    <div class="selCard">
      <el-form
        :model="tableFrom"
        ref="searchForm"
        inline
        size="small"
        label-width="85px"
      >
        <el-form-item label="活动日期：">
          <el-date-picker
            v-model="timeVal"
            value-format="yyyy/MM/dd"
            format="yyyy/MM/dd"
            size="small"
            type="daterange"
            placement="bottom-end"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            style="width: 280px;"
            :picker-options="pickerOptions"
            @change="onchangeTime"
          />
        </el-form-item>
        <el-form-item label="活动名称：" prop="name">
          <el-input
            v-model="tableFrom.name"
            @keyup.enter.native="getList(1)"
            placeholder="请输入活动名称"
            class="selWidth"
          />
        </el-form-item>
        <el-form-item label="活动状态：" prop="active_status">
          <el-select
            v-model="tableFrom.active_status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList(1)"
          >
            <el-option
              v-for="item in storeStatusList"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="是否开启：" prop="seckill_active_status">
          <el-select
            v-model="tableFrom.seckill_active_status"
            placeholder="请选择"
            class="selWidth"
            clearable
            @change="getList"
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
    <el-card class="mt14">
      <div class="mb14">
        <router-link
          :to="{
            path: `${roterPre}` + '/marketing/seckill/store_seckill/create'
          }"
          class="mr10"
        >
          <el-button size="small" type="primary">添加秒杀活动</el-button>
        </router-link>
      </div>
      <el-table v-loading="listLoading" :data="tableData.data" size="small">
        <el-table-column prop="seckill_active_id" label="ID" min-width="50" />
        <el-table-column label="活动名称" prop="name" min-width="120" />
        <el-table-column prop="product_count" label="商品数量" min-width="90" />
        <el-table-column
          prop="merchant_count"
          label="店铺数量"
          min-width="90"
        />
        <el-table-column label="活动状态" prop="status_text" min-width="90" />
        <el-table-column label="活动日期" min-width="180">
          <template slot-scope="scope">
            <div>{{ scope.row.start_day }} - {{ scope.row.end_day }}</div>
          </template>
        </el-table-column>
        <el-table-column label="活动时间" min-width="100">
          <template slot-scope="scope">
            <div v-for="(item, i) in scope.row.seckill_time_text_arr" :key="i">
              {{ item }}<br />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" min-width="150" />
        <el-table-column prop="status" label="活动开关" min-width="90">
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="开启"
              inactive-text="关闭"
              @change="onchangeIsShow(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="150" fixed="right">
          <template slot-scope="scope">
            <el-button type="text" size="small">
              <router-link
                :to="{
                  path:
                    roterPre +
                    '/marketing/seckill/store_seckill/create/' +
                    scope.row.seckill_active_id +
                    '/look'
                }"
              >
                查看
              </router-link>
            </el-button>
            <el-button type="text" size="small">
              <router-link
                :to="{
                  path:
                    roterPre +
                    '/marketing/seckill/store_seckill/statistics/' +
                    scope.row.seckill_active_id
                }"
              >
                统计
              </router-link>
            </el-button>

            <el-dropdown class="ml10">
              <span class="el-dropdown-link">
                更多<i class="el-icon-arrow-down el-icon--right" />
              </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item>
                  <router-link
                    :to="{
                      path:
                        roterPre +
                        '/marketing/seckill/store_seckill/create/' +
                        scope.row.seckill_active_id +
                        '/edit'
                    }"
                  >
                    编辑
                  </router-link>
                </el-dropdown-item>
                <el-dropdown-item>
                  <router-link
                    :to="{
                      path:
                        roterPre +
                        '/marketing/seckill/store_seckill/create/' +
                        scope.row.seckill_active_id +
                        '/copy'
                    }"
                  >
                    复制
                  </router-link>
                </el-dropdown-item>
                <el-dropdown-item
                  @click.native="
                    handleDelete(scope.row.seckill_active_id, scope.$index)
                  "
                  >删除</el-dropdown-item
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
  seckillActivityList,
  seckillActivityDel,
  seckillChangeApi
} from "@/api/marketing";
import { roterPre } from "@/settings";
import timeOptions from "@/utils/timeOptions";
export default {
  name: "StoreSeckill",
  components: {},
  data() {
    return {
      pickerOptions: timeOptions,
      storeStatusList: [
        { label: "正在进行", value: 1 },
        { label: "未开始", value: 0 },
        { label: "已结束", value: -1 }
      ],
      timeVal: [],
      roterPre: roterPre,
      listLoading: true,
      tableData: {
        data: [],
        total: 0
      },
      tableFrom: {
        page: 1,
        limit: 20,
        active_status: "",
        keyword: "",
        seckill_active_status: ""
      }
    };
  },
  mounted() {
    this.getList("");
  },
  methods: {
    /**重置 */
    searchReset() {
      this.timeVal = [];
      this.tableFrom.date = "";
      this.$refs.searchForm.resetFields();
      this.getList(1);
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.tableFrom.date = e ? this.timeVal.join("-") : "";
      this.tableFrom.page = 1;
      this.getList(1);
    },
    onchangeIsShow(row) {
      seckillChangeApi(row.seckill_active_id, row.status)
        .then(({ message }) => {
          this.$message.success(message);
          this.getList("");
        })
        .catch(({ message }) => {
          this.$message.error(message);
        });
    },
    // 列表
    getList(num) {
      this.listLoading = true;
      this.tableFrom.page = num ? num : this.tableFrom.page;
      seckillActivityList(this.tableFrom)
        .then(res => {
          this.tableData.data = res.data.list;
          this.tableData.total = res.data.count;
          this.listLoading = false;
        })
        .catch(res => {
          this.listLoading = false;
          this.$message.error(res.message);
        });
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList(1);
    },
    // 删除
    handleDelete(id, idx) {
      this.$modalSure("确定删除该秒杀活动").then(() => {
        seckillActivityDel(id)
          .then(({ message }) => {
            this.$message.success(message);
            let num =
              this.tableData.data.length === 1 && this.tableFrom.page > 1
                ? this.tableFrom.page - 1
                : "";
            this.getList(num);
          })
          .catch(({ message }) => {
            this.$message.error(message);
          });
      });
    }
  }
};
</script>

<style scoped lang="scss"></style>

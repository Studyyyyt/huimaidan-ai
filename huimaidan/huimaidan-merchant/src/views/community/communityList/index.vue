<template>
  <div>
    <div class="selCard">
      <div class="container">
        <el-form
          :model="tableFrom"
          ref="searchForm"
          size="small"
          label-width="auto"
          inline
        >
          <el-form-item label="内容搜索：">
            <el-input
              v-model="tableFrom.keyword"
              clearable
              placeholder="请输入标题或者内容"
              class="selWidth"
              @change="getList(1)"
            />
          </el-form-item>
          <el-form-item label="审核状态：">
            <el-select
              v-model="tableFrom.status"
              placeholder="请选择审核状态"
              class="selWidth"
              style="width: 200px;"
              clearable
              @change="getList(1)"
            >
              <el-option
                v-for="item in statusList"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="关联话题：">
            <el-select
              v-model="tableFrom.topic_id"
              placeholder="请选择关联话题"
              class="selWidth"
              clearable
              @change="getList"
            >
              <el-option
                v-for="item in serviceList"
                :key="item.topic_id"
                :label="item.topic_name"
                :value="item.topic_id"
              ></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="内容类型：">
            <el-select
              v-model="tableFrom.is_type"
              placeholder="请选择内容类型"
              class="selWidth"
              style="width: 200px;"
              clearable
              @change="getList(1)"
            >
              <el-option
                v-for="item in typeList"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              ></el-option>
            </el-select>
          </el-form-item>

          <el-form-item>
            <el-button type="primary" size="small" @click="getList"
              >搜索</el-button
            >
            <el-button size="small" @click="reset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <el-card class="mt14 dataBox">
      <el-button type="primary" @click="addContent" size="small" class="mb20"
        >添加内容</el-button
      >
      <el-table :data="tableData.list" style="width: 100%">
        <el-table-column label="ID" prop="community_id" min-width="80" />
        <el-table-column
          :label="coverTitle"
          min-width="150"
        >
          <template slot-scope="scope">
            <div class="demo-image__preview">
              <el-image
                v-for="(item, index) in filterImgList(scope.row)"
                :key="index"
                :src="item"
                class="mr5"
                :preview-src-list="[item]"
              />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="内容描述" prop="title" min-width="100" />
        <el-table-column label="内容类型" prop="" min-width="100">
          <template slot-scope="scope">
            <span v-if="scope.row.is_type == 1">图文</span>
            <span v-else>视频</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" prop="author" min-width="100">
          <template slot-scope="scope">
            <span v-if="scope.row.status == 0" class="status col1">待审核</span>
            <span v-else-if="scope.row.status == 1" class="status col2"
              >审核通过</span
            >
            <span v-else class="status col3">审核未通过</span>
          </template>
        </el-table-column>

        <el-table-column prop="count_start" label="点赞数" min-width="100" />
        <el-table-column prop="count_reply" label="评论数" min-width="100" />
        <el-table-column prop="create_time" label="发布时间" min-width="150" />

        <el-table-column label="操作" fixed="right" width="200">
          <template slot-scope="scope">
            <el-button
              type="text"
              size="small"
              class="mr10"
              @click="onDetails(scope.row)"
              >详情</el-button
            >
            <el-button type="text" size="small" @click="handleEdit(scope.row)"
              >编辑</el-button
            >
            <el-button type="text" size="small" @click="handleDelete(scope.row)"
              >删除</el-button
            >
          </template>
        </el-table-column>
      </el-table>
      <div class="block">
        <el-pagination
          :page-size="tableFrom.limit"
          :current-page="tableFrom.page"
          background
          layout="total, prev, pager, next, jumper"
          :total="tableData.count"
          @size-change="handleSizeChange"
          @current-change="pageChange"
        />
      </div>
      <!-- 内容详情 -->
      <contentDetails ref="contentDetails"></contentDetails>
    </el-card>
  </div>
</template>
<script>
import { roterPre } from "@/settings";
import contentDetails from "./contentDetails.vue";
import {
  getCommunitylistApi,
  communityDeleteApi,
  communityCateApi
} from "@/api/community";
export default {
  name: "",
  components: { contentDetails },
  props: {},
  data() {
    return {
      tableFrom: {
        keyword: "",
        status: "",
        is_type: "",
        topic_id: "",
        limit: 20,
        page: 1
      },
      roterPre: roterPre,
      statusList: [
        { value: "0", label: "待审核" },
        { value: "1", label: "审核通过" },
        { value: "-1", label: "审核未通过" }
      ],
      typeList: [{ value: "1", label: "图文" }, { value: "2", label: "视频" }],
      serviceList: [],
      tableData: {
        list: [],
        count: 0
      }
    };
  },
  computed: {
    coverTitle() {
      const IMG_TYPE = "图文封面";
      const VIDEO_TYPE = "视频封面"
      const type = this.tableFrom.is_type;
      switch (type) {
        case "1":
          return IMG_TYPE;
        case "2":
          return VIDEO_TYPE;
        default:
          return IMG_TYPE + "/" + VIDEO_TYPE;
      }
    }
  },
  mounted() {
    this.getCateList();
    this.getList();
  },
  methods: {
    filterImgList(row) {
      if (row.is_type == "2") {
        return row.image.slice(0, 1);
      }
      return row.image;
    },
    addContent() {
      this.$router.push({
        path: `${this.roterPre}/community/list/addContent`
      });
    },

    getCateList() {
      communityCateApi().then(res => {
        this.serviceList = res.data;
      });
    },

    getList() {
      getCommunitylistApi(this.tableFrom).then(res => {
        this.tableData = res.data;
      });
    },
    // 打开详情
    onDetails(row) {
      this.$refs.contentDetails.openBox(row.community_id);
    },
    // 编辑
    handleEdit(row) {
      this.$router.push({
        path: `${this.roterPre}/community/list/addContent`,
        query: {
          id: row.community_id
        }
      });
    },
    handleDelete(row) {
      this.$modalSure("您确定要删除此内容吗?").then(() => {
        communityDeleteApi(row.community_id)
          .then(res => {
            this.$message.success(res.message);
            this.getList();
          })
          .catch(err => {
            this.$message.error(err.message);
          });
      });
    },
    reset() {
      for (let key in this.tableFrom) {
        this.tableFrom[key] = "";
      }
      this.tableFrom.limit = 20;
      this.tableFrom.page = 1;
      this.getList();
    },
    pageChange(page) {
      this.tableFrom.page = page;
      this.getList("");
    },
    handleSizeChange(val) {
      this.tableFrom.limit = val;
      this.getList("");
    }
  }
};
</script>
<style scoped lang="scss">
.block {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
.status {
  display: inline-block;
  height: 30px;
  padding: 0 10px;
  line-height: 30px;
  border-radius: 4px;
  font-size: 13px;
}
.col1 {
  color: #ff8d30;
  border: 1px solid #ff8d30;
}
.col2 {
  color: #377dff;
  border: 1px solid #377dff;
}
.col3 {
  color: #f95e45;
  border: 1px solid #f95e45;
}
</style>

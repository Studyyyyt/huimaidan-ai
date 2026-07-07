<template>
  <div class="box-container">
    <div v-for="moduleItem of renderList" :key="moduleItem.label">
      <div class="title" style="margin-top: 20px;">{{ moduleItem.label }}</div>
      <div class="acea-row">
        <template v-for="childItem of moduleItem.children">
          <v2-ubo-list v-if="childItem.type === 'ubo'" :data="childItem.value" />
          <div class="list sp" v-else>
            <label class="name">{{ childItem.label }}：</label>
            <template v-if="childItem.type === 'image'">
              <el-image class="img-preview" :src="src" :preview-src-list="childItem.value"
                v-for="(src, index) of childItem.value" :key="index" fit="cover" />
            </template>
            <template v-else>
              {{ childItem.value }}
            </template>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { moduleList } from "./v2-config.js";
import V2UboList from "./v2-ubo-list.vue";

export default {
  name: "DetailDrawerV2",
  props: {
    data: {
      type: Object
    }
  },
  components: {
    V2UboList
  },
  data() {
    return {
    }
  },
  computed: {
    renderList() {
      return moduleList.map(item => {
        return {
          label: item.label,
          children: item
            .children
            .filter(item => {
              return !item.visible || item.visible(this.data.info);
            })
            .map(item => {
              const data = {
                ...item,
                value: item.value(this.data.info),
              };
              if (data.type === "image") {
                if (Array.isArray(data.value)) {
                  data.value = data.value.map(item => item.dir);
                } else if (data.value) {
                  data.value = [data.value.dir];
                } else {
                  data.value = [];
                }
              }

              return data;
            })
        }
      });
    }
  },
  methods: {
    normalizePics(pics) {
      if (!pics) return [];
      if (Array.isArray(pics)) return pics;
      return [pics];
    }
  }
}
</script>

<style scoped lang="scss">
.box-container {
  overflow: hidden;
  padding: 0 35px;
}

.box-container .list {
  float: left;
  font-size: 13px;
  margin-top: 16px;
  color: #606266;
}

.box-container .sp {
  width: 50%;
}

.box-container .sp3 {
  width: 33.3333%;
}

.box-container .sp100 {
  width: 100%;
}

.box-container .list .blue {
  color: var(--prev-color-primary);
}

.box-container .list.image {
  // margin: 20px 0;
  position: relative;
}

.box-container .list.image .img {
  // position: absolute;
  // top: -20px;
  display: inline-block;
  vertical-align: top;
  // margin-top: 5px;

  img {
    margin-right: 10px;
  }
}


.labeltop {
  max-height: 280px;
  min-height: 120px;
  overflow-y: auto;
}

.title {
  padding-left: 10px;
  border-left: 3px solid var(--prev-color-primary);
  font-size: 14px;
  line-height: 15px;
  color: #303133;
  font-weight: bold;
}

.section {
  padding: 20px 0 8px;
  border-bottom: 1px dashed #eeeeee;
}

.img-preview {
  width: 50px;
  height: 50px;
  vertical-align: top;

  & + .img-preview {
    margin-left: 10px;
  }
}
</style>

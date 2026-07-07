<template>
  <div class="divBox ai-page">
    <el-card shadow="never">
      <el-tabs v-model="activeTab" @tab-click="handleTabChange">
        <el-tab-pane label="AI标签管理" name="tags">
          <div class="toolbar">
            <el-select v-model="tagQuery.tag_type" clearable placeholder="标签类型" size="small" style="width: 160px" @change="loadTags">
              <el-option v-for="item in tagTypes" :key="item.value" :label="item.label" :value="item.value" />
            </el-select>
            <el-input v-model="tagQuery.keyword" clearable placeholder="搜索标签" size="small" style="width: 220px" @keyup.enter.native="loadTags" />
            <el-button size="small" type="primary" @click="openTagDialog()">新增标签</el-button>
            <el-button size="small" @click="openImportDialog">批量导入</el-button>
          </div>
          <el-table :data="tagList" size="small" border>
            <el-table-column prop="tag_type" label="类型" width="120" />
            <el-table-column prop="tag_value" label="标签值" width="160" />
            <el-table-column prop="tag_label" label="展示名" width="160" />
            <el-table-column prop="synonyms" label="同义词" min-width="220" />
            <el-table-column prop="tag_weight" label="权重" width="90" />
            <el-table-column prop="status" label="状态" width="90">
              <template slot-scope="{ row }">
                <el-tag size="mini" :type="Number(row.status) === 1 ? 'success' : 'info'">{{ Number(row.status) === 1 ? '启用' : '禁用' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150" fixed="right">
              <template slot-scope="{ row }">
                <el-button type="text" size="small" @click="openTagDialog(row)">编辑</el-button>
                <el-button type="text" size="small" class="danger" @click="deleteTag(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="商户AI标签" name="merchantTags">
          <div class="toolbar">
            <el-select
              v-model="merchantTagMerId"
              clearable
              filterable
              remote
              :remote-method="loadMerchantOptions"
              :loading="merchantOptionsLoading"
              placeholder="请选择商户"
              size="small"
              style="width: 360px"
              @visible-change="handleMerchantSelectVisible"
              @change="loadMerchantTags"
            >
              <el-option
                v-for="item in merchantOptions"
                :key="item.mer_id"
                :label="item.display_name || `${item.mer_name || item.real_name}（ID:${item.mer_id}）`"
                :value="item.mer_id"
              />
            </el-select>
            <el-button size="small" type="primary" @click="loadMerchantTags">读取标签</el-button>
            <el-button size="small" @click="addMerchantTag">新增一行</el-button>
            <el-button size="small" type="success" @click="saveMerchantTags">保存人工标签</el-button>
            <el-button size="small" type="warning" @click="initMerchantTags(false)">初始化当前商户</el-button>
            <el-button size="small" @click="initMerchantTags(true)">初始化全部</el-button>
            <el-button size="small" @click="downloadMerchantTemplate">下载商户导入模板</el-button>
            <el-button size="small" type="primary" :loading="merchantImporting" @click="selectMerchantImportFile">导入商户Excel</el-button>
            <input ref="merchantImportFile" class="hidden-file" type="file" accept=".xlsx,.xls" @change="importMerchantExcel">
          </div>
          <el-table :data="merchantTagList" size="small" border>
            <el-table-column label="类型" width="170">
              <template slot-scope="{ row }">
                <el-select v-model="row.tag_type" placeholder="标签类型" size="small">
                  <el-option v-for="item in tagTypes" :key="item.value" :label="item.label" :value="item.value" />
                </el-select>
              </template>
            </el-table-column>
            <el-table-column label="标签值" min-width="180">
              <template slot-scope="{ row }"><el-input v-model="row.tag_value" size="small" /></template>
            </el-table-column>
            <el-table-column label="权重" width="130">
              <template slot-scope="{ row }"><el-input-number v-model="row.tag_weight" size="small" :min="1" :max="100" /></template>
            </el-table-column>
            <el-table-column label="来源" width="90">
              <template slot-scope="{ row }">
                <el-tag size="mini" :type="Number(row.is_auto) === 1 ? 'info' : 'success'">{{ Number(row.is_auto) === 1 ? '自动' : '人工' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="90" fixed="right">
              <template slot-scope="{ $index }">
                <el-button type="text" size="small" class="danger" @click="removeMerchantTag($index)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="AI Banner配置" name="banners">
          <el-table :data="bannerList" size="small" border>
            <el-table-column prop="meal_type" label="餐段" width="130" />
            <el-table-column prop="title_template" label="标题" width="180" />
            <el-table-column prop="subtitle_template" label="副标题" min-width="260" />
            <el-table-column prop="bg_color" label="背景色" width="120" />
            <el-table-column prop="text_color" label="文字色" width="120" />
            <el-table-column prop="is_enabled" label="状态" width="90">
              <template slot-scope="{ row }">
                <el-tag size="mini" :type="Number(row.is_enabled) === 1 ? 'success' : 'info'">{{ Number(row.is_enabled) === 1 ? '启用' : '禁用' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150" fixed="right">
              <template slot-scope="{ row }">
                <el-button type="text" size="small" @click="openBannerDialog(row)">编辑</el-button>
                <el-button type="text" size="small" class="danger" @click="deleteBanner(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="推荐参数" name="configs">
          <div class="ai-guide">
            <h3>AI 推荐大脑配置使用说明</h3>
            <ol>
              <li><b>先维护商户资料：</b>在 CRMEB 原有商户后台维护商户名称、分类、城市、地址、经纬度、头像、状态、精选、销量等基础资料；商户必须启用且营业，才适合进入推荐。</li>
              <li><b>再维护惠买单优惠：</b>在惠买单优惠规则页面配置商家结算折扣和会员消费折扣；AI 会优先从有可用优惠的商户里召回。</li>
              <li><b>补充 AI 标签：</b>在“AI标签管理”维护分类、场景、口味、设施、价格、餐段、优惠等标签；在“商户AI标签”给每个商户打标签，例如 <code>火锅</code>、<code>聚餐</code>、<code>辣</code>、<code>包间</code>、<code>午餐</code>。</li>
              <li><b>配置首页 Banner：</b>在“AI Banner配置”维护早餐、午餐、下午茶、晚餐、夜宵等文案和颜色；推荐商户由“当前餐段 + 位置 + 优惠 + 评分热度”动态产生。</li>
              <li><b>调整推荐权重：</b><code>score_weight_tag</code> 控制标签匹配，<code>score_weight_distance</code> 控制距离，<code>score_weight_heat</code> 控制销量/评分/精选，<code>score_weight_promo</code> 控制优惠力度。想让“想吃火锅”更准，就提高标签权重；想让“附近”更明显，就提高距离权重。</li>
              <li><b>调整召回范围：</b><code>recall_radius_km</code> 是附近半径，<code>recall_max_candidates</code> 是候选商户上限，<code>result_limit</code> 是 AI 对话返回商户数量。</li>
              <li><b>控制使用安全：</b><code>daily_chat_limit</code> 控制单个用户每日 AI 对话次数，<code>input_max_length</code> 控制用户输入长度，<code>sensitive_words</code> 可配置敏感词，多个词用逗号或换行分隔。</li>
              <li><b>查看效果：</b>到“推荐日志”查看用户输入、识别标签、召回数量、推荐商户、耗时和用户点击/反馈；如果推荐不准，优先检查商户城市、优惠、AI 标签，再调整权重。</li>
            </ol>
          </div>
          <el-table :data="configList" size="small" border>
            <el-table-column prop="config_key" label="配置键" width="220" />
            <el-table-column prop="config_value" label="配置值" width="180" />
            <el-table-column prop="config_desc" label="说明" min-width="260" />
            <el-table-column label="操作" width="150" fixed="right">
              <template slot-scope="{ row }">
                <el-button type="text" size="small" @click="openConfigDialog(row)">编辑</el-button>
                <el-button type="text" size="small" class="danger" @click="deleteConfig(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <el-tab-pane label="推荐日志" name="logs">
          <div class="toolbar">
            <el-input v-model="logQuery.uid" clearable placeholder="用户ID" size="small" style="width: 160px" @keyup.enter.native="loadLogs" />
            <el-input v-model="logQuery.session_id" clearable placeholder="Session ID" size="small" style="width: 260px" @keyup.enter.native="loadLogs" />
            <el-button size="small" type="primary" @click="loadLogs">查询</el-button>
          </div>
          <el-table :data="logList" size="small" border>
            <el-table-column prop="log_id" label="ID" width="80" />
            <el-table-column prop="uid" label="用户" width="90" />
            <el-table-column prop="query_text" label="用户输入" min-width="220" />
            <el-table-column prop="recall_count" label="召回" width="80" />
            <el-table-column prop="result_mer_ids" label="推荐商户" min-width="180" />
            <el-table-column prop="click_mer_id" label="点击商户" width="100" />
            <el-table-column prop="order_mer_id" label="买单商户" width="100" />
            <el-table-column prop="user_feedback" label="反馈" width="90">
              <template slot-scope="{ row }">
                <span>{{ feedbackText(row.user_feedback) }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="degraded" label="降级" width="80">
              <template slot-scope="{ row }">
                <el-tag size="mini" :type="Number(row.degraded) === 1 ? 'warning' : 'success'">{{ Number(row.degraded) === 1 ? '是' : '否' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="response_time_ms" label="耗时ms" width="100" />
            <el-table-column prop="create_time" label="时间" width="170" />
          </el-table>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <el-dialog :title="tagForm.tag_id ? '编辑AI标签' : '新增AI标签'" :visible.sync="tagDialogVisible" width="520px">
      <el-form label-width="90px" size="small">
        <el-form-item label="标签类型">
          <el-select v-model="tagForm.tag_type" placeholder="请选择">
            <el-option v-for="item in tagTypes" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="标签值"><el-input v-model="tagForm.tag_value" /></el-form-item>
        <el-form-item label="展示名"><el-input v-model="tagForm.tag_label" /></el-form-item>
        <el-form-item label="同义词"><el-input v-model="tagForm.synonyms" placeholder="用逗号分隔，如：不贵,实惠" /></el-form-item>
        <el-form-item label="权重"><el-input-number v-model="tagForm.tag_weight" :min="1" :max="100" /></el-form-item>
        <el-form-item label="状态"><el-switch v-model="tagForm.status" :active-value="1" :inactive-value="0" /></el-form-item>
      </el-form>
      <span slot="footer">
        <el-button size="small" @click="tagDialogVisible = false">取消</el-button>
        <el-button size="small" type="primary" @click="saveTag">保存</el-button>
      </span>
    </el-dialog>

    <el-dialog title="批量导入AI标签" :visible.sync="importDialogVisible" width="720px">
      <el-input
        v-model="importText"
        type="textarea"
        :autosize="{ minRows: 8, maxRows: 16 }"
        placeholder='[{"tag_type":"category","tag_value":"火锅","tag_label":"火锅","synonyms":["涮肉"],"tag_weight":10,"sort":0,"status":1}]'
      />
      <span slot="footer">
        <el-button size="small" @click="importDialogVisible = false">取消</el-button>
        <el-button size="small" type="primary" @click="importTags">导入</el-button>
      </span>
    </el-dialog>

    <el-dialog title="编辑Banner配置" :visible.sync="bannerDialogVisible" width="620px">
      <el-form label-width="90px" size="small">
        <el-form-item label="餐段"><el-input v-model="bannerForm.meal_type" disabled /></el-form-item>
        <el-form-item label="标题"><el-input v-model="bannerForm.title_template" /></el-form-item>
        <el-form-item label="副标题"><el-input v-model="bannerForm.subtitle_template" /></el-form-item>
        <el-form-item label="背景色"><el-input v-model="bannerForm.bg_color" /></el-form-item>
        <el-form-item label="文字色"><el-input v-model="bannerForm.text_color" /></el-form-item>
        <el-form-item label="状态"><el-switch v-model="bannerForm.is_enabled" :active-value="1" :inactive-value="0" /></el-form-item>
      </el-form>
      <span slot="footer">
        <el-button size="small" @click="bannerDialogVisible = false">取消</el-button>
        <el-button size="small" type="primary" @click="saveBanner">保存</el-button>
      </span>
    </el-dialog>

    <el-dialog title="编辑推荐参数" :visible.sync="configDialogVisible" width="520px">
      <el-form label-width="90px" size="small">
        <el-form-item label="配置键"><el-input v-model="configForm.config_key" disabled /></el-form-item>
        <el-form-item label="配置值"><el-input v-model="configForm.config_value" type="textarea" :autosize="{ minRows: 3, maxRows: 8 }" /></el-form-item>
        <el-form-item label="说明"><el-input v-model="configForm.config_desc" /></el-form-item>
      </el-form>
      <span slot="footer">
        <el-button size="small" @click="configDialogVisible = false">取消</el-button>
        <el-button size="small" type="primary" @click="saveConfig">保存</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { aiBannerDeleteApi, aiBannerSaveApi, aiBannersApi, aiConfigDeleteApi, aiConfigSaveApi, aiConfigsApi, aiLogsApi, aiMerchantImportApi, aiMerchantImportTemplateUrl, aiMerchantTagsApi, aiMerchantTagsInitApi, aiMerchantTagsSaveApi, aiMerchantsApi, aiTagDeleteApi, aiTagImportApi, aiTagSaveApi, aiTagsApi } from '@/api/huimaidanAi'

export default {
  name: 'HuimaidanAi',
  data() {
    return {
      activeTab: 'tags',
      tagTypes: [
        { label: '品类', value: 'category' },
        { label: '场景', value: 'scene' },
        { label: '口味', value: 'taste' },
        { label: '设施', value: 'facility' },
        { label: '价格', value: 'price' },
        { label: '特色', value: 'feature' },
        { label: '餐段', value: 'meal' },
        { label: '优惠', value: 'promotion' }
      ],
      tagQuery: { tag_type: '', keyword: '', page: 1, limit: 50 },
      tagList: [],
      tagDialogVisible: false,
      tagForm: {},
      importDialogVisible: false,
      importText: '',
      merchantTagMerId: '',
      merchantOptions: [],
      merchantOptionsLoading: false,
      merchantTagList: [],
      merchantImporting: false,
      bannerList: [],
      bannerDialogVisible: false,
      bannerForm: {},
      configList: [],
      configDialogVisible: false,
      configForm: {},
      logQuery: { uid: '', session_id: '', page: 1, limit: 50 },
      logList: []
    }
  },
  mounted() {
    this.loadTags()
    this.loadMerchantOptions()
  },
  methods: {
    handleTabChange() {
      if (this.activeTab === 'tags') this.loadTags()
      if (this.activeTab === 'merchantTags') {
        this.loadMerchantOptions()
        if (this.merchantTagMerId) this.loadMerchantTags()
      }
      if (this.activeTab === 'banners') this.loadBanners()
      if (this.activeTab === 'configs') this.loadConfigs()
      if (this.activeTab === 'logs') this.loadLogs()
    },
    loadTags() {
      aiTagsApi(this.tagQuery).then(res => {
        this.tagList = res.data ? res.data.list || [] : res.list || []
      })
    },
    openTagDialog(row = {}) {
      this.tagForm = { tag_weight: 10, status: 1, ...row, synonyms: this.formatSynonyms(row.synonyms) }
      this.tagDialogVisible = true
    },
    saveTag() {
      aiTagSaveApi(this.tagForm).then(res => {
        this.$message.success(res.message || '保存成功')
        this.tagDialogVisible = false
        this.loadTags()
      })
    },
    deleteTag(row) {
      this.$modalSure('删除该AI标签吗？').then(() => aiTagDeleteApi(row.tag_id)).then(res => {
        this.$message.success(res.message || '删除成功')
        this.loadTags()
      })
    },
    openImportDialog() {
      this.importText = ''
      this.importDialogVisible = true
    },
    importTags() {
      let tags = []
      try {
        tags = JSON.parse(this.importText)
      } catch (e) {
        this.$message.warning('请填写合法的 JSON 数组')
        return
      }
      if (!Array.isArray(tags) || tags.length === 0) {
        this.$message.warning('导入内容不能为空')
        return
      }
      aiTagImportApi(tags).then(res => {
        const data = res.data || {}
        this.$message.success(`导入成功：${data.count || 0} 条`)
        this.importDialogVisible = false
        this.loadTags()
      })
    },
    loadMerchantOptions(keyword = '') {
      this.merchantOptionsLoading = true
      aiMerchantsApi({ keyword, page: 1, limit: 200 }).then(res => {
        this.merchantOptions = res.data ? res.data.list || res.data : []
      }).finally(() => {
        this.merchantOptionsLoading = false
      })
    },
    handleMerchantSelectVisible(visible) {
      if (visible && !this.merchantOptions.length) this.loadMerchantOptions()
    },
    loadMerchantTags() {
      const merId = Number(this.merchantTagMerId)
      if (!merId) {
        this.$message.warning('请先从下拉选择商户')
        return
      }
      aiMerchantTagsApi(merId).then(res => {
        this.merchantTagList = (res.data || []).map(item => ({
          tag_type: item.tag_type,
          tag_value: item.tag_value,
          tag_weight: Number(item.tag_weight || 10),
          is_auto: Number(item.is_auto || 0)
        }))
      })
    },
    addMerchantTag() {
      this.merchantTagList.push({ tag_type: 'category', tag_value: '', tag_weight: 10, is_auto: 0 })
    },
    removeMerchantTag(index) {
      this.merchantTagList.splice(index, 1)
    },
    saveMerchantTags() {
      const merId = Number(this.merchantTagMerId)
      if (!merId) {
        this.$message.warning('请先从下拉选择商户')
        return
      }
      const manualTags = this.merchantTagList
        .filter(item => Number(item.is_auto) !== 1)
        .map(item => ({ tag_type: item.tag_type, tag_value: item.tag_value, tag_weight: item.tag_weight }))
      aiMerchantTagsSaveApi(merId, manualTags).then(res => {
        this.$message.success(res.message || '保存成功')
        this.loadMerchantTags()
      })
    },
    initMerchantTags(all) {
      const merId = all ? 0 : Number(this.merchantTagMerId)
      if (!all && !merId) {
        this.$message.warning('请先从下拉选择商户')
        return
      }
      const message = all ? '初始化全部活跃商户AI自动标签吗？' : '初始化当前商户AI自动标签吗？'
      this.$modalSure(message).then(() => aiMerchantTagsInitApi(merId)).then(res => {
        const data = res.data || {}
        this.$message.success(`初始化完成：${data.merchant_count || 0} 个商户，${data.tag_count || 0} 个标签`)
        if (!all) this.loadMerchantTags()
      })
    },
    downloadMerchantTemplate() {
      window.open(aiMerchantImportTemplateUrl())
    },
    selectMerchantImportFile() {
      if (this.$refs.merchantImportFile) {
        this.$refs.merchantImportFile.click()
      }
    },
    importMerchantExcel(event) {
      const file = event.target.files && event.target.files[0]
      if (!file) return
      const data = new FormData()
      data.append('file', file)
      this.merchantImporting = true
      aiMerchantImportApi(data).then(res => {
        const result = res.data || {}
        const errors = Array.isArray(result.errors) && result.errors.length ? `，失败原因：${result.errors.slice(0, 3).join('；')}` : ''
        this.$message.success(`导入完成：新增${result.created || 0}，更新${result.updated || 0}，失败${result.failed || 0}${errors}`)
        if (this.merchantTagMerId) this.loadMerchantTags()
      }).finally(() => {
        this.merchantImporting = false
        event.target.value = ''
      })
    },
    loadBanners() {
      aiBannersApi().then(res => {
        this.bannerList = res.data || []
      })
    },
    openBannerDialog(row) {
      this.bannerForm = { ...row }
      this.bannerDialogVisible = true
    },
    saveBanner() {
      aiBannerSaveApi(this.bannerForm).then(res => {
        this.$message.success(res.message || '保存成功')
        this.bannerDialogVisible = false
        this.loadBanners()
      })
    },
    deleteBanner(row) {
      this.$modalSure('删除该Banner配置吗？').then(() => aiBannerDeleteApi(row.config_id)).then(res => {
        this.$message.success(res.message || '删除成功')
        this.loadBanners()
      })
    },
    loadConfigs() {
      aiConfigsApi().then(res => {
        this.configList = res.data || []
      })
    },
    openConfigDialog(row) {
      this.configForm = { ...row }
      this.configDialogVisible = true
    },
    saveConfig() {
      aiConfigSaveApi(this.configForm).then(res => {
        this.$message.success(res.message || '保存成功')
        this.configDialogVisible = false
        this.loadConfigs()
      })
    },
    deleteConfig(row) {
      this.$modalSure('删除该推荐参数吗？').then(() => aiConfigDeleteApi(row.config_id)).then(res => {
        this.$message.success(res.message || '删除成功')
        this.loadConfigs()
      })
    },
    loadLogs() {
      aiLogsApi(this.logQuery).then(res => {
        this.logList = res.data ? res.data.list || [] : res.list || []
      })
    },
    formatSynonyms(value) {
      if (!value) return ''
      if (Array.isArray(value)) return value.join(',')
      try {
        const parsed = JSON.parse(value)
        return Array.isArray(parsed) ? parsed.join(',') : value
      } catch (e) {
        return value
      }
    },
    feedbackText(value) {
      if (Number(value) === 1) return '点赞'
      if (Number(value) === -1) return '点踩'
      if (Number(value) === 0) return '无反馈'
      return '-'
    }
  }
}
</script>

<style scoped lang="scss">
.ai-page {
  .toolbar {
    display: flex;
    gap: 10px;
    margin-bottom: 14px;
  }
  .danger {
    color: #f56c6c;
  }
  .hidden-file {
    display: none;
  }
  .ai-guide {
    margin-bottom: 16px;
    padding: 16px 18px;
    border: 1px solid #ebeef5;
    border-radius: 4px;
    background: #fafafa;
    color: #606266;
    line-height: 1.8;

    h3 {
      margin: 0 0 8px;
      color: #303133;
      font-size: 15px;
    }

    ol {
      margin: 0;
      padding-left: 20px;
    }

    code {
      padding: 2px 6px;
      border-radius: 3px;
      background: #f0f2f5;
      color: #409eff;
    }
  }
}
</style>

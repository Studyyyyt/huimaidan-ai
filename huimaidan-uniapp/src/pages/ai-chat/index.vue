<script lang="ts" setup>
import type { IAiChatResponse, IAiOnboardingConfig, IAiRecommendMerchant } from '@/api/ai'
import { onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app'
import { getAiOnboardingConfig, postAiChat, postAiEvent } from '@/api/ai'
import { getStoreList, getStoreNearby } from '@/api/huimaidan'
import { useLocationStore, useTokenStore, useUserStore } from '@/store'
import { toLoginPage } from '@/utils/toLoginPage'

defineOptions({
  name: 'AiChat',
})

definePage({
  style: {
    navigationBarTitleText: 'AI 小惠',
    navigationStyle: 'default',
  },
})

// 位置、用户、登录态 store
const locationStore = useLocationStore()
const userStore = useUserStore()
const tokenStore = useTokenStore()

// 消息列表（阶段四会拆分为独立组件）
interface IChatMessage extends IAiChatResponse {
  /** 消息发送方：user 为用户，ai 为 AI 回复 */
  role: 'user' | 'ai'
  feedback?: -1 | 1
}
const messages = ref<IChatMessage[]>([])
// 输入框内容
const inputValue = ref('')
// 发送中状态
const isLoading = ref(false)
// 会话 ID
const sessionId = ref('')
const selectedShareMerchant = ref<IAiRecommendMerchant | null>(null)
// 请求安全超时定时器，防止 loading 状态卡住
let loadingTimer: ReturnType<typeof setTimeout> | null = null
// 滚动定位 ID，用于自动滚动到底部
const scrollIntoViewId = ref('')
// 新手引导配置与显隐
const onboardingConfig = ref<IAiOnboardingConfig | null>(null)
const showOnboarding = ref(false)
const ONBOARDING_CLOSED_KEY = 'ai_guide_closed'
const welcomeText = ref('我是AI小惠，告诉我：你想吃什么？预算多少?距离优先还是折扣优先？和您同行的，有小孩或者老人吗？')

/**
 * 构建对话请求参数
 */
function buildChatParams(message: string): {
  session_id?: string
  message: string
  latitude?: number
  longitude?: number
  city_id?: number
  city_name?: string
} {
  const params: {
    session_id?: string
    message: string
    latitude?: number
    longitude?: number
    city_id?: number
    city_name?: string
  } = {
    message,
  }

  if (sessionId.value) {
    params.session_id = sessionId.value
  }
  if (locationStore.hasCoordinates) {
    params.latitude = locationStore.latitude ?? undefined
    params.longitude = locationStore.longitude ?? undefined
  }
  if (locationStore.cityId) {
    params.city_id = locationStore.cityId
  }
  else if (locationStore.city) {
    params.city_name = locationStore.city
  }

  return params
}

/**
 * 滚动到消息列表底部
 */
function scrollToBottom() {
  void nextTick(() => {
    scrollIntoViewId.value = ''
    void nextTick(() => {
      scrollIntoViewId.value = `msg-${messages.value.length - 1}`
    })
  })
}

/**
 * 发送消息
 */
async function handleSend() {
  const message = inputValue.value.trim()
  console.log('[AI Chat] 点击发送，输入内容：', message, '加载中：', isLoading.value)
  if (!message || isLoading.value) {
    if (!message) {
      uni.showToast({ title: '请输入内容', icon: 'none' })
    }
    return
  }

  if (!tokenStore.updateNowTime().hasLogin) {
    uni.showToast({ title: '请先登录', icon: 'none' })
    setTimeout(() => {
      toLoginPage({ queryString: `?redirect=${encodeURIComponent('/pages/ai-chat/index')}` })
    }, 600)
    return
  }

  isLoading.value = true
  inputValue.value = ''

  // 先展示用户消息
  const userMessage: IChatMessage = {
    role: 'user',
    session_id: sessionId.value || '',
    type: 'text',
    content: { text: message },
  }
  messages.value.push(userMessage)
  scrollToBottom()

  // 安全兜底：AI chat 请求可等待 75 秒，页面只做最终兜底。
  if (loadingTimer) {
    clearTimeout(loadingTimer)
  }
  loadingTimer = setTimeout(() => {
    if (isLoading.value) {
      isLoading.value = false
      uni.showToast({ title: '请求超时，请重试', icon: 'none' })
    }
  }, 80000)

  try {
    const params = buildChatParams(message)
    const res = await postAiChat(params)
    sessionId.value = res.session_id
    messages.value.push({ ...res, role: 'ai' })
    scrollToBottom()
  }
  catch (error) {
    console.error('AI 对话失败:', error)
    const fallback = await buildFallbackMessage(message)
    messages.value.push(fallback)
    scrollToBottom()
    uni.showToast({ title: 'AI 服务繁忙，已推荐热门商家', icon: 'none' })
  }
  finally {
    isLoading.value = false
    if (loadingTimer) {
      clearTimeout(loadingTimer)
      loadingTimer = null
    }
  }
}

async function buildFallbackMessage(message: string): Promise<IChatMessage> {
  const merchants = await fallbackMerchants()
  return {
    role: 'ai',
    session_id: sessionId.value || '',
    type: merchants.length ? 'recommend' : 'text',
    degraded: true,
    error_message: 'AI服务暂时繁忙',
    content: {
      text: merchants.length ? 'AI 服务繁忙，为你先推荐附近热门商家。' : 'AI 服务繁忙，暂时无法获取推荐商家，请稍后再试。',
      merchants,
      intent_tags: {
        action: 'fallback',
        query: message,
      },
    },
  }
}

async function fallbackMerchants(): Promise<IAiRecommendMerchant[]> {
  try {
    const res = locationStore.hasCoordinates
      ? await getStoreNearby({
          latitude: locationStore.latitude as number,
          longitude: locationStore.longitude as number,
          distance: 5,
          page: 1,
          limit: 3,
        })
      : await getStoreList({
          order: 'default',
          page: 1,
          limit: 3,
        })
    return (res.list || []).map(item => ({
      ...item,
      recommend_reason: item.distance ? `附近热门商家，距离约${item.distance}。` : '当前热门优惠商家。',
    }))
  }
  catch (error) {
    console.error('AI 对话降级推荐失败:', error)
    return []
  }
}

function reportAiEvent(source: IChatMessage, event: 'click' | 'detail' | 'navigate' | 'order' | 'feedback', merId?: number, feedback?: -1 | 0 | 1) {
  if (!tokenStore.updateNowTime().hasLogin) {
    return
  }
  void postAiEvent({
    log_id: source.log_id,
    session_id: source.session_id || sessionId.value,
    event,
    mer_id: merId,
    feedback,
  }).catch((error) => {
    console.error('AI 行为上报失败:', error)
  })
}

function goMerchantDetail(merchant: IAiRecommendMerchant, source: IChatMessage) {
  reportAiEvent(source, 'detail', merchant.mer_id)
  uni.navigateTo({
    url: `/pages/merchant/detail?id=${merchant.mer_id}&ai_log_id=${source.log_id || ''}`,
  })
}

function handleShareMerchant(merchant: IAiRecommendMerchant, source: IChatMessage) {
  selectedShareMerchant.value = merchant
  reportAiEvent(source, 'click', merchant.mer_id)
}

function resolveShareMerchant(options?: any) {
  const dataset = options?.target?.dataset || {}
  const merchantId = Number(dataset.merId || dataset.mer_id || 0)
  if (merchantId > 0) {
    return {
      mer_id: merchantId,
      mer_name: dataset.merName || dataset.mer_name || '惠买单优惠商家',
      mer_avatar: dataset.merAvatar || dataset.mer_avatar || '',
    } as IAiRecommendMerchant
  }
  return selectedShareMerchant.value
}

function buildMerchantSharePayload(merchant?: IAiRecommendMerchant | null) {
  if (merchant?.mer_id) {
    const payload: Record<string, string> = {
      title: `${merchant.mer_name || '优惠商家'}，这家店可以看看`,
      path: `/pages/merchant/detail?id=${merchant.mer_id}&from=ai_share`,
    }
    if (merchant.mer_avatar)
      payload.imageUrl = merchant.mer_avatar
    return payload
  }

  return {
    title: 'AI 小惠帮你找附近优惠好店',
    path: '/pages/ai-chat/index',
  }
}

function openLocation(merchant: IAiRecommendMerchant, source: IChatMessage) {
  const latitude = Number.parseFloat(String(merchant.latitude ?? ''))
  const longitude = Number.parseFloat(String(merchant.longitude ?? ''))
  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    uni.showToast({ title: '商家暂无定位', icon: 'none' })
    return
  }
  reportAiEvent(source, 'navigate', merchant.mer_id)
  uni.openLocation({
    latitude,
    longitude,
    name: merchant.mer_name || '',
    address: merchant.mer_address || '',
    scale: 16,
  })
}

function sendFeedback(msg: IChatMessage, feedback: -1 | 1) {
  msg.feedback = feedback
  reportAiEvent(msg, 'feedback', undefined, feedback)
  uni.showToast({ title: feedback === 1 ? '已收到点赞' : '已收到反馈', icon: 'none' })
}

/**
 * 获取新手引导本地缓存键，按用户隔离
 */
function onboardingClosedKey(version = 'default'): string {
  const uid = userStore.userInfo?.userId || userStore.userInfo?.uid || 0
  return `${ONBOARDING_CLOSED_KEY}_${uid || 'guest'}_${version || 'default'}`
}

/**
 * 检查是否需要展示新手引导
 */
async function checkOnboarding(): Promise<void> {
  try {
    const config = await getAiOnboardingConfig()
    welcomeText.value = config.chat_welcome_text || welcomeText.value
    if (!config || !config.enabled)
      return
    const closed = uni.getStorageSync(onboardingClosedKey(config.version || config.updated_at || 'default'))
    if (closed)
      return
    onboardingConfig.value = config
    showOnboarding.value = true
  }
  catch (error) {
    console.error('加载新手引导配置失败:', error)
  }
}

/**
 * 关闭新手引导浮层
 */
function closeOnboarding() {
  showOnboarding.value = false
  try {
    const version = onboardingConfig.value?.version || onboardingConfig.value?.updated_at || 'default'
    uni.setStorageSync(onboardingClosedKey(version), '1')
  }
  catch (error) {
    console.error('写入新手引导缓存失败:', error)
  }
}

/**
 * 点击示例问题，自动填入并发送
 */
function sendExampleQuestion(question: string) {
  closeOnboarding()
  inputValue.value = question
  void nextTick(() => {
    handleSend()
  })
}

onLoad((options) => {
  // 重置加载状态，防止热更新或页面保活后状态异常
  isLoading.value = false
  // 页面加载时获取用户信息（用于日志记录）
  if (tokenStore.updateNowTime().hasLogin && userStore.userInfo.userId <= 0) {
    void userStore.fetchUserInfo().catch((error) => {
      console.error('AI 对话页获取用户信息失败:', error)
    })
  }
  void checkOnboarding()
  // 如果从首页搜索框带关键词进入，自动填入并发送
  const keyword = options?.keyword
  if (keyword) {
    inputValue.value = decodeURIComponent(keyword)
    void nextTick(() => {
      handleSend()
    })
  }
})

onShareAppMessage(options => buildMerchantSharePayload(resolveShareMerchant(options)))

onShareTimeline(() => ({
  title: 'AI 小惠帮你找附近优惠好店',
  query: 'from=ai_share',
}))
</script>

<template>
  <view class="ai-chat-page">
    <!-- 消息列表区域 -->
    <scroll-view
      class="ai-chat-scroll"
      scroll-y
      scroll-with-animation
      :scroll-into-view="scrollIntoViewId"
    >
      <view class="p-4">
        <!-- 欢迎卡片 -->
        <view class="rounded-2xl bg-white p-4 shadow-sm">
          <text class="text-14px text-gray-600">
            {{ welcomeText }}
          </text>
        </view>

        <!-- 消息气泡（阶段四拆分为独立组件） -->
        <view
          v-for="(msg, index) in messages"
          :id="`msg-${index}`"
          :key="index"
          class="mt-3 flex"
          :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <view
            class="rounded-2xl p-4 shadow-sm"
            style="max-width: 80%;"
            :class="msg.role === 'user' ? 'bg-blue-500 text-white' : 'bg-white text-gray-800'"
          >
            <text class="text-14px">{{ msg.content.text }}</text>
            <view v-if="msg.role === 'ai' && msg.content.merchants?.length" class="mt-2">
              <view
                v-for="merchant in msg.content.merchants"
                :key="merchant.mer_id"
                class="mt-2 rounded-lg bg-gray-50 p-3"
              >
                <view class="flex gap-3">
                  <image
                    v-if="merchant.mer_avatar"
                    :src="merchant.mer_avatar"
                    class="h-56px w-56px flex-shrink-0 rounded-lg bg-gray-100"
                    mode="aspectFill"
                  />
                  <view class="min-w-0 flex-1">
                    <text class="block truncate text-14px font-bold">{{ merchant.mer_name }}</text>
                    <text class="mt-1 block text-12px text-gray-500">{{ merchant.recommend_reason }}</text>
                    <view class="mt-2 flex flex-wrap gap-1">
                      <text v-if="merchant.distance" class="rounded-full bg-white px-2 py-1 text-10px text-gray-500">{{ merchant.distance }}</text>
                      <text v-if="merchant.discount_label" class="rounded-full bg-orange-50 px-2 py-1 text-10px text-orange-500">{{ merchant.discount_label }}</text>
                      <text v-if="merchant.rating" class="rounded-full bg-white px-2 py-1 text-10px text-gray-500">{{ merchant.rating }}分</text>
                      <text v-if="merchant.price_per_person_text" class="rounded-full bg-white px-2 py-1 text-10px text-gray-500">{{ merchant.price_per_person_text }}</text>
                    </view>
                    <text v-if="merchant.mer_address" class="mt-2 block truncate text-11px text-gray-400">{{ merchant.mer_address }}</text>
                  </view>
                </view>
                <view class="mt-3 flex items-center justify-end gap-2">
                  <button class="ai-card-btn" @tap.stop="openLocation(merchant, msg)">
                    导航
                  </button>
                  <button class="ai-card-btn" @tap.stop="goMerchantDetail(merchant, msg)">
                    详情
                  </button>
                  <button
                    class="ai-card-btn ai-card-btn--primary"
                    open-type="share"
                    :data-mer-id="merchant.mer_id"
                    :data-mer-name="merchant.mer_name"
                    :data-mer-avatar="merchant.mer_avatar"
                    @tap.stop="handleShareMerchant(merchant, msg)"
                  >
                    分享
                  </button>
                </view>
              </view>
            </view>
            <view v-if="msg.role === 'ai' && msg.content.merchants?.length" class="mt-3 flex items-center justify-end gap-2">
              <button class="ai-feedback-btn" :class="{ 'ai-feedback-btn--active': msg.feedback === 1 }" @tap.stop="sendFeedback(msg, 1)">
                有用
              </button>
              <button class="ai-feedback-btn" :class="{ 'ai-feedback-btn--active': msg.feedback === -1 }" @tap.stop="sendFeedback(msg, -1)">
                不准
              </button>
            </view>
          </view>
        </view>

        <!-- 加载动画（阶段四拆分为独立组件） -->
        <view v-if="isLoading" class="mt-3 flex items-center rounded-2xl bg-white p-4 shadow-sm">
          <text class="i-carbon-bot text-18px text-purple-500" />
          <text class="ml-2 text-14px text-gray-500">AI 思考中...</text>
        </view>
      </view>
    </scroll-view>

    <!-- 新手引导浮层 -->
    <view v-if="showOnboarding" class="onboarding-overlay" @tap="void 0">
      <view class="onboarding-mask" @tap="closeOnboarding" />
      <view class="onboarding-content" @tap.stop="void 0">
        <view class="onboarding-header">
          <text class="onboarding-title">{{ onboardingConfig?.title || '你好，我是惠买单 AI 助手' }}</text>
          <text class="onboarding-close" @tap="closeOnboarding">✕</text>
        </view>
        <view class="onboarding-section">
          <text class="onboarding-section-title">我可以帮你：</text>
          <view
            v-for="(feature, idx) in onboardingConfig?.features || []"
            :key="`f-${idx}`"
            class="onboarding-feature"
          >
            <text class="onboarding-dot">•</text>
            <text class="onboarding-feature-text">{{ feature }}</text>
          </view>
        </view>
        <view class="onboarding-section">
          <text class="onboarding-section-title">试试这样问：</text>
          <view class="onboarding-examples">
            <view
              v-for="(example, idx) in onboardingConfig?.examples || []"
              :key="`e-${idx}`"
              class="onboarding-example"
              @tap="sendExampleQuestion(example)"
            >
              <text class="onboarding-example-text">{{ example }}</text>
            </view>
          </view>
        </view>
        <button class="onboarding-confirm" @tap="closeOnboarding">
          我知道了
        </button>
      </view>
    </view>

    <!-- 底部输入框（阶段四拆分为独立组件） -->
    <view class="ai-chat-input-bar">
      <view class="flex items-center rounded-full bg-gray-100 px-4 py-2">
        <input
          id="ai-input"
          v-model="inputValue"
          class="min-w-0 flex-1 text-14px"
          placeholder="输入你的需求，如：附近便宜的火锅"
          placeholder-class="text-gray-400"
          confirm-type="send"
          :disabled="isLoading"
          @confirm="handleSend"
        >
        <view
          id="ai-send-btn"
          class="ml-3 h-32px w-32px flex flex-shrink-0 items-center justify-center rounded-full from-blue-500 to-purple-500 bg-gradient-to-r"
          :style="{ opacity: isLoading ? 0.5 : 1 }"
          @tap="handleSend"
        >
          <text class="text-14px text-white" style="transform: rotate(-45deg); display: inline-block">➤</text>
        </view>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.ai-chat-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  min-height: 100vh;
  overflow: hidden;
  background: #f9fafb;
}

.ai-chat-scroll {
  flex: 1;
  min-height: 0;
}

.ai-chat-input-bar {
  flex-shrink: 0;
  padding: 24rpx;
  padding-bottom: calc(24rpx + constant(safe-area-inset-bottom));
  padding-bottom: calc(24rpx + env(safe-area-inset-bottom));
  border-top: 1rpx solid #e5e7eb;
  background: #fff;
}

.ai-card-btn {
  height: 52rpx;
  margin: 0;
  padding: 0 20rpx;
  border-radius: 26rpx;
  background: #fff;
  color: #374151;
  font-size: 22rpx;
  line-height: 52rpx;
}

.ai-card-btn::after {
  border: 0;
}

.ai-card-btn--primary {
  background: #018d71;
  color: #fff;
}

.ai-feedback-btn {
  height: 44rpx;
  margin: 0;
  padding: 0 18rpx;
  border-radius: 22rpx;
  background: #f3f4f6;
  color: #4b5563;
  font-size: 20rpx;
  line-height: 44rpx;
}

.ai-feedback-btn::after {
  border: 0;
}

.ai-feedback-btn--active {
  background: #e6f7f2;
  color: #018d71;
}

.onboarding-overlay {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
}

.onboarding-mask {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: rgba(0, 0, 0, 0.5);
}

.onboarding-content {
  position: relative;
  width: 78vw;
  max-width: 560rpx;
  padding: 40rpx;
  border-radius: 24rpx;
  background: #fff;
  box-shadow: 0 20rpx 60rpx rgba(0, 0, 0, 0.15);
}

.onboarding-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28rpx;
}

.onboarding-title {
  flex: 1;
  padding-right: 20rpx;
  font-size: 32rpx;
  font-weight: 600;
  color: #111827;
  line-height: 1.4;
}

.onboarding-close {
  font-size: 32rpx;
  color: #9ca3af;
  padding: 8rpx;
}

.onboarding-section {
  margin-bottom: 28rpx;
}

.onboarding-section-title {
  display: block;
  margin-bottom: 16rpx;
  font-size: 26rpx;
  font-weight: 500;
  color: #374151;
}

.onboarding-feature {
  display: flex;
  align-items: flex-start;
  margin-bottom: 10rpx;
}

.onboarding-dot {
  margin-right: 12rpx;
  color: #018d71;
  font-size: 24rpx;
  line-height: 1.5;
}

.onboarding-feature-text {
  flex: 1;
  font-size: 26rpx;
  color: #4b5563;
  line-height: 1.5;
}

.onboarding-examples {
  display: flex;
  flex-wrap: wrap;
  gap: 14rpx;
}

.onboarding-example {
  padding: 14rpx 22rpx;
  border-radius: 999rpx;
  background: #f3f4f6;
}

.onboarding-example-text {
  font-size: 24rpx;
  color: #111827;
}

.onboarding-confirm {
  width: 100%;
  height: 80rpx;
  margin: 0;
  padding: 0;
  border-radius: 40rpx;
  background: #018d71;
  color: #fff;
  font-size: 28rpx;
  line-height: 80rpx;
  text-align: center;
}

.onboarding-confirm::after {
  border: 0;
}
</style>

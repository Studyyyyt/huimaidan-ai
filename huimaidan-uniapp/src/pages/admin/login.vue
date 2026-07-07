<script lang="ts" setup>
import { useMerTokenStore } from '@/store/mer-token'

definePage({
  style: {
     navigationStyle: 'custom',
    navigationBarTitleText: '商户管理登录',
  },
})

const merTokenStore = useMerTokenStore()
const loading = ref(false)

const loginForm = reactive({
  account: '',
  password: '',
  captchaCode: '',
})

// 图形验证码
const captchaKey = ref('')
const captchaImage = ref('')

// 获取图形验证码
async function loadCaptcha() {
  try {
    const res = await merTokenStore.fetchCaptcha()
    captchaKey.value = res.key
    captchaImage.value = res.captcha
  }
  catch (error) {
    console.error('获取验证码失败:', error)
  }
}

// 刷新验证码
function refreshCaptcha() {
  loadCaptcha()
}

// 页面加载时获取验证码
onMounted(() => {
  loadCaptcha()
})

function handleBack() {
  uni.navigateBack()
}

async function handleLogin() {
  if (!loginForm.account || !loginForm.password) {
    uni.showToast({ title: '请输入账号和密码', icon: 'none' })
    return
  }

  if (!loginForm.captchaCode) {
    uni.showToast({ title: '请输入验证码', icon: 'none' })
    return
  }

  loading.value = true
  try {
    await merTokenStore.login({
      account: loginForm.account,
      password: loginForm.password,
      code: captchaKey.value,
      key: loginForm.captchaCode,
    })
    uni.showToast({ title: '登录成功', icon: 'success' })
    setTimeout(() => {
      // 使用 reLaunch 确保 tabbar 页面正确显示
      uni.reLaunch({
        url: '/pages/admin/finance',
      })
    }, 1500)
  }
  catch (error: any) {
    console.error('商户管理员登录失败', error)
    // 登录失败刷新验证码
    refreshCaptcha()
    loginForm.captchaCode = ''
  }
  finally {
    loading.value = false
  }
}
</script>

<template>
  <view class="login-page">
    <!-- 顶部导航栏 -->
    <view class="nav-bar">
      <view class="back-btn" @tap="handleBack">
        <text class="back-icon">‹</text>
      </view>
    </view>

    <!-- 标题区域 -->
    <view class="title-area">
      <text class="title-text">管理员登录</text>
    </view>

    <!-- 登录表单卡片 -->
    <view class="form-container">
      <view class="form-card">
        <!-- 手机号输入 -->
        <view class="form-item">
          <text class="form-label">账号：</text>
          <input
            v-model="loginForm.account"
            class="form-input"
            placeholder="请输入账号"
            placeholder-class="placeholder-text"
          >
        </view>

        <!-- 密码输入 -->
        <view class="form-item">
          <text class="form-label">密码：</text>
          <input
            v-model="loginForm.password"
            password
            class="form-input"
            placeholder="请输入密码"
            placeholder-class="placeholder-text"
          >
        </view>

        <!-- 验证码 -->
        <view class="form-item captcha-item">
          <text class="form-label">验证码：</text>
          <input
            v-model="loginForm.captchaCode"
            class="form-input captcha-input"
            placeholder="请输入验证码"
            placeholder-class="placeholder-text"
          >
          <view
            class="captcha-box"
            @tap="refreshCaptcha"
          >
            <image
              v-if="captchaImage"
              :src="captchaImage"
              class="captcha-image"
              mode="aspectFill"
            />
            <view v-else class="captcha-placeholder">
              <text class="captcha-text">点击获取</text>
            </view>
          </view>
        </view>

        <!-- 登录按钮 -->
        <button
          class="login-btn"
          :loading="loading"
          :disabled="loading"
          @tap="handleLogin"
        >
          登录
        </button>
      </view>
    </view>
  </view>
</template>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #e8eaf6 0%, #f3e5f5 50%, #ede7f6 100%);
  position: relative;
}

.nav-bar {
  position: fixed;
  left: 0;
  right: 0;
  top: 0;
  z-index: 10;
  display: flex;
  align-items: center;
  padding-top: 80rpx;
  padding-left: 24rpx;
}

.back-btn {
  width: 64rpx;
  height: 64rpx;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-icon {
  font-size: 48rpx;
  color: #37474f;
  font-weight: 300;
}

.title-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 180rpx;
}

.title-text {
  font-size: 52rpx;
  color: #1a237e;
  font-weight: 600;
  letter-spacing: 4rpx;
}

.form-container {
  padding: 60rpx 48rpx;
}

.form-card {
  background: #ffffff;
  border-radius: 24rpx;
  padding: 48rpx 40rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.08);
}

.form-item {
  display: flex;
  align-items: center;
  padding-bottom: 28rpx;
  margin-bottom: 28rpx;
  border-bottom: 1rpx solid #f0f0f0;
}

.form-item:last-of-type {
  margin-bottom: 0;
  border-bottom: none;
}

.form-label {
  width: 150rpx;
  font-size: 30rpx;
  color: #37474f;
  font-weight: 500;
}

.form-input {
  flex: 1;
  height: 80rpx;
  font-size: 28rpx;
  color: #37474f;
}

.placeholder-text {
  color: #bdbdbd;
}

.captcha-item {
  margin-bottom: 48rpx;
}

.captcha-input {
  flex: 1;
}

.captcha-box {
  margin-left: 16rpx;
  width: 200rpx;
  height: 80rpx;
  border-radius: 8rpx;
  overflow: hidden;
  background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
}

.captcha-image {
  width: 100%;
  height: 100%;
}

.captcha-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.captcha-text {
  color: #ffffff;
  font-size: 24rpx;
}

.login-btn {
  margin-top: 16rpx;
  height: 96rpx;
  width: 100%;
  border-radius: 48rpx;
  background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
  color: #ffffff;
  font-size: 32rpx;
  font-weight: 600;
  box-shadow: 0 8rpx 24rpx rgba(255, 152, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>

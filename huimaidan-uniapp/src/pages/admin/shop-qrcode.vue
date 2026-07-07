<script lang="ts" setup>
import { onShow } from '@dcloudio/uni-app'
import { getStoreQrcodeDetail } from '@/api/mer'
import type { IStoreQrcodeDetail } from '@/api/mer'
import { useMerTokenStore } from '@/store/mer-token'

definePage({
  style: {
    navigationBarTitleText: '店铺二维码',
  },
})

const merTokenStore = useMerTokenStore()

// 加载状态
const loading = ref(true)

// 店铺二维码数据
const qrcodeData = ref<IStoreQrcodeDetail | null>(null)

// 错误信息
const errorMsg = ref('')

// 复制scene值
function handleCopyScene() {
  if (!qrcodeData.value?.scene_value) return
  uni.setClipboardData({
    data: qrcodeData.value.scene_value,
    success: () => {
      uni.showToast({ title: '已复制', icon: 'success' })
    },
  })
}

// 下载二维码（保存到相册）
function handleDownloadQrcode() {
  if (!qrcodeData.value?.qr_image_url) {
    uni.showToast({ title: '暂无二维码图片', icon: 'none' })
    return
  }
  downloadImage(qrcodeData.value.qr_image_url, '店铺二维码')
}

// 下载图片通用方法
function downloadImage(url: string, filename: string) {
  uni.showLoading({ title: '保存中...' })

  // #ifdef H5
  // H5 环境下使用 a 标签下载
  const link = document.createElement('a')
  link.href = url
  link.download = `${filename}.png`
  link.click()
  uni.hideLoading()
  uni.showToast({ title: '下载成功', icon: 'success' })
  // #endif

  // #ifdef MP-WEIXIN || MP-ALIPAY || MP-BAIDU || MP-TOUTIAO
  // 小程序环境下保存到相册
  uni.downloadFile({
    url,
    success: (res) => {
      if (res.statusCode === 200) {
        uni.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: () => {
            uni.hideLoading()
            uni.showToast({ title: '保存成功', icon: 'success' })
          },
          fail: (err) => {
            uni.hideLoading()
            if (err.errMsg.includes('auth deny') || err.errMsg.includes('authorize')) {
              uni.showModal({
                title: '提示',
                content: '需要您授权保存图片到相册',
                confirmText: '去设置',
                success: (modalRes) => {
                  if (modalRes.confirm) {
                    uni.openSetting()
                  }
                },
              })
            } else {
              uni.showToast({ title: '保存失败', icon: 'none' })
            }
          },
        })
      } else {
        uni.hideLoading()
        uni.showToast({ title: '下载失败', icon: 'none' })
      }
    },
    fail: () => {
      uni.hideLoading()
      uni.showToast({ title: '下载失败', icon: 'none' })
    },
  })
  // #endif

  // #ifdef APP-PLUS
  // APP 环境下使用 plus API
  uni.downloadFile({
    url,
    success: (res) => {
      if (res.statusCode === 200) {
        uni.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: () => {
            uni.hideLoading()
            uni.showToast({ title: '保存成功', icon: 'success' })
          },
          fail: (err) => {
            uni.hideLoading()
            if (err.errMsg.includes('auth deny') || err.errMsg.includes('authorize')) {
              uni.showModal({
                title: '提示',
                content: '需要您授权保存图片到相册',
                confirmText: '去设置',
                success: (modalRes) => {
                  if (modalRes.confirm) {
                    uni.openSetting()
                  }
                },
              })
            } else {
              uni.showToast({ title: '保存失败', icon: 'none' })
            }
          },
        })
      } else {
        uni.hideLoading()
        uni.showToast({ title: '下载失败', icon: 'none' })
      }
    },
    fail: () => {
      uni.hideLoading()
      uni.showToast({ title: '下载失败', icon: 'none' })
    },
  })
  // #endif
}

// 加载店铺二维码数据
async function loadQrcodeData() {
  loading.value = true
  errorMsg.value = ''

  try {
    console.log('=== 店铺二维码原始数据 ===')
    console.log('接口: /mer/huimaidan/store_qrcode/detail')
    console.log('请求方式: GET')
    const res = await getStoreQrcodeDetail()
    console.log('响应数据:', res)
    console.log('==========================')
    qrcodeData.value = res
  }
  catch (error: any) {
    console.error('获取店铺二维码失败:', error)
    errorMsg.value = error?.message || '获取店铺二维码失败'
    qrcodeData.value = null
  }
  finally {
    loading.value = false
  }
}

onShow(() => {
  if (!merTokenStore.hasLogin) {
    uni.reLaunch({ url: '/pages/admin/login' })
    return
  }
  loadQrcodeData()
})
</script>

<template>
  <view class="min-h-screen bg-gray-50 pb-40rpx">
    <!-- 加载状态 -->
    <view v-if="loading" class="flex flex-col items-center justify-center py-120rpx">
      <text class="text-28rpx text-gray-400">加载中...</text>
    </view>

    <!-- 错误状态 -->
    <view v-else-if="errorMsg" class="mx-24rpx mt-24rpx">
      <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
        <text class="mb-16rpx block text-center text-30rpx text-red-500 font-bold">加载失败</text>
        <text class="block text-center text-26rpx text-gray-600">{{ errorMsg }}</text>
        <button
          class="mt-24rpx h-80rpx w-full rounded-full from-blue-400 via-blue-500 to-purple-500 bg-gradient-to-r text-28rpx text-white font-bold shadow-md"
          @tap="loadQrcodeData"
        >
          重新加载
        </button>
      </view>
    </view>

    <!-- 正常内容 -->
    <template v-else-if="qrcodeData">
      <!-- 商户信息 -->
      <view class="mx-24rpx mt-24rpx">
        <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
          <text class="mb-8rpx block text-30rpx text-gray-800 font-bold">{{ qrcodeData.mer_name }}</text>
          <text v-if="qrcodeData.branch_name_snapshot" class="block text-26rpx text-gray-500">{{ qrcodeData.branch_name_snapshot }}</text>
        </view>
      </view>

      <!-- 二维码卡片 -->
      <view class="mx-24rpx mt-24rpx">
        <view class="rounded-24rpx bg-white p-32rpx shadow-sm">
          <text class="mb-24rpx block text-center text-30rpx text-gray-800 font-bold">店铺二维码</text>

          <!-- 二维码图片 -->
          <view class="flex justify-center">
            <image
              v-if="qrcodeData.qr_image_url"
              class="h-400rpx w-400rpx rounded-16rpx"
              :src="qrcodeData.qr_image_url"
              mode="aspectFit"
              show-menu-by-longpress
            />
            <view v-else class="h-400rpx w-400rpx flex items-center justify-center rounded-16rpx bg-gray-100">
              <text class="text-26rpx text-gray-400">暂无二维码</text>
            </view>
          </view>

          <!-- 状态信息 -->
          <view class="mt-24rpx rounded-16rpx bg-gray-50 p-24rpx">
            <!-- 生成状态 -->
            <view class="mb-16rpx flex items-center justify-between">
              <text class="text-24rpx text-gray-500">生成状态</text>
              <text :class="qrcodeData.last_generate_status === 1 ? 'text-green-500' : 'text-red-500'" class="text-24rpx font-bold">
                {{ qrcodeData.last_generate_status_text }}
              </text>
            </view>

            <!-- 生成失败原因 -->
            <view v-if="qrcodeData.last_generate_status === 0 && qrcodeData.last_generate_error" class="mb-16rpx rounded-12rpx bg-red-50 p-16rpx">
              <text class="text-22rpx text-red-500">{{ qrcodeData.last_generate_error }}</text>
            </view>

            <!-- 使用上一版提示 -->
            <view v-if="qrcodeData.is_using_last_success === 1" class="mb-16rpx rounded-12rpx bg-orange-50 p-16rpx">
              <text class="text-22rpx text-orange-500">当前展示的是上一版本的二维码</text>
            </view>

            <!-- 生成时间 -->
            <view class="mb-16rpx flex items-center justify-between">
              <text class="text-24rpx text-gray-500">生成时间</text>
              <text class="text-24rpx text-gray-700">{{ qrcodeData.last_generated_at || '-' }}</text>
            </view>

            <!-- scene值（可复制） -->
            <view class="flex items-center justify-between">
              <text class="text-24rpx text-gray-500">Scene值</text>
              <view class="flex items-center" @tap="handleCopyScene">
                <text class="mr-8rpx text-24rpx text-gray-700">{{ qrcodeData.scene_value }}</text>
                <text class="text-22rpx text-blue-500">复制</text>
              </view>
            </view>
          </view>

          <!-- 下载按钮 -->
          <button
            class="mt-24rpx h-80rpx w-full rounded-full from-blue-400 via-blue-500 to-purple-500 bg-gradient-to-r text-28rpx text-white font-bold shadow-md"
            :disabled="!qrcodeData.qr_image_url"
            @tap="handleDownloadQrcode"
          >
            保存到相册
          </button>
        </view>
      </view>
    </template>
  </view>
</template>

<style lang="scss" scoped>
// 底部安全区域
.safe-area-inset-bottom {
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}
</style>

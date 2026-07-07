<script lang="ts" setup>
definePage({
  style: {
    navigationBarTitleText: '修改密码',
  },
})

const formData = reactive({
  newPassword: '',
  confirmPassword: '',
})

const showPassword = ref(false)

function handleSave() {
  if (!formData.newPassword || !formData.confirmPassword) {
    uni.showToast({ title: '请填写完整信息', icon: 'none' })
    return
  }
  if (formData.newPassword !== formData.confirmPassword) {
    uni.showToast({ title: '两次密码不一致', icon: 'none' })
    return
  }
  uni.showToast({ title: '密码修改成功', icon: 'success' })
  setTimeout(() => {
    uni.navigateBack()
  }, 1500)
}
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 表单区域 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="overflow-hidden rounded-24rpx bg-white shadow-sm">
        <!-- 新密码 -->
        <view class="flex items-center border-b border-gray-100 p-32rpx">
          <text class="w-160rpx text-30rpx text-gray-800">新密码：</text>
          <input
            v-model="formData.newPassword"
            type="text"
            :password="!showPassword"
            class="flex-1 text-30rpx text-gray-800"
            placeholder="请输入新密码"
            placeholder-class="text-gray-400"
          >
        </view>

        <!-- 确认密码 -->
        <view class="flex items-center p-32rpx">
          <text class="w-160rpx text-30rpx text-gray-800">确认密码：</text>
          <input
            v-model="formData.confirmPassword"
            type="text"
            :password="!showPassword"
            class="flex-1 text-30rpx text-gray-800"
            placeholder="请再次输入您的新密码"
            placeholder-class="text-gray-400"
          >
        </view>
      </view>
    </view>

    <!-- 底部区域 -->
    <view class="fixed bottom-0 left-0 right-0 px-24rpx pb-safe">
      <view class="flex items-center justify-between py-24rpx">
        <!-- 显示密码复选框 -->
        <view
          class="flex items-center"
          @tap="showPassword = !showPassword"
        >
          <view
            class="mr-12rpx h-36rpx w-36rpx flex items-center justify-center rounded-full"
            :class="showPassword ? 'bg-purple-500' : 'bg-gray-200'"
          >
            <text
              v-if="showPassword"
              class="i-carbon-checkmark text-20rpx text-white"
            />
          </view>
          <text class="text-28rpx text-gray-600">显示密码</text>
        </view>

        <!-- 保存按钮 -->
        <button
          class="ml-24rpx h-80rpx flex-1 rounded-full from-purple-400 via-purple-300 to-indigo-400 bg-gradient-to-r text-30rpx text-white font-bold shadow-lg"
          @tap="handleSave"
        >
          保存
        </button>
      </view>
    </view>
  </view>
</template>

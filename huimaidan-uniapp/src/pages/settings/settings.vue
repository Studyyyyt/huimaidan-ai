<script lang="ts" setup>
import { storeToRefs } from 'pinia'
import { useUserStore } from '@/store'
import { uploadFileUrl, useUpload } from '@/utils/uploadFile'

definePage({
  style: {
    navigationBarTitleText: '设置',
  },
})

const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

// 表单数据
const formData = reactive({
  nickname: '',
})

// 初始化表单数据
onMounted(() => {
  formData.nickname = userInfo.value.nickname || ''
})

// 上传头像
const { loading: uploadLoading, run: uploadRun } = useUpload<string>(uploadFileUrl.USER_AVATAR, {}, {
  onSuccess: (data) => {
    // 上传成功后更新用户头像
    if (data) {
      userStore.setUserAvatar(data as unknown as string)
      uni.showToast({
        title: '头像更新成功',
        icon: 'success',
      })
    }
  },
  onError: (err) => {
    console.error('头像上传失败:', err)
    uni.showToast({
      title: '头像上传失败，请检查网络或联系管理员',
      icon: 'none',
    })
  },
})

// 选择头像
function handleChooseAvatar() {
  uni.chooseImage({
    count: 1,
    sizeType: ['compressed'],
    sourceType: ['album', 'camera'],
    success: (res) => {
      const tempFilePath = res.tempFilePaths[0]
      // 先更新本地显示
      userStore.setUserAvatar(tempFilePath)
      uni.showToast({
        title: '头像已更新',
        icon: 'success',
      })
      // TODO: 后端接口修复后，取消注释以下代码进行服务器上传
      // uploadRun()
    },
  })
}

// 保存设置
function handleSave() {
  if (!formData.nickname.trim()) {
    uni.showToast({
      title: '请输入昵称',
      icon: 'none',
    })
    return
  }

  // 更新用户昵称
  userStore.setUserInfo({
    ...userInfo.value,
    nickname: formData.nickname.trim(),
  })

  uni.showToast({
    title: '保存成功',
    icon: 'success',
  })
}
</script>

<template>
  <view class="min-h-screen bg-gray-50">
    <!-- 用户信息卡片 -->
    <view class="mx-24rpx mt-24rpx">
      <view class="overflow-hidden rounded-24rpx bg-white shadow-sm">
        <!-- 头像 -->
        <view
          class="flex items-center justify-between border-b border-gray-100 p-32rpx"
          @tap="handleChooseAvatar"
        >
          <text class="text-30rpx text-gray-800">头像：</text>
          <view class="flex items-center">
            <view class="h-80rpx w-80rpx flex items-center justify-center rounded-full bg-gray-100">
              <image
                v-if="userInfo.avatar"
                class="h-80rpx w-80rpx rounded-full"
                :src="userInfo.avatar"
                mode="aspectFill"
              />
              <text v-else class="i-carbon-user text-40rpx text-gray-400" />
            </view>
            <text class="i-carbon-chevron-right ml-16rpx text-24rpx text-gray-400" />
          </view>
        </view>

        <!-- 昵称 -->
        <view class="flex items-center justify-between p-32rpx">
          <text class="text-30rpx text-gray-800">昵称：</text>
          <input
            v-model="formData.nickname"
            class="flex-1 text-right text-30rpx text-gray-600"
            placeholder="请输入"
            placeholder-class="text-gray-400"
          >
        </view>
      </view>
    </view>

    <!-- 保存按钮 -->
    <view class="mx-24rpx mt-48rpx">
      <button
        class="h-88rpx w-full rounded-full from-purple-400 via-purple-300 to-indigo-400 bg-gradient-to-r text-32rpx text-white font-bold shadow-lg"
        @tap="handleSave"
      >
        保存
      </button>
    </view>
  </view>
</template>

<template>
  <picker
    v-if="!isScrolled"
    mode="region"
    :value="region"
    @change="handleRegionChange"
  >
    <view class="min-w-0 flex items-center">
      <text class="i-carbon-location flex-shrink-0 text-12px text-gray-700" />
      <text class="max-w-72px truncate text-14px text-gray-700">
        {{ text }}
      </text>
      <text class="i-carbon-chevron-right flex-shrink-0 text-12px text-gray-700" />
    </view>
  </picker>

  <view v-else-if="locatable" class="flex flex-shrink-0 items-center">
    <view
      class="h-28px w-24px flex items-center justify-center"
      @tap="emit('locate')"
    >
      <text class="i-carbon-location text-16px text-gray-700" />
    </view>
    <picker
      mode="region"
      :value="region"
      @change="handleRegionChange"
    >
      <view class="h-24px w-18px flex items-center justify-center">
        <text class="i-carbon-chevron-right text-12px text-gray-700" />
      </view>
    </picker>
  </view>

  <picker
    v-else
    mode="region"
    :value="region"
    @change="handleRegionChange"
  >
    <view class="flex flex-shrink-0 items-center">
      <view class="h-28px w-24px flex items-center justify-center">
        <text class="i-carbon-location text-16px text-gray-700" />
      </view>
      <view class="h-24px w-18px flex items-center justify-center">
        <text class="i-carbon-chevron-right text-12px text-gray-700" />
      </view>
    </view>
  </picker>
</template>

<script lang="ts" setup>
defineOptions({
  name: 'TopLocationPicker',
})

withDefaults(defineProps<{
  isScrolled?: boolean
  locatable?: boolean
  region: [string, string, string]
  text: string
}>(), {
  isScrolled: false,
  locatable: false,
})

const emit = defineEmits<{
  change: [event: any]
  locate: []
}>()

function handleRegionChange(event: any) {
  emit('change', event)
}
</script>

<template>
  <view class="ai-robot-card" @tap="handleTap">
    <image class="ai-orbit" :src="asset('ai_robot_orbit.png')" mode="widthFix" />
    <image class="ai-sparkle sparkle-a" :src="asset('sparkle_large.png')" mode="widthFix" />
    <image class="ai-sparkle sparkle-b" :src="asset('sparkle_small.png')" mode="widthFix" />
    <image class="ai-sparkle sparkle-c" :src="asset('sparkle_small.png')" mode="widthFix" />

    <view class="ai-body-wrap">
      <image class="ai-shadow" :src="asset('ai_robot_shadow.png')" mode="widthFix" />
      <image class="ai-body" :src="asset('ai_robot_body.png')" mode="widthFix" />
      <image class="ai-face-blink" :src="asset('ai_robot_face_blink.png')" mode="widthFix" />
    </view>
  </view>
</template>

<script lang="ts" setup>
defineOptions({
  name: 'AiRobotBanner',
})

const props = withDefaults(defineProps<{
  assetBase?: string
}>(), {
  assetBase: '/static/ai-robot/',
})

const emit = defineEmits<{
  tap: []
}>()

function asset(name: string) {
  return `${props.assetBase}${name}`
}

function handleTap() {
  emit('tap')
}
</script>

<style scoped>
.ai-robot-card {
  position: relative;
  width: 100%;
  height: 0;
  padding-top: 82.23%;
  overflow: visible;
  transform: translateZ(0);
}

.ai-orbit,
.ai-sparkle,
.ai-shadow,
.ai-body,
.ai-face-blink {
  position: absolute;
  display: block;
  pointer-events: none;
  will-change: transform, opacity;
}

.ai-orbit {
  left: -3%;
  bottom: 4%;
  width: 106%;
  opacity: 0.62;
  animation: aiOrbitDrift 5.6s ease-in-out infinite;
}

.ai-body-wrap {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 0;
  padding-top: 82.23%;
  animation: aiFloat 3.2s ease-in-out infinite;
  will-change: transform;
}

.ai-shadow {
  left: 0;
  bottom: -5%;
  width: 100%;
  opacity: 0.46;
  animation: aiShadow 3.2s ease-in-out infinite;
}

.ai-body,
.ai-face-blink {
  left: 0;
  top: 0;
  width: 100%;
}

.ai-face-blink {
  opacity: 0;
  animation: aiBlink 4.8s linear infinite;
}

.ai-sparkle {
  opacity: 0.92;
}

.sparkle-a {
  right: 14%;
  top: 40%;
  width: 8.8%;
  animation: aiSparkleA 2.8s ease-in-out infinite;
}

.sparkle-b {
  left: 13%;
  top: 46%;
  width: 5%;
  animation: aiSparkleB 3.4s ease-in-out infinite;
}

.sparkle-c {
  left: 15%;
  bottom: 19%;
  width: 5.2%;
  animation: aiSparkleC 3.8s ease-in-out infinite;
}

.ai-robot-card:active .ai-body-wrap {
  animation: aiTapBounce 0.42s cubic-bezier(0.2, 0.8, 0.2, 1);
}

@keyframes aiFloat {
  0%,
  100% {
    transform: translate3d(0, 0, 0) rotate(-0.2deg);
  }
  50% {
    transform: translate3d(0, -2.8%, 0) rotate(0.3deg);
  }
}

@keyframes aiShadow {
  0%,
  100% {
    transform: scaleX(1);
    opacity: 0.46;
  }
  50% {
    transform: scaleX(0.88);
    opacity: 0.28;
  }
}

@keyframes aiOrbitDrift {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(1);
    opacity: 0.72;
  }
  50% {
    transform: translate3d(2%, -4%, 0) scale(1.015);
    opacity: 0.92;
  }
}

@keyframes aiBlink {
  0%,
  88%,
  100% {
    opacity: 0;
  }
  90%,
  93% {
    opacity: 1;
  }
  95% {
    opacity: 0;
  }
}

@keyframes aiSparkleA {
  0%,
  100% {
    transform: scale(0.86) rotate(0);
    opacity: 0.55;
  }
  45% {
    transform: scale(1.1) rotate(8deg);
    opacity: 1;
  }
}

@keyframes aiSparkleB {
  0%,
  100% {
    transform: scale(0.72);
    opacity: 0.38;
  }
  58% {
    transform: scale(1);
    opacity: 0.9;
  }
}

@keyframes aiSparkleC {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(0.8);
    opacity: 0.35;
  }
  50% {
    transform: translate3d(12%, -12%, 0) scale(1.05);
    opacity: 0.88;
  }
}

@keyframes aiTapBounce {
  0% {
    transform: translate3d(0, 0, 0) scale(1);
  }
  38% {
    transform: translate3d(0, -5%, 0) scale(1.035);
  }
  68% {
    transform: translate3d(0, 1%, 0) scale(0.99);
  }
  100% {
    transform: translate3d(0, 0, 0) scale(1);
  }
}
</style>

# AI robot animation layers

Source image: C:\Users\venuz\Documents\xwechat_files\wxid_nj1x81xschxa1_a53d\temp\RWTemp\2026-06\950fa3d1eb4c3620ac2b265f9206004e.png
Canvas: 1449x1086

Generated layers:
- ai_robot_orbit.png: independent light orbit layer
- ai_robot_shadow.png: soft shadow under the floating robot
- ai_robot_body.png: transparent robot body cutout, PNG only for real-device compatibility
- ai_robot_face_blink.png: face overlay for blink/thinking state
- sparkle_large.png / sparkle_small.png: reusable sparkle decorations
- AiRobotBanner.vue: uni-app component usage sample
- preview_transparent_robot.png: transparent preview without a background color block
- preview_on_white_no_bg_block.png: white-page preview without a background color block

Notes:
- The original was a flattened PNG, so this uses a hand-built alpha mask for the robot cutout.
- The component does not render a background image; the page background shows through.
- For production, place the PNG assets under `/static/ai-robot/`.
- Import `AiRobotBanner.vue` in the uni-app page and handle its `tap` event for business actions.

Example:

```vue
<template>
  <ai-robot-banner @tap="openAiAssistant" />
</template>

<script>
import AiRobotBanner from '@/components/AiRobotBanner.vue'

export default {
  components: { AiRobotBanner },
  methods: {
    openAiAssistant() {
      // Open search, recommendation, or assistant panel here.
    }
  }
}
</script>
```

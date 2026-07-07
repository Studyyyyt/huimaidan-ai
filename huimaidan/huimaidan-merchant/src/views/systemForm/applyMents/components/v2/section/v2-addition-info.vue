<template>
  <el-row>
    <el-col>
      <div class="title">补充材料</div>
      <v2SchemaForm :form="form.addition_info" path="addition_info" :config="ADDITION_FORM_INFO" />
    </el-col>
  </el-row>
</template>

<script>
import v2SchemaForm from '../shared/v2-schema-form.vue';

const ADDITION_FORM_INFO = [
  {
    type: "upload",
    accept: "image",
    field: "legal_person_commitment",
    label: "法定代表人开户承诺函",
    tips: [
      `请上传法定代表人或负责人亲笔签署的开户承诺函扫描件（<a href="https://kf.qq.com/faq/191018yUFjEj191018Vfmaei.html" target="_blank">下载模板</a>）。亲笔签名承诺函内容清晰可见，不得有涂污，破损，字迹不清晰现象。`
    ]
  },
  {
    type: "upload",
    accept: "video",
    field: "legal_person_video",
    label: "法定代表人开户意愿视频",
    tips: [
      `建议法定代表人按如下话术录制“法定代表人开户意愿视频”：<br />我是#公司全称#的法定代表人（或负责人），特此证明本公司申请的商户号为我司真实意愿开立且用于XX业务（或XX服务）。我司现有业务符合法律法规及腾讯的相关规定。`,
      `录制要求：`,
      `1. 请用普通话录制以上视频`,
      `2. 本视频须采集到清晰正面人脸，请务必保持正脸朝向摄像头且不要遮挡面部`,
      `3. 请避免对录制原始图像和音频进行任何处理(如滤镜、美颜、变音及其他后期效果)`,
      `4. 支持上传20M内的视频，格式可为avi、wmv、mpeg、mp4、mov、mkv、flv、f4v、m4v、rmvb`,
    ]
  },
  {
    type: "upload",
    accept: "image",
    field: "business_addition_pics",
    label: "补充材料",
    max: 5,
    tips: [
      `1.请上传彩色照片 or 彩色扫描件 or 加盖公章鲜拿的复印件，要求正面拍摄，露出证件四角且清晰、完整，所有字符清晰可识别，不得反光或遮挡。不得翻拍、截图、镜像、PS。`,
      `2.图片只支持JPG、BMP、PNG格式，文件大小不能超过5M。`,
      `3.最多可上传5张照片。`
    ]
  },
  {
    type: "text",
    field: "business_addition_msg",
    label: "补充说明",
    max: 512,
    tips: [
      `请填写不超过512个字符的补充说明。`,
      `根据驳回要求提供额外信息，如:业务模式不清晰时，请详细描述支付场景。`
    ]
  }
];

export default {
  name: "V2AdditionInfo",
  props: {
    form: Object
  },
  components: {
    v2SchemaForm
  },
  data() {
    return {
      ADDITION_FORM_INFO
    };
  }
}
</script>

<style scoped lang="scss"></style>

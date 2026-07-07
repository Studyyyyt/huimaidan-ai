import SettingMer from "@/libs/settingMer";
import { getToken } from "@/utils/auth";

/**
 * 获取上传组件配置
 * @param {*} limit 
 * @param {*} uploadType 
 * @returns 
 */
export function getUploadProps(limit = 10, uploadType = "image", accept = "image/*") {
  return {
    limit,
    uploadType,
    accept,
    action: SettingMer.https + "/upload/image/0/file",
    headers: {
      "X-Token": getToken()
    },
    beforeUpload(file) {
      const isValidFile = new RegExp(accept).test(file.type);
      if (!isValidFile) {
        this.$message.error("文件后缀不合法!");
        return false;
      }
      return true;
    },
    onSuccess(res, file, fileList) {
      if (res.status === 200) {
        file.url = res.data.src;
      } else {
        fileList.pop();
        this.$message.error(res.message);
      }
    }
  };
}
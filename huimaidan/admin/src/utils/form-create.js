import SettingMer from "@/libs/settingMer";
import { getToken } from "@/utils/auth";
import { cityDataLst } from "@/api/system";

const cacheMap = new Map();

/**
 * 懒加载城市列表
 * @param {*} node 
 * @param {*} resolve 
 * @returns 
 */
export async function lazyLoadCity(node, resolve) {
  const cityId = node.level === 0 ? 0 : node.value;

  if (!cacheMap.has(cityId)) {
    const res = await cityDataLst(cityId);
    const cityList = res.data.map(item => {
      return {
        label: item.name,
        value: item.id,
        leaf: !item.hasChildren
      };
    });
    cacheMap.set(cityId, cityList);
  }
  const data = cacheMap.get(cityId);

  // 必须异步执行 resolve 回调函数，否则 elementui 级联选择器 UI 不会更新
  setTimeout(() => {
    resolve(JSON.parse(JSON.stringify(data)));
  });
}


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

// 系统表单类型映射到form-create类型
const SYSTEM_FORM_TYPE_MAP = {
  checkboxs: "checkbox",
  citys: "cascader",
  dates: "datePicker",
  dateranges: "datePicker",
  radios: "radio",
  selects: "select",
  texts: "input",
  times: "timePicker",
  timeranges: "timePicker",
  uploadPicture: "upload"
}

// 文本框类型映射
const INPUT_TYPE_MAP = {
  0: "text",
  1: "tel",
  2: "id_card",
  3: "email",
  4: "number"
};


/**
 * 将系统表单转换为form-create规则
 * @param {*} systemForm 
 */
export function systemFormToFormCreateRule(systemForm) {
  return Object.values(systemForm).map(value => {
    const type = SYSTEM_FORM_TYPE_MAP[value.name];
    if (!type) return null;
    const options = {
      type,
      rawType: value.name,
      title: value.titleConfig.value,
      field: value.titleConfig.value,
      $required: !!value.titleShow.val,
    };

    const systemFormItemType = value.name;

    switch (systemFormItemType) {
      case "checkboxs": // 多选框
      case "radios": // 单选框
      case "selects": // 下拉框
        options.options = value.wordsConfig.list.map(item => {
          return {
            label: item.val,
            value: item.val
          };
        });
        break;
      case "citys": // 城市选择器
        options.props = {
          props: {
            lazy: true,
            lazyLoad: lazyLoadCity
          }
        };
        break;
      case "dateranges": // 日期范围选择器
        options.props = {
          type: "daterange",
          rangeSeparator: "~"
        };
        break;
      case "texts": // 文本框
        options.props = {
          type: INPUT_TYPE_MAP[value.valConfig.tabVal],
          placeholder: value.tipConfig.value
        };
        break;
      case "times": // 时间选择器
        options.props = {
          format: "HH:mm",
          valueFormat: "HH:mm",
        };
        break;
      case "timeranges": // 时间范围选择器
        options.props = {
          format: "HH:mm",
          valueFormat: "HH:mm",
          isRange: true,
          rangeSeparator: "~"
        };
        break;
      case "uploadPicture": // 上传图片
        options.props = getUploadProps(value.numConfig.val)
        break;
      default:
        return null;
    }

    return options;
  }).filter(item => item !== null);
}
import { getMerCateApi } from "@/api/merchant";
import { getstoreTypeApi } from "@/api/merchant";
import { getBusinessZoneDetailApi } from "@/api/business-zone";
import store from "@/store";

// 店铺名称、负责人、联系电话、登陆账号、登陆密码；
export const getStoreCreateFormConfig = async () => {
  let categoryList = [];
  let typeList = [];
  let cateId = null;
  let typeId = null;

  const currentMerId = store.state.user.zoneId;
  console.log(currentMerId);

  try {
    const [categoryRes, typeRes, zoneDetailRes] = await Promise.all([
      getMerCateApi(),
      getstoreTypeApi(),
      getBusinessZoneDetailApi(currentMerId)
    ]);
    categoryList = categoryRes.data;
    typeList = typeRes.data;
    cateId = zoneDetailRes.data.business_store_category;
    typeId = zoneDetailRes.data.business_store_type;
  } catch (error) {
    this.$message.error(error.message);
  }
  return [
    {
      type: "input",
      field: "mer_name",
      title: "店铺名称：",
      props: {
        placeholder: "请输入店铺名称",
      },
      $required: true
    },
    {
      type: "input",
      field: "mer_real_name",
      title: "负责人：",
      props: {
        placeholder: "请输入负责人",
      },
      $required: true
    },
    {
      type: "input",
      field: "mer_phone",
      title: "联系电话：",
      props: {
        placeholder: "请输入联系电话",
      },
      $required: true
    },
    {
      type: "input",
      field: "mer_account",
      title: "登录账号：",
      props: {
        placeholder: "请输入登录账号",
      },
      $required: true
    },
    {
      type: "input",
      field: "mer_password",
      title: "登录密码：",
      props: {
        placeholder: "请输入登录密码",
      },
      $required: true
    },
    {
      type: "select",
      field: "category_id",
      title: "店铺分类：",
      options: categoryList,
      value: cateId,
      props: {
        disabled: true,
        class: "w-full",
        placeholder: "请选择店铺分类",
      },
      $required: true
    },
    {
      type: "select",
      field: "type_id",
      title: "店铺类型：",
      options: typeList,
      value: typeId,
      props: {
        disabled: true,
        class: "w-full",
        placeholder: "请选择店铺类型",
      },
      $required: true
    },
  ];
};
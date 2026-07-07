

export const getDefaultAdminEditForm = () => {
  return [
    {
      rawType: "input",
      type: "input",
      field: "name",
      title: "管理姓名：",
      props: {
        placeholder: "请输入管理员姓名"
      },
      $required: true
    },
    {
      rawType: "input",
      type: "input",
      field: "phone",
      title: "手机号码：",
      class: "has-tips",
      props: {
        placeholder: "请输入手机号码"
      },
      $required: true
    },
    {
      type: "FormDialogTip",
      name: "tip",
      props: {
        tip: "手机号码：为商户管理的登录账号，登录密码默认000000"
      }
    },
    {
      rawType: "input",
      type: "input",
      field: "account",
      title: "登录账号：",
      class: "has-tips",
      props: {
        placeholder: "请输入登录账号"
      },
      $required: true
    },
    {
      rawType: "input",
      type: "input",
      field: "password",
      title: "登录密码：",
      class: "has-tips",
      value: "000000",
      props: {
        type: "password",
        showPassword: true,
        placeholder: "请输入登录密码"
      },
      $required: true
    },
    {
      rawType: "user",
      type: "frame",
      field: "uid",
      title: "关联用户：",
      props: {
        type: "image",
        maxLength: 1,
        title: "请选择用户：",
        src: "/admin/setting/userList?field=uid&type=1",
        srcKey: "src",
        width: "800px",
        height: "600px",
        icon: "el-icon-camera",
        modal: {
          modal: false
        },
        onOk() {
          this.$message.warning(`请选择用户`);
          return false;
        }
      }
    },
    // {
    //   rawType: "switches",
    //   type: "switch",
    //   field: "status",
    //   title: "账号状态：",
    //   class: "switch-width-double",
    //   props: {
    //     activeValue: 1,
    //     inactiveValue: 0,
    //     activeText: "开启",
    //     inactiveText: "关闭"
    //   }
    // },
    // {
    //   type: "FormDialogTip",
    //   name: "tip",
    //   props: {
    //     tip: "关闭后，该账号禁止登录。"
    //   }
    // },
  ];
};

// 商户管理员编辑表单配置
export const ADMIN_EDIT_FORM = getDefaultAdminEditForm();

// 判断对象是否存在指定属性
export const hasOwn = (obj, key) => Object.prototype.hasOwnProperty.call(obj, key);

// 根据初始值生成表单规则
export const generateAdminEditForm = (initConfig = {}) => {
  return ADMIN_EDIT_FORM
    .filter(item => item.field !== "password")
    .map(item => {
      if (!item.field || !hasOwn(initConfig, item.field)) return item;
      const config = initConfig[item.field];

      const newConfig = {
        ...item
      };

      // 合并props
      if (hasOwn(config, 'props')) {
        newConfig.props = {
          ...item.props,
          ...config.props
        };
      }

      // 合并value
      if (hasOwn(config, 'value')) {
        newConfig.value = config.value;
      }

      return newConfig;
    });
};
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { login, logout, getInfo, getMenusApi } from "@/api/user";
import { getToken, setToken, removeToken } from "@/utils/auth";
import router, { resetRouter } from "@/router";
import { isLoginApi } from "@/api/sms";
import Cookies from "js-cookie";
import groupRouter from "@/router/modules/group";
import {
  formatFlatteningRoutes,
  findFirstNonNullChildren
} from "@/utils/system.js";

const safeParse = (value) => {
  try {
    return JSON.parse(value);
  } catch (e) {
    return;
  }
};

const ZONE_ID_KEY = "current_zone_id";

const state = () => {
  const adminInfo = safeParse(localStorage.getItem("AdminInfo"));
  const isAgent = adminInfo && adminInfo.is_agent > 0; // 是否为区域代理商 or 商户管理员

  // 获取之前暂存的区域 ID
  let zoneId = Number(localStorage.getItem(ZONE_ID_KEY)) || 0;

  // 如果当前用户是区域代理商且下辖若干区域
  if (adminInfo && adminInfo.region_name && adminInfo.region_name.length && isAgent) {

    // 如果之前暂存的区域 ID 不存在于当前用户下辖区域列表中
    const isZoneExist = adminInfo.region_name.some((item) => item.circle_id === zoneId);

    // 则将暂存的区域 ID 设置为当前用户下辖区域列表中的第一个区域 ID
    if (!isZoneExist) {
      zoneId = adminInfo.region_name[0].circle_id;
    }
  }
  return {
    token: getToken(),
    name: "",
    avatar: "",
    introduction: "",
    roles: [],
    zoneId,
    adminInfo,
    menuList: JSON.parse(localStorage.getItem("MenuList")),
    isLogin: Cookies.get("isLogin"),
    sidebarWidth: window.localStorage.getItem("sidebarWidth"),
    sidebarStyle: window.localStorage.getItem("sidebarStyle"),
    oneLvMenus: [],
    oneLvRoutes: JSON.parse(localStorage.getItem("oneLvRoutes")),
    childMenuList: []
  };
};
const mutations = {
  SET_ZONE_ID: (state, zoneId) => {
    state.zoneId = zoneId;
    localStorage.setItem(ZONE_ID_KEY, zoneId);
  },
  SET_MENU_LIST: (state, menuList) => {
    state.menuList = menuList;
  },
  SET_TOKEN: (state, token) => {
    state.token = token;
  },
  SET_ISLOGIN: (state, isLogin) => {
    state.isLogin = isLogin;
    Cookies.set(isLogin);
  },
  SET_INTRODUCTION: (state, introduction) => {
    state.introduction = introduction;
  },
  SET_NAME: (state, name) => {
    state.name = name;
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar;
  },
  SET_ROLES: (state, roles) => {
    state.roles = roles;
  },
  SET_SIDEBAR_WIDTH: (state, width) => {
    state.sidebarWidth = width;
  },
  SET_SIDEBAR_STYLE: (state, style) => {
    state.sidebarStyle = style;
    window.localStorage.setItem("sidebarStyle", style);
  },
  setOneLvMenus(state, oneLvMenus) {
    state.oneLvMenus = oneLvMenus;
  },
  setOneLvRoute(state, oneLvRoutes) {
    state.oneLvRoutes = oneLvRoutes;
  },
  childMenuList(state, list) {
    state.childMenuList = list;
  },
  SET_ADMIN_INFO(state, adminInfo) {
    state.adminInfo = adminInfo;
    localStorage.setItem("AdminInfo", JSON.stringify(adminInfo));
  }
};
const actions = {
  // user login
  login({ commit }, userInfo) {
    // const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login(userInfo)
        .then(response => {
          const { data } = response;
          if (data.admin.is_agent && !data.admin.region_name.length) {
            throw new Error("请先联系平台管理员绑定区域!");
          }

          commit("SET_TOKEN", data.token);
          Cookies.set("AdminName", data.admin.account);
          commit("SET_ADMIN_INFO", data.admin);
          if (data.admin.is_agent) {
            commit("SET_ZONE_ID", data.admin.region_name[0].circle_id)
          }
          setToken(data.token);
          resolve(data);
        })
        .catch(error => {
          reject(error);
        });
    });
  },
  // 短信是否登录
  isLogin({ commit }, userInfo) {
    // const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      isLoginApi()
        .then(async res => {
          commit("SET_ISLOGIN", res.data.status);
          resolve(res);
        })
        .catch(res => {
          commit("SET_ISLOGIN", false);
          reject(res);
        });
    });
  },
  getMenus({ commit, getters }) {
    return new Promise((resolve, reject) => {
      const params = {};
      if (getters.isAgent && getters.zoneId) {
        params.circle_id = getters.zoneId;
      }
      getMenusApi(params)
      .then(response => {
          commit("SET_MENU_LIST", response.data);
          localStorage.setItem("MenuList", JSON.stringify(response.data));
          let arr = formatFlatteningRoutes(router.options.routes);
          let routes = formatFlatteningRoutes(response.data);
          localStorage.setItem("oneLvRoutes", JSON.stringify(routes));
          commit("setOneLvMenus", arr);
          commit("setOneLvRoute", routes);
          resolve(response);
        })
        .catch(error => {
          reject(error);
        });
    });
  },
  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo(state.token)
        .then(response => {
          const { data } = response;

          if (!data) {
            reject("Verification failed, please Login again.");
          }

          const { roles, name, avatar, introduction } = data;

          // roles must be a non-empty array
          if (!roles || roles.length <= 0) {
            reject("getInfo: roles must be a non-null array!");
          }

          commit("SET_ROLES", roles);
          commit("SET_NAME", name);
          commit("SET_AVATAR", avatar);
          commit("SET_INTRODUCTION", introduction);
          resolve(data);
        })
        .catch(error => {
          reject(error);
        });
    });
  },
  // user logout
  logout({ commit, state, dispatch }) {
    return new Promise((resolve, reject) => {
      logout(state.token)
        .then(() => {
          commit("SET_TOKEN", "");
          commit("SET_ROLES", []);
          removeToken();
          resetRouter();
          Cookies.remove();
          localStorage.removeItem(ZONE_ID_KEY);
          // localStorage.clear();
          // reset visited views and cached views
          // to fixed https://github.com/PanJiaChen/vue-element-admin/issues/2485
          dispatch("tagsView/delAllViews", null, { root: true });
          localStorage.clear();
          resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      commit("SET_TOKEN", "");
      commit("SET_ROLES", []);
      removeToken();
      resolve();
    });
  },

  // dynamically modify permissions
  changeRoles({ commit, dispatch }, role) {
    return new Promise(async resolve => {
      const token = role + "-token";

      commit("SET_TOKEN", token);
      setToken(token);

      const { roles } = await dispatch("getInfo");

      resetRouter();

      // generate accessible routes map based on roles
      const accessRoutes = await dispatch("permission/generateRoutes", roles, {
        root: true
      });

      // dynamically add accessible routes
      router.addRoutes(accessRoutes);

      // reset visited views and cached views
      dispatch("tagsView/delAllViews", null, { root: true });

      resolve();
    });
  }
};

const getters = {
  // 角色类型
  roleType: state => state.adminInfo && state.adminInfo.is_agent,
  // 是否为区域代理商 or 商户管理员
  isAgent: state => state.adminInfo && state.adminInfo.is_agent > 0,
  // 是否为商户管理员
  isMerAdmin: state => state.adminInfo && state.adminInfo.is_agent === 2,
  // 是否为区域代理
  isZoneAgent: state => state.adminInfo && state.adminInfo.is_agent === 1,
  // 区域代理下辖区域列表 or 商户管理员下辖商户列表
  zoneList: state => state.adminInfo && state.adminInfo.region_name ? state.adminInfo.region_name : [],
  zoneMap: (state, getters) => {
    // 区域 id -> 区域信息映射
    return getters.zoneList.reduce((acc, item) => {
      acc[item.circle_id] = item;
      return acc;
    }, {});
  },
  // 选中的区域 id or 选中的商户 id
  zoneId: state => state.zoneId,
  // 代理 id or 商户管理员 id
  agentId: (state, getters) => {
    if (!getters.zoneMap || !getters.zoneId) return "";
    const zoneInfo = getters.zoneMap[getters.zoneId];
    return zoneInfo ? zoneInfo.circle_agent_id : null;
  },
  // 真实姓名
  realName: state => state.adminInfo ? state.adminInfo.real_name : "",
  zoneName: (state, getters) => {
    // 区域名称
    if (!getters.zoneMap || !getters.zoneId) return "";
    const zoneInfo = getters.zoneMap[getters.zoneId];
    return zoneInfo ? zoneInfo.name : "";
  },
  menuMap: state => {
    const menuMap = {};
    const build = list => {
      list.forEach(item => {
        menuMap[item.path] = item;
        if (item.children && item.children.length) {
          build(item.children);
        }
      });
    }
    build(state.menuList);
    return menuMap;
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
};

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import * as product from '@/api/product';

const state = {
  merSelect: [] /** 店铺列表 **/,
  merCateList: [] /** 店铺分类 **/,
  merProductLabelList: [] /** 商品标签 **/,
  sysCateList: [] /** 平台分类 **/,
  storeTypeList: [] /** 店铺类型 **/,
};

const mutations = {
  SET_MerSelect: (state, merSelect) => {
    state.merSelect = merSelect;
    // localStorage.setItem('merSelect', JSON.stringify(changeNodes(merSelect)));
    // if (!merSelect.length) localStorage.removeItem('merSelect');
  },
  SET_MerCateList: (state, merCateList) => {
    state.merCateList = merCateList;
  },
  SET_ProductLabelList: (state, merProductLabelList) => {
    state.merProductLabelList = merProductLabelList;
  },
  SET_SysCateList: (state, sysCateList) => {
    state.sysCateList = sysCateList;
  },
  SET_StoreTypeList: (state, storeTypeList) => {
    state.storeTypeList = storeTypeList;
  },
};

const actions = {
  /** 店铺列表 **/
  getMerSelect({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .merSelectApi()
        .then(async (res) => {
          commit('SET_MerSelect',res.data);
          resolve(res);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  /**店铺分类 */
  getMerCateSelect({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .getMerCateApi()
        .then(async (res) => {
          commit('SET_MerCateList',res.data);
          resolve(res);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  /**商品标签 */
  getProductLabel({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .getProductLabelApi()
        .then(async (res) => {
          commit('SET_ProductLabelList',res.data);
          resolve(res);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  /** 平台分类 **/
  getSysCategoryList({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .categoryListApi()
        .then(async (res) => {
          commit('SET_SysCateList', res.data);
          resolve(res);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  /** 店铺类型 **/
  getStoreType({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .getstoreTypeApi()
        .then(async (res) => {
          commit('SET_StoreTypeList', res.data);
          resolve(res);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

/** tree去除 childList=[] 的结构**/
const changeNodes = function (data) {
  if (data.length > 0) {
    for (var i = 0; i < data.length; i++) {
      if (data[i].isShow === false) {
        data[i].disabled = true;
      }
      if (!data[i].childList || data[i].childList.length < 1) {
        data[i].childList = undefined;
      } else {
        changeNodes(data[i].childList);
      }
    }
  }
  return data;
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  changeNodes,
};

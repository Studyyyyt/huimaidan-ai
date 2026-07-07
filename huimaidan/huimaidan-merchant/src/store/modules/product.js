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
  merCateList: [] /** 商户分类 **/,
  merProductLabelList: [] /** 商品标签 **/,
  sysCateList: [] /** 平台分类 **/,
};

const mutations = {
  SET_MerCateList: (state, merCateList) => {
    state.merCateList = merCateList;
  },
  SET_ProductLabelList: (state, merProductLabelList) => {
    state.merProductLabelList = merProductLabelList;
  },
  SET_SysCateList: (state, sysCateList) => {
    state.sysCateList = sysCateList;
  },
};

const actions = {
  /**商户分类 */
  getMerCateSelect({ commit, dispatch }) {
    return new Promise((resolve, reject) => {
      product
        .categorySelectApi()
        .then(async (res) => {
          commit('SET_MerCateList',res.data);
          console.log(res.data);
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

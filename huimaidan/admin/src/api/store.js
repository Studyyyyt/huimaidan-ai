import request from './request';

/**
 * @description 获取店铺分组列表
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 店铺分组列表
 */
export function getStoreGroupListApi(data) {
  return request.get('system/merchant/group/lst', data);
}

/**
 * @description 获取店铺分组详情
 * @param {number} id 店铺分组ID
 * @returns {Promise<Object>} 店铺分组详情
 */
export function getStoreGroupDetailApi(id) {
  return request.get(`system/merchant/group/detail/${id}`);
}

/**
 * @description 创建店铺分组
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 创建店铺分组
 */
export function createStoreGroupApi(data) {
  return request.post('system/merchant/group/create', data);
}

/**
 * @description 更新店铺分组
 * @param {number} id 店铺分组ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 更新店铺分组
 */
export function updateStoreGroupApi(id, data) {
  return request.post(`system/merchant/group/update/${id}`, data);
}

/**
 * @description 更新店铺分组状态
 * @param {number} id 店铺分组ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 更新店铺分组状态
 */
export function updateStoreGroupStatusApi(id, data) {
  return request.post(`system/merchant/group/status/${id}`, data);
}

/**
 * @description 删除店铺分组
 * @param {number} id 店铺分组ID
 * @returns {Promise<Object>} 删除店铺分组
 */
export function deleteStoreGroupApi(id) {
  return request.delete(`system/merchant/group/delete/${id}`);
}

/**
 * @description 获取店铺分组选项
 * @returns {Promise<Object>} 店铺分组选项
 */
export function getStoreGroupOptionsApi() {
  return request.get(`system/merchant/group/options`);
}

/**
 * @description 设置店铺分组模板
 * @param {number} id 店铺分组ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 设置店铺分组模板
 */
export function setStoreGroupTemplateApi(id, data) {
  return request.post(`system/merchant/group/setTemplate/${id}`, data);
}

/**
 * @description 获取店铺分组店铺列表
 * @param {number} id 店铺分组ID
 * @returns {Promise<Object>} 获取店铺分组店铺列表
 */
export function getStoreGroupStoresApi(id, data) {
  return request.get(`system/merchant/group/stores/${id}`, data);
}
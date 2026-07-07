import request from './request';

/**
 * @description 获取区域列表
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 区域列表
 */
export function getBusinessZoneListApi(data) {
  return request.get('circle/list', data);
}

/**
 * @description 获取区域详情
 * @param {number} id 区域ID
 * @returns {Promise<Object>} 区域详情
 */
export function getBusinessZoneDetailApi(id) {
  return request.get(`circle/detail/${id}`);
}

/**
 * @description 创建区域
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 创建区域
 */
export function createBusinessZoneApi(data) {
  return request.post('circle/create', data);
}

/**
 * @description 更新区域
 * @param {number} id 区域ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 更新区域
 */
export function updateBusinessZoneApi(id, data) {
  return request.post(`circle/update/${id}`, data);
}

/**
 * @description 删除区域
 * @param {number} id 区域ID
 * @returns {Promise<Object>} 删除区域
 */
export function deleteBusinessZoneApi(id) {
  return request.delete(`circle/delete/${id}`);
}

/**
 * @description 更新区域状态
 * @param {number} id 区域ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 更新区域状态
 */
export function updateBusinessZoneStatusApi(id, data) {
  return request.post(`circle/switch/${id}`, data);
}

/**
 * @description 获取区域关联店铺列表
 * @param {number} id 区域ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 区域关联店铺列表
 */
export function getZoneRelatedMerchantListApi(id, data) {
  return request.get(`circle/merchantList/${id}`, data);
}

/**
 * @description 获取区域菜单列表
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 区域菜单列表
 */
export function menuListApi(data) {
  return request.get('circle/menu/lst', data);
}

/**
 * @description 创建区域菜单
 * @returns {Promise<Object>} 创建区域菜单
 */
export function menuCreateApi() {
  return request.get('circle/menu/create/form');
}

/**
 * @description 更新区域菜单
 * @param {number} id 区域菜单ID
 * @returns {Promise<Object>} 更新区域菜单表单
 */
export function menuUpdateApi(id) {
  return request.get(`circle/menu/update/form/${id}`);
}

/**
 * @description 删除区域菜单
 * @param {number} id 区域菜单ID
 * @returns {Promise<Object>} 删除区域菜单
 */
export function menuDeleteApi(id) {
  return request.delete(`circle/menu/delete/${id}`);
}

/**
 * @description 获取区域级联菜单
 * @returns {Promise<Object>} 区域级联菜单
 */
export function getBusinessZoneSelectApi(query) {
  return request.get('circle/options', query);
}
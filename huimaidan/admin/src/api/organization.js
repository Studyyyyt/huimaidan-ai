import request from './request';

/**
 * @description 修改身份状态
 * @param {number} id 身份ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 修改身份状态
 */
export function roleStatusApi(id, data) {
  return request.post(`/organization/role/status/${id}`, data)
}

/**
 * @description 删除身份
 * @param {number} id 身份ID
 * @returns {Promise<Object>} 删除身份
 */
export function roleDeleteApi(id) {
  return request.delete(`/organization/role/delete/${id}`)
}

/**
 * @description 身份管理 -- 新增表单
 * @returns {Promise<Object>} 新增表单
 */
export function roleCreateFormApi() {
  return request.get(`/organization/role/create/form`)
}

/**
 * @description 身份管理 -- 编辑表单
 * @param {number} id 身份ID
 * @returns {Promise<Object>} 编辑表单
 */
export function roleUpdateFormApi(id) {
  return request.get(`/organization/role/update/form/${id}`)
}
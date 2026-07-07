import request from './request';

/**
 * @description 获取区域代理列表
 */
export function getAgentListApi(data) {
  return request.get('circle/agent/list', data)
}

/**
 * @description 更新区域代理状态
 * @param {number} id 代理ID
 */
export function updateAgentStatusApi(id, data) {
  return request.post(`circle/agent/audit/${id}`, data);
}

/**
 * @description 获取区域代理详情
 * @param {number} id 代理ID
 * @returns {Promise<Object>} 代理详情
 */
export function getAgentDetailApi(id) {
  return request.get(`circle/agent/detail/${id}`);
}

/**
 * @description 删除区域代理
 * @param {number} id 代理ID
 * @returns {Promise<Object>} 删除结果
 */
export function deleteAgentApi(id) {
  return request.delete(`circle/agent/delete/${id}`);
}

/**
 * @description 创建区域代理
 * @param {Object} data 代理数据
 * @returns {Promise<Object>} 创建结果
 */
export function createAgentApi(data) {
  return request.post('circle/agent/create', data);
}

/**
 * @description 更新区域代理
 * @param {number} agentId 代理ID
 * @param {Object} data 代理数据
 * @returns {Promise<Object>} 更新结果
 */
export function updateAgentApi(agentId, data) {
  return request.post(`circle/agent/update/${agentId}`, data);
}

/**
 * @description 获取区域代理关联店铺列表
 * @param {number} agentId 代理ID
 * @param {Object} data 请求参数
 * @returns {Promise<Object>} 关联店铺列表
 */
export function getAgentRelativeMerApi(agentId, data) {
  return request.get(`circle/agent/merchantList/${agentId}`, data);
}

/**
 * @description 重置区域代理密码
 * @param {number} agentId 代理ID
 * @returns {Promise<Object>} 重置结果
 */
export function resetAgentPwdApi(agentId) {
  return request.post(`circle/agent/resetPwd/${agentId}`);
}
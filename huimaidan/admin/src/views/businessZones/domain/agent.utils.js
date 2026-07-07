import { AGENT_STATUS } from './agent.enum.js';

// 初始化搜索表单
export function getInitSearchForm(status = AGENT_STATUS.ALL) {
  return {
    name: "", // 代理名称
    phone: "", // 代理手机号
    createTime: [], // 创建时间
    nickname: "", // 用户昵称
    user_phone: "", // 用户手机号
    uid: "", // 用户ID
    status
  }
}
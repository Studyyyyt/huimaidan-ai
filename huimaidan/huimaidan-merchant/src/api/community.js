// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import request from './request'

/**
 * @description 内容-获取用户列表
 */
export function getUserlistApi(data) {
  return request.get(`manager/user/lst`, data)
}


/**
 * @description 内容-获取商品列表
 */
export function getGoodslistApi(data) {
  return request.get(`product_list`, data)
}


/**
 * @description 内容 -- 社区列表
 */
export function getCommunitylistApi(data) {
  return request.get(`community/lst`, data)
}


/**
 * @description 内容 -- 添加内容
 */
export function communityCreateApi(data) {
  return request.post(`community/create`, data)
}


/**
 * @description 内容 -- 编辑内容
 */
export function communityUpdateApi(id,data) {
  return request.post(`community/update/${id}`, data)
}


/**
 * @description 内容 -- 内容详情
 */
export function communityDetailApi(id) {
  return request.get(`community/detail/${id}`)
}


/**
 * @description 内容 -- 内容删除
 */
export function communityDeleteApi(id) {
  return request.delete(`community/delete/${id}`)
}


/**
 * @description 内容 -- 话题列表
 */
export function communityCateApi() {
  return request.get(`community/cate/lst`)
}

/**
 * @description 内容 -- 评论列表
 */
export function communityReplyApi(id,data) {
  return request.get(`community/reply/${id}`,data)
}

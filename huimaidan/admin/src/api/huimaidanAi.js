import request from './request'
import SettingMer from '@/libs/settingMer'
import { getToken } from '@/utils/auth'

export function aiTagsApi(params) {
  return request.get('huimaidan/ai/tags', params)
}

export function aiTagSaveApi(data) {
  return request.post('huimaidan/ai/tag/save', data)
}

export function aiTagImportApi(tags) {
  return request.post('huimaidan/ai/tag/import', { tags })
}

export function aiTagDeleteApi(id) {
  return request.delete(`huimaidan/ai/tag/delete/${id}`)
}

export function aiMerchantTagsApi(merId) {
  return request.get(`huimaidan/ai/merchant_tags/${merId}`)
}

export function aiMerchantsApi(params) {
  return request.get('huimaidan/ai/merchants', params)
}

export function aiMerchantTagsSaveApi(merId, tags) {
  return request.post(`huimaidan/ai/merchant_tags/${merId}`, { tags })
}

export function aiMerchantTagsInitApi(merId) {
  return request.post('huimaidan/ai/merchant_tags/init', { mer_id: merId || 0 })
}

export function aiMerchantImportTemplateUrl() {
  return `${SettingMer.https}/huimaidan/ai/merchant_import/template?token=${getToken()}`
}

export function aiMerchantImportApi(data) {
  return request.post('huimaidan/ai/merchant_import', data, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

export function aiBannersApi() {
  return request.get('huimaidan/ai/banners')
}

export function aiBannerSaveApi(data) {
  return request.post('huimaidan/ai/banner/save', data)
}

export function aiBannerDeleteApi(id) {
  return request.delete(`huimaidan/ai/banner/delete/${id}`)
}

export function aiConfigsApi() {
  return request.get('huimaidan/ai/configs')
}

export function aiConfigSaveApi(data) {
  return request.post('huimaidan/ai/config/save', data)
}

export function aiConfigDeleteApi(id) {
  return request.delete(`huimaidan/ai/config/delete/${id}`)
}

export function aiLogsApi(params) {
  return request.get('huimaidan/ai/logs', params)
}

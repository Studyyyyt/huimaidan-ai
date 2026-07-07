export interface IHttpBusinessError extends Error {
  data?: unknown
  response?: unknown
}

function responseMessage(responseData: Record<string, unknown>) {
  const message = responseData.message ?? responseData.msg
  return typeof message === 'string' && message.trim() ? message : '请求错误'
}

export function normalizeBusinessError(responseData: Record<string, unknown>): IHttpBusinessError {
  const error = new Error(responseMessage(responseData)) as IHttpBusinessError
  error.data = responseData.data
  error.response = responseData
  return error
}

export type TWeixinEnvVersion = 'develop' | 'trial' | 'release' | string

export interface ISelectWeixinBaseUrlOptions {
  defaultBaseUrl: string
  envVersion: TWeixinEnvVersion
  weixinDevelopBaseUrl?: string
  weixinTrialBaseUrl?: string
  weixinReleaseBaseUrl?: string
}

function normalizeOptionalBaseUrl(value?: string) {
  return value?.trim() || ''
}

export function selectWeixinBaseUrl(options: ISelectWeixinBaseUrlOptions) {
  const envMap: Record<string, string> = {
    develop: normalizeOptionalBaseUrl(options.weixinDevelopBaseUrl),
    trial: normalizeOptionalBaseUrl(options.weixinTrialBaseUrl),
    release: normalizeOptionalBaseUrl(options.weixinReleaseBaseUrl),
  }

  return envMap[options.envVersion] || options.defaultBaseUrl
}

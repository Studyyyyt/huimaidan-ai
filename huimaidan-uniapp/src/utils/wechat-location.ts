export interface WechatUserLocation {
  latitude: number
  longitude: number
}

interface WechatLocationSuccess {
  latitude: number
  longitude: number
}

interface WechatLocationFail {
  errMsg?: string
  errno?: number
}

declare const wx: {
  getLocation: (options: {
    type: 'gcj02'
    isHighAccuracy: true
    highAccuracyExpireTime: number
    success: (res: WechatLocationSuccess) => void
    fail: (error: unknown) => void
  }) => void
}

function toLocationError(error: unknown) {
  if (error instanceof Error) {
    return error
  }

  if (typeof error === 'object' && error !== null) {
    const locationError = error as WechatLocationFail
    if (locationError.errMsg) {
      return Object.assign(new Error(`定位失败：${locationError.errMsg}`), {
        errno: locationError.errno,
        raw: error,
      })
    }
  }

  if (typeof error === 'string' && error) {
    return new Error(`定位失败：${error}`)
  }

  return new Error('定位失败，请稍后重试或检查定位权限')
}

export function getWechatUserLocation(): Promise<WechatUserLocation> {
  return new Promise((resolve, reject) => {
    // #ifdef MP-WEIXIN
    wx.getLocation({
      type: 'gcj02',
      isHighAccuracy: true,
      highAccuracyExpireTime: 4000,
      success: (res) => {
        resolve({
          latitude: res.latitude,
          longitude: res.longitude,
        })
      },
      fail: (error) => {
        reject(toLocationError(error))
      },
    })
    // #endif

    // #ifndef MP-WEIXIN
    reject(new Error('当前端暂不支持位置推荐'))
    // #endif
  })
}

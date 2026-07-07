// 获取屏幕边界到安全区域距离（懒加载，避免模块初始化时调用 API 导致加载失败）

interface SafeAreaInsets {
  top: number
  right: number
  bottom: number
  left: number
}

let _systemInfo: any = null
let _safeAreaInsets: SafeAreaInsets | null = null
let _initialized = false

function init() {
  if (_initialized)
    return
  _initialized = true

  try {
    // #ifdef MP-WEIXIN
    // 微信小程序使用新的API
    _systemInfo = uni.getWindowInfo()
    _safeAreaInsets = _systemInfo.safeArea
      ? {
          top: _systemInfo.safeArea.top,
          right: _systemInfo.windowWidth - _systemInfo.safeArea.right,
          bottom: _systemInfo.windowHeight - _systemInfo.safeArea.bottom,
          left: _systemInfo.safeArea.left,
        }
      : null
    // #endif

    // #ifndef MP-WEIXIN
    // 其他平台继续使用uni API
    _systemInfo = uni.getSystemInfoSync()
    _safeAreaInsets = _systemInfo.safeAreaInsets
    // #endif

    console.log('systemInfo', _systemInfo)
  }
  catch (e) {
    console.error('初始化 systemInfo 失败:', e)
  }
}

// 延迟初始化，在首次访问时才执行
export function getSystemInfo() {
  init()
  return _systemInfo
}

export function getSafeAreaInsets() {
  init()
  return _safeAreaInsets
}

import type { Plugin } from 'vite'
import fs from 'node:fs'
import path from 'node:path'
import process from 'node:process'

interface ManifestType {
  'plus'?: {
    distribute?: {
      plugins?: Record<string, any>
    }
  }
  'app-plus'?: {
    distribute?: {
      plugins?: Record<string, any>
    }
  }
  'mp-weixin'?: WeixinAppJson
}

interface WeixinAppJson {
  permission?: Record<string, { desc?: string }>
  requiredPrivateInfos?: string[]
}

const WEIXIN_LOCATION_SCOPE = 'scope.userLocation'
const WEIXIN_GET_LOCATION_PRIVATE_INFO = 'getLocation'

function readJson<T>(filePath: string): T {
  return JSON.parse(fs.readFileSync(filePath, 'utf8')) as T
}

function writeJson(filePath: string, value: unknown) {
  fs.writeFileSync(filePath, `${JSON.stringify(value, null, 2)}\n`)
}

function applyWeixinLocationPermission(appJson: WeixinAppJson, sourceMpWeixin: WeixinAppJson) {
  const locationPermission = sourceMpWeixin.permission?.[WEIXIN_LOCATION_SCOPE]

  if (!locationPermission?.desc) {
    throw new Error(`src/manifest.json 缺少 mp-weixin.permission.${WEIXIN_LOCATION_SCOPE}.desc`)
  }

  if (!sourceMpWeixin.requiredPrivateInfos?.includes(WEIXIN_GET_LOCATION_PRIVATE_INFO)) {
    throw new Error(`src/manifest.json 缺少 mp-weixin.requiredPrivateInfos.${WEIXIN_GET_LOCATION_PRIVATE_INFO}`)
  }

  appJson.permission = {
    ...appJson.permission,
    [WEIXIN_LOCATION_SCOPE]: {
      ...appJson.permission?.[WEIXIN_LOCATION_SCOPE],
      desc: locationPermission.desc,
    },
  }

  appJson.requiredPrivateInfos = Array.from(new Set([
    ...(Array.isArray(appJson.requiredPrivateInfos) ? appJson.requiredPrivateInfos : []),
    ...sourceMpWeixin.requiredPrivateInfos,
  ]))

  return appJson
}

function getWeixinAppJsonPaths(mode: string) {
  const currentDistType = mode === 'development' ? 'dev' : 'build'
  const currentPath = path.resolve(process.cwd(), `./dist/${currentDistType}/mp-weixin/app.json`)
  const knownPaths = [
    currentPath,
    path.resolve(process.cwd(), './dist/dev/mp-weixin/app.json'),
    path.resolve(process.cwd(), './dist/build/mp-weixin/app.json'),
  ]

  return {
    currentPath,
    paths: Array.from(new Set(knownPaths)),
  }
}

function syncWeixinLocationPermissions(srcManifestPath: string, mode: string) {
  if (process.env.UNI_PLATFORM !== 'mp-weixin') {
    return
  }

  const { currentPath, paths } = getWeixinAppJsonPaths(mode)

  if (!fs.existsSync(currentPath)) {
    throw new Error(`未找到当前模式微信小程序 app.json，无法同步定位权限配置: ${currentPath}`)
  }

  const srcManifest = readJson<ManifestType>(srcManifestPath)
  const sourceMpWeixin = srcManifest['mp-weixin']
  if (!sourceMpWeixin) {
    throw new Error('src/manifest.json 缺少 mp-weixin 配置')
  }

  for (const distAppPath of paths) {
    if (!fs.existsSync(distAppPath)) {
      continue
    }

    const distAppJson = readJson<WeixinAppJson>(distAppPath)
    applyWeixinLocationPermission(distAppJson, sourceMpWeixin)
    writeJson(distAppPath, distAppJson)
    console.log(`✅ 微信小程序定位权限同步成功: ${path.relative(process.cwd(), distAppPath)}`)
  }
}

export default function syncManifestPlugin(): Plugin {
  let mode = 'production'

  return {
    name: 'sync-manifest',
    apply: 'build',
    enforce: 'post',
    configResolved(config) {
      mode = config.mode
    },
    writeBundle: {
      order: 'post',
      handler() {
        const srcManifestPath = path.resolve(process.cwd(), './src/manifest.json')
        const distAppPath = path.resolve(process.cwd(), './dist/dev/app/manifest.json')

        try {
          // 读取源文件
          const srcManifest = readJson<ManifestType>(srcManifestPath)

          // 确保目标目录存在
          const distAppDir = path.dirname(distAppPath)
          if (!fs.existsSync(distAppDir)) {
            fs.mkdirSync(distAppDir, { recursive: true })
          }

          // 读取目标文件（如果存在）
          let distManifest: ManifestType = {}
          if (fs.existsSync(distAppPath)) {
            distManifest = readJson<ManifestType>(distAppPath)
          }

          // 如果源文件存在 plugins
          if (srcManifest['app-plus']?.distribute?.plugins) {
            // 确保目标文件中有必要的对象结构
            if (!distManifest.plus)
              distManifest.plus = {}
            if (!distManifest.plus.distribute)
              distManifest.plus.distribute = {}

            // 复制 plugins 内容
            distManifest.plus.distribute.plugins = srcManifest['app-plus'].distribute.plugins

            // 写入更新后的内容
            writeJson(distAppPath, distManifest)
            console.log('✅ Manifest plugins 同步成功')
          }
        }
        catch (error) {
          console.error('❌ 同步 manifest plugins 失败:', error)
        }

        syncWeixinLocationPermissions(srcManifestPath, mode)
      },
    },
  }
}

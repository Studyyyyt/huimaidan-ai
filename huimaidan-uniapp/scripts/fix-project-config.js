import fs from 'node:fs'
import path from 'node:path'
import process from 'node:process'

/**
 * 修复微信开发者工具 project.config.json 被错误识别为小游戏的问题。
 * 部分情况下构建产物中的 compileType 会被生成为 "game"，
 * 需要将其强制修正为 "miniprogram"。
 */

const projectRoot = process.cwd()

// 需要检查的 project.config.json 路径（开发/生产）
const targetFiles = [
  path.join(projectRoot, 'dist/dev/mp-weixin/project.config.json'),
  path.join(projectRoot, 'dist/build/mp-weixin/project.config.json'),
]

function fixProjectConfig(filePath) {
  if (!fs.existsSync(filePath)) {
    return
  }

  let content
  try {
    content = fs.readFileSync(filePath, 'utf-8')
  }
  catch (error) {
    console.error(`[fix-project-config] 读取失败: ${filePath}`, error)
    return
  }

  let config
  try {
    config = JSON.parse(content)
  }
  catch (error) {
    console.error(`[fix-project-config] JSON 解析失败: ${filePath}`, error)
    return
  }

  if (config.compileType !== 'miniprogram') {
    config.compileType = 'miniprogram'
    try {
      fs.writeFileSync(filePath, `${JSON.stringify(config, null, 2)}\n`, 'utf-8')
      console.log(`[fix-project-config] 已修复: ${filePath} -> compileType = "miniprogram"`)
    }
    catch (error) {
      console.error(`[fix-project-config] 写入失败: ${filePath}`, error)
    }
  }
}

for (const file of targetFiles) {
  fixProjectConfig(file)
}

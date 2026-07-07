const { Automator } = require('@dcloudio/uni-automator');
const path = require('path');
const { spawn } = require('child_process');
const http = require('http');

const projectPath = path.resolve(__dirname, '../dist/dev/mp-weixin');
const cliPath = '/Applications/wechatwebdevtools.app/Contents/MacOS/cli';

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function normalizePath(p) {
  return typeof p === 'string' ? p.replace(/^\//, '') : p;
}

async function waitForElement(page, selector, timeoutMs = 6000, intervalMs = 500) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    const el = await page.$(selector);
    if (el) return el;
    await sleep(intervalMs);
  }
  return null;
}

async function dumpPageXml(page, label) {
  try {
    const wxml = await page.wxml();
    console.log(`[${label}] 页面 WXML 前 2000 字符:\n`, wxml.substring(0, 2000));
  } catch (e) {
    console.log(`[${label}] 获取 WXML 失败:`, e.message);
  }
}

function checkWebsocketPort(port) {
  return new Promise((resolve) => {
    const req = http.get(`http://127.0.0.1:${port}/json`, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => resolve(data.length > 0));
    });
    req.on('error', () => resolve(false));
    req.setTimeout(2000, () => { req.destroy(); resolve(false); });
  });
}

async function launchDevTools() {
  console.log('手动启动微信开发者工具...');
  const proc = spawn(cliPath, ['auto', '--project', projectPath, '--auto-port', '9520'], {
    detached: true,
    stdio: 'ignore',
  });
  proc.unref();

  // 等待远程调试 WebSocket 端口就绪
  for (let i = 0; i < 30; i++) {
    if (await checkWebsocketPort(9420)) {
      console.log('微信开发者工具远程调试端口已就绪');
      return;
    }
    await sleep(1000);
  }
  throw new Error('微信开发者工具远程调试端口未就绪');
}

async function main() {
  await launchDevTools();
  await sleep(3000);

  console.log('正在连接自动化...');
  const automator = new Automator();
  const miniProgram = await automator.launch({
    projectPath,
    platform: 'mp-weixin',
    launch: false,
    port: 9420,
    timeout: 60000,
  });
  console.log('已连接小程序');

  const results = [];

  try {
    // 1. 打开首页
    const homePage = await miniProgram.reLaunch('/pages/index/index');
    await sleep(3000);
    const homePath = normalizePath(homePage.path);
    console.log('当前页面路径:', homePath);
    results.push({ step: '打开首页', path: homePath, ok: homePath === 'pages/index/index' });

    await dumpPageXml(homePage, '首页');

    // 2. 点击 Banner 推荐商家胶囊，应进入商户详情
    const merchantCapsule = await waitForElement(homePage, '.ai-smart-banner .inline-flex', 8000);
    if (merchantCapsule) {
      await merchantCapsule.tap();
      await sleep(2000);
      const detailPage = await miniProgram.currentPage();
      const detailPath = normalizePath(detailPage.path);
      console.log('点击推荐商家后路径:', detailPath);
      results.push({ step: '点击 Banner 推荐商家', path: detailPath, ok: detailPath.includes('pages/merchant/detail') });
    } else {
      console.log('⚠️ 未找到 Banner 推荐商家胶囊');
      results.push({ step: '点击 Banner 推荐商家', path: 'N/A', ok: false, note: '未找到胶囊元素' });
    }

    // 3. 返回首页，点击 Banner 主体（非胶囊区域），应进入 AI 对话页
    await miniProgram.reLaunch('/pages/index/index');
    await sleep(3000);
    const homePage2 = await miniProgram.currentPage();
    const banner = await waitForElement(homePage2, '.ai-smart-banner', 8000);
    if (banner) {
      await banner.tap();
      await sleep(2000);
      const chatPage = await miniProgram.currentPage();
      const chatPath = normalizePath(chatPage.path);
      console.log('点击 Banner 主体后路径:', chatPath);
      results.push({ step: '点击 Banner 主体', path: chatPath, ok: chatPath === 'pages/ai-chat/index' });
    } else {
      console.log('⚠️ 未找到 Banner 主体');
      results.push({ step: '点击 Banner 主体', path: 'N/A', ok: false, note: '未找到 Banner' });
    }

    // 4. 直接打开 AI 对话页
    const aiChatPage = await miniProgram.reLaunch('/pages/ai-chat/index');
    await sleep(2000);
    const aiChatPath = normalizePath(aiChatPage.path);
    results.push({ step: '打开 AI 对话页', path: aiChatPath, ok: aiChatPath === 'pages/ai-chat/index' });

    // 5. 直接打开商户详情页
    const merchantPage = await miniProgram.reLaunch('/pages/merchant/detail?id=1\u0026from=ai_banner');
    await sleep(2000);
    const merchantPath = normalizePath(merchantPage.path);
    results.push({ step: '打开商户详情页', path: merchantPath, ok: merchantPath.includes('pages/merchant/detail') });

    console.log('\n测试结果汇总:');
    for (const r of results) {
      console.log(`${r.ok ? '✅' : '❌'} ${r.step}: ${r.path}${r.note ? ' (' + r.note + ')' : ''}`);
    }

    await miniProgram.close();
    process.exit(results.every(r => r.ok) ? 0 : 1);
  } catch (e) {
    console.error('测试出错:', e);
    try {
      await miniProgram.close();
    } catch (_) {}
    process.exit(1);
  }
}

main().catch(e => {
  console.error('启动失败:', e);
  process.exit(1);
});

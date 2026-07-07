# Composer Vendor 迁移说明

本仓从 `d37a020682ad79730551414e9684745f70a82c25` 基线开始，将官方随包 `vendor/` 里的运行补丁外置为 Composer patches，目标是让依赖可以由 Composer 重装，同时保留 CRMEB 官方包里的行为修正。

## 迁移边界

- `vendor/` 不再作为业务源码提交，改由 `composer.lock` 与 `patches/vendor/*.patch` 复现。
- 官方随包 vendor 中的行为补丁已外置到 `composer.json` 的 `extra.patches`。
- `guzzlehttp/command`、`guzzlehttp/guzzle-services` 等官方 vendor 独有历史目录未在非 vendor 代码中发现引用，本轮不重新声明为依赖。
- `weeks/logviewer` 原在旧 lock/vendor 中存在，但不受 `composer.json` 约束，且非 vendor 代码未发现引用；同步 lock 时已移除。

## WSL 验证命令

项目运行环境以 WSL PHP 7.3 为准。Windows 侧 Composer 可以通过 WSL PHP 调用同一个 `composer.phar`：

```bash
cd /mnt/c/Users/venuz/Desktop/works/yukatong/crmeb
php /mnt/c/ProgramData/ComposerSetup/bin/composer.phar install --no-scripts --prefer-dist
php /mnt/c/ProgramData/ComposerSetup/bin/composer.phar validate --no-check-publish --no-ansi
php /mnt/c/ProgramData/ComposerSetup/bin/composer.phar check-platform-reqs --no-dev --no-ansi
```

`composer install` 会自动应用 `patches/vendor/` 下的补丁。若补丁无法应用，Composer 必须失败，不允许静默跳过。

## 已外置补丁

- `doctrine/cache`：修正文件缓存路径生成兼容性。
- `riverslei/payment`：保留支付宝禁用支付渠道参数、付款中状态与 PHP 7.4+ 兼容修正。
- `topthink/framework`：保留路由分组边界匹配修正。
- `topthink/think-swoole`：保留官方随包源码一致性。
- `topthink/think-trace`：保留 trace 注入禁用行为。
- `ucloud/ufile-php-sdk`：保留官方随包源码一致性。
- `xaboy/form-builder`：保留表单组件解析、上传、日期范围与 iframe 校验行为修正。

## 操作规则

- 不直接修改 `vendor/`；需要改第三方包时，先生成或更新 `patches/vendor/*.patch`。
- 不做无边界 `composer update`；新增依赖时限定包名，并说明原因。
- 修改 patches 后必须在 WSL fresh install 中验证补丁可重放。

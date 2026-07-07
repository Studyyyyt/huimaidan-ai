<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\user\UserRepository;

$authFile = dirname(__DIR__, 2) . '/app/controller/api/Auth.php';
$authSource = file_get_contents($authFile);
if (strpos($authSource, 'function refreshToken(') === false) {
    throw new RuntimeException('Auth 控制器缺少小程序 token 刷新入口 refreshToken');
}

$routeFile = dirname(__DIR__, 2) . '/route/api.php';
$routeSource = file_get_contents($routeFile);
if (strpos($routeSource, "auth/refresh_token") === false) {
    throw new RuntimeException('route/api.php 缺少 /api/auth/refresh_token 路由');
}
if (strpos($routeSource, 'UserTokenMiddleware::class, true') === false) {
    throw new RuntimeException('刷新 token 路由必须复用 UserTokenMiddleware 强制登录校验');
}

$repositoryReflection = new ReflectionClass(UserRepository::class);
$checkToken = $repositoryReflection->getMethod('checkToken');
$source = file($checkToken->getFileName());
$methodSource = implode('', array_slice(
    $source,
    $checkToken->getStartLine() - 1,
    $checkToken->getEndLine() - $checkToken->getStartLine() + 1
));

if (strpos($methodSource, "Cache::get('admin.user_token_valid_exp'") !== false) {
    throw new RuntimeException('UserRepository::checkToken 不能从 Cache 读取 token 有效期配置');
}
if (strpos($methodSource, "Config::get('admin.user_token_valid_exp'") === false) {
    throw new RuntimeException('UserRepository::checkToken 必须从 Config 读取 token 有效期配置');
}

echo "UserTokenRefreshContractTest passed\n";

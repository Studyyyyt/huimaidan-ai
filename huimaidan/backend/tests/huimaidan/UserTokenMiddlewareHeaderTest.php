<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\middleware\UserTokenMiddleware;

$reflection = new ReflectionClass(UserTokenMiddleware::class);
$middleware = $reflection->newInstanceWithoutConstructor();

$tokenFromHeaders = $reflection->getMethod('tokenFromHeaders');
$tokenFromHeaders->setAccessible(true);

$legacy = $tokenFromHeaders->invoke($middleware, [
    'X-Token' => 'legacy-token',
    'Authorization' => '',
    'Authori-zation' => '',
]);
if ($legacy !== 'legacy-token') {
    throw new RuntimeException('X-Token token 解析失败: ' . var_export($legacy, true));
}

$authorizationBearer = $tokenFromHeaders->invoke($middleware, [
    'X-Token' => '',
    'Authorization' => 'Bearer jwt-token',
    'Authori-zation' => '',
]);
if ($authorizationBearer !== 'jwt-token') {
    throw new RuntimeException('Authorization Bearer token 解析失败: ' . var_export($authorizationBearer, true));
}

$authorizationDirect = $tokenFromHeaders->invoke($middleware, [
    'X-Token' => '',
    'Authorization' => 'direct-token',
    'Authori-zation' => '',
]);
if ($authorizationDirect !== 'direct-token') {
    throw new RuntimeException('Authorization 直接 token 解析失败: ' . var_export($authorizationDirect, true));
}

$hyphenAuthorization = $tokenFromHeaders->invoke($middleware, [
    'X-Token' => '',
    'Authorization' => '',
    'Authori-zation' => 'Bearer hyphen-token',
]);
if ($hyphenAuthorization !== 'hyphen-token') {
    throw new RuntimeException('Authori-zation Bearer token 解析失败: ' . var_export($hyphenAuthorization, true));
}

$xTokenWins = $tokenFromHeaders->invoke($middleware, [
    'X-Token' => 'legacy-token',
    'Authorization' => 'Bearer jwt-token',
    'Authori-zation' => '',
]);
if ($xTokenWins !== 'legacy-token') {
    throw new RuntimeException('X-Token 优先级不正确: ' . var_export($xTokenWins, true));
}

echo "UserTokenMiddlewareHeaderTest passed\n";

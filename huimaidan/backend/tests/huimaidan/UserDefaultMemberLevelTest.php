<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\user\UserRepository;

$reflection = new ReflectionClass(UserRepository::class);
$create = $reflection->getMethod('create');
$source = file($create->getFileName());
$methodSource = implode('', array_slice(
    $source,
    $create->getStartLine() - 1,
    $create->getEndLine() - $create->getStartLine() + 1
));

if (strpos($methodSource, "\$userInfo['member_level'] = 1;") === false) {
    throw new RuntimeException('UserRepository::create 创建新用户时必须默认写入 member_level=1');
}

if (strpos($methodSource, '$this->dao->create($userInfo)') === false) {
    throw new RuntimeException('UserRepository::create 必须通过统一 userInfo 数据创建用户');
}

$registr = $reflection->getMethod('registr');
$registrSource = implode('', array_slice(
    $source,
    $registr->getStartLine() - 1,
    $registr->getEndLine() - $registr->getStartLine() + 1
));
if (strpos($registrSource, 'return $this->create($user_type, $data);') === false) {
    throw new RuntimeException('手机号注册必须继续复用 UserRepository::create 的默认会员等级逻辑');
}

echo "UserDefaultMemberLevelTest passed\n";

<?php

// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace app\common\repositories\huimaidan;

use think\exception\ValidateException;

class HuimaidanStoreQrcodeSceneService
{
    const SCENE_TYPE_PAYMENT_CHECKOUT = 'payment_checkout';
    const PAGE_PATH = 'pages/scan-entry/index';
    const ENTRY_CODE_MIN_LENGTH = 6;
    const ENTRY_CODE_MAX_LENGTH = 10;
    const SCENE_MAX_LENGTH = 32;

    protected $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';

    public function newEntryCode(int $length = self::ENTRY_CODE_MIN_LENGTH): string
    {
        $length = max(self::ENTRY_CODE_MIN_LENGTH, min(self::ENTRY_CODE_MAX_LENGTH, $length));
        $max = strlen($this->chars) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $this->chars[random_int(0, $max)];
        }
        return $code;
    }

    public function buildScene(int $merId, string $entryCode): string
    {
        if ($merId <= 0) {
            throw new ValidateException('商户ID无效');
        }
        $entryCode = trim($entryCode);
        if (!preg_match('/^[A-Za-z0-9]{6,10}$/', $entryCode)) {
            throw new ValidateException('入口码格式错误');
        }
        $scene = 'm' . $merId . '.e' . $entryCode;
        $this->assertScene($scene);
        return $scene;
    }

    public function parseScene(string $scene): array
    {
        $scene = trim($scene);
        if ($scene === '') {
            throw new ValidateException('二维码参数错误');
        }
        if (strpos($scene, '%') !== false) {
            throw new ValidateException('二维码参数错误');
        }
        if (!preg_match('/^m([1-9][0-9]*)\.e([A-Za-z0-9]{6,10})$/', $scene, $matches)) {
            throw new ValidateException('二维码参数错误');
        }
        return [
            'mer_id' => (int)$matches[1],
            'entry_code' => $matches[2],
        ];
    }

    public function normalizePagePath(string $pagePath): string
    {
        $pagePath = trim($pagePath);
        $pagePath = ltrim($pagePath, '/');
        if ($pagePath === '' || strpos($pagePath, '?') !== false) {
            throw new ValidateException('小程序页面路径错误');
        }
        return $pagePath;
    }

    protected function assertScene(string $scene): void
    {
        if (strlen($scene) > self::SCENE_MAX_LENGTH) {
            throw new ValidateException('二维码参数超长');
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $scene)) {
            throw new ValidateException('二维码参数包含非法字符');
        }
    }
}

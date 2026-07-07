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

class HuimaidanStoreQrcodeImageService
{
    const RELATIVE_DIR = '/uploads/huimaidan/store-qrcode';
    const TEMP_DEVELOP_SITE_URL = 'https://dev-huimaidan.yeeaf.net';

    protected $publicRoot;
    protected $siteUrl;

    public function __construct(?string $publicRoot = null, ?string $siteUrl = null)
    {
        $this->publicRoot = rtrim($publicRoot ?: app()->getRootPath() . 'public', "\\/");
        $this->siteUrl = $siteUrl;
    }

    public function save(int $merId, string $binary): array
    {
        if ($merId <= 0) {
            throw new ValidateException('商户ID无效');
        }
        if (@getimagesizefromstring($binary) === false) {
            throw new ValidateException('二维码图片内容无效');
        }

        $relativePath = self::RELATIVE_DIR . '/' . $merId . '.png';
        $absolutePath = $this->absolutePath($relativePath);
        $dir = dirname($absolutePath);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new ValidateException('二维码图片目录创建失败');
        }
        if (file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
            throw new ValidateException('二维码图片保存失败');
        }

        return [
            'path' => $relativePath,
            'url' => $this->url($relativePath),
            'absolute_path' => $absolutePath,
        ];
    }

    public function exists(?string $relativePath): bool
    {
        if (!$relativePath || strpos($relativePath, 'http') === 0) {
            return false;
        }
        return is_file($this->absolutePath($relativePath));
    }

    public function absolutePath(string $relativePath): string
    {
        $relativePath = '/' . ltrim($relativePath, '/');
        return $this->publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    protected function url(string $relativePath): string
    {
        if ($this->siteUrl === null) {
            // 临时操作：开发环境二维码图片强制走 dev 域名，避免返回 crmeb.local；上线前改回 site_url 配置。
            return self::temporaryDevelopUrl($relativePath);
        }

        $base = $this->siteUrl;
        $base = rtrim((string)$base, '/');
        return $base ? $base . $relativePath : $relativePath;
    }

    public static function temporaryDevelopUrl(string $relativePath): string
    {
        $relativePath = '/' . ltrim($relativePath, '/');
        return rtrim(self::TEMP_DEVELOP_SITE_URL, '/') . $relativePath;
    }
}

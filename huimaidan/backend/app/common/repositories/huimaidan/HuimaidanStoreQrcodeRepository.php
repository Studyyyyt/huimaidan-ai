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

use app\common\dao\huimaidan\StoreQrcodeDao;
use app\common\model\huimaidan\StoreQrcode;
use app\common\model\system\merchant\Merchant;
use app\common\repositories\BaseRepository;
use app\common\repositories\system\merchant\MerchantRepository;
use crmeb\services\huimaidan\StoreQrcodeWechatService;
use think\exception\ValidateException;
use think\facade\Log;
use think\Model;

/**
 * @mixin StoreQrcodeDao
 */
class HuimaidanStoreQrcodeRepository extends BaseRepository
{
    const STATUS_DISABLE = StoreQrcode::STATUS_DISABLE;
    const STATUS_ENABLE = StoreQrcode::STATUS_ENABLE;

    const GENERATE_FAIL = StoreQrcode::GENERATE_FAIL;
    const GENERATE_SUCCESS = StoreQrcode::GENERATE_SUCCESS;

    const SOURCE_MERCHANT = 'merchant';
    const SOURCE_ADMIN = 'admin';

    protected $sceneService;
    protected $wechatService;
    protected $imageService;
    protected $profileRepository;

    public function __construct(
        StoreQrcodeDao $dao,
        HuimaidanStoreQrcodeSceneService $sceneService,
        StoreQrcodeWechatService $wechatService,
        HuimaidanStoreQrcodeImageService $imageService,
        MerchantProfileRepository $profileRepository
    ) {
        $this->dao = $dao;
        $this->sceneService = $sceneService;
        $this->wechatService = $wechatService;
        $this->imageService = $imageService;
        $this->profileRepository = $profileRepository;
    }

    public function getList(array $where, $page, $limit): array
    {
        $where['scene_type'] = $where['scene_type'] ?? HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT;
        $query = $this->dao->search($where);
        $count = $query->count();
        $rows = $this->dao->search($where)->page($page, $limit)->select()->toArray();
        $merchantNames = $this->merchantNames(array_column($rows, 'mer_id'));
        $list = array_map(function (array $row) use ($merchantNames) {
            return $this->payload($row, [
                'mer_name' => $merchantNames[(int)$row['mer_id']] ?? '',
            ]);
        }, $rows);
        return compact('count', 'list');
    }

    public function merchantDetail(int $merId): array
    {
        return $this->detailByMerId($merId, true, self::SOURCE_MERCHANT);
    }

    public function adminDetail(int $merId): array
    {
        return $this->detailByMerId($merId, true, self::SOURCE_ADMIN);
    }

    public function detailByMerId(int $merId, bool $autoGenerate = true, string $source = self::SOURCE_MERCHANT): array
    {
        $merchant = $this->assertMerchant($merId);
        $record = $this->getOrCreateRecord($merId);
        if ($autoGenerate && $this->shouldGenerate($record)) {
            return $this->generateRecord($record, $merchant, false, $source, 0, '');
        }
        return $this->payload($record, $merchant);
    }

    public function refresh(int $merId, bool $adminForce = false, string $source = self::SOURCE_MERCHANT, int $operatorId = 0, string $reason = ''): array
    {
        $merchant = $this->assertMerchant($merId);
        $record = $this->getOrCreateRecord($merId);
        if (!$adminForce) {
            $this->assertRefreshAllowed($record);
        }
        return $this->generateRecord($record, $merchant, true, $source, $operatorId, $reason);
    }

    public function download(int $merId): array
    {
        $merchant = $this->assertMerchant($merId);
        $record = $this->getOrCreateRecord($merId);
        if ($this->shouldGenerate($record)) {
            $this->generateRecord($record, $merchant, false, self::SOURCE_MERCHANT, 0, '');
            $record = $this->dao->get((int)$record->id);
        }
        $data = $this->toArray($record);
        if (empty($data['qr_image_path']) || !$this->imageService->exists($data['qr_image_path'])) {
            throw new ValidateException('二维码图片不存在，请先刷新二维码');
        }
        return [
            'absolute_path' => $this->imageService->absolutePath($data['qr_image_path']),
            'file_name' => 'huimaidan-store-qrcode-' . $merId . '.png',
        ];
    }

    public function markAccessByScene(string $scene): array
    {
        $parsed = $this->sceneService->parseScene($scene);
        $record = $this->dao->getBySceneValue($scene);
        if (!$record || (int)$record->mer_id !== (int)$parsed['mer_id']) {
            throw new ValidateException('二维码参数错误');
        }
        $this->dao->update((int)$record->id, [
            'last_access_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->payload($this->dao->get((int)$record->id), $this->assertMerchant((int)$record->mer_id));
    }

    protected function getOrCreateRecord(int $merId)
    {
        $record = $this->dao->getByMerIdAndType($merId, HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT);
        if ($record) {
            return $this->ensureRecordDefaults($record);
        }

        for ($i = 0; $i < 5; $i++) {
            $entryCode = $this->uniqueEntryCode();
            $scene = $this->sceneService->buildScene($merId, $entryCode);
            $now = date('Y-m-d H:i:s');
            try {
                return $this->dao->create([
                    'mer_id' => $merId,
                    'entry_code' => $entryCode,
                    'scene_value' => $scene,
                    'scene_type' => HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT,
                    'page_path' => HuimaidanStoreQrcodeSceneService::PAGE_PATH,
                    'qr_image_url' => '',
                    'qr_image_path' => '',
                    'status' => self::STATUS_ENABLE,
                    'last_generate_status' => self::GENERATE_FAIL,
                    'last_generate_error' => '',
                    'generate_version' => 0,
                    'refresh_count' => 0,
                    'branch_name_snapshot' => $this->branchNameSnapshot($merId),
                    'scene_ext' => json_encode([
                        'scene_rule' => 'm{mer_id}.e{entry_code}',
                        'scene_rule_version' => 1,
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } catch (\Throwable $e) {
                if (!$this->isDuplicateKeyError($e)) {
                    throw $e;
                }
                if ($i === 4) {
                    throw new ValidateException('二维码入口码生成失败');
                }
            }
        }
        throw new ValidateException('二维码入口码生成失败');
    }

    protected function ensureRecordDefaults($record)
    {
        $data = $this->toArray($record);
        $entryCode = (string)($data['entry_code'] ?? '');
        if ($entryCode === '') {
            $entryCode = $this->uniqueEntryCode();
        }
        $scene = $this->sceneService->buildScene((int)$data['mer_id'], $entryCode);
        $updates = [];
        if (($data['entry_code'] ?? '') !== $entryCode) {
            $updates['entry_code'] = $entryCode;
        }
        if (($data['scene_value'] ?? '') !== $scene) {
            $updates['scene_value'] = $scene;
        }
        if (($data['scene_type'] ?? '') !== HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT) {
            $updates['scene_type'] = HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT;
        }
        if (($data['page_path'] ?? '') !== HuimaidanStoreQrcodeSceneService::PAGE_PATH) {
            $updates['page_path'] = HuimaidanStoreQrcodeSceneService::PAGE_PATH;
        }
        if (($data['branch_name_snapshot'] ?? '') === '') {
            $updates['branch_name_snapshot'] = $this->branchNameSnapshot((int)$data['mer_id']);
        }
        if ($updates) {
            $updates['updated_at'] = date('Y-m-d H:i:s');
            $this->dao->update((int)$data['id'], $updates);
            return $this->dao->get((int)$data['id']);
        }
        return $record;
    }

    protected function shouldGenerate($record): bool
    {
        $data = $this->toArray($record);
        if (empty($data['qr_image_url']) || empty($data['qr_image_path'])) {
            return true;
        }
        if (($data['page_path'] ?? '') !== HuimaidanStoreQrcodeSceneService::PAGE_PATH) {
            return true;
        }
        $expectedScene = $this->sceneService->buildScene((int)$data['mer_id'], (string)$data['entry_code']);
        if (($data['scene_value'] ?? '') !== $expectedScene) {
            return true;
        }
        return !$this->imageService->exists($data['qr_image_path']);
    }

    protected function generateRecord($record, array $merchant, bool $isRefresh, string $source, int $operatorId, string $reason): array
    {
        $data = $this->toArray($record);
        $now = date('Y-m-d H:i:s');
        $refreshCount = (int)($data['refresh_count'] ?? 0) + ($isRefresh ? 1 : 0);

        try {
            $entryCode = (string)$data['entry_code'];
            $scene = $this->sceneService->buildScene((int)$data['mer_id'], $entryCode);
            $page = $this->sceneService->normalizePagePath((string)($data['page_path'] ?: HuimaidanStoreQrcodeSceneService::PAGE_PATH));
            $binary = $this->wechatService->getUnlimited($scene, $page, [
                'check_path' => false,
                'env_version' => 'develop',
                'width' => 430,
            ]);
            $image = $this->imageService->save((int)$data['mer_id'], $binary);

            $this->dao->update((int)$data['id'], [
                'entry_code' => $entryCode,
                'scene_value' => $scene,
                'scene_type' => HuimaidanStoreQrcodeSceneService::SCENE_TYPE_PAYMENT_CHECKOUT,
                'page_path' => $page,
                'qr_image_url' => $image['url'],
                'qr_image_path' => $image['path'],
                'status' => self::STATUS_ENABLE,
                'last_generate_status' => self::GENERATE_SUCCESS,
                'last_generate_error' => '',
                'generate_version' => (int)($data['generate_version'] ?? 0) + 1,
                'refresh_count' => $refreshCount,
                'last_generated_at' => $now,
                'branch_name_snapshot' => $this->branchNameSnapshot((int)$data['mer_id']),
                'scene_ext' => json_encode([
                    'scene_rule' => 'm{mer_id}.e{entry_code}',
                    'scene_rule_version' => 1,
                    'source' => $source,
                    'operator_id' => $operatorId,
                    'reason' => $reason,
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            return $this->payload($this->dao->get((int)$data['id']), $merchant);
        } catch (\Throwable $e) {
            $message = mb_substr((string)$e->getMessage(), 0, 500);
            $this->dao->update((int)$data['id'], [
                'last_generate_status' => self::GENERATE_FAIL,
                'last_generate_error' => $message,
                'refresh_count' => $refreshCount,
                'updated_at' => $now,
            ]);
            Log::error('惠买单店铺二维码生成失败 mer_id=' . (int)$data['mer_id'] . ' error=' . $message);
            throw new ValidateException($message ?: '二维码生成失败');
        }
    }

    protected function assertMerchant(int $merId): array
    {
        if ($merId <= 0) {
            throw new ValidateException('商户ID无效');
        }
        $merchant = app()->make(MerchantRepository::class)->get($merId);
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        return $this->toArray($merchant);
    }

    protected function assertRefreshAllowed($record): void
    {
        $data = $this->toArray($record);
        if (!empty($data['last_generated_at']) && strtotime($data['last_generated_at']) > strtotime('-60 seconds')) {
            throw new ValidateException('刷新过于频繁，请稍后再试');
        }
    }

    protected function uniqueEntryCode(): string
    {
        for ($i = 0; $i < 10; $i++) {
            $entryCode = $this->sceneService->newEntryCode();
            if (!$this->dao->entryCodeExists($entryCode)) {
                return $entryCode;
            }
        }
        throw new ValidateException('二维码入口码生成失败');
    }

    protected function isDuplicateKeyError(\Throwable $e): bool
    {
        $message = $e->getMessage();
        return strpos($message, 'Duplicate entry') !== false
            || strpos($message, 'SQLSTATE[23000]') !== false;
    }

    protected function branchNameSnapshot(int $merId): string
    {
        $profile = $this->profileRepository->displayProfileByMerId($merId);
        return (string)($profile['branch_name'] ?? '');
    }

    protected function merchantNames(array $merIds): array
    {
        $merIds = array_values(array_filter(array_unique(array_map('intval', $merIds))));
        return $merIds ? Merchant::getDB()->whereIn('mer_id', $merIds)->column('mer_name', 'mer_id') : [];
    }

    protected function payload($record, array $merchant): array
    {
        $data = $this->toArray($record);
        $status = (int)($data['status'] ?? self::STATUS_DISABLE);
        $lastGenerateStatus = (int)($data['last_generate_status'] ?? self::GENERATE_FAIL);
        $hasImage = !empty($data['qr_image_url']);
        return [
            'id' => (int)($data['id'] ?? 0),
            'mer_id' => (int)($data['mer_id'] ?? 0),
            'mer_name' => (string)($merchant['mer_name'] ?? ''),
            'branch_name_snapshot' => (string)($data['branch_name_snapshot'] ?? ''),
            'entry_code' => (string)($data['entry_code'] ?? ''),
            'scene_value' => (string)($data['scene_value'] ?? ''),
            'scene_type' => (string)($data['scene_type'] ?? ''),
            'page_path' => (string)($data['page_path'] ?? ''),
            'status' => $status,
            'status_text' => $status === self::STATUS_ENABLE ? '可用' : '禁用',
            'qr_image_url' => $this->qrcodeImageUrl($data),
            'qr_image_path' => (string)($data['qr_image_path'] ?? ''),
            'last_generated_at' => (string)($data['last_generated_at'] ?? ''),
            'last_generate_status' => $lastGenerateStatus,
            'last_generate_status_text' => $lastGenerateStatus === self::GENERATE_SUCCESS ? '生成成功' : '生成失败',
            'last_generate_error' => (string)($data['last_generate_error'] ?? ''),
            'generate_version' => (int)($data['generate_version'] ?? 0),
            'refresh_count' => (int)($data['refresh_count'] ?? 0),
            'last_access_at' => (string)($data['last_access_at'] ?? ''),
            'is_using_last_success' => $lastGenerateStatus === self::GENERATE_FAIL && $hasImage ? 1 : 0,
            'updated_at' => (string)($data['updated_at'] ?? ''),
        ];
    }

    protected function qrcodeImageUrl(array $data): string
    {
        $path = (string)($data['qr_image_path'] ?? '');
        if ($path === '') {
            $url = (string)($data['qr_image_url'] ?? '');
            $parsedPath = parse_url($url, PHP_URL_PATH);
            $path = is_string($parsedPath) ? $parsedPath : '';
        }

        if ($path === '') {
            return (string)($data['qr_image_url'] ?? '');
        }

        // 临时操作：开发环境二维码图片强制走 dev 域名，避免已有记录继续返回 crmeb.local；上线前改回 site_url 配置。
        return HuimaidanStoreQrcodeImageService::temporaryDevelopUrl($path);
    }

    protected function toArray($value): array
    {
        if ($value instanceof Model) {
            return $value->toArray();
        }
        return is_array($value) ? $value : (array)$value;
    }
}

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

use app\common\dao\huimaidan\MerchantProfileDao;
use app\common\repositories\BaseRepository;
use think\exception\ValidateException;

/**
 * @mixin MerchantProfileDao
 */
class MerchantProfileRepository extends BaseRepository
{
    /**
     * @var AiTagRepository
     */
    protected $tagRepository;

    public function __construct(MerchantProfileDao $dao, AiTagRepository $tagRepository)
    {
        $this->dao = $dao;
        $this->tagRepository = $tagRepository;
    }

    public function formData(int $merId): array
    {
        $profile = $this->dao->getByMerId($merId);
        return $this->flattenFormData($profile ? $profile->toArray() : []);
    }

    public function saveByMerId(int $merId, array $data)
    {
        $payload = $this->normalizePayload($data);
        $payload['mer_id'] = $merId;
        $profile = $this->dao->getByMerId($merId);
        if ($profile) {
            $profile->save($payload);
            return $profile;
        }
        return $this->dao->create($payload);
    }

    public function displayProfiles(array $merIds): array
    {
        $merIds = array_values(array_filter(array_unique(array_map('intval', $merIds))));
        if (!$merIds) {
            return [];
        }
        $rows = $this->dao->search(['mer_ids' => $merIds])->select()->toArray();
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['mer_id']] = $this->displayProfile($row);
        }
        return $result;
    }

    public function displayProfileByMerId(int $merId): array
    {
        $profile = $this->dao->getByMerId($merId);
        return $this->displayProfile($profile ? $profile->toArray() : []);
    }

    public function displayProfile(array $profile): array
    {
        return [
            'branch_name' => (string)($profile['branch_name'] ?? ''),
            'configured_sales' => max(0, (int)($profile['configured_sales'] ?? 0)),
            'per_capita' => $this->money($profile['per_capita'] ?? '0.00'),
            'business_hours' => $this->decodeArray($profile['business_hours'] ?? []),
            'facilities' => $this->normalizeFacilities($profile['facilities'] ?? []),
            'promo_image' => (string)($profile['promo_image'] ?? ''),
            'slogan' => (string)($profile['slogan'] ?? ''),
        ];
    }

    protected function flattenFormData(array $profile): array
    {
        $display = $this->displayProfile($profile);
        $facilities = $display['facilities'];
        $data = [
            'branch_name' => $display['branch_name'],
            'configured_sales' => $display['configured_sales'],
            'per_capita' => $display['per_capita'],
            'business_hours' => $this->businessHoursText($display['business_hours']),
            'promo_image' => $display['promo_image'],
            'slogan' => $display['slogan'],
        ];
        foreach ($this->facilityTagMap() as $key => $label) {
            $data[$key] = (int)($facilities[$key] ?? 0);
        }
        return $data;
    }

    protected function normalizePayload(array $data): array
    {
        $branchName = trim((string)($data['branch_name'] ?? ''));
        $slogan = trim((string)($data['slogan'] ?? ''));
        $promoImage = trim((string)($data['promo_image'] ?? ''));

        if (mb_strlen($branchName) > 64) {
            throw new ValidateException('分店名不能超过64个字符');
        }
        if (mb_strlen($slogan) > 255) {
            throw new ValidateException('商户标语不能超过255个字符');
        }
        if (mb_strlen($promoImage) > 255) {
            throw new ValidateException('促销图地址不能超过255个字符');
        }
        if (!is_numeric($data['configured_sales'] ?? 0) || (int)$data['configured_sales'] < 0) {
            throw new ValidateException('配置销量不能小于0');
        }
        $perCapita = $data['per_capita'] ?? null;
        if (!is_null($perCapita) && (!is_numeric($perCapita) || (float)$perCapita < 0)) {
            throw new ValidateException('人均消费不能小于0');
        }

        return [
            'branch_name' => $branchName,
            'configured_sales' => (int)($data['configured_sales'] ?? 0),
            'per_capita' => $this->money($perCapita ?? '0.00'),
            'business_hours' => json_encode($this->normalizeBusinessHours($data['business_hours'] ?? []), JSON_UNESCAPED_UNICODE),
            'facilities' => json_encode($this->normalizeFacilities($data['facilities'] ?? $data), JSON_UNESCAPED_UNICODE),
            'promo_image' => $promoImage,
            'slogan' => $slogan,
            'update_time' => date('Y-m-d H:i:s'),
        ];
    }

    protected function normalizeBusinessHours($value): array
    {
        if ($value === '' || $value === null) {
            return [];
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return [];
            }
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }
            if (in_array(substr($value, 0, 1), ['[', '{'], true)) {
                throw new ValidateException('营业时间格式错误');
            }
            return array_values(array_map(function ($line) {
                return ['day' => '', 'time' => trim($line)];
            }, array_filter(preg_split('/\r\n|\r|\n/', $value), function ($line) {
                return trim($line) !== '';
            })));
        }
        if (!is_array($value)) {
            throw new ValidateException('营业时间格式错误');
        }
        return array_values($value);
    }

    protected function normalizeFacilities($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $text = $value;
                $value = [];
                foreach ($this->facilityTagMap() as $key => $label) {
                    if (
                        ($label !== '' && mb_strpos($text, $label) !== false)
                        || ($key !== '' && mb_strpos($text, $key) !== false)
                    ) {
                        $value[$key] = 1;
                    }
                }
            }
        }
        if (!is_array($value)) {
            $value = [];
        }
        $result = [];
        foreach ($this->facilityTagMap() as $key => $label) {
            $result[$key] = !empty($value[$key]);
        }
        return $result;
    }

    /**
     * 从 AI 标签字典读取启用的设施标签映射（key => label）。
     * 字典读取失败时返回硬编码兜底，避免发版期间标签消失。
     */
    protected function facilityTagMap(): array
    {
        try {
            $tags = $this->tagRepository->search(['tag_type' => 'facility', 'status' => 1])->select()->toArray();
        } catch (\Throwable $e) {
            $tags = [];
        }
        $map = [];
        foreach ($tags as $tag) {
            $value = (string)($tag['tag_value'] ?? '');
            $label = (string)($tag['tag_label'] ?? '');
            if ($value !== '' && $label !== '') {
                $map[$value] = $label;
            }
        }
        return $map ?: $this->fallbackFacilityLabels();
    }

    protected function fallbackFacilityLabels(): array
    {
        return [
            'has_large_table' => '大桌',
            'has_baby_chair' => '宝宝椅',
            'has_private_room' => '包间',
            'can_phone_reserve' => '电话预订',
            'is_non_smoking' => '无烟餐厅',
        ];
    }

    protected function decodeArray($value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    protected function businessHoursText(array $hours): string
    {
        $lines = [];
        foreach ($hours as $item) {
            if (is_array($item)) {
                $day = trim((string)($item['day'] ?? ''));
                $time = trim((string)($item['time'] ?? ''));
                $lines[] = trim($day . ' ' . $time);
            } else {
                $lines[] = trim((string)$item);
            }
        }
        return implode("\n", array_filter($lines, function ($line) {
            return $line !== '';
        }));
    }

    protected function money($amount): string
    {
        return number_format(round((float)$amount, 2), 2, '.', '');
    }
}

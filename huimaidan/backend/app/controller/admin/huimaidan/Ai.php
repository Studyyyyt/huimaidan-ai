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

namespace app\controller\admin\huimaidan;

use app\common\repositories\huimaidan\AiBannerConfigRepository;
use app\common\repositories\huimaidan\AiConfigRepository;
use app\common\repositories\huimaidan\AiMerchantHealthRepository;
use app\common\repositories\huimaidan\AiNluRepository;
use app\common\repositories\huimaidan\AiRecommendLogRepository;
use app\common\repositories\huimaidan\AiRecommendRepository;
use app\common\repositories\huimaidan\AiRerankRepository;
use app\common\repositories\huimaidan\AiTagRepository;
use app\common\repositories\huimaidan\MerchantImportRepository;
use app\common\repositories\huimaidan\MerchantDiscountRepository;
use app\common\repositories\huimaidan\MerchantProfileRepository;
use app\common\repositories\huimaidan\MerchantTagInitializerRepository;
use app\common\repositories\huimaidan\MerchantTagRepository;
use app\common\model\system\merchant\Merchant;
use app\common\model\store\CityArea;
use crmeb\basic\BaseController;
use crmeb\services\ai\LlmClientService;
use think\App;
use think\exception\ValidateException;
use think\facade\Cache;

class Ai extends BaseController
{
    protected $tagRepository;
    protected $merchantTagRepository;
    protected $bannerRepository;
    protected $configRepository;
    protected $logRepository;
    protected $initializerRepository;
    protected $merchantImportRepository;
    protected $merchantProfileRepository;
    protected $healthRepository;
    protected $llmClient;
    protected $nluRepository;
    protected $recommendRepository;
    protected $rerankRepository;

    public function __construct(
        App $app,
        AiTagRepository $tagRepository,
        MerchantTagRepository $merchantTagRepository,
        AiBannerConfigRepository $bannerRepository,
        AiConfigRepository $configRepository,
        AiRecommendLogRepository $logRepository,
        MerchantTagInitializerRepository $initializerRepository,
        MerchantImportRepository $merchantImportRepository,
        MerchantProfileRepository $merchantProfileRepository,
        AiMerchantHealthRepository $healthRepository,
        LlmClientService $llmClient,
        AiNluRepository $nluRepository,
        AiRecommendRepository $recommendRepository,
        AiRerankRepository $rerankRepository
    ) {
        parent::__construct($app);
        $this->tagRepository = $tagRepository;
        $this->merchantTagRepository = $merchantTagRepository;
        $this->bannerRepository = $bannerRepository;
        $this->configRepository = $configRepository;
        $this->logRepository = $logRepository;
        $this->initializerRepository = $initializerRepository;
        $this->merchantImportRepository = $merchantImportRepository;
        $this->merchantProfileRepository = $merchantProfileRepository;
        $this->healthRepository = $healthRepository;
        $this->llmClient = $llmClient;
        $this->nluRepository = $nluRepository;
        $this->recommendRepository = $recommendRepository;
        $this->rerankRepository = $rerankRepository;
    }

    public function tags()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['tag_type', 'keyword', 'status']);
        $query = $this->tagRepository->search($where);
        return app('json')->success([
            'count' => $query->count(),
            'list' => $query->page($page, $limit)->select()->toArray(),
        ]);
    }

    public function saveTag()
    {
        $data = $this->request->params(['tag_id', 'tag_type', 'tag_value', 'tag_label', ['synonyms', []], ['tag_weight', 10], ['sort', 0], ['status', 1]]);
        $payload = $this->tagPayload($data);
        if (!empty($data['tag_id'])) {
            $this->tagRepository->update((int)$data['tag_id'], $payload);
        } else {
            $this->tagRepository->create($payload);
        }
        return app('json')->success('保存成功');
    }

    public function deleteTag($id)
    {
        $this->tagRepository->delete((int)$id);
        return app('json')->success('删除成功');
    }

    public function importTags()
    {
        $tags = $this->request->param('tags', []);
        if (!is_array($tags)) {
            throw new ValidateException('导入标签格式错误');
        }
        $count = 0;
        foreach ($tags as $tag) {
            if (!is_array($tag)) {
                continue;
            }
            $payload = $this->tagPayload($tag);
            $exists = $this->tagRepository->search([
                'tag_type' => $payload['tag_type'],
                'tag_value' => $payload['tag_value'],
            ])->find();
            if ($exists) {
                $this->tagRepository->update((int)$exists->tag_id, $payload);
            } else {
                $this->tagRepository->create($payload);
            }
            $count++;
        }
        return app('json')->success(['count' => $count]);
    }

    public function merchants()
    {
        $keyword = trim((string)$this->request->param('keyword', ''));
        $limit = max(1, min(200, (int)$this->request->param('limit', 100)));
        $query = Merchant::getDB()
            ->where('is_del', 0)
            ->where('status', 1);
        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword) {
                $query->whereLike('mer_name|real_name|mer_phone|service_phone', '%' . $keyword . '%');
                if (ctype_digit($keyword)) {
                    $query->whereOr('mer_id', (int)$keyword);
                }
            });
        }

        $rows = $query
            ->field('mer_id,mer_name,real_name,mer_address,mer_phone,service_phone,city_id,category_id,long,lat,sales,status,mer_state,mer_avatar')
            ->order('mer_id DESC')
            ->limit($limit)
            ->select()
            ->toArray();

        $merIds = array_column($rows, 'mer_id');
        $healthList = $this->healthRepository->checkMerchants($merIds, 200)['list'] ?? [];
        $healthIndex = [];
        foreach ($healthList as $health) {
            $healthIndex[(int)$health['mer_id']] = $health;
        }

        foreach ($rows as &$row) {
            $row['display_name'] = ($row['mer_name'] ?: $row['real_name']) . '（ID:' . $row['mer_id'] . '）';
            $row['display_phone'] = $row['service_phone'] ?: $row['mer_phone'];
            $row['coordinate'] = trim((string)$row['long']) !== '' && trim((string)$row['lat']) !== ''
                ? $row['long'] . ', ' . $row['lat']
                : '';
            $stateLabels = [];
            $stateLabels[] = (int)($row['status'] ?? 0) === 1 ? '店铺启用' : '店铺关闭';
            $stateLabels[] = (int)($row['mer_state'] ?? 0) === 1 ? '营业中' : '未营业';
            if ((int)($row['city_id'] ?? 0) <= 0) {
                $stateLabels[] = '缺城市';
            }
            if ($row['coordinate'] === '') {
                $stateLabels[] = '缺坐标';
            }
            if (trim((string)($row['mer_avatar'] ?? '')) === '') {
                $stateLabels[] = '缺头像';
            }
            $row['status_label'] = implode(' / ', $stateLabels);
            $row['is_recommendable_base'] = (int)($row['status'] ?? 0) === 1
                && (int)($row['mer_state'] ?? 0) === 1
                && (int)($row['city_id'] ?? 0) > 0
                && $row['coordinate'] !== '';

            $health = $healthIndex[(int)$row['mer_id']] ?? null;
            $row['ai_health_status'] = $health['status'] ?? 'needs_improvement';
            $row['ai_health_status_text'] = $health['status_text'] ?? '待完善';
            $row['ai_health_score'] = $health['score'] ?? 0;
            $row['ai_health_missing'] = $this->mapMissingItems($health['missing_items'] ?? []);
        }

        return app('json')->success($rows);
    }

    /**
     * 将健康检查缺失项映射为前端可操作的状态标签。
     */
    protected function mapMissingItems(array $missingItems): array
    {
        $map = [
            '店铺未启用' => ['key' => 'disabled', 'label' => '店铺未启用', 'action' => ''],
            '未营业' => ['key' => 'closed', 'label' => '未营业', 'action' => ''],
            '头像' => ['key' => 'avatar', 'label' => '缺头像', 'action' => 'avatar'],
            '地址' => ['key' => 'address', 'label' => '缺地址', 'action' => 'coordinate'],
            '经纬度' => ['key' => 'coordinate', 'label' => '缺坐标', 'action' => 'coordinate'],
            '城市' => ['key' => 'city', 'label' => '缺城市', 'action' => ''],
            '人均消费' => ['key' => 'per_capita', 'label' => '缺人均', 'action' => 'per_capita'],
            '营业时间' => ['key' => 'business_hours', 'label' => '缺营业时间', 'action' => 'business_hours'],
            '设施' => ['key' => 'facility', 'label' => '缺设施', 'action' => 'facility'],
            '分类' => ['key' => 'category', 'label' => '缺分类', 'action' => ''],
            'AI标签' => ['key' => 'tags', 'label' => '缺标签', 'action' => 'tags'],
            '优惠规则' => ['key' => 'discount', 'label' => '缺优惠', 'action' => 'discount'],
        ];
        $result = [];
        foreach ($missingItems as $item) {
            if (isset($map[$item])) {
                $result[] = $map[$item];
            }
        }
        return $result;
    }

    public function merchantProfile($merId)
    {
        $merchant = Merchant::getDB()
            ->where('mer_id', (int)$merId)
            ->where('is_del', 0)
            ->find();
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        $data = $this->merchantProfileRepository->formData((int)$merId);
        $data['mer_id'] = (int)$merchant->mer_id;
        $data['mer_name'] = (string)($merchant->mer_name ?: $merchant->real_name);
        $data['mer_avatar'] = (string)($merchant->mer_avatar ?? '');
        $data['mer_banner'] = (string)($merchant->mer_banner ?? '');
        $data['mer_info'] = (string)($merchant->mer_info ?? '');
        $data['real_sales'] = max(0, (int)($merchant->sales ?? 0));
        $data['display_sales_total'] = $data['real_sales'] + max(0, (int)($data['configured_sales'] ?? 0));
        $data['mer_phone'] = (string)($merchant->service_phone ?: $merchant->mer_phone);
        $data['mer_address'] = (string)$merchant->mer_address;
        $data['longitude'] = (string)($merchant->long ?? '');
        $data['latitude'] = (string)($merchant->lat ?? '');
        $rule = app()->make(MerchantDiscountRepository::class)->displayDiscounts([(int)$merId], 0);
        $data['discount_label'] = $this->discountLabel($rule[(int)$merId] ?? null);
        return app('json')->success($data);
    }

    public function saveMerchantProfile($merId)
    {
        $merchant = Merchant::getDB()
            ->where('mer_id', (int)$merId)
            ->where('is_del', 0)
            ->find();
        if (!$merchant) {
            throw new ValidateException('商户不存在');
        }
        $data = $this->request->params([
            'branch_name',
            ['configured_sales', 0],
            ['per_capita', 0],
            'business_hours',
            'promo_image',
            'slogan',
            ['has_large_table', 0],
            ['has_baby_chair', 0],
            ['has_private_room', 0],
            ['can_phone_reserve', 0],
            ['is_non_smoking', 0],
            'longitude',
            'latitude',
            'mer_avatar',
            'mer_banner',
            'mer_info',
        ]);

        $longitude = trim((string)($data['longitude'] ?? ''));
        $latitude = trim((string)($data['latitude'] ?? ''));
        $merchantPayload = [];
        if ($longitude !== '' || $latitude !== '') {
            if (!is_numeric($longitude) || (float)$longitude < -180 || (float)$longitude > 180) {
                throw new ValidateException('经度格式错误，应为 -180 到 180 之间的数值');
            }
            if (!is_numeric($latitude) || (float)$latitude < -90 || (float)$latitude > 90) {
                throw new ValidateException('纬度格式错误，应为 -90 到 90 之间的数值');
            }
            $merchantPayload['long'] = $longitude;
            $merchantPayload['lat'] = $latitude;
        }
        foreach (['mer_avatar' => '推荐头像', 'mer_banner' => '门店Banner'] as $field => $label) {
            $value = trim((string)($data[$field] ?? ''));
            if (mb_strlen($value) > 255) {
                throw new ValidateException($label . '地址不能超过255个字符');
            }
            if ($value !== '') {
                $merchantPayload[$field] = $value;
            }
        }
        $merInfo = trim((string)($data['mer_info'] ?? ''));
        if (mb_strlen($merInfo) > 1000) {
            throw new ValidateException('详情页商户介绍不能超过1000个字符');
        }
        $merchantPayload['mer_info'] = $merInfo;
        $merchantPayload['status'] = 1;
        $merchantPayload['mer_state'] = 1;
        if ((int)($merchant->city_id ?? 0) <= 0) {
            $cityId = $this->inferCityIdFromAddress((string)($merchant->mer_address ?? ''));
            if ($cityId > 0) {
                $merchantPayload['city_id'] = $cityId;
            }
        }
        if ($merchantPayload) {
            Merchant::getDB()->where('mer_id', (int)$merId)->update($merchantPayload);
        }

        $this->merchantProfileRepository->saveByMerId((int)$merId, $data);
        return app('json')->success('保存成功');
    }

    protected function discountLabel(?array $rule): string
    {
        if (!$rule) {
            return '';
        }
        if (!empty($rule['discount_label'])) {
            return (string)$rule['discount_label'];
        }
        if (isset($rule['member_discount'])) {
            $rate = bcmul((string)$rule['member_discount'], '10', 2);
            return rtrim(rtrim($rate, '0'), '.') . '折';
        }
        return '已配置优惠';
    }

    public function deleteMerchant($merId)
    {
        $merchant = Merchant::getDB()
            ->where('mer_id', (int)$merId)
            ->where('is_del', 0)
            ->find();
        if (!$merchant) {
            throw new ValidateException('商户不存在或已删除');
        }
        Merchant::getDB()->where('mer_id', (int)$merId)->update([
            'status' => 0,
            'mer_state' => 0,
            'is_del' => 1,
        ]);
        return app('json')->success('删除成功');
    }

    public function merchantTags($merId)
    {
        return app('json')->success($this->merchantTagRepository->search(['mer_id' => (int)$merId])->select()->toArray());
    }

    public function saveMerchantTags($merId)
    {
        $tags = $this->request->param('tags', []);
        if (!is_array($tags)) {
            throw new ValidateException('标签格式错误');
        }
        $this->merchantTagRepository->replaceManualTags((int)$merId, $tags);
        return app('json')->success('保存成功');
    }

    public function initMerchantTags()
    {
        $merId = (int)$this->request->param('mer_id', 0);
        return app('json')->success($this->initializerRepository->initialize($merId));
    }

    public function suggestMerchantTags($merId)
    {
        return app('json')->success($this->initializerRepository->suggest((int)$merId));
    }

    public function banners()
    {
        return app('json')->success($this->bannerRepository->search([])->select()->toArray());
    }

    public function saveBanner()
    {
        $data = $this->request->params(['config_id', 'meal_type', 'title_template', 'subtitle_template', 'bg_color', 'text_color', ['sort', 0], ['is_enabled', 1]]);
        foreach (['meal_type', 'title_template', 'subtitle_template'] as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                throw new ValidateException('请填写完整Banner配置');
            }
        }
        $payload = [
            'meal_type' => trim((string)$data['meal_type']),
            'title_template' => trim((string)$data['title_template']),
            'subtitle_template' => trim((string)$data['subtitle_template']),
            'bg_color' => trim((string)$data['bg_color']) ?: '#FFF3E0',
            'text_color' => trim((string)$data['text_color']) ?: '#E65100',
            'sort' => (int)$data['sort'],
            'is_enabled' => (int)$data['is_enabled'],
        ];
        if (!empty($data['config_id'])) {
            $this->bannerRepository->update((int)$data['config_id'], $payload);
        } else {
            $this->bannerRepository->create($payload);
        }
        return app('json')->success('保存成功');
    }

    public function deleteBanner($id)
    {
        $this->bannerRepository->delete((int)$id);
        return app('json')->success('删除成功');
    }

    public function configs()
    {
        $showSecrets = (int)$this->request->param('show_secrets', 1) === 1;
        $rows = $this->configRepository->search([])->select()->toArray();
        foreach ($rows as &$row) {
            $key = (string)($row['config_key'] ?? '');
            $row['is_secret'] = substr($key, -8) === '_api_key' ? 1 : 0;
            if ($row['is_secret'] && !$showSecrets) {
                $row['config_value'] = trim((string)($row['config_value'] ?? '')) !== '' ? '******' : '';
            }
        }
        return app('json')->success($rows);
    }

    public function saveConfig()
    {
        $data = $this->request->params(['config_id', 'config_key', 'config_value', 'config_desc', ['sort', 0]]);
        if (trim((string)($data['config_key'] ?? '')) === '') {
            throw new ValidateException('配置键不能为空');
        }
        $payload = [
            'config_key' => trim((string)$data['config_key']),
            'config_value' => trim((string)($data['config_value'] ?? '')),
            'config_desc' => trim((string)($data['config_desc'] ?? '')),
            'sort' => (int)$data['sort'],
        ];
        if (substr($payload['config_key'], -8) === '_api_key' && $payload['config_value'] === '******') {
            return app('json')->success('保存成功');
        }
        if (!empty($data['config_id'])) {
            $this->configRepository->update((int)$data['config_id'], $payload);
        } else {
            $this->configRepository->create($payload);
        }
        return app('json')->success('保存成功');
    }

    public function deleteConfig($id)
    {
        $this->configRepository->delete((int)$id);
        return app('json')->success('删除成功');
    }

    public function logs()
    {
        [$page, $limit] = $this->getPage();
        $where = $this->request->params(['uid', 'session_id', 'keyword', 'degraded']);
        $query = $this->logRepository->search($where);
        return app('json')->success([
            'count' => $query->count(),
            'list' => $query->page($page, $limit)->select()->toArray(),
        ]);
    }

    /**
     * 推荐日志近 24 小时聚合统计
     */
    public function logsSummary()
    {
        $where = $this->request->params(['uid', 'session_id', 'keyword', 'degraded']);
        $where['date'] = [date('Y-m-d H:i:s', strtotime('-24 hours')), date('Y-m-d H:i:s')];
        return app('json')->success($this->logRepository->summary($where));
    }

    public function merchantImportTemplate()
    {
        $file = $this->merchantImportRepository->templateFile();
        return download($file['absolute_path'], $file['file_name']);
    }

    public function merchantImport()
    {
        $file = $this->request->file('file');
        if (!$file) {
            throw new ValidateException('请上传Excel文件');
        }
        $file = is_array($file) ? $file[0] : $file;
        validate(["file|文件" => ['fileExt' => 'xlsx,xls']])->check(['file' => $file]);
        return app('json')->success($this->merchantImportRepository->importFile($file->getPathname()));
    }

    /**
     * 测试大模型连接
     */
    public function testConnection()
    {
        try {
            $payload = $this->request->params([
                'llm_driver',
                'bailian_mode',
                'bailian_app_id',
                'bailian_api_key',
                'bailian_app_api_url',
                'bailian_model',
                'bailian_compatible_api_url',
                'deepseek_api_key',
                'deepseek_api_url',
                'deepseek_model',
                'claude_api_key',
                'claude_api_url',
                'claude_model',
            ]);
            $result = $this->runModelConnectionTest($payload);
            return app('json')->success($result);
        } catch (\Throwable $e) {
            return app('json')->fail('模型连接失败：' . $e->getMessage());
        }
    }

    /**
     * 重置 LLM 熔断状态
     */
    public function resetCircuitBreaker()
    {
        $failKey = (string)config('huimaidan.ai.circuit_breaker.fail_key', 'ai:llm:fails');
        $openKey = (string)config('huimaidan.ai.circuit_breaker.open_key', 'ai:llm:open_until');
        Cache::delete($failKey);
        Cache::delete($openKey);
        return app('json')->success('AI 服务熔断状态已清除');
    }

    /**
     * 执行模型连接测试并校验返回内容。
     */
    protected function runModelConnectionTest(array $overrides = []): array
    {
        $driver = $this->overrideText($overrides, 'llm_driver', $this->configRepository->text('llm_driver', (string)config('huimaidan.ai.llm_driver', 'bailian')));
        $bailianMode = $this->overrideText($overrides, 'bailian_mode', $this->configRepository->text('bailian_mode', 'app'));
        $this->assertLlmConfigured($driver, $bailianMode, $overrides);

        $prompt = '请只回复一个字：喵';
        $response = $this->llmClient->completion($prompt, '', [], '', null, null, $overrides);
        $text = trim((string)($response['text'] ?? ''));
        if ($text === '') {
            throw new ValidateException('模型连接成功，但返回内容为空');
        }
        if (mb_strpos($text, '喵') === false) {
            $display = $text;
            if (mb_strlen($display) > 1000) {
                $display = mb_substr($display, 0, 1000) . '...（已截断）';
            }
            throw new ValidateException('模型返回内容异常：' . $display);
        }
        return [
            'connected' => true,
            'sample' => mb_substr($text, 0, 50),
            'driver' => $driver,
            'mode' => $driver === 'bailian' ? $bailianMode : '',
        ];
    }

    /**
     * 校验当前驱动必填配置是否已填写。
     */
    protected function assertLlmConfigured(string $driver, string $bailianMode, array $overrides = []): void
    {
        if ($driver === 'bailian') {
            if ($bailianMode === 'compatible') {
                $apiKey = $this->overrideText($overrides, 'bailian_api_key', $this->configRepository->text('bailian_api_key', ''));
                $model = $this->overrideText($overrides, 'bailian_model', $this->configRepository->text('bailian_model', ''));
                $apiUrl = $this->overrideText($overrides, 'bailian_compatible_api_url', $this->configRepository->text('bailian_compatible_api_url', $this->configRepository->text('bailian_api_url', '')));
                if ($apiKey === '') {
                    throw new ValidateException('百炼 API Key 未配置');
                }
                if ($model === '') {
                    throw new ValidateException('百炼兼容模式模型未配置');
                }
                if ($apiUrl === '') {
                    throw new ValidateException('百炼兼容模式 API 地址未配置');
                }
            } else {
                $appId = $this->overrideText($overrides, 'bailian_app_id', $this->configRepository->text('bailian_app_id', ''));
                $apiKey = $this->overrideText($overrides, 'bailian_api_key', $this->configRepository->text('bailian_api_key', ''));
                if ($appId === '') {
                    throw new ValidateException('百炼应用 ID 未配置');
                }
                if ($apiKey === '') {
                    throw new ValidateException('百炼 API Key 未配置');
                }
            }
            return;
        }

        if ($driver === 'deepseek') {
            $apiKey = $this->overrideText($overrides, 'deepseek_api_key', $this->configRepository->text('deepseek_api_key', ''));
            $apiUrl = $this->overrideText($overrides, 'deepseek_api_url', $this->configRepository->text('deepseek_api_url', ''));
            $model = $this->overrideText($overrides, 'deepseek_model', $this->configRepository->text('deepseek_model', ''));
            if ($apiKey === '') {
                throw new ValidateException('DeepSeek API Key 未配置');
            }
            if ($apiUrl === '') {
                throw new ValidateException('DeepSeek API 地址未配置');
            }
            if ($model === '') {
                throw new ValidateException('DeepSeek 模型未配置');
            }
            return;
        }

        if ($driver === 'claude') {
            $apiKey = $this->overrideText($overrides, 'claude_api_key', $this->configRepository->text('claude_api_key', ''));
            $apiUrl = $this->overrideText($overrides, 'claude_api_url', $this->configRepository->text('claude_api_url', ''));
            $model = $this->overrideText($overrides, 'claude_model', $this->configRepository->text('claude_model', ''));
            if ($apiKey === '') {
                throw new ValidateException('Claude API Key 未配置');
            }
            if ($apiUrl === '') {
                throw new ValidateException('Claude API 地址未配置');
            }
            if ($model === '') {
                throw new ValidateException('Claude 模型未配置');
            }
        }
    }

    protected function overrideText(array $overrides, string $key, string $default = ''): string
    {
        $value = trim((string)($overrides[$key] ?? ''));
        if ($value === '' || $value === '******') {
            return $default;
        }
        return $value;
    }

    /**
     * 单个商户 AI 资料健康检查
     */
    public function merchantHealth($merId)
    {
        return app('json')->success($this->healthRepository->checkMerchant((int)$merId));
    }

    /**
     * 商户 AI 资料健康检查列表
     */
    public function merchantHealthList()
    {
        [$page, $limit] = $this->getPage();
        $minScore = $this->request->param('min_score');
        $minScore = $minScore === '' || $minScore === null ? null : (int)$minScore;
        $merIds = $this->request->param('mer_ids', []);
        if (is_string($merIds)) {
            $merIds = array_values(array_filter(array_map('intval', explode(',', $merIds))));
        }
        $mode = (string)$this->request->param('mode', 'needs_improvement');
        if ($merIds) {
            $data = $this->healthRepository->checkMerchants($merIds, 200);
        } elseif ($mode !== 'needs_improvement') {
            $data = $this->healthRepository->listMerchants($page, $limit, $mode, $minScore);
        } else {
            $data = $this->healthRepository->needImprovement($page, $limit, $minScore);
        }
        return app('json')->success($data);
    }

    /**
     * AI 推荐模拟测试：展示规则召回、LLM排序和最终结果。
     */
    public function simulate()
    {
        $message = trim((string)$this->request->param('message', ''));
        if ($message === '') {
            throw new ValidateException('请输入模拟测试问题');
        }
        $location = [
            'latitude' => (float)$this->request->param('latitude', 40.800861),
            'longitude' => (float)$this->request->param('longitude', 111.690894),
            'city_name' => (string)$this->request->param('city_name', '呼和浩特'),
        ];
        $cityId = (int)$this->request->param('city_id', 0);
        if ($cityId <= 0) {
            $cityId = $this->resolveCityIdForSimulate($location['city_name']);
        }
        if ($cityId > 0) {
            $location['city_id'] = $cityId;
        }

        $nlu = $this->nluRepository->parse($message, []);
        $intent = (array)($nlu['intent_tags'] ?? []);
        $recommend = $this->recommendRepository->recommend($intent, $location, 0);
        $ruleRanked = $recommend['candidates'] ?? $recommend['list'] ?? [];
        $rerank = $this->rerankRepository->rerank($message, $intent, $ruleRanked, []);
        $limit = $this->rerankRepository->resultLimit();
        $final = !$rerank['degraded'] && !empty($rerank['sorted_mer_ids'])
            ? $this->rerankRepository->applyRerank($ruleRanked, $rerank, $limit)
            : array_slice($ruleRanked, 0, max(1, $limit));

        return app('json')->success([
            'message' => $message,
            'intent_tags' => $intent,
            'recall_count' => (int)($recommend['count'] ?? count($ruleRanked)),
            'candidate_mer_ids_before' => array_values(array_map('intval', array_column($ruleRanked, 'mer_id'))),
            'candidate_mer_ids_after' => array_values(array_map('intval', (array)($rerank['sorted_mer_ids'] ?? []))),
            'rerank_degraded' => !empty($rerank['degraded']),
            'rerank_error' => (string)($rerank['error_message'] ?? ''),
            'summary' => (string)($rerank['summary'] ?? ''),
            'rule_candidates' => array_slice($ruleRanked, 0, 10),
            'final_merchants' => $final,
            'resolved_city_id' => $location['city_id'] ?? 0,
        ]);
    }

    /**
     * 模拟测试时根据城市名称解析 city_id，与真实对话保持一致。
     */
    protected function resolveCityIdForSimulate(string $cityName): int
    {
        $cityName = trim($cityName);
        if ($cityName === '') {
            return 0;
        }
        $normalized = preg_replace('/市$/u', '', $cityName);
        try {
            $query = CityArea::getDB()->where('level', 2);
            $query->where(function ($query) use ($cityName, $normalized) {
                $query->where('name', $cityName);
                if ($normalized !== $cityName && $normalized !== '') {
                    $query->whereOr('name', $normalized);
                    $query->whereOr('name', $normalized . '市');
                }
            });
            return (int)$query->value('id');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * AI 推荐整体上线检查
     */
    public function aiStatus()
    {
        $status = 'normal';
        $checks = [];
        $suggestions = [];
        $merId = (int)$this->request->param('mer_id', 0);

        // 1. 模型连接状态
        $connected = false;
        $modelError = '';
        try {
            $this->runModelConnectionTest();
            $connected = true;
        } catch (\Throwable $e) {
            $modelError = $e->getMessage();
        }
        $checks[] = [
            'name' => '模型连接',
            'passed' => $connected,
            'message' => $connected ? '模型连接正常' : '模型连接异常：' . $modelError,
        ];
        if (!$connected) {
            $status = 'model_error';
            $suggestions[] = '请到“模型接入”步骤检查 API Key、模型地址和应用 ID。';
        }

        // 2. 商户数据完整度
        if ($merId > 0) {
            $singleHealth = $this->healthRepository->checkMerchant($merId);
            $healthPassed = ($singleHealth['status'] ?? '') !== 'needs_improvement';
            $checks[] = [
                'name' => '商户资料完整度',
                'passed' => $healthPassed,
                'message' => '商户资料评分 ' . (int)($singleHealth['score'] ?? 0) . ' 分，状态：' . (string)($singleHealth['status_text'] ?? '待完善'),
                'detail' => $singleHealth,
            ];
            if (!$healthPassed) {
                foreach ((array)($singleHealth['suggestions'] ?? []) as $suggestion) {
                    $suggestions[] = (string)$suggestion;
                }
            }
        } else {
            $health = $this->healthRepository->listMerchants(1, 1000, 'needs_improvement');
            $needsImprovementCount = count($health['list']);
            $merchantCount = max(1, (int)$health['count']);
            $healthRate = round(($merchantCount - $needsImprovementCount) / $merchantCount * 100, 2);
            $healthPassed = $healthRate >= 70;
            $checks[] = [
                'name' => '商户资料完整度',
                'passed' => $healthPassed,
                'message' => "商户资料完整率 {$healthRate}%（{$needsImprovementCount}/{$merchantCount} 家待完善）",
            ];
        }
        if (!$healthPassed) {
            $status = $status === 'normal' ? 'merchant_insufficient' : $status;
            if ($merId <= 0) {
                $suggestions[] = '请到“商户资料健康检查”补充缺失字段。';
            }
        }

        // 3. 优惠规则状态
        $discountRepository = app()->make(\app\common\repositories\huimaidan\MerchantDiscountRepository::class);
        $discountMerchantIds = $discountRepository->eligibleMerchantIds();
        $discountCount = count($discountMerchantIds);
        $discountPassed = $merId > 0 ? in_array($merId, $discountMerchantIds, true) : $discountCount > 0;
        $checks[] = [
            'name' => '优惠规则',
            'passed' => $discountPassed,
            'message' => $merId > 0
                ? ($discountPassed ? '当前商户已配置启用中的会员消费折扣' : '当前商户暂无启用中的会员消费折扣')
                : ($discountPassed ? "已有 {$discountCount} 家商户配置优惠" : '暂无启用中的优惠规则'),
        ];
        if (!$discountPassed) {
            $status = $status === 'normal' ? 'config_missing' : $status;
            $suggestions[] = $merId > 0
                ? '请到“惠买单优惠规则”给当前商户配置并启用会员消费折扣。'
                : '请到“惠买单优惠规则”配置至少一家商户的会员消费折扣。';
        }

        // 4. 标签覆盖率
        $tagCount = $this->tagRepository->enabledTags();
        $tagPassed = count($tagCount) >= 5;
        $checks[] = [
            'name' => 'AI标签覆盖',
            'passed' => $tagPassed,
            'message' => $tagPassed ? '已配置 ' . count($tagCount) . ' 个 AI 标签' : 'AI 标签数量较少，建议至少配置 5 个',
        ];

        // 5. 最近推荐日志
        $recentLog = $this->logRepository->search([])->limit(1)->find();
        $checks[] = [
            'name' => '推荐日志',
            'passed' => !empty($recentLog),
            'message' => !empty($recentLog) ? '存在推荐日志记录' : '暂无推荐日志，建议联调小程序后观察',
        ];

        // 6. 关键配置项
        $requiredConfigs = ['llm_driver', 'llm_rerank_enabled', 'llm_rerank_candidate_limit'];
        $missingConfigs = [];
        $allConfigs = $this->configRepository->allKeyValue();
        foreach ($requiredConfigs as $key) {
            if (!isset($allConfigs[$key]) || trim((string)$allConfigs[$key]) === '') {
                $missingConfigs[] = $key;
            }
        }
        $configPassed = !$missingConfigs;
        $checks[] = [
            'name' => '关键配置项',
            'passed' => $configPassed,
            'message' => $configPassed ? '关键配置项已配置' : '缺少配置：' . implode('、', $missingConfigs),
        ];
        if (!$configPassed) {
            $status = $status === 'normal' ? 'config_missing' : $status;
            $suggestions[] = '请到“推荐策略”步骤完成 LLM 动态排序配置。';
        }

        $statusMap = [
            'normal' => '正常',
            'model_error' => '模型异常',
            'merchant_insufficient' => '商户资料不足',
            'config_missing' => '部分配置缺失',
        ];

        return app('json')->success([
            'status' => $status,
            'status_text' => $statusMap[$status] ?? '未知',
            'can_go_live' => $connected && $healthPassed && $discountPassed && $configPassed,
            'checks' => $checks,
            'suggestions' => $suggestions,
        ]);
    }

    public function onboardingConfig()
    {
        $config = $this->loadOnboardingConfig();
        return app('json')->success($config);
    }

    public function saveOnboardingConfig()
    {
        $data = $this->request->params([
            ['enabled', 1],
            'title',
            'home_subtitle',
            'home_search_placeholder',
            'home_featured_subtitle',
            'chat_welcome_text',
            ['features', []],
            ['examples', []],
        ]);

        $enabled = (int)($data['enabled'] ?? 1);
        $title = trim((string)($data['title'] ?? ''));
        $homeSubtitle = trim((string)($data['home_subtitle'] ?? ''));
        $homeSearchPlaceholder = trim((string)($data['home_search_placeholder'] ?? ''));
        $homeFeaturedSubtitle = trim((string)($data['home_featured_subtitle'] ?? ''));
        $chatWelcomeText = trim((string)($data['chat_welcome_text'] ?? ''));
        $features = $this->normalizeStringList($data['features'] ?? []);
        $examples = $this->normalizeStringList($data['examples'] ?? []);

        if ($title === '') {
            throw new ValidateException('引导标题不能为空');
        }
        if ($homeSubtitle === '') {
            throw new ValidateException('首页顶部说明不能为空');
        }
        if ($homeSearchPlaceholder === '') {
            throw new ValidateException('首页搜索提示不能为空');
        }
        if ($homeFeaturedSubtitle === '') {
            throw new ValidateException('AI 智能精选说明不能为空');
        }
        if ($chatWelcomeText === '') {
            throw new ValidateException('AI 对话欢迎语不能为空');
        }
        if (count($features) === 0) {
            throw new ValidateException('请至少填写一项能力说明');
        }
        if (count($examples) === 0) {
            throw new ValidateException('请至少填写一个示例问题');
        }

        $payload = [
            'enabled' => $enabled,
            'title' => $title,
            'home_subtitle' => $homeSubtitle,
            'home_search_placeholder' => $homeSearchPlaceholder,
            'home_featured_subtitle' => $homeFeaturedSubtitle,
            'chat_welcome_text' => $chatWelcomeText,
            'features' => $features,
            'examples' => $examples,
            'version' => date('YmdHis'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $exists = $this->configRepository->search(['config_key' => 'onboarding_config'])->find();
        if ($exists) {
            $this->configRepository->update((int)$exists->config_id, [
                'config_value' => $json,
                'config_desc' => '小程序 AI 对话新手引导配置',
                'sort' => 300,
            ]);
        } else {
            $this->configRepository->create([
                'config_key' => 'onboarding_config',
                'config_value' => $json,
                'config_desc' => '小程序 AI 对话新手引导配置',
                'sort' => 300,
            ]);
        }

        return app('json')->success('保存成功');
    }

    protected function loadOnboardingConfig(): array
    {
        $json = $this->configRepository->text('onboarding_config', '');
        if ($json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return [
                    'enabled' => (int)($decoded['enabled'] ?? 1),
                    'title' => (string)($decoded['title'] ?? ''),
                    'home_subtitle' => (string)($decoded['home_subtitle'] ?? "告诉我你的需求，\n我会给你适合的建议"),
                    'home_search_placeholder' => (string)($decoded['home_search_placeholder'] ?? '附近，均80，有小孩，可停车'),
                    'home_featured_subtitle' => (string)($decoded['home_featured_subtitle'] ?? '根据您的需求和实时情况，为您推荐'),
                    'chat_welcome_text' => (string)($decoded['chat_welcome_text'] ?? '我是AI小惠，告诉我：你想吃什么？预算多少?距离优先还是折扣优先？和您同行的，有小孩或者老人吗？'),
                    'features' => $this->normalizeStringList($decoded['features'] ?? []),
                    'examples' => $this->normalizeStringList($decoded['examples'] ?? []),
                    'version' => (string)($decoded['version'] ?? ('legacy_' . substr(sha1($json), 0, 12))),
                    'updated_at' => (string)($decoded['updated_at'] ?? ''),
                ];
            }
        }
        return [
            'enabled' => 1,
            'title' => '你好，我是惠买单 AI 助手',
            'home_subtitle' => "告诉我你的需求，\n我会给你适合的建议",
            'home_search_placeholder' => '附近，均80，有小孩，可停车',
            'home_featured_subtitle' => '根据您的需求和实时情况，为您推荐',
            'chat_welcome_text' => '我是AI小惠，告诉我：你想吃什么？预算多少?距离优先还是折扣优先？和您同行的，有小孩或者老人吗？',
            'features' => [
                '智能推荐附近好店',
                '一句话找优惠',
                '根据口味、场景做选择',
            ],
            'examples' => [
                '附近有什么好吃的？',
                '适合聚会的餐厅推荐',
                '今天有什么优惠活动？',
            ],
            'version' => 'default',
            'updated_at' => '',
        ];
    }

    protected function normalizeStringList($value): array
    {
        if (is_string($value)) {
            $value = array_values(array_filter(array_map('trim', explode(',', $value)), function ($item) {
                return $item !== '';
            }));
        }
        if (!is_array($value)) {
            return [];
        }
        return array_values(array_filter(array_map(function ($item) {
            return trim((string)$item);
        }, $value), function ($item) {
            return $item !== '';
        }));
    }

    protected function inferCityIdFromAddress(string $address): int
    {
        $address = trim($address);
        if ($address === '') {
            return 0;
        }
        $rows = CityArea::getDB()
            ->whereIn('level', [1, 2, 3])
            ->field('id,name')
            ->select()
            ->toArray();
        $matchedId = 0;
        $matchedLength = 0;
        foreach ($rows as $row) {
            $name = trim((string)($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $candidates = [$name];
            if (mb_substr($name, -1) === '市') {
                $candidates[] = mb_substr($name, 0, -1);
            }
            foreach (array_unique($candidates) as $candidate) {
                if ($candidate !== '' && mb_strpos($address, $candidate) !== false && mb_strlen($candidate) > $matchedLength) {
                    $matchedId = (int)$row['id'];
                    $matchedLength = mb_strlen($candidate);
                }
            }
        }
        return $matchedId;
    }

    protected function tagPayload(array $data): array
    {
        foreach (['tag_type', 'tag_value'] as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                throw new ValidateException('标签类型和值不能为空');
            }
        }
        $synonyms = $data['synonyms'] ?? [];
        if (is_string($synonyms)) {
            $decoded = json_decode($synonyms, true);
            $synonyms = is_array($decoded) ? $decoded : array_values(array_filter(array_map('trim', explode(',', $synonyms))));
        }
        return [
            'tag_type' => trim((string)$data['tag_type']),
            'tag_value' => trim((string)$data['tag_value']),
            'tag_label' => trim((string)($data['tag_label'] ?? $data['tag_value'])),
            'synonyms' => json_encode(array_values($synonyms), JSON_UNESCAPED_UNICODE),
            'tag_weight' => max(1, min(100, (int)($data['tag_weight'] ?? 10))),
            'sort' => (int)($data['sort'] ?? 0),
            'status' => (int)($data['status'] ?? 1),
        ];
    }
}

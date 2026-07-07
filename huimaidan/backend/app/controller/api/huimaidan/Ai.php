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

namespace app\controller\api\huimaidan;

use app\common\repositories\huimaidan\AiConfigRepository;
use app\common\repositories\huimaidan\AiRepository;
use crmeb\basic\BaseController;
use think\App;

class Ai extends BaseController
{
    protected $repository;
    protected $configRepository;

    public function __construct(App $app, AiRepository $repository, AiConfigRepository $configRepository)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->configRepository = $configRepository;
    }

    public function banner()
    {
        $params = $this->request->params(['latitude', 'longitude', 'city_id', 'city_name']);
        return app('json')->success($this->repository->banner($params, $this->uid()));
    }

    public function chat()
    {
        $params = $this->request->params(['session_id', 'message', 'latitude', 'longitude', 'city_id', 'city_name']);
        return app('json')->success($this->repository->chat($this->uid(), $params));
    }

    public function event()
    {
        $params = $this->request->params(['log_id', 'session_id', 'event', 'mer_id', 'feedback']);
        return app('json')->success($this->repository->event($this->uid(), $params));
    }

    public function onboardingConfig()
    {
        $json = $this->configRepository->text('onboarding_config', '');
        $defaults = [
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
        if ($json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return app('json')->success([
                    'enabled' => (int)($decoded['enabled'] ?? 1),
                    'title' => (string)($decoded['title'] ?? $defaults['title']),
                    'home_subtitle' => (string)($decoded['home_subtitle'] ?? $defaults['home_subtitle']),
                    'home_search_placeholder' => (string)($decoded['home_search_placeholder'] ?? $defaults['home_search_placeholder']),
                    'home_featured_subtitle' => (string)($decoded['home_featured_subtitle'] ?? $defaults['home_featured_subtitle']),
                    'chat_welcome_text' => (string)($decoded['chat_welcome_text'] ?? $defaults['chat_welcome_text']),
                    'features' => $this->normalizeStringList($decoded['features'] ?? []),
                    'examples' => $this->normalizeStringList($decoded['examples'] ?? []),
                    'version' => (string)($decoded['version'] ?? ('legacy_' . substr(sha1($json), 0, 12))),
                    'updated_at' => (string)($decoded['updated_at'] ?? ''),
                ]);
            }
        }
        return app('json')->success($defaults);
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
        return array_values(array_filter(array_map('trim', $value), function ($item) {
            return $item !== '';
        }));
    }

    protected function uid(): int
    {
        return $this->request->hasMacro('isLogin') && $this->request->isLogin() ? (int)$this->request->uid() : 0;
    }
}

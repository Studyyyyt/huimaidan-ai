<?php

$root = dirname(__DIR__, 2);
$route = file_get_contents($root . '/route/api.php');
$adminRoute = file_get_contents($root . '/route/admin/huimaidan.php');
$frontendApi = file_get_contents(dirname($root) . '/../huimaidan-uniapp/src/api/ai.ts');
$frontendChat = file_get_contents(dirname($root) . '/../huimaidan-uniapp/src/pages/ai-chat/index.vue');
$frontendRouterConfig = file_get_contents(dirname($root) . '/../huimaidan-uniapp/src/router/config.ts');
$frontendRouterInterceptor = file_get_contents(dirname($root) . '/../huimaidan-uniapp/src/router/interceptor.ts');
$adminAiApi = file_get_contents(dirname($root) . '/admin/src/api/huimaidanAi.js');
$adminAiView = file_get_contents(dirname($root) . '/admin/src/views/huimaidan/ai/index.vue');
$standaloneAdminAiView = file_get_contents($root . '/public/huimaidan_ai_admin.html');
$aiNluRepository = file_get_contents($root . '/app/common/repositories/huimaidan/AiNluRepository.php');
$aiRepository = file_get_contents($root . '/app/common/repositories/huimaidan/AiRepository.php');
$aiLearningRepository = file_get_contents($root . '/app/common/repositories/huimaidan/AiUserLearningRepository.php');
$merchantTagInitializer = file_get_contents($root . '/app/common/repositories/huimaidan/MerchantTagInitializerRepository.php');
$promptRepository = file_get_contents($root . '/app/common/repositories/huimaidan/AiPromptRepository.php');
$llmClient = file_get_contents($root . '/crmeb/services/ai/LlmClientService.php');
$config = file_get_contents($root . '/config/huimaidan.php');
$aiSqlFile = $root . '/docs/sql/migrations/011_惠买单_AI推荐大脑表结构.sql';
if (!is_file($aiSqlFile)) {
    $aiSqlFile = $root . '/docs/sql/惠买单_AI推荐大脑表结构.sql';
}
$aiSql = file_get_contents($aiSqlFile);

foreach ([
    "Route::post('ai/chat', 'Ai/chat')" => 'AI 对话接口必须挂在强制登录的惠买单组内',
    "Route::post('ai/event', 'Ai/event')" => 'AI 行为归因接口必须挂在强制登录的惠买单组内',
    "Route::get('huimaidan/ai/banner', 'api.huimaidan.Ai/banner')->middleware(UserTokenMiddleware::class, false)" => 'AI Banner 必须允许游客可选登录访问',
] as $snippet => $message) {
    if (strpos($route, $snippet) === false) {
        throw new RuntimeException($message);
    }
}

foreach (['AI标签列表', '商户AI标签', 'AI Banner配置', 'AI推荐参数', 'AI推荐日志'] as $alias) {
    if (strpos($adminRoute, $alias) === false) {
        throw new RuntimeException('后台AI运营接口缺少：' . $alias);
    }
}

foreach ([
    "Route::delete('banner/delete/:id', 'Ai/deleteBanner')" => '后台 Banner 配置必须支持删除',
    "Route::delete('config/delete/:id', 'Ai/deleteConfig')" => '后台推荐参数必须支持删除',
    "Route::post('tag/import', 'Ai/importTags')" => '后台 AI 标签必须支持批量导入',
    "Route::get('merchants', 'Ai/merchants')" => '后台商户 AI 标签页面必须支持搜索选择商户',
    "Route::post('merchant_tags/init', 'Ai/initMerchantTags')" => '后台商户 AI 标签必须支持触发自动初始化',
    "Route::get('merchant_import/template', 'Ai/merchantImportTemplate')" => '后台必须提供商户 Excel 导入模板下载',
    "Route::post('merchant_import', 'Ai/merchantImport')" => '后台必须提供商户 Excel 导入接口',
] as $snippet => $message) {
    if (strpos($adminRoute, $snippet) === false) {
        throw new RuntimeException($message);
    }
}

if (strpos($adminAiApi, 'aiTagImportApi') === false || strpos($adminAiApi, 'aiMerchantTagsInitApi') === false || strpos($adminAiApi, 'aiMerchantImportApi') === false || strpos($adminAiView, '批量导入') === false || strpos($adminAiView, '初始化全部') === false || strpos($adminAiView, '下载商户导入模板') === false || strpos($adminAiView, '导入商户Excel') === false) {
    throw new RuntimeException('后台 AI 页面缺少批量导入、Excel商户导入或自动初始化入口');
}

if (strpos($standaloneAdminAiView, '下载商户导入模板') === false || strpos($standaloneAdminAiView, '导入商户 Excel') === false || strpos($standaloneAdminAiView, 'merchant_import/template') === false || strpos($standaloneAdminAiView, 'merchant_import') === false || strpos($standaloneAdminAiView, '搜索商户名称 / ID / 电话') === false || strpos($standaloneAdminAiView, 'AI 标签不用写英文参数') === false) {
    throw new RuntimeException('独立 AI 后台页面缺少商户 Excel 模板下载、导入入口或中文维护说明');
}

if (strpos($merchantTagInitializer, 'replaceAutoTags') === false || strpos($merchantTagInitializer, 'buildTags') === false) {
    throw new RuntimeException('商户 AI 自动标签初始化逻辑必须可被命令行和后台复用');
}

if (strpos($frontendApi, 'dashscope.aliyuncs.com') !== false || strpos($frontendApi, 'BAILIAN_API_KEY') !== false || strpos($frontendApi, 'mockPostAiChat') !== false) {
    throw new RuntimeException('小程序 AI API 不得直连百炼、暴露密钥或静默 Mock');
}

foreach (["'/api/huimaidan/ai/banner'", "'/api/huimaidan/ai/chat'"] as $snippet) {
    if (strpos($frontendApi, $snippet) === false) {
        throw new RuntimeException('小程序 AI API 必须调用后端接口：' . $snippet);
    }
}

if (strpos($aiRepository, 'resolveCityId') === false || strpos($aiRepository, "'city_name'") === false || strpos($frontendApi, 'city_name?: string') === false || strpos($frontendChat, 'city_name') === false) {
    throw new RuntimeException('AI 推荐接口必须支持 city_name 兜底解析，避免前端必须硬编码城市 ID');
}

if (strpos($frontendApi, "'/api/huimaidan/ai/event'") === false || strpos($frontendApi, 'IAiEventResponse') === false || strpos($frontendChat, 'postAiEvent') === false) {
    throw new RuntimeException('小程序必须上报 AI 推荐点击、买单或反馈行为');
}

foreach (["'detail'", "'navigate'", "'order'", "'feedback'"] as $eventName) {
    if (strpos($frontendApi, $eventName) === false || strpos($aiRepository, $eventName) === false) {
        throw new RuntimeException('AI 行为事件缺少支持：' . $eventName);
    }
}

if (strpos($frontendRouterConfig, "'/pages/ai-chat/index'") === false || strpos($frontendRouterInterceptor, 'FORCE_LOGIN_PATH_LIST') === false) {
    throw new RuntimeException('AI 对话页必须在小程序路由层强制登录');
}

if (strpos($aiRepository, 'AiUserLearningRepository') === false || strpos($aiLearningRepository, 'preference_updated') === false || strpos($aiLearningRepository, 'trackEvent') === false) {
    throw new RuntimeException('AI 用户行为学习必须有独立预留层，并暂不自动更新长期偏好');
}

if (strpos($aiRepository, "'background_color'") === false) {
    throw new RuntimeException('AI Banner 接口必须返回前端契约字段 background_color');
}

foreach (['input_max_length', 'sensitive_words'] as $configKey) {
    if (strpos($aiRepository, $configKey) === false || strpos($aiSql, $configKey) === false) {
        throw new RuntimeException('AI 输入安全配置缺少：' . $configKey);
    }
}

if (strpos($aiNluRepository, 'BailianAppClient') !== false || strpos($aiNluRepository, 'LlmClientService') === false) {
    throw new RuntimeException('NLU 不得直接依赖百炼客户端，必须走统一 LLM 服务');
}

if (strpos($aiNluRepository, 'AiPromptRepository') === false || strpos($aiRepository, 'reasoningPrompt') === false || strpos($promptRepository, 'prompt_nlu') === false || strpos($promptRepository, 'prompt_reasoning') === false) {
    throw new RuntimeException('AI Prompt 必须支持配置化 NLU 与推荐回复模板');
}

foreach (['price_range', 'time', 'people'] as $intentKey) {
    if (strpos($aiNluRepository, $intentKey) === false || strpos($promptRepository, $intentKey) === false || strpos($aiSql, $intentKey) === false) {
        throw new RuntimeException('AI 意图解析必须兼容字段：' . $intentKey);
    }
}

foreach (['prompt_nlu', 'prompt_reasoning'] as $configKey) {
    if (strpos($aiSql, $configKey) === false) {
        throw new RuntimeException('AI SQL 缺少 Prompt 配置：' . $configKey);
    }
}

foreach (['bailian', 'deepseek', 'claude'] as $driver) {
    if (strpos($llmClient, $driver) === false || strpos($config, "'" . $driver . "'") === false) {
        throw new RuntimeException('LLM 多驱动缺少：' . $driver);
    }
}

echo "AiRouteContractTest passed\n";

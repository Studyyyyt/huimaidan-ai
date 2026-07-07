<?php

return [
    // AI 推荐大脑配置
    'ai' => [
        'enabled' => env('HUIMAIDAN_AI_ENABLED', true),
        'llm_driver' => env('HUIMAIDAN_AI_LLM_DRIVER', 'bailian'),
        'drivers' => [
            'bailian' => [
                // mode=app 走百炼应用 API，mode=compatible 走 OpenAI 兼容模式；未显式配置时保持旧逻辑。
                'mode' => env('BAILIAN_MODE', ''),
                // 百炼应用 API 配置
                'app_id' => env('BAILIAN_APP_ID', ''),
                // 兼容模式 Chat Completions 配置（推荐，配置 model 时优先使用）
                'api_key' => env('BAILIAN_API_KEY', ''),
                'api_url' => env('BAILIAN_API_URL', 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions'),
                'model' => env('BAILIAN_MODEL', 'qwen-plus-latest'),
                'max_tokens' => (int)env('BAILIAN_MAX_TOKENS', 512),
                'temperature' => (float)env('BAILIAN_TEMPERATURE', 0.1),
                'workspace' => env('BAILIAN_WORKSPACE', ''),
                'timeout' => (int)env('BAILIAN_TIMEOUT', 15),
            ],
            'deepseek' => [
                'api_key' => env('DEEPSEEK_API_KEY', ''),
                'api_url' => env('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions'),
                'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-pro'),
                'timeout' => (int)env('DEEPSEEK_TIMEOUT', 15),
            ],
            'claude' => [
                'api_key' => env('CLAUDE_API_KEY', ''),
                'api_url' => env('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages'),
                'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-latest'),
                'version' => env('CLAUDE_API_VERSION', '2023-06-01'),
                'timeout' => (int)env('CLAUDE_TIMEOUT', 15),
            ],
        ],
        'score_weights' => [
            'tag' => 0.35,
            'distance' => 0.25,
            'heat' => 0.25,
            'promo' => 0.15,
        ],
        'recall' => [
            'default_radius_km' => 5,
            'max_candidates' => 50,
            'result_limit' => 5,
        ],
        'rerank' => [
            'enabled' => (int)env('HUIMAIDAN_AI_RERANK_ENABLED', 1),
            'candidate_limit' => (int)env('HUIMAIDAN_AI_RERANK_CANDIDATE_LIMIT', 12),
            'result_limit' => (int)env('HUIMAIDAN_AI_RERANK_RESULT_LIMIT', 5),
            'fallback_enabled' => (int)env('HUIMAIDAN_AI_RERANK_FALLBACK_ENABLED', 1),
            'timeout' => (int)env('HUIMAIDAN_AI_RERANK_TIMEOUT', 0),
            'max_tokens' => (int)env('HUIMAIDAN_AI_RERANK_MAX_TOKENS', 1024),
        ],
        'session' => [
            'ttl' => 3600,
            'max_history' => 5,
        ],
        'rate_limit' => [
            'daily_max' => 500,
        ],
        'input' => [
            'max_message_length' => 200,
            'sensitive_words' => [],
        ],
        'banner' => [
            'cache_ttl' => 300,
        ],
        'llm' => [
            'retry_times' => (int)env('HUIMAIDAN_AI_LLM_RETRY_TIMES', 0),
            'retry_sleep_ms' => (int)env('HUIMAIDAN_AI_LLM_RETRY_SLEEP_MS', 200),
        ],
        'circuit_breaker' => [
            'fail_key' => 'ai:llm:fails',
            'open_key' => 'ai:llm:open_until',
            'fail_threshold' => (int)env('HUIMAIDAN_AI_LLM_FAIL_THRESHOLD', 3),
            'recovery_seconds' => (int)env('HUIMAIDAN_AI_LLM_RECOVERY_SECONDS', 900),
        ],
    ],

    // 三木森语音播报配置
    'voice' => [
        'api_url'     => 'http://cs.mqlinks.com/txmsgpush/',
        'timeout'     => 10,  // 请求超时（秒）
        'max_retry'   => 3,   // 最大重试次数
        'retry_delay' => 30,  // 重试间隔（秒）
    ],
];

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

class AiPromptRepository
{
    protected $configRepository;

    public function __construct(AiConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * 构造 NLU 提示词。
     *
     * 返回数组包含 system（角色与格式要求）和 user（具体任务内容），
     * 便于底层 LLM 客户端使用 Chat Completions 的 system/user 角色分离，
     * 从而显著提升输出稳定性和响应速度。
     */
    public function nluPrompt(string $message, array $history, array $fallback): array
    {
        $userTemplate = $this->configRepository->text('prompt_nlu', $this->defaultNluUserTemplate());
        $systemTemplate = $this->configRepository->text('prompt_nlu_system', $this->defaultNluSystemTemplate());
        return [
            'system' => $systemTemplate,
            'user' => $this->render($userTemplate, [
                'message' => $message,
                'history' => json_encode(array_slice($history, -6), JSON_UNESCAPED_UNICODE),
                'fallback' => json_encode($fallback, JSON_UNESCAPED_UNICODE),
            ]),
        ];
    }

    /**
     * 构造推荐理由生成提示词。
     */
    public function reasoningPrompt(array $intent, array $merchants, string $fallbackText): array
    {
        $userTemplate = $this->configRepository->text('prompt_reasoning', $this->defaultReasoningUserTemplate());
        $systemTemplate = $this->configRepository->text('prompt_reasoning_system', $this->defaultReasoningSystemTemplate());
        $merchantPayload = array_map(function (array $merchant) {
            return [
                'mer_id' => (int)($merchant['mer_id'] ?? 0),
                'mer_name' => (string)($merchant['mer_name'] ?? ''),
                'distance' => (string)($merchant['distance'] ?? ''),
                'rating' => $merchant['rating'] ?? '',
                'price_per_person_text' => (string)($merchant['price_per_person_text'] ?? ''),
                'discount_label' => (string)($merchant['discount_label'] ?? ''),
                'recommend_reason' => (string)($merchant['recommend_reason'] ?? ''),
                'score_factors' => $merchant['score_factors'] ?? [],
            ];
        }, array_slice($merchants, 0, 5));

        return [
            'system' => $systemTemplate,
            'user' => $this->render($userTemplate, [
                'intent' => json_encode($intent, JSON_UNESCAPED_UNICODE),
                'merchants' => json_encode($merchantPayload, JSON_UNESCAPED_UNICODE),
                'fallback_text' => $fallbackText,
            ]),
        ];
    }

    /**
     * 构造 LLM 动态排序提示词。
     *
     * @param string $message 用户原话
     * @param array $intent 意图标签
     * @param array $candidates 候选商户完整摘要
     * @param array $history 历史对话
     * @return array ['system' => ..., 'user' => ...]
     */
    public function rerankPrompt(string $message, array $intent, array $candidates, array $history = []): array
    {
        $userTemplate = $this->configRepository->text('prompt_rerank', $this->defaultRerankUserTemplate());
        $systemTemplate = $this->configRepository->text('prompt_rerank_system', $this->defaultRerankSystemTemplate());

        $merchantPayload = array_map(function (array $merchant) {
            return [
                'mer_id' => (int)($merchant['mer_id'] ?? 0),
                'mer_name' => (string)($merchant['mer_name'] ?? ''),
                'category_name' => (string)($merchant['category_name'] ?? ''),
                'distance_km' => $merchant['distance_km'] ?? null,
                'distance_text' => (string)($merchant['distance'] ?? ''),
                'per_capita' => $merchant['per_capita'] ?? 0,
                'rating' => $merchant['rating'] ?? 0,
                'sales' => $merchant['sales'] ?? 0,
                'discount_label' => (string)($merchant['discount_label'] ?? ''),
                'member_discount' => $merchant['member_discount'] ?? null,
                'address' => (string)($merchant['mer_address'] ?? ''),
                'business_hours' => $merchant['business_hours'] ?? [],
                'facilities' => $merchant['facility_tags'] ?? [],
                'tags' => $merchant['tags'] ?? [],
                'city_name' => (string)($merchant['city_name'] ?? ''),
                'backend_recall_score' => $merchant['score'] ?? 0,
                'backend_recall_reason' => (string)($merchant['recommend_reason'] ?? ''),
            ];
        }, $candidates);

        return [
            'system' => $systemTemplate,
            'user' => $this->render($userTemplate, [
                'message' => $message,
                'intent' => json_encode($intent, JSON_UNESCAPED_UNICODE),
                'history' => json_encode(array_slice($history, -3), JSON_UNESCAPED_UNICODE),
                'candidates' => json_encode($merchantPayload, JSON_UNESCAPED_UNICODE),
            ]),
        ];
    }

    protected function render(string $template, array $vars): string
    {
        $replace = [];
        foreach ($vars as $key => $value) {
            $replace['{' . $key . '}'] = (string)$value;
        }
        return strtr($template, $replace);
    }

    protected function defaultNluSystemTemplate(): string
    {
        return "你是\"惠买单\"本地生活推荐系统的意图理解模块。任务：把用户口语化的需求，精准映射为系统可计算的 JSON 标签。\n\n"
            . "输出要求：\n"
            . "1. 严格输出 JSON，不要任何解释、不要对话、不要帮助说明。\n"
            . "2. 如果\"规则解析结果\"已经准确，可以直接使用；如果用户表达更复杂（如氛围、性价比、适合人群），请补充更精准的标签。\n"
            . "3. 价格区间必须是 0-30、30-60、60-100、100-150、150+ 中的一个；\"8折\"、\"7.5折\"等是折扣（promotion），不是价格，不要填入 price。\n"
            . "4. 不要编造用户输入中不存在的需求。\n"
            . "5. 不要输出商户列表、推荐理由或任何虚构信息。\n\n"
            . "支持标签字段：category（品类）、scene（场景）、taste（口味）、facility（设施）、feature（特色）、promotion（折扣，如8折、7.5折）、price/price_range（价格区间，单位元）、time/meal（餐段）、people（人群）、distance（距离）、action（动作）。\n\n"
            . "语义映射示例：\n"
            . '- "想吃辣但不贵" → {"category":["火锅"],"taste":["辣"],"price":"30-60"}' . "\n"
            . '- "约会氛围好" → {"scene":["约会"],"feature":["环境好"]}' . "\n"
            . '- "适合带老人孩子" → {"scene":["家庭","亲子"],"facility":["宝宝椅"]}' . "\n"
            . '- "性价比高" → {"price":"60-100","feature":["高评分"]}' . "\n"
            . '- "我要吃8折火锅" → {"category":["火锅"],"promotion":["8折"]}' . "\n"
            . '- "现在营业的" → 无需返回 requires_open_now，系统会自动处理。';
    }

    protected function defaultNluUserTemplate(): string
    {
        return "规则解析结果:{fallback}\n历史对话:{history}\n用户输入:{message}";
    }

    protected function defaultReasoningSystemTemplate(): string
    {
        return "你是\"惠买单\"小程序的本地生活推荐助手。任务：根据\"用户意图\"和\"候选商户信息\"，生成一句自然、口语化、个性化的推荐理由。\n\n"
            . "要求：\n"
            . "1. 结合用户提到的核心需求（如辣、聚餐、便宜、包间等）。\n"
            . "2. 突出该商户最符合需求的 1-2 个亮点。\n"
            . "3. 自然融入距离、优惠、人均等具体信息，不要生硬罗列。\n"
            . "4. 语气像朋友推荐，亲切自然。\n"
            . "5. 不要改变商户顺序，不要编造商户、优惠或距离。\n"
            . "6. 只输出正向推荐话术，禁止出现“不推荐”“不适合”“不太适合”“不建议”“缺点”“但是不”等否定评价；不要解释为什么其他商户不合适。\n\n"
            . '输出格式：严格返回 JSON {"text":"..."}，不要解释。';
    }

    protected function defaultReasoningUserTemplate(): string
    {
        return "用户意图:{intent}\n候选商户:{merchants}\n兜底文案:{fallback_text}\n\n"
            . "示例1：用户想吃火锅，绿达涮府7.5折、1.4km、人均80 → {\"text\":\"这家绿达涮府离你只有1.4公里，主打锡盟鲜羊火锅，现在全场7.5折，人均80左右，聚餐涮肉很合适。\"}\n"
            . "示例2：用户想找有包间的地方，宋北魏家宴8折、5km → {\"text\":\"宋北魏家宴有包间，适合聚餐，距离你约5公里，目前有8折优惠。\"}";
    }

    protected function defaultRerankSystemTemplate(): string
    {
        return "你是\"惠买单\"本地生活推荐系统的排序专家。任务：根据用户原话、意图标签和候选商户列表，对候选商户进行动态排序，并为每家商户生成一句推荐理由。\n\n"
            . "重要约束：\n"
            . "1. 只能对下面提供的候选商户进行排序，绝对不允许编造、新增或删除候选商户。\n"
            . "2. 必须充分理解用户的复杂偏好，包括：距离（最近/最远）、价格（最贵/便宜/人均范围）、评分、折扣（8折左右）、口味、设施、场景、人群、营业状态等。\n"
            . "3. 优先按照用户明确表达的核心偏好排序；当偏好冲突时，选择综合最符合用户需求的商户排在前面。\n"
            . "4. 不要改变商户的任何 factual 信息，如距离、人均、折扣、评分等。\n"
            . "5. 文案必须像朋友口语推荐，禁止出现 '后台召回分'、'后端召回'、'召回得分'、'候选集'、'候选'、'规则排序'、'排序依据'、'score'、'supper'、'late_night'、'business_hours'、'backend'、'factual'、'字段'、'权重'、'匹配度'、'标签'、'商户ID'、'编号' 等内部或技术词汇；涉及餐段请用早餐/午餐/晚餐/夜宵等中文表达。\n"
            . "6. summary 和 reason 只能写正向推荐，不要写“不推荐”“不适合”“不建议”“不太适合”“缺点”“但是不”等否定评价；不要对未入选商户做负面比较，只说明当前推荐商户的亮点。\n\n"
            . "输出格式：严格返回 JSON，不要任何解释。格式如下：\n"
            . '{"summary":"我按你的要求优先挑了更符合的一家，距离、优惠和口味都比较匹配。","items":[{"mer_id":2061,"rank":1,"reason":"这家是火锅店，距离更符合你的要求，同时有优惠，适合优先看看。"}]}' . "\n"
            . "注意：summary 和 reason 是展示给普通用户看的文案，不要出现 JSON、候选商户、意图标签、排序依据、规则分、score、backend_recall_score、标签、商户ID、编号等内部词，也不要提及候选序号或商户数字编号。";
    }

    protected function defaultRerankUserTemplate(): string
    {
        return "用户原话：{message}\n"
            . "历史对话：{history}\n"
            . "意图标签：{intent}\n"
            . "候选商户：{candidates}\n\n"
            . "请根据用户原话和意图标签，对候选商户进行排序并生成推荐理由。严格返回 JSON 格式。";
    }
}

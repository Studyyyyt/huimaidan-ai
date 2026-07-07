<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use app\common\repositories\huimaidan\AiResponseParser;

$parser = new AiResponseParser();

$plain = $parser->parseJson('{"category":["火锅"],"price":"30-60"}');
if ($plain['category'][0] !== '火锅' || $plain['price'] !== '30-60') {
    throw new RuntimeException('AI JSON 解析失败');
}

$markdown = $parser->parseJson("```json\n{\"scene\":[\"聚餐\"]}\n```");
if ($markdown['scene'][0] !== '聚餐') {
    throw new RuntimeException('AI Markdown JSON 解析失败');
}

$embedded = $parser->parseJson('好的，结果如下：{"taste":["辣"]}');
if ($embedded['taste'][0] !== '辣') {
    throw new RuntimeException('AI 嵌入 JSON 解析失败');
}

if ($parser->parseJson('不是 JSON') !== null) {
    throw new RuntimeException('非 JSON 文本不应解析成功');
}

echo "AiResponseParserTest passed\n";

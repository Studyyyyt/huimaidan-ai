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

declare (strict_types=1);

namespace app\command;

use app\common\repositories\huimaidan\MerchantTagInitializerRepository;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class HuimaidanAiInitTags extends Command
{
    protected function configure()
    {
        $this->setName('huimaidan:ai:init-tags')
            ->addOption('mer_id', null, Option::VALUE_OPTIONAL, '指定商户ID')
            ->setDescription('初始化惠买单商户AI标签');
    }

    protected function execute(Input $input, Output $output)
    {
        $merId = (int)$input->getOption('mer_id');
        $result = app()->make(MerchantTagInitializerRepository::class)->initialize($merId);
        $output->writeln('已初始化AI标签商户数：' . $result['merchant_count'] . '，标签数：' . $result['tag_count']);
    }
}

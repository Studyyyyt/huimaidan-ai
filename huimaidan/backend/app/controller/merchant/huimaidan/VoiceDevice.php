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

namespace app\controller\merchant\huimaidan;

use app\common\repositories\huimaidan\VoiceDeviceRepository;
use app\common\repositories\huimaidan\VoicePushRepository;
use crmeb\basic\BaseController;
use think\App;

class VoiceDevice extends BaseController
{
    protected $voiceDeviceRepository;
    protected $voicePushRepository;

    public function __construct(
        App $app,
        VoiceDeviceRepository $voiceDeviceRepository,
        VoicePushRepository $voicePushRepository
    ) {
        parent::__construct($app);
        $this->voiceDeviceRepository = $voiceDeviceRepository;
        $this->voicePushRepository   = $voicePushRepository;
    }

    /**
     * 设备列表
     * GET /merchant/huimaidan/voice_device/lst
     */
    public function lst()
    {
        [$page, $limit] = $this->getPage();
        $keyword = $this->request->get('keyword', '');

        $where = ['mer_id' => $this->request->merId()];
        if ($keyword) {
            $where['keyword'] = $keyword;
        }

        $result = $this->voiceDeviceRepository->getList($where, (int)$page, (int)$limit);

        return app('json')->success($result);
    }

    /**
     * 设备详情
     * GET /merchant/huimaidan/voice_device/detail
     */
    public function detail()
    {
        $id = (int)$this->request->get('id', 0);
        if (!$id) {
            return app('json')->fail('参数错误');
        }

        $result = $this->voiceDeviceRepository->detail($id, $this->request->merId());
        return app('json')->success($result);
    }

    /**
     * 新增设备
     * POST /merchant/huimaidan/voice_device/create
     */
    public function create()
    {
        $data = [
            'device_sn'   => $this->request->post('device_sn', ''),
            'device_name' => $this->request->post('device_name', ''),
        ];

        $id = $this->voiceDeviceRepository->create($this->request->merId(), $data);
        return app('json')->success('绑定成功', ['id' => $id]);
    }

    /**
     * 编辑设备
     * POST /merchant/huimaidan/voice_device/update
     */
    public function update()
    {
        $id   = (int)$this->request->post('id', 0);
        $data = [
            'device_name' => $this->request->post('device_name', ''),
            'remark'      => $this->request->post('remark', ''),
        ];

        $this->voiceDeviceRepository->update($id, $this->request->merId(), $data);
        return app('json')->success('编辑成功');
    }

    /**
     * 删除设备
     * POST /merchant/huimaidan/voice_device/delete
     */
    public function delete()
    {
        $id = (int)$this->request->post('id', 0);
        $this->voiceDeviceRepository->delete($id, $this->request->merId());
        return app('json')->success('删除成功');
    }

    /**
     * 切换设备状态
     * POST /merchant/huimaidan/voice_device/changeStatus
     */
    public function changeStatus()
    {
        $id = (int)$this->request->post('id', 0);
        $this->voiceDeviceRepository->changeStatus($id, $this->request->merId());
        return app('json')->success('操作成功');
    }

    /**
     * 测试播报
     * POST /merchant/huimaidan/voice_device/testPush
     */
    public function testPush()
    {
        $deviceId = (int)$this->request->post('device_id', 0);
        $logId    = $this->voicePushRepository->createTestBroadcast($this->request->merId(), $deviceId);
        return app('json')->success('测试播报已发送', ['log_id' => $logId]);
    }

    /**
     * 播报日志列表
     * GET /merchant/huimaidan/voice_device/pushLog
     */
    public function pushLog()
    {
        [$page, $limit] = $this->getPage();
        $where = ['mer_id' => $this->request->merId()];

        $deviceId = (int)$this->request->get('device_id', 0);
        if ($deviceId) {
            $where['device_id'] = $deviceId;
        }

        $pushStatus = $this->request->get('push_status', '');
        if ($pushStatus !== '') {
            $where['push_status'] = (int)$pushStatus;
        }

        $result = $this->voicePushRepository->getLogList($where, (int)$page, (int)$limit);
        return app('json')->success($result);
    }

    /**
     * 播报统计
     * GET /merchant/huimaidan/voice_device/statistics
     */
    public function statistics()
    {
        $result = $this->voicePushRepository->getStatistics($this->request->merId());
        return app('json')->success($result);
    }
}

<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Controller\Admin;

use Plugin\Jileapp\Smartscreen\Controller\AbstractController;
use Plugin\Jileapp\Smartscreen\Service\SmartScreenControlService;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: 'admin/plugin/smart-screen/control')]
class SmartScreenControlController extends AbstractController
{
    #[Inject]
    protected SmartScreenControlService $service;

    /**
     * 切换显示模式
     */
    #[PostMapping('switch-mode')]
    public function switchMode(): Result
    {
        $data = $this->request->all();
        $result = $this->service->switchDisplayMode($data);
        return $this->success($result, '显示模式切换成功');
    }

    /**
     * 推送内容（批量设置显示内容）
     */
    #[PostMapping('push-content')]
    public function pushContent(): Result
    {
        $data = $this->request->all();
        $result = $this->service->pushContent($data);
        return $this->success($result, '内容推送成功');
    }

    /**
     * 批量设置播放列表
     */
    #[PostMapping('set-playlist')]
    public function setPlaylist(): Result
    {
        $data = $this->request->all();
        $result = $this->service->setPlaylist($data);
        return $this->success($result, '播放列表设置成功');
    }

    /**
     * 广播控制
     */
    #[PostMapping('broadcast')]
    public function broadcast(): Result
    {
        $data = $this->request->all();
        $result = $this->service->broadcastControl($data);
        return $this->success($result, '广播控制成功');
    }

    /**
     * 获取设备状态
     */
    #[PostMapping('device-status')]
    public function getDeviceStatus(): Result
    {
        $data = $this->request->all();
        $result = $this->service->getDeviceStatus($data);
        return $this->success($result);
    }

    /**
     * 获取控制操作历史
     */
    #[PostMapping('operation-history')]
    public function getOperationHistory(): Result
    {
        $data = $this->request->all();
        $result = $this->service->getOperationHistory($data);
        return $this->success($result);
    }
} 
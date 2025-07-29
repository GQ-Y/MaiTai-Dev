<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Controller\Admin;

use Plugin\Jileapp\Smartscreen\Controller\AbstractController;
use Plugin\Jileapp\Smartscreen\Service\SmartScreenDeviceService;
use App\Http\Common\Result;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: 'admin/plugin/smart-screen/device')]
class SmartScreenDeviceController extends AbstractController
{
    #[Inject]
    protected SmartScreenDeviceService $service;

    /**
     * 列表
     */
    #[GetMapping('list')]
    public function list(): Result
    {
        return $this->success($this->service->page($this->request->all()));
    }

    /**
     * 创建
     */
    #[PostMapping('')]
    public function create(): Result
    {
        $data = $this->request->all();
        return $this->success($this->service->create($data));
    }

    /**
     * 更新
     */
    #[PutMapping('{id}')]
    public function save(int $id): Result
    {
        $data = $this->request->all();
        return $this->success($this->service->update($id, $data));
    }

    /**
     * 删除
     */
    #[DeleteMapping('')]
    public function delete(): Result
    {
        $data = $this->request->getBody()->getContents();
        $params = json_decode($data, true);
        $ids = $params['ids'] ?? [];
        return $this->success($this->service->delete($ids));
    }

    /**
     * 激活设备
     */
    #[PostMapping('activate')]
    public function activate(): Result
    {
        $id = (int)($this->request->input('id') ?? 0);
        $this->service->activate($id);
        return $this->success(['id' => $id, 'active' => 1], '设备已激活');
    }

    /**
     * 禁用设备
     */
    #[PostMapping('deactivate')]
    public function deactivate(): Result
    {
        $id = (int)($this->request->input('id') ?? 0);
        $this->service->deactivate($id);
        return $this->success(['id' => $id, 'active' => 0], '设备已禁用');
    }

    /**
     * 设置设备显示内容
     */
    #[PostMapping('set-content')]
    public function setContent(): Result
    {
        $deviceId = (int)($this->request->input('device_id') ?? 0);
        $contentId = $this->request->input('content_id') ? (int)$this->request->input('content_id') : null;
        
        $result = $this->service->setDeviceContent($deviceId, $contentId);
        return $this->success(['device_id' => $deviceId, 'content_id' => $contentId], '内容设置成功');
    }

    /**
     * 设置设备播放列表
     */
    #[PostMapping('set-playlist')]
    public function setPlaylist(): Result
    {
        $deviceId = (int)($this->request->input('device_id') ?? 0);
        $playlistIds = $this->request->input('playlist_ids', []);
        
        $result = $this->service->setDevicePlaylist($deviceId, $playlistIds);
        return $this->success([
            'device_id' => $deviceId, 
            'playlist_ids' => $playlistIds,
            'count' => count($playlistIds)
        ], '播放列表设置成功');
    }
}

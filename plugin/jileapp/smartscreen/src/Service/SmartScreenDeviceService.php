<?php

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDeviceRepository;
use Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher;

class SmartScreenDeviceService extends IService
{
    public function __construct(
        protected readonly SmartScreenDeviceRepository $repository
    ) {}

    /**
     * 分页列表，包含内容关联信息
     */
    public function page(array $params = [], int $page = 1, int $pageSize = 15): array
    {
        return $this->repository->getPageList($params, $page, $pageSize);
    }

    /**
     * 新增设备，参数校验与异常处理
     */
    public function create(array $data): mixed
    {
        // 数据验证
        if (empty($data['mac_address'])) {
            throw new BusinessException(ResultCode::FAIL, 'MAC地址不能为空');
        }
        if (empty($data['device_name'])) {
            throw new BusinessException(ResultCode::FAIL, '设备名称不能为空');
        }
        
        // 设置默认值
        $data['status'] = $data['status'] ?? 1;
        $data['is_online'] = $data['is_online'] ?? 0;
        $data['display_mode'] = $data['display_mode'] ?? 1;
        $data['created_by'] = $data['created_by'] ?? 1;
        $data['updated_by'] = $data['updated_by'] ?? 1;
        
        return parent::create($data);
    }

    /**
     * 更新设备
     */
    public function update(int $id, array $data): mixed
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }
        
        // 获取原设备信息
        $device = $this->repository->findById($id);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        
        // 检查播放策略是否变更
        $displayModeChanged = isset($data['display_mode']) && $data['display_mode'] != $device->display_mode;
        
        // 设置更新者
        $data['updated_by'] = $data['updated_by'] ?? 1;
        
        // 移除不能更新的字段
        unset($data['id'], $data['created_at'], $data['created_by']);
        
        $result = $this->updateById($id, $data);
        
        // 如果播放策略发生变更，推送通知到设备
        if ($displayModeChanged && $result) {
            DeviceWebSocketPusher::pushDisplayModeChange($device->mac_address, $data['display_mode']);
            
            // 根据新的播放策略推送相应内容
            $content = $this->getCurrentPlayContent($id);
            if ($content) {
                DeviceWebSocketPusher::pushContent($device->mac_address, $content);
            }
        }
        
        return $result;
    }

    /**
     * 更新设备，参数校验与异常处理
     */
    public function updateById(mixed $id, array $data): mixed
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }

        $model = $this->repository->findById($id);
        if (! $model) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }

        $model->fill($data);
        if (!array_key_exists('current_content_id', $data)) {
            $model->current_content_id = null;
        }

        return $model->save();
    }

    /**
     * 批量删除设备
     */
    public function delete(array $ids): int
    {
        if (empty($ids)) {
            throw new BusinessException(ResultCode::FAIL, 'ID列表不能为空');
        }
        $count = 0;
        foreach ($ids as $id) {
            $count += $this->deleteById($id);
        }
        return $count;
    }

    /**
     * 激活设备
     */
    public function activate(int $id): void
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }
        $device = $this->repository->findById($id);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        $this->repository->updateById($id, ['status' => 1]);
        // 推送激活状态
        DeviceWebSocketPusher::pushActiveStatus($device->mac_address, 1, '设备已激活');
    }

    /**
     * 禁用设备
     */
    public function deactivate(int $id): void
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }
        $device = $this->repository->findById($id);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        $this->repository->updateById($id, ['status' => 0]);
        // 推送禁用状态
        DeviceWebSocketPusher::pushActiveStatus($device->mac_address, 0, '设备已禁用');
    }

    /**
     * 设置设备显示内容
     */
    public function setDeviceContent(int $deviceId, ?int $contentId): bool
    {
        if (empty($deviceId)) {
            throw new BusinessException(ResultCode::FAIL, '设备ID不能为空');
        }
        
        $device = $this->repository->findById($deviceId);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        
        // 更新设备内容并设置播放策略为"直接内容优先"(2)
        $result = $this->repository->updateById($deviceId, [
            'current_content_id' => $contentId,
            'display_mode' => 2
        ]);
        
        // 设置内容后，根据播放策略推送相应内容
        if ($result) {
            $content = $this->getCurrentPlayContent($deviceId);
            if ($content) {
                DeviceWebSocketPusher::pushContent($device->mac_address, $content);
            }
        }
        
        return $result;
    }

    /**
     * 根据播放策略获取设备当前应播放的内容
     */
    public function getCurrentPlayContent(int $deviceId): ?array
    {
        if (empty($deviceId)) {
            throw new BusinessException(ResultCode::FAIL, '设备ID不能为空');
        }
        
        $device = $this->repository->findById($deviceId);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        
        switch ($device->display_mode) {
            case 1: // 播放列表优先
                return $this->getPlaylistContent($deviceId) ?: $this->getDirectContent($deviceId);
            case 2: // 直接内容优先  
                return $this->getDirectContent($deviceId) ?: $this->getPlaylistContent($deviceId);
            case 3: // 仅播放列表
                return $this->getPlaylistContent($deviceId);
            case 4: // 仅直接内容
                return $this->getDirectContent($deviceId);
            default:
                return null;
        }
    }

    /**
     * 获取设备直接关联的内容
     */
    private function getDirectContent(int $deviceId): ?array
    {
        return $this->repository->getDeviceDirectContent($deviceId);
    }

    /**
     * 获取设备播放列表中的内容
     */
    private function getPlaylistContent(int $deviceId): ?array
    {
        return $this->repository->getDevicePlaylistContent($deviceId);
    }

    /**
     * 推送当前内容到设备
     */
    public function pushCurrentContentToDevice(int $deviceId): bool
    {
        if (empty($deviceId)) {
            throw new BusinessException(ResultCode::FAIL, '设备ID不能为空');
        }
        
        $device = $this->repository->findById($deviceId);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        
        $content = $this->getCurrentPlayContent($deviceId);
        if ($content) {
            return DeviceWebSocketPusher::pushContent($device->mac_address, $content);
        }
        
        return false;
    }

    /**
     * 设置设备播放列表
     */
    public function setDevicePlaylist(int $deviceId, array $playlistIds): bool
    {
        if (empty($deviceId)) {
            throw new BusinessException(ResultCode::FAIL, '设备ID不能为空');
        }
        
        $device = $this->repository->findById($deviceId);
        if (!$device) {
            throw new BusinessException(ResultCode::FAIL, '设备不存在');
        }
        
        // 更新播放列表并设置播放策略为"播放列表优先"(1)
        $result = $this->repository->setDevicePlaylist($deviceId, $playlistIds);
        $this->repository->updateById($deviceId, ['display_mode' => 1]);
        
        // 设置播放列表后，根据播放策略推送相应内容
        if ($result) {
            $content = $this->getCurrentPlayContent($deviceId);
            if ($content) {
                DeviceWebSocketPusher::pushContent($device->mac_address, $content);
            }
        }
        
        return $result;
    }

    /**
     * 获取设备直接内容（对外）
     */
    public function getDeviceDirectContent(int $deviceId): ?array
    {
        return $this->repository->getDeviceDirectContent($deviceId);
    }
    /**
     * 获取设备所有播放列表内容（对外）
     */
    public function getDeviceAllPlaylistContents(int $deviceId): array
    {
        return $this->repository->getDeviceAllPlaylistContents($deviceId);
    }
    /**
     * 获取设备显示模式（对外）
     */
    public function getDeviceDisplayMode(int $deviceId): int
    {
        $device = $this->repository->findById($deviceId);
        return $device ? ($device->display_mode ?? 1) : 1;
    }

    // 其它业务方法可按需扩展
} 
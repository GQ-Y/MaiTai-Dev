<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDeviceGroupRepository;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDeviceRepository;
use Hyperf\Di\Annotation\Inject;

class SmartScreenDeviceGroupService extends IService
{
    public function __construct(
        protected readonly SmartScreenDeviceGroupRepository $repository,
        protected readonly SmartScreenDeviceRepository $deviceRepository
    ) {}

    public function getRepository(): SmartScreenDeviceGroupRepository
    {
        return $this->repository;
    }

    /**
     * 获取分组分页列表
     */
    public function getPageList(?array $params = []): array
    {
        return $this->repository->getPageList($params);
    }

    /**
     * 获取所有启用的分组
     */
    public function getAllEnabled(): array
    {
        return $this->repository->getAllEnabled();
    }

    /**
     * 根据ID获取分组详情，包含设备信息
     */
    public function getGroupWithDevices(int $id): ?array
    {
        return $this->repository->getGroupWithDevices($id);
    }

    /**
     * 创建分组
     */
    public function create(array $data): array
    {
        // 数据验证
        $this->validateGroupData($data);

        // 设置默认值
        $data['color'] = $data['color'] ?? '#1890ff';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['status'] = $data['status'] ?? 1;

        return $this->repository->create($data);
    }

    /**
     * 更新分组
     */
    public function update(int $id, array $data): bool
    {
        // 字段白名单过滤，只允许更新指定字段
        $allowedFields = ['name', 'description', 'color', 'sort_order', 'status'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        // 数据验证
        $this->validateGroupData($filteredData, $id);

        return $this->repository->update($id, $filteredData);
    }

    /**
     * 删除分组
     */
    public function delete(int $id): bool
    {
        // 检查分组是否存在
        $group = $this->repository->getGroupWithDevices($id);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        // 检查是否有设备关联
        if (!empty($group['devices'])) {
            throw new \InvalidArgumentException('该分组下还有设备，请先移除设备后再删除分组');
        }

        return $this->repository->delete($id);
    }

    /**
     * 批量删除分组
     */
    public function deleteByIds(array $ids): bool
    {
        // 检查每个分组是否有设备关联
        foreach ($ids as $id) {
            $group = $this->repository->getGroupWithDevices($id);
            if ($group && !empty($group['devices'])) {
                throw new \InvalidArgumentException("分组 \"{$group['name']}\" 下还有设备，请先移除设备后再删除");
            }
        }

        return $this->repository->deleteByIds($ids);
    }

    /**
     * 获取分组的设备列表
     */
    public function getGroupDevices(int $groupId): array
    {
        return $this->repository->getGroupDevices($groupId);
    }

    /**
     * 添加设备到分组
     */
    public function addDevicesToGroup(int $groupId, array $deviceIds): array
    {
        // 验证分组是否存在
        $group = $this->repository->getGroupWithDevices($groupId);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        // 验证设备是否存在
        $validDeviceIds = [];
        $invalidDevices = [];
        
        foreach ($deviceIds as $deviceId) {
            $device = $this->deviceRepository->getModel()::find($deviceId);
            if ($device) {
                // 检查设备是否已在该分组中
                if (!$this->repository->isDeviceInGroup($groupId, $deviceId)) {
                    $validDeviceIds[] = $deviceId;
                } else {
                    $invalidDevices[] = "设备 \"{$device->device_name}\" 已在该分组中";
                }
            } else {
                $invalidDevices[] = "设备ID {$deviceId} 不存在";
            }
        }

        if (empty($validDeviceIds)) {
            throw new \InvalidArgumentException('没有有效的设备可以添加：' . implode(', ', $invalidDevices));
        }

        // 添加设备到分组
        $result = $this->repository->addDevicesToGroup($groupId, $validDeviceIds);

        return [
            'success' => $result,
            'added_count' => count($validDeviceIds),
            'invalid_devices' => $invalidDevices,
        ];
    }

    /**
     * 从分组中移除设备
     */
    public function removeDevicesFromGroup(int $groupId, array $deviceIds): bool
    {
        // 验证分组是否存在
        $group = $this->repository->getGroupWithDevices($groupId);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        return $this->repository->removeDevicesFromGroup($groupId, $deviceIds);
    }

    /**
     * 更新设备在分组中的排序
     */
    public function updateDeviceSort(int $groupId, array $sortData): bool
    {
        // 验证分组是否存在
        $group = $this->repository->getGroupWithDevices($groupId);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        // 验证排序数据格式
        foreach ($sortData as $item) {
            if (!isset($item['device_id']) || !isset($item['sort_order'])) {
                throw new \InvalidArgumentException('排序数据格式错误');
            }
        }

        return $this->repository->updateDeviceSort($groupId, $sortData);
    }

    /**
     * 获取设备所属的分组
     */
    public function getDeviceGroups(int $deviceId): array
    {
        return $this->repository->getDeviceGroups($deviceId);
    }

    /**
     * 获取分组统计信息
     */
    public function getGroupStats(): array
    {
        return $this->repository->getGroupStats();
    }

    /**
     * 获取可添加到分组的设备列表
     */
    public function getAvailableDevicesForGroup(int $groupId): array
    {
        // 获取所有设备
        $allDevices = $this->deviceRepository->getModel()::select(['id', 'device_name', 'mac_address', 'status', 'is_online'])
            ->where('status', 1) // 只获取启用的设备
            ->get()
            ->toArray();

        // 获取已在该分组中的设备ID
        $groupDeviceIds = collect($this->repository->getGroupDevices($groupId))->pluck('id')->toArray();

        // 过滤出未在该分组中的设备，并重新索引数组
        $filteredDevices = array_filter($allDevices, function ($device) use ($groupDeviceIds) {
            return !in_array($device['id'], $groupDeviceIds);
        });

        // 重新索引数组，确保返回连续索引的数组
        return array_values($filteredDevices);
    }

    /**
     * 批量设置设备分组
     */
    public function batchSetDeviceGroups(array $deviceIds, array $groupIds): array
    {
        $results = [];
        
        foreach ($deviceIds as $deviceId) {
            try {
                // 先清除设备的所有分组关联
                $currentGroups = $this->getDeviceGroups($deviceId);
                foreach ($currentGroups as $group) {
                    $this->removeDevicesFromGroup($group['id'], [$deviceId]);
                }

                // 添加到新的分组
                foreach ($groupIds as $groupId) {
                    $this->addDevicesToGroup($groupId, [$deviceId]);
                }

                $results[$deviceId] = ['success' => true, 'message' => '设置成功'];
            } catch (\Exception $e) {
                $results[$deviceId] = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * 批量设置分组内设备的显示内容
     */
    public function batchSetGroupDevicesContent(int $groupId, ?int $contentId): array
    {
        // 验证分组是否存在
        $group = $this->repository->getGroupWithDevices($groupId);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        $devices = $group['devices'] ?? [];
        if (empty($devices)) {
            return [
                'success_count' => 0,
                'fail_count' => 0,
                'message' => '该分组下没有设备',
                'results' => []
            ];
        }

        // 验证内容是否存在且可用（如果提供了内容ID）
        if ($contentId) {
            // 直接查询内容表验证
            $contentExists = \Hyperf\DbConnection\Db::table('smart_screen_content')
                ->where('id', $contentId)
                ->where('status', 1)
                ->exists();
                
            if (!$contentExists) {
                throw new \InvalidArgumentException('内容不存在或已禁用');
            }
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 调用设备服务设置内容
                $deviceService = \Hyperf\Context\ApplicationContext::getContainer()->get(\Plugin\Jileapp\Smartscreen\Service\SmartScreenDeviceService::class);
                $result = $deviceService->setDeviceContent($device['id'], $contentId);
                
                // 新增：推送 content_response 消息
                $mac = strtolower($device['mac_address']);
                // 通过设备服务提供的接口获取内容
                $directContent = $deviceService->getDeviceDirectContent($device['id']);
                $playlistContents = $deviceService->getDeviceAllPlaylistContents($device['id']);
                $displayMode = $deviceService->getDeviceDisplayMode($device['id']);
                $displayModes = [1=>'播放列表优先',2=>'直接内容优先',3=>'仅播放列表',4=>'仅直接内容'];
                $displayModeName = $displayModes[$displayMode] ?? '未知策略';
                $primaryContents = [];
                $secondaryContents = [];
                switch ($displayMode) {
                    case 1:
                        $primaryContents = $playlistContents;
                        if ($directContent) $secondaryContents = [$directContent];
                        break;
                    case 2:
                        if ($directContent) $primaryContents = [$directContent];
                        $secondaryContents = $playlistContents;
                        break;
                    case 3:
                        $primaryContents = $playlistContents;
                        break;
                    case 4:
                        if ($directContent) $primaryContents = [$directContent];
                        break;
                }
                $contentResponseData = [
                    'device_id' => $device['id'],
                    'display_mode' => $displayMode,
                    'display_mode_name' => $displayModeName,
                    'direct_content' => $directContent,
                    'playlist_contents' => $playlistContents,
                    'has_direct_content' => !empty($directContent),
                    'has_playlist_contents' => !empty($playlistContents),
                    'primary_contents' => $primaryContents,
                    'secondary_contents' => $secondaryContents,
                    'total_contents' => count($primaryContents) + count($secondaryContents),
                ];
                \Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::pushContentResponse($mac, $contentResponseData, '批量设置内容');
                
                if ($result) {
                    // 检查WebSocket推送状态
                    $wsStatus = 'unknown';
                    if (\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$server && 
                        \Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$deviceTable) {
                        $mac = strtolower($device['mac_address']);
                        if (\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$deviceTable->exist($mac)) {
                            $wsStatus = 'pushed';
                        } else {
                            $wsStatus = 'offline';
                        }
                    } else {
                        $wsStatus = 'service_unavailable';
                    }
                    
                    $results[] = [
                        'device_id' => $device['id'],
                        'device_name' => $device['device_name'],
                        'mac_address' => $device['mac_address'],
                        'success' => true,
                        'message' => '内容设置成功',
                        'websocket_status' => $wsStatus
                    ];
                    $successCount++;
                } else {
                    $results[] = [
                        'device_id' => $device['id'],
                        'device_name' => $device['device_name'],
                        'mac_address' => $device['mac_address'],
                        'success' => false,
                        'message' => '内容设置失败',
                        'websocket_status' => 'failed'
                    ];
                    $failCount++;
                }

            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device['id'],
                    'device_name' => $device['device_name'],
                    'mac_address' => $device['mac_address'],
                    'success' => false,
                    'message' => $e->getMessage(),
                    'websocket_status' => 'error'
                ];
                $failCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_count' => count($devices),
            'message' => $successCount > 0 ? '批量设置完成' : '批量设置失败',
            'results' => $results
        ];
    }

    /**
     * 批量设置分组内设备的播放列表
     */
    public function batchSetGroupDevicesPlaylist(int $groupId, array $playlistIds): array
    {
        // 验证分组是否存在
        $group = $this->repository->getGroupWithDevices($groupId);
        if (!$group) {
            throw new \InvalidArgumentException('分组不存在');
        }

        $devices = $group['devices'] ?? [];
        if (empty($devices)) {
            return [
                'success_count' => 0,
                'fail_count' => 0,
                'message' => '该分组下没有设备',
                'results' => []
            ];
        }

        // 验证播放列表是否存在且可用（如果提供了播放列表ID）
        if (!empty($playlistIds)) {
            $validPlaylistCount = \Hyperf\DbConnection\Db::table('smart_screen_playlist')
                ->whereIn('id', $playlistIds)
                ->where('status', 1)
                ->count();
                
            if ($validPlaylistCount !== count($playlistIds)) {
                throw new \InvalidArgumentException('部分播放列表不存在或已禁用');
            }
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 调用设备服务设置播放列表
                $deviceService = \Hyperf\Context\ApplicationContext::getContainer()->get(\Plugin\Jileapp\Smartscreen\Service\SmartScreenDeviceService::class);
                $result = $deviceService->setDevicePlaylist($device['id'], $playlistIds);
                
                // 新增：推送 content_response 消息
                $mac = strtolower($device['mac_address']);
                $directContent = $deviceService->getDeviceDirectContent($device['id']);
                $playlistContents = $deviceService->getDeviceAllPlaylistContents($device['id']);
                $displayMode = $deviceService->getDeviceDisplayMode($device['id']);
                $displayModes = [1=>'播放列表优先',2=>'直接内容优先',3=>'仅播放列表',4=>'仅直接内容'];
                $displayModeName = $displayModes[$displayMode] ?? '未知策略';
                $primaryContents = [];
                $secondaryContents = [];
                switch ($displayMode) {
                    case 1:
                        $primaryContents = $playlistContents;
                        if ($directContent) $secondaryContents = [$directContent];
                        break;
                    case 2:
                        if ($directContent) $primaryContents = [$directContent];
                        $secondaryContents = $playlistContents;
                        break;
                    case 3:
                        $primaryContents = $playlistContents;
                        break;
                    case 4:
                        if ($directContent) $primaryContents = [$directContent];
                        break;
                }
                $contentResponseData = [
                    'device_id' => $device['id'],
                    'display_mode' => $displayMode,
                    'display_mode_name' => $displayModeName,
                    'direct_content' => $directContent,
                    'playlist_contents' => $playlistContents,
                    'has_direct_content' => !empty($directContent),
                    'has_playlist_contents' => !empty($playlistContents),
                    'primary_contents' => $primaryContents,
                    'secondary_contents' => $secondaryContents,
                    'total_contents' => count($primaryContents) + count($secondaryContents),
                ];
                \Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::pushContentResponse($mac, $contentResponseData, '批量设置播放列表');
                
                if ($result) {
                    // 检查WebSocket推送状态
                    $wsStatus = 'unknown';
                    if (\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$server && 
                        \Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$deviceTable) {
                        $mac = strtolower($device['mac_address']);
                        if (\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher::$deviceTable->exist($mac)) {
                            $wsStatus = 'pushed';
                        } else {
                            $wsStatus = 'offline';
                        }
                    } else {
                        $wsStatus = 'service_unavailable';
                    }
                    
                    $results[] = [
                        'device_id' => $device['id'],
                        'device_name' => $device['device_name'],
                        'mac_address' => $device['mac_address'],
                        'success' => true,
                        'message' => '播放列表设置成功',
                        'websocket_status' => $wsStatus
                    ];
                    $successCount++;
                } else {
                    $results[] = [
                        'device_id' => $device['id'],
                        'device_name' => $device['device_name'],
                        'mac_address' => $device['mac_address'],
                        'success' => false,
                        'message' => '播放列表设置失败',
                        'websocket_status' => 'failed'
                    ];
                    $failCount++;
                }

            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device['id'],
                    'device_name' => $device['device_name'],
                    'mac_address' => $device['mac_address'],
                    'success' => false,
                    'message' => $e->getMessage(),
                    'websocket_status' => 'error'
                ];
                $failCount++;
            }
        }

        return [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'total_count' => count($devices),
            'message' => $successCount > 0 ? '批量设置完成' : '批量设置失败',
            'results' => $results
        ];
    }

    /**
     * 验证分组数据
     */
    protected function validateGroupData(array $data, ?int $excludeId = null): void
    {
        // 验证名称
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('分组名称不能为空');
        }

        if (mb_strlen($data['name']) > 100) {
            throw new \InvalidArgumentException('分组名称不能超过100个字符');
        }

        // 检查名称是否重复
        $query = $this->repository->getModel()::where('name', $data['name']);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($query->exists()) {
            throw new \InvalidArgumentException('分组名称已存在');
        }

        // 验证描述
        if (isset($data['description']) && mb_strlen($data['description']) > 500) {
            throw new \InvalidArgumentException('分组描述不能超过500个字符');
        }

        // 验证颜色格式
        if (isset($data['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            throw new \InvalidArgumentException('颜色格式错误，请使用十六进制颜色码（如：#1890ff）');
        }

        // 验证状态
        if (isset($data['status']) && !in_array($data['status'], [0, 1])) {
            throw new \InvalidArgumentException('状态值错误');
        }

        // 验证排序
        if (isset($data['sort_order']) && (!is_numeric($data['sort_order']) || $data['sort_order'] < 0)) {
            throw new \InvalidArgumentException('排序值必须为非负数');
        }
    }
} 
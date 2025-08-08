<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Service;

use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDeviceRepository;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenContentRepository;
use Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher;
use Hyperf\Di\Annotation\Inject;
use App\Exception\BusinessException;
use App\Http\Common\ResultCode;

class SmartScreenControlService
{
    #[Inject]
    protected SmartScreenDeviceRepository $deviceRepository;

    #[Inject]
    protected SmartScreenContentRepository $contentRepository;

    #[Inject]
    protected SmartScreenDeviceService $deviceService;

    /**
     * 切换显示模式
     */
    public function switchDisplayMode(array $params): array
    {
        $deviceIds = $params['device_ids'] ?? [];
        $displayMode = (int)($params['display_mode'] ?? 1);

        if (!in_array($displayMode, [1, 2, 3, 4])) {
            throw new BusinessException(ResultCode::FAIL, '无效的显示模式');
        }

        // 如果设备ID为空，则获取所有设备
        if (empty($deviceIds)) {
            $devices = $this->deviceRepository->getModel()->where('status', 1)->get();
        } else {
            $devices = $this->deviceRepository->getModel()->whereIn('id', $deviceIds)->where('status', 1)->get();
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 更新设备显示模式
                $this->deviceRepository->updateById($device->id, [
                    'display_mode' => $displayMode
                ]);

                // 推送模式变更通知
                $pushResult = DeviceWebSocketPusher::pushDisplayModeChange($device->mac_address, $displayMode);
                
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'push_result' => $pushResult,
                    'status' => 'success'
                ];
                $successCount++;

                // 根据新的播放策略推送内容
                $this->pushContentByStrategy($device->id);

            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
                $failCount++;
            }
        }

        // 记录操作日志
        $this->logOperation('switch_mode', [
            'device_count' => count($devices),
            'display_mode' => $displayMode,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);

        return [
            'total' => count($devices),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * 推送内容（批量设置显示内容）
     */
    public function pushContent(array $params): array
    {
        $deviceIds = $params['device_ids'] ?? [];
        $contentId = (int)($params['content_id'] ?? 0);
        $duration = (int)($params['duration'] ?? 0);

        if (!$contentId) {
            throw new BusinessException(ResultCode::FAIL, '请选择要推送的内容');
        }

        // 验证内容是否存在且可用
        $content = $this->contentRepository->findById($contentId);
        if (!$content || $content->status !== 1) {
            throw new BusinessException(ResultCode::FAIL, '内容不存在或已禁用');
        }

        // 如果设备ID为空，则获取所有设备（不再判断是否在线）
        if (empty($deviceIds)) {
            $devices = $this->deviceRepository->getModel()
                ->where('status', 1)
                ->get();
        } else {
            $devices = $this->deviceRepository->getModel()
                ->whereIn('id', $deviceIds)
                ->where('status', 1)
                ->get();
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 更新设备的当前内容，并设置播放策略为"直接内容优先"(2)
                $this->deviceRepository->updateById($device->id, [
                    'current_content_id' => $contentId,
                    'display_mode' => 2
                ]);

                // 准备推送内容（不再包含is_temp参数）
                $pushContent = [
                    'id' => $content->id,
                    'title' => $content->title,
                    'content_type' => $content->content_type,
                    'content_url' => $content->content_url,
                    'thumbnail' => $content->thumbnail,
                    'duration' => $duration > 0 ? $duration : $content->duration
                ];

                // 直接推送内容（不再判断websocket是否在线）
                $pushResult = DeviceWebSocketPusher::pushContent($device->mac_address, $pushContent);
                
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'push_result' => $pushResult,
                    'status' => 'success'
                ];
                $successCount++;

            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
                $failCount++;
            }
        }

        // 记录操作日志
        $this->logOperation('push_content', [
            'content_id' => $contentId,
            'content_title' => $content->title,
            'device_count' => count($devices),
            'duration' => $duration,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);

        return [
            'total' => count($devices),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'content_info' => [
                'id' => $content->id,
                'title' => $content->title,
                'content_type' => $content->content_type
            ],
            'results' => $results
        ];
    }

    /**
     * 批量设置播放列表
     */
    public function setPlaylist(array $params): array
    {
        $deviceIds = $params['device_ids'] ?? [];
        $playlistIds = $params['playlist_ids'] ?? [];

        // 如果设备ID为空，则获取所有设备
        if (empty($deviceIds)) {
            $devices = $this->deviceRepository->getModel()
                ->where('status', 1)
                ->get();
        } else {
            $devices = $this->deviceRepository->getModel()
                ->whereIn('id', $deviceIds)
                ->where('status', 1)
                ->get();
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 更新设备的播放列表，并设置播放策略为"播放列表优先"(1)
                $this->deviceRepository->setDevicePlaylist($device->id, $playlistIds);
                $this->deviceRepository->updateById($device->id, ['display_mode' => 1]);

                // 获取设备详细信息用于推送
                $deviceService = \Hyperf\Context\ApplicationContext::getContainer()->get(\Plugin\Jileapp\Smartscreen\Service\SmartScreenDeviceService::class);
                $directContent = $deviceService->getDeviceDirectContent($device->id);
                $playlistContents = $deviceService->getDeviceAllPlaylistContents($device->id);
                // 更新后的播放模式
                $displayMode = 1;
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
                    'device_id' => $device->id,
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
                
                // 推送设备完整配置（直接推送，不再判断websocket是否在线）
                $pushResult = DeviceWebSocketPusher::pushContentResponse($device->mac_address, $contentResponseData, '批量设置播放列表');

                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'push_result' => $pushResult,
                    'status' => 'success'
                ];
                $successCount++;
            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
                $failCount++;
            }
        }

        // 记录操作日志
        $this->logOperation('set_playlist', [
            'playlist_count' => count($playlistIds),
            'device_count' => count($devices),
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);

        return [
            'total' => count($devices),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * 广播控制
     */
    public function broadcastControl(array $params): array
    {
        $action = $params['action'] ?? '';
        $deviceIds = $params['device_ids'] ?? [];
        $message = $params['message'] ?? '';

        if (!in_array($action, ['activate', 'deactivate', 'refresh', 'restart', 'shutdown'])) {
            throw new BusinessException(ResultCode::FAIL, '无效的操作类型');
        }

        // 如果设备ID为空，则获取所有设备
        if (empty($deviceIds)) {
            $devices = $this->deviceRepository->getModel()->get();
        } else {
            $devices = $this->deviceRepository->getModel()->whereIn('id', $deviceIds)->get();
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                $result = false;
                
                switch ($action) {
                    case 'activate':
                        $this->deviceService->activate($device->id);
                        $result = true;
                        break;
                    case 'deactivate':
                        $this->deviceService->deactivate($device->id);
                        $result = true;
                        break;
                    case 'refresh':
                        // 推送刷新指令
                        $result = $this->pushRefreshCommand($device->mac_address, $message);
                        break;
                    case 'restart':
                        // 推送重启指令
                        $result = $this->pushBatchControlCommand($device->mac_address, 'restart', $message);
                        break;
                    case 'shutdown':
                        // 推送关闭指令
                        $result = $this->pushBatchControlCommand($device->mac_address, 'shutdown', $message);
                        break;
                }

                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'action' => $action,
                    'result' => $result,
                    'status' => 'success'
                ];
                $successCount++;

            } catch (\Exception $e) {
                $results[] = [
                    'device_id' => $device->id,
                    'device_name' => $device->device_name,
                    'mac_address' => $device->mac_address,
                    'action' => $action,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
                $failCount++;
            }
        }

        // 记录操作日志
        $this->logOperation('broadcast_control', [
            'action' => $action,
            'device_count' => count($devices),
            'message' => $message,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);

        return [
            'total' => count($devices),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'action' => $action,
            'results' => $results
        ];
    }

    /**
     * 获取设备状态
     */
    public function getDeviceStatus(array $params): array
    {
        $devices = $this->deviceRepository->getPageList($params);

        // 增强设备信息（使用 Redis + 内存表双通道判断在线）
        foreach ($devices['list'] as &$device) {
            $mac = strtolower($device['mac_address']);
            $device['websocket_status'] = DeviceWebSocketPusher::isOnlineByMac($mac) ? 'connected' : 'disconnected';
            $device['last_heartbeat'] = DeviceWebSocketPusher::getLastHeartbeat($mac);
            
            // 获取当前内容信息
            if ($device['current_content_id']) {
                $content = $this->contentRepository->findById($device['current_content_id']);
                $device['current_content'] = $content ? [
                    'id' => $content->id,
                    'title' => $content->title,
                    'content_type' => $content->content_type,
                    'status' => $content->status
                ] : null;
            } else {
                $device['current_content'] = null;
            }
        }

        return $devices;
    }

    /**
     * 获取控制操作历史
     */
    public function getOperationHistory(array $params): array
    {
        // 这里可以实现操作历史的查询
        // 暂时返回空数组，后续可以根据需要实现完整的日志系统
        return [
            'list' => [],
            'total' => 0
        ];
    }

    /**
     * 根据播放策略推送内容
     */
    private function pushContentByStrategy(int $deviceId): void
    {
        try {
            $content = $this->deviceService->getCurrentPlayContent($deviceId);
            if ($content) {
                $device = $this->deviceRepository->findById($deviceId);
                if ($device) {
                    DeviceWebSocketPusher::pushContent($device->mac_address, [
                        'id' => $content->id,
                        'title' => $content->title,
                        'content_type' => $content->content_type,
                        'content_url' => $content->content_url,
                        'thumbnail' => $content->thumbnail,
                        'duration' => $content->duration
                    ]);
                }
            }
        } catch (\Exception $e) {
                         // 记录错误但不抛出异常
             // 日志记录暂时注释，避免语法错误
             // \Hyperf\Logger\LoggerFactory::get('smartscreen')->error(
             //     'Failed to push content by strategy: ' . $e->getMessage(),
             //     ['device_id' => $deviceId]
             // );
        }
    }

    /**
     * 推送刷新指令
     */
    private function pushRefreshCommand(string $mac, string $message = ''): bool
    {
        $mac = strtolower($mac);
        $server = DeviceWebSocketPusher::getServer();
        $fd = DeviceWebSocketPusher::getFdByMac($mac);
        if ($server && $fd && $server->isEstablished($fd)) {
            $data = [
                'type' => 'refresh',
                'message' => $message ?: '系统刷新'
            ];
            $server->push($fd, json_encode($data));
            return true;
        }
        
        return false;
    }

    /**
     * 推送批量控制指令（重启、关闭等）
     */
    private function pushBatchControlCommand(string $mac, string $action, string $message = ''): bool
    {
        return DeviceWebSocketPusher::pushBatchControl($mac, $action, $message);
    }

    /**
     * 记录操作日志
     */
    private function logOperation(string $operation, array $data): void
    {
        try {
            // 日志记录功能暂时注释，避免语法错误
            // \Hyperf\Logger\LoggerFactory::get('smartscreen-control')->info(
            //     "Control operation: {$operation}",
            //     $data
            // );
        } catch (\Exception $e) {
            // 忽略日志错误
        }
    }
} 
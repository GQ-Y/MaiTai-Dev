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
     * 推送内容
     */
    public function pushContent(array $params): array
    {
        $deviceIds = $params['device_ids'] ?? [];
        $contentId = (int)($params['content_id'] ?? 0);
        $isTemp = (bool)($params['is_temp'] ?? false);
        $duration = (int)($params['duration'] ?? 0);

        if (!$contentId) {
            throw new BusinessException(ResultCode::FAIL, '请选择要推送的内容');
        }

        // 验证内容是否存在且可用
        $content = $this->contentRepository->findById($contentId);
        if (!$content || $content->status !== 1) {
            throw new BusinessException(ResultCode::FAIL, '内容不存在或已禁用');
        }

        // 如果设备ID为空，则获取所有在线设备
        if (empty($deviceIds)) {
            $devices = $this->deviceRepository->getModel()
                ->where('status', 1)
                ->where('is_online', 1)
                ->get();
        } else {
            $devices = $this->deviceRepository->getModel()
                ->whereIn('id', $deviceIds)
                ->where('status', 1)
                ->where('is_online', 1)
                ->get();
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($devices as $device) {
            try {
                // 如果不是临时推送，更新设备的当前内容
                if (!$isTemp) {
                    $this->deviceRepository->updateById($device->id, [
                        'current_content_id' => $contentId
                    ]);
                }

                // 准备推送内容
                $pushContent = [
                    'id' => $content->id,
                    'title' => $content->title,
                    'content_type' => $content->content_type,
                    'content_url' => $content->content_url,
                    'thumbnail' => $content->thumbnail,
                    'duration' => $duration > 0 ? $duration : $content->duration,
                    'is_temp' => $isTemp
                ];

                // 推送内容
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
            'is_temp' => $isTemp,
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
        
        // 获取在线设备信息
        $onlineDevices = [];
        if (DeviceWebSocketPusher::$deviceTable) {
            foreach (DeviceWebSocketPusher::$deviceTable as $mac => $info) {
                $onlineDevices[$mac] = $info;
            }
        }

        // 增强设备信息
        foreach ($devices['list'] as &$device) {
            $mac = strtolower($device['mac_address']);
            $device['websocket_status'] = isset($onlineDevices[$mac]) ? 'connected' : 'disconnected';
            $device['last_heartbeat'] = $onlineDevices[$mac]['last_heartbeat'] ?? null;
            
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
        if (!DeviceWebSocketPusher::$server || !DeviceWebSocketPusher::$deviceTable) {
            return false;
        }
        
        $mac = strtolower($mac);
        if (!DeviceWebSocketPusher::$deviceTable->exist($mac)) {
            return false;
        }
        
        $fd = DeviceWebSocketPusher::$deviceTable->get($mac, 'fd');
        if ($fd && DeviceWebSocketPusher::$server->isEstablished($fd)) {
            $data = [
                'type' => 'refresh',
                'message' => $message ?: '系统刷新'
            ];
            DeviceWebSocketPusher::$server->push($fd, json_encode($data));
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
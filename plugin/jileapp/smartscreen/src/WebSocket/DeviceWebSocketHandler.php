<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\WebSocket;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Di\Annotation\Inject;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDeviceRepository;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenContentRepository;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\WebSocket\Frame as SwooleFrame;

class DeviceWebSocketHandler implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    // 使用推送器的设备表，确保一致性

    #[Inject]
    protected SmartScreenDeviceRepository $deviceRepository;

    #[Inject]  
    protected SmartScreenContentRepository $contentRepository;

    public static function initTable(): void
    {
        if (!DeviceWebSocketPusher::$deviceTable) {
            try {
                $table = new \Swoole\Table(1024);
                $table->column('fd', \Swoole\Table::TYPE_INT);
                $table->column('mac', \Swoole\Table::TYPE_STRING, 32);
                $table->column('active', \Swoole\Table::TYPE_INT);
                $table->column('device_id', \Swoole\Table::TYPE_INT);
                $table->column('last_heartbeat', \Swoole\Table::TYPE_INT);
                $table->create();
                DeviceWebSocketPusher::$deviceTable = $table;
                echo "Device table initialized successfully\n";
            } catch (\Exception $e) {
                echo "Error initializing device table: {$e->getMessage()}\n";
                DeviceWebSocketPusher::$deviceTable = null;
            }
        }
    }

    // 新增：在主服务启动时自动初始化内存表
    public static function onWorkerStart()
    {
        self::initTable();
    }

    // 连接建立事件
    public function onOpen($server, $request): void
    {
        self::initTable();
        // 设置server实例到推送器
        DeviceWebSocketPusher::$server = $server;
        // 尝试清理旧的 FD 映射（防止重连残留）
        try {
            if (isset($request->fd)) {
                DeviceWebSocketPusher::clearConnectionByFd((int) $request->fd);
            }
        } catch (\Throwable $e) {
            // 忽略
        }
        
        if (DeviceWebSocketPusher::$deviceTable === null) {
            echo "Failed to initialize device table for connection: {$request->fd}\n";
            if (method_exists($server, 'close')) {
                $server->close((int) $request->fd);
            }
            return;
        }
        echo "WebSocket connection opened: {$request->fd}\n";
        // 连接成功日志
        try {
            $ip = $request->server['remote_addr'] ?? 'unknown';
            $ua = $request->header['user-agent'] ?? '';
            echo sprintf("[WS] Connected fd=%d ip=%s ua=%s\n", (int) $request->fd, $ip, $ua);
        } catch (\Throwable $e) {}
    }

    // 消息事件
    public function onMessage($server, $frame): void
    {
        $data = json_decode($frame->data, true);
        if (!is_array($data) || !isset($data['type'])) {
            $server->push($frame->fd, json_encode(['type' => 'error', 'msg' => '消息格式错误']));
            return;
        }
        
        switch ($data['type']) {
            case 'register':
                $this->handleRegister($server, $frame, $data);
                break;
            case 'get_content':
                $this->handleGetContent($server, $frame, $data);
                break;
            case 'heartbeat':
                $this->handleHeartbeat($server, $frame, $data);
                break;
            default:
                $server->push($frame->fd, json_encode(['type' => 'error', 'msg' => '未知消息类型']));
        }
    }

    /**
     * 处理设备注册 - 自动注册新设备
     */
    private function handleRegister($server, $frame, $data): void
    {
        // 确保内存表已初始化
        self::initTable();
        if (DeviceWebSocketPusher::$deviceTable === null) {
            $server->push($frame->fd, json_encode(['type' => 'register_ack', 'success' => false, 'msg' => '服务器内存表初始化失败']));
            return;
        }
        
        if (empty($data['mac'])) {
            $server->push($frame->fd, json_encode(['type' => 'register_ack', 'success' => false, 'msg' => '缺少mac地址']));
            return;
        }

        $mac = strtolower($data['mac']);
        
        // 查询数据库中的设备信息
        $device = $this->deviceRepository->getModel()
            ->where('mac_address', $mac)
            ->first();

        if (!$device) {
            // 设备不存在，自动创建新设备
            try {
                $deviceData = [
                    'mac_address' => $mac,
                    'device_name' => $data['device_name'] ?? 'SmartScreen-' . strtoupper(substr($mac, -8)),
                    'status' => 0, // 默认未激活状态
                    'is_online' => 1, // 注册时设为在线
                    'display_mode' => 1, // 默认播放列表优先
                    'current_content_id' => null,
                    'last_online_time' => date('Y-m-d H:i:s'),
                    'created_by' => null, // 系统自动创建
                    'updated_by' => null,
                ];

                $device = $this->deviceRepository->create($deviceData);
                
                // 记录日志
                echo "Auto-registered new device: {$mac} with ID: {$device->id}\n";
                
            } catch (\Exception $e) {
                // 创建设备失败，返回错误
                $server->push($frame->fd, json_encode([
                    'type' => 'register_ack', 
                    'success' => false, 
                    'msg' => '设备自动注册失败：' . $e->getMessage()
                ]));
                return;
            }
        } else {
            // 设备已存在，更新在线状态
            $this->deviceRepository->updateById($device->id, [
                'is_online' => 1,
                'last_online_time' => date('Y-m-d H:i:s')
            ]);
        }

        // 存储到内存表
        DeviceWebSocketPusher::$deviceTable->set($mac, [
            'fd' => $frame->fd,
            'mac' => $mac,
            'active' => $device->status, // 使用数据库中的真实状态
            'device_id' => $device->id,
            'last_heartbeat' => time(),
        ]);

        // 记录连接到 Redis，供跨进程推送使用
        DeviceWebSocketPusher::setConnection($mac, (int) $frame->fd);
        DeviceWebSocketPusher::setHeartbeat($mac, time());

        // 返回注册结果
        $statusMsg = $device->status ? '设备已激活' : '设备未激活，请联系管理员激活';
        
        // 判断是否为新创建的设备（通过检查创建时间是否为最近几秒）
        $isNewDevice = false;
        if ($device->created_at) {
            // 如果是Carbon对象，转换为时间戳；如果是字符串，使用strtotime
            $createdAt = $device->created_at instanceof \Carbon\Carbon 
                ? $device->created_at->timestamp 
                : strtotime($device->created_at);
            $isNewDevice = (time() - $createdAt) < 5; // 5秒内创建的认为是新设备
        }
        
        $server->push($frame->fd, json_encode([
            'type' => 'register_ack',
            'success' => true,
            'active' => $device->status,
            'device_id' => $device->id,
            'is_new_device' => $isNewDevice,
            'msg' => $isNewDevice 
                ? "设备已自动注册成功！{$statusMsg}" 
                : "设备重新连接成功！{$statusMsg}"
        ]));
    }

    /**
     * 处理获取内容请求 - 根据播放策略返回内容
     */
    private function handleGetContent($server, $frame, $data): void
    {
        // 确保内存表已初始化
        self::initTable();
        if (DeviceWebSocketPusher::$deviceTable === null) {
            $server->push($frame->fd, json_encode(['type' => 'content_response', 'success' => false, 'msg' => '服务器内存表初始化失败']));
            return;
        }
        
        if (empty($data['mac'])) {
            $server->push($frame->fd, json_encode(['type' => 'content_response', 'success' => false, 'msg' => '缺少mac地址']));
            return;
        }

        $mac = strtolower($data['mac']);
        
        // 检查设备是否已注册
        if (!DeviceWebSocketPusher::$deviceTable->exist($mac)) {
            $server->push($frame->fd, json_encode(['type' => 'content_response', 'success' => false, 'msg' => '设备未注册']));
            return;
        }

        $deviceInfo = DeviceWebSocketPusher::$deviceTable->get($mac);
        
        // 检查设备是否已激活
        if (!$deviceInfo['active']) {
            $server->push($frame->fd, json_encode(['type' => 'content_response', 'success' => false, 'msg' => '设备未激活']));
            return;
        }

        // 获取设备详细信息
        $device = $this->deviceRepository->findById($deviceInfo['device_id']);
        if (!$device) {
            $server->push($frame->fd, json_encode(['type' => 'content_response', 'success' => false, 'msg' => '设备信息不存在']));
            return;
        }

        // 获取直接关联的内容
        $directContent = $this->deviceRepository->getDeviceDirectContent($device->id);
        
        // 获取播放列表的所有内容
        $playlistContents = $this->deviceRepository->getDeviceAllPlaylistContents($device->id);

        // 播放策略说明
        $displayModes = [
            1 => '播放列表优先',
            2 => '直接内容优先', 
            3 => '仅播放列表',
            4 => '仅直接内容'
        ];

        $displayMode = $device->display_mode;
        $displayModeName = $displayModes[$displayMode] ?? '未知策略';

        // 根据播放策略决定返回的内容
        $responseData = [
            'device_id' => $device->id,
            'display_mode' => $displayMode,
            'display_mode_name' => $displayModeName,
            'direct_content' => $directContent,
            'playlist_contents' => $playlistContents,
            'has_direct_content' => !empty($directContent),
            'has_playlist_contents' => !empty($playlistContents),
        ];

        // 根据播放策略确定优先内容
        $primaryContents = [];
        $secondaryContents = [];

        switch ($displayMode) {
            case 1: // 播放列表优先
                $primaryContents = $playlistContents;
                if ($directContent) {
                    $secondaryContents = [$directContent];
                }
                break;
                
            case 2: // 直接内容优先
                if ($directContent) {
                    $primaryContents = [$directContent];
                }
                $secondaryContents = $playlistContents;
                break;
                
            case 3: // 仅播放列表
                $primaryContents = $playlistContents;
                $secondaryContents = [];
                break;
                
            case 4: // 仅直接内容
                if ($directContent) {
                    $primaryContents = [$directContent];
                }
                $secondaryContents = [];
                break;
        }

        $responseData['primary_contents'] = $primaryContents;
        $responseData['secondary_contents'] = $secondaryContents;
        $responseData['total_contents'] = count($primaryContents) + count($secondaryContents);

        // 检查是否有可播放的内容
        if (empty($primaryContents) && empty($secondaryContents)) {
            $server->push($frame->fd, json_encode([
                'type' => 'content_response', 
                'success' => false, 
                'msg' => "当前播放策略（{$displayModeName}）下暂无可播放内容",
                'data' => $responseData
            ]));
            return;
        }

        // 返回内容响应
        $server->push($frame->fd, json_encode([
            'type' => 'content_response',
            'success' => true,
            'msg' => "获取内容成功，当前策略：{$displayModeName}",
            'data' => $responseData
        ]));
    }

    /**
     * 处理心跳
     */
    private function handleHeartbeat($server, $frame, $data): void
    {
        // 确保内存表已初始化
        self::initTable();
        if (DeviceWebSocketPusher::$deviceTable === null) {
            $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => false, 'msg' => '服务器内存表初始化失败']));
            return;
        }
        
        if (empty($data['mac'])) {
            $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => false, 'msg' => '缺少mac地址']));
            return;
        }
        
        $mac = strtolower($data['mac']);
        $now = time();
        if (! DeviceWebSocketPusher::$deviceTable->exist($mac)) {
            // 未注册则自动注册到内存表与 Redis，尽量容错
            try {
                // 查询或自动创建设备
                $device = $this->deviceRepository->getModel()
                    ->where('mac_address', $mac)
                    ->first();
                if (! $device) {
                    $device = $this->deviceRepository->create([
                        'mac_address' => $mac,
                        'device_name' => 'SmartScreen-' . strtoupper(substr($mac, -8)),
                        'status' => 0,
                        'is_online' => 1,
                        'display_mode' => 1,
                        'current_content_id' => null,
                        'last_online_time' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    // 标记在线与更新时间
                    $this->deviceRepository->updateById($device->id, [
                        'is_online' => 1,
                        'last_online_time' => date('Y-m-d H:i:s'),
                    ]);
                }

                // 写入内存表
                DeviceWebSocketPusher::$deviceTable->set($mac, [
                    'fd' => $frame->fd,
                    'mac' => $mac,
                    'active' => (int) $device->status,
                    'device_id' => (int) $device->id,
                    'last_heartbeat' => $now,
                ]);
                // 写入 Redis
                DeviceWebSocketPusher::setConnection($mac, (int) $frame->fd);
                DeviceWebSocketPusher::setHeartbeat($mac, $now);

                $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => true, 'active' => (int) $device->status, 'msg' => '心跳成功（自动注册）']));
                return;
            } catch (\Throwable $e) {
                $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => false, 'msg' => '心跳失败：' . $e->getMessage()]));
                return;
            }
        }

        // 已注册设备：更新心跳与最近在线时间
        DeviceWebSocketPusher::$deviceTable->set($mac, [
            'last_heartbeat' => $now,
        ]);
        DeviceWebSocketPusher::setHeartbeat($mac, $now);

        // 尝试更新 DB 最近在线时间（忽略异常）
        try {
            $row = DeviceWebSocketPusher::$deviceTable->get($mac);
            if (! empty($row['device_id'])) {
                $this->deviceRepository->updateById((int) $row['device_id'], [
                    'is_online' => 1,
                    'last_online_time' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {}

        $active = DeviceWebSocketPusher::$deviceTable->get($mac, 'active');
        $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => true, 'active' => $active, 'msg' => '心跳成功']));
    }

    // 关闭事件
    public function onClose($server, int $fd, int $reactorId): void
    {
        // 确保内存表已初始化
        self::initTable();
        if (DeviceWebSocketPusher::$deviceTable === null) {
            return;
        }
        
        // 先通过 Redis 快速定位 mac
        $mac = DeviceWebSocketPusher::getMacByFd($fd);
        if ($mac) {
            $row = DeviceWebSocketPusher::$deviceTable->get($mac);
            if ($row && isset($row['device_id'])) {
                try {
                    $this->deviceRepository->updateById((int) $row['device_id'], ['is_online' => 0]);
                } catch (\Throwable $e) {
                    echo "Error updating device offline status: {$e->getMessage()}\n";
                }
            }
            DeviceWebSocketPusher::$deviceTable->del($mac);
            DeviceWebSocketPusher::clearConnectionByMac($mac);
            echo "Device disconnected: {$mac}\n";
            return;
        }

        // 回退：遍历内存表匹配 fd
        foreach (DeviceWebSocketPusher::$deviceTable as $tbMac => $row) {
            if ((int) $row['fd'] === $fd) {
                if (isset($row['device_id'])) {
                    try {
                        $this->deviceRepository->updateById((int) $row['device_id'], ['is_online' => 0]);
                    } catch (\Throwable $e) {
                        echo "Error updating device offline status: {$e->getMessage()}\n";
                    }
                }
                DeviceWebSocketPusher::$deviceTable->del($tbMac);
                DeviceWebSocketPusher::clearConnectionByMac($tbMac);
                echo "Device disconnected: {$tbMac}\n";
                break;
            }
        }
    }
} 
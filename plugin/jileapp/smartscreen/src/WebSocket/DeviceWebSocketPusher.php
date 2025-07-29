<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\WebSocket;

/**
 * WebSocket推送服务，支持设备激活/禁用状态变更实时推送。
 * 仅适用于单进程/单实例场景，分布式需用Redis等IPC。
 */
class DeviceWebSocketPusher
{
    /**
     * Swoole\WebSocket\Server实例（需在WebSocket服务端启动时注入）
     * @var \Swoole\WebSocket\Server|null
     */
    public static ?\Swoole\WebSocket\Server $server = null;

    /**
     * 设备内存表（mac=>fd等），需在WebSocket服务端启动时注入
     * @var \Swoole\Table|null
     */
    public static ?\Swoole\Table $deviceTable = null;

    /**
     * 推送设备激活/禁用状态
     * @param string $mac
     * @param int $active 1=激活 0=禁用
     * @param string $msg
     * @return bool
     */
    public static function pushActiveStatus(string $mac, int $active, string $msg = ''): bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $data = [
                'type' => 'active_status',
                'active' => $active,
                'msg' => $msg ?: ($active ? '设备已激活' : '设备已禁用'),
            ];
            self::$server->push($fd, json_encode($data));
            // 同步内存表状态
            self::$deviceTable->set($mac, ['active' => $active]);
            return true;
        }
        return false;
    }

    /**
     * 推送内容到设备
     * @param string $mac 设备MAC地址
     * @param array $content 内容信息
     * @return bool
     */
    public static function pushContent(string $mac, array $content): bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $data = [
                'type' => 'push_content',
                'data' => [
                    'content_id' => $content['id'] ?? null,
                    'content_type' => $content['content_type'] ?? 1,
                    'content_url' => $content['content_url'] ?? '',
                    'title' => $content['title'] ?? '',
                    'duration' => $content['duration'] ?? 0,
                    'thumbnail' => $content['thumbnail'] ?? '',
                ],
            ];
            self::$server->push($fd, json_encode($data));
            return true;
        }
        return false;
    }

    /**
     * 推送播放策略变更通知
     * @param string $mac 设备MAC地址
     * @param int $displayMode 播放策略
     * @return bool
     */
    public static function pushDisplayModeChange(string $mac, int $displayMode): bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $modeNames = [
                1 => '播放列表优先',
                2 => '直接内容优先',
                3 => '仅播放列表',
                4 => '仅直接内容',
            ];
            $data = [
                'type' => 'display_mode_change',
                'display_mode' => $displayMode,
                'mode_name' => $modeNames[$displayMode] ?? '未知模式',
            ];
            self::$server->push($fd, json_encode($data));
            return true;
        }
        return false;
    }

    /**
     * 推送临时内容
     * @param string $mac 设备MAC地址
     * @param array $content 内容信息
     * @param int $duration 显示时长（秒）
     * @return bool
     */
    public static function pushTempContent(string $mac, array $content, int $duration = 0): bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $data = [
                'type' => 'temp_content',
                'data' => [
                    'content_id' => $content['id'] ?? null,
                    'content_type' => $content['content_type'] ?? 1,
                    'content_url' => $content['content_url'] ?? '',
                    'title' => $content['title'] ?? '',
                    'duration' => $duration > 0 ? $duration : ($content['duration'] ?? 0),
                    'thumbnail' => $content['thumbnail'] ?? '',
                    'is_temp' => true,
                ],
            ];
            self::$server->push($fd, json_encode($data));
            return true;
        }
        return false;
    }

    /**
     * 推送批量控制指令
     * @param string $mac 设备MAC地址
     * @param string $action 操作类型
     * @param string $message 消息内容
     * @return bool
     */
    public static function pushBatchControl(string $mac, string $action, string $message = ''): bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $data = [
                'type' => 'batch_control',
                'action' => $action,
                'message' => $message,
                'timestamp' => time(),
            ];
            self::$server->push($fd, json_encode($data));
            return true;
        }
        return false;
    }

    /**
     * 推送 content_response 消息到设备
     * @param string $mac 设备MAC地址
     * @param array $contentResponseData content_response 的 data 字段内容
     * @param string $msg 可选提示
     * @return bool
     */
    public static function pushContentResponse(string $mac, array $contentResponseData, string $msg = '批量内容下发') : bool
    {
        if (!self::$server || !self::$deviceTable) {
            return false;
        }
        $mac = strtolower($mac);
        if (!self::$deviceTable->exist($mac)) {
            return false;
        }
        $fd = self::$deviceTable->get($mac, 'fd');
        if ($fd && self::$server->isEstablished($fd)) {
            $data = [
                'type' => 'content_response',
                'success' => true,
                'msg' => $msg,
                'data' => $contentResponseData
            ];
            self::$server->push($fd, json_encode($data));
            return true;
        }
        return false;
    }
}

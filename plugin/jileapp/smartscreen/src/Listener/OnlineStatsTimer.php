<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Context\ApplicationContext;
use Hyperf\Redis\Redis as RedisClient;
use Hyperf\DbConnection\Db;
use Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher;

#[Listener]
class OnlineStatsTimer implements ListenerInterface
{
    public function listen(): array
    {
        return [AfterWorkerStart::class];
    }

    public function process(object $event): void
    {
        if (! $event instanceof AfterWorkerStart) {
            return;
        }
        // 仅在 worker 0 启动定时器
        if ($event->workerId !== 0) {
            return;
        }

        // 启动时将所有设备置为离线，并清理Redis连接映射（避免脏数据）
        try {
            $affected = Db::table('smart_screen_device')->update(['is_online' => 0]);
            echo sprintf("[WS] Reset all devices to offline, affected=%d\n", (int) $affected);
        } catch (\Throwable $e) {
            echo "[WS] Reset devices offline error: " . $e->getMessage() . "\n";
        }

        try {
            $container = ApplicationContext::getContainer();
            if ($container && $container->has(RedisClient::class)) {
                /** @var RedisClient $redis */
                $redis = $container->get(RedisClient::class);
                $patterns = [
                    'smartscreen:conn:mac:*',
                    'smartscreen:conn:fd:*',
                    'smartscreen:heartbeat:*',
                ];
                foreach ($patterns as $pattern) {
                    $keys = $redis->keys($pattern);
                    if (! empty($keys)) {
                        // del 接收可变参数
                        $redis->del(...$keys);
                    }
                }
                echo "[WS] Cleared Redis connection & heartbeat keys.\n";
            }
        } catch (\Throwable $e) {
            echo "[WS] Clear Redis keys error: " . $e->getMessage() . "\n";
        }

        // 每分钟统计一次在线/离线
        \Swoole\Timer::tick(60_000, function () {
            try {
                $online = 0;
                $totalTracked = 0;
                $macList = [];

                // 优先从 Redis 获取跟踪的连接 MAC 列表（跨进程准确）
                $container = ApplicationContext::getContainer();
                if ($container && $container->has(RedisClient::class)) {
                    /** @var RedisClient $redis */
                    $redis = $container->get(RedisClient::class);
                    $keys = $redis->keys('smartscreen:conn:mac:*');
                    foreach ($keys as $key) {
                        $mac = substr($key, strrpos($key, ':') + 1);
                        if ($mac) {
                            $macList[$mac] = true;
                        }
                    }
                }

                // 回退：若 Redis 为空，则使用本进程内存表
                if (empty($macList)) {
                    $table = DeviceWebSocketPusher::getDeviceTable();
                    if ($table) {
                        foreach ($table as $mac => $row) {
                            $macList[$mac] = true;
                        }
                    }
                }

                foreach (array_keys($macList) as $mac) {
                    $totalTracked++;
                    if (DeviceWebSocketPusher::isOnlineByMac($mac)) {
                        $online++;
                    }
                }

                $offline = max(0, $totalTracked - $online);
                echo sprintf("[WS] Online stats: online=%d, offline=%d, tracked=%d, at=%s\n", $online, $offline, $totalTracked, date('Y-m-d H:i:s'));
            } catch (\Throwable $e) {
                echo "[WS] Online stats error: " . $e->getMessage() . "\n";
            }
        });
    }
}



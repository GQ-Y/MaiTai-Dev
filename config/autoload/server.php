<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */
use Hyperf\Framework\Bootstrap\PipeMessageCallback;
use Hyperf\Framework\Bootstrap\WorkerExitCallback;
use Hyperf\Framework\Bootstrap\WorkerStartCallback;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Swoole\Constant;

return [
    'mode' => \SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => \SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
        // [
        //     'name' => 'ws',
        //     'type' => Server::SERVER_WEBSOCKET,
        //     'host' => '0.0.0.0',
        //     'port' => 9502,
        //     'sock_type' => \SWOOLE_SOCK_TCP,
        //     'callbacks' => [
        //         Event::ON_OPEN => [\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketHandler::class, 'onOpen'],
        //         Event::ON_MESSAGE => [\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketHandler::class, 'onMessage'],
        //         Event::ON_CLOSE => [\Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketHandler::class, 'onClose'],
        //     ],
        // ],
        // [
        //     'name' => 'notification-ws',
        //     'type' => Server::SERVER_WEBSOCKET,
        //     'host' => '0.0.0.0',
        //     'port' => 9509,
        //     'sock_type' => \SWOOLE_SOCK_TCP,
        //     'callbacks' => [
        //         Event::ON_OPEN => [\Plugin\Jileapp\NotificationCenter\WebSocket\NotificationWebSocketHandler::class, 'onOpen'],
        //         Event::ON_MESSAGE => [\Plugin\Jileapp\NotificationCenter\WebSocket\NotificationWebSocketHandler::class, 'onMessage'],
        //         Event::ON_CLOSE => [\Plugin\Jileapp\NotificationCenter\WebSocket\NotificationWebSocketHandler::class, 'onClose'],
        //     ],
        // ],
    ],
    'settings' => [
        // 开启外部可以访问
        Constant::OPTION_ENABLE_STATIC_HANDLER => true,
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_WORKER_NUM => env('APP_DEBUG') ? 1 : swoole_cpu_num(),
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/hyperf.pid',
        Constant::OPTION_DOCUMENT_ROOT => BASE_PATH . '/storage',
        Constant::OPTION_OPEN_TCP_NODELAY => true,
        Constant::OPTION_MAX_COROUTINE => 100000,
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_MAX_REQUEST => 100000,
        Constant::OPTION_UPLOAD_MAX_FILESIZE => 10 * 1024 * 1024 * 10000,
        Constant::OPTION_HTTP_INDEX_FILES => ['index.html'],
        Constant::OPTION_SOCKET_BUFFER_SIZE => 3 * 1024 * 1024,
        // 上传最大为4M
        Constant::OPTION_PACKAGE_MAX_LENGTH => 4 * 1024 * 1024 * 10000,
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [WorkerStartCallback::class, 'onWorkerStart'],
        Event::ON_PIPE_MESSAGE => [PipeMessageCallback::class, 'onPipeMessage'],
        Event::ON_WORKER_EXIT => [WorkerExitCallback::class, 'onWorkerExit'],
    ],
];

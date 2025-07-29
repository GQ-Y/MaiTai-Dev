<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputOption;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\WebSocket\Frame as SwooleFrame;
use Plugin\Jileapp\Smartscreen\WebSocket\DeviceWebSocketPusher;

#[Command]
class SmartScreenWebSocketServerCommand extends HyperfCommand
{
    /**
     * WebSocket服务端主命令，负责智慧屏设备实时通信。
     * 支持设备心跳、内容推送、指令下发等常规场景。
     * 严格遵循MineAdmin插件开发规范。
     */
    public function __construct()
    {
        parent::__construct('smartscreen:websocket-server');
    }

    public function configure()
    {
        $this->setDescription('启动智慧屏WebSocket服务端');
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, '监听地址', '0.0.0.0');
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL, '监听端口', 9502);
    }

    public function handle()
    {
        $host = $this->input->getOption('host');
        $port = (int)$this->input->getOption('port');

        $server = new WebSocketServer($host, $port);
        $deviceTable = new \Swoole\Table(1024);
        $deviceTable->column('fd', \Swoole\Table::TYPE_INT);
        $deviceTable->column('mac', \Swoole\Table::TYPE_STRING, 32);
        $deviceTable->column('active', \Swoole\Table::TYPE_INT); // 1=激活,0=未激活
        $deviceTable->column('last_heartbeat', \Swoole\Table::TYPE_INT);
        $deviceTable->create();

        // 注入推送服务静态属性
        DeviceWebSocketPusher::$server = $server;
        DeviceWebSocketPusher::$deviceTable = $deviceTable;

        $server->on('open', function (WebSocketServer $server, SwooleRequest $request) {
            echo "[open] 客户端#{$request->fd} 连接成功\n";
        });

        $server->on('message', function (WebSocketServer $server, SwooleFrame $frame) use ($deviceTable) {
            $data = json_decode($frame->data, true);
            if (!is_array($data) || !isset($data['type'])) {
                $server->push($frame->fd, json_encode(['type' => 'error', 'msg' => '消息格式错误']));
                return;
            }
            switch ($data['type']) {
                case 'register':
                    // 设备注册，必须有mac
                    if (empty($data['mac'])) {
                        $server->push($frame->fd, json_encode(['type' => 'register_ack', 'success' => false, 'msg' => '缺少mac']));
                        return;
                    }
                    $mac = strtolower($data['mac']);
                    $deviceTable->set($mac, [
                        'fd' => $frame->fd,
                        'mac' => $mac,
                        'active' => 0, // 默认未激活
                        'last_heartbeat' => time(),
                    ]);
                    $server->push($frame->fd, json_encode(['type' => 'register_ack', 'success' => true, 'active' => 0, 'msg' => '注册成功，待激活']));
                    echo "[register] 设备mac={$mac} fd={$frame->fd} 注册\n";
                    break;
                case 'heartbeat':
                    // 心跳包，需带mac
                    if (empty($data['mac'])) {
                        $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => false, 'msg' => '缺少mac']));
                        return;
                    }
                    $mac = strtolower($data['mac']);
                    if ($deviceTable->exist($mac)) {
                        $deviceTable->set($mac, [
                            'last_heartbeat' => time(),
                        ]);
                        $active = $deviceTable->get($mac, 'active');
                        $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => true, 'active' => $active, 'msg' => '心跳成功']));
                        echo "[heartbeat] 设备mac={$mac} 心跳\n";
                    } else {
                        $server->push($frame->fd, json_encode(['type' => 'heartbeat_ack', 'success' => false, 'msg' => '设备未注册']));
                    }
                    break;
                default:
                    $server->push($frame->fd, json_encode(['type' => 'error', 'msg' => '未知消息类型']));
            }
        });

        $server->on('close', function (WebSocketServer $server, $fd) use ($deviceTable) {
            // 查找并移除fd对应的mac
            foreach ($deviceTable as $mac => $row) {
                if ($row['fd'] === $fd) {
                    $deviceTable->del($mac);
                    echo "[close] 设备mac={$mac} fd={$fd} 下线\n";
                    break;
                }
            }
        });

        echo "智慧屏WebSocket服务启动：ws://{$host}:{$port}\n";
        $server->start();
    }
}

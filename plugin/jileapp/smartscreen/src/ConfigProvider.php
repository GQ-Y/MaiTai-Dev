<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen;

class ConfigProvider
{
    public function __invoke()
    {
        // Initial configuration
        return [
            // 注解扫描路径
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 依赖注入配置
            'dependencies' => [],
            // 命令行工具配置
            'commands' => [],
            // 事件监听器配置
            'listeners' => [
                \Plugin\Jileapp\Smartscreen\Listener\OnlineStatsTimer::class,
            ],
            // 发布配置文件机制（如有默认配置文件可补充）
            'publish' => [
                // 示例：如有默认配置文件可在publish目录下添加
                // [
                //     'id' => 'smartscreen-config',
                //     'description' => '智慧屏插件默认配置',
                //     'source' => __DIR__ . '/../publish/smartscreen.php',
                //     'destination' => BASE_PATH . '/config/autoload/smartscreen.php',
                // ],
            ],
        ];
    }
}
<?php

declare(strict_types=1);

/**
 * 通知中心插件配置文件
 * 
 * @description 通知中心插件的各项配置参数
 * @author GQ
 * @date 2024-12-24
 */

return [
    'notification_center' => [
        
        // WebSocket实时推送配置
        'enable_websocket' => true,
        
        // 邮件通知配置（需要配置邮件服务）
        'enable_email_notification' => false,
        
        // 短信通知配置（需要配置短信服务）
        'enable_sms_notification' => false,
        
        // 消息默认优先级：1=普通，2=重要，3=紧急
        'default_priority' => 1,
        
        // 自动标记已读延迟时间（秒）
        'auto_mark_read_delay' => 30,
        
        // 数据清理周期（天）- 清理已删除的数据
        'cleanup_days' => 90,
        
        // API调用是否需要密钥验证
        'api_key_required' => true,
        
        // 消息推送配置
        'push' => [
            // 批量推送单次最大数量
            'batch_size' => 100,
            
            // 推送失败重试次数
            'retry_times' => 3,
            
            // 推送超时时间（秒）
            'timeout' => 10,
        ],
        
        // 通知配置
        'notification' => [
            // 通知默认有效期（天）
            'default_expire_days' => 30,
            
            // 公告类通知默认有效期（天）
            'announcement_expire_days' => 90,
        ],
        
        // 缓存配置
        'cache' => [
            // 未读统计缓存时间（秒）
            'unread_count_ttl' => 300,
            
            // 用户消息列表缓存时间（秒）
            'user_messages_ttl' => 600,
        ],
        
    ],
]; 
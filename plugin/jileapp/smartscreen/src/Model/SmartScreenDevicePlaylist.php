<?php

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * 智慧屏设备-播放列表关联表
 * @property int $id
 * @property int $device_id
 * @property int $playlist_id
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class SmartScreenDevicePlaylist extends Model
{
    protected ?string $table = 'smart_screen_device_playlist';

    protected array $fillable = [
        'device_id', 'playlist_id', 'sort_order', 'created_at', 'updated_at'
    ];

    protected array $casts = [
        'device_id' => 'integer',
        'playlist_id' => 'integer',
        'sort_order' => 'integer',
    ];

    // 关联关系可后续补充
} 
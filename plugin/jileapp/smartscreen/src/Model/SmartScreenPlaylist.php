<?php

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * 智慧屏播放列表表
 * @property int $id
 * @property string $name
 * @property int $play_mode
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class SmartScreenPlaylist extends Model
{
    protected ?string $table = 'smart_screen_playlist';

    protected array $fillable = [
        'name', 'play_mode', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected array $casts = [
        'play_mode' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // 关联关系可后续补充
} 
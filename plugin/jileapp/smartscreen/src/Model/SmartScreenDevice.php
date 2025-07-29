<?php

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * 智慧屏设备表
 * @property int $id
 * @property string $mac_address
 * @property string $device_name
 * @property int $status
 * @property int $is_online
 * @property int $display_mode 播放策略：1播放列表优先/2直接内容优先/3仅播放列表/4仅直接内容
 * @property int|null $current_content_id
 * @property string|null $last_online_time
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class SmartScreenDevice extends Model
{
    protected ?string $table = 'smart_screen_device';

    protected array $fillable = [
        'mac_address', 'device_name', 'status', 'is_online', 'display_mode', 'current_content_id', 'last_online_time', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected array $casts = [
        'status' => 'integer',
        'is_online' => 'integer',
        'display_mode' => 'integer',
        'current_content_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * 设备关联的当前内容
     */
    public function currentContent()
    {
        return $this->belongsTo(SmartScreenContent::class, 'current_content_id', 'id');
    }

    /**
     * 设备关联的播放列表
     */
    public function playlists()
    {
        return $this->belongsToMany(
            SmartScreenPlaylist::class,
            'smart_screen_device_playlist',
            'device_id',
            'playlist_id'
        )->withPivot(['sort_order', 'created_at', 'updated_at'])
         ->withTimestamps();
    }

    /**
     * 设备的分组关联
     */
    public function groupRelations()
    {
        return $this->hasMany(SmartScreenDeviceGroupRelation::class, 'device_id', 'id');
    }

    /**
     * 设备所属的分组（通过关联表）
     */
    public function groups()
    {
        return $this->belongsToMany(
            SmartScreenDeviceGroup::class,
            'smart_screen_device_group_relation',
            'device_id',
            'group_id'
        )->withPivot(['sort_order', 'created_at', 'updated_at'])
         ->withTimestamps();
    }

    /**
     * 获取在线设备
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', 1);
    }

    /**
     * 获取已激活设备
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
} 
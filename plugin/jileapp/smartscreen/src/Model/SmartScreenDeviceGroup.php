<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * 智慧屏设备分组模型
 */
class SmartScreenDeviceGroup extends Model
{
    protected ?string $table = 'smart_screen_device_group';

    protected array $fillable = [
        'name',
        'description', 
        'color',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 分组下的设备关联
     */
    public function deviceRelations()
    {
        return $this->hasMany(SmartScreenDeviceGroupRelation::class, 'group_id', 'id');
    }

    /**
     * 分组下的设备（通过关联表）
     */
    public function devices()
    {
        return $this->belongsToMany(
            SmartScreenDevice::class,
            'smart_screen_device_group_relation',
            'group_id',
            'device_id'
        )->withPivot(['sort_order', 'created_at', 'updated_at'])
         ->withTimestamps();
    }

    /**
     * 获取启用的分组
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    /**
     * 按排序获取
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
} 
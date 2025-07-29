<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * 智慧屏设备分组关联模型
 */
class SmartScreenDeviceGroupRelation extends Model
{
    protected ?string $table = 'smart_screen_device_group_relation';

    protected array $fillable = [
        'device_id',
        'group_id',
        'sort_order',
    ];

    protected array $casts = [
        'id' => 'integer',
        'device_id' => 'integer',
        'group_id' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联的设备
     */
    public function device()
    {
        return $this->belongsTo(SmartScreenDevice::class, 'device_id', 'id');
    }

    /**
     * 关联的分组
     */
    public function group()
    {
        return $this->belongsTo(SmartScreenDeviceGroup::class, 'group_id', 'id');
    }

    /**
     * 按分组内排序获取
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
} 
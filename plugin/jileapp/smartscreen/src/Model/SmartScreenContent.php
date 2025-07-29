<?php

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * 智慧屏内容表
 * @property int $id
 * @property string $title
 * @property int $content_type
 * @property string $content_url
 * @property string|null $thumbnail
 * @property int $duration
 * @property int $status
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class SmartScreenContent extends Model
{
    protected ?string $table = 'smart_screen_content';

    protected array $fillable = [
        'title', 'content_type', 'content_url', 'thumbnail', 'duration', 'status', 'sort_order', 'created_by', 'updated_by', 'created_at', 'updated_at'
    ];

    protected array $casts = [
        'content_type' => 'integer',
        'duration' => 'integer',
        'status' => 'integer',
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // 关联关系可后续补充
} 
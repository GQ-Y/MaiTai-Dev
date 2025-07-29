<?php

namespace Plugin\Jileapp\Smartscreen\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;

/**
 * 智慧屏播放列表-内容关联表
 * @property int $id
 * @property int $playlist_id
 * @property int $content_id
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class SmartScreenPlaylistContent extends Model
{
    protected ?string $table = 'smart_screen_playlist_content';

    protected array $fillable = [
        'playlist_id', 'content_id', 'sort_order', 'created_at', 'updated_at'
    ];

    protected array $casts = [
        'playlist_id' => 'integer',
        'content_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 关联播放列表
     */
    public function playlist(): BelongsTo
    {
        return $this->belongsTo(SmartScreenPlaylist::class, 'playlist_id', 'id');
    }

    /**
     * 关联内容
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(SmartScreenContent::class, 'content_id', 'id');
    }
} 
<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Schema;

use Plugin\Jileapp\Smartscreen\Model\SmartScreenPlaylistContent;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'SmartScreenPlaylistContentSchema')]
final class SmartScreenPlaylistContentSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'playlist_id', title: '播放列表ID', type: 'int')]
    public ?int $playlistId;

    #[Property(property: 'content_id', title: '内容ID', type: 'int')]
    public ?int $contentId;

    #[Property(property: 'sort_order', title: '排序', type: 'int')]
    public ?int $sortOrder;

    #[Property(property: 'created_at', title: '创建时间', type: 'string')]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string')]
    public ?string $updatedAt;

    #[Property(property: 'deleted_at', title: '删除时间', type: 'string')]
    public ?string $deletedAt;

    public function __construct(SmartScreenPlaylistContent $model)
    {
        $this->id = $model->id;
        $this->playlistId = $model->playlist_id;
        $this->contentId = $model->content_id;
        $this->sortOrder = $model->sort_order;
        $this->createdAt = $model->created_at;
        $this->updatedAt = $model->updated_at;
        $this->deletedAt = $model->deleted_at;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'playlist_id' => $this->playlistId,
            'content_id' => $this->contentId,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
} 
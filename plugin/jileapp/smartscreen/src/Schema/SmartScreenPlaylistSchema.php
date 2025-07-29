<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Schema;

use Plugin\Jileapp\Smartscreen\Model\SmartScreenPlaylist;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'SmartScreenPlaylistSchema')]
final class SmartScreenPlaylistSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'name', title: '播放列表名称', type: 'string')]
    public ?string $name;

    #[Property(property: 'play_mode', title: '播放模式', type: 'int')]
    public ?int $playMode;

    #[Property(property: 'status', title: '状态', type: 'int')]
    public ?int $status;

    #[Property(property: 'created_by', title: '创建者', type: 'int')]
    public ?int $createdBy;

    #[Property(property: 'updated_by', title: '更新者', type: 'int')]
    public ?int $updatedBy;

    #[Property(property: 'created_at', title: '创建时间', type: 'string')]
    public ?string $createdAt;

    #[Property(property: 'updated_at', title: '更新时间', type: 'string')]
    public ?string $updatedAt;

    #[Property(property: 'deleted_at', title: '删除时间', type: 'string')]
    public ?string $deletedAt;

    public function __construct(SmartScreenPlaylist $model)
    {
        $this->id = $model->id;
        $this->name = $model->name;
        $this->playMode = $model->play_mode;
        $this->status = $model->status;
        $this->createdBy = $model->created_by;
        $this->updatedBy = $model->updated_by;
        $this->createdAt = $model->created_at;
        $this->updatedAt = $model->updated_at;
        $this->deletedAt = $model->deleted_at;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'play_mode' => $this->playMode,
            'status' => $this->status,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
} 
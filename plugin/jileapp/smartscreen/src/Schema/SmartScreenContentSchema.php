<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Schema;

use Plugin\Jileapp\Smartscreen\Model\SmartScreenContent;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'SmartScreenContentSchema')]
final class SmartScreenContentSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'title', title: '内容标题', type: 'string')]
    public ?string $title;

    #[Property(property: 'content_type', title: '内容类型', type: 'int', description: '1网页/2图片/3视频')]
    public ?int $contentType;

    #[Property(property: 'content_url', title: '内容URL', type: 'string')]
    public ?string $contentUrl;

    #[Property(property: 'thumbnail', title: '缩略图', type: 'string')]
    public ?string $thumbnail;

    #[Property(property: 'duration', title: '播放时长(秒)', type: 'int')]
    public ?int $duration;

    #[Property(property: 'status', title: '状态', type: 'int', description: '0禁用/1启用')]
    public ?int $status;

    #[Property(property: 'sort_order', title: '排序', type: 'int')]
    public ?int $sortOrder;

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

    public function __construct(SmartScreenContent $model)
    {
        $this->id = $model->id;
        $this->title = $model->title;
        $this->contentType = $model->content_type;
        $this->contentUrl = $model->content_url;
        $this->thumbnail = $model->thumbnail;
        $this->duration = $model->duration;
        $this->status = $model->status;
        $this->sortOrder = $model->sort_order;
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
            'title' => $this->title,
            'content_type' => $this->contentType,
            'content_url' => $this->contentUrl,
            'thumbnail' => $this->thumbnail,
            'duration' => $this->duration,
            'status' => $this->status,
            'sort_order' => $this->sortOrder,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
} 
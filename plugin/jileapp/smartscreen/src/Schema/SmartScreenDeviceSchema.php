<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Schema;

use Plugin\Jileapp\Smartscreen\Model\SmartScreenDevice;
use Hyperf\Swagger\Annotation\Property;
use Hyperf\Swagger\Annotation\Schema;

#[Schema(title: 'SmartScreenDeviceSchema')]
final class SmartScreenDeviceSchema implements \JsonSerializable
{
    #[Property(property: 'id', title: '主键', type: 'int')]
    public ?int $id;

    #[Property(property: 'mac_address', title: 'MAC地址', type: 'string')]
    public ?string $macAddress;

    #[Property(property: 'device_name', title: '设备名称', type: 'string')]
    public ?string $deviceName;

    #[Property(property: 'status', title: '设备状态', type: 'int')]
    public ?int $status;

    #[Property(property: 'is_online', title: '在线状态', type: 'int')]
    public ?int $isOnline;

    #[Property(property: 'display_mode', title: '显示模式', type: 'int')]
    public ?int $displayMode;

    #[Property(property: 'current_content_id', title: '当前内容ID', type: 'int')]
    public ?int $currentContentId;

    #[Property(property: 'last_online_time', title: '最后在线时间', type: 'string')]
    public ?string $lastOnlineTime;

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

    public function __construct(SmartScreenDevice $model)
    {
        $this->id = $model->id;
        $this->macAddress = $model->mac_address;
        $this->deviceName = $model->device_name;
        $this->status = $model->status;
        $this->isOnline = $model->is_online;
        $this->displayMode = $model->display_mode;
        $this->currentContentId = $model->current_content_id;
        $this->lastOnlineTime = $model->last_online_time;
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
            'mac_address' => $this->macAddress,
            'device_name' => $this->deviceName,
            'status' => $this->status,
            'is_online' => $this->isOnline,
            'display_mode' => $this->displayMode,
            'current_content_id' => $this->currentContentId,
            'last_online_time' => $this->lastOnlineTime,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }
} 
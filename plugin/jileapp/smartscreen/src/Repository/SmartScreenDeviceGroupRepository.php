<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenDeviceGroup;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenDeviceGroupRelation;

class SmartScreenDeviceGroupRepository extends IRepository
{
    public function __construct(
        protected readonly SmartScreenDeviceGroup $model
    ) {}

    /**
     * 获取分页列表
     */
    public function getPageList(?array $params = [], bool $isScope = true): array
    {
        $model = $this->model->newQuery();
        
        if ($isScope) {
            $model = $model->enabled()->ordered();
        }

        // 搜索条件
        if (!empty($params['name'])) {
            $model->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $model->where('status', $params['status']);
        }

        if (!empty($params['color'])) {
            $model->where('color', $params['color']);
        }

        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['pageSize'] ?? 15);

        $total = $model->count();
        $list = $model->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        // 手动计算每个分组的设备数量并获取设备列表
        foreach ($list as &$group) {
            // 获取分组的设备列表
            $devices = SmartScreenDeviceGroupRelation::join('smart_screen_device', 'smart_screen_device_group_relation.device_id', '=', 'smart_screen_device.id')
                ->where('smart_screen_device_group_relation.group_id', $group['id']) // 确保设备未被软删除
                ->select([
                    'smart_screen_device.id',
                    'smart_screen_device.device_name',
                    'smart_screen_device.mac_address',
                    'smart_screen_device.status',
                    'smart_screen_device.is_online',
                    'smart_screen_device_group_relation.sort_order',
                    'smart_screen_device.created_at as device_created_at'
                ])
                ->orderBy('smart_screen_device_group_relation.sort_order')
                ->orderBy('smart_screen_device.device_name')
                ->get()
                ->toArray();
                
            $group['device_count'] = count($devices);
            $group['devices'] = $devices;
        }

        return [
            'total' => $total,
            'list' => $list,
            'page' => $page,
            'pageSize' => $pageSize,
        ];
    }

    /**
     * 获取所有启用的分组
     */
    public function getAllEnabled(): array
    {
        return $this->model->newQuery()->enabled()->ordered()->get()->toArray();
    }

    /**
     * 根据ID获取分组详情，包含设备信息
     */
    public function getGroupWithDevices(int $id): ?array
    {
        $group = $this->model->newQuery()->with(['devices' => function ($query) {
            $query->select([
                'smart_screen_device.id', 
                'smart_screen_device.device_name', 
                'smart_screen_device.mac_address', 
                'smart_screen_device.status', 
                'smart_screen_device.is_online'
            ])
            ->orderBy('smart_screen_device.device_name');
        }])->find($id);

        return $group ? $group->toArray() : null;
    }

    /**
     * 创建分组
     */
    public function create(array $data): array
    {
        $group = $this->model->newQuery()->create($data);
        return $group->toArray();
    }

    /**
     * 更新分组
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->newQuery()->where('id', $id)->update($data) > 0;
    }

    /**
     * 删除分组（软删除）
     */
    public function delete(int $id): bool
    {
        $group = $this->model->newQuery()->find($id);
        if (!$group) {
            return false;
        }

        // 删除分组时同时删除设备关联关系（硬删除）
        SmartScreenDeviceGroupRelation::where('group_id', $id)->delete();
        
        return $group->delete();
    }

    /**
     * 批量删除分组
     */
    public function deleteByIds(array $ids): bool
    {
        // 删除所有相关的设备关联关系（硬删除）
        SmartScreenDeviceGroupRelation::whereIn('group_id', $ids)->delete();
        
        return $this->model->newQuery()->whereIn('id', $ids)->delete() > 0;
    }

    /**
     * 获取分组的设备列表
     */
    public function getGroupDevices(int $groupId): array
    {
        $group = $this->model->newQuery()->with(['devices' => function ($query) {
            $query->select([
                'smart_screen_device.id', 
                'smart_screen_device.device_name', 
                'smart_screen_device.mac_address', 
                'smart_screen_device.status', 
                'smart_screen_device.is_online'
            ])
            ->orderBy('smart_screen_device_group_relation.sort_order')
            ->orderBy('smart_screen_device.device_name');
        }])->find($groupId);

        return $group ? $group->devices->toArray() : [];
    }

    /**
     * 添加设备到分组
     */
    public function addDevicesToGroup(int $groupId, array $deviceIds): bool
    {
        // 过滤掉已经存在的关联关系
        $existingDeviceIds = SmartScreenDeviceGroupRelation::where('group_id', $groupId)
            ->whereIn('device_id', $deviceIds)
            ->pluck('device_id')
            ->toArray();
            
        $newDeviceIds = array_diff($deviceIds, $existingDeviceIds);
        
        if (empty($newDeviceIds)) {
            return true; // 没有新设备需要添加
        }
        
        $relations = [];
        $maxSortOrder = SmartScreenDeviceGroupRelation::where('group_id', $groupId)
            ->max('sort_order') ?? 0;

        foreach ($newDeviceIds as $index => $deviceId) {
            $relations[] = [
                'group_id' => $groupId,
                'device_id' => $deviceId,
                'sort_order' => $maxSortOrder + $index + 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        return SmartScreenDeviceGroupRelation::insert($relations);
    }

    /**
     * 从分组中移除设备
     */
    public function removeDevicesFromGroup(int $groupId, array $deviceIds): bool
    {
        // 直接硬删除关联关系
        return SmartScreenDeviceGroupRelation::where('group_id', $groupId)
            ->whereIn('device_id', $deviceIds)
            ->delete() > 0;
    }

    /**
     * 更新设备在分组中的排序
     */
    public function updateDeviceSort(int $groupId, array $sortData): bool
    {
        foreach ($sortData as $item) {
            SmartScreenDeviceGroupRelation::where('group_id', $groupId)
                ->where('device_id', $item['device_id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return true;
    }

    /**
     * 获取设备所属的分组
     */
    public function getDeviceGroups(int $deviceId): array
    {
        return SmartScreenDeviceGroupRelation::with(['group' => function ($query) {
            $query->select(['id', 'name', 'color', 'status']);
        }])
        ->where('device_id', $deviceId)
        ->get()
        ->pluck('group')
        ->filter()
        ->toArray();
    }

    /**
     * 检查设备是否已在分组中
     */
    public function isDeviceInGroup(int $groupId, int $deviceId): bool
    {
        return SmartScreenDeviceGroupRelation::where('group_id', $groupId)
            ->where('device_id', $deviceId)
            ->exists();
    }

    /**
     * 批量检查设备是否已在分组中
     */
    public function areDevicesInGroup(int $groupId, array $deviceIds): array
    {
        $existingRelations = SmartScreenDeviceGroupRelation::where('group_id', $groupId)
            ->whereIn('device_id', $deviceIds)
            ->pluck('device_id')
            ->toArray();

        $result = [];
        foreach ($deviceIds as $deviceId) {
            $result[$deviceId] = in_array($deviceId, $existingRelations);
        }

        return $result;
    }

    /**
     * 获取分组统计信息
     */
    public function getGroupStats(): array
    {
        $totalGroups = $this->model->newQuery()->count();
        $enabledGroups = $this->model->newQuery()->enabled()->count();
        $totalDeviceRelations = SmartScreenDeviceGroupRelation::count();
        
        return [
            'total_groups' => $totalGroups,
            'enabled_groups' => $enabledGroups,
            'disabled_groups' => $totalGroups - $enabledGroups,
            'total_device_relations' => $totalDeviceRelations,
        ];
    }
} 
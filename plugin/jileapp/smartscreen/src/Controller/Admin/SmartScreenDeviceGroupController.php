<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Controller\Admin;

use Plugin\Jileapp\Smartscreen\Controller\AbstractController;
use App\Http\Common\Result;
use App\Http\Common\ResultCode;
use Plugin\Jileapp\Smartscreen\Service\SmartScreenDeviceGroupService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;

#[Controller(prefix: 'admin/plugin/smart-screen/device-group')]
class SmartScreenDeviceGroupController extends AbstractController
{
    #[Inject]
    protected SmartScreenDeviceGroupService $service;

    /**
     * 获取分组分页列表
     */
    #[GetMapping('list')]
    public function index(): Result
    {
        try {
            $params = $this->request->all();
            $result = $this->service->getPageList($params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取所有启用的分组（用于选择器）
     */
    #[GetMapping('all-enabled')]
    public function getAllEnabled(): Result
    {
        try {
            $result = $this->service->getAllEnabled();
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分组统计信息
     */
    #[GetMapping('statistics')]
    public function getStats(): Result
    {
        try {
            $result = $this->service->getGroupStats();
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分组详情，包含设备信息
     */
    #[GetMapping('{id}')]
    public function show(int $id): Result
    {
        try {
            $result = $this->service->getGroupWithDevices($id);
            if (!$result) {
                return $this->json(ResultCode::NOT_FOUND, [], '分组不存在');
            }
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 创建分组
     */
    #[PostMapping('')]
    public function store(): Result
    {
        try {
            $data = $this->request->all();
            $result = $this->service->create($data);
            return $this->success($result, '分组创建成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('创建分组失败：' . $e->getMessage());
        }
    }

    /**
     * 更新分组
     */
    #[PutMapping('{id}')]
    public function update(int $id): Result
    {
        try {
            $data = $this->request->all();
            $result = $this->service->update($id, $data);
            if (!$result) {
                return $this->json(ResultCode::NOT_FOUND, [], '分组不存在');
            }
            return $this->success([], '分组更新成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('更新分组失败：' . $e->getMessage());
        }
    }

    /**
     * 删除分组
     */
    #[DeleteMapping('{id}')]
    public function destroy(int $id): Result
    {
        try {
            $result = $this->service->delete($id);
            if (!$result) {
                return $this->json(ResultCode::NOT_FOUND, [], '分组不存在');
            }
            return $this->success([], '分组删除成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('删除分组失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除分组
     */
    #[DeleteMapping('')]
    public function batchDestroy(): Result
    {
        try {
            $data = $this->request->all();
            $ids = $data['ids'] ?? [];
            
            if (empty($ids)) {
                return $this->error('请选择要删除的分组');
            }

            $result = $this->service->deleteByIds($ids);
            return $this->success([], '批量删除成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取分组的设备列表
     */
    #[GetMapping('{id}/devices')]
    public function getGroupDevices(int $id): Result
    {
        try {
            $result = $this->service->getGroupDevices($id);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取可添加到分组的设备列表
     */
    #[GetMapping('{id}/available-devices')]
    public function getAvailableDevices(int $id): Result
    {
        try {
            $result = $this->service->getAvailableDevicesForGroup($id);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 添加设备到分组
     */
    #[PostMapping('{id}/devices')]
    public function addDevicesToGroup(int $id): Result
    {
        try {
            $data = $this->request->all();
            $deviceIds = $data['device_ids'] ?? [];
            
            if (empty($deviceIds)) {
                return $this->error('请选择要添加的设备');
            }

            $result = $this->service->addDevicesToGroup($id, $deviceIds);
            
            $message = "成功添加 {$result['added_count']} 个设备";
            if (!empty($result['invalid_devices'])) {
                $message .= '，以下设备未添加：' . implode(', ', $result['invalid_devices']);
            }

            return $this->success($result, $message);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('添加设备失败：' . $e->getMessage());
        }
    }

    /**
     * 从分组中移除设备
     */
    #[DeleteMapping('{id}/devices')]
    public function removeDevicesFromGroup(int $id): Result
    {
        try {
            $data = $this->request->all();
            $deviceIds = $data['device_ids'] ?? [];
            
            if (empty($deviceIds)) {
                return $this->error('请选择要移除的设备');
            }

            $result = $this->service->removeDevicesFromGroup($id, $deviceIds);
            return $this->success([], '设备移除成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('移除设备失败：' . $e->getMessage());
        }
    }

    /**
     * 更新设备在分组中的排序
     */
    #[PutMapping('{id}/devices/sort')]
    public function updateDeviceSort(int $id): Result
    {
        try {
            $data = $this->request->all();
            $sortData = $data['sort_data'] ?? [];
            
            if (empty($sortData)) {
                return $this->error('排序数据不能为空');
            }

            $result = $this->service->updateDeviceSort($id, $sortData);
            return $this->success([], '排序更新成功');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('更新排序失败：' . $e->getMessage());
        }
    }

    /**
     * 获取设备所属的分组
     */
    #[GetMapping('device/{deviceId}/groups')]
    public function getDeviceGroups(int $deviceId): Result
    {
        try {
            $result = $this->service->getDeviceGroups($deviceId);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量设置设备分组
     */
    #[PostMapping('batch-set-device-groups')]
    public function batchSetDeviceGroups(): Result
    {
        try {
            $data = $this->request->all();
            $deviceIds = $data['device_ids'] ?? [];
            $groupIds = $data['group_ids'] ?? [];
            
            if (empty($deviceIds)) {
                return $this->error('请选择要设置的设备');
            }

            $result = $this->service->batchSetDeviceGroups($deviceIds, $groupIds);
            
            $successCount = count(array_filter($result, fn($r) => $r['success']));
            $failCount = count($result) - $successCount;
            
            $message = "成功设置 {$successCount} 个设备";
            if ($failCount > 0) {
                $message .= "，{$failCount} 个设备设置失败";
            }

            return $this->success($result, $message);
        } catch (\Exception $e) {
            return $this->error('批量设置失败：' . $e->getMessage());
        }
    }

    /**
     * 批量设置分组内设备的显示内容
     */
    #[PostMapping('{id}/batch-set-content')]
    public function batchSetGroupDevicesContent(int $id): Result
    {
        try {
            $data = $this->request->all();
            $contentId = $data['content_id'] ?? null;
            
            $result = $this->service->batchSetGroupDevicesContent($id, $contentId);
            
            if ($result['success_count'] === 0) {
                return $this->error($result['message']);
            }
            
            $message = "成功为 {$result['success_count']} 台设备设置内容";
            if ($result['fail_count'] > 0) {
                $message .= "，{$result['fail_count']} 台设备设置失败";
            }
            
            return $this->success($result, $message);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('批量设置内容失败：' . $e->getMessage());
        }
    }

    /**
     * 批量设置分组内设备的播放列表
     */
    #[PostMapping('{id}/batch-set-playlist')]
    public function batchSetGroupDevicesPlaylist(int $id): Result
    {
        try {
            $data = $this->request->all();
            $playlistIds = $data['playlist_ids'] ?? [];
            
            $result = $this->service->batchSetGroupDevicesPlaylist($id, $playlistIds);
            
            if ($result['success_count'] === 0) {
                return $this->error($result['message']);
            }
            
            $message = "成功为 {$result['success_count']} 台设备设置播放列表";
            if ($result['fail_count'] > 0) {
                $message .= "，{$result['fail_count']} 台设备设置失败";
            }
            
            return $this->success($result, $message);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error('批量设置播放列表失败：' . $e->getMessage());
        }
    }

} 
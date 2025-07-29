<?php

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenDevicePlaylistRepository;

class SmartScreenDevicePlaylistService extends IService
{
    /**
     * @var SmartScreenDevicePlaylistRepository
     */
    public $repository;

    public function __construct()
    {
        $this->repository = new SmartScreenDevicePlaylistRepository();
    }

    /**
     * 新增关联，参数校验与异常处理
     */
    public function create(array $data): mixed
    {
        if (empty($data['device_id']) || empty($data['playlist_id'])) {
            throw new BusinessException(ResultCode::FAIL, '设备ID和播放列表ID不能为空');
        }
        return parent::create($data);
    }

    /**
     * 更新关联，参数校验与异常处理
     */
    public function updateById(mixed $id, array $data): mixed
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }

        $model = $this->repository->findById($id);
        if (!$model) {
            throw new BusinessException(ResultCode::FAIL, '关联记录不存在');
        }

        $model->fill($data);
        return $model->save();
    }

    // 其它业务方法可按需扩展
} 
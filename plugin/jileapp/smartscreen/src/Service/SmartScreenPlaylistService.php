<?php

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenPlaylistRepository;

class SmartScreenPlaylistService extends IService
{
    /**
     * @var SmartScreenPlaylistRepository
     */
    public $repository;

    public function __construct()
    {
        $this->repository = new SmartScreenPlaylistRepository();
    }

    /**
     * 新增播放列表，参数校验与异常处理
     */
    public function create(array $data): mixed
    {
        if (empty($data['name']) || empty($data['play_mode'])) {
            throw new BusinessException(ResultCode::FAIL, '播放列表名称和播放模式不能为空');
        }
        return parent::create($data);
    }

    /**
     * 更新播放列表，参数校验与异常处理
     */
    public function updateById(mixed $id, array $data): mixed
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }
        
        $model = $this->repository->findById($id);
        if (!$model) {
            throw new BusinessException(ResultCode::FAIL, '播放列表不存在');
        }

        $model->fill($data);
        return $model->save();
    }

    // 其它业务方法可按需扩展
} 
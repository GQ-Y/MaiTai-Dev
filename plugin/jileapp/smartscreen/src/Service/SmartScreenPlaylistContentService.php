<?php

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenPlaylistContentRepository;

class SmartScreenPlaylistContentService extends IService
{
    /**
     * @var SmartScreenPlaylistContentRepository
     */
    public $repository;

    public function __construct()
    {
        $this->repository = new SmartScreenPlaylistContentRepository();
    }

    /**
     * 新增关联，参数校验与异常处理
     */
    public function create(array $data): mixed
    {
        if (empty($data['playlist_id']) || empty($data['content_id'])) {
            throw new BusinessException(ResultCode::FAIL, '播放列表ID和内容ID不能为空');
        }

        // 检查是否存在记录
        $existingRecord = $this->repository->getModel()
            ->where('playlist_id', $data['playlist_id'])
            ->where('content_id', $data['content_id'])
            ->first();

        if ($existingRecord) {
            throw new BusinessException(ResultCode::FAIL, '该内容已存在于播放列表中');
        }

        // 如果不存在记录，则创建新记录
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

    /**
     * 批量更新排序
     */
    public function updateSortOrder(array $items): bool
    {
        if (empty($items)) {
            throw new BusinessException(ResultCode::FAIL, '更新数据不能为空');
        }

        try {
            foreach ($items as $item) {
                if (empty($item['id']) || !isset($item['sort_order'])) {
                    throw new BusinessException(ResultCode::FAIL, '排序数据格式错误');
                }
                
                $this->repository->updateById($item['id'], [
                    'sort_order' => (int)$item['sort_order']
                ]);
            }
            return true;
        } catch (\Exception $e) {
            throw new BusinessException(ResultCode::FAIL, '更新排序失败：' . $e->getMessage());
        }
    }

    // 其它业务方法可按需扩展
} 
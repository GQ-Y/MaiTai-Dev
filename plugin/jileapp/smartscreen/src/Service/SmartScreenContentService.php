<?php

namespace Plugin\Jileapp\Smartscreen\Service;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Service\IService;
use Plugin\Jileapp\Smartscreen\Repository\SmartScreenContentRepository;

class SmartScreenContentService extends IService
{
    /**
     * @var SmartScreenContentRepository
     */
    public $repository;

    public function __construct()
    {
        $this->repository = new SmartScreenContentRepository();
    }

    /**
     * 新增内容，参数校验与异常处理
     */
    public function create(array $data): mixed
    {
        if (empty($data['title']) || empty($data['content_type']) || empty($data['content_url'])) {
            throw new BusinessException(ResultCode::FAIL, '标题、类型、内容URL不能为空');
        }
        return parent::create($data);
    }

    /**
     * 更新内容，参数校验与异常处理
     */
    public function updateById(mixed $id, array $data): mixed
    {
        if (empty($id)) {
            throw new BusinessException(ResultCode::FAIL, 'ID不能为空');
        }

        $model = $this->repository->findById($id);
        if (!$model) {
            throw new BusinessException(ResultCode::FAIL, '内容不存在');
            }

        // 显式处理可空或有条件的字段
        // 缩略图：如果未提交则设为null
        $model->thumbnail = $data['thumbnail'] ?? null;

        // 内容URL：根据内容类型处理
        $contentType = $data['content_type'] ?? $model->content_type;
        if ($contentType == 1) {
            // 网页类型：验证规则已确保content_url存在
            $model->content_url = $data['content_url'];
        } else {
            // 图片/视频类型：如果未提交则设为空字符串
            $model->content_url = $data['content_url'] ?? '';
        }

        // 从$data中移除已处理的字段，避免被fill覆盖
        unset($data['thumbnail'], $data['content_url']);

        $model->fill($data);
        return $model->save();
    }

    // 其它业务方法可按需扩展
} 
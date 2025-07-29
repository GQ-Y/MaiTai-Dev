<?php

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenPlaylistContent;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Arr;

class SmartScreenPlaylistContentRepository extends IRepository
{
    /**
     * @var SmartScreenPlaylistContent
     */
    public $model;

    public function __construct()
    {
        $this->model = new SmartScreenPlaylistContent();
    }

    /**
     * 支持playlist_id、content_id精确查询
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        // 添加内容表的关联查询
        $query = $query->with(['content' => function ($query) {
            $query->select(['id', 'title', 'content_type', 'content_url', 'thumbnail', 'duration', 'status']);
        }]);
            
        $query = $query
            ->when(Arr::get($params, 'playlist_id'), static function (Builder $query, $playlist_id) {
                $query->where('playlist_id', (int) $playlist_id);
            })
            ->when(Arr::get($params, 'content_id'), static function (Builder $query, $content_id) {
                $query->where('content_id', (int) $content_id);
            });
            
        return parent::handleSearch($query, $params);
    }

    /**
     * 重写列表查询，确保包含内容关联
     */
    public function getList(?array $params = [], bool $isScope = true): array
    {
        $params = $params ?? [];
        $query = $this->model->newQuery();
        
        // 添加内容表关联
        $query->with(['content' => function ($query) {
            $query->select(['id', 'title', 'content_type', 'content_url', 'thumbnail', 'duration', 'status']);
        }]);
        
        // 添加排序
        $query->orderBy('sort_order', 'asc');
        
        if ($isScope) {
            $query = $this->handleSearch($query, $params);
        }

        return $this->setPaginate($query, $params);
    }
} 
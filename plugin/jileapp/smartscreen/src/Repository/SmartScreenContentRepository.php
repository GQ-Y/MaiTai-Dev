<?php

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenContent;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Arr;

class SmartScreenContentRepository extends IRepository
{
    /**
     * @var SmartScreenContent
     */
    public $model;

    public function __construct()
    {
        $this->model = new SmartScreenContent();
    }

    /**
     * 支持title模糊查询，content_type、status精确查询
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        $query = $query
            ->when(Arr::get($params, 'title'), static function (Builder $query, string $title) {
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when(Arr::get($params, 'content_type'), static function (Builder $query, $content_type) {
                $query->where('content_type', $content_type);
            })
            ->when(Arr::has($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', (int) Arr::get($params, 'status'));
            });
            
        return parent::handleSearch($query, $params);
    }
} 
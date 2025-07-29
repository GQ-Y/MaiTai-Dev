<?php

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenPlaylist;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Arr;

class SmartScreenPlaylistRepository extends IRepository
{
    /**
     * @var SmartScreenPlaylist
     */
    public $model;

    public function __construct()
    {
        $this->model = new SmartScreenPlaylist();
    }

    /**
     * 支持name模糊查询，play_mode、status精确查询
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        $query = $query
            ->when(Arr::get($params, 'name'), static function (Builder $query, string $name) {
                $query->where('name', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'play_mode'), static function (Builder $query, $play_mode) {
                $query->where('play_mode', $play_mode);
            })
            ->when(Arr::has($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('status', (int) Arr::get($params, 'status'));
            });
            
        return parent::handleSearch($query, $params);
    }
} 
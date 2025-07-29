<?php

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenDevicePlaylist;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Arr;

class SmartScreenDevicePlaylistRepository extends IRepository
{
    /**
     * @var SmartScreenDevicePlaylist
     */
    public $model;

    public function __construct()
    {
        $this->model = new SmartScreenDevicePlaylist();
    }

    /**
     * 支持device_id、playlist_id精确查询
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        $query = $query
            ->when(Arr::get($params, 'device_id'), static function (Builder $query, $device_id) {
                $query->where('device_id', (int) $device_id);
            })
            ->when(Arr::get($params, 'playlist_id'), static function (Builder $query, $playlist_id) {
                $query->where('playlist_id', (int) $playlist_id);
            });
            
        return parent::handleSearch($query, $params);
    }
} 
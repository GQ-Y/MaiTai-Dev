<?php

namespace Plugin\Jileapp\Smartscreen\Repository;

use App\Repository\IRepository;
use Plugin\Jileapp\Smartscreen\Model\SmartScreenDevice;
use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Arr;

class SmartScreenDeviceRepository extends IRepository
{
    public function __construct(
        protected readonly SmartScreenDevice $model
    ) {}

    /**
     * 支持mac_address、device_name模糊查询，status精确查询
     */
    public function handleSearch(Builder $query, array $params): Builder
    {
        $query = $query
            ->when(Arr::get($params, 'mac_address'), static function (Builder $query, string $mac_address) {
                $query->where('smart_screen_device.mac_address', 'like', '%' . $mac_address . '%');
            })
            ->when(Arr::get($params, 'device_name'), static function (Builder $query, string $device_name) {
                $query->where('smart_screen_device.device_name', 'like', '%' . $device_name . '%');
            })
            ->when(Arr::has($params, 'status'), static function (Builder $query) use ($params) {
                $query->where('smart_screen_device.status', (int) Arr::get($params, 'status'));
            })
            ->when(Arr::has($params, 'is_online'), static function (Builder $query) use ($params) {
                $query->where('smart_screen_device.is_online', (int) Arr::get($params, 'is_online'));
            });
        
        return parent::handleSearch($query, $params);
    }

    /**
     * 获取设备列表，包含关联的内容信息
     */
    public function getPageList(array $params = [], int $page = 1, int $pageSize = 15): array
    {
        $query = $this->model->newQuery()
            ->leftJoin('smart_screen_content', 'smart_screen_device.current_content_id', '=', 'smart_screen_content.id')
            ->select([
                'smart_screen_device.*',
                'smart_screen_content.title as current_content_title',
                'smart_screen_content.content_type as current_content_type',
            ]);
            
        $query = $this->handleSearch($query, $params);
        
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->orderBy('smart_screen_device.id', 'desc')
            ->get()
            ->toArray();

        // 为每个设备附加播放列表信息
        foreach ($list as &$device) {
            $playlists = $this->getDevicePlaylists($device['id']);
            $device['playlists'] = $playlists;
            $device['playlist_count'] = count($playlists);
            $device['playlist_names'] = implode(', ', array_column($playlists, 'name'));
        }
            
        return [
            'list' => $list,
            'total' => $total,
        ];
    }

    /**
     * 设置设备显示内容
     */
    public function setDeviceContent(int $deviceId, ?int $contentId): bool
    {
        return $this->updateById($deviceId, ['current_content_id' => $contentId]);
    }

    /**
     * 获取设备直接关联的内容
     */
    public function getDeviceDirectContent(int $deviceId): ?array
    {
        $device = $this->model->newQuery()
            ->leftJoin('smart_screen_content', 'smart_screen_device.current_content_id', '=', 'smart_screen_content.id')
            ->where('smart_screen_device.id', $deviceId)
            ->whereNotNull('smart_screen_device.current_content_id')
            ->where('smart_screen_content.status', 1) // 只获取启用的内容
            ->select([
                'smart_screen_content.id',
                'smart_screen_content.title',
                'smart_screen_content.content_type',
                'smart_screen_content.content_url',
                'smart_screen_content.thumbnail',
                'smart_screen_content.duration',
            ])
            ->first();
            
        return $device ? $device->toArray() : null;
    }

    /**
     * 获取设备播放列表中的内容（获取第一个播放列表的第一个内容）
     */
    public function getDevicePlaylistContent(int $deviceId): ?array
    {
        $content = $this->model->newQuery()
            ->join('smart_screen_device_playlist', 'smart_screen_device.id', '=', 'smart_screen_device_playlist.device_id')
            ->join('smart_screen_playlist', 'smart_screen_device_playlist.playlist_id', '=', 'smart_screen_playlist.id')
            ->join('smart_screen_playlist_content', 'smart_screen_playlist.id', '=', 'smart_screen_playlist_content.playlist_id')
            ->join('smart_screen_content', 'smart_screen_playlist_content.content_id', '=', 'smart_screen_content.id')
            ->where('smart_screen_device.id', $deviceId)
            ->where('smart_screen_content.status', 1) // 只获取启用的内容
            ->select([
                'smart_screen_content.id',
                'smart_screen_content.title',
                'smart_screen_content.content_type',
                'smart_screen_content.content_url',
                'smart_screen_content.thumbnail',
                'smart_screen_content.duration',
                'smart_screen_playlist.name as playlist_name',
                'smart_screen_playlist.play_mode',
            ])
            ->orderBy('smart_screen_device_playlist.sort_order', 'asc')
            ->orderBy('smart_screen_playlist_content.sort_order', 'asc')
            ->first();
            
        return $content ? $content->toArray() : null;
    }

    /**
     * 设置设备播放列表
     */
    public function setDevicePlaylist(int $deviceId, array $playlistIds): bool
    {
        // 首先删除设备现有的播放列表关联
        \Hyperf\DbConnection\Db::table('smart_screen_device_playlist')
            ->where('device_id', $deviceId)
            ->delete();
        
        // 如果有新的播放列表，则添加关联
        if (!empty($playlistIds)) {
            $data = [];
            foreach ($playlistIds as $index => $playlistId) {
                $data[] = [
                    'device_id' => $deviceId,
                    'playlist_id' => $playlistId,
                    'sort_order' => $index + 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            
            \Hyperf\DbConnection\Db::table('smart_screen_device_playlist')->insert($data);
        }
        
        return true;
    }

    /**
     * 获取设备关联的播放列表
     */
    public function getDevicePlaylists(int $deviceId): array
    {
        return \Hyperf\DbConnection\Db::table('smart_screen_device_playlist')
            ->join('smart_screen_playlist', 'smart_screen_device_playlist.playlist_id', '=', 'smart_screen_playlist.id')
            ->where('smart_screen_device_playlist.device_id', $deviceId)
            ->where('smart_screen_playlist.status', 1)
            ->select([
                'smart_screen_playlist.id',
                'smart_screen_playlist.name',
                'smart_screen_playlist.play_mode',
                'smart_screen_device_playlist.sort_order',
            ])
            ->orderBy('smart_screen_device_playlist.sort_order', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 获取设备播放列表中的所有内容
     */
    public function getDeviceAllPlaylistContents(int $deviceId): array
    {
        $contents = \Hyperf\DbConnection\Db::table('smart_screen_device_playlist')
            ->join('smart_screen_playlist', 'smart_screen_device_playlist.playlist_id', '=', 'smart_screen_playlist.id')
            ->join('smart_screen_playlist_content', 'smart_screen_playlist.id', '=', 'smart_screen_playlist_content.playlist_id')
            ->join('smart_screen_content', 'smart_screen_playlist_content.content_id', '=', 'smart_screen_content.id')
            ->where('smart_screen_device_playlist.device_id', $deviceId)
            ->where('smart_screen_playlist.status', 1) // 只获取启用的播放列表
            ->where('smart_screen_content.status', 1) // 只获取启用的内容
            ->select([
                'smart_screen_content.id',
                'smart_screen_content.title',
                'smart_screen_content.content_type',
                'smart_screen_content.content_url',
                'smart_screen_content.thumbnail',
                'smart_screen_content.duration',
                'smart_screen_playlist.id as playlist_id',
                'smart_screen_playlist.name as playlist_name',
                'smart_screen_playlist.play_mode',
                'smart_screen_device_playlist.sort_order as playlist_sort',
                'smart_screen_playlist_content.sort_order as content_sort',
            ])
            ->orderBy('smart_screen_device_playlist.sort_order', 'asc')
            ->orderBy('smart_screen_playlist_content.sort_order', 'asc')
            ->get()
            ->toArray();

        return $contents;
    }
} 
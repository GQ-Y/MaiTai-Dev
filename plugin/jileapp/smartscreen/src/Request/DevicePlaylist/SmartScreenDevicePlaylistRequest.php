<?php

namespace Plugin\Jileapp\Smartscreen\Request\DevicePlaylist;

use Hyperf\Validation\Request\FormRequest;

class SmartScreenDevicePlaylistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'device_id' => 'required|integer',
            'playlist_id' => 'required|integer',
            'sort_order' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'device_id.required' => '设备ID不能为空',
            'device_id.integer' => '设备ID必须为整数',
            'playlist_id.required' => '播放列表ID不能为空',
            'playlist_id.integer' => '播放列表ID必须为整数',
            'sort_order.integer' => '排序必须为整数',
        ];
    }

    public function attributes(): array
    {
        return [
            'device_id' => '设备ID',
            'playlist_id' => '播放列表ID',
            'sort_order' => '排序',
        ];
    }
} 
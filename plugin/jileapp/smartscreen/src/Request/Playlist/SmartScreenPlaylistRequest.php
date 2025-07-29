<?php

namespace Plugin\Jileapp\Smartscreen\Request\Playlist;

use Hyperf\Validation\Request\FormRequest;

class SmartScreenPlaylistRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'play_mode' => 'required|integer|in:1,2,3',
            'status' => 'integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '播放列表名称不能为空',
            'name.max' => '播放列表名称不能超过200位',
            'play_mode.required' => '播放模式不能为空',
            'play_mode.integer' => '播放模式必须为整数',
            'play_mode.in' => '播放模式只能为1顺序/2随机/3单循环',
            'status.integer' => '状态必须为整数',
            'status.in' => '状态只能为0禁用/1启用',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '播放列表名称',
            'play_mode' => '播放模式',
            'status' => '状态',
        ];
    }
} 
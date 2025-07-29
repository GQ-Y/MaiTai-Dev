<?php

namespace Plugin\Jileapp\Smartscreen\Request\PlaylistContent;

use Hyperf\Validation\Request\FormRequest;

class SmartScreenPlaylistContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'playlist_id' => 'required|integer',
            'content_id' => 'required|integer',
            'sort_order' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'playlist_id.required' => '播放列表ID不能为空',
            'playlist_id.integer' => '播放列表ID必须为整数',
            'content_id.required' => '内容ID不能为空',
            'content_id.integer' => '内容ID必须为整数',
            'sort_order.integer' => '排序必须为整数',
        ];
    }

    public function attributes(): array
    {
        return [
            'playlist_id' => '播放列表ID',
            'content_id' => '内容ID',
            'sort_order' => '排序',
        ];
    }
} 
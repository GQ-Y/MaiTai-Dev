<?php

namespace Plugin\Jileapp\Smartscreen\Request\Content;

use Hyperf\Validation\Request\FormRequest;

class SmartScreenContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'content_type' => 'required|integer|in:1,2,3',
            'content_url' => 'nullable|string|max:500|required_if:content_type,1',
            'thumbnail' => 'nullable|string|max:500',
            'duration' => 'integer|min:0',
            'status' => 'integer|in:0,1',
            'sort_order' => 'integer',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '内容标题不能为空',
            'title.max' => '内容标题不能超过200位',
            'content_type.required' => '内容类型不能为空',
            'content_type.integer' => '内容类型必须为整数',
            'content_type.in' => '内容类型只能为1网页/2图片/3视频',
            'content_url.required_if' => '内容类型为网页时，内容URL不能为空',
            'content_url.max' => '内容URL不能超过500位',
            'thumbnail.max' => '缩略图地址不能超过500位',
            'duration.integer' => '播放时长必须为整数',
            'duration.min' => '播放时长不能为负数',
            'status.integer' => '状态必须为整数',
            'status.in' => '状态只能为0禁用/1启用',
            'sort_order.integer' => '排序必须为整数',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => '内容标题',
            'content_type' => '内容类型',
            'content_url' => '内容URL',
            'thumbnail' => '缩略图',
            'duration' => '播放时长',
            'status' => '状态',
            'sort_order' => '排序',
        ];
    }
} 
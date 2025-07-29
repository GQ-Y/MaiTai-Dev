<?php

namespace Plugin\Jileapp\Smartscreen\Request\Device;

use Hyperf\Validation\Request\FormRequest;

class SmartScreenDeviceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mac_address' => 'required|string|max:17',
            'device_name' => 'required|string|max:100',
            'status' => 'integer|in:0,1',
            'is_online' => 'integer|in:0,1',
            'display_mode' => 'integer|in:1,2,3',
        ];
    }

    public function messages(): array
    {
        return [
            'mac_address.required' => 'MAC地址不能为空',
            'mac_address.max' => 'MAC地址不能超过17位',
            'device_name.required' => '设备名称不能为空',
            'device_name.max' => '设备名称不能超过100位',
            'status.integer' => '设备状态必须为整数',
            'status.in' => '设备状态只能为0或1',
            'is_online.integer' => '在线状态必须为整数',
            'is_online.in' => '在线状态只能为0或1',
            'display_mode.integer' => '显示模式必须为整数',
            'display_mode.in' => '显示模式只能为1网页/2图片/3视频',
        ];
    }

    public function attributes(): array
    {
        return [
            'mac_address' => 'MAC地址',
            'device_name' => '设备名称',
            'status' => '设备状态',
            'is_online' => '在线状态',
            'display_mode' => '显示模式',
        ];
    }
} 
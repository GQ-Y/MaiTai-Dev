<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Database\Seeder;

use Hyperf\Database\Seeders\Seeder;

class SmartScreenDeviceGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        \Hyperf\DbConnection\Db::table('smart_screen_device_group')->insert([
            [
                'name' => '默认分组',
                'description' => '系统默认设备分组',
                'color' => '#1890ff',
                'sort_order' => 1,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '大厅区域',
                'description' => '大厅区域的智慧屏设备',
                'color' => '#52c41a',
                'sort_order' => 2,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '会议室',
                'description' => '会议室的智慧屏设备',
                'color' => '#faad14',
                'sort_order' => 3,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '办公区',
                'description' => '办公区域的智慧屏设备',
                'color' => '#722ed1',
                'sort_order' => 4,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
} 
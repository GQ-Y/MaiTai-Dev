<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Database\Seeder;

use Hyperf\Database\Seeders\Seeder;

class SmartScreenDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        \Hyperf\DbConnection\Db::table('smart_screen_device')->insert([
            [
                'mac_address' => 'AA:BB:CC:DD:EE:01',
                'device_name' => '智慧屏-一号',
                'status' => 1,
                'is_online' => 1,
                'display_mode' => 1,
                'current_content_id' => 1,
                'last_online_time' => $now,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'mac_address' => 'AA:BB:CC:DD:EE:02',
                'device_name' => '智慧屏-二号',
                'status' => 0,
                'is_online' => 0,
                'display_mode' => 2,
                'current_content_id' => 2,
                'last_online_time' => $now,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

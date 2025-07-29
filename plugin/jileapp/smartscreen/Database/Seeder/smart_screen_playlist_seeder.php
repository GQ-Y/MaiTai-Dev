<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Database\Seeder;

use Hyperf\Database\Seeders\Seeder;

class SmartScreenPlaylistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        \Hyperf\DbConnection\Db::table('smart_screen_playlist')->insert([
            [
                'name' => '默认播放列表',
                'play_mode' => 1,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '宣传轮播',
                'play_mode' => 2,
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

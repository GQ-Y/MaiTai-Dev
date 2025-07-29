<?php

declare(strict_types=1);

namespace Plugin\Jileapp\Smartscreen\Database\Seeder;

use Hyperf\Database\Seeders\Seeder;

class SmartScreenContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        \Hyperf\DbConnection\Db::table('smart_screen_content')->insert([
            [
                'title' => '智慧屏官网',
                'content_type' => 1,
                'content_url' => 'https://smartscreen.example.com',
                'thumbnail' => null,
                'duration' => 0,
                'status' => 1,
                'sort_order' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => '宣传图片',
                'content_type' => 2,
                'content_url' => 'https://smartscreen.example.com/image.jpg',
                'thumbnail' => 'https://smartscreen.example.com/thumb.jpg',
                'duration' => 10,
                'status' => 1,
                'sort_order' => 2,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => '宣传视频',
                'content_type' => 3,
                'content_url' => 'https://smartscreen.example.com/video.mp4',
                'thumbnail' => 'https://smartscreen.example.com/videothumb.jpg',
                'duration' => 60,
                'status' => 1,
                'sort_order' => 3,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

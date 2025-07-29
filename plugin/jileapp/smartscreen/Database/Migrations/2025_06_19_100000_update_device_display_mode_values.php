<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 更新现有设备的播放策略值
     */
    public function up(): void
    {
        // 由于字段含义发生变化，我们将所有现有设备的display_mode重置为默认值1（播放列表优先）
        // 这确保了兼容性，管理员可以根据需要重新配置
        Db::table('smart_screen_device')->update(['display_mode' => 1]);
        
        // 可选：添加注释说明字段含义的变更
        Schema::table('smart_screen_device', static function (Blueprint $table) {
            $table->comment('智慧屏设备表 - 播放策略字段已更新：1播放列表优先/2直接内容优先/3仅播放列表/4仅直接内容');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚时恢复原有的注释
        Schema::table('smart_screen_device', static function (Blueprint $table) {
            $table->comment('智慧屏设备表');
        });
    }
}; 
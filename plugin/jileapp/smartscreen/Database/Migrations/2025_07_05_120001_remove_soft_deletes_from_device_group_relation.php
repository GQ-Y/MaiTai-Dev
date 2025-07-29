<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('smart_screen_device_group_relation', static function (Blueprint $table) {
            // 移除软删除字段
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smart_screen_device_group_relation', static function (Blueprint $table) {
            // 恢复软删除字段
            $table->softDeletes();
        });
    }
}; 
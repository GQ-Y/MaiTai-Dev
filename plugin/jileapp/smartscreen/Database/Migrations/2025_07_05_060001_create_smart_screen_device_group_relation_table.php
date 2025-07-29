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
        Schema::create('smart_screen_device_group_relation', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('device_id')->comment('设备ID');
            $table->unsignedBigInteger('group_id')->comment('分组ID');
            $table->integer('sort_order')->default(0)->comment('分组内排序');
            $table->timestamps();
            $table->softDeletes();
            
            // 外键约束
            $table->foreign('device_id')->references('id')->on('smart_screen_device')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('smart_screen_device_group')->onDelete('cascade');
            
            // 唯一约束：一个设备在同一个分组中只能存在一次
            $table->unique(['device_id', 'group_id'], 'device_group_unique');
            
            // 添加索引
            $table->index('device_id', 'idx_device_id');
            $table->index('group_id', 'idx_group_id');
            $table->index(['group_id', 'sort_order'], 'idx_group_sort');
            
            $table->comment('智慧屏设备分组关联表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_device_group_relation');
    }
}; 
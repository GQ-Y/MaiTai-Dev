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
        Schema::create('smart_screen_device_group', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->comment('分组名称');
            $table->string('description', 500)->nullable()->comment('分组描述');
            $table->string('color', 7)->default('#1890ff')->comment('分组颜色标识');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->tinyInteger('status')->default(1)->comment('状态：0禁用/1启用');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('智慧屏设备分组表');
            
            // 添加索引
            $table->index(['status', 'sort_order'], 'idx_status_sort');
            $table->index('name', 'idx_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_device_group');
    }
}; 
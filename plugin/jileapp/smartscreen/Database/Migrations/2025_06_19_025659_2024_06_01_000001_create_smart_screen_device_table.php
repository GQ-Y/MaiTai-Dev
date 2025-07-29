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
        Schema::create('smart_screen_device', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mac_address', 17)->unique()->comment('设备MAC地址');
            $table->string('device_name', 100)->comment('设备名称');
            $table->tinyInteger('status')->default(0)->comment('设备状态：0未激活/1已激活');
            $table->tinyInteger('is_online')->default(0)->comment('在线状态：0离线/1在线');
            $table->tinyInteger('display_mode')->default(1)->comment('播放策略：1播放列表优先/2直接内容优先/3仅播放列表/4仅直接内容');
            $table->unsignedBigInteger('current_content_id')->nullable()->comment('当前内容ID');
            $table->timestamp('last_online_time')->nullable()->comment('最后在线时间');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('智慧屏设备表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_device');
    }
};

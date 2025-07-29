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
        Schema::create('smart_screen_device_playlist', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('device_id')->comment('设备ID');
            $table->unsignedBigInteger('playlist_id')->comment('播放列表ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('device_id')->references('id')->on('smart_screen_device')->onDelete('cascade');
            $table->foreign('playlist_id')->references('id')->on('smart_screen_playlist')->onDelete('cascade');
            $table->unique(['device_id', 'playlist_id'], 'device_playlist_unique');
            $table->comment('智慧屏设备-播放列表关联表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_device_playlist');
    }
};

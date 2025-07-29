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
        Schema::create('smart_screen_playlist', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200)->comment('播放列表名称');
            $table->tinyInteger('play_mode')->default(1)->comment('播放模式：1顺序/2随机/3单循环');
            $table->tinyInteger('status')->default(1)->comment('状态：0禁用/1启用');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('智慧屏播放列表表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_playlist');
    }
};

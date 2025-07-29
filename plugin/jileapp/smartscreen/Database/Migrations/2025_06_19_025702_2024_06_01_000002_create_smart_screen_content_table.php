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
        Schema::create('smart_screen_content', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 200)->comment('内容标题');
            $table->tinyInteger('content_type')->default(1)->comment('内容类型：1网页/2图片/3视频(视频文件)/4直播流/5音频');
            $table->text('content_url')->comment('内容URL或文件路径');
            $table->string('thumbnail', 500)->nullable()->comment('缩略图');
            $table->integer('duration')->default(0)->comment('播放时长（秒）');
            $table->tinyInteger('status')->default(1)->comment('状态：0禁用/1启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('智慧屏内容表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_content');
    }
};

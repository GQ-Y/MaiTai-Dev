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
        Schema::create('smart_screen_playlist_content', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playlist_id')->comment('播放列表ID');
            $table->unsignedBigInteger('content_id')->comment('内容ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('playlist_id')->references('id')->on('smart_screen_playlist')->onDelete('cascade');
            $table->foreign('content_id')->references('id')->on('smart_screen_content')->onDelete('cascade');
            $table->unique(['playlist_id', 'content_id'], 'playlist_content_unique');
            $table->comment('智慧屏播放列表-内容关联表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_screen_playlist_content');
    }
};

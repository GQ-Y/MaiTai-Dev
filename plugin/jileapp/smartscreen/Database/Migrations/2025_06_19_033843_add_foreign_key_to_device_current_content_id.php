<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_screen_device', function (Blueprint $table) {
            $table->foreign('current_content_id')->references('id')->on('smart_screen_content');
        });
    }

    public function down(): void
    {
        Schema::table('smart_screen_device', function (Blueprint $table) {
            $table->dropForeign(['current_content_id']);
        });
    }
}; 
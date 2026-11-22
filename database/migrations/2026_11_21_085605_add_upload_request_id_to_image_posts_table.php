<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_posts', function (Blueprint $table): void {
            $table->uuid('upload_request_id')->nullable();
            $table->unique(['user_id', 'upload_request_id']);
        });
    }

    public function down(): void
    {
        Schema::table('image_posts', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'upload_request_id']);
            $table->dropColumn('upload_request_id');
        });
    }
};
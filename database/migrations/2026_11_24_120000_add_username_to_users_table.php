<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 32)->nullable()->unique();
        });

        DB::table('users')->select('id')->chunkById(500, function ($users): void {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'username' => 'user-' . $user->id,
                ]);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 32)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('username');
        });
    }
};
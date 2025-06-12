<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_calendar_id')) {
                $table->string('google_calendar_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'google_calendar_token')) {
                $table->text('google_calendar_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'google_calendar_refresh_token')) {
                $table->text('google_calendar_refresh_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'google_token_expires_at')) {
                $table->timestamp('google_token_expires_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_calendar_id',
                'google_calendar_token',
                'google_calendar_refresh_token',
                'google_token_expires_at'
            ]);
        });
    }
};

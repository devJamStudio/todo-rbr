<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, update existing records
        DB::table('tasks')
            ->where('status', 'to-do')
            ->update(['status' => 'pending']);

        DB::table('tasks')
            ->where('status', 'in progress')
            ->update(['status' => 'in_progress']);

        DB::table('tasks')
            ->where('status', 'done')
            ->update(['status' => 'completed']);

        // Then modify the column
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending')->change();
        });
    }

    public function down()
    {
        // No down migration as reverting to old status values is not supported.
    }
};

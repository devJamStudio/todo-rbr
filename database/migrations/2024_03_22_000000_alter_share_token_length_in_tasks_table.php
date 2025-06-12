<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // This migration is no longer needed as we're adding the share_token column
        // with the correct length in the previous migration
    }

    public function down()
    {
        // No need to revert anything
    }
};

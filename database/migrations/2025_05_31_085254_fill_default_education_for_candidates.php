<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE candidates SET education = 'Not Specified' WHERE education IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE candidates SET education = NULL WHERE education = 'Not Specified'");
    }
};

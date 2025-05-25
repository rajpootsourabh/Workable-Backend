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
        // Update null emails with generated unique placeholders
        DB::statement('UPDATE candidates SET email = CONCAT("user", id, "@example.com") WHERE email IS NULL');

        // Set default for null country values
        DB::table('candidates')->whereNull('country')->update(['country' => 'Unknown']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional rollback logic
        DB::table('candidates')->where('email', 'like', 'user%@example.com')->update(['email' => null]);
        DB::table('candidates')->where('country', 'Unknown')->update(['country' => null]);
    }
};

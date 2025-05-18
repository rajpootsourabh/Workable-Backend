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
    public function up()
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        // Insert default stages
        DB::table('stages')->insert([
            ['id' => 1, 'name' => 'Sourced'],
            ['id' => 2, 'name' => 'Applied'],
            ['id' => 3, 'name' => 'Phone Screen'],
            ['id' => 4, 'name' => 'Assessment'],
            ['id' => 5, 'name' => 'Interview'],
            ['id' => 6, 'name' => 'Offer'],
            ['id' => 7, 'name' => 'Hired'],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};

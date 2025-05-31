<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
        });

        // 🔁 Example: Set default company_id = 1 for all job posts
        DB::statement("UPDATE job_posts SET company_id = 1 WHERE company_id IS NULL");

        // ✅ Add foreign key constraint after data is updated
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('company_id')->nullable(false)->change(); // make it required
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};

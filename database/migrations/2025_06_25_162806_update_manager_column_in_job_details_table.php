<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateManagerColumnInJobDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('job_details', function (Blueprint $table) {
            $table->dropColumn('manager'); // remove old string field
            $table->unsignedBigInteger('manager_id')->nullable()->after('division');

            $table->foreign('manager_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('job_details', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
            $table->string('manager')->nullable(); // restore old column
        });
    }
}

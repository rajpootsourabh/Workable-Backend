<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToEmployeesTable extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add the company_id column
            $table->unsignedBigInteger('company_id')->nullable()->after('id');;
            // Set up the foreign key constraint
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['company_id']);
            
            // Drop the company_id column
            $table->dropColumn('company_id');
        });
    }
}

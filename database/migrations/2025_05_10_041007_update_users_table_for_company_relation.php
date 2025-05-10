<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop old columns (only if they're present and not used anymore)
            $table->dropColumn([
                'companyName',
                'companyWebsite',
                'companySize',
                'phoneNumber',
                'evaluatingWebsite'
            ]);

            // Correct foreign key type based on companies.id
            $table->unsignedBigInteger('company_id')->nullable()->after('id'); // not uuid
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');

            // Optionally restore dropped fields
            $table->string('companyName');
            $table->string('companyWebsite');
            $table->string('companySize');
            $table->string('phoneNumber')->unique();
            $table->text('evaluatingWebsite')->nullable();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;

class AddPdfmakeSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('core__invoicedesigns', function ($table) {
            $table->mediumText('pdfmake')->nullable()->after('javascript');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('core__invoicedesigns', function ($table) {
            $table->dropColumn('pdfmake');
        });
    }
}

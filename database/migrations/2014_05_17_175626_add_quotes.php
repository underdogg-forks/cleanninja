<?php

use Illuminate\Database\Migrations\Migration;

class AddQuotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function ($table) {
            $table->boolean('invoice_type_id')->default(0)->after('invoice_status_id');
            $table->unsignedInteger('quote_id')->nullable()->after('invoice_type_id');
            $table->unsignedInteger('quote_invoice_id')->nullable()->after('quote_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function ($table) {
            $table->dropColumn('invoice_type_id');
            $table->dropColumn('quote_id');
            $table->dropColumn('quote_invoice_id');
        });
    }
}

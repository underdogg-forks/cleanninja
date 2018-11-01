<?php

use Illuminate\Database\Migrations\Migration;

class AddSupportForInvoiceDesigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core__invoicedesigns', function ($table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('core__invoicedesigns')->insert(['name' => 'Clean']);
        DB::table('core__invoicedesigns')->insert(['name' => 'Bold']);
        DB::table('core__invoicedesigns')->insert(['name' => 'Modern']);
        DB::table('core__invoicedesigns')->insert(['name' => 'Plain']);

        Schema::table('invoices', function ($table) {
            $table->unsignedInteger('invoice_design_id')->default(1)->after('invoice_status_id');
        });

        Schema::table('accounts', function ($table) {
            $table->unsignedInteger('invoice_design_id')->default(1)->after('currency_id');
        });

        DB::table('invoices')->update(['invoice_design_id' => 1]);
        DB::table('accounts')->update(['invoice_design_id' => 1]);

        Schema::table('invoices', function ($table) {
            $table->foreign('invoice_design_id')->references('id')->on('core__invoicedesigns');
        });
    
        Schema::table('accounts', function ($table) {
            $table->foreign('invoice_design_id')->references('id')->on('core__invoicedesigns');
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
            $table->dropForeign('invoices_invoice_design_id_foreign');
            $table->dropColumn('invoice_design_id');
        });

        Schema::table('accounts', function ($table) {
            $table->dropForeign('accounts_invoice_design_id_foreign');
            $table->dropColumn('invoice_design_id');
        });

        Schema::dropIfExists('core__invoicedesigns');
    }
}

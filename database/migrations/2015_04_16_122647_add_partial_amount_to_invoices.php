<?php

use Illuminate\Database\Migrations\Migration;

class AddPartialAmountToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function ($table) {
            $table->decimal('partial', 13, 2)->nullable()->after('recurring_invoice_id');
        });

        Schema::table('accounts', function ($table) {
            $table->boolean('utf8_invoices')->default(true)->after('slug');
            $table->boolean('auto_wrap')->default(false)->after('utf8_invoices');
            $table->string('subdomain')->nullable()->after('auto_wrap');
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
            $table->dropColumn('partial');
        });

        Schema::table('accounts', function ($table) {
            if (Schema::hasColumn('accounts', 'utf8_invoices')) {
                $table->dropColumn('utf8_invoices');
            }
            if (Schema::hasColumn('accounts', 'auto_wrap')) {
                $table->dropColumn('auto_wrap');
            }
            $table->dropColumn('subdomain');
        });
    }
}

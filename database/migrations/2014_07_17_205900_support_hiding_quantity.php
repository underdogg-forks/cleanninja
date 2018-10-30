<?php

use Illuminate\Database\Migrations\Migration;

class SupportHidingQuantity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function ($table) {
            $table->boolean('hide_quantity')->default(0)->after('invoice_item_taxes');
            $table->boolean('hide_paid_to_date')->default(0)->after('hide_quantity');

            $table->string('custom_invoice_label1')->nullable()->after('hide_paid_to_date');
            $table->string('custom_invoice_label2')->nullable()->after('custom_invoice_label1');

            $table->boolean('custom_invoice_taxes1')->nullable()->after('custom_invoice_label2');
            $table->boolean('custom_invoice_taxes2')->nullable()->after('custom_invoice_taxes1');
        });

        Schema::table('invoices', function ($table) {
            $table->decimal('custom_value1', 13, 2)->default(0)->after('recurring_invoice_id');
            $table->decimal('custom_value2', 13, 2)->default(0)->after('custom_value1');

            $table->boolean('custom_taxes1')->default(0)->after('custom_value2');
            $table->boolean('custom_taxes2')->default(0)->after('custom_taxes1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function ($table) {
            $table->dropColumn('hide_quantity');
            $table->dropColumn('hide_paid_to_date');

            $table->dropColumn('custom_invoice_label1');
            $table->dropColumn('custom_invoice_label2');

            $table->dropColumn('custom_invoice_taxes1');
            $table->dropColumn('custom_invoice_taxes2');
        });
        
        Schema::table('invoices', function ($table) {
            $table->dropColumn('custom_value1');
            $table->dropColumn('custom_value2');

            $table->dropColumn('custom_taxes1');
            $table->dropColumn('custom_taxes2');
        });
    }
}

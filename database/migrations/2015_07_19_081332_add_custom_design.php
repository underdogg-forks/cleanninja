<?php

use Illuminate\Database\Migrations\Migration;

class AddCustomDesign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function ($table) {
            $table->mediumText('custom_design')->nullable()->after('slug');
        });

        DB::table('core__invoicedesigns')->insert(['id' => CUSTOM_DESIGN1, 'name' => 'Custom']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function ($table) {
            $table->dropColumn('custom_design');
        });
    }
}

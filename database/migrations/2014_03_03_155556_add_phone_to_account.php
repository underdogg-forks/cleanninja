<?php

use Illuminate\Database\Migrations\Migration;

class AddPhoneToAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function ($table) {
            $table->string('work_phone')->nullable()->after('name');
            $table->string('work_email')->nullable()->after('work_phone');
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
            $table->dropColumn('work_phone');
            $table->dropColumn('work_email');
        });
    }
}

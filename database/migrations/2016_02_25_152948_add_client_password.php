<?php

use Illuminate\Database\Migrations\Migration;

class AddClientPassword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function ($table) {
            $table->boolean('enable_portal_password')->default(0)->after('show_currency_code');
            $table->boolean('send_portal_password')->default(0)->after('enable_portal_password');
        });
        
        Schema::table('contacts', function ($table) {
            $table->string('password', 255)->nullable()->after('phone');
            $table->boolean('confirmation_code', 255)->nullable()->after('password');
            $table->boolean('remember_token', 100)->nullable()->after('confirmation_code');
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
            $table->dropColumn('enable_portal_password');
            $table->dropColumn('send_portal_password');
        });
        
        Schema::table('contacts', function ($table) {
            $table->dropColumn('password');
            $table->dropColumn('confirmation_code');
            $table->dropColumn('remember_token');
        });
    }
}

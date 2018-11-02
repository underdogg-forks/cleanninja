<?php

use Illuminate\Database\Migrations\Migration;

class CreatePaymentLibraries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('payments__libraries');

        Schema::create('payments__libraries', function ($t) {
            $t->increments('id');

            $t->string('name');
            $t->boolean('visible')->default(true);

            $t->timestamps();

        });

        Schema::table('core__gateways', function ($table) {
            $table->unsignedInteger('payment_library_id')->default(1)->after('name');
        });

        DB::table('core__gateways')->update(['payment_library_id' => 1]);

        Schema::table('core__gateways', function ($table) {
            $table->foreign('payment_library_id')->references('id')->on('payments__libraries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('core__gateways', 'payment_library_id')) {
            Schema::table('core__gateways', function ($table) {
                $table->dropForeign('gateways_payment_library_id_foreign');
                $table->dropColumn('payment_library_id');
            });
        }

        Schema::dropIfExists('payments__libraries');
    }
}

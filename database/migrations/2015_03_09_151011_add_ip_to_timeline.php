<?php

use Illuminate\Database\Migrations\Migration;

class AddIpToTimeline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('core__timeline', function ($table) {
            $table->string('ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('core__timeline', function ($table) {
            $table->dropColumn('ip');
        });
    }
}

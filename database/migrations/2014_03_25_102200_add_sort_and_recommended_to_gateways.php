<?php

use Illuminate\Database\Migrations\Migration;

class AddSortAndRecommendedToGateways extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('core__gateways', function ($table) {
            $table->unsignedInteger('sort_order')->default(10000)->after('name');
            $table->boolean('recommended')->default(0)->after('sort_order');
            $table->string('site_url', 200)->nullable()->after('recommended');
        });
    }
    
    public function down()
    {
        if (Schema::hasColumn('core__gateways', 'sort_order')) {
            Schema::table('core__gateways', function ($table) {
                $table->dropColumn('sort_order');
            });
        }
        
        if (Schema::hasColumn('core__gateways', 'recommended')) {
            Schema::table('core__gateways', function ($table) {
                $table->dropColumn('recommended');
            });
        }
        
        if (Schema::hasColumn('core__gateways', 'site_url')) {
            Schema::table('core__gateways', function ($table) {
                $table->dropColumn('site_url');
            });
        }
    }
}

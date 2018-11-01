<?php

use App\Models\Timeline;
use Illuminate\Database\Migrations\Migration;

class AddIsSystemToTimeline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('core__timeline', function ($table) {
            $table->boolean('is_system')->default(0);
        });

        $timeline = Timeline::where('message', 'like', '%<i>System</i>%')->get();
        foreach ($timeline as $timeline) {
            $timeline->is_system = true;
            $timeline->save();
        }

        Schema::table('core__timeline', function ($table) {
            $table->dropColumn('message');
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
            $table->dropColumn('is_system');
        });

        Schema::table('core__timeline', function ($table) {
            $table->text('message')->nullable();
        });
    }
}

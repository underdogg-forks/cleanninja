<?php

use Illuminate\Database\Migrations\Migration;

class EnableResumingTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function ($table) {
            $table->boolean('is_running')->default(false)->after('is_deleted');
            $table->integer('break_duration')->nullable()->after('is_running');
            $table->timestamp('resume_time')->nullable()->after('break_duration');
            $table->text('time_log')->nullable()->after('resume_time');
        });

        $tasks = DB::table('tasks')
                    ->where('duration', '=', -1)
                    ->select('id', 'duration', 'start_time')
                    ->get();

        foreach ($tasks as $task) {
            $data = [
                'is_running' => true,
                'duration' => 0,
                
            ];

            DB::table('tasks')
                ->where('id', $task->id)
                ->update($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function ($table) {
            $table->dropColumn('is_running');
            $table->dropColumn('resume_time');
            $table->dropColumn('break_duration');
            $table->dropColumn('time_log');
        });
    }
}

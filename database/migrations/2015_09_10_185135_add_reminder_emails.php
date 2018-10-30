<?php

use Illuminate\Database\Migrations\Migration;

class AddReminderEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function ($table) {
            $table->string('email_subject_invoice')->nullable()->after('slug');
            $table->string('email_subject_quote')->nullable()->after('email_subject_invoice');
            $table->string('email_subject_payment')->nullable()->after('email_subject_quote');

            $table->string('email_subject_reminder1')->nullable()->after('email_subject_payment');
            $table->string('email_subject_reminder2')->nullable()->after('email_subject_reminder1');
            $table->string('email_subject_reminder3')->nullable()->after('email_subject_reminder2');

            $table->text('email_template_reminder1')->nullable()->after('email_subject_reminder3');
            $table->text('email_template_reminder2')->nullable()->after('email_template_reminder1');
            $table->text('email_template_reminder3')->nullable()->after('email_template_reminder2');

            $table->boolean('enable_reminder1')->default(false)->after('email_template_reminder3');
            $table->boolean('enable_reminder2')->default(false)->after('enable_reminder1');
            $table->boolean('enable_reminder3')->default(false)->after('enable_reminder2');

            $table->smallInteger('num_days_reminder1')->default(7)->after('enable_reminder3');
            $table->smallInteger('num_days_reminder2')->default(14)->after('num_days_reminder1');
            $table->smallInteger('num_days_reminder3')->default(30)->after('num_days_reminder2');
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
            if (Schema::hasColumn('accounts', 'email_subject_invoice')) {
                $table->dropColumn('email_subject_invoice');
                $table->dropColumn('email_subject_quote');
                $table->dropColumn('email_subject_payment');

                $table->dropColumn('email_subject_reminder1');
                $table->dropColumn('email_subject_reminder2');
                $table->dropColumn('email_subject_reminder3');

                $table->dropColumn('email_template_reminder1');
                $table->dropColumn('email_template_reminder2');
                $table->dropColumn('email_template_reminder3');
            }

            $table->dropColumn('enable_reminder1');
            $table->dropColumn('enable_reminder2');
            $table->dropColumn('enable_reminder3');

            $table->dropColumn('num_days_reminder1');
            $table->dropColumn('num_days_reminder2');
            $table->dropColumn('num_days_reminder3');
        });
    }
}

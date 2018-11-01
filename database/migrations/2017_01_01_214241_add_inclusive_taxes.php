<?php

use Illuminate\Database\Migrations\Migration;

class AddInclusiveTaxes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('tax_rates', 'is_inclusive')) {
            Schema::table('tax_rates', function ($table) {
                $table->boolean('is_inclusive')->default(false);
            });
        }

        Schema::table('core__plans', function ($table) {
            $table->enum('bluevine_status', ['ignored', 'signed_up'])->nullable();
        });

        DB::statement('UPDATE plans
            LEFT JOIN accounts ON accounts.plan_id = plans.id AND accounts.bluevine_status IS NOT NULL
            SET plans.bluevine_status = accounts.bluevine_status');

        Schema::table('accounts', function ($table) {
            $table->dropColumn('bluevine_status');
            $table->text('bcc_email')->nullable();
            $table->text('client_number_prefix')->nullable();
            $table->integer('client_number_counter')->default(0)->nullable();
            $table->text('client_number_pattern')->nullable();
            $table->tinyInteger('domain_id')->default(1)->nullable()->unsigned();
            $table->tinyInteger('core__paymentterms')->nullable();
        });

        Schema::table('core__timeline', function ($table) {
            $table->text('notes')->nullable();
        });

        Schema::table('date_formats', function ($table) {
            $table->string('format_moment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tax_rates', function ($table) {
            $table->dropColumn('is_inclusive');
        });

        Schema::table('core__plans', function ($table) {
            $table->dropColumn('bluevine_status');
        });

        Schema::table('accounts', function ($table) {
            $table->enum('bluevine_status', ['ignored', 'signed_up'])->nullable();
            if (Schema::hasColumn('accounts', 'bcc_email')) {
                $table->dropColumn('bcc_email');
            }
            $table->dropColumn('client_number_prefix');
            $table->dropColumn('client_number_counter');
            $table->dropColumn('client_number_pattern');
            $table->dropColumn('domain_id');
            $table->dropColumn('core__paymentterms');
        });

        Schema::table('core__timeline', function ($table) {
            $table->dropColumn('notes');
        });

        Schema::table('date_formats', function ($table) {
            $table->dropColumn('format_moment');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToPaymentTransactionReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_transaction_report', function (Blueprint $table) {
            //
            $table->string('username')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transaction_report', function (Blueprint $table) {
            //
        });
    }
}

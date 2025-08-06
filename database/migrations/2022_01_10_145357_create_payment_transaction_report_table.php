<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transaction_report', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->float('amount', 20, 2)->default(0);
            $table->string('status');
            $table->string('transaction_reference');
            $table->string('recipient_code');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transaction_report');
    }
}

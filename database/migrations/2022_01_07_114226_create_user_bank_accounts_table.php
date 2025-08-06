<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBankAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id',false,true);
            $table->timestamps();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('bank_name');
            $table->string('BVN_Number')->nullable();
            $table->string('Active_status')->nullable();
            $table->string('bank_code');
            $table->string('recipient_code')->nullable();   
            $table->string('num_type')->nullable();

            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bank_accounts');
    }
}

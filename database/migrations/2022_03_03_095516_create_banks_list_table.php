<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks_list', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('country');
            $table->string('currency');
            $table->string('type');
            $table->bigInteger('bank_list_id',false,true);

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
        Schema::dropIfExists('banks_list');
    }
}

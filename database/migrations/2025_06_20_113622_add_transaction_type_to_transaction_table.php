<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionTypeToTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction', function (Illuminate\Database\Schema\Blueprint $table) {
        $table->string('transaction_type', 50)->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('transaction', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
}

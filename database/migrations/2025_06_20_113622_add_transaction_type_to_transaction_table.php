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
        // ADD THIS CHECK: This will only run the code inside if the 'transaction_type' column does NOT exist.
        if (!Schema::hasColumn('transaction', 'transaction_type')) {
            Schema::table('transaction', function (Blueprint $table) {
                $table->string('transaction_type', 50)->nullable()->after('remarks');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ADD THIS CHECK: This makes the rollback safer by only trying to drop the column if it exists.
        if (Schema::hasColumn('transaction', 'transaction_type')) {
            Schema::table('transaction', function (Blueprint $table) {
                $table->dropColumn('transaction_type');
            });
        }
    }
}
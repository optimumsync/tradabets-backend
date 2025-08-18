<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SimplifyBonusesTable extends Migration
{
    public function up()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            // Remove the columns that are no longer needed
            $table->dropColumn(['description', 'is_active']);
        });
    }

    public function down()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            // This allows you to reverse the change if necessary
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }
}
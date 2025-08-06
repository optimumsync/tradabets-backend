<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInboxNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbox_notification', function (Blueprint $table) {
            $table->increments('inbox_notification_id', 10)->unsigned();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->bigInteger('receiver')->default(null)->unsigned()->nullable();
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
        Schema::dropIfExists('inbox_notification');
    }
}

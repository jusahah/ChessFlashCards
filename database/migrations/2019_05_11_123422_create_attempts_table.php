<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('position_id')->index();
            $table->enum('result', [0,1,2,3]); // best_move = 1, 2nd_best_move = 2, etc.
            $table->string('inputted_move', 16);

            // These will be copied from Move object.
            $table->string('best_move', 16)->nullable();
            $table->string('2nd_best_move', 16)->nullable();
            $table->string('3rd_best_move', 16)->nullable();

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
        Schema::dropIfExists('attempts');
    }
}

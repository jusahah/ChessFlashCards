<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('game_id')->index();
            $table->unsignedInteger('move_id')->index();
            $table->enum('verdict', ['terrible', 'bad', 'neutral', 'good', 'great'])->nullable();
            $table->signedInteger('eval')->nullable(); // In centipawns
            $table->timestamps();

            $this->unique(['game_id', 'move_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moves');
    }
}

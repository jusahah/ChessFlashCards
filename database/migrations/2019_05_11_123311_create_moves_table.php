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
            $table->string('game_id')->index();
            $table->unsignedInteger('position_id')->index();
            $table->unsignedInteger('half_move_num');
            $table->string('move', 16);
            // Whether we search for any incoming game for this move.
            $table->boolean('flagging_enabled')->default(true);
            // Whether this move was deemed good/bad by earlier move that had flagging_enabled
            $table->boolean('was_flagged')->default(false);

            $table->smallInteger('eval')->nullable(); // In centipawns

            $table->timestamps();

            $table->unique(['game_id', 'half_move_num']);
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

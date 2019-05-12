<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('game_set_id')->index();
            $table->unsignedInteger('game_group_id')->index()->nullable();

            $table->string('white')->nullable();
            $table->string('black')->nullable();
            $table->enum('result', ['1-0', '0-1', '1/2'])->nullable();

            // Whether we have ran this game through AWS Lambda etc.
            $table->boolean('processed_by_lambda')->default(false);
            $table->boolean('processed_by_user')->default(false);
            $table->text('pgn');
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
        Schema::dropIfExists('games');
    }
}

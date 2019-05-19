<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedInteger('user_id')->index();

            $table->boolean('is_white_to_move');
            $table->string('fen')->unique();
            // Verdict from who is to move perspective
            $table->enum('verdict', ['terrible', 'bad', 'neutral', 'good', 'great'])->nullable();
            
            $table->string('best_move', 16)->nullable();
            $table->string('2nd_best_move', 16)->nullable();
            $table->string('3rd_best_move', 16)->nullable();
            $table->smallInteger('best_move_eval')->nullable(); // In centipawns
            $table->smallInteger('2nd_best_move_eval')->nullable(); // In centipawns
            $table->smallInteger('3rd_best_move_eval')->nullable(); // In centipawns

            $table->text('comment')->nullable();

            $table->boolean('training_enabled')->default(true);

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
        Schema::dropIfExists('positions');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();

            // How many half moves we use to auto-detect GameGroup membership for Game?
            // NULL means we won't save incoming Game to GameGroup
            $table->unsignedInteger('game_group_creation_hm_count')->nullable();

            $table->unsignedInteger('analysis_depth')->default(18);
            $table->unsignedInteger('bad_move_trigger')->default(500); // Centipawns
            $table->smallInteger('bad_position_trigger')->default(-1000); // Centipawns

            $table->unsignedInteger('process_from_nth_move')->default(5);
            $table->unsignedInteger('process_to_nth_move')->default(25);

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
        Schema::dropIfExists('settings');
    }
}

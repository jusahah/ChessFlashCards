<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('name');
            // In what order to test what group incoming game belongs to
            $table->unsignedInteger('priority')->default(999999); // Priority 999999 for user-managed groups
            // If NULL we won't be automatching games to this GameGroup
            $table->string('first_n_moves')->nullable(); // E.g. '1.e4 e5 2.Nf3 Nc6'
            $table->boolean('am_i_white');
            $table->timestamps();

            $table->unique(['user_id', 'first_n_moves', 'am_i_white']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_groups');
    }
}

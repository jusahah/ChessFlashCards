<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerdictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verdicts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('position_id')->index();
            $table->string('move', 16);

            // Note! When inserting we first check if same move played earlier has
            // some verdict already! If so, we use that verdict. If not, we use computer analysis
            // verdict or leave null.
            $table->enum('verdict', ['terrible', 'bad', 'neutral', 'good', 'great'])->nullable();

            $table->timestamps();

            $table->unique(['position_id', 'move', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verdicts');
    }
}

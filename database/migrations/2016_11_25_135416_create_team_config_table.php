<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_config', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title_normalised', 250);
            $table->string('image', 250)->nullable();
            $table->string('background_color', 7);
            $table->string('text_color', 7);
            $table->unsignedTinyInteger('premier_league')->default(0);

            $table->unique('title_normalised');
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
        Schema::dropIfExists('team_config');
    }
}

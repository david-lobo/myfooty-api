<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title', 250);
            $table->string('alias', 250);
            $table->string('image', 250)->nullable();
            $table->unsignedTinyInteger('premier_league')->default(0);
            $table->string('title_normalised', 250);

            $table->unique('title');
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

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('team');
        Schema::enableForeignKeyConstraints();
    }
}

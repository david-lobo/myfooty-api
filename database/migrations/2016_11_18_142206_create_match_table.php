<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('home_id');
            $table->unsignedInteger('away_id');
            $table->dateTime('kickoff')->nullable();
            $table->unsignedInteger('competition_id');

            $table->unique(['home_id', 'away_id', 'kickoff']);

            $table->foreign('away_id')
                  ->references('id')->on('team')
                  ->onDelete('cascade');

            $table->foreign('home_id')
                  ->references('id')->on('team')
                  ->onDelete('cascade');

            $table->foreign('competition_id')
                  ->references('id')->on('competition')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('match');
        Schema::enableForeignKeyConstraints();

    }
}

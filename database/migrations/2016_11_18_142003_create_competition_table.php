<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competition', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title', 250);
            $table->string('title_normalised', 250);
            $table->unsignedTinyInteger('priority')->nullable()->default(null);
            $table->timestamps();
            
            $table->unique('title_normalised');
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
        Schema::dropIfExists('competition');
        Schema::enableForeignKeyConstraints();
    }
}
